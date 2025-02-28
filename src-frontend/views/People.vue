<template>
    <div class="d-flex flex-column">
        <div class="row mb-3">
            <div class="col-sm-6 offset-md-4 col-md-4 d-flex flex-row justify-content-center">
                <ul class="nav calendar-nav nav-pills d-flex flex-row justify-content-center align-items-center gap-3">
                    <li class="nav-item">
                        <AddProjectUser :project="projectStore.selectedProject" />
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="p-5" v-if="projectStore.selectedProject">
        <div class="row d-flex flex-column align-items-center gap-2">
            <div
                class="col-sm-12 col-md-6 col-lg-4 mb-2"
                v-for="projectUser in projectStore.selectedProject.projectUsers"
            >
                <ProjectUser
                    :projectUser="projectUser"
                    :project="projectStore.selectedProject"
                    @updateProjectUser="onUpdateProjectUser"
                />
            </div>

            <!-- invitations / pending users -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-2">
                <ProjectInvitationList />
            </div>
        </div>
        <div
            v-if="hasLoaded && projectStore.selectedProject.projectUsers.length === 1 && userStore.userProjectInvitations.length === 0"
            class="mt-4 d-flex flex-row justify-content-center align-items-center gap-2"
        >
            <div class="d-flex flex-column align-items-center">
                <h4 class="bold">No users invited yet.</h4>
                <p class="text-muted m-0">Why don't you invite any?</p>
            </div>
        </div>
    </div>

</template>
<script setup>
    import { ref, onMounted, nextTick } from 'vue';
    import { useRoute } from 'vue-router';
    import ProjectInvitationList from '@/components/Project/ProjectInvitationList.vue';
    import ProjectUser from '@/components/Project/ProjectUser.vue';
    import AddProjectUser from '@/components/Project/AddProjectUser.vue';
    import UserBadge from '@/components/User/UserBadge.vue';
    import { useProjectStore } from '@/stores/ProjectStore.js';
    import { useUserStore } from '@/stores/UserStore.js';

    const projectStore = useProjectStore();
    const userStore = useUserStore();
    const hasLoaded = ref(false);

    onMounted(() => {
        projectStore.getSelectedProject().then((selectedProject) => {
            userStore.getUserProjectInvitations(selectedProject.id);

            nextTick().then(() => {
                // prevent the user from showing the "no invitations yet" message if there are invitations and the user has not yet loaded.
                hasLoaded.value = true;
            });
        });
    });

    const onUpdateProjectUser = (projectUser) => {
        const index = projectStore.selectedProject.projectUsers.findIndex((pu) => pu.id === projectUser.id);
        projectStore.selectedProject.projectUsers[index] = projectUser;
    };
</script>