<template>
    <div class="card">
        <div class="card-body">
            <h5>{{ projectUser.user.email }}</h5>
            <div v-if="isCurrentUser">
                <small class="text-muted">(you)</small>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, computed } from 'vue';
import { useUserStore } from '@/stores/UserStore.js';

const userStore = useUserStore();
const props = defineProps({
    projectUser: {
        type: Object,
        required: true,
    },
});
const isCurrentUser = computed(() => {
    return userStore.currentUser.id == props.projectUser.user.id;
});
</script>