import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useUserStore } from '@/stores/UserStore';
import { fetchGetProject, fetchCreateProject, fetchDeleteProjectUser } from '@/fetch/ProjectFetcher';

export const useProjectStore = defineStore('project', () => {
    const projects = ref({});
    const selectedProject = ref(null);
    const userStore = useUserStore();

    async function getSelectedProject() {
        return new Promise((resolve) => {
            if (selectedProject.value) {
                resolve(selectedProject.value);
                return;
            }

            userStore.getCurrentUser().then((user) => {
                // fetch another serialized version from the GET project endpoint to get all project users correctly serialized, even the current user itself.
                // if we use the response from the user info endpoint, we will not get the current user in the project users list.
                getProject(user.selectedProject.id).then((project) => {
                    selectedProject.value = project;
                    resolve(project);
                });
            });
        });
    }

    async function getProject(projectId) {
        return new Promise((resolve) => {
            if (projects.value[projectId]) {
                resolve(projects.value[projectId]);
                return;
            }

            fetchGetProject(projectId).then((project) => {
                projects.value[projectId] = project;
                resolve(project);
                return;
            });
        });
    }

    async function deleteProjectUser(projectUserId) {
        return new Promise((resolve) => {
            fetchDeleteProjectUser(projectUserId).then(() => {
                resolve();
            });
        });
    }

    return {
        projects,
        getSelectedProject,
        selectedProject,
        getProject,
        deleteProjectUser
    };
});