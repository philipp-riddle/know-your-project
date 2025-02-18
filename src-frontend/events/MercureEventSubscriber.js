import { useMercureStore } from '@/stores/MercureStore';
import { useUserStore } from '@/stores/UserStore';
import { ref, watch } from 'vue';
import { EventSourcePolyfill } from 'event-source-polyfill';
import { usePageEventHandler } from '@/events/handlers/PageEventHandler';
import { useTagEventHandler } from '@/events/handlers/TagEventHandler';
import { useTagPageEventHandler } from '@/events/handlers/TagPageEventHandler';
import { useTaskEventHandler } from '@/events/handlers/TaskEventHandler';

export function useMercureEventSubscriber() {
    const mercureStore = useMercureStore();
    const userStore = useUserStore();
    const eventSource = ref(null);

    // event handlers - used to bundle incoming events to the appropriate store by entity type, e.g. 'Page'
    const pageEventHandler = usePageEventHandler();
    const tagEventHandler = useTagEventHandler();
    const tagPageEventHandler = useTagPageEventHandler();
    const taskEventHandler = useTaskEventHandler();

    watch(() => mercureStore.jws, (newJws) => {
        if  (newJws) {
            connect(); // whenever the JWS token changes, reconnect to the Mercure hub
        }
    }, {deep: true});

    const setup = (config) => {
        mercureStore.url = config.url;
        mercureStore.jws = config.jws;
        mercureStore.topics = config.topics;
    }

    /**
     * The connection to the Mercure event hub is lazy; this is to prevent unnecessary connections.
     * The connection is established when the first topic is subscribed.
     */
    const connect = () => {
        console.log('Connecting to Mercure Hub...');

        if (!mercureStore.url || !mercureStore.jws) {
            console.error('Mercure URL and JWS must be set. Forgot to call setup()?');
            return;
        }

        if (mercureStore.topics.length == 0) {
            console.warn('No topics to subscribe to. Call subscribeToTopic() to subscribe to a topic.');
            return;
        }

        const url = new URL(mercureStore.url);

        for (const topic of mercureStore.topics) { // default topics the user immediately subscribes to
            url.searchParams.append('topic', topic);
        }

        // close the current connection if it exists
        close();

        // create and open a new EventSource connection
        eventSource.value = new EventSourcePolyfill(url, {
            withCredentials: true,
            headers: {
                Authorization: `Bearer ${mercureStore.jws}`,
            },
        });

        eventSource.value.onopen = () => {
            console.log('Connection to Mercure Hub established');
            mercureStore.isConnected = true;

            // setup a timeout to refresh the JWS token before it expires
            mercureStore.refreshJWSTimeout();
        }

        eventSource.value.onerror = (error) => {
            if (error.error) {
                console.error('Error in Mercure Hub EventSource', error);
            }

            // EventSource readyStates: 0 = CONNECTING, 1 = OPEN, 2 = CLOSED
            if (eventSource.value.readyState !== 1 && mercureStore.isConnected) {
                mercureStore.isConnected = false;

                if (error.error) {
                    console.error('Lost connection to Mercure', error);
                }
            }
        }

        eventSource.value.onmessage = (event) => {
            const data = JSON.parse(event.data);

            if (data.user == userStore.currentUser.id) {
                return; // ignore events that were triggered by the current user
            }

            console.log(data.endpoint + ' > '  + data.action + ' event');
            
            if (data.endpoint === 'Page') {
                pageEventHandler.handle(data);
            } else if (data.endpoint === 'Task') {
                taskEventHandler.handle(data);
            } else if (data.endpoint == 'Tag') {
                tagEventHandler.handle(data);
            } else if (data.endpoint === 'TagPage') {
                tagPageEventHandler.handle(data);
            } else {
                console.error('Unknown endpoint', data.endpoint);
                console.error('Event', event);
            }
        }
    }

    const close = () => {
        if (eventSource.value) {
            eventSource.value.close();
        }
    }

    const subscribeToTopic = (topic) => {
        if (!mercureStore.isConnected) {
            connect();
        }

        // deep copy the original topics array
        const originalTopics = [...mercureStore.topics];
        mercureStore.topics.push(topic);
        mercureStore.topics = mercureStore.topics.filter((value, index, self) => self.indexOf(value) === index); // filter out duplicates

        if (originalTopics !== mercureStore.topics) {
            connect(); // reconnect with the new topics
        }
    }

    return {
        setup,
        connect,
        subscribeToTopic,
    };
}