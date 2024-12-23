<template>
    <div class="dropdown task-options">
        <h5 class="btn btn-primary dropdown-toggle m-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" @click.stop="">
            <font-awesome-icon icon="fa-solid fa-plus" />
        </h5>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" ref="checklistDropdown">
            <li><span class="dropdown-item" href="#" @click.stop="switchToPageSectionType('text')">Text</span></li>
            <li><span class="dropdown-item" href="#" @click.stop="switchToPageSectionType('checklist')">Checklist</span></li>
            <li><span class="dropdown-item" href="#" @click.stop="switchToPageSectionType('upload')">Upload</span></li>
        </ul>
    </div>
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
    const createMode = ref(props.openedCreateDialogue ?? 'text');
    const pageSectionStore = usePageSectionStore();

    const switchToPageSectionType = (type) => {
        checklistDropdown.value.classList.remove('show');
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
        }

        // @todo this is a very hacky way to create an object which is not yet saved in the database
        // we assign this ID to make it easier to mutate via VUE and to keep of track of all these non-initialized objects
        // defaultObject.id = 'NULL-' + Math.random(0, 1000);
        // pageSectionStore.displayedPageSections = pageSectionStore.displayedPageSections.filter((section) => !isNaN(section.id));
        pageSectionStore.displayedPageSections.push(defaultObject);
    };
</script>