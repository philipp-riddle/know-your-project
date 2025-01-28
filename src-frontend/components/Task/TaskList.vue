<template>
	<div class="task-list d-flex flex-column flex-fill overflow-scroll">
		<div class="d-flex align-items-center">
			<p class=m-0>
				{{ workflowStep.name }}
			</p>
		</div>

		<div v-if="tasks.length === 0">
			<p class="text-muted m-0"><i>No tasks in this step</i></p>
		</div>

		<div
			ref="listArea"
			class="task-list-area d-flex flex-column gap-3 flex-fill p-0"
			style="min-height: 15rem;"
			:workflow-step="workflowStep.id"
		>
			<draggable
				class="task-drag-area pb-4 m-0 d-flex gap-3 flex-column"
				:data-workflowStep="workflowStep"
				tag="ul"
				:list="tasks"
				:group="{ name: 'tasks' }"
				item-key="id"
				@end="onDragEnd"
			>
				<template #item="{ element }">
					<TaskCard :task="element" @click="onTaskClick(element)" />
				</template>
			</draggable>
		</div>

		<div class="pb-4 mb-0" v-if="isAddingTask">
			
		</div>
	</div>
	<div
		class="btn btn-dark btn-add-task d-flex align-items-center justify-content-center"
		@click="showNewTaskInput(!isAddingTask)"
		v-if="!isAddingTask && !isAddingTaskRequestLoading"
	>
		<p class="pe-3 m-0"><font-awesome-icon icon="fa-solid fa-plus" /></p>
		<p class="m-0">ADD NEW TASK</p>
	</div>
	<textarea
		v-else
		cols="9"
		rows="2"
		style="max-width: 100%;"
		class="form-control"
		ref="newTaskInput"
		v-model="newTaskName"
		@keyup.enter="onTaskCreateClick"
		@keyup.escape="showNewTaskInput(false)"
		@blur="showNewTaskInput(false)"
		:disabled="isAddingTaskRequestLoading"
	></textarea>
</template>

<script>
	import draggable from "vuedraggable";
	import Vue from 'vue';
	import { ref, onMounted, nextTick, computed } from "vue";
	import { createTask } from "@/stores/fetch/TaskFetcher.js";
	import TaskCard from '@/components/Task/TaskCard.vue';
	import { useTaskStore } from '@/stores/TaskStore.js';
	import { usePageStore } from '@/stores/PageStore.js';
	import { useRouter } from 'vue-router';

	export default {
		props: {
			tasks: {
				type: Array,
				required: true,
			},
			workflowStep: {
				type: String,
				required: true
			},
			onTaskDrag: {
				type: Function,
				required: false,
			},
			onTaskSelect: {
				type: Function,
				required: true,
			},
			onTaskCreate: {
				type: Function,
				required: false,
			},
		},
		components: {
			draggable,
			TaskCard,
		},
		name: "nested-draggable",
		mounted() {
			this.taskStore = useTaskStore();
			this.pageStore = usePageStore();
			this.router = useRouter();
		},
		data() {
			return {
				isAddingTask: false,
				isAddingTaskRequestLoading: false,
				newTaskName: '',
				taskStore: null,
				pageStore: null,
				router: null,
				listArea: null,
			};
		},
		methods: {
			onDragEnd(event) {
				this.onTaskDrag(event); // pass it to the parent TaskBoard
			},
			async showNewTaskInput(show) {
				this.$data.isAddingTask = show;
				if (show) {
					await nextTick(); // wait for the DOM to show the controls

					if (this.$refs.listArea) {
						this.$refs.listArea.scrollTop = this.$refs.listArea.scrollHeight;
					}

					if (this.$refs.newTaskInput) {
						this.$refs.newTaskInput.focus(); // set focus directly on the new task input
					}
				}
			},
			onTaskClick(task) {
				this.taskStore.setSelectedTask(task);
				this.onTaskSelect(task);
			},
			async onTaskCreateClick(event) {
				if (this.newTaskName.length < 3) {
					return;
				}

				this.isAddingTaskRequestLoading = true;

				this.taskStore.createTask(this.workflowStep, this.newTaskName).then(async (createdTask) => {
					this.newTaskName = '';

					if (this.onTaskCreate) {
						this.onTaskCreate(createdTask);
					}

					this.$data.isAddingTask = true;
					this.isAddingTaskRequestLoading = false;
					await nextTick();
					this.$refs.newTaskInput?.focus(); // set focus back on the new task input
				});
			},
		},
	};
</script>