<section class="public-panel public-panel--soft">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Blocchi reali del dashboard</p>
        <h2>Prodotti mostrati con card indice e pannello info coerenti con la UI admin</h2>
    </div>

    <div class="dashboard-preview-grid">
        <div class="dashboard-preview-stack prod_index">
            <div class="res-item prod" data-product-id="148">
                <div class="no_img">
                    <i class="bi bi-image-fill"></i>
                </div>

                <div class="name_cat">
                    <div class="name">Tagliatelle al ragu della casa</div>
                    <div class="cat">Primi piatti</div>
                </div>

                <div class="price_btn">
                    <div class="price">{{ $appCurrency['symbol'] }}14,00</div>
                    <button type="button" class="action_menu action_menu_info">
                        <i class="bi bi-info-circle-fill" style="font-size: var(--fs-400)"></i>Info
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-content dashboard-product-modal-preview">
            <div class="modal-body">
                <div class="name_cat">
                    <div class="name">Tagliatelle al ragu della casa</div>
                    <div class="cat">Primi piatti</div>
                </div>

                <section>
                    <h4>Ingredienti</h4>
                    <p>Pasta fresca all uovo, ragu di manzo, pomodoro, parmigiano.</p>
                </section>

                <section>
                    <h4>Descrizione</h4>
                    <p>Piatto visibile nel catalogo con descrizione, prezzo e informazioni utili al servizio.</p>
                </section>

                <div class="price">{{ $appCurrency['symbol'] }}14,00</div>

                <div class="allergens">
                    <div class="al">Glutine</div>
                    <div class="al">Uova</div>
                    <div class="al">Sedano</div>
                    <x-dashboard.state-pill tone="active">Visibile</x-dashboard.state-pill>
                </div>
            </div>
        </div>
    </div>
</section>
