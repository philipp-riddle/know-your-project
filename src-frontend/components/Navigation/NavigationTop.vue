<template>
    <div class="d-flex flex-column gap-1">
        <div class="d-flex flex-row  justify-content-between gap-3 align-items-center">
            <small class="text-muted p bold m-0" v-tooltip="'Project name'">{{ projectName }}</small>
            <NavigationFilterDropdown />
        </div>
    </div>
</template>
<script setup>
    import { computed, ref, onMounted } from 'vue';
    import { useUserStore } from '@/stores/UserStore.js';
    import NavigationFilterDropdown from '@/components/Navigation/NavigationFilterDropdown.vue';

    const userStore = useUserStore();
    const currentUser = ref(null);
    const isLoading = ref(true);

    onMounted(async () => {
        userStore.getCurrentUser().then((user) => {
            isLoading.value = false;
        });
    });

    const projectName = computed(() => {
        return (userStore.currentUser?.selectedProject.name ?? 'Project').toUpperCase();
    });
</script>