<template>
    <div class="d-flex flex-column gap-1">
        <div class="d-flex flex-row  justify-content-between gap-3 align-items-center">
            <div>
                <small class="text-muted p bold m-0" v-tooltip="'Project name'">{{ projectName }}</small>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { useUserStore } from '@/stores/UserStore.js';
    import { computed, ref, onMounted } from 'vue';

    const userStore = useUserStore();
    const currentUser = ref(null);
    const isLoading = ref(true);

    onMounted(async () => {
        userStore.getCurrentUser().then((user) => {
            currentUser.value = user;
            isLoading.value = false;
        });
    });

    const projectName = computed(() => {
        return (currentUser.value?.selectedProject.name ?? 'Project').toUpperCase();
    });
</script>