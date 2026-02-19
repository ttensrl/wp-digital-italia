<!-- SIDEBAR UNICA - Contenuto dinamico gestito da JavaScript -->
<div class="col-12 col-lg-3 mb-4 d-lg-block" id="booking-sidebar">
    <div class="cmp-navscroll sticky-top" aria-labelledby="sidebar-title">
        <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="INFORMAZIONI RICHIESTE" data-bs-navscroll>
            <div class="navbar-custom" id="navbarNavProgress">
                <div class="menu-wrapper">
                    <div class="link-list-wrapper">
                        <div class="accordion">
                            <div class="accordion-item">
                                <span class="accordion-header" id="sidebar-title">
                                    <button class="accordion-button pb-10 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-collapse" aria-expanded="true" aria-controls="sidebar-collapse">
                                        INFORMAZIONI RICHIESTE
                                        <svg class="icon icon-xs right">
                                            <use href="#it-expand"></use>
                                        </svg>
                                    </button>
                                </span>
                                <div class="progress">
                                    <div class="progress-bar it-navscroll-progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div id="sidebar-collapse" class="accordion-collapse collapse show" role="region" aria-labelledby="sidebar-title">
                                    <div class="accordion-body">
                                        <!-- Contenuto dinamico inserito via JavaScript -->
                                        <ul class="link-list" data-element="page-index" id="sidebar-links">
                                            <!-- Links inseriti dinamicamente -->
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <?php do_action('gov_auth_booking_sidebar'); ?>
    </div>
</div>

