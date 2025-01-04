<template>
    <div
        class="card task-card"
        :class="{ 'card-selected': taskStore.selectedTask?.id === task.id }"
        :task="task.id"
    >
        <div class="card-body d-flex justify-content-between">
            <div class="d-flex flex-column gap-2">
                <span>{{ task.name }}</span>
                <div class="d-flex flex-row gap-1 align-items-center">
                    <small v-for="tagPage in task.page.tags">
                        <span class="btn btn-sm me-2" :style="{'background-color': tagPage.tag.color}" v-tooltip="'Tag: '+tagPage.tag.name">&nbsp;&nbsp;&nbsp;</span>
                    </small>

                    <div
                        v-if="task.dueDate"
                        class="d-flex flex-row align-items-center gap-1"
                        :class="{
                            'alert alert-danger p-1 m-0': isDue,
                            'alert alert-warning p-1 m-0': isDueSoon,
                        }"
                        v-tooltip="dueDateTooltip"
                    >
                        <font-awesome-icon icon="fa-solid fa-calendar-check" />
                        <span>{{ dateFormatter.formatDateDistance(task.dueDate) }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-row gap-2">
                <span>{{ getTaskProgress(task) }}</span>
                <div class="dropdown card-options">
                    <h5 class="dropdown-toggle m-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" @click.stop="">
                        <font-awesome-icon :icon="['fas', 'ellipsis']" />
                    </h5>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><span class="dropdown-item" href="#" @click.stop="onTaskDeleteClick(task)">Archive Task</span></li>
                        <li><span class="dropdown-item" href="#" @click.stop="onTaskDeleteClick(task)">Delete Task</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { defineProps, computed } from 'vue';
    import { useDateFormatter } from '@/composables/DateFormatter.js';
    import { useTaskStore } from '@/stores/TaskStore.js';

    const props = defineProps({
        task: {
            type: Object,
            required: true,
        },
    });
    const dateFormatter = useDateFormatter();
    const taskStore = useTaskStore();

    const getTaskProgress = (task) => {
        let checklistItemsTotal = 0;
        let checklistItemsComplete = 0;

        for (const pageTab of task.page.pageTabs) {
            for (const pageSection of Object.values(pageTab.pageSections ?? [])) {
                if (pageSection.pageSectionChecklist) {
                    const pageSectionChecklistItems = pageSection.pageSectionChecklist.pageSectionChecklistItems;

                    checklistItemsTotal += pageSectionChecklistItems.length;
                    checklistItemsComplete += pageSectionChecklistItems.filter((item) => item.complete).length;
                }
            }
        }

        if (checklistItemsTotal === 0) {
            return '';
        }

        return checklistItemsComplete + '/' + checklistItemsTotal;
    };

    const onTaskDeleteClick = async (task) =>  {
        taskStore.deleteTask(task);
    };

    const isDue = computed(() => {
        return props.task.dueDate && new Date(props.task.dueDate) < new Date();
    });

    /**
     * A due date is soon if it's in the next 24 hours
     */
    const isDueSoon = computed(() => {
        return props.task.dueDate && new Date(props.task.dueDate) < new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
    });

    const dueDateTooltip = computed(() => {
        return dateFormatter.formatDate(props.task.dueDate);
    });
</script>

<style scoped lang="scss">
	@import '@/styles/colors.scss';

	.card {
		border-width: 2px;
	}

	.card:hover {
		cursor: pointer;
		border-color: $green !important;
		border-width: 2px;
	}

	.card-selected {
		background-color: $green !important;
		border-color: $green !important;
		color: white;
	}
</style>