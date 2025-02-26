import { defineStore } from 'pinia';
import { useFilterStore  } from './FilterStore';
import { usePageStore  } from './PageStore';
import { useProjectStore } from './ProjectStore';
import { ref } from 'vue';
import { fetchCreateTag, fetchUpdateTag, fetchDeleteTag, fetchChangeTagPageOrder, fetchChangeTagOrder } from '@/stores/fetch/TagFetcher';

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
     * Important: The order index is saved along the nested IDs to make sure the order is kept.
     */
    const nestedTagIdMap = ref({});

    const setup = () => {
        const project = projectStore.selectedProject;

        if (project === null) {
            return;
        }
        
        tags.value = project.tags.map((tag) => {
            tag.project = project; // enrich the serialized object; otherwise it is only an ID because of circular dependencies in the entities

            return tag;
        });
        tags.value = tags.value.sort((a, b) => a.orderIndex - b.orderIndex); // sort the tags by their order index when setting up the store
        tags.value.forEach((tag) => {
            for (const projectTag of tag.tags) { // .tags are all the tags which are nested in the tag
                addTagToNestedIds(tag, projectTag);   
            }
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
            addTagToNestedIds(parentTag, tag);
        }
    }

    const addTagToNestedIds = (parentTag, tag) => {
        if (!nestedTagIdMap.value[parentTag.id]) {
            nestedTagIdMap.value[parentTag.id] = {};
        } else if (Object.values(nestedTagIdMap.value[parentTag.id]).some((tagId) => tagId === tag.id)) {
            console.error('return; already in nested tags');
            return; // the tag is already included in the nested tags
        }

        nestedTagIdMap.value[parentTag.id][tag.orderIndex] = tag.id;
        
        // now we need to sort the nested IDs
        const orderIndices = Object.keys(nestedTagIdMap.value[parentTag.id]).sort((a, b) => a - b);
        let newNestedTagIds = {};

        for (const orderIndex of orderIndices) {
            newNestedTagIds[orderIndex] = nestedTagIdMap.value[parentTag.id][orderIndex];
        }

        nestedTagIdMap.value[parentTag.id] = newNestedTagIds;
    }

    const addPageToTags = (page, tags) => {
        const hasTags = tags && tags.length > 0;
        const tagIds = hasTags ? tags : [-1]; // -1 is the key for all pages that have no tag; this is the case if no tag was given

        for (const tag of tagIds) {
            const tagId = tag?.tag?.id ?? tag;
            let tagPage = typeof tag === 'object' ? tag : null;

            if (tagPage) {
                tagPage.page = page; // enrich the serialized object; otherwise it is only an ID because of circular dependencies in the entities
            }

            if (!tagPages.value[tagId]) {
                tagPages.value[tagId] = {};
            } else if(Object.values(tagPages.value[tagId]).some((tagPageOrPage) => tagPageOrPage.id === page.id || tagPageOrPage.id === tagPage?.id)) {
                continue; // the page is already included in the tag
            }

            let orderIndex = tagPage?.orderIndex ?? page.orderIndex;

            // there are several cases in which we want to regenerate the order index:
            //    - when adding a page to a new tag: this means adding it to the end of the list.
            //    - work with corrupt order indices - check if the order index is already taken or if it is null and generate a new one; this ensures that the order index is unique and all pages are displayed.
            if (orderIndex === null || (tagPages.value[tagId][orderIndex] ?? null)) {
                orderIndex = Math.max(0, ...Object.keys(tagPages.value[tagId] ?? {})) + 1;

                if (tagPage) {
                    tagPage.orderIndex = orderIndex;
                } else {
                    page.orderIndex = orderIndex;
                }
            }

            // attach the tag page or just the page
            tagPages.value[tagId][orderIndex] = tagPage ?? page;
            // now sort the pages by their order index
            sortTagPages(tagId);
        }

        // if the page was added to a tag we must check if it is included in the uncategorized section -
        // if so we must remove it from there.
        if (!tagIds.some((id) => id == -1) && Object.values(tagPages.value[-1] ?? {}).some((tagPageOrPage) => tagPageOrPage.pageTabs && tagPageOrPage.id === page.id)) {
            removeTagFromPage(page, null, false);
        }
    };

    const sortTagPages = (tagId) => {
        const sortedTagPages = Object.values(tagPages.value[tagId]).sort((a, b) => a.orderIndex - b.orderIndex);
        let newTagPages = {};

        for (const tagPage of sortedTagPages) {
            newTagPages[tagPage.orderIndex] = tagPage;
        }

        tagPages.value[tagId] = newTagPages;
    }

    const removeTagFromPage = (page, tag, addToUntagged=true) => {
        const tagId = tag?.id ?? -1;

        if (tagPages.value[tagId]) {
            const oldLength = Object.values(tagPages.value[tagId]).length;
            let newTagPages = {};

            for (const orderIndex of Object.keys(tagPages.value[tagId])) {
                const tagPageOrPage = tagPages.value[tagId][orderIndex];
                const isPage = typeof tagPageOrPage.pageTabs !== undefined;
                const isTagPage = typeof tagPageOrPage.page !== undefined;
                
                if (isPage && tagPageOrPage.id === page.id) {
                    continue;
                }

                if (isTagPage && tagPageOrPage.page?.id === page.id) {
                    continue;
                }

                newTagPages[orderIndex] = tagPageOrPage;
            }

            tagPages.value[tagId] = newTagPages;

            if (tagId !== -1 && oldLength === Object.values(tagPages.value[tagId]).length) {
                console.error('Could not remove page from tag', page, tag);
            }
        }

        // we must check if the page has any tags left;
        // if not we can add it to the uncategorized section.
        if (addToUntagged && page.tags.length === 0) {
            addPageToTags(page, null);
        }
    };

    const removePageFromTagsCompletely = (page) => {
        if (page.tags.length > 0) {
            for (const tag of page.tags) {
                removeTagFromPage(page, tag.tag, false);
            }
        } else if (tagPages.value[-1]) {
            removeTagFromPage(page, -1, false);
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
    };

    const reorderTagPages = (tagId, tagPageOrPageIds) => {
        fetchChangeTagPageOrder(projectStore.selectedProject.id, tagId, tagPageOrPageIds).then((sortedItems) => {
            tagPages.value[tagId] = sortedItems;
            // sortTagPages(tagId);
        });
    }

    const reorderTags = (parentTagId, tagIdOrder) => {
        fetchChangeTagOrder(projectStore.selectedProject.id, parentTagId, tagIdOrder).then((sortedTags) => {
            tags.value = tags.value.map((tag) => {
                return sortedTags.find((sortedTag) => sortedTag.id === tag.id) ?? tag;
            });
            tags.value = tags.value.sort((a, b) => a.orderIndex - b.orderIndex);

            // also, regenerate all the nested IDs map for the parent tag
            let newNestedTagIdMap = {};

            for (const reorderedTag of sortedTags) {
                newNestedTagIdMap[reorderedTag.orderIndex] = reorderedTag.id;
            }

            nestedTagIdMap.value[parentTagId] = newNestedTagIdMap;
        });
    }

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
        reorderTagPages,
        reorderTags,
    };
});