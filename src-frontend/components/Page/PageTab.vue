<template>
    <div class="">
        <div v-for="pageSection in pageSectionStore.displayedPageSections">
            <PageSection :page="page" :pageSection="pageSection" :onPageSectionSubmit="onPageSectionSubmit" :onPageSectionDelete="onPageSectionDelete" />
        </div>

        <PageSectionCreateWidget :page="page" :onCreate="onPageSectionSubmit" />
    </div>
</template>

<script setup>
    import PageSection from '@/components/Page/PageSection.vue';
    import PageSectionCreateWidget from '@/components/Page/PageSectionCreateWidget.vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { usePageTabStore } from '@/stores/PageTabStore.js';
    import { defineProps } from 'vue';

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
    const pageTabStore = usePageTabStore();
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