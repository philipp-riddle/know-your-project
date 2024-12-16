import axios from "axios";

const BaseService = "/api/project";

export async function fetchGetProject(projectId) {
    const resp = await axios.get(`${BaseService}/${projectId}`);

    return resp.data;
}

export async function fetchCreateProject(project) {
    const resp = await axios.post(`${BaseService}`, project);

    return resp.data;
}

const BaseUserService = "/api/project/user";

export async function fetchDeleteProjectUser(projectUserId) {
    const resp = await axios.delete(`${BaseUserService}/${projectUserId}`);

    return resp.data;
}