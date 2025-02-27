<template>
    <div
        class="d-flex flex-column align-items-center justify-content-start"
        :class="{
            // if we do not set this class the thread button is always visible.
            // this helps with boosting the visibility of the existing thread and its items; otherwise the thread button is only visible on hover.
            'section-options': !showThreadButton,
        }"
    >
        <button
            class="btn m-0 p-2 position-relative"
            v-tooltip="tooltip"
            @click="() => toggleThreadBox()"
            :disabled="threadStore.isCreatingThread"
            :class="{
                'btn-dark-gray': pageSection.threadContext && pageSection.threadContext.id == threadStore.selectedThread?.id,
                'active': pageSection.threadContext && pageSection.threadContext.id == threadStore.selectedThread?.id,
                'btn-light-gray': !pageSection.threadContext || pageSection.threadContext.id != threadStore.selectedThread?.id,
                'p-0': pageSection.threadContext === null,
            }"
        >
            <font-awesome-icon :icon="['fas', 'comments']" />
            <span v-if="pageSection.threadContext != null && pageSection.threadContext.thread.threadItems.length > 0" class="position-absolute top-50 start-100 translate-middle badge rounded-pill bg-danger">
                {{ pageSection.threadContext.thread.threadItems.length }}
            </span>
        </button>
    </div>
</template>

<script setup>
    import { useThreadStore } from '@/stores/ThreadStore.js';
    import { computed, ref } from 'vue';

    const props = defineProps({
        pageSection: {
            type: Object,
            required: false,
        },
    });
    const threadStore = useThreadStore();

    const tooltip = computed(() => {
        if (props.pageSection.threadContext && threadStore.selectedThread && props.pageSection.threadContext.thread.id === threadStore.selectedThread.id) {
            return 'Close the thread';
        }

        var tooltip = '';

        if (hasThreadWithItems.value) {
            tooltip = 'Resume the thread';
        } else {
            tooltip = 'Start a thread';
        }

        if (isAIThread.value) {
            tooltip += ' with the assistant';
        }

        return tooltip;
    });
    const isAIThread = computed(() => {
        return props.pageSection.aiPrompt !== null;
    });
    const hasThreadWithItems = computed(() => {
        return props.pageSection.threadContext !== null && props.pageSection.threadContext?.thread.threadItems.length > 0;
    });
    const showThreadButton = computed(() => {
        if (hasThreadWithItems.value) {
            return true;
        }

        if (null === threadStore.selectedThread) {
            return false;
        }

        return threadStore.selectedThread.id === props.pageSection.threadContext?.thread.id;
    })

    const toggleThreadBox = () => {
        if (null === props.pageSection.threadContext || threadStore.selectedThread?.id !== props.pageSection.threadContext.thread.id) {
            onThreadStart();
        } else {
            threadStore.selectedThread = null;
        }
    };

    const onThreadStart = () => {
        if (props.pageSection.threadContext === null) {
            // this creates an empty thread from the page section and opens the thread box automatically
            threadStore.createThreadFromPageSection(props.pageSection).then((createdThread) => {
                const newPageSectionThreadContext = {
                    ...createdThread.pageSectionContext,
                    thread: createdThread,
                };
                props.pageSection.threadContext = newPageSectionThreadContext;
            });
        } else {
            var threadValue = props.pageSection.threadContext.thread;
            threadValue.pageSectionContext = { // we inject the page section into the thread context data as it is not fully serialised (it is a circular reference)
                pageSection: props.pageSection,
            };
            threadStore.selectedThread = threadValue; // this opens the thread box for the already existing thread
        }
    };
</script>