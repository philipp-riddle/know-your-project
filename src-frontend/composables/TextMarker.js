import { h } from "vue";

export function  useTextMarker() {
    /**
     * Given a text, this function will generate a HTML string that highlights the search term in the text.
     * It also works with HTML text, i.e. it will not highlight the search term in HTML tags and will also not break the HTML structure.
     * 
     * @param {String} date 
     * @returns {String} 
     */
    const generateTextMarkerHtml = (searchTerm, text, maxTextLength, defaultValue) => {
        return markSearchInText(searchTerm, text, maxTextLength) ?? defaultValue ?? null;
    };

    const markSearchInText = (searchTerm, text, maxTextLength) => {
        /**
         * To convert the given text to a text we can search we must remove all HTML tags and convert all whitespaces to single spaces.
         * This text we can then search for the search term (or parts of it, i.e. tokenized search term).
         * 
         * @reference Used code from:
         *      - https://www.30secondsofcode.org/js/s/strip-html-tags
         *      - https://www.30secondsofcode.org/js/s/find-remove-compact-whitespace
         */
        text = text.replace(/<[^>]*>?/gm, ' '); // replace all HTML tags with spaces; this is used for tokenizing + searching the text
        text = text.replace(/\s+/g, ' '); // convert all whitespaces to single spaces
        searchTerm = searchTerm.replace('  ', ' '); // remove double spaces

        // convert the search term into a tokenized version by splitting it at spaces, thus single words
        const tokenizedSearch = searchTerm.split(' ');
        var lowerCaseText = text.toLowerCase();
        var markedText = text;

        // now go through all the search tokens and check if one of them is in the text.
        // if yes we set the marked text to the text with the token marked and set hasMatch to true.
        for (var i = 0; i < tokenizedSearch.length; i++) {
            const token = tokenizedSearch[i];

            if (token.length < 5) {
                continue; // ignore tokens that are too short; this prevents words like 'is', 'the', 'what', ... from being marked
            }

            if (lowerCaseText.includes(token)) {
                const startIndex = lowerCaseText.toLowerCase().indexOf(token);
                const endIndex = startIndex + token.length;

                const cutOffset = Math.floor((maxTextLength - token.length) / 2);

                var currentText = text.substring(startIndex - cutOffset, cutOffset * 2 + token.length);
                const currentTextSentences = currentText.split('. ');

                // this is a 'hack' to remove any sentences surrounding the token
                // it also highlights the whole sentence the token is in which is a nice feature.
                if (currentTextSentences.length > 1) {
                    currentText = currentTextSentences[1] + '.';
                } else {
                    currentText = currentTextSentences[0];
                }

                return markedText.substring(0, startIndex) + '<mark>' + currentText + '</mark>' + markedText.substring(endIndex);
            }
        }

        return null;
    }

    return {
        generateTextMarkerHtml,
    };
};