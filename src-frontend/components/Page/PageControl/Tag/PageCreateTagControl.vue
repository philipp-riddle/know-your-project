<template>
    <div>
        <TagDialogue
            :tags="currentPageTags"
            @createTag="(tagName) => handleTagCreate(tagName)"
            @addTag="(tag) => handleTagAdd(tag)"
            @removeTag="(tag) => handleTagRemove(tag)"
        />
    </div>
</template>

<script setup>
    import { ref, watch } from 'vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useUserStore } from '@/stores/UserStore.js';
    import TagDialogue from '@/components/Tag/TagDialogue.vue';

    const pageStore = usePageStore();
    const userStore = useUserStore();
    const currentPageTags = ref(pageStore.selectedPage.tags.map((tagPage) => tagPage.tag));

    // whenever the page tags change, the assigned page tags must change as well
    watch(() => pageStore.selectedPage.tags, (newPageTags) => {
        currentPageTags.value = newPageTags.map((tagPage) => tagPage.tag);
    }, {deep: true});

    const handleTagCreate = (tagName) => {
        pageStore.addTagToPageByName(pageStore.selectedPage, tagName).then((pageTag) => {
            userStore.currentUser.selectedProject.tags.push(pageTag.tag);
        });
    };

    const handleTagAdd = (tag) => {
        pageStore.addTagToPageById(pageStore.selectedPage, tag.id);
    };

    const handleTagRemove = (tag) => {
        // find the applied tag page in the page object.
        // the tag dialogue only gives us the general tag object / ID here.
        const tagPage = pageStore.selectedPage.tags.find((tagPage) => tagPage.tag.id === tag.id);

        if (null === tagPage) {
            console.error('Tag page not found in page object; cannot delete');
            return;
        }

        pageStore.removeTagFromPage(pageStore.selectedPage, tagPage);
    };
</script>