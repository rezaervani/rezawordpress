document.querySelectorAll('.reza-editorial-menu .menu-item-has-children').forEach((item) => {
    const link = item.querySelector(':scope > a');
    const submenu = item.querySelector(':scope > .sub-menu');
    if (!link || !submenu) {
        return;
    }

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'reza-menu-toggle';
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-controls', item.id + '-submenu');
    button.setAttribute('aria-label', link.textContent.trim());
    button.textContent = '⌄';
    submenu.id = item.id + '-submenu';
    link.after(button);

    function setExpanded(expanded) {
        button.setAttribute('aria-expanded', String(expanded));
        item.classList.toggle('reza-menu-open', expanded);
    }

    button.addEventListener('click', () => {
        setExpanded(button.getAttribute('aria-expanded') !== 'true');
    });
    item.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') {
            setExpanded(false);
            button.focus();
            event.stopPropagation();
        }
    });
});
