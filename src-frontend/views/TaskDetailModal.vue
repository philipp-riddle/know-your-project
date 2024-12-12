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
            <div class="modal-content">
                <div class="modal-body">
                    <div v-if="taskStore.getSelectedTask()">
                        <TaskDetail :task="taskStore.getSelectedTask()" />
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
    import { useTaskProvider } from '@/providers/TaskProvider.js';
    import { useTaskStore } from '@/stores/TaskStore.js';

    import { Modal } from "bootstrap";
    import { onMounted, ref, nextTick } from "vue";
    import { useRoute, useRouter } from 'vue-router';

    const taskProvider = useTaskProvider();
    const taskStore = useTaskStore();
    const route = useRoute();
    const router = useRouter();
    const taskId = route.params.id;
    const taskDetailModal = ref(null);
    const task = ref(null);
    const modalShow = ref(false);

    onMounted(() => {
        nextTick();
        const modal = new Modal(document.getElementById('taskDetailModal'));

        taskProvider.getTask(taskId).then((task) => {
            taskStore.setSelectedTask(task);
            modal.show();
            modalShow.value = true;
        });
    });

    const onCloseModal = () => {
        // this prevents faulty closing of the modal;
        // this way we do not have to create any other dependencies and just rely on this 'show' class being set
        if (taskDetailModal.value && taskDetailModal.value.classList.contains('show')) {
            return;
        }

        taskProvider.resetSelectedTask();
        router.push({ name: 'Tasks' });
    };
</script>

<style scoped lang="sass">
    .modal-dialog {
        padding-left: 20%;
        padding-right: 0%;
        padding-top: 5%;
        padding-bottom: 0%;
    }

    .modal-content {
        border-radius: 15px !important;
    }
</style>