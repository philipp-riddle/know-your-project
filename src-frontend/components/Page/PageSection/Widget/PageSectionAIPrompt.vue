<template>
    <div class="card" style="max-width: 70%; text-wrap: wrap !important;" @click.stop="(e) => e.stopPropagation()">
        <div class="p-3 card-header d-flex flex-row justify-content-between align-items-start">
            <div class="d-flex flex-column gap-2">
                <div class="d-flex flex-row gap-3 align-items-start">
                    <button class="btn btn-sm" v-tooltip="'This is your instruction to the assistant - e.g. summarize the page'">
                        <font-awesome-icon :icon="['fas', 'question']" />
                    </button>
                    <h4 class="m-0">Ask prompt</h4>
                </div>
                <div v-if="isPromptLoading">
                    <p class="m-0" v-html="currentText"></p>
                </div>
                <editor-content
                    v-else
                    class="m-0"
                    v-tooltip="tooltip"
                    :editor="editor"
                />
            </div>

            <button
                class="btn btn-dark d-flex flex-row gap-3 align-items-center"
                :disabled="!canSubmit"
                v-tooltip="canSubmit ? '' : 'Enter a prompt to generate'"
                @click="onPromptSubmit"
            >
                <div v-if="isPromptLoading" class="spinner-border text-white" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <font-awesome-icon
                    v-else
                    :icon="['fas', isRegenerate ? 'arrows-rotate' : 'microchip']"
                />
                <span>{{ isRegenerate ? 'Regenerate' : 'Generate'}}</span>
            </button>
        </div>
        <div class="card-body" v-if="pageSection.aiPrompt.responseText && pageSection.aiPrompt.responseText != ''">
            <p class="m-0"v-html="pageSection.aiPrompt.responseText"></p>
        </div>
    </div>
</template>

<script setup>
    import Placeholder from '@tiptap/extension-placeholder';
    import { useEditor, EditorContent } from '@tiptap/vue-3';
    import StarterKit from '@tiptap/starter-kit';
    import Link from '@tiptap/extension-link';
    import { ref, computed, watch } from 'vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';

    const props = defineProps({
        pageSection: {
            type: Object,
            required: false,
        },
        onPageSectionSubmit: {
            type: Function,
            required: true,
        },
    });
    /**
     * We can determine whether it is a regenerate or a new prompt by checking if the response text is not empty.
     * We change some UI elements based on this flag.
     */
    const isRegenerate = props.pageSection.aiPrompt.responseText !== '' && props.pageSection.aiPrompt.responseText !== null;
    const pageSection = ref(props.pageSection);
    const isPromptLoading = ref(false);
    const pageSectionStore = usePageSectionStore();
    const currentText = ref(props.pageSection.aiPrompt.prompt);
    const tooltip = computed(() => {
        return props.pageSection.aiPrompt.prompt === '' ? 'Set prompt' : 'Edit prompt';
    });
    const editor = useEditor({
        content: currentText.value,
        extensions: [
            StarterKit, // add starter kit; otherwise the editor cannot render due to missing schemas
            Placeholder.configure({
                placeholder: 'e.g. "Summarize the page"',
                emptyEditorClass: 'is-editor-empty',
            }),
            Link,
        ],
        onUpdate: ({ editor }) => {
            currentText.value = editor.getHTML();
        },
        onKeydown: ({ editor, event }) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                onPromptSubmit();
            }
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

    const canSubmit = computed(() =>  {
        const text = currentText.value ?? props.pageSection.aiPrompt.prompt;

        if (!text) {
            return false;
        }

        if (isPromptLoading.value) {
            return false;
        }

        // if the prompt is not empty, we can submit
        return text.trim() !== '';
    });

    const onPromptSubmit = () => {
        if (!canSubmit.value) {
            return;
        }

        isPromptLoading.value = true;
        props.onPageSectionSubmit({
            aiPrompt: {
                prompt: currentText.value,
            },
        }).then((updatedSection) => {
            pageSection.value = updatedSection;
            isPromptLoading.value = false;
        });
    };
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

    .card-header {
        // this is to ensure that the text box does not have any natural margin
        p {
            margin: 0! important;
        }
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