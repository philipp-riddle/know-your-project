<template>
    <div v-if="isImage" class="img-container d-flex flex-column gap-2">
        <img
            :src="pageSection.pageSectionUpload.file.publicFilePath"
            :alt="pageSection.pageSectionUpload.file.name"
        />
        <p class="m-0 text-muted">{{ pageSection.pageSectionUpload.file.name }}</p>
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

<style scoped>
    .img-container {
        max-height: 40%;
    }

    img {
        max-height: 25rem;
        /*  keep aspect ratio */
        object-fit: contain;
        border-radius: 2rem;
    }

    img:hover {
        cursor: pointer;
    }
</style>
