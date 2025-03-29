<template>
    <ul class="nav nav-pills nav-fill d-flex flex-column p-0 m-0">
        <NestedPageList
            v-if="props.tag"
            :tagIds="Object.values(tagStore.nestedTagIdMap[props.tag.id] ?? [])"
        />

        <!-- display all pages in the currently selected tag or show all untagged pages (if props.tag is NULL) -->
        <draggable
            class="nav nav-pills nav-fill d-flex flex-column p-0 m-0"
            v-model="tagPages"
            tag="ul"
            item-key="id"
            @end="onDragEnd"
        >
            <template #item="{ element }">
                <li>
                    <PageListItem
                        :tag="tag"
                        :page="pageStore.displayedPages[!props.tag ? element.id : element.page.id]"
                        :onPageDelete="onPageDelete"
                    />
                </li>
            </template>
        </draggable>
    </ul>
</template>
<script setup>
	import draggable from "vuedraggable";

    import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
    import PageListItem from '@/components/Page/Explorer/PageListItem.vue';
    import NestedPageList from '@/components/Page/Explorer/NestedPageList.vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useTagStore } from '@/stores/TagStore.js';
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
    const tagStore = useTagStore();
    const userStore = useUserStore();

    // copy of tag pages - we need to use a ref here to make sure the draggable component works and can update the list;
    // if the tag store changes, we need to update this list as well.
    const tagPages = ref(null);

    onMounted(() => {
        userStore.getCurrentUser().then((user) => {
            const selectedProject = props.project ?? userStore.currentUser?.selectedProject;
            
            if (!selectedProject) {
                console.error('No project in context in PageList.vue component!');
                return;
            }

            pageStore.getPageList(selectedProject?.id, props.tag ? [props.tag.id] : []).then(() => {
                tagPages.value = Object.values(tagStore.tagPages[props.tag?.id ?? -1] ?? []);
            });
        });
    });

    onUnmounted(() => {
        delete tagStore.tagPages[props.tag?.id ?? -1];
    });

    watch (() => tagStore.tagPages[props.tag?.id ?? -1], (newValue) => {
        tagPages.value = Object.values(newValue);
    });

    const onPageDelete = async (page) => {
        await pageStore.deletePage(page);
    };

    const onDragEnd = (event) => {
        let pageIdOrder = [];
        let tagPageIdOrder = [];

        for (let i = 0; i < event.to.children.length; i++) {
            let tagPageElement = event.to.children[i].children[0]; // first get the <li>, then the <PageListItem>
            let pageId = parseInt(tagPageElement.getAttribute('page'));

            if (isNaN(pageId)) {
                console.error('Invalid page ID in PageList.vue component!', tagPageElement);
                continue; // this is to prevent us from including any corrupted data in the list.
            }

            if (props.tag) { // dragging around tagged pages
                const tagId = parseInt(tagPageElement.getAttribute('tag'));
                const tagPage = Object.values(tagStore.tagPages[props.tag.id]).find((tagPage) => tagPage.tag.id == tagId && tagPage.page.id == pageId) ?? null;
                
                if (null === tagPage) {
                    console.error('Invalid tag page ID in PageList.vue component!', tagPageElement);
                    continue; // this is to prevent us from including any corrupted data in the list.
                }

                tagPageIdOrder.push(tagPage.id);
            } else { // dragging around the untagged pages
                pageIdOrder.push(pageId);
            }
        }

        if (tagPageIdOrder.length > 0) {
            tagStore.reorderTagPages(props.tag?.id, tagPageIdOrder);
        } else if (pageIdOrder.length > 0) {
            tagStore.reorderTagPages(null, pageIdOrder);
        }
    };
</script>