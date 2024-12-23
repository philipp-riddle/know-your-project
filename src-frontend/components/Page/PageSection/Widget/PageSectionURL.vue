<template>
    <div class="row">
        <div class="col-sm-12 col-lg-4 card">
            <div class="card-body">
                <small class="text-muted"><strong>URL</strong></small>
                <input
                    class="form-control magic-input"
                    type="text"
                    ref="urlInput"
                    placeholder="Enter URL"
                    v-tooltip="'Enter the URL of the website you want to link to.'"
                    @keyup="onURLKeyup"
                    @keyup.enter="onURLSubmit"
                />
                <div v-if="isValidUrl !== null">
                    <div v-if="!isValidUrl" class="alert alert-danger" role="alert">
                        Invalid URL
                    </div>
                    <div v-else>
                        <span class="text-success">Valid URL</span>
                    </div>
                </div>
            </div>
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

        if (isValidUrl.value === false) {
            return;
        }

        props.onPageSectionSubmit({
            pageSectionURL: {
                url: urlInput.value.value,
            },
        });
    };
</script>