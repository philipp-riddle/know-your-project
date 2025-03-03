<template>
    <div class="toast-box">
        <div v-for="toast in toastStore.toasts" :key="toast.id">
            <ToastCard :toast="toast" />
        </div>
    </div>
</template>

<script setup>
    import ToastCard from '@/components/Util/Toast/ToastCard.vue';
    import { useToastStore } from '@/stores/ToastStore';

    const toastStore = useToastStore();
</script>