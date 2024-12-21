import { fetchTasks, fetchTask, fetchUpdateTask, fetchCreateTask, fetchDeleteTask, fetchMoveTask, fetchChangeOrder } from "@/fetch/TaskFetcher.js";
import { useTaskStore } from "@/stores/TaskStore.js";

/**
 * A provider strips away lot of complexity out of the views by abstracting the API and the Store layer into one.
 * While a provider makes sure already known data is not requested again, it also makes sure to update it accordingly.
 * @returns 
 */
export function useTaskProvider() {
    const taskStore = useTaskStore();

    async function getTasks(stepType) {
        return new Promise((resolve) => {
            const storedTasks = taskStore.getTasks(stepType);

            if (storedTasks) {
                resolve(storedTasks);
            } else {
                fetchTasks(stepType).then((tasks) => {
                    taskStore.setTasks(stepType, tasks);
                    resolve(tasks);
                });
            }
        });
    }

    async function getTask(taskId) {
        return new Promise((resolve) => {
            const task = taskStore.getTask(taskId);

            if (task) {
                resolve(task);
            } else {
                fetchTask(taskId).then((task) => {
                    taskStore.addTask(task);
                    resolve(task);
                });
            }
        });
    }

    function setSelectedTask(task) {
        if (taskStore.selectedTask == task) {
            return false;
        }

        taskStore.selectedTask = task;

        return true;
    }

    function getSelectedTask() {
        return taskStore.selectedTask;
    }

    function resetSelectedTask() {
        taskStore.selectedTask = null;
    }

    async function updateTask(task) {
        taskStore.updateTask(task);

        return new Promise((resolve) => {
            fetchUpdateTask(task).then((taskUpdate) => {
                // taskStore.updateTask(task); // we do NOT sync here on purpose - otherwise user content can get deleted again
                resolve(taskUpdate);
            });
        });
    }

    async function moveTask(task, stepType, index) {
        if (!taskStore.getTaskFromStep(stepType, task.id)) {
            taskStore.moveTask(task, stepType, index);
        }

        return new Promise((resolve) => {
            fetchMoveTask(task, stepType, index).then((taskMove) => {
                resolve(taskMove);
            });
        });
    }

    async function changeOrder(workflowStepType, order) {
        return new Promise((resolve) => {
            fetchChangeOrder(workflowStepType, order).then((tasks) => {
                resolve(tasks);
            });
        });
    }

    async function createTask(stepType, name) {
        return new Promise((resolve) => {
            fetchCreateTask(stepType, name).then((createdTask) => {
                taskStore.addTask(createdTask);
                resolve(createdTask);
            });
        });
    }

    async function deleteTask(task) {
        taskStore.deleteTask(task);

        return new Promise((resolve) => {
            fetchDeleteTask(task.id).then((task) => {
                resolve(null);
            });
        });
        
    }

    return {
        getTasks,
        getTask,
        setSelectedTask,
        getSelectedTask,
        resetSelectedTask,
        updateTask,
        moveTask,
        changeOrder,
        createTask,
        deleteTask,
    };
}