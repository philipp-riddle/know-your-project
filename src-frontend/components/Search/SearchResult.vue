<template>
    <div class="card" @click.stop="onSearchResultClick">
        <div class="card-body d-flex flex-row justify-content-between gap-3">
            <div class="d-flex flex-row gap-3 align-items-center justify-content-top">
                <span
                    class="btn m-0 p-0"
                    :class="{'btn-sm': condensed, 'btn-lg': !condensed}"
                >
                    <font-awesome-icon :icon="['fas', searchResultIcon]" v-tooltip="searchResultTooltip" />
                </span>
                <div>
                    <p v-if="condensed" class="m-0 p-0 black">{{ searchResultName }}</p>
                    <h5 v-else class="m-0 p-0 black">{{ searchResultName }}</h5>

                    <SearchResultSummary v-if="!condensed" :result="result" :searchTerm="searchTerm" />
                </div>
            </div>

            <div class="d-flex flex-row gap-1 align-items-center">
                <small v-for="tag in tags">
                    <span class="btn btn-sm me-2" :style="{'background-color': tag.color}" v-tooltip="'Tag: '+tag.name">&nbsp;&nbsp;&nbsp;</span>
                </small>
            </div>
        </div>
    </div>

    <!-- there are sub results; e.g. if the page title and a section match -->
    <div v-if="result.subResults" class="ps-5 ms-3 pt-2">
    </div>
</template>
<script setup>
    import { computed } from 'vue';
    import { useRouter } from 'vue-router';
    import NestedSearchResult from '@/components/Search/NestedSearchResult.vue';
    import SearchResultSummary from '@/components/Search/SearchResultSummary.vue';
    import { usePageSectionAccessibilityHelper } from '@/composables/PageSectionAccessibilityHelper.js';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useSearchStore } from '@/stores/SearchStore.js';

    const emit = defineEmits(['searchResultClick'])
    const props = defineProps({
        result: {
            type: Object,
            required: true
        },
        searchTerm: {
            type: String,
            required: true
        },
        condensed: {
            type: Boolean,
            required: false,
            default: false,
        },
    });
    const router = useRouter();
    const pageStore = usePageStore();
    const searchStore = useSearchStore();

    const searchResultName = computed(() => {
        const type = props.result.type;

        if (type === 'Page') {
            return props.result.result.name;
        } else if (type === 'Task') {
            return props.result.result.page.name;
        } else if (type === 'PageSection') {
            return props.result.result.pageTab.page.name;
        }

        return type;
    });

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

        if (props.result.type === 'Page') {
            pageStore.setSelectedPage(props.result.result).then((page) => {
                router.push({ name: 'Page', params: { id: page.id } });
            });
        } else if (props.result.type === 'Task') {
            pageStore.setSelectedPage(props.result.result.page).then((page) => {
                router.push({ name: 'TasksDetail', params: { id: props.result.result.id } });
            });
        } else if (props.result.type === 'PageSection') {
            pageStore.setSelectedPage(props.result.result.pageTab.page).then((page) => {
                router.push({ name: 'Page', params: { id: page.id } });
            });
        }
    };

    const accessibilityHelper = usePageSectionAccessibilityHelper();
    const searchResultIcon = computed(() => {
        if (props.result.type === 'Page') {
            return 'fa-file-alt';
        } else if (props.result.type === 'Task') {
            return 'fa-tasks';
        } else if (props.result.type === 'PageSection') {
            return accessibilityHelper.getIcon(props.result.result);
        }
    });
    const searchResultTooltip = computed(() => {
        if (props.result.type === 'Page') {
            return 'Found in page';
        } else if (props.result.type === 'Task') {
            return 'Found in task';
        } else if (props.result.type === 'PageSection') {
            return 'Found in page section: ' + accessibilityHelper.getTooltip(props.result.result);
        } else {
            return props.result.type;
        }
    });
</script>

<style scoped>
    div.card {
        cursor: pointer !important;
    }
</style>