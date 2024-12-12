import { defineStore } from 'pinia';
import { usePageStore } from './PageStore';

export const useTaskStore = defineStore('task', {
    state: () => ({
        tasks: {},
        selectedTask: null,
        pageStore: usePageStore(),
    }),
    actions: {
        setSelectedTask(task) {
            this.selectedTask = task;
            this.pageStore.addPage(task.page); // to load the page into the redux as well; is now needed for the task page
        },
        getSelectedTask() {
            return this.selectedTask;
        },
        setTasks(stepType, tasks) {
            this.tasks[stepType] = tasks;
        },
        getTasks(stepType) {
            return this.tasks[stepType] ?? null;
        },
        getTask(taskId) {
            for (const stepType in this.tasks) {
                const task = this.tasks[stepType].find((t) => t.id == taskId);

                if (task) {
                    return task;
                }
            }

            return null;
        },
        getTaskFromStep(stepType, taskId) {
            if (!this.tasks[stepType]) {
                return null;
            }

            return this.tasks[stepType].find((t) => t.id === taskId);
        },
        addTask(task) {
            if (this.getTaskFromStep(task.stepType, task.id)) {
                return;
            }

            if (!this.tasks[task.stepType]) {
                this.tasks[task.stepType] = [];
            }

            this.tasks[task.stepType].push(task);
        },
        updateTask(task) {
            const tasks = this.tasks[task.stepType] ?? [];
            const index = tasks.findIndex((t) => t.id === task.id);

            if (index === -1) {
                this.tasks[task.stepType].push(task);
            } else {
                this.tasks[task.stepType][index] = task;
            }
        },
        deleteTask(task) {
            if (!this.tasks[task.stepType]) {
                return;
            }

            const index = this.tasks[task.stepType].findIndex((t) => t.id === task.id);
            this.tasks[task.stepType].splice(index, 1);
        },
        moveTask(task, stepType, index) {
            this.deleteTask(task);
            task.stepType = stepType;

            if (!this.tasks[stepType]) {
                return; // if the tasks are not loaded yet the loading will do the rest
            }

            this.tasks[stepType].splice(index, 0, task);
        }
    }
});