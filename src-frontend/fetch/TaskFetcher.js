import axios from "axios";

const BaseService = "/api/task";

export async function fetchTask(taskId) {
    const resp = await axios.get(`${BaseService}/${taskId}`);

    return resp.data;
}

export async function fetchTasks(stepType) {
    const resp = await axios.get(`${BaseService}/list/${stepType}`);

    return resp.data;
}

export async function fetchCreateTask(stepType, name) {
    const resp = await axios.post(`${BaseService}/${stepType}`, {
        name: name,
        stepType: stepType,
    });

    return resp.data;
}

export async function fetchUpdateTask(task) {
    const updateTaskData = {
        name: task.name,
        stepType: task.stepType,
        isArchived: task.isArchived,
        dueDate: task.dueDate,
    };
    const taskId = task.id;
    const resp = await axios.put(`${BaseService}/${taskId}`, updateTaskData);

    return resp.data;
}

export async function fetchDeleteTask(taskId) {
    const resp = await axios.delete(`${BaseService}/${taskId}`);

    return resp.data;
}

export async function fetchChangeOrder(workflowStepType, order) {
    const resp = await axios.post(`${BaseService}/${workflowStepType}/order`, {
        idOrder: order
    });

    return resp.data;
}

export async function fetchMoveTask(task, stepType, index) {
    const taskId = task.id;
    const resp = await axios.post(`${BaseService}/${taskId}/move`, {
        stepType: stepType,
        orderIndex: index,
    });

    return resp.data;
}

// === LEGACY API ROUTES ====

export async function getWorkflowStepTasks(workflowStepId) {
    const resp = await axios.get(`${BaseService}/workflow-step/${workflowStepId}`);

    return resp.data;
}