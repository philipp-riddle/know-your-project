<template>
    <div
        class="modal fade"
        id="taskDetailModal"
        tabindex="-1"
        aria-labelledby="taskDetailModalLabel"
        aria-hidden="true"
        ref="taskDetailModal"
        @blur="onCloseModal"
    >
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content m-0 p-0">
                <div class="modal-body m-0 p-0 pt-xl-3 pt-sm-2">
                    <div v-if="pageStore.selectedPage">
                        <TaskDetail :page="pageStore.selectedPage" :task="pageStore.selectedPage.task" />
                    </div>
                    <div v-else>
                        <p>nichts geladen?</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import TaskDetail from '@/components/Task/TaskDetail.vue';
    import { useTaskStore } from '@/stores/TaskStore.js';
    import { usePageStore } from '@/stores/PageStore.js';

    import { Modal } from "bootstrap";
    import { onMounted, onUnmounted, ref, nextTick } from "vue";
    import { useRoute, useRouter } from 'vue-router';

    const taskStore = useTaskStore();
    const pageStore = usePageStore();
    const route = useRoute();
    const router = useRouter();
    const taskId = route.params.id;
    const taskDetailModal = ref(null);

    onMounted(() => {
        nextTick();
        const modal = new Modal(document.getElementById('taskDetailModal'));

        taskStore.getTask(taskId).then((task) => {
            // on mount set the selected page and force it to fetch it from the server to get all the required serialised contents
            pageStore.setSelectedPage(task.page, true).then(() => {
                modal.show();
            });
        });
    });

    /**
     * When unmounting the component, we want to make sure the modal backdrop is removed.
     * This is necessary because the modal backdrop is not part of the Vue component tree and therefore not automatically removed.
     */
    onUnmounted(() => {
        document.getElementsByClassName('modal-backdrop')[0]?.remove();
        pageStore.resetStore(); // reset the store to prevent any data leakage from the previously opened task page
    });

    const onCloseModal = () => {
        // this prevents faulty closing of the modal;
        // this way we do not have to create any other dependencies and just rely on this 'show' class being set
        if (taskDetailModal.value && taskDetailModal.value.classList.contains('show')) {
            return;
        }

        router.push({ name: 'Tasks' });
    };
</script>

<style scoped lang="sass">
    .modal-dialog {
        padding-left: 40%;
        padding-right: 0%;
        padding-top: 0%;
        padding-bottom: 0%;
        
        // on mobile we want a margin on the top
        @media (max-width: 768px) {
            padding-left: 0%;
            padding-right: 0%;
            padding-top: 10%;
            padding-bottom: 0%;
        }
    }

    .modal-content {
        border-radius: 0px !important;
    }
</style>