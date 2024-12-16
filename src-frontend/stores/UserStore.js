import { defineStore } from 'pinia';
import { fetchGetCurrentUser, fetchUserProjectInvititations, fetchCreateUserProjectInvitation } from '@/fetch/UserFetcher';
import { ref } from 'vue';

export const useUserStore = defineStore('user', () => {
    const currentUser = ref(null);
    const userProjectInvitations = ref([]);

    async function getCurrentUser() {
        return new Promise((resolve) => {
            if (currentUser.value) {
                resolve(currentUser.value);
            }

            fetchGetCurrentUser().then((userInfo) => {
                currentUser.value = userInfo;
                resolve(userInfo);
            });
        });
    }

    async function getUserProjectInvitations(projectId) {
        return new Promise((resolve) => {
            if (userProjectInvitations.value.length) {
                resolve(userProjectInvitations.value);
            }

            fetchUserProjectInvititations(projectId).then((invitations) => {
                userProjectInvitations.value = invitations;
                resolve(invitations);
            });
        });
    }

    async function createUserProjectInvitation(project, email) {
        return new Promise((resolve) => {
            fetchCreateUserProjectInvitation(project.id, email).then((invitation) => {
                userProjectInvitations.value.push(invitation);
                resolve(invitation);
            });
        });
    }

    return {
        currentUser,
        getCurrentUser,
        userProjectInvitations,
        getUserProjectInvitations,
        createUserProjectInvitation,
    };
});