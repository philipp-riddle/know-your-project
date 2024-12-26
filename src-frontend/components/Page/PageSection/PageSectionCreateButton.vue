<template>
    <VMenu
        :distance="5"
        :shown="showPopover"
        @blur="showPopover = false"
    >
        <!-- This will be the popover reference (for the events and position) -->
        <div class="d-flex flex-row justify-content-center">
            <button @click.stop="showPopover = !showPopover" class="btn btn-sm btn-primary " v-tooltip="'Add sections'">
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
                    </ul>
                </div>
            </div>
        </template>
    </VMenu>
</template>

<script setup>
    import { useTaskProvider } from '@/providers/TaskProvider.js';
    import { onMounted, ref } from 'vue';
    import PageSectionChecklist from '@/components/Page/PageSection/Widget/PageSectionChecklist.vue';
    import PageSectionText from '@/components/Page/PageSection/Widget/PageSectionText.vue';
    import PageSection from '@/components/Page/PageSection/PageSection.vue';
    import { usePageSectionStore } from '@/stores/PageSectionStore.js';

    const props = defineProps({
        index: {
            type: Number,
            required: false,
            default: null,
        },
    });
    const checklistDropdown = ref(null);
    const showPopover = ref(false);
    const createMode = ref(props.openedCreateDialogue ?? 'text');
    const pageSectionStore = usePageSectionStore();

    const switchToPageSectionType = (type) => {
        showPopover.value = false;
        let defaultObject = {};

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
        }

        // @todo this is a very hacky way to create an object which is not yet saved in the database
        // we assign this ID to make it easier to mutate via VUE and to keep of track of all these non-initialized objects
        // defaultObject.id = 'NULL-' + Math.random(0, 1000);
        // pageSectionStore.displayedPageSections = pageSectionStore.displayedPageSections.filter((section) => !isNaN(section.id));
        pageSectionStore.displayedPageSections.push(defaultObject);
    };
</script>