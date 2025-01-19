import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useUserStore } from '@/stores/UserStore';
import { fetchGetProject, fetchCreateProject, fetchDeleteProjectUser } from '@/stores/fetch/ProjectFetcher';

export const useProjectStore = defineStore('project', () => {
    const projects = ref({});
    const selectedProject = ref(null);
    const selectedProjectPromise = ref(null); // used to store the promise of the current getProject call to avoid multiple calls at the same time
    const userStore = useUserStore();

    async function getSelectedProject() {
        return new Promise(async (resolve) => {
            if (selectedProject.value) {
                resolve(selectedProject.value);
                return;
            }

            // use the prefetched value from the window object if it exists
            if (window.selectedProject) {
                selectedProject.value = window.selectedProject;
                resolve(window.selectedProject);
                return;
            }

            const user = await userStore.getCurrentUser();

            // if we are already fetching the project, wait for the promise to resolve.
            // this avoids concurrent calls to the same endpoint.
            if (selectedProjectPromise.value) {
                selectedProjectPromise.value.then(() => {
                    resolve(selectedProject.value);
                });
                return;
            }

            // fetch another serialized version from the GET project endpoint to get all project users correctly serialized, even the current user itself.
            // if we use the response from the user info endpoint, we will not get the current user in the project users list.
            selectedProjectPromise.value = getProject(user.selectedProject.id).then((project) => {
                selectedProject.value = project;
                selectedProjectPromise.value = null;
                resolve(project);
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