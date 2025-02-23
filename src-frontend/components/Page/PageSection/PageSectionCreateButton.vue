<template>
    <VMenu
        :distance="5"
        @blur="showPopover = false"
    >
        <!-- This will be the popover reference (for the events and position) -->
        <div class="d-flex flex-row justify-content-center">
            <button
                @click.stop="showPopover = !showPopover"
                class="btn btn-sm btn-primary "
                v-tooltip="'Add content'"
            >
            
                <font-awesome-icon class="white" :icon="['fas', 'plus']" />
            </button>
        </div>

        <!-- This will be the content of the popover -->
        <template #popper>
            <div class="p-2">
                <div class="d-flex flex-column justify-content-center">
                    <ul class="nav nav-pills nav-fill d-flex flex-column">
                        <li
                            class="nav-item"
                            v-for="type in pageSectionTypes"
                        >
                            <button class="nav-link inactive btn btn-sm p d-flex flex-row gap-4" type="button" @click.stop="() => switchToPageSectionType(type)">
                                <font-awesome-icon
                                    :icon="['fas', PageSectionAccessibilityHelper.getIconFromTitle(type)]"
                                    class="black"
                                />
                                <p class="m-0">{{ PageSectionAccessibilityHelper.getTitle(type) }}</p>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </template>
    </VMenu>
</template>

<script setup>
    import { ref } from 'vue';
    import { usePageSectionAccessibilityHelper } from '@/composables/PageSectionAccessibilityHelper.js';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { usePageTabStore } from '@/stores/PageTabStore.js';
    
    const pageSectionTypes = [
        'text',
        'checklist',
        'upload',
        'url',
        'summary',
        'aiPrompt',
        'embeddedPage',
        'calendarEvent',
    ];
    const showPopover = ref(false);
    const PageSectionAccessibilityHelper = usePageSectionAccessibilityHelper();
    const pageSectionStore = usePageSectionStore();
    const pageTabStore = usePageTabStore();

    const switchToPageSectionType = (type) => {
        showPopover.value = false;
        let defaultObject = {};

        // @todo move this code to a composable or some place where it is better found, managed, and maintained.
        if (type == 'text') {
            defaultObject = {
                pageSectionText: {
                    content: '',
                },
            };
        } else if (type == 'checklist') {
            defaultObject = {
                pageSectionChecklist: {
                    name: 'Checklist',
                    pageSectionChecklistItems: [],
                },
            };
        } else if (type == 'upload') {
            openFileExplorerForUpload();

            return;
        } else if (type == 'embeddedPage') {
            defaultObject = {
                embeddedPage: {
                    page: null,
                },
            };
        } else if (type == 'aiPrompt') {
            defaultObject = {
                aiPrompt: {
                    prompt: {
                        promptText: '',
                    },
                },
            };
        } else if (type == 'summary') {
            defaultObject = {
                pageSectionSummary: {
                    prompt: {
                        promptText: '',
                    },
                },
            };
        } else if (type == 'url') {
            defaultObject = {
                pageSectionURL: {
                    url: '',
                },
            };
        } else if (type == 'calendarEvent') {
            defaultObject = {
                calendarEvent: {
                    calendarEvent: null,
                },
            };
        }

        pageSectionStore.createSection(pageTabStore.selectedTab.id, defaultObject);
    };

    const openFileExplorerForUpload = () => {
        let input = document.createElement('input');
        // allow multiple files to be uploaded
        input.multiple = true;
        input.type = 'file';
        input.onchange = () => {
            let files = input.files?? null;

            if (files) {
                pageSectionStore.uploadSection(pageTabStore.selectedTab.id, files);
            }
        };
        input.click();
    };
</script>