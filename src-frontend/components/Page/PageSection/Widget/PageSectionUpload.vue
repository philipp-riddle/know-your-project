<template>
    <div v-if="isImage" class="d-flex flex-row">
        <div class="img-container d-flex flex-column">
            <div class="overlay d-flex flex-row justify-content-between align-items-center w-100">
                <p class="m-0 white">{{ pageSection.pageSectionUpload.file.name }}</p>
                <a class="btn btn-sm btn-light m-0" v-tooltip="'Download file'" :href="'/api/file/download/' + pageSection.pageSectionUpload.file.id">
                    <font-awesome-icon :icon="['fas', 'download']" />
                </a>
            </div>
            <img
                :src="pageSection.pageSectionUpload.file.publicFilePath"
                :alt="pageSection.pageSectionUpload.file.name"
            />
        </div>
    </div>
    <div v-else class="card section-card section-card-small w-100">
        <div class="card-body p-4 d-flex flex-row justify-content-between align-items-center">
            <p class="m-0">{{ pageSection.pageSectionUpload.file.name }}</p>
            <div>
                <a class="btn m-0 p-0" v-tooltip="'Download file'" :href="'/api/file/download/' + pageSection.pageSectionUpload.file.id">
                    <font-awesome-icon :icon="['fas', 'download']" />
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed, ref } from 'vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';

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
    const pageSectionStore = usePageSectionStore();

    const isImage = computed(() => {
        return props.pageSection.pageSectionUpload.file.mimeType.startsWith('image');
    });
</script>