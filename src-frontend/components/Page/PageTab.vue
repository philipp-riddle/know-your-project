<template>
    <div class="d-grid flex-column gap-4">
        <PageSectionDraggable :page="page" :pageTab="pageTab" :onPageSectionSubmit="onPageSectionSubmit" />

        <div>
            <div class="row">
                <div class="col-sm-1">
                    <PageSectionCreateButton />
                </div>
                <div class="col-sm-11">
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import PageSection from '@/components/Page/PageSection/PageSection.vue';
    import PageSectionCreateButton from '@/components/Page/PageSection/PageSectionCreateButton.vue';
    import PageSectionDraggable from '@/components/Page/PageSection/PageSectionDraggable.vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { defineProps, ref } from 'vue';

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