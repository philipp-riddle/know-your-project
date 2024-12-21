<template>
    <div class="d-grid flex-column gap-4">
        <PageSectionDraggable :page="page" :pageTab="pageTab" :onPageSectionSubmit="onPageSectionSubmit" />

        <div>
            <div class="row">
                <div class="col-sm-1">
                    <PageSectionCreateButton />
                </div>
                <div class="col-sm-11">
                    <PageSectionCreateWidget :page="page" :onCreate="onPageSectionSubmit" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import PageSection from '@/components/Page/PageSection/PageSection.vue';
    import PageSectionCreateButton from '@/components/Page/PageSection/PageSectionCreateButton.vue';
    import PageSectionCreateWidget from '@/components/Page/PageSection/PageSectionCreateWidget.vue';
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

    /**
     * This value is set if the user is currently adding a new section.
     * This may be null if the user is not adding a new section.
     * Making it one value is practical as this limits the application naturally to only add a section at one place at a time.
     */
    const sectionAddIndex = ref(null);
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
                    ...pageSection,
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