<template>
    <div class="container-fluid p-0 m-0 h-100 w-100">
        <div class="row h-100 p-0 m-0">
            <div class="col-sm-12 d-flex flex-column gap-4">
                <Navigation />

                <router-view></router-view>
            </div>
        </div>
    </div>

    <ThreadBox />
    <SearchModal />
</template>

<script setup>
    import { watch,reactive, computed } from 'vue';
    import { useRoute } from 'vue-router';
    import Navigation from '@/components/Navigation/Navigation.vue';
    import SearchModal from '@/components/Search/SearchModal.vue';
    import ThreadBox from '@/components/Thread/ThreadBox.vue';
    import { usePageStore } from '@/stores/PageStore.js';

    const currentRoute = useRoute();
    const pageStore = usePageStore();

    // watch the current route and reset the store if the route is not a page.
    // this makes it more memory efficient but also avoids many bugs, e.g. page is still selected on an irrelevant page.
    watch(() => currentRoute.name, (name) => {
        if (!name.includes('Wiki')) { // if the user is not in the wiki anymore, reset the store.
            pageStore.resetStore();
        }
    });
</script>

<style scoped lang="sass">
    .page-panel {
        /* this is a very subtle drop shadow - lifts the main page panel. */
        -webkit-box-shadow: -28px 3px 15px -6px rgba(0,0,0,0.05); 
        box-shadow: -28px 3px 15px -6px rgba(0,0,0,0.05);
    }
</style>