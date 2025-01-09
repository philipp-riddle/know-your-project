<template>
    <VDropdown
        :placement="'left'"
        :triggers="[]"
        :shown="showDueDatePopover"
    >
        <div class="row" @click="showDueDatePopover = !showDueDatePopover">
            <div class="col-sm-12 col-md-3 col-xl-2 d-flex justify-content-center align-items-top">
                <button class="btn btn-sm m-0 p-0 text-muted d-flex flex-row gap-2 align-items-center" v-tooltip="'Click to change due date'">
                    <font-awesome-icon :icon="['fas', 'fa-calendar-check']" />
                    <span class="bold">DUE DATE</span>
                </button>
            </div>
            <div class="col-sm-12 col-md-9 col-xl-10 col-xl-8">
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
                    v-if="pageStore.selectedPage.task.dueDate"
                    @click="resetDueDate"
                >
                    X
                </button>
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { ref, computed } from 'vue';
    import { useDebounceFn } from '@vueuse/core';
    import { useTaskStore } from '@/stores/TaskStore.js';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useDateFormatter } from '@/composables/DateFormatter.js';

    const dateFormatter = useDateFormatter();
    const pageStore = usePageStore();
    const taskStore = useTaskStore();
    const showDueDatePopover = ref(false);
    const dateInput = ref(null);
    const timeInput = ref(null);

    const displayedDueDate = computed(() => {
        if (pageStore.selectedPage.task?.dueDate) {
            let dateParts = pageStore.selectedPage.task.dueDate.split('T');
            const date = dateParts[0];
            const time = dateParts[1].slice(0, 5);

            const formattedDate = dateFormatter.formatDate(pageStore.selectedPage.task.dueDate);
            const formattedDateDistance = dateFormatter.formatDateDistance(pageStore.selectedPage.task.dueDate);

            return `${formattedDateDistance} (${formattedDate} ${time})`;
        }

        return 'No due date set.';
    });

    const currentDate = computed(() => {
        return pageStore.selectedPage.task.dueDate ? pageStore.selectedPage.task.dueDate.split('T')[0] : null;
    });
    const currentTime = computed(() => {
        return pageStore.selectedPage.task.dueDate ? pageStore.selectedPage.task.dueDate.split('T')[1].slice(0, 5) : null;
    });

    const isDue = computed(() => {
        if (pageStore.selectedPage.task?.dueDate) {
            return new Date(pageStore.selectedPage.task.dueDate) < new Date();
        }

        return false;
    });
    /**
     * A due date is soon if it's in the next 24 hours
     */
    const isDueSoon = computed(() => {
        if (pageStore.selectedPage.task?.dueDate) {
            return new Date(pageStore.selectedPage.task.dueDate) < new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
        }

        return false;
    });

    const debouncedTaskUpdate = useDebounceFn(() => {
        taskStore.updateTask(pageStore.selectedPage.task).then((updatedTask) => {
            pageStore.selectedPage.task = updatedTask;
        });
    }, 200);

    const updateDueDate = async () => {
        const date = dateInput.value.value;
        const time = timeInput.value.value === '' ? '00:00' : timeInput.value.value;
        const dueDate = date + 'T' + time;

        pageStore.selectedPage.task.dueDate = dueDate;

        await debouncedTaskUpdate();
    };
    
    const resetDueDate = async () => {
        pageStore.selectedPage.task.dueDate = null;
        dateInput.value.value = null;
        timeInput.value.value = null;

        showDueDatePopover.value = false;

        await debouncedTaskUpdate();
    };
</script>