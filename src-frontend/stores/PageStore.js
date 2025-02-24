import { defineStore } from 'pinia';
import { useTagStore } from './TagStore';
import { useTaskStore } from './TaskStore';
import { usePageTabStore  } from './PageTabStore';
import { ref } from 'vue';
import { fetchCreatePage, fetchDeletePage, fetchUpdatePage, fetchGetPage, fetchGetPageList, fetchCreatePageUser, fetchDeletePageUser } from '@/stores/fetch/PageFetcher';
import { fetchCreateTagPageFromTagName, fetchCreateTagPageFromTagId, fetchDeleteTagPage } from '@/stores/fetch/TagFetcher';

export const usePageStore = defineStore('page', () => {
    const pages = ref({}); // @todo confusion... why is this here?
    const selectedPage = ref(null);
    const tagStore = useTagStore();
    const taskStore = useTaskStore();
    const pageTabStore = usePageTabStore();

    // all displayed pages live here. they are saved with their ID as key to make it easier to retrieve and remove them from the list
    const displayedPages = ref({});

    // used to prevent loading a page multiple times in different places, Page view gets blocked while this boolean is true
    const isLoadingPage = ref(false);
    
    /**
     * Cleanup method when the user navigates away from a page. This makes sure that all displayed data is cleared and the store is ready for the next page.
     */
    async function resetStore() {
        pages.value = {};
        selectedPage.value = null;
        isLoadingPage.value = false;
        pageTabStore.resetStore(); // this clears the tab store as well as the section store
    }

    async function setSelectedPage(page, forceRefresh = false) {
        return new Promise((resolve) => {
            if (!page) {
                resolve(null);
                console.error('No page given to set as selected page');
                return;
            }
    
            resetStore(); // clean up the store before setting a new page
            selectedPage.value = page;
            let refresh = false;

            // if forceRefresh is set to true we must reload the page from the server in any case
            if (forceRefresh) {
                refresh = true;

            // if the page has no loaded page tabs we assume that the serializer skipped the tabs.
            // thus, we force a reload of the page to get the tabs and additionally select the first tab as selected.
            } else if (!page.pageTabs || !Array.isArray(page.pageTabs) || page.pageTabs.length === 0) {
                refresh = true;

            // If the page tabs are loaded we must check if they are arrays or objects.
            // If they are not, we must refresh the page. E.g. if the serialiser returned only numbers as the circular reference handler clicked in.
            } else {
                for (let i = 0; i < page.pageTabs.length; i++) {
                    const pageTab = page.pageTabs[i];
                    if (!Array.isArray(pageTab) && typeof pageTab !== 'object') {
                        refresh = true; // contains page tabs which are not arrays/objects, thus we must refresh
                    }
                }
            }

            if (refresh) {
                selectedPage.value = null;
                isLoadingPage.value = true;
    
                getPage(page.id, true).then((fetchedPage) => {
                    displayedPages.value[fetchedPage.id] = fetchedPage;
                    selectedPage.value = fetchedPage;
    
                    if (fetchedPage.pageTabs.length > 0) {
                        pageTabStore.setSelectedTab(fetchedPage.pageTabs[0]);
                    }
    
                    isLoadingPage.value = false;
                    addPage(fetchedPage);
                    resolve(fetchedPage);
                });
            } else {
                addPage(selectedPage.value); // to make sure all tabs are loaded into the store
                pageTabStore.setSelectedTab(page.pageTabs[0]);
                resolve(selectedPage.value);
            }
        });
    }

    function getSelectedPage() {
        return selectedPage.value;
    }

    function createPage(page) {
        return new Promise((resolve) => {
            fetchCreatePage(page).then((newPage) => {
                addPage(newPage);

                resolve(newPage);
            });
        });
    }

    function addPage(page, tags) {
        // we have one store where we keep all page IDs - this prevents the store from storing multiple instances of the same page object
        displayedPages.value[page.id] = page;
        tagStore.addPageToTags(page, tags ?? page.tags);

        page.pageTabs.forEach((tab) => {
            pageTabStore.addTab(page.id, tab);
        });
    }

    function updatePage(page) {
        return new Promise((resolve) => {
            fetchUpdatePage(page).then((updatedPage) => {
                addPage(updatedPage);
                resolve(updatedPage);
            });
        });
    }

    function getPage(pageId, forceRefresh = false) {
        return new Promise((resolve) => {
            // the page is already loaded into the store.
            // we must check if the full object is available as the list endpoints only serialize a subset of the object to reduce the payload size.
            if (!forceRefresh && pages.value[pageId]?.pageTabs?.length > 0) {
                resolve(pages.value[pageId]);
            } else {
                fetchGetPage(pageId).then((page) => {
                    addPage(page);
                    resolve(page);
                });
            }
        });
    }

    function getPageList(projectId, tags) {
        return new Promise((resolve) => {
            // use the preloaded version in the window object if available and if we want to load all pages with no tags
            if (tags && tags.length === 0 && window.untaggedPages) {
                addPagesToStore(window.untaggedPages);
                resolve(window.untaggedPages);
                return;
            }

            fetchGetPageList(projectId, null, null, null, tags).then((pageList) => {
                addPagesToStore(pageList);

                resolve(pageList);
            });
        });
    }

    /**
     * When we get a list of pages from the server, we must add them to the store and also add the tags to the store.
     * This can get quite tricky as we have to deal with uncategorized pages, uninitialized tags, and pages that are already displayed.
     */
    function addPagesToStore(pages, tags) {
        for (const page of pages) {
            addPage(page);
        }
    }

    function addTagToPageByName(page, tagName, parentTag) {
        return new Promise((resolve) => {
            fetchCreateTagPageFromTagName(page.id, tagName, parentTag?.id).then((pageTag) => {
                selectedPage.value.tags.push(pageTag);
                tagStore.addTag(pageTag.tag, parentTag);
                tagStore.addPageToTags(page, [pageTag]);

                resolve(pageTag);
            });
        });
    }

    function addTagToPageById(page, tagId) {
        return new Promise((resolve) => {
            fetchCreateTagPageFromTagId(page.id, tagId).then((pageTag) => {
                selectedPage.value.tags.push(pageTag);
                tagStore.addPageToTags(page, [pageTag]);

                resolve(pageTag);
            });
        });
    }

    function removeTagFromPage(page, tagPage) {
        return new Promise((resolve) => {
            fetchDeleteTagPage(tagPage.id).then(() => {
                selectedPage.value.tags = selectedPage.value.tags.filter((tp) => tp.id !== tagPage.id);
                tagStore.removeTagFromPage(selectedPage.value, tagPage.tag);

                resolve();
            });
        });
    }

    /**
     * Completely removes the tag from the store, i.e. in its displayed page and displayed page lists + associated tags.
     *
     * @param {Tag} tag 
     */
    function removeTag(tag) {
        selectedPage.value.tags = selectedPage.value.tags.filter((tp) => tp.tag.id !== tag.id);
    }

    function addUserToPage(page, user) {
        return new Promise((resolve) => {
            fetchCreatePageUser(page.id, user.id).then((userPage) => {
                selectedPage.value.users.push(userPage);
                resolve(userPage);
            });
        });
    }

    function removeUserFromPage(userPage) {
        return new Promise((resolve) => {
            fetchDeletePageUser(userPage.id).then(() => {
                selectedPage.value.users = selectedPage.value.users.filter((up) => up.id !== userPage.id);
                resolve();
            });
        });
    }

    function deletePage(page) {
        return new Promise((resolve) => {
            fetchDeletePage(page.id).then(() => {
                if (selectedPage.value && selectedPage.value.id === page.id) {
                    selectedPage.value = null;
                }

                removePage(page);

                if (page.task !== null) {
                    taskStore.removeTaskFromStore(page.task);
                }

                resolve();
            });
        });
    }

    function removePage(page) {
        // also filter it from the displayed pages
        if (displayedPages.value?.length > 0) {
            displayedPages.value = displayedPages.value.filter((p) => p.id !== page.id);
        }

        tagStore.removePageFromTagsCompletely(page);

        if (pages.value[page.id]) {
            delete pages.value[page.id];
        }
    }

    return {
        displayedPages,
        pages,
        isLoadingPage,
        resetStore,
        selectedPage,
        setSelectedPage,
        getSelectedPage,
        createPage,
        addPage,
        updatePage,
        getPage,
        getPageList,
        addPagesToStore,
        addTagToPageByName,
        addTagToPageById,
        removeTagFromPage,
        removeTag,
        addUserToPage,
        removeUserFromPage,
        deletePage,
        removePage,
    };
});