<?php
/* Custom search form */
?>
<div class="di-search form-group autocomplete-wrapper">
    <form method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" accept-charset="UTF-8" class="d-flex w-100">
        <div class="input-group align-items-end flex-nowrap">
            <label for="main-search" class="visually-hidden active">Cerca nel sito</label>
            <input type="search" class="autocomplete ps-2 ps-md-5 flex-grow-1 min-w-0" name="s" id="main-search" placeholder="Cerca..." value="<?php echo get_search_query(); ?>">
            <span class="autocomplete-icon d-none d-md-block px-0" aria-hidden="true">
                <svg class="icon"><use href="<?php echo get_template_directory_uri(); ?>/bootstrap-italia/svg/sprites.svg#it-search"></use></svg>
            </span>
            <button type="submit" class="btn btn-primary rounded ms-2 ms-sm-3 ms-md-4 px-3 px-md-4">
                <span class="d-none d-md-block">Cerca</span>
                <svg class="icon icon-sm icon-white d-block d-md-none" aria-label="Cerca"><use href="<?php echo get_template_directory_uri(); ?>/bootstrap-italia/svg/sprites.svg#it-search"></use></svg>
            </button>
        </div>
    </form>
</div>
<!--
<form role="search" method="get" id="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="input-group mb-3">
    <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
        <div class="input-group">
            <label for="wp-block-search__input-1" class="visually-hidden active">Cerca</label>
            <input type="search" class="autocomplete form-control" placeholder="Cerca" id="wp-block-search__input-1" name="s">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" id="button-3" data-focus-mouse="false">Invio</button>
            </div>

            <span class="autocomplete-icon" aria-hidden="true">
              <svg class="icon icon-sm icon-primary">
                <use href="../assets/bootstrap-italia/dist/svg/sprites.svg#it-search"></use>
              </svg>
            </span>
        </div>
    </div>
</form>
 -->