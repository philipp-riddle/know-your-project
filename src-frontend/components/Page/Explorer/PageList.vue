<template>
    <ul class="nav nav-pills nav-fill d-flex flex-column p-0 m-0">
        <li
            v-for="page in pageStore.displayedPageTags[tag?.id ?? -1]"
            :key="page"
            class="nav-item d-flex flex-row align-items-center"
        >
            <PageListItem
                v-if="pageStore.displayedPages[page]"
                :tag="tag"
                :page="pageStore.displayedPages[page]"
                :onPageDelete="onPageDelete"
            />
            <span class="text-danger" v-else>Cannot display page {{ page }}</span>
        </li>
    </ul>
</template>
<script setup>
    import { computed, ref, onMounted, onUnmounted } from 'vue';
    import PageListItem from '@/components/Page/Explorer/PageListItem.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useUserStore } from '@/stores/UserStore.js';

    const props = defineProps({
        project: { // provide a project object if you want to only see all pages of a project, not of the current user.
            type: Object,
            required: false,
            default: null,
        },
        tag: {
            type: Object,
            required: false,
            default: null,
        },
    });
    const pageStore = usePageStore();
    const userStore = useUserStore();

    onMounted(async () => {
        userStore.getCurrentUser().then((user) => {
            const selectedProject = props.project ?? userStore.currentUser?.selectedProject;
            
            if (!selectedProject) {
                console.error('No project in context in PageList.vue component!');
                return;
            }

            pageStore.getPageList(selectedProject?.id, props.tag ? [props.tag.id] : []);
        });
    });

    onUnmounted(() => {
        delete pageStore.displayedPageTags[props.tag?.id ?? -1];
    });

    const onPageDelete = async (page) => {
        await pageStore.deletePage(page);
    };

</script>