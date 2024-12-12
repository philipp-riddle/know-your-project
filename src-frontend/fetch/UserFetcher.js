import axios from "axios";

const BaseService = "/api/user";

export async function fetchGetCurrentUser() {
    const resp = await axios.get(`${BaseService}`);

    return resp.data;
}