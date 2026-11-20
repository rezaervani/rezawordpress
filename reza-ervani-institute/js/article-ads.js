(() => {
    const content = document.querySelector('.reza-article-content');
    if (!content) {
        return;
    }

    const templates = [...content.querySelectorAll(':scope > template[data-reza-inline-ad-minimum]')];
    if (templates.length === 0) {
        return;
    }

    const paragraphs = [];
    let totalWords = 0;
    for (const element of content.querySelectorAll(':scope > p')) {
        const words = element.textContent.match(/[\p{L}\p{N}\p{M}]+(?:['’-][\p{L}\p{N}\p{M}]+)*/gu);
        if (words) {
            totalWords += words.length;
            paragraphs.push({ element, wordsThroughParagraph: totalWords });
        }
    }

    const slots = templates.filter((template) => totalWords >= Number(template.dataset.rezaInlineAdMinimum));
    const minimumParagraphs = 3;
    const minimumWords = 300;

    function findPositions(count) {
        const positions = [];
        let previousIndex = -1;
        let previousWords = 0;

        for (let slot = 0; slot < count; slot++) {
            const sectionsRemaining = count - slot;
            const target = totalWords * (slot + 1) / (count + 1);
            const firstIndex = previousIndex + minimumParagraphs;
            const lastIndex = paragraphs.length - 1 - sectionsRemaining * minimumParagraphs;
            let bestIndex = -1;
            let closestDistance = Infinity;

            for (let index = firstIndex; index <= lastIndex; index++) {
                const words = paragraphs[index].wordsThroughParagraph;
                if (words > totalWords - sectionsRemaining * minimumWords) {
                    break;
                }

                const distance = Math.abs(words - target);
                if (words >= previousWords + minimumWords && distance < closestDistance) {
                    bestIndex = index;
                    closestDistance = distance;
                }
            }

            if (bestIndex === -1) {
                return [];
            }

            previousIndex = bestIndex;
            previousWords = paragraphs[bestIndex].wordsThroughParagraph;
            positions.push(paragraphs[bestIndex].element);
        }

        return positions;
    }

    let positions = findPositions(slots.length);
    if (positions.length === 0 && slots.length > 1) {
        positions = findPositions(1);
    }
    positions.forEach((paragraph, index) => {
        // Widget scripts run only after an ad has been placed in the article.
        paragraph.after(slots[index].content.cloneNode(true));
    });
})();
