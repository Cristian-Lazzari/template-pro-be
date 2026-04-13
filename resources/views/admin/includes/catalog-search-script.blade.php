<script defer>
    document.addEventListener('DOMContentLoaded', function () {
        const page = document.querySelector('.catalog-index-page');
        if (!page) {
            return;
        }

        const input = page.querySelector('[data-catalog-search]');
        if (!input) {
            return;
        }

        const cards = Array.from(page.querySelectorAll('[data-search-name]'));
        const emptyState = input.dataset.catalogEmpty ? page.querySelector(input.dataset.catalogEmpty) : null;

        const updateSearch = () => {
            const query = input.value.trim().toLowerCase();
            let visibleCards = 0;

            cards.forEach((card) => {
                const name = (card.dataset.searchName || '').toLowerCase();
                const matches = query === '' || name.includes(query);

                card.classList.toggle('catalog-search-hidden', !matches);

                if (matches && !card.classList.contains('d-none')) {
                    visibleCards += 1;
                }
            });

            if (emptyState) {
                emptyState.classList.toggle('d-none', visibleCards !== 0);
            }
        };

        input.addEventListener('input', updateSearch);
        input.addEventListener('search', updateSearch);

        updateSearch();
    });
</script>
