<template>
    <textarea
        ref="textAreaElement"
        @keyup="onTextareaChange"
        @keyup.enter="onTextareaSubmit"
        @blur="onFocusExit"
        :readonly="!isEditable"
        :value="props.text"
        :rows="minimumRows"
        :placeholder="props.placeholder"
        ></textarea>
</template>

<script setup>
    // this text area is expandable and can disappear when not in focus
    import { useTaskProvider } from '@/providers/TaskProvider.js';
    import { onMounted, ref, computed } from 'vue';
    import { useDebounceFn } from '@vueuse/core';

    const props = defineProps({
        text: {
            type: String,
            required: false, // if it is not provided it is an empty text
        },
        onTextChange: {
            type: Function,
            required: false,
        },
        onTextSubmit: {
            type: Function,
            required: false,
        },
        resetOnSubmit: {
            type: Boolean,
            required: false,
            default: false,
        },
        focus: {
            type: Boolean,
            required: false,
            default: false,
        },
        onFocusExit: {
            type: Function,
            required: false,
        },
        placeholder: {
            type: String,
            required: false,
            default: 'Put in text here....',
        },
        isEditable: {
            type: Boolean,
            required: false,
            default: true,
        }
    });
    const text = props.text ?? '';
    const textAreaElement = ref(text);
    const originalText = ref(text);
    const minimumRows = text.split('\n').length;
    const debouncedTextUpdate = useDebounceFn(() => {
        if (props.onTextChange) {
            props.onTextChange(textAreaElement.value.value);
        }

        originalText.value = textAreaElement.value.value;
    }, 1000);

    onMounted(() => {
        resizeTextarea();
    });

    const onTextareaChange = async () => {
        resizeTextarea();
        const newText = textAreaElement.value.value.trim();

        if (originalText.value !== newText && newText !== '') {
            await debouncedTextUpdate();
        }
    };

    const onTextareaSubmit = () => {
        if (props.onTextSubmit && textAreaElement.value.value.trim() !== '') {
            props.onTextSubmit(textAreaElement.value.value.trim());
        }

        if (props.resetOnSubmit) {
            textAreaElement.value.value = '';
            originalText.value = '';
        }
    };

    const resizeTextarea = () => {
        textAreaElement.value.style.height = 'auto';
        const scrollHeight = textAreaElement.value.scrollHeight;

        if (scrollHeight < 50) {
            // textAreaElement.value.style.height = '50px';
        } else {
            textAreaElement.value.style.minHeight = textAreaElement.value.scrollHeight + 'px';
        }
    };
</script>

<style scoped lang="sass">
    @import '@/styles/colors.scss';

    textarea, input {
        width: 100%;
        border-style: none; 
        border-color: Transparent;
        border-radius: 10px;
        background-color: Transparent;
        overflow: auto !important;      
        cursor: pointer;
    }

    textarea:focus {
        cursor: text;
    }
</style>