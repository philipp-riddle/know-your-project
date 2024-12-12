<template>
    <div v-if="taskProvider.getSelectedTask()">
        <div class="d-flex justify-content-between align-items-center">
            <div class="col-sm-10">
                <h3 class="m-0"><input class="magic-input" v-model="taskProvider.getSelectedTask().name" @keyup="updateTaskContents" /></h3>
                <p class="text-muted">{{ taskProvider.getSelectedTask().stepType }}</p>
            </div>
            <div>
                <div class="dropdown task-options">
                    <h5 class="btn btn-primary dropdown-toggle m-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-3">Move</span> <font-awesome-icon :icon="['fas', 'arrow-right']" />
                    </h5>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" ref="dropdownMenu">
                        <li v-for="moveChoice in possibleMoveChoices" :key="moveChoice">
                            <span class="dropdown-item" href="#" @click.stop="onTaskMoveClick(moveChoice)">{{ moveChoice }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="d-flex gap-5 flex-row">
            <div class="d-flex justify-content-between align-items-center">
                <h5
                    v-if="!showDueDatePicker"
                    class="m-0 me-4 no-due-date-heading"
                    @click.stop="showDueDatePickerClick"
                >No due date set.</h5>
                <input
                    type="datetime-local"
                    id="meeting-time"
                    name="meeting-time"
                    ref="dueDatePicker"
                    :style="{ display: showDueDatePicker ? 'block' : 'none' }"
                    v-model="taskProvider.getSelectedTask().dueDate"
                    :min="currentDate"
                    :max="maxDate"
                    @keyup="updateDueDate"
                    />
                <button v-if="showDueDatePicker" class="btn btn-sm btn-danger ms-2" @click.stop="resetDueDate">
                    <font-awesome-icon :icon="['fas', 'xmark']" />
                </button>
            </div>
        </div>

        <div class="mt-4 mb-3">
            <Page :page="taskProvider.getSelectedTask().page" />
        </div>
    </div>
</template>

<script setup>
    import { ref, onMounted, computed } from 'vue';
    import { updateTask, moveTask } from '@/fetch/TaskFetcher.js';
    import { useRouter } from 'vue-router';
    import { useTaskStore } from '@/stores/TaskStore.js';
    import { useTaskProvider } from '@/providers/TaskProvider.js';
    import Page from '@/components/Page/Page.vue';
    import TextArea from '@/components/Util/TextArea.vue';

    const taskStore = useTaskStore();
    const taskProvider = useTaskProvider();
    const router = useRouter();
    const props = defineProps({
        task: {
            type: Object,
            required: true,
        },
        onTaskMove: {
            type: Function,
            required: false,
        },
    });
    taskProvider.setSelectedTask(props.task);
    const taskUpdateTimeout = ref(null);
    const dueDatePicker = ref(null);
    const showDueDatePicker = ref(false);
    const currentDate = ref(null);
    const dropdownMenu = ref(null);
    // max date should be 10 years from now
    const maxDate = computed (() => {
        return new Date(new Date().setFullYear(new Date().getFullYear() + 10)).toISOString().slice(0, 10);
    });
    const possibleMoveChoices = computed(() => {
        return [
            'Discover',
            'Define',
            'Develop',
            'Deliver',
        ].filter((workflowStep) => workflowStep !== taskProvider.getSelectedTask().stepType);
    });

    onMounted(() => {
        showDueDatePicker.value = taskProvider.getSelectedTask().dueDate !== null;
        currentDate.value = taskProvider.getSelectedTask().dueDate ? taskProvider.getSelectedTask().dueDate.slice(0, 16) : new Date().toISOString().slice(0, 16);
    });

    const onTaskMoveClick = (workflowStep) => {
        taskProvider.moveTask(taskProvider.getSelectedTask(), workflowStep, 0).then((movedTask) => {
            if (props.onTaskMove) {
                props.onTaskMove(movedTask);
            }

            dropdownMenu.value.classList.remove('show');
        });
    };

    const showDueDatePickerClick = () => {
        showDueDatePicker.value = !showDueDatePicker.value;
        dueDatePicker.value.style.display = showDueDatePicker.value ? 'block' : 'none';
        dueDatePicker.value.focus();
    };

    const showDueDatePickerBlur = () => {
        showDueDatePicker.value = false;
        dueDatePicker.value.style.display = 'none';
    };

    const updateDueDate = (event) => {
        if (taskProvider.getSelectedTask().dueDate) {
            updateTaskContents();
        }
    };

    const resetDueDate = () => {
        taskProvider.getSelectedTask().dueDate = null;
        updateTaskContents();
        showDueDatePicker.value = false;
    };

    const updateTaskContents = () => {
        taskStore.updateTask(taskProvider.getSelectedTask());

        clearTimeout(taskUpdateTimeout.value);
        taskUpdateTimeout.value = setTimeout(() => {
            taskProvider.updateTask(taskProvider.getSelectedTask());
        }, 400);
    };
</script>

<style scoped lang="sass">
    @import '@/styles/colors.scss';

    .no-due-date-heading {
        cursor: pointer;
        font-style: italic;
    }
</style>