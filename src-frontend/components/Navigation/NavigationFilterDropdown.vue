<template>
    <div class="nav-link btn-dark d-flex flex-row align-items-center gap-2">
        <VDropdown
            v-model:shown="isDropdownVisible"
        >
            <button
                class="nav-link btn btn-dark position-relative"
                v-tooltip="'Filter project'"
                :class="{
                    'dark-gray': isDropdownVisible || filterStore.filterTags.length > 0,
                    'inactive': !isDropdownVisible && filterStore.filterTags.length === 0,
                }"
            >
                <font-awesome-icon :icon="['fas', 'filter']" />

                <span v-if="filterStore.filterTags.length > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ filterStore.filterTags.length }}
                </span>
            </button>

            <template #popper>
                <div class="p-2" style="min-width: 15rem">
                    <TagDialogue
                        :tags="filterStore.filterTags"
                        :showEditControls="false"
                        :showCreateControls="false"
                        @addTag="(tag) => onTagFilterAdd(tag)"
                        @removeTag="(tag) => onTagFilterRemove(tag)"
                    />
                </div>
            </template>
        </VDropdown>
    </div>
</template>

<script setup>
    import { ref } from 'vue'; 
    import TagDialogue from '@/components/Tag/TagDialogue.vue';
    import { useFilterStore } from '@/stores/FilterStore.js';

    const filterStore = useFilterStore();
    const isDropdownVisible = ref(false);

    const onTagFilterAdd = (tag) => {
        filterStore.addFilterTag(tag);
    };

    const onTagFilterRemove = (tag) => {
        filterStore.removeFilterTag(tag);
    };
</script>