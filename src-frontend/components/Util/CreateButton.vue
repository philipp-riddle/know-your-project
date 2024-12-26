<template>
    <button
        class="btn m-0 p-0"
        @click="onClick()"
        v-tooltip="tooltip"
    >
        <strong class="h4">+</strong>
    </button>
</template>

<script setup>
    import { defineProps } from 'vue';

    const props = defineProps({
        onClick: {
            type: Function,
            required: true,
        },
        tooltip: {
            type: String,
            required: false,
            default: 'Create',
        },
    });
</script>