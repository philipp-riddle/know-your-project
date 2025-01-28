<template>
    <div class="d-flex flex-column gap-4" :task="task.id">
        <div
            class="card task-card"
            :class="{ 'card-selected': pageStore.selectedPage?.task?.id === task.id }"
        >
            <div class="card-body p-2 d-flex justify-content-between align-items-start">
                <div class="d-flex flex-column gap-2">
                    <span class="card-text">{{ task.page.name }}</span>
                    <div class="d-flex flex-row gap-1 align-items-center">
                        <div
                            v-if="task.dueDate"
                            class="badge d-flex flex-row align-items-center gap-1"
                            :class="{
                                'bg-danger p-1 m-0': isDue,
                                'bg-warning p-1 m-0': isDueSoon,
                            }"
                            v-tooltip="dueDateTooltip"
                        >
                            <font-awesome-icon icon="fa-solid fa-calendar-check" />
                            <span>{{ dateFormatter.formatShortDateDistance(task.dueDate) }}</span>
                        </div>
                        <div
                            v-if="taskProgress"
                            class="badge d-flex flex-row align-items-center gap-1"
                            :class="{
                                'bg-light': taskProgress.complete === 0,
                                'bg-success': taskProgress.complete === taskProgress.total,
                                'bg-warning': taskProgress.complete > 0 && taskProgress.complete < taskProgress.total,
                            }"
                            v-tooltip="taskProgressTooltip"
                        >
                            <font-awesome-icon icon="fa-solid fa-check-circle" />
                            <span>{{ taskProgress.complete }} / {{ taskProgress.total }}</span>
                        </div>
                        <small v-for="tagPage in task.page.tags">
                            <span class="btn btn-sm me-1" :style="{'background-color': tagPage.tag.color}" v-tooltip="'Tag: '+tagPage.tag.name">&nbsp;&nbsp;&nbsp;</span>
                        </small>
                    </div>
                </div>
                <div class="d-flex flex-row gap-3 justify-content-start align-items-center">
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
        <hr>
    </div>
</template>

<script setup>
    import { computed } from 'vue';
    import { useDateFormatter } from '@/composables/DateFormatter.js';
    import { useTaskStore } from '@/stores/TaskStore.js';
    import { usePageStore } from '@/stores/PageStore.js';

    const props = defineProps({
        task: {
            type: Object,
            required: true,
        },
    });
    const dateFormatter = useDateFormatter();
    const taskStore = useTaskStore();
    const pageStore = usePageStore();

    const taskProgress = computed(() => {
        let checklistItemsTotal = 0;
        let checklistItemsComplete = 0;

        for (const pageTab of props.task.page.pageTabs ?? []) {
            for (const pageSection of Object.values(pageTab.pageSections ?? [])) {
                if (pageSection.pageSectionChecklist) {
                    const pageSectionChecklistItems = pageSection.pageSectionChecklist.pageSectionChecklistItems;

                    checklistItemsTotal += pageSectionChecklistItems.length;
                    checklistItemsComplete += pageSectionChecklistItems.filter((item) => item.complete).length;
                }
            }
        }

        if (checklistItemsTotal === 0) {
            return null;
        }

        return {
            'total': checklistItemsTotal,
            'complete': checklistItemsComplete,
        };
    });
    const taskProgressTooltip = computed(() => {
        if (!taskProgress.value) {
            return '';
        }

        if (taskProgress.value.complete === taskProgress.value.total) {
            return 'All checklist items are complete';
        }

        return `${taskProgress.value.complete} / ${taskProgress.value.total} checklist items complete`;
    });

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
        var formattedDate = dateFormatter.formatDate(props.task.dueDate);

        formattedDate += ' (' + dateFormatter.formatDateDistance(props.task.dueDate) + ')';

        return formattedDate;
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