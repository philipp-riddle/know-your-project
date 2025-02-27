<template>

    <!-- convert the text editor into a paragraph when the sections get rearranged - this way no text can be dropped in this editor from other editors. -->
    <div v-if="pageSectionStore.isDraggingPageSection">
        <span v-html="currentText"></span>
    </div>
    <editor-content
        v-else
        :editor="editor"
    />
</template>

<script setup>
    import Placeholder from '@tiptap/extension-placeholder';
    import { useEditor, EditorContent } from '@tiptap/vue-3';
    import StarterKit from '@tiptap/starter-kit';
    import Link from '@tiptap/extension-link';
    import { ref, computed, watch } from 'vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';

    const props = defineProps({
        text: {
            type: String,
            required: true,
        },
        onTextChange: {
            type: Function,
            required: true,
        },
        pageSection: {
            type: Object,
            required: false,
        },
    });
    const currentText = ref(props.text);
    const editor = useEditor({
        content: props.text,
        extensions: [
            StarterKit,
            Placeholder.configure({
                placeholder: 'Start typing...',
                emptyEditorClass: 'is-editor-empty',
            }),
            Link,
        ],
        onUpdate: ({ editor }) => {
            currentText.value = editor.getHTML();
            props.onTextChange(currentText.value);
        },
        onCreate: ({ editor }) => {
            if (props.text === '') {
                editor.commands.focus('start'); // this automatically sets the focus to the end of the editor when initialized / created
            }
        },
        onFocus: ({ editor }) => {
            // sync it with the global store state
            // this way we can show the text editor controls elsewhere
            pageSectionStore.selectedPageSectionTextEditor = editor;
        },
        onBlur: ({ editor, event }) => {
            // user clicked on toolbar - ignore blur; instead we want to keep the editor open and refocus it
            if (event.relatedTarget?.getAttribute('editor-cmd') || event.relatedTarget?.getAttribute('id')?.includes('popper')) {
                editor.commands.focus();
            } else {
                pageSectionStore.selectedPageSectionTextEditor = null;
            }
        },
    });
    const pageSectionStore = usePageSectionStore();
</script>

<style>
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
</style>