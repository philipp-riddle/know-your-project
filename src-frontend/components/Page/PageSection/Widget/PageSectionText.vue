<template>
    <div>
        <PageSectionTextEditor :text="pageSection?.pageSectionText?.content ?? ''" :onTextChange="onTextSubmit" :pageSection="pageSection" />
    </div>
</template>

<script setup>
    import PageSectionTextEditor from '@/components/Page/PageSection/Widget/PageSectionTextEditor.vue';
    import { ref } from 'vue';

    const props = defineProps({
        pageSection: { // this prop is only set if we have an already existing section
            type: Object,
            required: false,
        },
        onPageSectionSubmit: {
            type: Function,
            required: true,
        },
    });
    const pageSection = ref(props.pageSection);

    const onTextSubmit = (text) => {
        props.onPageSectionSubmit({
            pageSectionText: {
                content: text,
            },
        });
    };
</script>