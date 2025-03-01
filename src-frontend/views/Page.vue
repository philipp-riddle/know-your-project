<template>
    <div v-if="pageStore.isLoadingPage">
        <div class="spinner-border mt-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <div v-else-if="pageStore.selectedPage">
        <Page :page="pageStore.selectedPage" />
    </div>
    <div v-else>
        <p>Could not load given page.</p>
    </div>
</template>
<script setup>
    import { onMounted } from 'vue';
    import { useRoute } from 'vue-router';
    import Page from '@/components/Page/Page.vue';
    import  { usePageStore } from '@/stores/PageStore.js';
    import  { useTagStore } from '@/stores/TagStore.js';

    const currentRoute = useRoute();
    const pageStore = usePageStore();
    const tagStore = useTagStore();
        
    onMounted(() => {
        // this case is when the user refreshes the page and enters via route; load the requested page.
        // we need to make sure to not load anything here if we already load a page in the store; otherwise we'd overwrite this loaded page again
        if (!pageStore.selectedPage && !pageStore.isLoadingPage) {
            pageStore.isLoadingPage = true;
            const pageRoute = pageStore.getPage(currentRoute.params.id).then((page) => {
                pageStore.setSelectedPage(page);
                pageStore.isLoadingPage = false;

                // now we need to open the tag in the navigation - this requires some more logic if the page is nested in the tree.
                tagStore.openTagNavigationTreeForPage(page);
            });
        }
    });
</script>