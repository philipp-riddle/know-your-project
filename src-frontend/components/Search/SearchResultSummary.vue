<template>
    <p class="m-0"><span v-html="markedText"></span></p>
</template>
<script setup>
    import { computed } from 'vue';
    import { useTextMarker } from '@/composables/TextMarker.js';

    const props = defineProps({
        result: {
            type: Object,
            required: true
        },
        searchTerm: {
            type: String,
            required: false,
        }
    });
    const maxTextLength = 90;
    const textMarker = useTextMarker();

    const markedText = computed(() => {
        const textForEmbedding = props.result.result.textForEmbedding;

        return textMarker.generateTextMarkerHtml(props.searchTerm, textForEmbedding, maxTextLength);
    });
</script>