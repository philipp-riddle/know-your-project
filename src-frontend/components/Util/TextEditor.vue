<template>
    <editor-content
        class="m-0"
        v-tooltip="tooltip"
        :editor="editor"
        :disabled="disabled"
        :key="editorRandomKey" 
    />
</template>

<script setup>
    import Placeholder from '@tiptap/extension-placeholder';
    import { useEditor, EditorContent } from '@tiptap/vue-3';
    import StarterKit from '@tiptap/starter-kit';
    import Link from '@tiptap/extension-link';
    import BulletList from '@tiptap/extension-bullet-list'
    import { computed, watch, ref } from 'vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';

    const emit = defineEmits(['onChange', 'onFocus']);
    const props = defineProps({
        text: {
            type: String,
            required: true,
        },
        placeholder: {
            type: String,
            required: false,
            default: 'Type here...',
        },
        tooltip: {
            type: String,
            required: false,
            default: '',
        },
        focus: {
            type: Boolean,
            required: false,
            default: false,
        },
        editable: {
            type: Boolean,
            required: false,
            default: true,
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        },
    });
    const pageSectionStore = usePageSectionStore();
    const currentText = computed(() => props.text);
    const editorRandomKey = ref(Math.random()); // this is to force the editor to re-render when the text changes; otherwise it is set into stone :/

    const editor = useEditor({
        content: currentText.value,
        editable: props.editable,
        extensions: [
            StarterKit, // add starter kit; otherwise the editor cannot render due to missing schemas
            Placeholder.configure({
                placeholder: props.placeholder ,
                emptyEditorClass: 'is-editor-empty',
            }),
            Link,
        ],
        onUpdate: ({ editor }) => {
            emit('onChange', editor.getHTML());
        },
        onCreate: ({ editor }) => {
            if (props.focus) {
                editor.commands.focus();
            }
        },
        onFocus: ({ editor }) => {
            emit('onFocus');
        },
    });

    watch (() => props.text, (newValue) => {
        if (newValue === '') {
            // if any component outside of this text editor sets the text to an empty string, we need to reset the editor and re-render it by setting a new key
            editor.value?.commands.setContent(newValue);
            editorRandomKey.value = Math.random();

            if (props.focus) {
                editor.value?.commands.focus();
            }
        }
    });

    watch (() => props.focus, (newValue) => {
        if (newValue) {
            editor.value?.commands.focus();
        }
    });
</script>


<style lang="scss" scoped>
    .tiptap p.is-editor-empty:first-child::before {
        color: #adb5bd;
        content: attr(data-placeholder);
        float: left;
        height: 0;
        pointer-events: none;
    }

    :focus-visible {
        outline: none !important;
    }

    // this is to ensure that the text box does not have any natural margin
    p {
        margin: 0 !important;
    }

    .card-body > p {
        // override the text sizes in the response box to make them smaller but have them in the correct size when transferring them to a text component or something similar

        & > h1 {
            font-size: 1.5rem !important;
        }

        & > h2 {
            font-size: 1.25rem !important;
        }

        & > h3 {
            font-size: 1rem !important;
        }

        & > h4, h5, h6 {
            font-size: 0.8rem !important;
        }

        & > p, & > li {
            font-size: 0.6rem !important;
        }
    }
</style>