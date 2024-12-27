import axios from "axios";

// ==== TagPage API FUNCTIONS

const BaseService = "/api/tag/page";

export async function fetchCreateTagPageFromTagId(pageId, tagId) {
    const resp = await axios.post(`${BaseService}`, {
        page: pageId,
        tag: tagId,
    });

    return resp.data;
}

export async function fetchCreateTagPageFromTagName(pageId, tagName, parentTagId) {
    const resp = await axios.post(`${BaseService}`, {
        page: pageId,
        tagName: tagName,
        parent: parentTagId ?? null,
    });

    return resp.data;
}

export async function fetchDeleteTagPage(tagPageId) {
    const resp = await axios.delete(`${BaseService}/${tagPageId}`);

    return resp.data;
}