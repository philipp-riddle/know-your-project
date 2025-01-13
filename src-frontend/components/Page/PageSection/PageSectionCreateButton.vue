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
                        <li class="nav-item"><p class="text-muted bold m-0 p-0">ADD CONTENT</p></li>
                        <li class="nav-item"><button class="nav-link inactive btn btn-sm p" type="button" @click.stop="() => switchToPageSectionType('text')">Text</button></li>
                        <li class="nav-item"><button class="nav-link inactive btn btn-sm p" type="button" @click.stop="() => switchToPageSectionType('checklist')">Checklist</button></li>
                        <li class="nav-item"><button class="nav-link inactive btn btn-sm p" type="button" @click.stop="() => switchToPageSectionType('upload')">Upload</button></li>
                        <li class="nav-item"><button class="nav-link inactive btn btn-sm p" type="button" @click.stop="() => switchToPageSectionType('embeddedPage')">Embed other page / task</button></li>
                        <li class="nav-item"><button class="nav-link inactive btn btn-sm p" type="button" @click.stop="() => switchToPageSectionType('aiPrompt')">Ask assistant</button></li>
                    </ul>
                </div>
            </div>
        </template>
    </VMenu>
</template>

<script setup>
    import { ref } from 'vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';
    import { usePageTabStore } from '@/stores/PageTabStore.js';

    const props = defineProps({
        index: {
            type: Number,
            required: false,
            default: null,
        },
    });
    const showPopover = ref(false);
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
            defaultObject = {
                pageSectionUpload: {
                    files: [],
                },
            };
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
        }

        pageSectionStore.createSection(pageTabStore.selectedTab.id, defaultObject);
    };
</script>