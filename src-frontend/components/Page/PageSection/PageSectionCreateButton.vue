<template>
    <div class="dropdown task-options">
        <h5 class="btn btn-primary dropdown-toggle m-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" @click.stop="">
            <font-awesome-icon icon="fa-solid fa-plus" />
        </h5>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" ref="checklistDropdown">
            <li><span class="dropdown-item" href="#" @click.stop="switchToPageSectionType('text')">Text</span></li>
            <li><span class="dropdown-item" href="#" @click.stop="switchToPageSectionType('checklist')">Checklist</span></li>
        </ul>
    </div>
</template>

<script setup>
    import { useTaskProvider } from '@/providers/TaskProvider.js';
    import { onMounted, ref } from 'vue';
    import PageSectionChecklist from '@/components/Page/PageSectionChecklist.vue';
    import PageSectionText from '@/components/Page/PageSectionText.vue';
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
        pageSectionStore.pageSectionAddType = type;
        pageSectionStore.pageSectionAddIndex = props.index;

        console.log(pageSectionStore.pageSectionAddType, pageSectionStore.pageSectionAddIndex);
    };
</script>