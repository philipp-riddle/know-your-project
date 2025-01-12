export function  useTextMarker() {
    /**
     * Given a text, this function will generate a HTML string that highlights the search term in the text.
     * It also works with HTML text, i.e. it will not highlight the search term in HTML tags and will also not break the HTML structure.
     * 
     * @param {String} date 
     * @returns {String} 
     */
    const generateTextMarkerHtml = (searchTerm, text, maxTextLength) => {
        const textElementsToCheck = [];

        if (text.startsWith('<')) {
            var htmlEmbedding = document.createElement( 'html' );
            htmlEmbedding.innerHTML = text;

            for (var i = 0; i < htmlEmbedding.querySelector('body').children.length; i++) {
                const child = htmlEmbedding.querySelector('body').children[i].innerText;
                textElementsToCheck.push(child);
            }
        } else {
            textElementsToCheck.push(text);
        }

        var foundIndex = null;
        var foundTextElement = null;

        for (const textElement of textElementsToCheck) {
            // this could be further improved by checking if parts of the search term are included in the text element; this makes it much more flexible and user-friendly
            const index = textElement.toLowerCase().indexOf(searchTerm.toLowerCase());

            if (index !== -1) {
                foundIndex = index;
                foundTextElement = textElement;
                break;
            }
        }

        if (foundIndex === null) {
            return text.substring(0, maxTextLength); // return the first n characters if the search term was not found
        }

        // we want to strip some text before and after the matched text to provide context
        // we do this by calculating the offset from the found index, i.e. how far we can "go" to to the left and right in the found text element 
        const textOffset = Math.floor((maxTextLength - searchTerm.length) / 2);
        const start = Math.max(0, foundIndex - textOffset);
        const end = Math.min(foundTextElement.length, foundIndex + searchTerm.length + textOffset);

        var markedText = '';
        markedText += foundTextElement.substring(start, foundIndex);
        markedText += '<mark>';
        markedText += foundTextElement.substring(foundIndex, foundIndex + searchTerm.length);
        markedText += '</mark>';
        markedText += foundTextElement.substring(foundIndex + searchTerm.length, end);

        return markedText;
    };

    return {
        generateTextMarkerHtml,
    };
};