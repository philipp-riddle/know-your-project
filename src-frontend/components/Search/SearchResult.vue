<template>
    <div
        class="search-result d-flex flex-column gap-2"
        :class="{
            'search-result-root': !props.isNested,
        }"
        @click.stop="onSearchResultClick"
    >
        <div class="search-result-content d-flex flex-column gap-1">
            <div class="flex-fill d-flex flex-row gap-2">
                <span
                    class="btn m-0 p-0"
                    :class="{'btn-sm': condensed, 'btn-lg': !condensed}"
                >
                    <font-awesome-icon :icon="['fas', searchResultIcon]" v-tooltip="searchResultTooltip" />
                </span>

                <div class="flex-fill d-flex flex-column gap-1">
                    <div v-if="!isNested" class="d-flex flex-row justify-content-between gap-2">
                        <h5 v-if=" result.title != null" class="m-0 p-0 black"><span v-html="result.title"></span></h5>

                        <div class="d-flex flex-row gap-2">
                            <div v-if="tags.length > 0" class="d-flex flex-row gap-1">
                                <div class="d-flex flex-row gap-1 align-items-center">
                                    <TagBadge
                                        v-for="tag in tags"
                                        :key="tag.id"
                                        :tag="tag"
                                    />
                                </div>
                            </div>
                            <div v-if="result.result?.users?.length > 0" class="d-flex flex-row gap-1">
                                <UserBadge
                                    v-for="pageUser in result.result.users"
                                    :user="pageUser.user"
                                    :key="pageUser.user.id"
                                    imageSize="xxs"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- this span contains the text with text marked; if any of the text matches the search term. -->
                    <p v-if="result.text != ''"><span v-html="result.text"></span></p>
                </div>
            </div>
        </div>

        <!-- there are sub results; e.g. if the page title and a section match -->
        <div v-if="result.subResults && result.subResults.length > 0" class="ms-3">
            <NestedSearchResult
                :subResults="result.subResults"
                @searchResultClick="(data) => $emit('searchResultClick', evt)"
            />
        </div>
    </div>
</template>
<script setup>
    import { computed } from 'vue';
    import { useRouter } from 'vue-router';
    import NestedSearchResult from '@/components/Search/NestedSearchResult.vue';
    import TagBadge from '@/components/Tag/TagBadge.vue';
    import UserBadge from '@/components/User/UserBadge.vue';
    import { usePageSectionAccessibilityHelper } from '@/composables/PageSectionAccessibilityHelper.js';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useSearchStore } from '@/stores/SearchStore.js';
    import { useTagStore } from '@/stores/TagStore.js';
    import { useThreadStore } from '@/stores/ThreadStore.js';

    const emit = defineEmits(['searchResultClick'])
    const props = defineProps({
        result: {
            type: Object,
            required: true
        },
        condensed: {
            type: Boolean,
            required: false,
            default: false,
        },
        isNested: {
            type: Boolean,
            required: false,
            default: false,
        },
    });
    const router = useRouter();
    const pageStore = usePageStore();
    const searchStore = useSearchStore();
    const tagStore = useTagStore();
    const threadStore = useThreadStore();

    /**
     * This computed property is used to get the tags from any search result.
     */
    const tags = computed(() => {
        if (props.result.result.tags) {
            return props.result.result.tags.map((tagPage) => tagPage.tag);
        }

        if (props.result.result.page) {
            return props.result.result.page.tags.map((tagPage) => tagPage.tag);
        }

        if (props.result.result.pageTab) {
            return props.result.result.pageTab.page.tags.map((tagPage) => tagPage.tag);
        }

        return [];
    });

    const onSearchResultClick = async () => {
        // this makes sure the search modal is closed among other things
        await emit('searchResultClick', props.projectUser);

        // we now extract where to navigate to
        let searchResultPage = null;
        let searchResultTask = null;

        if (props.result.type === 'Page') {
            searchResultPage = props.result.result;
        } else if (props.result.type === 'Task') {
            searchResultPage = props.result.result.page;
            searchResultTask = props.result.result;
        } else if (props.result.type === 'PageSection') {
            searchResultPage = props.result.result.pageTab.page;
        } else if (props.result.type === 'ThreadItem') {
            searchResultPage = props.result.result.thread.pageSectionContext.pageSection.pageTab.page;
            searchResultTask = searchResultPage.task;
        }

        if (!searchResultPage) {
            console.error('Could not find page for search result', props.result);
            return;
        }

        tagStore.openTagNavigationTreeForPage(searchResultPage); // this will open the tag navigation tree for the page (left nav bar)
        pageStore.setSelectedPage(searchResultPage).then((selectedPage) => {
            if (searchResultTask !== null) {
                router.push({ name: 'TasksDetail', params: { id: searchResultTask.id } });
            } else {
                router.push({ name: 'WikiPage', params: { id: selectedPage.id } });
            }
        });
    };

    const accessibilityHelper = usePageSectionAccessibilityHelper();
    const searchResultIcon = computed(() => {
        if (props.result.type === 'Page') {
            return 'file-lines';
        } else if (props.result.type === 'Task') {
            return 'pager';
        } else if (props.result.type === 'PageSection') {
            return accessibilityHelper.getIcon(props.result.result);
        } else if (props.result.type === 'ThreadItem') {
            return 'fa-comments';
        } else {
            return 'fa-question';
        }
    });
    const searchResultTooltip = computed(() => {
        if (props.result.type === 'Page') {
            return 'Found in page';
        } else if (props.result.type === 'Task') {
            return 'Found in task';
        } else if (props.result.type === 'PageSection') {
            return 'Found in page section: ' + accessibilityHelper.getTooltip(props.result.result);
        } else if (props.result.type === 'ThreadItem') {
            return 'Found in thread comment: ' + accessibilityHelper.getTooltip(props.result.result);
        } else {
            return props.result.type;
        }
    });
</script>

<style scoped>
    div.card {
        cursor: pointer !important;
    }

    /* especially for the shown search results */
    p {
        margin: 0;
        padding: 0;
    }
</style>