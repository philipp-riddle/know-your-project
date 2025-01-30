<template>
    <div class="d-flex flex-column gap-2">
        <div class="d-flex flex-row align-items-end justify-content-between">
            <small class="text-muted p" v-tooltip="'All your project pages and notes are displayed here.'"><strong>PAGES</strong></small>
            <NavigationCreateContentMenu />
        </div>

        <!-- for the uncategorized pages -->
        <PageList />

        <!-- display all tags in the project, let the user expand and collapse the tagged pages -->
        <ul class="nav nav-pills nav-fill d-flex flex-column gap-2 p-0 m-0">
            <NestedPageList
                v-for="tag in topLevelTags"
                :key="tag.id"
                :tag="tag"
                class="nav-item d-flex flex-column gap-1"
            />
        </ul>
    </div>
</template>

<script setup>
    import { computed, onMounted, ref } from 'vue';
    import { useRoute } from 'vue-router';
    import PageList from '@/components/Page/Explorer/PageList.vue';
    import NestedPageList from '@/components/Page/Explorer/NestedPageList.vue';
    import NavigationCreateContentMenu from '@/components/Navigation/NavigationCreateContentMenu.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useTagStore } from '@/stores/TagStore.js';

    const pageStore = usePageStore();
    const projectStore = useProjectStore();
    const tagStore = useTagStore();

    // in the parent overview we only want to show tags who are top level, i.e. are not a child of any other tag
    const topLevelTags = computed(() => {
        return tagStore.tags.filter((tag) => tag.parent === null);
    })
</script>
