<template>
    <div class="d-flex flex-column gap-2">
        <!-- for the uncategorized pages -->
        <PageList />

        <!-- display all tags in the project, let the user expand and collapse the tagged pages -->
        <NestedPageList
            :tagIds="topLevelTagIds"
            class="nav-item d-flex flex-column gap-1"
        />
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
    const topLevelTagIds = computed(() => {
        return tagStore.tags.filter((tag) => tag.parent === null).map((tag) => tag.id);
    })
</script>
