import axios from "axios";

const BaseService = "/api/tag";

export async function fetchCreateTag(projectId, name, parentTagId) {
    const resp = await axios.post(`${BaseService}`, {
        project: projectId,
        name: name,
        parent: parentTagId ?? null,
    });

    return resp.data;
}

export async function fetchUpdateTag(tag) {
    const resp = await axios.put(`${BaseService}/${tag.id}`, {
        name: tag.name,
        color: tag.color,
    });

    return resp.data;
}

export async function fetchDeleteTag(tag) {
    const resp = await axios.delete(`${BaseService}/${tag.id}`);

    return resp.data;
}

export async function fetchChangeTagOrder(projectId, parentTagId, idOrder) {
    let url = `${BaseService}/order/${projectId}`;

    if (parentTagId) {
        url += `/${parentTagId}`;
    }

    const resp = await axios.post(url, {
        idOrder: idOrder,
    });

    return resp.data;
}

// ==== TagPage API FUNCTIONS

const BaseTagPageService = BaseService + "/page";

export async function fetchCreateTagPageFromTagId(pageId, tagId, parentTagId) {
    const resp = await axios.post(`${BaseTagPageService}`, {
        page: pageId,
        tag: tagId,
        parent: parentTagId ?? null,
    });

    return resp.data;
}

export async function fetchCreateTagPageFromTagName(pageId, tagName, parentTagId) {
    const resp = await axios.post(`${BaseTagPageService}`, {
        page: pageId,
        tagName: tagName,
        parent: parentTagId ?? null,
    });

    return resp.data;
}

export async function fetchChangeTagPageOrder(projectId, tagPageId, idOrder) {
    let url = `${BaseTagPageService}/order/${projectId}`;

    if (tagPageId) {
        url += `/${tagPageId}`;
    }

    const resp = await axios.post(url, {
        idOrder: idOrder,
    });

    return resp.data;
}

export async function fetchDeleteTagPage(tagPageId) {
    const resp = await axios.delete(`${BaseTagPageService}/${tagPageId}`);

    return resp.data;
}

export async function fetchGetTagPageList(tagId) {
    const resp = await axios.get(`${BaseTagPageService}/list/${tagId}`);

    return resp.data;
}

// ==== TagProjectUser API FUNCTIONS

const BaseTagProjectUserService = BaseService + "/project-user";

export async function fetchCreateTagProjectUserFromTagId(projectUserId, tagId, parentTagId) {
    const resp = await axios.post(`${BaseTagProjectUserService}`, {
        tag: tagId,
        projectUser: projectUserId,
        parent: parentTagId ?? null,
    });

    return resp.data;
}

export async function fetchCreateTagProjectUserFromTagName(projectUserId, tagName, parentTagId) {
    const resp = await axios.post(`${BaseTagProjectUserService}`, {
        tagName: tagName,
        projectUser: projectUserId,
        parent: parentTagId ?? null,
    });

    return resp.data;
}

export async function fetchDeleteTagProjectUser(tagProjectUserId) {
    const resp = await axios.delete(`${BaseTagProjectUserService}/${tagProjectUserId}`);

    return resp.data;
}