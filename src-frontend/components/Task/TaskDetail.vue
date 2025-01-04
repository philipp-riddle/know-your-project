<template>
    <div v-if="taskStore.selectedTask">
        <div class="d-flex justify-content-between row">
            <div class="col-sm-12 offset-xl-1 col-xl-8">
                <h2 class="mb-3" v-tooltip="'Edit task name'"><input class="magic-input" v-model="taskStore.selectedTask.name" @keyup="updateTaskContents" /></h2>
            </div>
        </div>

        <TaskStatusControl v-if="taskStore.selectedTask" :onTaskMove="onTaskMove" />
        <TaskDueDateControl v-if="taskStore.selectedTask" />

        <div class="mb-3">
            <Page :page="taskStore.selectedTask.page" />
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue';
    import { useRouter } from 'vue-router';
    import { useTaskStore } from '@/stores/TaskStore.js';
    import Page from '@/components/Page/Page.vue';
    import TextArea from '@/components/Util/TextArea.vue';
    import TaskStatusControl from '@/components/Task/TaskStatusControl.vue';
    import TaskDueDateControl from '@/components/Task/TaskDueDateControl.vue';

    const taskStore = useTaskStore();
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

    onMounted(() => {
        taskStore.setSelectedTask(props.task);
    });

    const onTaskMoveClick = (workflowStep) => {
        taskStore.moveTask(taskStore.selectedTask, workflowStep, 0).then((movedTask) => {
            if (props.onTaskMove) {
                props.onTaskMove(movedTask);
            }
        });
    };
</script>

<style scoped lang="sass">
    @import '@/styles/colors.scss';

    .no-due-date-heading {
        cursor: pointer;
        font-style: italic;
    }
</style>