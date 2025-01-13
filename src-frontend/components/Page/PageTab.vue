<template>
    <div class="d-grid flex-column gap-4 mb-5">
        <PageSectionDraggable :page="page" :pageTab="pageTab" :onPageSectionSubmit="onPageSectionSubmit" />

        <div class="row">
            <!--  we need this many classes to be in the same layout as the page section -->
            <div class="offset-md-3 offset-xl-2 col-sm-12 col-md-9 col-xl-10">
                <div v-if="pageSectionStore.isCreatingPageSection" class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-3 col-xl-2">
                <PageSectionCreateButton />
            </div>
        </div>
    </div>
</template>

<script setup>
    import PageSectionCreateButton from '@/components/Page/PageSection/PageSectionCreateButton.vue';
    import PageSectionDraggable from '@/components/Page/PageSection/PageSectionDraggable.vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
        pageTab: {
            type: Object,
            required: true,
        },
        selectedTabId: {
            type: Number,
            required: false,
        },
    });

    const pageSectionStore = usePageSectionStore();

    const onPageSectionSubmit = async (pageSection, updatedPageSectionItem) => {
        if (updatedPageSectionItem instanceof File) {
            return new Promise(async (resolve) => {
                const formData = new FormData();
                formData.append('file', updatedPageSectionItem);

                pageSectionStore.uploadSection(props.pageTab.id, updatedPageSectionItem).then((response) => {
                    resolve(response);
                });
            });
        }

        return new Promise(async (resolve) => {
            const pageSectionSubmitObject = {
                id: pageSection.id,
                ...updatedPageSectionItem,
            };

            pageSectionStore.updateSection(pageSectionSubmitObject).then((updatedSection) => {
                resolve(updatedSection);
            });
        });
    };

    const onPageSectionDelete = async (pageSection) => {
        await pageSectionStore.deleteSection(pageSection);
    };
</script>