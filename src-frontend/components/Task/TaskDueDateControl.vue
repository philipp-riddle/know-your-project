<template>
    <VDropdown
        :placement="'left'"
        :triggers="[]"
        :shown="showDueDatePopover"
    >
        <div class="row mt-2" @click="showDueDatePopover = !showDueDatePopover">
            <div class="col-sm-12 col-md-1 d-flex justify-content-center align-items-top">
                <button class="btn btn-sm m-0 p-0 text-muted d-flex flex-row gap-2" v-tooltip="'Click to change due date'">
                    <font-awesome-icon :icon="['fas', 'spinner']" />
                    <span class="bold">DUE DATE</span>
                </button>
            </div>
            <div class="col-sm-12 col-md-11 col-xl-8">
                <div v-if="isDue" class="alert alert-danger p-2 d-flex flex-row gap-3 align-items-center" v-tooltip="'This task is overdue!'">
                    <span class="bold">Overdue</span>
                    <p class="m-0 text-muted">{{ displayedDueDate }}</p>
                </div>
                <div v-else-if="isDueSoon" class="alert alert-warning p-2 d-flex flex-row gap-3 align-items-center" v-tooltip="'This task is due soon!'">
                    <span class="bold">Due soon</span>
                    <p class="m-0 text-muted">{{ displayedDueDate }}</p>
                </div>
                <p v-else class="m-0 text-muted">{{ displayedDueDate }}</p>
            </div>
        </div>

        <template #popper>
            <div class="p-2 d-flex flex-row justify-content-between gap-1">
                <div>
                    <input
                        type="date"
                        class="form-control"
                        ref="dateInput"
                        :value="currentDate"
                        @change="updateDueDate"
                        @keyup="updateDueDate"
                    >
                    <input
                        type="time"
                        class="form-control"
                        :value="currentTime"
                        ref="timeInput"
                        @change="updateDueDate"
                        @keyup="updateDueDate"
                    >
                </div>
                <button
                    class="btn btn-sm btn-danger"
                    v-if="taskStore.selectedTask.dueDate"
                    @click="resetDueDate"
                >
                    X
                </button>
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { defineProps, ref, computed } from 'vue';
    import { useDebounceFn } from '@vueuse/core';
    import { useTaskStore } from '@/stores/TaskStore.js';
    import { useDateFormatter } from '@/composables/DateFormatter.js';

    const dateFormatter = useDateFormatter();
    const taskStore = useTaskStore();
    const showDueDatePopover = ref(false);
    const dateInput = ref(null);
    const timeInput = ref(null);

    const displayedDueDate = computed(() => {
        if (taskStore.selectedTask?.dueDate) {
            let dateParts = taskStore.selectedTask.dueDate.split('T');
            const date = dateParts[0];
            const time = dateParts[1].slice(0, 5);

            const formattedDate = dateFormatter.formatDate(taskStore.selectedTask.dueDate);
            const formattedDateDistance = dateFormatter.formatDateDistance(taskStore.selectedTask.dueDate);

            return `${formattedDateDistance} (${formattedDate} ${time})`;
        }

        return 'No due date set.';
    });

    const currentDate = computed(() => {
        return taskStore.selectedTask.dueDate ? taskStore.selectedTask.dueDate.split('T')[0] : null;
    });
    const currentTime = computed(() => {
        return taskStore.selectedTask.dueDate ? taskStore.selectedTask.dueDate.split('T')[1].slice(0, 5) : null;
    });

    const isDue = computed(() => {
        if (taskStore.selectedTask?.dueDate) {
            return new Date(taskStore.selectedTask.dueDate) < new Date();
        }

        return false;
    });
    /**
     * A due date is soon if it's in the next 24 hours
     */
    const isDueSoon = computed(() => {
        if (taskStore.selectedTask?.dueDate) {
            return new Date(taskStore.selectedTask.dueDate) < new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
        }

        return false;
    });

    const debouncedTaskUpdate = useDebounceFn(() => {
        taskStore.updateTask(taskStore.selectedTask);
    }, 200);

    const updateDueDate = async () => {
        const date = dateInput.value.value;
        const time = timeInput.value.value === '' ? '00:00' : timeInput.value.value;
        const dueDate = date + 'T' + time;

        taskStore.selectedTask.dueDate = dueDate;

        await debouncedTaskUpdate();
    };
    
    const resetDueDate = async () => {
        taskStore.selectedTask.dueDate = null;
        dateInput.value.value = null;
        timeInput.value.value = null;

        showDueDatePopover.value = false;

        await debouncedTaskUpdate();
    };
</script>