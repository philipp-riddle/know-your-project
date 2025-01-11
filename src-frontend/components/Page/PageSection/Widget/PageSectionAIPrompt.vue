<template>
    <div class="section-card card" @click.stop="(e) => e.stopPropagation()">
        <div class="p-3 card-header d-flex flex-row gap-5 justify-content-between align-items-start">
            <div v-if="isPromptLoading">
                <p class="m-0" v-html="currentText"></p>
            </div>
            <TextEditor
                v-else
                :text="currentText"
                :tooltip="tooltip"
                :focus="!isPromptLoading"
                @onChange="currentText = $event"
                placeholder="e.g. Summarize the page"
            />
            
            <button
                v-if="!isRegenerate || oldText !== currentText"
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
                <span>{{ isRegenerate ? 'Regenerate' : 'Generate'}}</span>
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
                    <div v-if="isPromptLoading" class="spinner-border spinner-border-sm text-white" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <font-awesome-icon
                        v-else
                        :icon="['fas', 'arrows-rotate']"
                    />
                </button>
            </div>
        </div>
        <div class="card-body" v-if="pageSection.aiPrompt.responseText && pageSection.aiPrompt.responseText != ''">
            <TextEditor
                :text="pageSection.aiPrompt.responseText"
                @onChange="pageSection.aiPrompt.responseText = $event"
            />
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, watch } from 'vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
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
    const pageSection = ref(props.pageSection);
    const isPromptLoading = ref(false);
    const pageSectionStore = usePageSectionStore();
    const oldText = ref(props.pageSection.aiPrompt.prompt); // save the prompt text before the user changes it - this is used to determine whether the user has changed the prompt text and change the UI accordingly
    const currentText = ref(props.pageSection.aiPrompt.prompt);

    const tooltip = computed(() => {
        return props.pageSection.aiPrompt.prompt === '' ? 'Set prompt' : 'Edit prompt';
    });

    /**
     * We can determine whether it is a regenerate or a new prompt by checking if the response text is not empty.
     * We change some UI elements based on this flag.
     */
    const isRegenerate = computed(() => {
        if (!pageSection.value) {
            return false;
        }

        return pageSection.value.aiPrompt.responseText !== '' && props.pageSection.aiPrompt.responseText !== null
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
            oldText.value = currentText.value;
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