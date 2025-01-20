import axios from "axios";

const BaseSearchService = "/api/generation";

export async function fetchProjectAsk(projectId, question) {
    const resp = await axios.post(`${BaseSearchService}/ask/${projectId}`, {
        question: question,
    });

    return resp.data;
}