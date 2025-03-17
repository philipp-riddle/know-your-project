<template>
    <div v-if="editor" class="d-flex flex-row align-items-center justify-content-center gap-2">
        <VDropdown>
            <ul class="nav nav-pills d-flex flex-row justify-content-center align-items-center gap-1">
                <li class="nav-item">
                    <button class="nav-link btn btn-sm inactive text-editor-control">{{ currentTextTag }}</button>
                </li>
            </ul>

            <template #popper>
                <ul class="p-2 nav nav-pills d-flex flex-column justify-content-center align-items-center gap-1">
                    <li class="nav-item">
                        <button
                            @click.stop="editor.chain().focus().setParagraph().run()"
                            editor-cmd="paragraph"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('paragraph'), 'inactive': !editor.isActive('paragraph') }"
                            v-tooltip="editor.isActive('paragraph') ? 'Paragraph' : 'Change to paragraph'"
                        >
                            P
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            @click.stop="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                            editor-cmd="paragraph"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('heading', { level: 1 }), 'inactive': !editor.isActive('heading', { level: 1 }) }"
                            v-tooltip="editor.isActive('heading', { level: 1 }) ? 'Heading 1' : 'Change to heading 1'"
                        >
                            H1
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            @click.stop="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                            editor-cmd="paragraph"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('heading', { level: 2 }), 'inactive': !editor.isActive('heading', { level: 2 }) }"
                            v-tooltip="editor.isActive('heading', { level: 2 }) ? 'Heading 2' : 'Change to heading 2'"
                        >
                            H2
                        </button>
                    </li>
                    <li class="nav-item d-md-none">
                        <button
                            @click.stop="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                            editor-cmd="paragraph"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('heading', { level: 3 }), 'inactive': !editor.isActive('heading', { level: 3 }) }"
                            v-tooltip="editor.isActive('heading', { level: 3 }) ? 'Heading 3' : 'Change to heading 3'"
                        >
                            H3
                        </button>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <button
                            @click.stop="editor.chain().focus().toggleHeading({ level: 4 }).run()"
                            editor-cmd="paragraph"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('heading', { level: 4 }), 'inactive': !editor.isActive('heading', { level: 4 }) }"
                            v-tooltip="editor.isActive('heading', { level: 4 }) ? 'Heading 4' : 'Change to heading 4'"
                        >
                            H4
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            @click.stop="editor.chain().focus().toggleBulletList().run()"
                            editor-cmd="bullet-list"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('bulletList'), 'inactive': !editor.isActive('bulletList') }"
                            v-tooltip="editor.isActive('bulletList') ? 'Bullet list' : 'Toggle bullet list'"
                        >
                            <font-awesome-icon :icon="['fas', 'list']" />
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            @click.stop="editor.chain().focus().toggleOrderedList().run()"
                            editor-cmd="ordered-list"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('orderedList'), 'inactive': !editor.isActive('orderedList') }"
                            v-tooltip="editor.isActive('orderedList') ? 'Ordered list' : 'Toggle ordered list'"
                        >
                            <font-awesome-icon :icon="['fas', 'list-ol']" />
                        </button>
                    </li>
                </ul>
            </template>
        </VDropdown>

        <VDropdown>
            <ul class="nav nav-pills d-flex flex-row justify-content-center align-items-center gap-1">
                <li class="nav-item">
                    <button class="nav-link btn btn-sm inactive text-editor-control">Text style</button>
                </li>
            </ul>

            <template #popper>
                <ul class="p-2 nav nav-pills d-flex flex-column justify-content-center align-items-center gap-1">
                    <li class="nav-item">
                        <button
                            @click.stop="editor.chain().focus().toggleBold().run()"
                            :disabled="!editor.can().chain().focus().toggleBold().run()"
                            editor-cmd="paragraph"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('bold'), 'inactive': !editor.isActive('bold') }"
                            v-tooltip="editor.isActive('bold') ? 'Bold' : 'Toggle bold'"
                        >
                            <font-awesome-icon :icon="['fas', 'bold']" />
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            @click.stop="editor.chain().focus().toggleItalic().run()"
                            :disabled="!editor.can().chain().focus().toggleItalic().run()"
                            editor-cmd="italic"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('italic'), 'inactive': !editor.isActive('italic') }"
                            v-tooltip="editor.isActive('italic') ? 'Italic' : 'Toggle italic'"
                        >
                            <font-awesome-icon :icon="['fas', 'italic']" />
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            @click.stop.stop="editor.chain().focus().toggleStrike().run()"
                            :disabled="!editor.can().chain().focus().toggleStrike().run()"
                            editor-cmd="strike"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('strike'), 'inactive': !editor.isActive('strike') }"
                            v-tooltip="editor.isActive('strike') ? 'Strike' : 'Toggle strike'"
                        >
                            <font-awesome-icon :icon="['fas', 'strikethrough']" />
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            @click.stop="editor.chain().focus().toggleCode().run()"
                            :disabled="!editor.can().chain().focus().toggleCode().run()"
                            editor-cmd="code"
                            class="nav-link btn btn-sm"
                            :class="{ 'active': editor.isActive('code'), 'inactive': !editor.isActive('code') }"
                            v-tooltip="editor.isActive('code') ? 'Code' : 'Toggle code'"
                        >
                            <font-awesome-icon :icon="['fas', 'code']" />
                        </button>
                    </li>
                </ul>
            </template>
        </VDropdown>
    </div>
</template>

<script setup>
    import { computed, ref, watch, onMounted } from 'vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';

    const pageSectionStore = usePageSectionStore();
    const editor = ref(null);

    watch(() => pageSectionStore.selectedPageSectionTextEditor, (newValue) => {
        editor.value = newValue; // sync the editor with the global state
    }, { deep: true });

    const currentTextTag = computed(() => {
        if (editor.value.isActive('heading', { level: 1 })) {
            return 'H1';
        } else if (editor.value.isActive('heading', { level: 2 })) {
            return 'H2';
        } else if (editor.value.isActive('heading', { level: 3 })) {
            return 'H3';
        } else if (editor.value.isActive('heading', { level: 4 })) {
            return 'H4';
        } else if (editor.value.isActive('bulletList')) {
            return 'UL';
        } else if (editor.value.isActive('orderedList')) {
            return 'OL';
        } else {
            return 'P';
        }
    });
</script>