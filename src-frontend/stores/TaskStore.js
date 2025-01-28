import { defineStore } from 'pinia';
import { usePageStore } from './PageStore';
import { useProjectStore } from './ProjectStore';
import { ref, watch } from 'vue';
import { fetchTasks, fetchTask, fetchUpdateTask, fetchCreateTask, fetchDeleteTask, fetchMoveTask, fetchChangeOrder } from "@/stores/fetch/TaskFetcher.js";

export const useTaskStore = defineStore('task', () => {
    const tasks = ref({});
    const pageStore = usePageStore();
    const projectStore = useProjectStore();

    /**
     * If the selected page changes we need to update the respective task in the store -
     * This makes it very easy to sync between the stores as we only want to update the selected page and work with that.
     */
    watch(() => pageStore.selectedPage, (page) => {
        if (page && page.task && tasks.value[page.task.stepType]) {
            // find task and update the page item in it
            tasks.value[page.task.stepType] = tasks.value[page.task.stepType].map((t) => {
                if (t.id === page.task.id) {
                    const task = {
                        ...t,
                        // we do this to have a completely serialised page object in the updated task.
                        // otherwise it is only an ID as the API handles circular dependencies by only serialising their ID.
                        page: page,
                    };

                    return task; // @todo does not work
                }

                return t;
            });
        } 
    }, { deep: true }); // deep watch is needed to watch the object's nested properties

    const getTasks = async (tags) => {
        tags = tags ?? null; // if the tags were not specified we assign NULL to it
        const selectedProject = await projectStore.getSelectedProject();

        return new Promise((resolve) => {
            fetchTasks(selectedProject.id, tags).then((fetchedTasks) => {
                tasks.value = {}; // reset store
                // fetch all the tasks and once and then assign them to the appropriate stepType
                for (let i = 0; i < fetchedTasks.length; i++) {
                    const task = fetchedTasks[i];

                    if (!tasks.value[task.stepType]) {
                        tasks.value[task.stepType] = [];
                    }

                    tasks.value[task.stepType].push(task);
                }

                // finally, sort the tasks by their orderIndex
                for (const stepType in tasks.value) {
                    tasks.value[stepType] = tasks.value[stepType].sort((a, b) => a.orderIndex - b.orderIndex);
                }

                resolve(fetchedTasks);
            });
        });
    };

    const getTask = (taskId) => {
        return new Promise((resolve) => {
            for (const stepType in tasks.value) {
                const task = tasks.value[stepType].find((t) => t.id == taskId);

                if (task) {
                    resolve(task);
                    return;
                }
            }
    
            // if the task is not in the store, fetch it from the server
            fetchTask(taskId).then((task) => {
                addTask(task);
                resolve(task);
            });
        });
    };
    const getTaskFromStep = (stepType, taskId) => {
        if (!tasks.value[stepType]) {
            return null;
        }
        return tasks.value[stepType].find((t) => t.id === taskId);
    };

    async function setSelectedTask(task) {
        return new Promise(async (resolve) => {
            pageStore.setSelectedPage(task.page).then(() => {
                pageStore.selectedPage.task = task;
                resolve(null);
            })
        });
    }

    async function changeTaskOrder(workflowStepType, order) {
        return new Promise((resolve) => {
            fetchChangeOrder(workflowStepType, order).then((tasks) => {
                resolve(tasks);
            });
        });
    }

    async function createTask(stepType, name) {
        return new Promise((resolve) => {
            fetchCreateTask(stepType, name).then((createdTask) => {
                var createdTaskObject = createdTask.page;
                createdTaskObject.task = createdTask; // because of serialisation we need to add the task to the page object

                addTask(createdTask);
                pageStore.addPagesAndTagsToStore([createdTaskObject], []); // pass [] as tags to add the tag to the uncategorized list
                resolve(createdTask);
            });
        });
    }

    function addTask(task) {
        if (getTaskFromStep(task.stepType, task.id)) {
            return;
        }

        if (!tasks.value[task.stepType]) {
            tasks.value[task.stepType] = [];
        }

        tasks.value[task.stepType].push(task);
    }

    function updateTask(task) {
        // const tasksList = tasks.value[task.stepType] ?? [];
        // const index = tasksList.findIndex((t) => t.id === task.id);

        // if (index === -1) {
        //     tasksList.push(task);
        // } else {
        //     tasksList[index] = task;
        // }

        return new Promise((resolve) => {
            fetchUpdateTask(task).then((taskUpdate) => {
                // taskStore.updateTask(task); // we do NOT sync here on purpose - otherwise user content can get deleted again
                resolve(taskUpdate);
            });
        });
    }

    function deleteTask(task) {
        removeTaskFromStore(task);
        
        if (task.page) {
            pageStore.removePage(task.page);
        }

        fetchDeleteTask(task.id);
    }

    function removeTaskFromStore(task) {
        if (!tasks.value[task.stepType]) {
            return;
        }

        const index = tasks.value[task.stepType].findIndex((t) => t.id === task.id);
        tasks.value[task.stepType].splice(index, 1);
    }

    /**
     * Moves a task to a new stepType and index.
     * 
     * @param {object} originalTask 
     * @param {object} stepType Discover / Define / Develop / Deliver
     * @param {Number|undefined} index if provided the task will be inserted at that index
     * @param {Boolean|undefined} isDraggable if the move comes from a draggable event we need to handle it differently as the task is already moved in the UI
     * @returns 
     */
    function moveTask(originalTask, stepType, index, isDraggable) {
        return new Promise((resolve) => {
            fetchMoveTask(originalTask, stepType, index).then((movedTask) => {
                if (originalTask.stepType !== stepType) {
                    tasks.value[originalTask.stepType] = tasks.value[originalTask.stepType].filter((t) => t.id !== originalTask.id);
                }

                movedTask.orderIndex = movedTask.orderIndex - 1; // we do this to account for the 1-based index in the UI

                // replace the task in the store
                var hasTask = false;

                if (!tasks.value[stepType]) {
                    tasks.value[stepType] = [];
                } else {
                    tasks.value[movedTask.stepType] = tasks.value[movedTask.stepType].map((task) => {
                        if (task.id === movedTask.id) {
                            tasks.value[stepType].push(movedTask);
                            hasTask = true;
                            return task;
                        }

                        return task;
                    });
                }

                if (!hasTask) {
                    tasks.value[stepType].push(movedTask);
                }

                // now, reorder them by their order index
                tasks.value[stepType] = tasks.value[stepType].sort((a, b) => a.orderIndex - b.orderIndex);

                resolve(movedTask);
            });
        });
    }

    return {
        tasks,
        pageStore,
        getTasks,
        getTask,
        getTaskFromStep,
        setSelectedTask,
        changeTaskOrder,
        createTask,
        addTask,
        updateTask,
        deleteTask,
        removeTaskFromStore,
        moveTask,
    };
});
