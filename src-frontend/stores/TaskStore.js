import { defineStore } from 'pinia';
import { usePageStore } from './PageStore';
import { ref } from 'vue';
import { fetchTasks, fetchTask, fetchUpdateTask, fetchCreateTask, fetchDeleteTask, fetchMoveTask, fetchChangeOrder } from "@/fetch/TaskFetcher.js";

export const useTaskStore = defineStore('task', () => {
    const tasks = ref({});
    const selectedTask = ref(null);
    const pageStore = usePageStore();

    const getSelectedTask = () => selectedTask.value;
    const getTasks = (stepType) => tasks.value[stepType] ?? null;
    const getTask = (taskId) => {
        for (const stepType in tasks.value) {
            const task = tasks.value[stepType].find((t) => t.id == taskId);
            if (task) {
                return task;
            }
        }
        return null;
    };
    const getTaskFromStep = (stepType, taskId) => {
        if (!tasks.value[stepType]) {
            return null;
        }
        return tasks.value[stepType].find((t) => t.id === taskId);
    };

    async function setSelectedTask(task) {
        return new Promise(async (resolve) => {
            selectedTask.value = task;

            // load the underlying task page also into the store; make sure to not use any cached version (2nd argument = true, forceRefresh)
            await pageStore.setSelectedPage(task.page, true);

            // resolve with null to indicate that the task was set
            resolve(null);
        });
    }

    function setTasks(stepType, tasksList) {
        tasks.value[stepType] = tasksList;
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
        const tasksList = tasks.value[task.stepType] ?? [];
        const index = tasksList.findIndex((t) => t.id === task.id);

        if (index === -1) {
            tasksList.push(task);
        } else {
            tasksList[index] = task;
        }

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
        });
        
    }

    function moveTask(originalTask, stepType, index) {
        return new Promise((resolve) => {
            fetchMoveTask(originalTask, stepType, index).then((task) => {
                tasks.value[originalTask.stepType].splice(tasks.value[originalTask.stepType].findIndex((t) => t.id === originalTask.id), 1);

                if (!tasks.value[stepType]) {
                    return; // if the tasks are not loaded yet the loading will do the rest
                }

                // @todo if we do this the task is in the list twice. :(
                // tasks.value[stepType] = [
                //     ...tasks.value[stepType].slice(0, index),
                //     task,
                //     ...tasks.value[stepType].slice(index),
                // ];

                if (task.id == selectedTask.value?.id) {
                    selectedTask.value = task;
                }

                resolve(task);
            });
        });
    }

    return {
        tasks,
        selectedTask,
        pageStore,
        getSelectedTask,
        getTasks,
        getTask,
        getTaskFromStep,
        setSelectedTask,
        setTasks,
        addTask,
        updateTask,
        deleteTask,
        moveTask,
    };
});
