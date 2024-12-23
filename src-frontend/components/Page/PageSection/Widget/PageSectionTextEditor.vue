<template>
    <div class="d-flex gap-3 flex-row mb-2">
        <div class="card">
            <div class="card-body p-0 d-flex flex-row gap-1" v-if="editor && isFocussed">
                <button
                    @click="editor.chain().focus().setParagraph().run()"
                    editor-cmd="paragraph"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('paragraph') }"
                >
                    P
                </button>
                <button
                    @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                    editor-cmd="h1"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('heading', { level: 1 }) }"
                >
                    H1
                </button>
                <button
                    @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    editor-cmd="h2"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('heading', { level: 2 }) }"
                >
                    H2
                </button>
                <button
                    @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                    editor-cmd="h3"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('heading', { level: 3 }) }"
                >
                    H3
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0 d-flex flex-row gap-1" v-if="editor && isFocussed">
                <button
                    @click.stop="editor.chain().focus().toggleBold().run()"
                    :disabled="!editor.can().chain().focus().toggleBold().run()"
                    editor-cmd="bold"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('bold') }"
                >
                    <font-awesome-icon :icon="['fas', 'bold']" />
                </button>
                <button
                    @click.stop="editor.chain().focus().toggleItalic().run()"
                    :disabled="!editor.can().chain().focus().toggleItalic().run()"
                    editor-cmd="italic"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('italic') }"
                >
                    <font-awesome-icon :icon="['fas', 'italic']" />
                </button>
                <button
                    @click.stop="editor.chain().focus().toggleStrike().run()"
                    :disabled="!editor.can().chain().focus().toggleStrike().run()"
                    editor-cmd="strike"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('strike') }"
                >
                    
                    <font-awesome-icon :icon="['fas', 'strikethrough']" />
                </button>
                <button
                    @click="editor.chain().focus().toggleCode().run()"
                    :disabled="!editor.can().chain().focus().toggleCode().run()"
                    editor-cmd="code"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('code') }"
                >
                    <font-awesome-icon :icon="['fas', 'code']" />
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0 d-flex flex-row gap-1" v-if="editor && isFocussed">
                <button
                    @click="editor.chain().focus().toggleBulletList().run()"
                    editor-cmd="bullet-list"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('bulletList') }"
                >
                    <font-awesome-icon :icon="['fas', 'list']" />
                </button>
                <button
                    @click="editor.chain().focus().toggleOrderedList().run()"
                    editor-cmd="order-list"
                    class="btn btn-sm"
                    :class="{ 'btn-dark': editor.isActive('orderedList') }"
                >
                    <font-awesome-icon :icon="['fas', 'list-ol']" />
                </button>
            </div>
        </div>
    </div>
    <editor-content :editor="editor" />
</template>

<script setup>
    import Placeholder from '@tiptap/extension-placeholder';
    import { useEditor, EditorContent } from '@tiptap/vue-3';
    import StarterKit from '@tiptap/starter-kit';
    import Paragraph from '@tiptap/extension-paragraph';
    import Heading from '@tiptap/extension-heading';
    import Link from '@tiptap/extension-link';
    import { defineProps, ref } from 'vue';

    const props = defineProps({
        text: {
            type: String,
            required: true,
        },
        onTextChange: {
            type: Function,
            required: true,
        },
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
            props.onTextChange(editor.getHTML());
        },
        onCreate: ({ editor }) => {
            editor.commands.focus('end');
        },
        onFocus: ({ editor }) => {
            isFocussed.value = true;
        },
        onBlur: ({ editor, event }) => {
            if (!event.relatedTarget || !event.relatedTarget.getAttribute('editor-cmd')) { // user clicks outside of the editor or on no element of the editor UI
                isFocussed.value = false;
            }
        },
    });
    const isFocussed = ref(false);
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