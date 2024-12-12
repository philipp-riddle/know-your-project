import { defineStore } from 'pinia';
import { fetchGetCurrentUser } from '@/fetch/UserFetcher';
import { ref } from 'vue';

export const useUserStore = defineStore('user', () => {
    const currentUser = ref(null);

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

    return {
        currentUser,
        getCurrentUser,
    };
});