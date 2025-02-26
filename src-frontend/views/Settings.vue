<template>
    <div class="d-flex flex-row justify-content-center align-items-center">
        <CreateProjectDropdown />
    </div>

    <div class="row m-0 p-0">
        <div class="col-sm-4 col-lg-2 p-0 m-0 d-flex flex-column gap-3">
            <LoggedInUserBadge :user="userStore.currentUser" />

            <ul class="nav nav-pills nav-fill d-flex flex-column">
                <li class="nav-item">
                    <a
                        href="/logout"
                        v-tooltip="'Logout'"
                        class="nav-link inactive d-flex flex-row gap-3 align-items-center"
                    >
                        <font-awesome-icon :icon="['fas', 'right-from-bracket']" />
                        Logout
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-sm-8 col-lg-9 m-0 p-0 ps-5 d-flex flex-column gap-4">
            <h2>Your projects</h2>

            <div class="d-flex flex-row gap-2">
                <div
                    class="card project-card"
                    v-for="project in ownedProjects"
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
                            v-if="ownedProjects.length > 0 && (project.owner == userStore.currentUser.id || project.owner?.id === userStore.currentUser.id)"
                            :class="{
                                // the class 'card-options' is used to hide the card options and make them only visible on hover.
                                // however, when the project delete dropdown is visible, we want to show the card options at all cases to not make the buttons disappear.
                                'card-options': projectIdDeleteDropdownVisible !== project.id,
                            }"
                        >
                            <DeletionButton
                                label="project"
                                :showTooltip="false"
                                :darkMode="projectStore.selectedProject?.id === project.id"
                                @onConfirm="() => projectStore.deleteProject(project)"
                                @onShowDropdown="projectIdDeleteDropdownVisible = project.id"
                                @onHideDropdown="projectIdDeleteDropdownVisible = null"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { computed, ref, onMounted } from 'vue';
    import CreateProjectDropdown from '@/components/Project/CreateProjectDropdown.vue';
    import LoggedInUserBadge from '@/components/User/LoggedInUserBadge.vue';
    import DeletionButton from '@/components/Util/DeletionButton.vue';
    import { useUserStore } from '@/stores/UserStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const userStore = useUserStore();
    const projectStore = useProjectStore();

    const projectIdDeleteDropdownVisible = ref(null);
    
    const hasProfilePicture = computed(() => {
        return userStore.currentUser.profilePicture != null;
    });

    const ownedProjects = computed(() => {
        return userStore.currentUser.projectUsers
            .filter(projectUser => projectUser.project.owner == userStore.currentUser.id || projectUser.project.owner?.id === userStore.currentUser.id) // first, filter out the non-owner project users
            .map((projectUser) => projectUser.project); // then map the projectUser objects to the project objects as we only need the project objects
    });
</script>