import axios from "axios";

// ==== Page API FUNCTIONS

const BaseService = "/api/page";

export async function fetchGetPage(pageId) {
    const resp = await axios.get(`${BaseService}/${pageId}`);

    return resp.data;
}

export async function fetchDeletePage(pageId) {
    const resp = await axios.delete(`${BaseService}/${pageId}`);

    return resp.data;
}

export async function fetchCreatePage(page) {
    const resp = await axios.post(`${BaseService}`, page);

    return resp.data;
}

export async function fetchUpdatePage(page) {
    const pageId = page.id;
    delete page.id;
    const resp = await axios.put(`${BaseService}/${pageId}`, page);

    return resp.data;
}

export async function fetchGetPageList(projectId, includeUserPages, query, limit) {
    includeUserPages = (includeUserPages ?? true) ? '1' : '0'; // Convert boolean to string
    limit = limit ?? 100; // Default limit to 100
    query = encodeURIComponent(query ?? ''); // URL encode the query to be sure the query does not break the URL

    const resp = await axios.get(`${BaseService}/project-list/${projectId}?includeUserPages=${includeUserPages}&query=${query}&limit=${limit}`);

    return resp.data;
}

// ==== PageTab API FUNCTIONS

const BasePageTabService = BaseService + "/tab";

export async function fetchGetPageTab(pageTabId) {
    const resp = await axios.get(`${BasePageTabService}/${pageTabId}`);

    return resp.data;
}

export async function fetchDeletePageTab(pageTabId) {
    const resp = await axios.delete(`${BasePageTabService}/${pageTabId}`);

    return resp.data;
}

export async function fetchCreatePageTab(pageId, pageTab) {
    const resp = await axios.post(`${BasePageTabService}`, {
        name: pageTab.name ?? 'Untitled tab',
        emojiIcon: pageTab.emojiIcon ?? '📝',
        page: pageId,
    });

    return resp.data;
}

export async function fetchUpdatePageTab(pageTab) {
    const resp = await axios.put(`${BasePageTabService}/${pageTab.id}`, {
        name: pageTab.name,
        emojiIcon: pageTab.emojiIcon,
    });

    return resp.data;
}

// ==== PageSection API FUNCTIONS

const BasePageSectionService = BaseService + "/section";

export async function fetchGetPageSection(pageSectionId) {
    const resp = await axios.get(`${BasePageSectionService}/${pageSectionId}`);

    return resp.data;
}

export async function fetchDeletePageSection(pageSectionId) {
    const resp = await axios.delete(`${BasePageSectionService}/${pageSectionId}`);

    return resp.data;
}

export async function fetchCreatePageSection(pageTabId, pageSection) {
    const resp = await axios.post(`${BasePageSectionService}`, {
        pageTab: pageTabId,
        ...pageSection,
    });

    return resp.data;
}

export async function fetchUpdatePageSection(pageSectionId, pageSection) {
    const resp = await axios.put(`${BasePageSectionService}/${pageSectionId}`, pageSection);

    return resp.data;
}

export async function fetchChangePageSectionOrder(pageTabId, sectionIds) {
    const resp = await axios.put(`${BasePageSectionService}/order/${pageTabId}`, {
        idOrder: sectionIds,
    });

    return resp.data;
}

export async function fetchUploadPageSection(pageTabId, file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('pageTab', pageTabId);

    const resp = await axios.post(`${BasePageSectionService}/upload`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    });

    return resp.data;
}

// ==== PageSectionChecklistItem API FUNCTIONS

const BaseChecklistItemService = BasePageSectionService + "/checklist/item";

export async function fetchCreatePageSectionChecklistItem(checklist) {
    const resp = await axios.post(`${BaseChecklistItemService}`, checklist);

    return resp.data;
}

export async function fetchUpdatePageSectionChecklistItem(checklistItem) {
    const resp = await axios.put(`${BaseChecklistItemService}/${checklistItem.id}`, {
        name: checklistItem.name,
        complete: checklistItem.complete,
    });

    return resp.data;
}

export async function fetchDeletePageSectionChecklistItem(checklistItem) {
    const resp = await axios.delete(`${BaseChecklistItemService}/${checklistItem.id}`);

    return resp.data;
}