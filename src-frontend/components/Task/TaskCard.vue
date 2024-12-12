<template>
    <div
        class="card task-card mt-2"
        :task="element.id"
        @click="props.onTaskSelect(element)"
    >
        <div class="card-body">
            {{ props.task.name }}
        </div>
    </div>
</template>

<script setup>
    const props = defineProps({
        task: {
            type: Object,
            required: true,
        },
        onTaskSelect: {
            type: Function,
            required: true,
        },
    });
</script>

<style scoped>
    .task-card {
        cursor: pointer;
        backgroundColor: #f8f9fa;
    }
</style>