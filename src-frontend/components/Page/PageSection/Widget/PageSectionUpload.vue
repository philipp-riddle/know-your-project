<template>
    <div class="card">
        <div
            class="card-body"
            @dragover="isDraggingOver = true"
            @dragleave="isDraggingOver = false"
            @dragstop="drop"
        >
            <div v-if="isDraggingOver">
                <p>Release to drop files here.</p>
            </div>
            <div v-else>
                <input
                    type="file"
                    multiple
                    name="file"
                    id="fileInput"
                    class="hidden-input"
                    @change="onUploadChange"
                    ref="file"
                    accept=".pdf,.jpg,.jpeg,.png"
                />

                <label for="fileInput" class="file-label d-flex flex-column justify-content-center align-items-center">
                    <div v-if="isDraggingOver">
                        Release to drop files here.
                    </div>
                    <div v-else-if="files.length == 0">
                        Click here to upload
                    </div>
                </label>
            </div>
            

            <div v-if="files.length > 0" class="row">
                <div v-for="file in files" :key="file.name" class="col-sm-12 col-md-6 col-xl-4 ps-2 pe-2">
                    {{ file.name }}
                </div>
            </div>
        </div>
  </div>
</template>

<script setup>
    import { ref, onMounted, computed } from 'vue';
    import { useDropZone } from '@vueuse/core';
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
    const urlInput = ref(null);
    const isValidUrl = ref(null);
    const isDraggingOver = ref(false);
    const files = ref([]);
    

    const onUploadChange = (event) => {
        const file = event.target.files[0];

        files.value.push(file);
        isDraggingOver.value = false;
        props.onPageSectionSubmit(file);
    };
    const drop = (event) => {
        console.log(drop);
        event.preventDefault();
        // isDragging.value = false;
        const files = event.dataTransfer.files;
    };

</script>

<style scoped>
    input[type='file'] {
        color: rgba(0, 0, 0, 0);
        opacity: 0;
        overflow: hidden;
        position: absolute;
        width: 1px;
        height: 1px;
    }
</style>
