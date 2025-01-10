import axios from "axios";

const BaseSearchService = "/api/search";

export async function fetchProjectSearch(projectId, searchTerm) {
    const resp = await axios.post(`${BaseSearchService}/project/${projectId}`, {
        search: searchTerm,
    });

    return resp.data;
}