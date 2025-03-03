<template>
    <div
        class="w-100 col-sm-12 col-md-6 col-lg-4 mb-2"
        v-for="invitation in userStore.userProjectInvitations"
    >
        <div class="card w-100">
            <div class="card-body d-flex flex-row justify-content-between align-items-center">
                <UserBadge
                    v-if="invitation.user && invitation.user.profilePicture !== null"
                    :user="invitation.user"
                    imageSize="small"
                />
                <div class="d-flex flex-column align-items-start justify-content-center">
                    <h5 class="m-0">{{ invitation.email }}</h5>
                    <p class="text-muted m-0"><i>Invitation pending</i></p>
                </div>
                <button class="btn btn-dark-gray" v-tooltip="'Cancel invitation'" @click="() => userStore.deleteUserProjectInvitation(invitation)">
                    <font-awesome-icon :icon="['fas', 'trash']" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { useUserStore } from '@/stores/UserStore.js';
    import UserBadge from '@/components/User/UserBadge.vue';

    const userStore = useUserStore();
</script>