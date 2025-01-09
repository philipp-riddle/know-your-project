<template>
    <div v-if="pageStore.selectedPage?.task" class="mb-3">
        <Page :page="page" />
    </div>
</template>

<script setup>
    import { usePageStore } from '@/stores/PageStore.js';
    import Page from '@/components/Page/Page.vue';

    const pageStore = usePageStore();
    const props = defineProps({
        task: {
            type: Object,
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
    });
</script>