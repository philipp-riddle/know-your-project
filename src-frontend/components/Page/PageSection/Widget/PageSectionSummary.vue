<template>
    <div class="card section-card section-card-small">
        <div class="card-body d-flex flex-row justify-content-between gap-3 align-items-top">
            <span v-html="pageSection.pageSectionSummary.prompt.responseText"></span>
            <button
                class="btn btn-sm m-0 p-0 d-flex flex-row gap-3 align-items-top"
                :disabled="!canSubmit"
                @click="onSummaryClickRefresh"
            >
                <div v-if="isPromptLoading" class="spinner-border spinner-border-sm text-white" role="status" v-tooltip="'Loading response...'">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <font-awesome-icon
                    v-else
                    v-tooltip="'Refresh summary'"
                    :icon="['fas', 'arrows-rotate']"
                />
            </button>
        </div>
    </div>
</template>

<script setup>
    import PageSectionTextEditor from '@/components/Page/PageSection/Widget/PageSectionTextEditor.vue';
    import { computed, ref, onMounted } from 'vue';

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
    const pageSection = ref(props.pageSection);
    const isPromptLoading = ref(false);

    const canSubmit = computed(() => {
        return !isPromptLoading.value; // @todo currently only when prompt is not loading - later on only after a certain cooldown to avoid spamming
    });

    const onSummaryClickRefresh = () => {
        props.onPageSectionSubmit({
            pageSectionSummary: {
                prompt: {
                    promptText: ''
                },
            },
        }).then(() => {
            isPromptLoading.value = false;
        }).catch(() => {
            isPromptLoading.value = false;
        });
    };
</script>