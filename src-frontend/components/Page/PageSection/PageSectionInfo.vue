<template>
    <VMenu :distance="5"
    >
        <!-- This will be the popover reference (for the events and position) -->
        <button class="btn btn-light-gray m-0 p-0">
            <font-awesome-icon :icon="['fas', 'circle-info']" />
        </button>

        <!-- This will be the content of the popover -->
        <template #popper>
            <div class="p-2 d-flex flex-row gap-3 justify-content-center">
                <div class="d-flex flex-row justify-content-start gap-2 align-content-center">
                    <font-awesome-icon :icon="['fas', 'calendar']" />
                    <p class="m-0">Created {{ dateFormatter.formatDateDistance(pageSection.createdAt) }}</p>
                </div>
                <div class="d-flex flex-row justify-content-start gap-2 align-content-center">
                    <font-awesome-icon :icon="['fas', 'user']" />
                    <p class="m-0">Created by {{ pageSection.author.email }}</p>
                </div>
            </div>
        </template>
    </VMenu>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { useDateFormatter } from '@/composables/DateFormatter.js';

    const props = defineProps({
        pageSection: {
            type: Object,
            required: true,
        },
    });
    const pageSectionStore = usePageSectionStore();
    const dateFormatter = useDateFormatter();

    const onPageSectionDeleteClick = async () => {
        showPopover.value = false;
        await pageSectionStore.deleteSection(props.pageSection);
    };
</script>