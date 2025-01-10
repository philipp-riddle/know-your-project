import axios from "axios";

const BaseService = "/api/user";

export async function fetchGetCurrentUser() {
    const resp = await axios.get(`${BaseService}`);

    return resp.data;
}

export async function fetchUserProjectInvititations(projectId) {
    const resp = await axios.get(`${BaseService}/invitation/project/list/${projectId}`);

    return resp.data;
}

export async function fetchCreateUserProjectInvitation(projectId, email) {
    const resp = await axios.post(`${BaseService}/invitation`, {
        email: email,
        project: projectId,
    });

    return resp.data;
}

export async function fetchDeleteUserProjectInvitation(invitationId) {
    const resp = await axios.delete(`${BaseService}/invitation/${invitationId}`);

    return resp.data;
}