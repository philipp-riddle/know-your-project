import axios from "axios";

const BaseService = "/api/mercure";

export async function fetchJWS(projectId, topics) {
    const formattedTopics = topics.join(',');
    const resp = await axios.get(`${BaseService}/jws/${projectId}?topics=${formattedTopics}`);

    return resp.data;
}