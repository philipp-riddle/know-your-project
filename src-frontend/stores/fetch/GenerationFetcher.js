import axios from "axios";

const BaseSearchService = "/api/generation";

export async function fetchProjectAsk(projectId, question) {
    const resp = await axios.post(`${BaseSearchService}/ask/${projectId}`, {
        question: question,
    });

    return resp.data;
}

export async function fetchProjectCreate(pageId, intro) {
    const resp = await axios.post(`${BaseSearchService}/create/${pageId}`, {
        intro: intro,
    });

    return resp.data;
}

export async function fetchProjectPageSave(pageId, title, content, tagId, checklistItems = []) {
    const resp = await axios.post(`${BaseSearchService}/save/${pageId}`, {
        title: title,
        content: content,
        tag: tagId,
        checklistItems: checklistItems,
    });

    return resp.data;
}