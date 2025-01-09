<template>
    <div class="d-flex flex-row gap-3 task-overview h-100 p-5 m-2 pt-2 pb-2">
        <div>
            <h5 v-tooltip="'Discover and understand the problem'">Discover</h5>
            <TaskList workflowStep="Discover" :onTaskSelect="onTaskSelect" :onTaskDrag="onTaskDrag" />
        </div>
        <div>
            <h5 v-tooltip="'Define the requirements'">Define</h5>
            <TaskList workflowStep="Define" :onTaskSelect="onTaskSelect" :onTaskDrag="onTaskDrag" />
        </div>
        <div>
            <h5 v-tooltip="'Develop the solution to solve the problem'">Develop</h5>
            <TaskList workflowStep="Develop" :onTaskSelect="onTaskSelect" :onTaskDrag="onTaskDrag" />
        </div>
        <div>
            <h5 v-tooltip="'Deliver the solution, testing it out'">Deliver</h5>
            <TaskList workflowStep="Deliver" :onTaskSelect="onTaskSelect" :onTaskDrag="onTaskDrag" />
        </div>
    </div>

    <div v-if="taskStore.tasks['Discover'] && taskStore.tasks['Define'] && taskStore.tasks['Develop'] && taskStore.tasks['Deliver']">
        <router-view></router-view>
    </div>
</template>

<script setup>
    import TaskList from '@/components/Task/TaskList.vue';
    import TaskDetailModal from '@/views/TaskDetailModal.vue';
    import { useTaskStore } from '@/stores/TaskStore.js';

    import { ref } from 'vue';
    import { useRouter } from 'vue-router';

    const taskStore = useTaskStore();
    const router = useRouter();
    const discoverTasks = ref(null);
    const developTasks = ref(null);
    const deliverTasks = ref(null);

    const onTaskSelect = (task) => {
        router.push({ name: 'TasksDetail', params: {id: task.id}}); // @todo rework with modal route
    };

    const onTaskDrag = (event) => {
        const movedTaskToOtherList = event.from !== event.to;
        const taskId = parseInt(event.item.getAttribute('task'));
        const taskOrderIndex = event.newIndex;
        const targetWorkflowStep = event.to.getAttribute('data-workflowStep');

        taskStore.getTask(taskId).then((task) => {
            if (movedTaskToOtherList) {
                taskStore.moveTask(task, targetWorkflowStep, taskOrderIndex, true);
            } else {
                const taskIdOrder = [];

                for (let i = 0; i < event.to.children.length; i++) {
                    taskIdOrder.push(parseInt(event.to.children[i].getAttribute('task')));
                }

                taskStore.changeTaskOrder(targetWorkflowStep, taskIdOrder);
            }
        });
    };
</script>

<style scoped>
    .task-overview {
		overflow-x: auto !important;
		overflow-y: hidden !important;
    }

    .outer {
        width: 500px;
        height: 100px;
        white-space: nowrap;
        position: relative;
        overflow-x: scroll;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }
</style>