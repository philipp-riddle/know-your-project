<template>
    <button
        class="btn btn-sm position-relative"
        v-tooltip="tooltip"
        @click="onThreadStart"
        :disabled="threadStore.isCreatingThread"
        :class="{
            'btn-dark': pageSection.threadContext && pageSection.threadContext.id == threadStore.selectedThread?.id,
            'p-0': pageSection.threadContext === null,
        }"
    >
        <font-awesome-icon :icon="['fas', 'comments']" />
        <span v-if="pageSection.threadContext != null" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            {{ pageSection.threadContext.thread.threadItems.length }}
        </span>
    </button>
</template>

<script setup>
    import { useThreadStore } from '@/stores/ThreadStore.js';
    import { computed } from 'vue';

    const props = defineProps({
        pageSection: {
            type: Object,
            required: false,
        },
    });
    const threadStore = useThreadStore();

    const tooltip = computed(() => {
        var tooltip = '';

        if (props.pageSection.threadContext) {
            tooltip = 'Resume the thread';
        } else {
            tooltip = 'Start a thread';
        }

        if (props.pageSection.aiPrompt !== null) {
            tooltip += ' with the assistant';
        }

        return tooltip;
    });


    const onThreadStart = () => {
        if (props.pageSection.threadContext === null) {
            threadStore.createThreadFromPageSectionAIPrompt(props.pageSection); // this creates a thread and opens the thread box automatically
        } else {
            threadStore.selectedThread = props.pageSection.threadContext.thread; // this opens the thread box for the already existing task
        }
    };
</script>