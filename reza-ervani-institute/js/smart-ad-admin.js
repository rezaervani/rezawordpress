document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-reza-smart-pick]');
    if (!button || !window.wp?.media) {
        return;
    }

    const fields = button.closest('.reza-smart-ad-fields');
    const media = wp.media({ library: { type: 'image' }, multiple: false });
    media.on('select', () => {
        const attachment = media.state().get('selection').first().toJSON();
        const source = fields.querySelector('[data-reza-smart-image]');
        const alt = fields.querySelector('[data-reza-smart-alt]');
        source.value = attachment.url;
        alt.value = attachment.alt || '';
        source.dispatchEvent(new Event('input', { bubbles: true }));
        source.dispatchEvent(new Event('change', { bubbles: true }));
        alt.dispatchEvent(new Event('change', { bubbles: true }));
    });
    media.open();
});
