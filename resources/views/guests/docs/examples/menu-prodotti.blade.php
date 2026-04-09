<section class="public-panel public-panel--soft">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Blocchi reali del dashboard</p>
        <h2>Prodotti mostrati con card indice e pannello info coerenti con la UI admin</h2>
    </div>

    <div class="dashboard-preview-grid">
        <div class="dashboard-preview-stack prod_index">
            <div class="res-item prod" data-product-id="148">
                <div class="no_img">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-image-fill" viewBox="0 0 16 16">
                        <path d="M.002 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-12a2 2 0 0 1-2-2zm1 9v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062zm5-6.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
                    </svg>
                </div>

                <div class="name_cat">
                    <div class="name">Tagliatelle al ragu della casa</div>
                    <div class="cat">Primi piatti</div>
                </div>

                <div class="price_btn">
                    <div class="price">€14,00</div>
                    <button type="button" class="action_menu action_menu_info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
                        </svg>Info
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

                <div class="price">€14,00</div>

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
