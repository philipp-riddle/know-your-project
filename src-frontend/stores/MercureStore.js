import { defineStore } from 'pinia';
import { ref } from 'vue';
import { fetchJWS } from '@/stores/fetch/MercureFetcher';
import { useProjectStore } from '@/stores/ProjectStore';

/**
 * This store is used to manage any connections to the mercure hub,
 * such as subscribed topics and meta data about the connection.
 */
export const useMercureStore = defineStore('mercure', () => {
    const projectStore = useProjectStore();

    const isConnected = ref(false);
    const url = ref(null);
    const jws = ref(null);
    const currentJwsPromise = ref(null); // used to store the current JWS promise; if it is still loading, don't refresh the token over and over again
    const topics = ref ([]);

    const refreshTimeout = ref(null); // variable to store the timeout for refreshing the JWS token

    const refreshJWS = () => {
        if (currentJwsPromise.value) {
            return;
        }

        if (topics.value.length === 0) {
            return;
        }

        const projectId = projectStore.selectedProject.id;

        currentJwsPromise.value = fetchJWS(projectId, topics.value).then((data) => {
            jws.value = data.token;
            currentJwsPromise.value = null;
        });
    }

    const refreshJWSTimeout = () => {
        const jwsPayload = JSON.parse(window.atob(jws.value.split('.')[1]));
        const remainingSeconds = Math.max(0, jwsPayload.exp - Math.floor(Date.now() / 1000) - 5); // -5 seconds to make sure the token is refreshed before it expires

        if (refreshTimeout.value) {
            clearTimeout(refreshTimeout.value);
        }

        refreshTimeout.value = setTimeout(() => {
            refreshJWS();
        }, remainingSeconds * 1000);
    };

    return {
        isConnected,
        url,
        jws,
        topics,

        refreshJWS,
        refreshJWSTimeout,
    };
});