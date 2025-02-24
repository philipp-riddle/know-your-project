<template>
    <div class="flex-fill d-flex flex-row align-items-center justify-content-end p-1 gap-2">
        <PageTagManagerControl
            :page="page"
            @hideDropdown="isDropdownVisible = false"
            @showDropdown="isDropdownVisible = true"
        />
        <div
            v-if="pageStore.selectedPage.tags?.length > 0 || isDropdownVisible"
            class="tags-container d-flex flex-row align-items-center flex-wrap gap-2"
        >
            <TagBadge
                v-for="tagPage in pageStore.selectedPage.tags"
                :tag="tagPage.tag"
                :tagWrapperEntity="tagPage"
            />
        </div>
    </div>
</template>

<script setup>
    import { computed, ref, watch } from 'vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useUserStore } from '@/stores/UserStore.js';
    import PageTagManagerControl from '@/components/Page/PageControl/Tag/PageTagManagerControl.vue';
    import TagBadge from '@/components/Tag/TagBadge.vue';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
    });
    const pageStore = usePageStore();
    const userStore = useUserStore();

    // indicates if any of the tag controls is shown.
    // we need this to display the tag controls and its buttons always when a dropdown is shown (if there are no tags the tag button is otherwise only shown on hover).
    const isDropdownVisible = ref(false);

    const hasTags = computed(() => pageStore.selectedPage?.tags.length > 0);
</script>