<template>
    <div class="p-5" v-if="currentProject">
        <div class="d-flex flex-row align-items-center gap-3">
            <h5 class="m-0">People in your project</h5>
            <AddProjectUser :project="currentProject" />
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-4">
                <div v-for="projectUser in currentProject.projectUsers">
                    <ProjectUser :projectUser="projectUser" />
                </div>
            </div>
        </div>

        <div class="row mt-4" v-if="userStore.userProjectInvitations">
            <div class="col-sm-12 col-md-6 col-lg-4">
                <h5>Pending invitations</h5>
                <div v-for="invitation in userStore.userProjectInvitations">
                    <div class="card">
                        <div class="card-body">
                            <p>{{ invitation.email }}</p>
                        </div>
                    </div>
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
const currentProject = ref(null);

onMounted(() => {
    projectStore.getSelectedProject().then((selectedProject) => {
        currentProject.value = selectedProject;

        userStore.getUserProjectInvitations(selectedProject.id);
    });
});

</script>