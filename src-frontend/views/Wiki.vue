<template>
    <div class="row wiki-container m-0 p-0">
        <div class="wiki-col mt-5 col-sm-4 col-lg-2 p-0 m-0">
            <PageExplorer />
        </div>
        <div class="wiki-col col-sm-8 col-lg-10 m-0 p-0 h-100 d-flex flex-column">
            <div class="flex-fill">
                <!-- this is where the selected page will be rendered  -->
                <router-view></router-view> 
            </div>
        </div>
    </div>
</template>
<script setup>
    import PageExplorer from '@/components/Page/Explorer/PageExplorer.vue';
</script>