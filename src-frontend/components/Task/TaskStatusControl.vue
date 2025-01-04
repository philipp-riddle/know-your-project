<template>
    <VDropdown
        :placement="'left'"
    >
        <div class="row tags-container">
            <div class="col-sm-12 col-md-1 d-flex justify-content-center">
                <button class="btn btn-sm m-0 p-0 text-muted d-flex flex-row gap-2" v-tooltip="'Click to change status'">
                    <font-awesome-icon :icon="['fas', 'spinner']" />
                    <span class="bold">STATUS</span>
                </button>
            </div>
            <div class="col-sm-12 col-md-11 col-xl-8">
                <p class="m-0 text-muted">{{ taskStore.selectedTask.stepType }}</p>
            </div>
        </div>

        <template #popper>
            <div class="p-2">
                <ul class="nav nav-pills nav-fill d-flex flex-column gap-1">
                    <li class="nav-item" v-for="moveChoice in possibleMoveChoices">
                        <button
                            class="nav-link d-flex flex-row gap-3 align-items-center"
                            :class="{ 'active': taskProvider.getSelectedTask().stepType === moveChoice, 'inactive': taskProvider.getSelectedTask().stepType !== moveChoice }"
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
    import { defineProps, ref, computed } from 'vue';
    import { useTaskProvider } from '@/providers/TaskProvider.js';
    import { useTaskStore } from '@/stores/TaskStore.js';

    const props = defineProps({
        onTaskMove: {
            type: Function,
            required: false,
        },
    });

    const taskStore = useTaskStore();
    const taskProvider = useTaskProvider();
    const possibleMoveChoices = computed(() => {
        return [
            'Discover',
            'Define',
            'Develop',
            'Deliver',
        ];
    });

    const onTaskMoveClick = (workflowStep) => {
        taskStore.moveTask(taskStore.selectedTask, workflowStep, 0).then((movedTask) => {
            if (props.onTaskMove) {
                props.onTaskMove(movedTask);
            }
        });
    };

</script>