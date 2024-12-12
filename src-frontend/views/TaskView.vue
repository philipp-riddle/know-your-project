<template>
    <div class="row">
        <div class="col-sm-12 col-md-4">
            <TaskList
                :workflowStep="props.stepType"
                :onTaskSelect="onTaskSelect"
                :onTaskDrag="onTaskDrag"
                :onTaskCreate="onTaskCreate"
                :onTaskDelete="onTaskDelete"
            />
        </div>
        <div class="col-sm-12 col-md-8" v-if="taskProvider.getTasks(props.stepType)">
            <router-view></router-view>
        </div>
    </div>
</template>

<script setup>
    import { onMounted, ref } from 'vue';
    import { useRouter } from 'vue-router';
    import TaskList from '@/components/Task/TaskList.vue';
    import TaskDetail from '@/components/Task/TaskDetail.vue';
    import { useTaskProvider } from '@/providers/TaskProvider.js';
    
    const props = defineProps({
        stepType: {
            type: String,
            required: true
        }
    });

    const taskProvider = useTaskProvider();
    const router = useRouter();

    const onTaskSelect = (task) => {
        router.push({ name: props.stepType+'Detail', params: {id: task.id}});
    };

    const onTaskDrag = (event) => {
        const taskId = parseInt(event.item.getAttribute('task'));
        const taskOrderIndex = event.newIndex;

        const taskIdOrder = [];

        for (let i = 0; i < event.to.children.length; i++) {
            taskIdOrder.push(parseInt(event.to.children[i].getAttribute('task')));
        }

        taskProvider.changeOrder(props.stepType, taskIdOrder);
    };

    const onTaskCreate = (task) => {
        taskProvider.setSelectedTask(task);
        router.push({ name: props.stepType + 'Detail', params: {id: task.id}});
    }

    const onTaskDelete = (task) => {
        if (task.id === taskProvider.getSelectedTask()?.id) {
            taskProvider.resetSelectedTask();
            router.push({ name: props.stepType });
        }
    }
</script>