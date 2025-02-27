<template>
    <div v-if="isWikiEmpty" class="mt-5 d-flex flex-row justify-content-center align-items-center">
        <div class="d-flex flex-column align-items-center">
            <h4 class="bold">No pages created yet.</h4>
            <p class="m-0 text-muted">Create pages and tasks to browse here.</p>
        </div>
    </div>
    <div class="flex-fill d-flex row wiki-container m-0 p-0">
        <div v-if="!isWikiEmpty && !isFullscreenMode" class="wiki-col col-sm-4 col-lg-2 p-0 m-0 d-flex flex-column align-items-start">
            <button
                class="btn"
                @click="isFullscreenMode = !isFullscreenMode"
                v-tooltip="isFullscreenMode ? 'Exit fullscreen mode' : 'Enter fullscreen mode'"
                :class="{ 'btn-dark-gray': !isFullscreenMode, 'btn-dark': isFullscreenMode }"
            >
                <font-awesome-icon :icon="['fa', 'expand']" />
            </button>

            <PageExplorer />
        </div>
        <div v-else-if="!isWikiEmpty" class="col-sm-1">
            <button
                class="btn"
                @click="isFullscreenMode = !isFullscreenMode"
                v-tooltip="isFullscreenMode ? 'Exit fullscreen mode' : 'Enter fullscreen mode'"
                :class="{ 'btn-dark-gray': !isFullscreenMode, 'btn-dark': isFullscreenMode }"
            >
                <font-awesome-icon :icon="['fa', 'expand']" />
            </button>
        </div>
        <div class="flex-fill wiki-col col-sm-8 col-lg-10 m-0 p-0 d-flex flex-column" v-if="!isWikiEmpty">
            <div class="flex-fill">
                <!-- this is where the selected page will be rendered  -->
                <router-view></router-view> 
            </div>
        </div>
    </div>
</template>
<script setup>
    import { computed, onMounted, ref, watch } from 'vue';
    import { useRoute } from 'vue-router';
    import { useRouter } from 'vue-router';
    import PageExplorer from '@/components/Page/Explorer/PageExplorer.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useTagStore } from '@/stores/TagStore.js';

    const pageStore = usePageStore();
    const projectStore = useProjectStore();
    const tagStore = useTagStore();
    const currentRoute = useRoute();
    const router = useRouter();

    const isFullscreenMode = ref(false);
    const isWikiEmpty = computed(() => {
        return tagStore.tags.length === 0 && Object.keys(pageStore.displayedPages).length === 0;
    });

    onMounted(() => {
        // get all untagged pages; then redirect to the first untagged page if there is one
        pageStore.getPageList(projectStore.selectedProject.id, []).then(() => {
            redirectToFirstPage();
        });
    });

    /**
     * whenever the user is on the wiki page and has no selected page, select the first untagged page (if there is one)
     * this accounts for the fact that the untagged pages are not ready when the wiki page is loaded.
     */
    watch (() => tagStore.tagPages[-1], (newUntaggedPages) => {
        redirectToFirstPage();
    }, {deep: true});

    /**
     * Whenever the user navigates to the wiki page, redirect to the first untagged page if there is one.
     */
    watch(() => currentRoute.name, (newRouteName) => {
        redirectToFirstPage();
    });

    const redirectToFirstPage = () => {
        if (currentRoute.name == 'Wiki' && Object.values(tagStore.tagPages[-1] ?? {}).length > 0) {
            const firstPage = Object.values(tagStore.tagPages[-1])[0];

            pageStore.setSelectedPage(firstPage);
            router.push({ name: 'WikiPage', params: { id: firstPage.id } });
        }
    };
</script>