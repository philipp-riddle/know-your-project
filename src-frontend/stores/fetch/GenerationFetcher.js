import axios from "axios";

const BaseSearchService = "/api/generation";

export async function fetchProjectAsk(projectId, question) {
    const resp = await axios.post(`${BaseSearchService}/ask/${projectId}`, {
        question: question,
    });

    return resp.data;
}

/**
 * Generetes a response based on the given page and text.
 */
export async function fetchProjectPageCreate(pageId, text) {
    const resp = await axios.post(`${BaseSearchService}/create/${pageId}`, {
        text: text,
    });

    return resp.data;
}

export async function fetchProjectPageSave(projectId, pageId, title, content, tagId, checklistItems = []) {
    let saveUrl = `${BaseSearchService}/save/${projectId}`;

    if (pageId) {
        saveUrl += `/${pageId}`;
    }

    const resp = await axios.post(saveUrl, {
        title: title,
        content: content,
        tag: tagId,
        checklistItems: checklistItems,
    });

    return resp.data;
}