import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useUserStore } from '@/stores/UserStore';
import { fetchGetProject, fetchCreateProject, fetchSelectProject, fetchDeleteProject, fetchDeleteProjectUser } from '@/stores/fetch/ProjectFetcher';

export const useProjectStore = defineStore('project', () => {
    const projects = ref({});
    const selectedProject = ref(null);
    const selectedProjectPromise = ref(null); // used to store the promise of the current getProject call to avoid multiple calls at the same time
    const userStore = useUserStore();

    function setup() {
        // use the prefetched value from the window object if it exists
        if (window.selectedProject != null) {
            selectedProject.value = window.selectedProject;
        }
    }

    async function getSelectedProject() {
        return new Promise(async (resolve) => {
            if (selectedProject.value) {
                resolve(selectedProject.value);
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

    async function createProject(name, selectAfterCreating=false) {
        return new Promise((resolve) => {
            fetchCreateProject(name, selectAfterCreating).then((project) => {
                if (selectAfterCreating) {
                    resolve(project); // if it is selected right away, resolve the promise and continue
                } else {
                    window.location.reload(); // reload the page to reload the selected project if it is not selected right away
                }
            });
        });
    }

    async function selectProject(project) {
        fetchSelectProject(project.id).then(() => {
            window.location.reload(); // reload the page to reload the selected project and everything else
        });
    }

    async function deleteProject(project) {
        fetchDeleteProject(project.id).then(() => {
            window.location.reload(); // reload the page to reload the selected project and everything else
        });
    }

    async function deleteProjectUser(projectUserId) {
        fetchDeleteProjectUser(projectUserId).then(() => {
            selectedProject.value.projectUsers = selectedProject.value.projectUsers.filter((pu) => pu.id !== projectUserId);
            window.location.reload();
        });
    }

    return {
        projects,
        selectedProject,
        setup,
        getSelectedProject,
        getProject,
        createProject,
        selectProject,
        deleteProject,
        deleteProjectUser,
    };
});