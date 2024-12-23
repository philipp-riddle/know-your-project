<template>
    <div class="page-section row" :page-section="pageSection.id">
        <div class="col-sm-1">
            <div class="section-options d-flex flex-row gap-3" v-if="pageSection.id != null">
                <PageSectionInfo :pageSection="pageSection" />
                <button class="btn" v-tooltip="'Rearrange order'">
                    <span class="black"><font-awesome-icon :icon="['fas', 'grip-vertical']" /></span>
                </button>
            </div>
        </div>

        <!-- the PageSection elements all need the v-once directive! -->
        <!-- this is important to not cause any re-renders of the object when updating the pageSection ref value - this could interrupt the user flow of typing. -->
        <!-- @todo we need to rethink this when we introduce real time editing as this will require background updates -->
        <PageSectionText
            v-once
            v-if="pageSection.pageSectionText != null"
            class="col-sm-11"
            :pageSection="pageSection"
            :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
        />
        <PageSectionChecklist
            v-once
            v-if="pageSection.pageSectionChecklist != null"
            class="col-sm-11"
            :pageSection="pageSection"
            :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
        />
        <PageSectionURL
            v-once
            v-if="pageSection.pageSectionURL != null"
            class="col-sm-11"
            :pageSection="pageSection"
            :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
        />
        <PageSectionUpload
            v-once
            v-if="pageSection.pageSectionUpload != null"
            class="col-sm-11"
            :pageSection="pageSection"
            :onPageSectionSubmit="(sectionItem) => onPageSectionSubmitHandler(pageSection, sectionItem)"
        />
    </div>
</template>

<script setup>
    import PageSectionCreateButton from '@/components/Page/PageSection/PageSectionCreateButton.vue';
    import PageSectionInfo from '@/components/Page/PageSection/PageSectionInfo.vue';
    import PageSectionChecklist from '@/components/Page/PageSection/Widget/PageSectionChecklist.vue';
    import PageSectionUpload from '@/components/Page/PageSection/Widget/PageSectionUpload.vue';
    import PageSectionText from '@/components/Page/PageSection/Widget/PageSectionText.vue';
    import PageSectionURL from '@/components/Page/PageSection/Widget/PageSectionURL.vue';
    import { defineProps, ref, onMounted } from 'vue';
    import { useDebounceFn } from '@vueuse/core';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
        pageSection: {
            type: Object,
            required: true,
        },
        onPageSectionSubmit: {
            type: Function,
            required: true,
        },
        onPageSectionDelete: {
            type: Function,
            required: true,
        },
    });
    const pageSection = ref(props.pageSection);
    const debouncedPageSectionSubmit = useDebounceFn((section, sectionItem) => props.onPageSectionSubmit(section, sectionItem), 500);

    onMounted(() => {
        // our non-initialized objects have a string ID to make it easier to identify them for Vue - we filter them out here
        if (isNaN(pageSection.value.id)) {
            delete pageSection.value.id;
        }
    });

    const onPageSectionSubmitHandler = async (section, sectionItem) => {
        return new Promise(async (resolve) => {
            debouncedPageSectionSubmit(section, sectionItem).then((updatedSection) => {
                if (updatedSection) {
                    pageSection.value = updatedSection;
                    resolve(updatedSection);
                }
            });
        });
    };
</script>

<style scoped>
    .section-options:not(.active) {
        opacity: 0.0 !important;
        display: none;
    }

    .page-section:hover > div > .section-options {
        opacity: 1.0 !important;
        transition: 'opacity' 0.5s 'ease-in-out';
    }

    .page-section:hover > div > .section-options {
        opacity: 1.0 !important;
        transition: 'opacity' 0.5s 'ease-in-out';
    }
</style>