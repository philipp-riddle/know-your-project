<template>
    <div class="d-flex flex-fill flex-row task-overview">
        <div v-if="isLoadingTasks" class="p-5">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div v-else class="ps-5 pe-5 pt-3 pb-3 flex-fill d-flex flex-row justify-content-between gap-3">
            <div v-for="(tooltip, step) in stepsAndTooltips" :key="step" class="d-flex flex-column gap-3">
                <h5 class="m-0" v-tooltip="tooltip">{{ step }}</h5>
                <TaskList
                    :workflowStep="step"
                    :tasks="taskStore.tasks[step] ?? []"
                    :onTaskSelect="onTaskSelect"
                    :onTaskDrag="onTaskDrag"
                />
            </div>
        </div>
    </div>

    <router-view></router-view>
</template>

<script setup>
    import TaskList from '@/components/Task/TaskList.vue';
    import TaskDetailModal from '@/views/TaskDetailModal.vue';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useTaskStore } from '@/stores/TaskStore.js';

    import { ref, onMounted } from 'vue';
    import { useRouter } from 'vue-router';

    const stepsAndTooltips = {
        'Discover': 'Discover and understand the problem',
        'Define': 'Define the requirements',
        'Develop': 'Develop the solution to solve the problem',
        'Deliver': 'Deliver the solution, testing it out',
    };

    const projectStore = useProjectStore();
    const taskStore = useTaskStore();
    const router = useRouter();
    const isLoadingTasks = ref(true);

    onMounted(() => {
        projectStore.getSelectedProject().then((selectedProject) => {
            taskStore.getTasks().then(() => {
                isLoadingTasks.value = false;
            });
        })
    })

    const onTaskSelect = (task) => {
        router.push({ name: 'TasksDetail', params: {id: task.id}}); // @todo rework with modal route
    };

    const onTaskDrag = (event) => {
        const movedTaskToOtherList = event.from !== event.to;
        const taskId = parseInt(event.item.getAttribute('task'));

        if (!taskId) {
            console.error('Task ID not found in dragged element', event.item);
            return;
        }

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
</style>