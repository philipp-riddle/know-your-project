<template>
    <div class="card" @click.stop="onSearchResultClick">
        <div class="card-body">
            <small class="text-muted">{{ result.type }}</small>
            <p>{{ searchResultName }}</p>
        </div>
    </div>
</template>
<script setup>
    import { computed } from 'vue';
    import { useRouter } from 'vue-router';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useSearchStore } from '@/stores/SearchStore.js';

    const props = defineProps({
        result: {
            type: Object,
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

    const onSearchResultClick = () => {
        searchStore.isSearching = false; // this hides the search modal

        if (props.result.type === 'Page') {
            pageStore.setSelectedPage(props.result.result);
            router.push({ name: 'Page', params: { id: props.result.result.id } });
        } else if (props.result.type === 'Task') {
            pageStore.setSelectedPage(props.result.result.page);
            router.push({ name: 'TasksDetail', params: { id: props.result.id } });
        } else if (props.result.type === 'PageSection') {
            pageStore.setSelectedPage(props.result.result.pageTab.page);
            router.push({ name: 'Page', params: { id: props.result.result.pageTab.page.id } });
        }
    };
</script>

<style scoped>
    div.card {
        cursor: pointer !important;
    }
</style>