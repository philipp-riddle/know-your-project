import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import { fetchCreateThread, fetchCreateThreadCommentItem, fetchCreateThreadPromptItem, fetchDeleteThreadItem } from '@/stores/fetch/ThreadFetcher';
import { usePageSectionStore } from '@/stores/PageSectionStore';

export const useThreadStore = defineStore('thread', () => {
    const selectedThread = ref(null);
    const isCreatingThread = ref(false);
    const pageSectionStore = usePageSectionStore();

    /**
     * Watch for any changes on the selected thread value.
     * If the selected thread is updated, update the thread context for the page section in the page section store.
     */
    watch(() => selectedThread.value, (newValue) => {
        if (newValue) {
            for (let i = 0; i< pageSectionStore.displayedPageSections.length; i++) {
                let pageSection = pageSectionStore.displayedPageSections[i];

                if (pageSection.threadContext?.thread.id === newValue.id) {
                    pageSectionStore.displayedPageSections[i].threadContext.thread = newValue;
                    break;
                }
            }
        }
    }, { deep: true});

    const createThreadFromPageSection = (pageSection) => {
        return new Promise((resolve) => {
            if (isCreatingThread.value) {
                console.error("Already creating thread");
                return;
            }

            isCreatingThread.value = true;
            fetchCreateThread(pageSection.id).then((thread) => {
                selectedThread.value = thread;
                isCreatingThread.value = false;

                resolve(thread);
            }).catch(() => isCreatingThread.value = false);
        });
    };

    const createThreadCommentItem = (threadId, comment) => {
        return new Promise((resolve) => {
            fetchCreateThreadCommentItem(threadId, comment).then((threadCommentItem) => {
                // Add the comment item to the thread item; this is a bit of a hack to work with serialisation and circular references
                var threadItem = threadCommentItem.threadItem;
                threadItem.threadItemComment = threadCommentItem;

                selectedThread.value.threadItems.push(threadItem);
                isCreatingThread.value = false;

                resolve(threadCommentItem);
            }).catch(() => isCreatingThread.value = false);
        });
    }

    const createThreadPromptItem = (threadId, prompt) => {
        return new Promise((resolve) => {
            fetchCreateThreadPromptItem(threadId, prompt).then((threadPromptItem) => {
                // Add the prompt item to the thread item; this is a bit of a hack to work with serialisation and circular references
                var threadItem = threadPromptItem.threadItem;
                threadItem.itemPrompt = threadPromptItem;

                selectedThread.value.threadItems.push(threadItem);
                isCreatingThread.value = false;

                resolve(threadPromptItem);
            }).catch(() => isCreatingThread.value = false);
        });
    }

    const deleteThreadItem = (threadItem) => {
        return new Promise((resolve) => {
            fetchDeleteThreadItem(threadItem.id).then(() => {
                // Remove the thread item from the thread items
                var threadItems = selectedThread.value.threadItems;
                var index = threadItems.findIndex((item) => item.id === threadItem.id);

                if (index > -1) {
                    threadItems.splice(index, 1);
                }

                selectedThread.value.threadItems = threadItems;
                resolve(threadItem);
            });
        });
    };

    return {
        isCreatingThread,
        selectedThread,
        createThreadFromPageSection,
        createThreadCommentItem,
        createThreadPromptItem,
        deleteThreadItem,
    }
});