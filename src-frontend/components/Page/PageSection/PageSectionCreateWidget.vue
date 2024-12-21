<template>
    <div class="d-flex flex-column gap-4">
        <PageSectionText
            v-if="pageSectionStore.pageSectionAddType == 'text'"
            :onPageSectionSubmit="onPageWidgetCreate"
        />

        <PageSectionChecklist
            v-if="pageSectionStore.pageSectionAddType == 'checklist'"
            :onPageSectionSubmit="onPageWidgetCreate"
        />
    </div>
</template>

<script setup>
    import { useTaskProvider } from '@/providers/TaskProvider.js';
    import { onMounted, ref } from 'vue';
    import PageSectionChecklist from '@/components/Page/PageSectionChecklist.vue';
    import PageSectionText from '@/components/Page/PageSectionText.vue';
    import PageSection from '@/components/Page/PageSection/PageSection.vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';

    const props = defineProps({
        onCreate: {
            type: Function,
            required: true,
        },
        addIndex: {
            type: Number,
            required: false,
            default: null,
        }
    });
    const pageSectionStore = usePageSectionStore();

    const onPageWidgetCreate = (pageSection) => {
        props.onCreate(pageSection);
    };

    onMounted(() => {
        pageSectionStore.pageSectionAddType = 'text';
    });
</script>