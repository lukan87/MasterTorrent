(() => {
    function initialize({ focus = false } = {}) {
        document.querySelectorAll('[data-torrent-search]').forEach(form => {
            if (form.dataset.searchInitialized) return;
            form.dataset.searchInitialized = '1';
            const keyword = form.querySelector('[data-search-keyword]');
            const checkboxes = [...form.querySelectorAll('input[name="categories[]"]')];
            const counter = form.querySelector('[data-category-count]');
            const finder = form.querySelector('[data-category-find]');
            const groups = [...form.querySelectorAll('[data-category-group]')];
            let timer;
            const cancelSearch = () => window.clearTimeout(timer);

            function updateCategories() {
                const count = checkboxes.filter(input => input.checked).length;
                counter.textContent = count ? `Categories (${count})` : 'Categories';
                groups.forEach(group => {
                    const visible = [...group.querySelectorAll('[data-category-option]')]
                        .filter(option => !option.hidden)
                        .map(option => option.querySelector('input'));
                    const selected = visible.length > 0 && visible.every(input => input.checked);
                    group.querySelector('[data-category-select]').textContent = selected ? 'Clear group' : 'Select group';
                });
            }

            checkboxes.forEach(input => input.addEventListener('change', updateCategories));
            form.addEventListener('change', cancelSearch);
            form.addEventListener('submit', cancelSearch);
            groups.forEach(group => group.querySelector('[data-category-select]').addEventListener('click', () => {
                cancelSearch();
                const visible = [...group.querySelectorAll('[data-category-option]')]
                    .filter(option => !option.hidden)
                    .map(option => option.querySelector('input'));
                const select = !visible.every(input => input.checked);
                visible.forEach(input => { input.checked = select; });
                updateCategories();
                form.dispatchEvent(new Event('change', { bubbles: true }));
            }));
            form.querySelector('[data-category-clear]').addEventListener('click', () => {
                cancelSearch();
                checkboxes.forEach(input => { input.checked = false; });
                updateCategories();
                form.dispatchEvent(new Event('change', { bubbles: true }));
            });
            finder.addEventListener('input', () => {
                cancelSearch();
                const query = finder.value.trim().toLocaleLowerCase();
                groups.forEach(group => {
                    const options = [...group.querySelectorAll('[data-category-option]')];
                    options.forEach(option => { option.hidden = !option.dataset.categoryName.toLocaleLowerCase().includes(query); });
                    group.hidden = options.every(option => option.hidden);
                });
                form.querySelector('[data-category-empty]').hidden = groups.some(group => !group.hidden);
                updateCategories();
            });
            updateCategories();

            function scheduleSearch(event) {
                cancelSearch();
                if (event?.isComposing) return;
                const value = keyword.value.trim();
                if (value.length >= 2 || value.length === 0) {
                    timer = window.setTimeout(() => { if (form.isConnected) form.requestSubmit(); }, 500);
                }
            }
            keyword.addEventListener('input', scheduleSearch);
            keyword.addEventListener('compositionstart', cancelSearch);
            keyword.addEventListener('compositionend', scheduleSearch);
            // Opening category controls cancels a pending keyword-only navigation.
            form.querySelector('[data-bs-toggle="collapse"]').addEventListener('click', cancelSearch);
            form.addEventListener('focusin', event => {
                if (event.target !== keyword) cancelSearch();
            });
            const params = new URLSearchParams(window.location.search);
            if (focus && params.get('keyword') && [...params.keys()].every(key => ['keyword', 'page', 'sort', 'direction', 'genre', 'torrent_status'].includes(key)) && !params.get('genre') && ['active', null].includes(params.get('torrent_status'))) {
                keyword.focus();
                keyword.setSelectionRange(keyword.value.length, keyword.value.length);
            }
        });
    }
    initialize({ focus: true });
    document.addEventListener('torrent:updated', initialize);
    document.addEventListener('keydown', event => {
        if (event.key !== '/' || event.ctrlKey || event.metaKey || event.altKey || event.isComposing) return;
        const active = document.activeElement;
        if (active?.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(active?.tagName ?? '')) return;
        event.preventDefault();
        const keyword = document.querySelector('[data-search-keyword]');
        if (!keyword) return;
        keyword.focus();
        keyword.setSelectionRange(keyword.value.length, keyword.value.length);
    });
})();
