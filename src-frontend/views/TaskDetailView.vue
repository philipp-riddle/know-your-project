<template>
    <div v-if="!isLoading && taskProvider.getSelectedTask()">
        <TaskDetail :task="taskProvider.getSelectedTask()" :onTaskMove="onTaskMove" />
    </div>
</template>

<script setup>
    import TaskDetail from '@/components/Task/TaskDetail.vue';
    import { useRoute, useRouter } from 'vue-router';
    import { onMounted, ref } from 'vue';
    import { useTaskProvider } from '@/providers/TaskProvider.js';

    const route = useRoute();
    const router = useRouter();
    const taskProvider = useTaskProvider();
    const isLoading = ref(true);

    onMounted(() => {
        taskProvider.getTask(route.params.id).then((data) => {
            taskProvider.setSelectedTask(data);
            isLoading.value = false;
        });
    });

    const onTaskMove = (movedTask) => {
        router.push({ name: workflowStep + 'Detail', params: {id:taskProvider.getSelectedTask().id}});
    };
</script>