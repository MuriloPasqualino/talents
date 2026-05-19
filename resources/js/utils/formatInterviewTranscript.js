/**
 * Quebra transcrições longas (ex.: Whisper) em parágrafos legíveis para exibição.
 *
 * @param {string|null|undefined} text
 * @returns {string[]}
 */
export function formatInterviewTranscript(text) {
    if (!text || typeof text !== 'string') {
        return [];
    }

    const normalized = text.replace(/\r\n/g, '\n').trim();
    if (!normalized) {
        return [];
    }

    const blocks = normalized.split(/\n{2,}/);
    const paragraphs = [];

    for (const block of blocks) {
        const line = block.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim();
        if (!line) {
            continue;
        }

        if (line.length <= 480) {
            paragraphs.push(line);
            continue;
        }

        const sentences = splitSentences(line);
        if (sentences.length <= 1) {
            paragraphs.push(...splitByLength(line));
            continue;
        }

        paragraphs.push(...groupSentences(sentences));
    }

    return paragraphs;
}

/** @param {string} text */
function splitSentences(text) {
    const parts = text
        .split(/(?<=[.!?…])\s+(?=[A-ZÁÀÂÃÉÊÍÓÔÕÚÇ"«(])/u)
        .map((part) => part.trim())
        .filter(Boolean);

    return parts.length > 0 ? parts : [text];
}

/** @param {string[]} sentences */
function groupSentences(sentences, maxSentences = 4, maxChars = 520) {
    /** @type {string[]} */
    const result = [];
    /** @type {string[]} */
    let current = [];
    let length = 0;

    for (const sentence of sentences) {
        const addedLength = sentence.length + (current.length ? 1 : 0);
        const shouldFlush =
            current.length >= maxSentences || (current.length > 0 && length + addedLength > maxChars);

        if (shouldFlush) {
            result.push(current.join(' '));
            current = [];
            length = 0;
        }

        current.push(sentence);
        length += addedLength;
    }

    if (current.length) {
        result.push(current.join(' '));
    }

    return result;
}

/** @param {string} text */
function splitByLength(text, maxChars = 480) {
    if (text.length <= maxChars) {
        return [text];
    }

    /** @type {string[]} */
    const chunks = [];
    let start = 0;

    while (start < text.length) {
        let end = Math.min(start + maxChars, text.length);

        if (end < text.length) {
            const space = text.lastIndexOf(' ', end);
            if (space > start + 160) {
                end = space;
            }
        }

        const chunk = text.slice(start, end).trim();
        if (chunk) {
            chunks.push(chunk);
        }
        start = end;
    }

    return chunks;
}
