import axios from "axios";

const BaseThreadService = "/api/thread";

export async function fetchCreateThread(pageSectionId) {
    const resp = await axios.post(`${BaseThreadService}`, {
        pageSection: pageSectionId,
    });

    return resp.data;
}

const BaseThreadItemService = BaseThreadService + "/item";

export async function fetchDeleteThreadItem(threadItemId) {
    const resp = await axios.delete(`${BaseThreadItemService}/${threadItemId}`);

    return resp.data;
}

const BaseThreadItemCommentService = BaseThreadItemService + "/comment";

export async function fetchCreateThreadCommentItem(threadId, comment) {
    const resp = await axios.post(`${BaseThreadItemCommentService}`, {
        thread: threadId,
        comment: comment,
    });

    return resp.data;
}

const BaseThreadItemPromptService = BaseThreadItemService + "/prompt";

export async function fetchCreateThreadPromptItem(threadId, prompt) {
    const resp = await axios.post(`${BaseThreadItemPromptService}`, {
        thread: threadId,
        prompt: {
            promptText: prompt,
        },
    });

    return resp.data;
}