import axios from "axios";

const BaseThreadService = "/api/thread";

const BaseThreadItemService = BaseThreadService + "/item";

export async function fetchDeleteThreadItem(threadItemId) {
    const resp = await axios.delete(`${BaseThreadItemService}/${threadItemId}`);

    return resp.data;
}

const BaseThreadPromptService = BaseThreadService + "/prompt";

export async function fetchCreateThreadPrompt(pageSectionAIPromptId) {
    const resp = await axios.post(`${BaseThreadPromptService}`, {
        pageSectionAIPrompt: pageSectionAIPromptId,
    });

    return resp.data;
}

const BaseThreadPromptItemService = BaseThreadPromptService + "/item";

export async function fetchCreateThreadPromptItem(threadId, prompt) {
    const resp = await axios.post(`${BaseThreadPromptItemService}`, {
        thread: threadId,
        prompt: {
            promptText: prompt,
        },
    });

    return resp.data;
}