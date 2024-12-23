<template>
    <div>
        <!-- <TextArea
            placeholder="Start typing"
            :onTextSubmit="onTextSubmit"
            :onTextChange="onTextChange"
            :text="pageSection?.pageSectionText?.content ?? ''"
            :resetOnSubmit="pageSection == null"
        /> -->

        <PageSectionTextEditor :text="pageSection?.pageSectionText?.content ?? ''" :onTextChange="onTextSubmit" />
    </div>
</template>

<script setup>
    import PageSectionChecklistItem from '@/components/Page/PageSection/Widget/PageSectionChecklistItem.vue';
    import TextArea from '@/components/Util/TextArea.vue';

    import PageSectionTextEditor from '@/components/Page/PageSection/Widget/PageSectionTextEditor.vue';
    import { ref, onMounted } from 'vue';

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