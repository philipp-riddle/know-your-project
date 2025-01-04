<template>
    <div class="p-5" v-if="projectStore.selectedProject">
        <h5 class="mb-3">People in your project</h5>

        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-4 mb-2" v-for="projectUser in projectStore.selectedProject.projectUsers">
                <ProjectUser
                    :projectUser="projectUser"
                    :project="projectStore.selectedProject"
                    @deleteProjectUser="onDeleteProjectUser"
                    @updateProjectUser="onUpdateProjectUser"
                />
            </div>
        </div>

        <div class="row mt-5" v-if="userStore.userProjectInvitations">
            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="d-flex flex-row align-items-center gap-3 mb-3">
                    <h5 class="m-0">Sent invitations</h5>
                    <AddProjectUser :project="projectStore.selectedProject" />
                </div>

                <div v-if="userStore.userProjectInvitations?.length > 0" v-for="invitation in userStore.userProjectInvitations">
                    <div class="card">
                        <div class="card-body d-flex flex-row justify-content-between align-items-center">
                            <h5>{{ invitation.email }}</h5>
                            <div class="card-options">
                                <button class="btn btn-sm" v-tooltip="'Cancel invitation'" @click="() => userStore.deleteUserProjectInvitation(invitation.id)">
                                    <font-awesome-icon :icon="['fas', 'trash']" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <p>No pending invitations.</p>
                </div>
            </div>
        </div>
    </div>

</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import ProjectUser from '@/components/Project/ProjectUser.vue';
import AddProjectUser from '@/components/Project/AddProjectUser.vue';
import { useProjectStore } from '@/stores/ProjectStore.js';
import { useUserStore } from '@/stores/UserStore.js';

const projectStore = useProjectStore();
const userStore = useUserStore();

onMounted(() => {
    projectStore.getSelectedProject().then((selectedProject) => {
        userStore.getUserProjectInvitations(selectedProject.id);
    });
});

const onDeleteProjectUser = (projectUser) => {
    projectStore.selectedProject.value.projectUsers = projectStore.selectedProject.value.projectUsers.filter((pu) => pu.id !== projectUser.id);
};

const onUpdateProjectUser = (projectUser) => {
    const index = projectStore.selectedProject.projectUsers.findIndex((pu) => pu.id === projectUser.id);
    projectStore.selectedProject.projectUsers[index] = projectUser;
};

</script>