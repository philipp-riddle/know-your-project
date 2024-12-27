<template>
    <div class="p-5">
        <div v-if="pageStore.isLoadingPage">
            <p>Loading....</p>
        </div>
        <div v-else-if="pageStore.selectedPage">
            <Page :page="pageStore.selectedPage" :showPageTitle="true" />
        </div>
        <div v-else>
            <p>Could not load given page.</p>
        </div>
    </div>
</template>
<script setup>
    import { onMounted } from 'vue';
    import { useRoute } from 'vue-router';
    import Page from '@/components/Page/Page.vue';
    import  { usePageStore } from '@/stores/PageStore.js';

    const currentRoute = useRoute();
    const id = currentRoute.params.id;
    const pageStore = usePageStore();
        
    onMounted(() => {
        // this case is when the user refreshes the page and enters via route; load the requested page.
        // we need to make sure to not load anything here if we already load a page in the store; otherwise we'd overwrite this loaded page again
        if (!pageStore.selectedPage && !pageStore.isLoadingPage) {
            pageStore.isLoadingPage = true;
            const pageRoute = pageStore.getPage(id).then((page) => {
                pageStore.setSelectedPage(page);
                pageStore.isLoadingPage = false;
            });
        }
    });
</script>