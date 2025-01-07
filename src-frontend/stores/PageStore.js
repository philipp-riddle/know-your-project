import { defineStore } from 'pinia';
import { usePageTabStore  } from './PageTabStore';
import { useProjectStore } from './ProjectStore';
import { ref } from 'vue';
import { fetchCreatePage, fetchDeletePage, fetchUpdatePage, fetchGetPage, fetchGetPageList } from '@/fetch/PageFetcher';
import { fetchCreateTagPageFromTagName, fetchCreateTagPageFromTagId, fetchDeleteTagPage } from '@/fetch/TagFetcher';

export const usePageStore = defineStore('page', () => {
    const pages = ref({});
    const selectedPage = ref(null);
    const pageTabStore = usePageTabStore();
    const projectStore = useProjectStore();

    // all displayed pages live here. they are saved with their ID as key to make it easier to retrieve and remove them from the list
    const displayedPages = ref({});

    // used to keep track of the tags that are displayed on the page; this lets us filter the pages by tags and easily remove all pages with a tag
    const displayedPageTags = ref({});

    // used to prevent loading a page multiple times in different places, Page view gets blocked while this boolean is true
    const isLoadingPage = ref(false);

    function reorderDisplayedPages() {
        displayedPages.value = Object.values(displayedPages.value).sort((a, b) => {
            return a.name - b.name;
        });
    }
    
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
        resetStore(); // clean up the store before setting a new page
        selectedPage.value = page;

        // if the page has no loaded page tabs we assume that the serializer skipped the tabs.
        // thus, we force a reload of the page to get the tabs and additionally select the first tab as selected.
        if (forceRefresh || !page.pageTabs || page.pageTabs.length === 0) {
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
            });
        } else {
            addPage(selectedPage.value); // to make sure all tabs are loaded into the store
            pageTabStore.setSelectedTab(page.pageTabs[0]);
        }
    }

    function getSelectedPage() {
        return selectedPage.value;
    }

    function createPage(page) {
        return new Promise((resolve) => {
            fetchCreatePage(page).then((newPage) => {
                addPage(newPage);

                if (!displayedPageTags.value[-1]) {
                    displayedPageTags.value[-1] = [];
                }
            
                displayedPageTags.value[-1].push(newPage.id); // make sure the page is displayed in the uncategorized section

                resolve(newPage);
            });
        });
    }

    function addPage(page) {
        // we have one store where we keep all page IDs - this prevents the store from storing multiple instances of the same page object
        displayedPages.value[page.id] = page;

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
            fetchGetPageList(projectId, null, null, null, null, tags).then((pageList) => {
                addPagesAndTagsToStore(pageList, tags);

                resolve(pageList);
            });
        });
    }

    /**
     * When we get a list of pages from the server, we must add them to the store and also add the tags to the store.
     * This can get quite tricky as we have to deal with uncategorized pages, uninitialized tags, and pages that are already displayed.
     */
    function addPagesAndTagsToStore(pages, tags) {
        if (tags) {
            for (const tag of tags) {
                // this makes sure that the tag nav is ready and initialized, even if we add new ones later and the tag had no pages before
                if (!displayedPageTags.value[tag]) {
                    displayedPageTags.value[tag] = [];
                }
            }
        }

        for (const page of pages) {
            addPage(page, tags);

            if (tags && tags.length === 0) {
                // if no tags are given, we display all uncategorized pages.
                // to make them appear and behave like all the other tags we use -1 as the key
                if (!displayedPageTags.value[-1]) {
                    displayedPageTags.value[-1] = [];
                }
    
                if (!displayedPageTags.value[-1].includes(page.id)) {
                    displayedPageTags.value[-1].push(page.id);
                }
            } else if (tags) {
                for (const tagPage of page.tags) {
                    const tag = tagPage.tag;
    
                    // this prevents the tag from being displayed if it is not in the list of tags we want to display
                    // or if it is already displayed on the page
                    if (!tags.includes(tag.id) || displayedPageTags.value[tag.id]?.includes(page.id)) {
                        continue;
                    }
    
                    if (!displayedPageTags.value[tag.id]) {
                        displayedPageTags.value[tag.id] = [];
                    }
    
                    displayedPageTags.value[tag.id].push(page.id);
                }
            }
        }
    }

    function addTagToPageByName(page, tagName) {
        return new Promise((resolve) => {
            fetchCreateTagPageFromTagName(page.id, tagName).then((pageTag) => {
                selectedPage.value.tags.push(pageTag);
                
                if (displayedPageTags.value[-1]?.includes(page.id)) {
                    displayedPageTags.value[-1] = displayedPageTags.value[-1].filter((p) => p !== page.id);
                }
                
                // if the tag is indeed displayed on the page, we add the tag to the displayed tags in the nav
                if (displayedPageTags.value[pageTag.tag.id]) {
                    displayedPageTags.value[pageTag.tag.id].push(page.id);   
                }

                projectStore.selectedProject?.tags.push(pageTag.tag);

                resolve(pageTag);
            });
        });
    }

    function addTagToPageById(page, tagId) {
        return new Promise((resolve) => {
            fetchCreateTagPageFromTagId(page.id, tagId).then((pageTag) => {
                selectedPage.value.tags.push(pageTag);

                if (displayedPageTags.value[-1]?.includes(page.id)) {
                    displayedPageTags.value[-1] = displayedPageTags.value[-1].filter((p) => p !== page.id);
                }
    
                // if the tag is indeed displayed on the page, we add the tag to the displayed tags in the nav
                if (displayedPageTags.value[pageTag.tag.id]) {
                    displayedPageTags.value[pageTag.tag.id].push(page.id);
                }
    
                resolve(pageTag);
            });
        });
    }

    function removeTagFromPage(page, tagPage) {
        return new Promise((resolve) => {
            fetchDeleteTagPage(tagPage.id).then(() => {
                selectedPage.value.tags = selectedPage.value.tags.filter((tp) => tp.id !== tagPage.id);

                // if the navigation is loaded for this tag we must remove the tagged page
                if (displayedPageTags.value[tagPage.tag.id]) {
                    // first, filter the page from the tag
                    displayedPageTags.value[tagPage.tag.id] = displayedPageTags.value[tagPage.tag.id].filter((p) => p !== page.id);

                    // if the seleceted page now has no tags, we must add it to the uncategorized pages
                    if (selectedPage.value.tags.length === 0) {
                        if (!displayedPageTags.value[-1]) {
                            displayedPageTags.value[-1] = [];
                        }

                        displayedPageTags.value[-1].push(page.id);
                    }
                }

                resolve();
            });
        });
    }

    function deletePage(pageId) {
        return new Promise((resolve) => {
            fetchDeletePage(pageId).then(() => {
                if (selectedPage.value && selectedPage.value.id === pageId) {
                    selectedPage.value = null;
                }

                removePage(pageId);

                resolve();
            });
        });
    }

    function removePage(pageId) {
        // also filter it from the displayed pages
        if (displayedPages.value) {
            displayedPages.value = displayedPages.value.filter((p) => p.id !== pageId);
        }

        if (pages.value[pageId]) {
            delete pages.value[pageId];
        }
    }

    return {
        displayedPages,
        displayedPageTags,
        reorderDisplayedPages,
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
        addTagToPageByName,
        addTagToPageById,
        removeTagFromPage,
        deletePage,
        removePage,
    };
});