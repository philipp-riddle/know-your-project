<template>
	<div style="min-width: 350px;">
		<div v-if="pageSectionStore && pageSectionStore.displayedPageSections" class="list-area p-0" style="min-height: 150px" :page="page.id">
			<draggable
				class="dragArea pb-4 m-0 d-flex gap-4 flex-column"
				:data-page="page"
				tag="ul"
				v-model="this.pageSectionStore.displayedPageSections"
				item-key="id"
				@start="onDragStart"
        		@end="onDragEnd"
			>
				<template #item="{ element }">
					<div
						:class="{ 'card': pageSectionStore.isDraggingPageSection }"
					>
						<PageSection :page="page" :pageSection="element" :onPageSectionSubmit="onPageSectionDraggableSubmit" :onPageSectionDelete="onPageSectionDelete" />
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
	import { createTask } from "@/stores/fetch/TaskFetcher.js";
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
				pageSectionStore: null,
				router: null,
			};
		},
		methods: {
			onDragStart(event) {
				this.pageSectionStore.isDraggingPageSection = true;
			},
			onDragEnd(event) {
				this.pageSectionStore.isDraggingPageSection = false;
				const pageSectionIdOrder = [];

                for (let i = 0; i < event.to.children.length; i++) {
					let pageSectionElement = event.to.children[i];

					// @todo this is weird - in the task modal this is different than in the standalone page editor
					if (pageSectionElement.getAttribute('data-draggable') == 'true') {
						pageSectionElement = pageSectionElement.children[0];
					}

					let pageSectionId = parseInt(pageSectionElement.getAttribute('page-section'));

					if (isNaN(pageSectionId)) {
						continue; // this is to prevent us from including the non-initialized tasks
					}

                    pageSectionIdOrder.push(pageSectionId);
                }

				this.pageSectionStore.reorderSections(this.pageTab.id, pageSectionIdOrder);
			},
			onPageSectionDraggableSubmit(pageSection, updatedPageSectionItem) {
				return new Promise(async (resolve) => {
					if (this.onPageSectionSubmit) {
						this.onPageSectionSubmit(pageSection, updatedPageSectionItem).then((updatedSection) => {
							resolve(updatedSection);
						});

						return;
					}

					resolve(pageSection);
				});
			},
			onPageSectionDelete(pageSection) {
				this.pageSectionStore.deleteSection(pageSection).then((deletedSection) => {
					if (this.onPageSectionDelete) {
						this.onPageSectionDelete(deletedSection);
					}
				});
			},
		},
	};
</script>

<style lang="sass" scoped>
	@import '@/styles/colors.scss';

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