<template>
	<div style="min-width: 350px;" class="task-list">
		<div v-if="pageSectionStore && pageSectionStore.displayedPageSections" class="list-area p-0" style="min-height: 150px" :page="page.id">
			<draggable
				class="dragArea pb-4 m-0 d-flex gap-3 flex-column"
				:data-page="page"
				tag="ul"
				v-model="this.pageSectionStore.displayedPageSections"
				:group="{ name: 'pageSections' }"
				item-key="id"
				@start="onDragStart"
        		@end="onDragEnd"
			>
				<template #item="{ element }">
					<div
						:class="{ 'card': pageSectionStore.isDraggingPageSection }"
					>
						<PageSection :index="1" :page="page" :pageSection="element" :onPageSectionSubmit="onPageSectionSubmit" :onPageSectionDelete="onPageSectionDelete" />
					</div>
				</template>
			</draggable>
		</div>
		<div v-else>
			<p>No page sections created so far.</p>
		</div>
	</div>
</template>

<script>
	import draggable from "vuedraggable";
	import Vue from 'vue';
	import { ref, onMounted, nextTick, computed } from "vue";
	import { createTask } from "@/fetch/TaskFetcher.js";
	import TaskCard from '@/components/Task/TaskCard.vue';
	import PageSection from '@/components/Page/PageSection/PageSection.vue';
	import { usePageSectionStore } from '@/stores/PageSectionStore.js';
	import { useRouter } from 'vue-router';

	export default {
		props: {
			page: {
				type: Object,
				required: true,
			},
			pageTab: {
				type: Object,
				required: true,
			},
			onPageSectionSubmit: {
				type: Function,
				required: false,
			},
			onTaskCreate: {
				type: Function,
				required: false,
			},
			onTaskDelete: {
				type: Function,
				required: false,
			}
		},
		components: {
			draggable,
			PageSection
		},
		name: "page-section-draggable",
		mounted() {
			this.router = useRouter();
			this.pageSectionStore = usePageSectionStore();
		},
		data() {
			return {
				isAddingTask: false,
				isAddingTaskRequestLoading: false,
				newTaskName: '',
				taskProvider: null,
				pageSectionStore: null,
				router: null,
			};
		},
		methods: {
			onDragStart(event) {
				console.log('onDragStart', event);
				this.pageSectionStore.isDraggingPageSection = true;
			},
			onDragEnd(event) {
				this.pageSectionStore.isDraggingPageSection = false;
				const pageSectionIdOrder = [];

                for (let i = 0; i < event.to.children.length; i++) {
                    pageSectionIdOrder.push(parseInt(event.to.children[i].getAttribute('page-section')));
                }

				this.pageSectionStore.reorderSections(this.pageTab.id, pageSectionIdOrder);
			},
			onPageSectionSubmit(pageSection, updatedPageSectionItem) {
				return new Promise(async (resolve) => {
					if (this.onPageSectionSubmit) {
						this.onPageSectionSubmit(pageSection, updatedPageSectionItem).then((updatedSection) => {
							resolve(updatedSection);
						});

						return;
					}

					resolve(pageSection);
					// if (pageSection.id) {
					// 	const pageSectionSubmitObject = {
					// 		id: pageSection.id,
					// 		...updatedPageSectionItem,
					// 	};

					// 	this.pageSectionStore.updateSection(pageSectionSubmitObject).then((updatedSection) => {
					// 		resolve(updatedSection);
					// 	});
					// } else {
					// 	const pageSectionSubmitObject = {
					// 		id: pageSection.id,
					// 		...pageSection,
					// 	};
					// 	this.pageSectionStore.createSection(this.pageTab.id, pageSectionSubmitObject).then((createdSection) => {
					// 		resolve(createdSection);
					// 	});
					// }
				});
			},
			onPageSectionDelete(pageSection) {
				this.pageSectionStore.deleteSection(pageSection).then((deletedSection) => {
					if (this.onPageSectionDelete) {
						this.onPageSectionDelete(deletedSection);
					}
				});
			},
			// onTaskClick(task) {
			// 	if (task === this.taskProvider.getSelectedTask()) { // nothing to do here - the task is already selected
			// 		return;
			// 	}

			// 	this.taskProvider.setSelectedTask(task);
			// 	this.onTaskSelect(task);
			// },
			// async onTaskCreateClick(event) {
			// 	if (this.newTaskName.length < 3) {
			// 		return;
			// 	}

			// 	this.isAddingTaskRequestLoading = true;

			// 	this.taskProvider.createTask(this.workflowStep, this.newTaskName).then(async (createdTask) => {
			// 		this.newTaskName = '';

			// 		if (this.onTaskCreate) {
			// 			this.onTaskCreate(createdTask);
			// 		}

			// 		this.$data.isAddingTask = true;
			// 		this.isAddingTaskRequestLoading = false;
			// 		await nextTick();
			// 		this.$refs.newTaskInput?.focus(); // set focus back on the new task input
			// 	});
			// },
			// async onTaskDeleteClick(task) {
			// 	this.taskProvider.deleteTask(task).then((deletedTask) => {
			// 		if (this.onTaskDelete) {
			// 			this.onTaskDelete(task);
			// 		}
			// 	});
			// },
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

	.card {
		border-width: 2px;
	}

	.card:hover {
		cursor: pointer;
		border-color: $green !important;
		border-width: 2px;
	}

	.dragArea {
		min-height: 150px;
		padding: 0;
	}
</style>