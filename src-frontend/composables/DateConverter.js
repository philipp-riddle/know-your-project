export function  useDateConverter() {
    /**
     * Given a date string, it returns a formatted date string presenting the "distance", e.g. "2 days ago", "in 3 days", "in 10 hours", etc.
     * @param {String} date 
     * @returns {String} 
     */
    const convertLocalDateToISOString = (date) => {
        const dateUTC = new Date(Date.UTC(
            date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds()
        ));

        return dateUTC.toISOString();
    }

    const convertUTCToLocalDateString = (date) => {
        const dateLocal = new Date(date); // append 'UTC' to the date string to indicate that the input date is in UTC format; automatically converts to local time.

        return dateLocal.toISOString();
    }

    return {
        convertLocalDateToISOString,
        convertUTCToLocalDateString,
    }
};