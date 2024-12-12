<template>
    <div>
        <TextArea
            placeholder="Start typing"
            :onTextSubmit="onTextSubmit"
            :onTextChange="onTextChange"
            :text="pageSection?.pageSectionText?.content ?? ''"
            :resetOnSubmit="pageSection == null"
        />
    </div>
</template>

<script setup>
    import PageSectionChecklistItem from '@/components/Page/PageSectionChecklistItem.vue';
    import TextArea from '@/components/Util/TextArea.vue';
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

    const onTextChange = (text) => {
        if (props.pageSection?.id) {
            onTextSubmit(text); // if the pageSection already exists we can submit the text; otherwise we only want to submit if the user presses 'enter'
        }
    };

    const onTextSubmit = (text) => {
        props.onPageSectionSubmit({
            pageSectionText: {
                content: text,
            },
        });
    };
</script>