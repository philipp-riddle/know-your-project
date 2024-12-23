<template>
    <VDropdown
        :distance="5"
        :shown="showPopover"
    >
        <!-- This will be the popover reference (for the events and position) -->
        <button class="btn btn-sm btn-dark nav-create-item" v-tooltip="'Details about this section'">
            <span class="white"><font-awesome-icon :icon="['fas', 'info']" /></span>
        </button>

        <!-- This will be the content of the popover -->
        <template #popper>
            <div class="m-4">
                <div class="d-flex flex-column justify-content-center">
                    <ul class="nav nav-pills nav-fill d-flex flex-column gap-1">
                        <li class="nav-item">Created at {{ pageSection.createdAt }}</li>
                        <li class="nav-item">Updated by {{ pageSection.author.email }}</li>
                        <li class="nav-item"><button class="btn btn-danger" @click.stop="onPageSectionDeleteClick">Delete</button></li>
                    </ul>
                </div>
            </div>
        </template>
    </VDropdown>
</template>

<script setup>
    import { defineProps, ref, computed, onMounted } from 'vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';

    const props = defineProps({
        pageSection: {
            type: Object,
            required: true,
        },
    });
    const showPopover = ref(false);
    const pageSectionStore = usePageSectionStore();

    const onPageSectionDeleteClick = async () => {
        showPopover.value = false;
        await pageSectionStore.deleteSection(props.pageSection);
    };
</script>