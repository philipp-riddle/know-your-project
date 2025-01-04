export function  useDateFormatter() {
    /**
     * Given a date string, it returns a formatted date string presenting the "distance", e.g. "2 days ago", "in 3 days", "in 10 hours", etc.
     * @param {String} date 
     * @returns {String} 
     */
    const formatDateDistance = (date) => {
        const currentDate = new Date();
        const dateToCompare = new Date(date);
        const distance = dateToCompare - currentDate;
        const seconds = Math.floor(Math.abs(distance) / 1000); // use the absolute distance in the first step to ignore negative values
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        const months = Math.floor(days / 30);
        const years = Math.floor(months / 12);
        var dateString = '';

        if (years > 0) {
            dateString = years === 1 ? '1 year' : `${years} years`;
        } else if (months > 0) {
            dateString = months === 1 ? '1 month' : `${months} months`;
        } else if (days > 0) {
            dateString = days === 1 ? '1 day' : `${days} days`;
        } else if (hours > 0) {
            dateString = hours === 1 ? '1 hour' : `${hours} hours`;
        } else if (minutes > 0) {
            dateString = minutes === 1 ? '1 minute' : `${minutes} minutes`;
        } else {
            return 'Just now';
        }

        if (distance > 0) {
            return `In ${dateString}`;
        }

        return `${dateString} ago`;
    };

    const formatDate = (date) => {
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        
        return (new Date(date)).toLocaleDateString('en-US', dateOptions);
    };
    
    return {
        formatDateDistance,
        formatDate,
    };
}
