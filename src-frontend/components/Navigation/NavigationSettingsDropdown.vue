<template>
    <div class="d-flex flex-column gap-1">
        <div class="d-flex flex-row gap-2">
            <div class="dropdown d-flex flex-row gap-3 align-items-center">
                <button v-tooltip="'Logout'" class="btn btn-dark dropdown-toggle" type="button" id="dropdownSettingsMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <font-awesome-icon :icon="['fas', 'cog']" />
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownSettingsMenu">
                    <li><a class="dropdown-item" href="/logout">Logout</a></li>
                    <li><p v-if="!isLoading" class="dropdown-item text-muted">Logged in as {{ currentUser.email }}</p></li>

                </ul>
            </div>
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