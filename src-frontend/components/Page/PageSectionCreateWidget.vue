<template>
    <div class="d-flex flex-row gap-3">
        <div class="dropdown task-options">
            <h5 class="btn btn-primary dropdown-toggle m-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" @click.stop="">
                <font-awesome-icon icon="fa-solid fa-plus" />
            </h5>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" ref="checklistDropdown">
                <li><span class="dropdown-item" href="#" @click.stop="switchToComment()">Text</span></li>
                <li><span class="dropdown-item" href="#" @click.stop="switchToChecklistCreate()">Checklist</span></li>
            </ul>
        </div>

        <div class="col-sm-6 d-flex flex-column gap-4">
            <div class="card border-success" v-if="createMode">
                <div class="card-body">
                    <div v-if="createMode == 'comment'">
                        <PageSectionText :onPageSectionSubmit="onPageWidgetCreate" />
                    </div>

                    <div v-if="createMode == 'checklist'">
                        <PageSectionChecklist :onPageSectionSubmit="onPageWidgetCreate" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { useTaskProvider } from '@/providers/TaskProvider.js';
    import { onMounted, ref } from 'vue';
    import PageSectionChecklist from '@/components/Page/PageSectionChecklist.vue';
    import PageSectionText from '@/components/Page/PageSectionText.vue';
    import PageSection from '@/components/Page/PageSection.vue';

    const props = defineProps({
        onCreate: {
            type: Function,
            required: true,
        },
        openedCreateDialogue: {
            type: String,
            required: false,
            default: null,
        },
    });
    const checklistDropdown = ref(null);
    const createMode = ref(props.openedCreateDialogue);

    const onCommentUpdate = (text) => {
        console.log('Comment updated');
        console.log(text);
    }

    const switchToComment = () => {
        checklistDropdown.value.classList.remove('show');
        createMode.value = 'comment';
    };

    const switchToChecklistCreate = () => {
        checklistDropdown.value.classList.remove('show');
        createMode.value = 'checklist';
    }

    const onPageWidgetCreate = (pageSection) => {
        props.onCreate(pageSection);
    };
</script>