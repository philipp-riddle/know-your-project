<template>
    <div class="section-card card w-100" @click.stop="(e) => e.stopPropagation()">
        <div class="p-3 card-header d-flex flex-row gap-5 justify-content-between align-items-start">
            <div v-if="isPromptLoading">
                <p class="m-0" v-html="currentText"></p>
            </div>
            <div v-else>
                <TextEditor
                    :text="currentText"
                    :tooltip="tooltip"
                    :focus="!isPromptLoading && currentText === ''"
                    @onChange="currentText = $event"
                    placeholder="e.g. Summarize the page"
                />
            </div>
            
            <button
                v-if="!isRegenerate"
                class="btn btn-dark d-flex flex-row gap-3 align-items-center"
                :disabled="!canSubmit"
                v-tooltip="canSubmit ? '' : 'Enter a prompt to generate'"
                @click="onPromptSubmit"
            >
                <div v-if="isPromptLoading" class="spinner-border spinner-border-sm text-white" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <font-awesome-icon
                    v-else
                    :icon="['fas', 'arrows-rotate']"
                />
                <span>Generate</span>
            </button>
            <div
                v-else
                class="d-flex flex-row gap-3 align-items-center"
            >
                <button
                    class="btn btn-sm p-0"
                    v-tooltip="'Refresh response'"
                    @click="onPromptSubmit"
                >
                    <div v-if="isPromptLoading" class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <font-awesome-icon
                        v-else
                        :icon="['fas', 'arrows-rotate']"
                    />
                </button>
            </div>
        </div>
        <div class="card-body" v-if="pageSection.aiPrompt.prompt.responseText && pageSection.aiPrompt.prompt.responseText != ''">
            <span
                v-html="promptResponseText"
                v-if="!showResponseTextEditor"
                @click="showResponseTextEditor = true"
                style="cursor: pointer;"
            ></span>
            <TextEditor
                v-else
                :text="promptResponseText"
                @onChange="pageSection.aiPrompt.prompt.responseText = $event"
                @onFocus="onFocusPromptResponse"
            />
            <p v-if="showResponseNotEditableError" class="text-danger m-0">
                <span class="bold">Note:</span> The response is not editable. Use this response only to copy contents. Alternatively, if you want to change the response, please start a thread with the assistant.
            </p>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue';
    import { useDebounceFn } from '@vueuse/core';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { useThreadStore } from '@/stores/ThreadStore.js';
    import TextEditor from '@/components/Util/TextEditor.vue';

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
    const pageSectionStore = usePageSectionStore();
    const threadStore = useThreadStore();
    const pageSection = ref(props.pageSection);
    const isPromptLoading = ref(false);
    const oldText = ref(props.pageSection.aiPrompt.prompt.promptText); // save the prompt text before the user changes it - this is used to determine whether the user has changed the prompt text and change the UI accordingly
    const currentText = ref(props.pageSection.aiPrompt.prompt.promptText);
    const showResponseTextEditor = ref(false);
    const showResponseNotEditableError = ref(false);

    // onMounted(() => {
    //     if (props.pageSection.threadContext != null) {
    //         threadStore.selectedThread = props.pageSection.threadContext.thread; // @todo always opens the thread box, for dev purposes
    //     }
    // })

    const tooltip = computed(() => {
        return null === props.pageSection.aiPrompt.prompt.promptText || props.pageSection.aiPrompt.prompt.promptText === '' ? 'Set prompt' : 'Edit prompt';
    });

    /**
     * We can determine whether it is a regenerate or a new prompt by checking if the response text is not empty.
     * We change some UI elements based on this flag.
     */
    const isRegenerate = computed(() => {
        return pageSection.value && pageSection.value.aiPrompt.prompt.responseText !== '' && pageSection.value.aiPrompt.prompt.responseText !== null;
    });

    const canSubmit = computed(() =>  {
        const text = currentText.value ?? props.pageSection.aiPrompt.prompt.promptText;

        if (!text) {
            return false;
        }

        if (isPromptLoading.value) {
            return false;
        }

        // if the prompt is not empty, we can submit
        return text.trim() !== '';
    });

    const promptResponseText = computed(() => {
        showResponseTextEditor.value = false; // switch to the HTML version again; user has to click to change it to a text editor

        // first, set the response text equals to the original prompt
        var responseText = pageSection.value.aiPrompt.prompt.responseText;

        // if the page section has a thread context, we can get a newer response text from the thread items - this will include a 'refined' response
        if (pageSection.value.threadContext !== null) {
            for (const threadItem of pageSection.value.threadContext.thread.threadItems) {
                if (threadItem.itemPrompt.prompt.responseText != null) {
                    responseText = threadItem.itemPrompt.prompt.responseText;
                }
            }
        }

        return responseText;
    });

    const onPromptSubmit = () => {
        if (!canSubmit.value) {
            return;
        }

        isPromptLoading.value = true;
        props.onPageSectionSubmit({
            aiPrompt: {
                prompt: {
                    promptText: currentText.value,
                }
            },
        }).then((updatedSection) => {
            oldText.value = currentText.value;
            pageSection.value = updatedSection;
            isPromptLoading.value = false;
        }).catch(() => {
            isPromptLoading.value = false;
        });
    };

    const debouncedErrorHide = useDebounceFn(() => {
        showResponseNotEditableError.value = false;
    }, 6000);

    const onFocusPromptResponse = () => {
        showResponseNotEditableError.value = true;
        debouncedErrorHide();
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