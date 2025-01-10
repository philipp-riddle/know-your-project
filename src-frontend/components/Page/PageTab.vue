<template>
    <div class="d-grid flex-column gap-4 mb-5">
        <PageSectionDraggable :page="page" :pageTab="pageTab" :onPageSectionSubmit="onPageSectionSubmit" />

        <div class="row">
            <div class="col-sm-12 col-md-3 col-xl-2">
                <PageSectionCreateButton />
            </div>
        </div>
    </div>
</template>

<script setup>
    import PageSection from '@/components/Page/PageSection/PageSection.vue';
    import PageSectionCreateButton from '@/components/Page/PageSection/PageSectionCreateButton.vue';
    import PageSectionDraggable from '@/components/Page/PageSection/PageSectionDraggable.vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { ref } from 'vue';

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
            if (pageSection.id) {
                const pageSectionSubmitObject = {
                    id: pageSection.id,
                    ...updatedPageSectionItem,
                };

                pageSectionStore.updateSection(pageSectionSubmitObject).then((updatedSection) => {
                    resolve(updatedSection);
                });
            } else {
                const pageSectionSubmitObject = {
                    id: pageSection.id,
                    ...updatedPageSectionItem,
                };

                pageSectionStore.createSection(props.pageTab.id, pageSectionSubmitObject).then((createdSection) => {
                    resolve(createdSection);
                });
            }
        });
    };

    const onPageSectionDelete = async (pageSection) => {
        await pageSectionStore.deleteSection(pageSection);
    };
</script>