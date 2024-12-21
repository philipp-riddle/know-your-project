<template>
    <div class="p-5">
        <div v-if="pageStore.selectedPage">
            <div class="row">
                <div class="col-sm-12 offset-md-1 col-md-11">
                    <h1 class="m-0"><input class="magic-input" v-model="pageStore.selectedPage.name" @keyup="updatePageTitle" /></h1>
                </div>
            </div>

            <Page :page="pageStore.selectedPage" />
        </div>
        <div v-else>
            <p>Please select or create a note to start.</p>
        </div>
    </div>
</template>
<script setup>
import { reactive, computed, ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useDebounceFn } from '@vueuse/core';
import Page from '@/components/Page/Page.vue';
import  { usePageStore } from '@/stores/PageStore.js';

const currentRoute = useRoute();
const id = currentRoute.params.id;
const pageStore = usePageStore();
const debouncedPageTitleUpdate = useDebounceFn(async () => {
    await pageStore.updatePage({
        id: pageStore.selectedPage.id,
        name: pageStore.selectedPage.name,
    });
}, 500);
    
onMounted(() => {
    if (!pageStore.selectedPage) { // this case is when the user refreshes the page and enters via route; load the requested page!
        const pageRoute = pageStore.getPage(id).then((page) => {
            pageStore.setSelectedPage(page);
        });
    }
});

const updatePageTitle = async () => {
    await debouncedPageTitleUpdate();
}

</script>