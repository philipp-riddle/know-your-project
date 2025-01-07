<template>
    <div class="d-flex flex-column gap-2">
        <div class="d-flex flex-row align-items-end justify-content-between">
            <small class="text-muted p" v-tooltip="'All your project pages and notes are displayed here.'"><strong>PAGES</strong></small>
            <NavigationCreateContentMenu />
        </div>

        <!-- for the uncategorized pages -->
        <PageList />

        <ul class="nav nav-pills nav-fill d-flex flex-column gap-2 p-0 m-0">
            <li
                v-for="tag in projectStore.selectedProject?.tags"
                :key="tag.id"
                class="nav-item d-flex flex-column gap-1"
            >
                <p
                    @click="() => toggleTag(tag.id)"
                    class="nav-link nav-directory-link d-flex flex-row justify-content-between m-0"
                    :class="{'active': shownTags[tag.id] ?? null, 'inactive': !(shownTags[tag.id] ?? null)}"
                >
                    <span class="d-flex flex-row align-items-center gap-2">
                        <font-awesome-icon :icon="['fas', 'chevron-'+((shownTags[tag.id] ?? null) ? 'down' : 'up')]" />
                        <span>{{ tag.name }}</span>
                    </span>
                    <span class="btn btn-sm me-2" :style="{'background-color': tag.color}">&nbsp;&nbsp;&nbsp;</span>
                </p>

                <PageList
                    v-if="shownTags[tag.id] ?? null"
                    :tag="tag"
                    class="ms-3"
                />
            </li>
        </ul>
    </div>
</template>

<script setup>
    import { onMounted, ref } from 'vue';
    import { useRoute } from 'vue-router';
    import PageList from '@/components/Page/Explorer/PageList.vue';
    import NavigationCreateContentMenu from '@/components/Navigation/NavigationCreateContentMenu.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const pageStore = usePageStore();
    const projectStore = useProjectStore();
    const shownTags = ref({});
    const route = useRoute();

    // on page reload we want the tag to be open, just like when we navigated to it.
    // we can read the route params and determine which tag to open.
    onMounted(() => {
        projectStore.getSelectedProject().then((project) => {
            const tag = route.params.tagName ? project.tags.find((tag) => tag.name == route.params.tagName) : null;

            if (tag) {
                shownTags.value[tag.id] = 1;
            }
        })
    });

    const toggleTag = (tagId) => {
        if (shownTags.value[tagId] ?? null) {
            delete shownTags.value[tagId];
        } else {
            shownTags.value[tagId] = 1;
        }
    };
</script>
