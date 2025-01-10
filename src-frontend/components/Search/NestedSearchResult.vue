<template>
    <div class="nested-search-result d-flex flex-column gap-2">
        <SearchResult
            v-for="subResult in props.subResults"
            :key="subResult.id"
            :result="subResult"
            :searchTerm="searchTerm"
            @searchResultClick="$emit('searchResultClick', subResult)"
        />
    </div>
</template>
<script setup>
    import SearchResult from '@/components/Search/SearchResult.vue';

    const emits = defineEmits(['searchResultClick']);
    const props = defineProps({
        subResults: {
            type: Array,
            required: true
        },
        searchTerm: {
            type: String,
            required: true
        }
    });
</script>