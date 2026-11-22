(() => {
    const libraryUrl = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
    const timeoutMs = 6000;

    function showAd(widget) {
        const fallbacks = [...widget.querySelectorAll('.reza-smart-ad-fallback')];
        const fallback = fallbacks[Math.floor(Math.random() * fallbacks.length)];
        const placement = widget.querySelector('.reza-smart-ad-unit');

        function showFallback() {
            fallbacks.forEach((item) => { item.hidden = item !== fallback; });
            widget.dataset.adState = fallback ? 'fallback' : 'empty';
        }

        if (!placement) {
            showFallback();
            return;
        }

        const settings = placement.dataset;
        widget.dataset.adState = 'pending';
        placement.hidden = false;
        if (settings.width && Number(settings.width) > placement.clientWidth) {
            placement.hidden = true;
            showFallback();
            return;
        }
        fallbacks.forEach((item) => { item.hidden = true; });

        const unit = document.createElement('ins');
        unit.className = 'adsbygoogle';
        unit.dataset.adClient = settings.adClient;
        unit.dataset.adSlot = settings.adSlot;
        if (settings.width && settings.height) {
            unit.style.display = 'inline-block';
            unit.style.width = settings.width + 'px';
            unit.style.height = settings.height + 'px';
        } else {
            unit.style.display = 'block';
            unit.dataset.adFormat = settings.adFormat;
            unit.dataset.fullWidthResponsive = settings.fullWidthResponsive;
        }
        placement.append(unit);

        const existing = [...document.scripts].find(({ src }) => src === libraryUrl || src.startsWith(libraryUrl + '?'));
        const library = existing || document.createElement('script');
        let finished = false;

        function finish(filled) {
            if (finished) {
                return;
            }
            finished = true;
            window.clearTimeout(timer);
            observer.disconnect();
            library.removeEventListener('error', onError);
            if (filled) {
                widget.dataset.adState = 'filled';
            } else {
                unit.remove();
                placement.hidden = true;
                showFallback();
            }
        }

        function checkStatus() {
            const status = unit.dataset.adStatus;
            if (status === 'filled') {
                finish(true);
            } else if (status === 'unfilled' || status === 'unfill-optimized') {
                finish(false);
            }
        }

        const onError = () => finish(false);
        const observer = new MutationObserver(checkStatus);
        const timer = window.setTimeout(onError, timeoutMs);
        observer.observe(unit, { attributes: true, attributeFilter: ['data-ad-status'] });
        library.addEventListener('error', onError);
        if (!existing) {
            library.async = true;
            library.crossOrigin = 'anonymous';
            library.src = libraryUrl + '?client=' + encodeURIComponent(settings.adClient);
            document.head.append(library);
        }
        try {
            (window.adsbygoogle = window.adsbygoogle || []).push({});
            checkStatus();
        } catch {
            finish(false);
        }
    }

    function start() {
        document.querySelectorAll('[data-reza-smart-ad]').forEach(showAd);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
