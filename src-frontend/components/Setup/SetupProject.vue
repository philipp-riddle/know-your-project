<template>
    <div class="w-100">
        <div class="d-flex flex-column align-items-center">
            <h2>Setting up project</h2>
            <p class="text-muted">This is where your colleagues go</p>
        </div>

        <div class="row w-100">
            <div class="col-sm-12 offset-md-3 col-md-6 d-flex flex-column align-items-center gap-3">
                <div v-if="projectStore.selectedProject == null" class="card w-100 project-card">
                    <div class="card-body">
                        <label for="projectName" class="text-muted">Project name</label>
                        <div class="d-flex flex-row justify-content-between gap-3">
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Project name"
                                id="projectName"
                                v-model="projectName"
                                @keyup.enter="submitProject"
                            />
                            <button
                                class="btn btn-dark-gray"
                                :disabled="!canSubmitProject"
                                @click="submitProject"
                            >
                                <font-awesome-icon :icon="['fas', 'circle-plus']" />
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="card setup-card w-50">
                    <div class="card-body d-flex flex-column gap-4">
                        <div class="d-flex flex-row justify-content-between gap-3">
                            <h3 class="m-0">{{ projectStore.selectedProject.name }}</h3>
                            <button class="btn btn-lg btn-dark-gray">
                                <font-awesome-icon :icon="['fas', 'check']" />
                            </button>
                        </div>
                        
                        <p class="m-0">Your project was created - want to invite some colleagues?</p>

                        <div class="d-flex flex-row justify-content-start">
                            <AddProjectUser :project="projectStore.selectedProject" />
                        </div>
                    </div>
                </div>

                <div class="w-50">
                    <ProjectInvitationList/>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed, ref, onMounted } from 'vue';
    import AddProjectUser from '@/components/Project/AddProjectUser.vue';
    import ProjectInvitationList from '@/components/Project/ProjectInvitationList.vue';
    import { useUserStore } from '@/stores/UserStore.js';
    import { useProjectStore } from '@/stores/ProjectStore.js';

    const userStore = useUserStore();
    const projectStore = useProjectStore();

    const projectName = ref('');

    const canSubmitProject = computed(() => {
        return projectName.value.trim() !== ''
    });

    onMounted(() => {
        if (projectStore.selectedProject !== null) {
            userStore.getUserProjectInvitations(projectStore.selectedProject.id);
        }
    })

    const submitProject = () => {
        if (!canSubmitProject.value) {
            return;
        }

        // create the project and select it right away; this saves the project choice of the user.
        projectStore.createProject(projectName.value.trim(), true).then((createdProject) => {
            projectStore.selectedProject = createdProject; // do this to select the project right away; we do this only in the setup.
        });
    }
</script>