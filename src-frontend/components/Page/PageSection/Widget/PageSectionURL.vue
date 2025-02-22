<template>
    <div class="card section-card section-card-small w-100">
        <div class="card-body">
            <h5><strong>URL</strong></h5>
            <div class="d-flex flex-row gap-3">
                <input
                    class="flex-fill form-control magic-input"
                    type="text"
                    ref="urlInput"
                    placeholder="Enter URL"
                    v-tooltip="'Enter the URL of the website you want to link to.'"
                    @keyup="onURLKeyup"
                    @keyup.enter="onURLSubmit"
                />
                <button
                    class="btn btn-dark"
                    @click="onURLSubmit"
                    v-tooltip="'Save URL'"
                    :disabled="!isValidUrl"
                >
                    <font-awesome-icon :icon="['fas', 'save']" />
                </button>
            </div>
            <p v-if="false === isValidUrl" class="m-0 text-danger">Invalid URL</p>
        </div>
    </div> 
</template>

<script setup>
    import { ref, onMounted } from 'vue';

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
    const urlInput = ref(null);
    const isValidUrl = ref(null);

    onMounted(() => {
        if (props.pageSection !== undefined) {
            urlInput.value.value = props.pageSection.pageSectionURL.url;
        }
    });

    const onURLKeyup = () => {
        try {
            new URL(urlInput.value.value);
            isValidUrl.value = true;
        } catch (e) {
            isValidUrl.value = false;
        }
    };

    const onURLSubmit = (text) => {
        onURLKeyup();

        if (!isValidUrl.value) {
            return;
        }

        props.onPageSectionSubmit({
            pageSectionURL: {
                url: urlInput.value.value,
            },
        });
    };
</script>