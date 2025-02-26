import axios from "axios";

const BaseService = "/api/project";

export async function fetchGetProject(projectId) {
    const resp = await axios.get(`${BaseService}/${projectId}`);

    return resp.data;
}

export async function fetchCreateProject(projectName, selectAfterCreating) {
    const resp = await axios.post(`${BaseService}`, {
        name: projectName,
        selectAfterCreating: selectAfterCreating
    });

    return resp.data;
}

export async function fetchSelectProject(projectId) {
    const resp = await axios.put(`${BaseService}/select/${projectId}`);

    return resp.data;
}

export async function fetchDeleteProject(projectId) {
    const resp = await axios.delete(`${BaseService}/${projectId}`);

    return resp.data;
}

const BaseUserService = "/api/project/user";

export async function fetchDeleteProjectUser(projectUserId) {
    const resp = await axios.delete(`${BaseUserService}/${projectUserId}`);

    return resp.data;
}