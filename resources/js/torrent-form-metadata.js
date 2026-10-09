// Keep description updates independent of the lookup UI so uploads and edits
// use the same rules without losing screenshots or other existing BBCode.
export function titleDescription(metadata) {
    const sections = [];
    if (metadata.title?.trim()) sections.push(`[b]${metadata.title.trim()}[/b]`);
    if (metadata.overview?.trim()) sections.push(metadata.overview.trim());
    if (metadata.cast?.trim()) sections.push(`[b]Cast:[/b] ${metadata.cast.trim()}`);
    return sections.join('\n\n');
}

export function mergeTitleDescription(current, generated, previous = '', replace = false) {
    if (!generated) return current;
    if (replace || !current.trim()) return generated;
    if (current.includes(generated)) return current;
    if (previous && current.includes(previous)) return current.replace(previous, generated);
    return `${current.trimEnd()}\n\n${generated}`;
}

export function applyTitleMetadata(form, metadata, { previous = '', replace = false } = {}) {
    for (const name of ['imdb_url', 'poster', 'genre']) {
        const input = form.querySelector(`[name="${name}"]`);
        if (input && typeof metadata[name] === 'string' && metadata[name].trim()) {
            input.value = metadata[name];
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
    const generated = titleDescription(metadata);
    const description = form.querySelector('[name="description"]');
    if (description) {
        description.value = mergeTitleDescription(description.value, generated, previous, replace);
        description.dispatchEvent(new Event('input', { bubbles: true }));
    }
    return generated;
}
