import { defineStore } from 'pinia';
import { fetchGetCurrentUser, fetchUserProjectInvititations, fetchCreateUserProjectInvitation, fetchDeleteUserProjectInvitation, fetchUploadUserProfilePicture } from '@/stores/fetch/UserFetcher';
import { ref } from 'vue';

export const useUserStore = defineStore('user', () => {
    const currentUser = ref(null);
    const userProjectInvitations = ref([]);
    const currentPromise = ref(null); // used to store the promise of the current fetchCurrentUser call to avoid multiple calls

    function setup() {
        // use the prefetched value from the window object if it exists
        if (window.currentUser) {
            currentUser.value = window.currentUser;
        }
    }

    async function getCurrentUser() {
        return new Promise((resolve) => {
            if (currentUser.value) {
                resolve(currentUser.value);
                return;
            }

            // if we are already fetching the user, wait for the promise to resolve
            if (currentPromise.value) {
                currentPromise.value.then(() => {
                    resolve(currentUser.value);
                });
                return;
            }

            currentPromise.value  = fetchGetCurrentUser().then((userInfo) => {
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

    async function deleteUserProjectInvitation(invitationId) {
        return new Promise((resolve) => {
            fetchDeleteUserProjectInvitation(invitationId).then(() => {
                userProjectInvitations.value = userProjectInvitations.value.filter((invitation) => invitation.id !== invitationId);
                resolve();
            });
        });
    }

    async function uploadUserProfilePicture(pictureFile) {
        return new Promise((resolve) => {
            fetchUploadUserProfilePicture(pictureFile).then((userInfo) => {
                currentUser.value = userInfo;
                resolve(userInfo);
            });
        });
    }

    return {
        currentUser,
        userProjectInvitations,
        setup,
        getCurrentUser,
        getUserProjectInvitations,
        createUserProjectInvitation,
        deleteUserProjectInvitation,
        uploadUserProfilePicture,
    };
});