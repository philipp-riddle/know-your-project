<?php

namespace App\Service\Helper;

/**
 * This class is responsible for extracting the most important text from a given text and mark exact matches.
 * To do this it must use complex matching and cutting algorithms to ensure that the text is matched & cut at the right position.
 */
final class TextMarker
{
    public const DEFAULT_MAX_LENGTH = 128;

    public static function getMarkedTextFromRichText(string $searchTerm, string $html, string $textLength = TextMarker::DEFAULT_MAX_LENGTH, ?string $defaultTagForMatchedElement = null): string
    {
        $textElements = HTMLParser::extractAllText($html);
        
        // if no text elements are found, we will treat the whole html as one (text) element
        if (null === $textElements) {
            // $defaultTextElementType = \filter_var($html, \FILTER_VALIDATE_URL) ? 'a' : 'p';
            $defaultTextElementType = 'p'; // for now only p - if we also allow 'a' we have to take care of the original URL which must be correct to display it anywhere.
            $textElements ??= [[$html, $defaultTextElementType]];
        }

        // now try to match any of the given texts with the search term
        $matchedTextElements = [];

        foreach ($textElements as $textElement) {
            // e.g. ['Heading', 'H1']
            list ($textElement, $textTagType) = $textElement;
            $markedText = self::getMarkedText($searchTerm, $textElement, $textLength);

            if (null !== $markedText) {
                $matchedTextElements[] = [
                    'text' => $textElement,
                    'markedText' => $markedText,
                    'type' => $textTagType,
                ];
            }
        }

        if (\count($matchedTextElements) === 0) {
            // return the first text element and cut it to the given length without breaking words
            return self::cutWord($textElements[0][0], 0, $textLength);
        }

        // convert the matched text elements to a string
        $matchElementsString = '';

        foreach ($matchedTextElements as $matchedTextElement) {
            $matchedTextElementType = $defaultTagForMatchedElement ?? $matchedTextElement['type'];
            $matchedTextElementHtml = \sprintf('<%s>%s</%s>', $matchedTextElementType, $matchedTextElement['markedText'], $matchedTextElementType);

            if ($matchedTextElementType === 'li') { // wrap each found list item in a ul
                $matchedTextElementHtml = \sprintf('<ul>%s</ul>', $matchedTextElementHtml);
            }

            $matchElementsString .= $matchedTextElementHtml;
        }

        return $matchElementsString;
    }

    public static function getMarkedText(string $searchTerm, string $text, string $textLength = TextMarker::DEFAULT_MAX_LENGTH): ?string
    {
        // first try to get exact matches in the text
        $searchTerm = \trim($searchTerm);
        $matches = static::getMatches($searchTerm, $text);

        if (\count($matches) === 0) {
            // try to find tokenized matches if there were no exact matches.
            $tokens = self::tokenize($searchTerm);

            foreach ($tokens as $token) {
                \array_push($matches, ...static::getMatches($token, $text));
            }

            // if the count after tokenizing is still 0, we return null
            if (\count($matches) === 0) {
                return null;
            }
        }

        $markedText = '';
        $lastIndex = 0;

        foreach ($matches as $match) {
            $markedText .= \mb_substr($text, $lastIndex, $match['index'] - $lastIndex);
            $markedText .= "<mark>{$match['text']}</mark>";
            $lastIndex = $match['index'] + \mb_strlen($match['text']);
        }

        $markedText .= \mb_substr($text, $lastIndex);

        return self::cutWord($markedText, 0, $textLength);
    }

