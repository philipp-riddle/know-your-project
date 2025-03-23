/**
 * Copied from https://gist.github.com/karlgroves/7544592 to extract full and unique DOM path.
 * Adapted to our coding guidelines and standards (i.e. better naming and empty spaces for better readibility).
 */
export function useDOMPath() {
    const getFullPath = (element) => {
        let fullPath = '';
        const allElements = getAllElements(element);

        for (let i = 0; i < allElements.length; i++) {
            const element = allElements[i];
            const elementRelativePath = element.nodeName + '#' + getIndexInParent(element);

            if (i > 0) {
                fullPath += '>';
            }

            fullPath += elementRelativePath;
        }

        return fullPath;
    };

    const getIndexInParent = (element) => {
        var index = 1;

        for(var elementSiblings of element.parentElement?.children ?? []) {
            if (elementSiblings.innerHTML === element.innerHTML) {
                return index;
            }

            if (elementSiblings.nodeName === element.nodeName) {
                index++;
            }
        }

        return index;
    }

    const getAllElements = (element) =>  {
        var stack = [];
        while ( element.parentNode != null ) {
            var sibCount = 0;
            var sibIndex = 0;

            for (var i = 0; i < element.parentNode.childNodes.length; i++) {
                var sib = element.parentNode.childNodes[i];

                if (sib.nodeName == element.nodeName) {
                    if (sib === element) {
                        sibIndex = sibCount;
                    }

                    sibCount++;
                }
            }

            stack.push(element);
            element = element.parentNode;
        }

        return stack.reverse();
    }

    const getElementAt = (fullDomPath) => {
        const elements = fullDomPath.split('>');
        let element = document;

        for (let i = 0; i < elements.length; i++) {
            const elementName = elements[i].split('#')[0];
            let elementIndex = parseInt(elements[i].split('#')[1]);

            const children = element.children;
            let foundCount = 0;

            for (let j = 0; j < children.length; j++) {
                if (children[j].nodeName === elementName) {
                    foundCount++;

                    if (elementIndex === foundCount) {
                        element = children[j];
                        break;
                    }
                }
            }

            if (!element) {
                console.log('abort at ' + elementName + ' ' + elementIndex + ' ' + elements[i]);
                return null;
            }
        }

        return element;
    };
      
    return {
        getFullPath,
        getElementAt,
    };
}