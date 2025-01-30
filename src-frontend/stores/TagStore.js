import { defineStore } from 'pinia';
import { useFilterStore  } from './FilterStore';
import { usePageStore  } from './PageStore';
import { useProjectStore } from './ProjectStore';
import { ref } from 'vue';
import { fetchCreateTag, fetchUpdateTag, fetchDeleteTag } from '@/stores/fetch/TagFetcher';

export const useTagStore = defineStore('tag', () => {
    const filterStore = useFilterStore();
    const pageStore = usePageStore();
    const projectStore = useProjectStore();

    /**
     * Global store for the tags of a project.
     */
    const tags = ref([]);

    /**
     * Global stores for all pages that have a specific tag and are displayed in the UI.
     *
     * -1 is the key for all pages that have no tag.
     * All other keys are the tag ids.
     */
    const tagPages = ref({});

    /**
     * Global store to save which tags are currently shown in the frontend.
     */
    const shownTags = ref({});

    /**
     * Global store to save how the tags are nested.
     * This can be used to recurisvely display the tags as a tree in the frontend.
     */
    const nestedTagIdMap = ref({});

    const setup = () => {
        const project = projectStore.selectedProject;
        
        tags.value = project.tags;
        tags.value.forEach((tag) => {
            nestedTagIdMap.value[tag.id] = tag.tags.map((t) => t.id);
        });
    };

    const createTag = (project, tagName, parentTag) => {
        return fetchCreateTag(project.id, tagName, parentTag?.id).then((createdTag) => {
            addTag(createdTag, parentTag);
        });
    };

    /**
     * Adds the tag to the global store.
     * If a parent tag is given the tag it is also added to the nestedTagIdMap.
     */
    const addTag = (tag, parentTag) => {
        tags.value.push(tag);

        if (parentTag) {
            if (!nestedTagIdMap.value[parentTag.id]) {
                nestedTagIdMap.value[parentTag.id] = [];
            }

            nestedTagIdMap.value[parentTag.id].push(tag.id);
        }
    }

    const addPageToTags = (page, tags) => {
        const hasTags = tags && tags.length > 0;
        const tagIds = hasTags ? tags : [-1]; // -1 is the key for all pages that have no tag; this is the case if no tag was given

        for (const tagId of tagIds) {   
            if (!tagPages.value[tagId]) {
                tagPages.value[tagId] = [];
            } else if(tagPages.value[tagId].includes(page.id)) {
                continue; // the page is already included in the tag
            }

            tagPages.value[tagId].push(page.id);
            tagPages.value[tagId] = [...new Set(tagPages.value[tagId])]; // filter out duplicates
        }

        // if the page was added to a tag we must check if it is included in the uncategorized section -
        // if so we must remove it from there.
        if (hasTags && tagPages.value[-1]) {
            tagPages.value[-1] = tagPages.value[-1].filter((id) => id !== page.id);
        }
    };

    const removeTagFromPage = (page, tag) => {
        const tagId = tag.id;

        if (tagPages.value[tagId]) {
            tagPages.value[tagId] = tagPages.value[tagId].filter((id) => id !== page.id);
        }

        // we must check if the page has any tags left;
        // if not we can add it to the uncategorized section.
        if (page.tags.length === 0) {
            addPageToTags(page, null);
        }
    };

    const removePageFromTagsCompletely = (page) => {
        if (page.tags.length > 0) {
            for (const tag of page.tags) {
                tagPages.value[tag.id] = tagPages.value[tag.id].filter((id) => id !== page.id);
            }
        } else if (tagPages.value[-1]) {
            tagPages.value[-1] = tagPages.value[-1].filter((id) => id !== page.id);
        }
    };

    const updateTag = (tag) => {
        return new Promise((resolve) => {
            fetchUpdateTag(tag).then((updatedTag) => {
                tags.value = tags.value.map((t) => {
                    if (t.id === updatedTag.id) {
                        return updatedTag;
                    }

                    return t;
                });

                resolve(updatedTag);
            });
        });
    };

    /**
     * Deletes the tag and removes it from the store.
     */
    const deleteTag = (tag) => {
        return fetchDeleteTag(tag).then(() => {
            filterStore.removeFilterTag(tag); // make sure the tag is not included in the filters anymore
            pageStore.removeTag(tag); // make sure the tag is not included in the displayed page and page lists anymore

            removeTag(tag);
        });
    };

    /**
     * Removes the tag completely from the store.
     */
    const removeTag = (tag) => {
        tags.value = tags.value.filter((t) => t.id !== tag.id); // make sure the tag is not included in the project anymore
        
        if (tagPages.value[tag.id]) {
            delete tagPages.value[tag.id];
        }

        // if the tag was associated with a parent tag we must remove it from the nestedTagIdMap
        if (tag.parent && nestedTagIdMap.value[tag.parent.id]) {
            nestedTagIdMap.value[tag.parent.id] = nestedTagIdMap.value[tag.parent.id].filter((id) => id !== tag.id);
        }

        // if the tag itself is a parent tag we need to remove all children
        if (nestedTagIdMap.value[tag.id]) {
            for (const childId of nestedTagIdMap.value[tag.id]) {
                delete tagPages.value[childId];
            }

            delete nestedTagIdMap.value[tag.id];
        }

        console.log(tag, tags.value);
    };

    return {
        tags,
        tagPages,
        shownTags,
        nestedTagIdMap,
        setup,
        createTag,
        addTag,
        addPageToTags,
        removeTagFromPage,
        removePageFromTagsCompletely,
        updateTag,
        deleteTag,
        removeTag,
    };
});