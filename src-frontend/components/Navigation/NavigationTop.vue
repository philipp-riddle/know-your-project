<template>
    <div class="d-flex flex-column gap-1">
        <div class="d-flex flex-row  justify-content-between gap-3 align-items-center">
            <span v-if="!isLoading">
                <small class="text-muted" v-tooltip="'Project name'"><strong>{{ currentUser.selectedProject.name.toUpperCase() }}</strong></small>
            </span>
        </div>
    </div>
</template>
<script setup>
    import { useUserStore } from '@/stores/UserStore.js';
    import { ref, onMounted } from 'vue';

    const userStore = useUserStore();
    const currentUser = ref(null);
    const isLoading = ref(true);

    onMounted(async () => {
        userStore.getCurrentUser().then((user) => {
            currentUser.value = user;
            isLoading.value = false;
        });
    });
</script>