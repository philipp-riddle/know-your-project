<template>
    <VDropdown
        v-if="pageStore.selectedPage?.task !== null"
        class="d-flex flex-row align-items-center p-1 gap-1"
    >
        <button
            v-tooltip="task.stepType"
            class="btn m-0 p-1"
        >
            <font-awesome-icon :icon="['fas', 'spinner']" />
        </button>

        <template #popper>
            <div class="p-2">
                <ul class="nav nav-pills nav-fill d-flex flex-column gap-1">
                    <li class="nav-item" v-for="moveChoice in possibleMoveChoices">
                        <button
                            class="nav-link d-flex flex-row gap-3 align-items-center"
                            :class="{ 'active': pageStore.selectedPage.task.stepType === moveChoice, 'inactive': pageStore.selectedPage.task.stepType !== moveChoice }"
                            @click.stop="onTaskMoveClick(moveChoice)"
                        >
                            {{ moveChoice }}
                        </button>
                    </li>
                </ul>
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { ref, computed } from 'vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useTaskStore } from '@/stores/TaskStore.js';

    const props = defineProps({
        task: {
            type: Object,
            required: true,
        },
    });

    const taskStore = useTaskStore();
    const pageStore = usePageStore();
    const possibleMoveChoices = computed(() => {
        return [
            'Discover',
            'Define',
            'Develop',
            'Deliver',
        ];
    });

    const onTaskMoveClick = (workflowStep) => {
        taskStore.moveTask(pageStore.selectedPage.task, workflowStep, 0, false).then((movedTask) => {
            pageStore.selectedPage.task = movedTask;
        });
    };

</script>