<template>
    <div
        class="flex-fill d-flex flex-row align-items-center justify-content-end gap-4"
        :class="{
            // if we set this class the tags are only shown on hover.
            // if the page is already associated with tags or one of the tag dropdowns is open we always want to show the tag controls and its buttons.
            'section-options': !hasTags && !isDropdownVisible,
        }"
    >
        <div
            class="tags-container d-flex flex-row align-items-center flex-wrap gap-2"
        >
            <PageTagUserControl
                :tagPage="tagPage"
                v-for="tagPage in pageStore.selectedPage.tags"
                @hideDropdown="isDropdownVisible = false"
                @showDropdown="isDropdownVisible = true"
            />
        </div>

        <PageTagManagerConrol
            :page="page"
            @hideDropdown="isDropdownVisible = false"
            @showDropdown="isDropdownVisible = true"
        />
    </div>
</template>

<script setup>
    import { computed, ref, watch } from 'vue';
    import { usePageStore } from '@/stores/PageStore.js';
    import { useUserStore } from '@/stores/UserStore.js';
    import PageTagManagerConrol from '@/components/Page/PageControl/Tag/PageTagManagerControl.vue';
    import PageTagUserControl from '@/components/Page/PageControl/Tag/PageTagUserControl.vue';

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