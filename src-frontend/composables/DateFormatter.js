export function  useDateFormatter() {
    /**
     * Given a date string, it returns a formatted date string presenting the "distance", e.g. "2 days ago", "in 3 days", "in 10 hours", etc.
     * @param {String} date 
     * @returns {String} 
     */
    const formatDateDistance = (date) => {
        var dateString = '';
        const timeUnits = extractTimeUnits(date);

        if (timeUnits.years > 0) {
            dateString = timeUnits.years === 1 ? '1 year' : `${timeUnits.years} years`;
        } else if (timeUnits.months > 0) {
            dateString = timeUnits.months === 1 ? '1 month' : `${timeUnits.months} months`;
        } else if (timeUnits.days > 0) {
            dateString = timeUnits.days === 1 ? '1 day' : `${timeUnits.days} days`;
        } else if (timeUnits.hours > 0) {
            dateString = timeUnits.hours === 1 ? '1 hour' : `${timeUnits.hours} hours`;
        } else if (timeUnits.minutes > 0) {
            dateString = timeUnits.minutes === 1 ? '1 minute' : `${timeUnits.minutes} minutes`;
        }

        if (timeUnits.multiplier === 1) {
            return `in ${dateString}`;
        } else if (timeUnits.multiplier === -1) {
            return `${dateString} ago`;
        } else {
            return 'now';
        }
    };

    const formatShortDateDistance = (date) => {
        var dateString = '';
        const timeUnits = extractTimeUnits(date);

        if (timeUnits.years > 0) {
            dateString = `${timeUnits.years}y`;
        } else if (timeUnits.months > 0) {
            dateString = `${timeUnits.months}m`;
        } else if (timeUnits.days > 0) {
            dateString = `${timeUnits.days}d`;
        } else if (timeUnits.hours > 0) {
            dateString = `${timeUnits.hours}h`;
        } else if (timeUnits.minutes > 0) {
            dateString = `${timeUnits.minutes}m`;
        } else {
            dateString = `${timeUnits.seconds}s`;
        }

        return dateString;
    };

    const extractTimeUnits = (date) => {
        const currentDate = new Date();
        const dateToCompare = new Date(date);
        const distance = dateToCompare - currentDate;
        const seconds = Math.floor(Math.abs(distance) / 1000); // use the absolute distance in the first step to ignore negative values
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        const months = Math.floor(days / 30);
        const years = Math.floor(months / 12);

        return {
            years,
            months,
            days,
            hours,
            minutes,
            seconds,
            multiplier: distance < 0 ? -1 : 1,
        };
    };

    const formatDate = (date) => {
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        
        return (new Date(date)).toLocaleDateString('en-US', dateOptions);
    };

    // format: Feb 25
    const formatShortDate = (date) => {
        const dateObj = new Date(date);
        const month = dateObj.toLocaleString('default', { month: 'short' });
        const day = dateObj.getDate();

        return `${month} ${day}`;
    }

    const formatHoursAndSeconds = (date) => {
        const dateObj = new Date(date);
        const hours = dateObj.getHours().toString().padStart(2, '0');
        const minutes = dateObj.getMinutes().toString().padStart(2, '0');

        return `${hours}:${minutes}`;
    }
    
    return {
        formatDateDistance,
        formatShortDateDistance,
        formatDate,
        formatShortDate,
        formatHoursAndSeconds,
    };
}
