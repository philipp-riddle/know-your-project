import axios from "axios";

const BaseService = "/api/user";

export async function fetchGetCurrentUser() {
    const resp = await axios.get(`${BaseService}`);

    return resp.data;
}

export async function fetchUploadUserProfilePicture(pictureFile) {
    const formData = new FormData();
    formData.append('picture', pictureFile);

    const resp = await axios.post(`${BaseService}/profile-picture`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    });

    return resp.data;
}

const BaseInvitationService = BaseService + "/invitation";

export async function fetchUserProjectInvititations(projectId) {
    const resp = await axios.get(`${BaseInvitationService}/project/list/${projectId}`);

    return resp.data;
}

export async function fetchUserInvitations() {
    const resp = await axios.get(`${BaseInvitationService}/list`);

    return resp.data;
}

export async function fetchCreateUserProjectInvitation(projectId, email) {
    const resp = await axios.post(`${BaseInvitationService}`, {
        email: email,
        project: projectId,
    });

    return resp.data;
}

export async function fetchAcceptUserProjectInvitation(invitationId) {
    const resp = await axios.post(`${BaseInvitationService}/accept/${invitationId}`);

    return resp.data;
}

export async function fetchDeleteUserProjectInvitation(invitationId) {
    const resp = await axios.delete(`${BaseInvitationService}/${invitationId}`);

    return resp.data;
}