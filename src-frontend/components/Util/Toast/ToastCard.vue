<template>
    <div class="card toast-card m-0 p-0" :class="toastCardClass">
        <div class="card-body m-0 p-3 d-flex flex-row align-items-start justify-content-between gap-3">
            <div class="d-flex flex-column align-items-start gap-1">
                <p class="m-0 p-0 bold">{{ toast.message }} </p>
                <p v-if="toast.type == 'error'" class="text-muted m-0 p-0">Error</p>
            </div>
            <button
                @click="closeToast"
                class="m-0 p-0 btn btn-lg btn-toast-close" aria-label="Close"
            >
                <font-awesome-icon :icon="['fas', 'times']" />
            </button>
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue';
    import { useToastStore } from '@/stores/ToastStore';

    const props = defineProps({
        toast: {
            type: Object,
            required: true
        },
    });
    const toastStore = useToastStore();

    const toastCardClass = computed(() => {
        return {
            'bg-success': props.toast.type === 'success',
            'bg-danger': props.toast.type === 'error',
            'bg-warning': props.toast.type === 'warning',
            'bg-info': props.toast.type === 'info',
        };
    });

    const closeToast = () => {
        toastStore.removeToast(props.toast.id);
    };
</script>