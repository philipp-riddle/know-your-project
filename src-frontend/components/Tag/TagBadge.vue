<template>
    <button
        class="btn btn-sm btn-tag"
        :style="{backgroundColor: tag.color}"
        v-tooltip="tag.name"
    >
        &nbsp;&nbsp;&nbsp;&nbsp;
    </button>
</template>

<script setup>
    const props = defineProps({
        tag: {
            type: Object,
            required: true,
        },
        tagWrapperEntity: { // e.g. tag associated with user, page, ...
            type: Object,
            required: false,
        },
    })
</script>