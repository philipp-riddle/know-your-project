<template>
    <div class="p-5">
        <div v-if="pageStore.selectedPage">
            <Page :page="pageStore.selectedPage" :showPageTitle="true" />
        </div>
        <div v-else>
            <p>Please select or create a note to start.</p>
        </div>
    </div>
</template>
<script setup>
    import { reactive, computed, ref, onMounted, watch } from 'vue';
    import { useRoute } from 'vue-router';
    import Page from '@/components/Page/Page.vue';
    import  { usePageStore } from '@/stores/PageStore.js';

    const currentRoute = useRoute();
    const id = currentRoute.params.id;
    const pageStore = usePageStore();

        
    onMounted(() => {
        if (!pageStore.selectedPage) { // this case is when the user refreshes the page and enters via route; load the requested page!
            const pageRoute = pageStore.getPage(id).then((page) => {
                pageStore.setSelectedPage(page);
            });
        }
    });
</script>