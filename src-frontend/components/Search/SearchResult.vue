<template>
    <div class="card" @click.stop="onSearchResultClick">
        <div class="card-body">
            <p class="m-0 bold text-muted">{{ result.type }}</p>
            <h5>{{ searchResultName }}</h5>

            <SearchResultSummary :result="result" :searchTerm="searchTerm" />

            <small v-for="tag in tags">
                <span class="btn btn-sm me-2" :style="{'background-color': tag.color}" v-tooltip="'Tag: '+tag.name">&nbsp;&nbsp;&nbsp;</span>
            </small>
        </div>
    </div>

    <!-- there are sub results; e.g. if the page title and a section match -->
    <div v-if="result.subResults" class="ps-5 ms-3 pt-2">
        <NestedSearchResult
            :subResults="result.subResults"
            @searchResultClick="(data) => $emit('searchResultClick', evt)"
            :searchTerm="searchTerm"
        />
    </div>
</template>
<script setup>
    import { computed } from 'vue';
    import { useRouter } from 'vue-router';
    import NestedSearchResult from '@/components/Search/NestedSearchResult.vue';
    import SearchResultSummary from '@/components/Search/SearchResultSummary.vue';
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
    });
    const router = useRouter();
    const pageStore = usePageStore();
    const searchStore = useSearchStore();

    const searchResultName = computed(() => {
        const textForEmbedding = props.result.result.textForEmbedding;

        // the embeddings are sometimes in HTML format, so we need to extract the first text node to get a good title.
        // if the HTML cannot be parsed we simply use the raw text
        if (textForEmbedding.startsWith('<')) {
            var htmlEmbedding = document.createElement( 'html' );
            htmlEmbedding.innerHTML = textForEmbedding;

            var firstTextNode = htmlEmbedding.querySelector('body').firstChild;

            if (firstTextNode != null) {
                return firstTextNode.textContent;
            }
        }

        return textForEmbedding;
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
</script>

<style scoped>
    div.card {
        cursor: pointer !important;
    }
</style>