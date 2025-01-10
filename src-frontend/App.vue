<template>
    <div class="container-fluid p-0 m-0 h-100" style="max-width: 100%">
        <div class="row h-100 p-0 m-0">
            <div class="col-sm-4 col-lg-2 p-0 m-0">
                <NavigationSidebar />
            </div>
            <div class="col-sm-8 col-lg-10 m-0 p-0 h-100 bg-white page-panel">
                <router-view></router-view>
            </div>
        </div>
    </div>

    <SearchModal />
</template>

<script setup>
    import { watch,reactive, computed } from 'vue';
    import { useRoute } from 'vue-router';
    import NavigationSidebar from '@/components/Navigation/NavigationSidebar.vue';
    import SearchModal from '@/components/Search/SearchModal.vue';
    import { usePageStore } from '@/stores/PageStore.js';

    const currentRoute = useRoute();
    const pageStore = usePageStore();

    // watch the current route and reset the store if the route is not a page.
    // this makes it more memory efficient but also avoids many bugs, e.g. page is still selected on an irrelevant page.
    watch(() => currentRoute.name, (name) => {
        if (!name.includes('Page')) {
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