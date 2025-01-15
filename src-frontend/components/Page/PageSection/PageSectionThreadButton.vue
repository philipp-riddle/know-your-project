<template>
    <div
        class="d-flex flex-column align-items-center justify-content-start"
        :class="{
            // if we do not set this class the thread button is always visible.
            // this helps with boosting the visibility of the existing thread and its items; otherwise the thread button is only visible on hover.
            'section-options': !hasThreadWithItems && threadStore.selectedThread?.id !== threadContext?.thread.id,
        }"
    >
        <button
            class="btn position-relative mt-lg-3"
            v-tooltip="tooltip"
            @click="() => toggleThreadBox()"
            :disabled="threadStore.isCreatingThread"
            :class="{
                'btn-dark': threadContext && threadContext.id == threadStore.selectedThread?.id,
                'p-0': threadContext === null,
            }"
        >
            <font-awesome-icon :icon="['fas', 'comments']" />
            <span v-if="threadContext != null && threadContext.thread.threadItems.length > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ threadContext.thread.threadItems.length }}
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
    const threadContext = ref(props.pageSection.threadContext);

    const tooltip = computed(() => {
        if (threadContext.value && threadStore.selectedThread && threadContext.value.thread.id === threadStore.selectedThread.id) {
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
        return threadContext.value && threadContext.value.thread.threadItems.length > 0;
    });

    const toggleThreadBox = () => {
        if (threadStore.selectedThread === null || threadStore.selectedThread.id !== threadContext.value.thread.id) {
            onThreadStart();
        } else {
            threadStore.selectedThread = null;
        }
    };

    const onThreadStart = () => {
        if (threadContext.value === null) {
            if (isAIThread.value) {
                // this creates a thread and opens the thread box automatically
                threadStore.createThreadFromPageSectionAIPrompt(props.pageSection);
            } else {
                // this creates an empty thread from the page section and opens the thread box automatically
                threadStore.createThreadFromPageSection(props.pageSection);
            }
        } else {
            var threadValue = threadContext.value.thread;
            threadValue.pageSectionContext = { // we inject the page section into the thread context data as it is not fully serialised (it is a circular reference)
                pageSection: props.pageSection,
            };
            threadStore.selectedThread = threadValue; // this opens the thread box for the already existing task
        }
    };
</script>