    /**
     * The first steps in marking any text is to find any relevant matches in the given text and return them.
     * 
     * @param string $searchTerm The search term to search for.
     * @param string $text The text in which to search for the search term.
     * 
     * @return array The matches found in the text; can be exact but partial matches are possible.
     */
    public static function getMatches(string $searchTerm, string $text): array
    {
        $searchTerm = \trim(\strip_tags($searchTerm));
        $matches = [];
        $searchTerm = \preg_quote($searchTerm, '/');

        if (\preg_match_all("/$searchTerm/i", $text, $matches, \PREG_OFFSET_CAPTURE)) {
            $mappedMatches = [];

            foreach ($matches[0] as $match) {
                $matchText = $match[0];
                $matchIndex = $match[1];
                $textCut = self::cutWord($text, $matchIndex, $matchIndex + \mb_strlen($searchTerm));

                // if the cut text is different than the original matched text we must adjust the index
                if ($matchText !== $textCut) {
                    $matchPos = \strpos($textCut, $matchText);
                    $matchIndex -= $matchPos;
                }

                $mappedMatches[] = [
                    'text' => $textCut,
                    'index' => $matchIndex,
                ];
            }

            return $mappedMatches;
        }

        return [];
    }

    /**
     * Tries to cut the text around the given start index and length without breaking words.
     * 
     * @param string $text The text to cut.
     * @param string $startIndexOffset The index to start cutting from.
     * @param string $endIndexOffset The index to end cutting at; if null, the text is cut at the next space or special char.
     * 
     * @return string The cut text; max length is $length + $lengthTolerance.
     */
    public static function cutWord(string $text, int $startIndexOffset, ?int $endIndexOffset = null): string
    {
        // search for the first char that is a space; then cut the text there
        $startIndex = null;

        for ($i = $startIndexOffset - 1; $i >= 0; $i--) {
            if (self::isEndChar($text[$i])) {
                $startIndex = $i + 1;
                break;
            }
        }

        // search for the last char that is a space or a special char which we can divide by; then cut the text there
        $endIndex = null;

        for ($i = $endIndexOffset ?? $startIndexOffset; $i < \mb_strlen($text); $i++) {
            if (self::isEndChar($text[$i])) {
                $endIndex = $i;
                break;
            }
        }

        $startIndex ??= 0;
        $endIndex ??= \mb_strlen($text);
        $text = \mb_substr($text, $startIndex, $endIndex - $startIndex);

        return $text;
    }

    public static function isEndChar(string $char): bool
    {
        return \ctype_space($char) || \in_array($char, ['.', ',', ';', ':', '!', '?', '(', ')', '[', ']', '{', '}', '<', '>', '"', "'"], true);
    }

    /**
     * Tokenizes the given input text by splitting it at spaces.
     * 
     * @param string $input The input text to tokenize.
     * @param int $maximumTokens The maximum number of tokens to return.
     * @param int $minimumTokenLength The minimum length of a token to be considered.
     * 
     * @return array The tokenized input text. E.g. 'Hello World' => ['Hello', 'World']
     */
    public static function tokenize(string $input, int $maximumTokens = 12, int $minimumTokenLength = 4): array
    {
        $input = \preg_replace('/\s+/', ' ', $input);
        $input = \preg_replace('/\s/', '|', $input);
        $input = \preg_replace('/\|+/', '|', $input);
        $input = \trim($input, '|');

        $tokens = \explode('|', $input); // split the input text at spaces
        $tokens = \array_slice($tokens, 0, $maximumTokens); // limit the number of tokens

        foreach ($tokens as $i => $token) {
            $token = \trim($token);

            if (\strlen($token) < $minimumTokenLength || \in_array(\strtolower($token), self::getCommonEnglishWords(), true)) {
                unset($tokens[$i]);
                continue;
            }

            // now clean the token of any special chars.
            foreach (\str_split($token) as $char) {
                if (self::isEndChar($char)) {
                    $token = \str_replace($char, '', $token);
                }
            }

            $tokens[$i] = $token;
        }
        
        $tokens = \array_unique($tokens); // remove duplicates
        $tokens = \array_values($tokens); // reset array indices

        return $tokens;
    }

    /**
     * These words can be ignored when tokenizing; it is not worth marking them as they are too common.
     */
    public static function getCommonEnglishWords()
    {
        return [
            'also',
            'well',
            'this',
            'that',
            'have',
            'with',
            'from',
            'they',
            'will',
            'what',
            'when',
            'make',
            'like',
            'time',
            'just',
            'should',
            'would',
            'could',
            'there',
            'their',
            'which',
            'other',
            'these',
            'those',
        ];
    }
}