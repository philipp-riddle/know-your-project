<template>
    <div class="d-flex gap-3 flex-row mb-2" v-if="editor && pageSectionStore.selectedPageSection == pageSection.id && !pageSectionStore.isDraggingPageSection">
        <div class="card">
            <div class="card-body p-0 d-flex flex-row gap-1">
                <button
                    @click="editor.chain().focus().setParagraph().run()"
                    editor-cmd="paragraph"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('paragraph') }"
                    v-tooltip="editor.isActive('paragraph') ? 'Paragraph' : 'Change to paragraph'"
                >
                    P
                </button>
                <button
                    @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                    editor-cmd="h1"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('heading', { level: 1 }) }"
                    v-tooltip="editor.isActive('heading', { level: 1 }) ? 'Heading 1' : 'Change to heading 1'"
                >
                    H1
                </button>
                <button
                    @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    editor-cmd="h2"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('heading', { level: 2 }) }"
                    v-tooltip="editor.isActive('heading', { level: 2 }) ? 'Heading 2' : 'Change to heading 2'"
                >
                    H2
                </button>
                <button
                    @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                    editor-cmd="h3"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('heading', { level: 3 }) }"
                    v-tooltip="editor.isActive('heading', { level: 3 }) ? 'Heading 3' : 'Change to heading 3'"
                >
                    H3
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0 d-flex flex-row gap-1">
                <button
                    @click.stop="editor.chain().focus().toggleBold().run()"
                    :disabled="!editor.can().chain().focus().toggleBold().run()"
                    editor-cmd="bold"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('bold') }"
                    v-tooltip="editor.isActive('bold') ? 'Bold' : 'Toggle bold'"
                >
                    <font-awesome-icon :icon="['fas', 'bold']" />
                </button>
                <button
                    @click.stop="editor.chain().focus().toggleItalic().run()"
                    :disabled="!editor.can().chain().focus().toggleItalic().run()"
                    editor-cmd="italic"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('italic') }"
                    v-tooltip="editor.isActive('italic') ? 'Italic' : 'Toggle italic'"
                >
                    <font-awesome-icon :icon="['fas', 'italic']" />
                </button>
                <button
                    @click.stop="editor.chain().focus().toggleStrike().run()"
                    :disabled="!editor.can().chain().focus().toggleStrike().run()"
                    editor-cmd="strike"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('strike') }"
                    v-tooltip="editor.isActive('strike') ? 'Strike' : 'Toggle strike'"
                >
                    
                    <font-awesome-icon :icon="['fas', 'strikethrough']" />
                </button>
                <button
                    @click="editor.chain().focus().toggleCode().run()"
                    :disabled="!editor.can().chain().focus().toggleCode().run()"
                    editor-cmd="code"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('code') }"
                    v-tooltip="editor.isActive('code') ? 'Code' : 'Toggle code'"
                >
                    <font-awesome-icon :icon="['fas', 'code']" />
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0 d-flex flex-row gap-1">
                <button
                    @click="editor.chain().focus().toggleBulletList().run()"
                    editor-cmd="bullet-list"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('bulletList') }"
                    v-tooltip="editor.isActive('bulletList') ? 'Bullet list' : 'Toggle bullet list'"
                >
                    <font-awesome-icon :icon="['fas', 'list']" />
                </button>
                <button
                    @click="editor.chain().focus().toggleOrderedList().run()"
                    editor-cmd="order-list"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('orderedList') }"
                    v-tooltip="editor.isActive('orderedList') ? 'Ordered list' : 'Toggle ordered list'"
                >
                    <font-awesome-icon :icon="['fas', 'list-ol']" />
                </button>
            </div>
        </div>
    </div>

    <!-- convert the text editor into a paragraph when the sections get rearranged - this way no text can be dropped in this editor from other editors. -->
    <div v-if="pageSectionStore.isDraggingPageSection">
        <span v-html="currentText"></span>
    </div>
    <editor-content
        v-else
        v-tooltip="tooltip"
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
    const tooltip = computed(() => {
        return !pageSectionStore.selectedPageSection == props.pageSection.id ? 'Edit text' : '';
    });
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
            editor.commands.focus('end'); // this automatically sets the focus to the end of the editor when initialized / created
        },
        onFocus: ({ editor }) => {
            pageSectionStore.selectedPageSection = props.pageSection.id;
        },
        onBlur: ({ editor, event }) => {
            // user clicks outside of the editor or on no element of the editor UI
            // we do not want to hide this editor if the user clicks on a button or similar because this would shift the layout and therefore the user would have to click twice if a button moves
            if (!event.relatedTarget) {
                pageSectionStore.selectedPageSection = null;
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