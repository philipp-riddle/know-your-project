<template>
    <VDropdown
        :placement="'left'"
        v-if="pageStore.selectedPage?.task !== null"
    >
        <div class="row m-0 p-0" v-if="pageStore.selectedPage">
            <div class="col-sm-12 col-md-3 col-xl-2 d-flex justify-content-center">
                <button class="btn btn-sm m-0 p-0 text-muted flex-fill d-flex flex-row justify-content-end gap-4" v-tooltip="'Click to change status'">
                    <span class="bold">STATUS</span>
                    <font-awesome-icon :icon="['fas', 'spinner']" />
                </button>
            </div>
            <div class="col-sm-12 col-md-9 col-xl-10">
                <p class="m-0 text-muted">{{ pageStore.selectedPage.task.stepType }}</p>
            </div>
        </div>

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