<template>
    <div>
        <div
            v-for="userPosition in userPositions"
            :key="userPosition.user.id"
            class="user-cursor"
            :style="{ top: userPosition.y + 'px', left: userPosition.x + 'px' }"
        >
            <UserCursor :userPosition="userPosition" />
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue';
    import { useRoute } from 'vue-router';
    import UserCursor from '@/components/User/UserCursor.vue';
    import { useUserMovementStore } from '@/stores/UserMovementStore';

    const userMovementStore = useUserMovementStore();
    const currentRoute = useRoute();

    const userPositions = computed(() => {
        return Object.values(userMovementStore.userPositions).filter((position) => position.routeName === currentRoute.name );
    });
</script>

<style>
    .user-cursor {
        position: absolute;
        z-index: 2000;
    }
</style>