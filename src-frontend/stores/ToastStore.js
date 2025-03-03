import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([]);

    const addToast = (type, message, autoExpiry=5000) => {
        const id = Math.random().toString(36).substring(7);
        toasts.value.push({ id, type, message });

        if (autoExpiry > 0) {
            setTimeout(() => {
                toasts.value = toasts.value.filter((toast) => toast.id !== id);
            }, autoExpiry);
        }
    }

    const removeToast = (id) => {
        toasts.value = toasts.value.filter((toast) => toast.id !== id);
    };

    return {
        toasts,
        addToast,
        removeToast,
    }
});