export function  usePageSectionAccessibilityHelper() {
    const getIcon = (pageSection) => {
        if (pageSection.pageSectionText) {
            return 'font';
        }

        if (pageSection.aiPrompt) {
            return 'microchip';
        }

        if (pageSection.pageSectionSummary) {
            return 'book';
        }

        if (pageSection.pageSectionURL) {
            return 'link';
        }

        if (pageSection.embeddedPage) {
            return 'arrow-up-right-from-square';
        }

        if (pageSection.pageSectionChecklist) {
            return 'list-check';
        }

        if (pageSection.pageSectionUpload) {
            const mimeType = pageSection.pageSectionUpload.file.mimeType;

            if (mimeType.startsWith('image')) {
                return 'image';
            } else if (mimeType === 'application/pdf') {
                return 'file-pdf';
            } else if (mimeType === 'application/msword' || mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                return 'file-word';
            } else if (mimeType === 'application/vnd.ms-excel' || mimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                return 'file-excel';
            } else if (mimeType === 'application/vnd.ms-powerpoint' || mimeType === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
                return 'file-powerpoint';
            } else if (mimeType === 'application/zip') {
                return 'file-archive';
            } else if (mimeType === 'text/csv') {
                return 'file-csv';
            }

            return 'file-upload';
        }

        if (pageSection.calendarEvent) {
            return 'calendar-day';
        }

        return null;
    };

    const getTooltip = (pageSection) => {
        const icon = getIcon(pageSection);

        if (!icon) {
            return null;
        }

        if (icon === 'font') {
            return 'Text';
        } else if (icon === 'microchip') {
            return 'AI Prompt';
        } else if (icon === 'arrow-up-right-from-square') {
            return 'Connected page';
        } else if (icon === 'list-check') {
            return 'Checklist';
        } else if (icon === 'image') {
            return 'Image';
        } else if (icon === 'file-pdf') {
            return 'PDF';
        } else if (icon === 'file-word') {
            return 'Word';
        } else if (icon === 'file-excel') {
            return 'Excel';
        } else if (icon === 'file-powerpoint') {
            return 'PowerPoint';
        } else if (icon === 'file-archive') {
            return 'ZIP Archive';
        } else if (icon === 'file-csv') {
            return 'CSV';
        } else if (icon === 'file-upload') {
            return 'File upload';
        } else if (icon === 'book') {
            return 'Summary';
        } else if (icon === 'link') {
            return 'URL';
        } else if (icon === 'calendar-day') {
            return 'Calendar event';
        }
    };

    const getTitle = (pageSectionType) => {
        if (pageSectionType === 'text') {
            return 'Text';
        } else if (pageSectionType === 'checklist') {
            return 'Checklist';
        } else if (pageSectionType === 'upload') {
            return 'Upload';
        } else if (pageSectionType === 'embeddedPage') {
            return 'Connect page';
        } else if (pageSectionType === 'summary') {
            return 'Generate summary';
        } else if (pageSectionType === 'aiPrompt') {
            return 'Ask AI Prompt';
        } else if (pageSectionType === 'url') {
            return 'URL';
        } else if (pageSectionType === 'calendarEvent') {
            return 'Connect calendar event';
        } else {
            return 'Unknown';
        }
    }

    const getIconFromTitle = (pageSectionType) => {
        if (pageSectionType === 'text') {
            return 'font';
        } else if (pageSectionType === 'checklist') {
            return 'list-check';
        } else if (pageSectionType === 'upload') {
            return 'file-upload';
        } else if (pageSectionType === 'embeddedPage') {
            return 'arrow-up-right-from-square';
        } else if (pageSectionType === 'summary') {
            return 'book';
        } else if (pageSectionType === 'aiPrompt') {
            return 'microchip';
        } else if (pageSectionType === 'url') {
            return 'link';
        } else if (pageSectionType === 'calendarEvent') {
            return 'calendar-day';
        } else {
            return null;
        }
    }

    return {
        getIcon,
        getTooltip,
        getTitle,
        getIconFromTitle,
    };
}