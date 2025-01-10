import { defineStore } from 'pinia';
import { usePageStore } from './PageStore';
import { ref, watch } from 'vue';
import { fetchTasks, fetchTask, fetchUpdateTask, fetchCreateTask, fetchDeleteTask, fetchMoveTask, fetchChangeOrder } from "@/stores/fetch/TaskFetcher.js";

export const useTaskStore = defineStore('task', () => {
    const tasks = ref({});
    const pageStore = usePageStore();

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

    const getTasks = (stepType) => {
        return new Promise((resolve) => {
            if (tasks.value[stepType] ?? null) { // already in the store
                resolve(tasks.value[stepType] ?? null);
            } else {
                fetchTasks(stepType).then((tasks) => {
                    setTasks(stepType, tasks);
                    resolve(tasks);
                });
            }
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

    function setTasks(stepType, tasksList) {
        tasks.value[stepType] = tasksList;
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
                addTask(createdTask);

                pageStore.addPagesAndTagsToStore([createdTask.page], []); // pass [] as tags to add the tag to the uncategorized list
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
        if (!tasks.value[task.stepType]) {
            return;
        }

        fetchDeleteTask(task.id).then(() => {
            const index = tasks.value[task.stepType].findIndex((t) => t.id === task.id);
            tasks.value[task.stepType].splice(index, 1);

            if (task.page) {
                pageStore.removePage(task.page);
            }
        });
        
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
            fetchMoveTask(originalTask, stepType, index).then((task) => {
                if (!tasks.value[stepType]) {
                    resolve(task);

                    return; // if the tasks are not loaded yet the loading will do the rest
                }

                // if the task is not draggable we need to update it in the store to reflect the change in the UI
                if (!isDraggable) {
                    tasks.value[originalTask.stepType].splice(tasks.value[originalTask.stepType].findIndex((t) => t.id === originalTask.id), 1);
                    tasks.value[stepType] = [
                        ...tasks.value[stepType].slice(0, index),
                        task,
                        ...tasks.value[stepType].slice(index),
                    ];
                }

                if (task.id == pageStore.selectedPage?.task?.id) {
                    pageStore.selectedPage.task = task;
                }

                resolve(task);
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
        setTasks,
        changeTaskOrder,
        createTask,
        addTask,
        updateTask,
        deleteTask,
        moveTask,
    };
});
