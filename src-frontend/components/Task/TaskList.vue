<template>
	<div style="min-width: 350px;" class="task-list">
		<div class="card-header d-flex align-items-center">
			<p class=m-0>
				{{ workflowStep.name }}
			</p>
		</div>

		<div v-if="isLoadingTasks">
			<p>Loading tasks...</p>
		</div>
		<div v-else>
			<div v-if="taskStore.getTasks(workflowStep).length === 0">
				<p>No tasks created so far.</p>
			</div>

			<div ref="listArea" class="list-area p-0" style="min-height: 150px" :workflow-step="workflowStep.id">
				<draggable
					class="dragArea pb-4 m-0 d-flex gap-3 flex-column"
					:data-workflowStep="workflowStep"
					tag="ul"
					:list="this.taskStore.tasks[workflowStep] ?? []"
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
				<textarea
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
	</div>
</template>

<script>
	import draggable from "vuedraggable";
	import Vue from 'vue';
	import { ref, onMounted, nextTick, computed } from "vue";
	import { createTask } from "@/fetch/TaskFetcher.js";
	import { useTaskProvider } from "@/providers/TaskProvider.js";
	import TaskCard from '@/components/Task/TaskCard.vue';
	import { useTaskStore } from '@/stores/TaskStore.js';
	import { useRouter } from 'vue-router';

	export default {
		props: {
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
			this.taskProvider = useTaskProvider();
			this.router = useRouter();
			this.loadTasks(this.workflowStep);
		},
		data() {
			return {
				isAddingTask: false,
				isAddingTaskRequestLoading: false,
				newTaskName: '',
				taskStore: null,
				taskProvider: null,
				router: null,
				isLoadingTasks: true,

				listArea: null,
				// newTaskInput: null,
			};
		},
		methods: {
			onDragEnd(event) {
				this.onTaskDrag(event); // pass it to the parent TaskBoard
			},
			loadTasks() {
				this.taskProvider.getTasks(this.workflowStep).then((data) => {
					this.isLoadingTasks = false;
				});
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
				if (task === this.taskProvider.getSelectedTask()) { // nothing to do here - the task is already selected
					return;
				}

				this.taskProvider.setSelectedTask(task);
				this.onTaskSelect(task);
			},
			async onTaskCreateClick(event) {
				if (this.newTaskName.length < 3) {
					return;
				}

				this.isAddingTaskRequestLoading = true;

				this.taskProvider.createTask(this.workflowStep, this.newTaskName).then(async (createdTask) => {
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

<style lang="sass" scoped>
	@import '@/styles/colors.scss';

	.list-area {
		max-height: 80vh;
        overflow-x: hidden;
        overflow-y: scroll;
        white-space: nowrap;
	}

	.dragArea {
		min-height: 150px;
		padding: 0;
	}
</style>