<template>
    <div
        class="card project-card"
        :class="{'active': projectStore.selectedProject?.id === project.id}"
        :key="project.id"
        @click="projectStore.selectProject(project)"
    >
        <div class="card-body d-flex flex-column justify-content-end">
            <div class="d-flex flex-row justify-content-between gap-2">
                <p class="m-0">{{ project.name }}</p>

                <font-awesome-icon
                    v-if="projectStore.selectedProject?.id === project.id"
                    :icon="['fas', 'circle-check']"
                    v-tooltip="'This project is currently selected'"
                />
            </div>
            <div
                class="d-flex flex-row justify-content-end"
                :class="{
                    // the class 'card-options' is used to hide the card options and make them only visible on hover.
                    // however, when the project delete dropdown is visible, we want to show the card options at all cases to not make the buttons disappear.
                    'card-options': projectIdDeleteDropdownVisible !== project.id,
                }"
            >
                <DeletionButton
                    v-if="isOwner"
                    label="project"
                    :showTooltip="false"
                    :darkMode="projectStore.selectedProject?.id === project.id"
                    @onConfirm="() => projectStore.deleteProject(project)"
                    @onShowDropdown="projectIdDeleteDropdownVisible = project.id"
                    @onHideDropdown="projectIdDeleteDropdownVisible = null"
                />
                <button
                    v-else-if="projectUser != null"
                    class="btn btn-light-gray"
                    v-tooltip="'Leave project'"
                    @click.stop="removeUserFromProject"
                >
                    <font-awesome-icon :icon="['fas', 'user-minus']" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed, ref } from 'vue';
    import { useUserStore } from '@/stores/UserStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import DeletionButton from '@/components/Util/DeletionButton.vue';

    const props = defineProps({
        project: {
            type: Object,
            required: true,
        },
        projectUser: {
            type: Object,
            required: false,
        },
    });

    const userStore = useUserStore();
    const projectStore = useProjectStore();

    const projectIdDeleteDropdownVisible = ref(false);

    const isOwner = computed(() => {
        return props.project.owner == userStore.currentUser.id || props.project.owner?.id === userStore.currentUser.id;
    });

    const removeUserFromProject = () => {
        if (props.projectUser == null) {
            console.error('No project user provided to remove from project.');
            return;
        }

        projectStore.deleteProjectUser(props.projectUser.id);
    };
</script>