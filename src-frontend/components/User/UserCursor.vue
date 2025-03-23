<template>
    <div class="d-flex flex-row align-items-center gap-2">
        <font-awesome-icon
            :icon="['fas', 'arrow-pointer']"
            :style="{ color: userPosition.color }"
        />

        <UserBadge
            v-if="userPosition.user.profilePicture != null"
            :user="userPosition.user"
            :borderColor="userPosition.color"
            imageSize="xxs"
        />
        <span
            v-else
            class="badge badge-sm badge-cursor"
            :style="{ color: userPosition.color }"
        >
            {{ userPosition.user.email }}
        </span>
    </div>
</template>

<script setup>
    import UserBadge from '@/components/User/UserBadge.vue';

    const props = defineProps({
        userPosition: Object,
    });
</script>