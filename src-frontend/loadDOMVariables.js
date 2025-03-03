/**
 * Gets a <script type="application/json"> element’s contents by ID.
 * @param {string} id The ID of the <script> element.
 * @param {boolean} isJson Whether to parse the contents as JSON.
 * @returns {any} The parsed JSON data, or the raw string if `isJson` is false.
 */
var getData = function (id, isJson = true) {
    // Read the JSON-formatted data from the DOM.
    var element = document.getElementById(id);

    if (!element) {
        console.error(`DOM Variable with ID "${id}" not found.`);
        return undefined;
    };

    var string = (element.textContent || element.innerText).trim(); // fallback for IE ≤ 8

    // Clear the element’s contents now that we have a copy of the data.
    element.innerHTML = "";

    if (!isJson) {
        return string;
    };

    return JSON.parse(string);
};

window.currentUser = await getData("variable-currentUser");
window.selectedProject = await getData("variable-project");
window.mercureConfig = await getData("variable-mercureConfig");
