<template>
    <div v-if="currentProject">
        <div class="d-flex flex-row align-items-center gap-3">
            <h5 class="m-0">Users in your project</h5>
            <AddProjectUser :project="currentProject" />
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-4" v-for="projectUser in currentProject.projectUsers">
                <ProjectUser :projectUser="projectUser" />
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

const projectStore = useProjectStore();
const currentProject = ref(null);

onMounted(() => {
    projectStore.getSelectedProject().then((selectedProject) => {
        currentProject.value = selectedProject;
    });
});

</script>