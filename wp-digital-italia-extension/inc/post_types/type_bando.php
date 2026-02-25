<?php

add_action( 'init', 'dci_register_post_type_bando' );
function dci_register_post_type_bando(): void
{
    $labels = array(
        'name'          => _x( 'Bandi', 'Post Type General Name', 'wp-digital-italia' ),
        'singular_name' => _x( 'Bando', 'Post Type Singular Name', 'wp-digital-italia' ),
        'add_new'       => _x( 'Aggiungi un Bando', 'Post Type Singular Name', 'wp-digital-italia' ),
        'add_new_item'  => _x( 'Aggiungi un Bando', 'Post Type Singular Name', 'wp-digital-italia' ),
        'edit_item'     => _x( 'Modifica il Bando', 'Post Type Singular Name', 'wp-digital-italia' ),
        'view_item'     => _x( 'Visualizza il Bando', 'Post Type Singular Name', 'wp-digital-italia' ),
    );

    $args = array(
        'label'                 => __( 'Bando', 'wp-digital-italia' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor' ),
        'hierarchical'          => false,
        'public'                => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-megaphone',
        'has_archive'           => 'bandi',
        // Disabilitiamo il rewrite automatico: lo gestiamo tutto noi
        'rewrite'               => false,
        'description'           => __( 'Bandi di gara e concorsi.', 'wp-digital-italia' ),
        'show_in_rest'          => true,
        'rest_base'             => 'bandi',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type( 'bando', $args );
}

/**
 * Genera il permalink del singolo bando come /bandi/{anno}/{slug}/
 */
add_filter( 'post_type_link', 'bando_year_archive_link', 10, 2 );
function bando_year_archive_link( $post_link, $post ) {
    if ( 'bando' === $post->post_type ) {
        $data_publish = get_post_meta( $post->ID, '_dci_bando_data_publish', true );
        $year = ! empty( $data_publish )
            ? date( 'Y', strtotime( $data_publish ) )
            : get_the_date( 'Y', $post );
        return home_url( "/bandi/{$year}/{$post->post_name}/" );
    }
    return $post_link;
}

/**
 * Rewrite rules.
 *
 * Il problema di avere /bandi/{anno}/ e /bandi/{anno}/{slug}/ è che
 * WordPress non può distinguerli solo dal pattern regex: entrambi matchano
 * ^bandi/([0-9]{4})/([^/]+)?$
 *
 * Soluzione: usiamo SEMPRE due query var (bando_year + bando_slug) per il
 * singolo, e solo bando_year per l'archivio. Poi in pre_get_posts
 * decidiamo cosa fare in base a queste var.
 *
 * Ordine importante: le regole più specifiche (più segmenti, keyword "page")
 * vanno registrate PER PRIME perché WordPress le valuta dall'alto.
 */
add_action( 'init', 'bando_add_rewrite_rules' );
function bando_add_rewrite_rules() {

    // Paginazione archivio anno — 4 segmenti, va prima del singolo a 3 segmenti
    add_rewrite_rule(
        '^bandi/([0-9]{4})/page/([0-9]+)/?$',
        'index.php?post_type=bando&bando_year=$matches[1]&paged=$matches[2]',
        'top'
    );

    // Singolo bando — 3 segmenti: /bandi/{anno}/{slug}/
    // Usiamo bando_slug come var dedicata così pre_get_posts sa che è un singolo
    add_rewrite_rule(
        '^bandi/([0-9]{4})/([^/]+)/?$',
        'index.php?post_type=bando&bando_year=$matches[1]&bando_slug=$matches[2]',
        'top'
    );

    // Archivio per anno — 2 segmenti: /bandi/{anno}/
    add_rewrite_rule(
        '^bandi/([0-9]{4})/?$',
        'index.php?post_type=bando&bando_year=$matches[1]',
        'top'
    );

    // Paginazione archivio generale
    add_rewrite_rule(
        '^bandi/page/([0-9]+)/?$',
        'index.php?post_type=bando&paged=$matches[1]',
        'top'
    );

    // Archivio generale
    add_rewrite_rule(
        '^bandi/?$',
        'index.php?post_type=bando',
        'top'
    );
}

/**
 * Registra le query var custom.
 */
add_filter( 'query_vars', 'bando_add_query_vars' );
function bando_add_query_vars( $vars ) {
    $vars[] = 'bando_year';
    $vars[] = 'bando_slug';
    return $vars;
}

/**
 * Hook centrale: gestisce singolo e archivio-anno in base alle query var.
 *
 * FIX ordinamento decrescente:
 * - Named clauses nella meta_query (data_publish_clause / data_publish_missing)
 *   consentono di referenziarle direttamente in orderby.
 * - WordPress genera una LEFT JOIN invece di INNER JOIN, così i post senza
 *   il meta non vengono esclusi ma rimangono ordinati per data post come fallback.
 * - Si evita meta_key globale che in presenza di OR/NOT EXISTS crea conflitti
 *   nel JOIN e rompe l'ordinamento.
 */
add_action( 'pre_get_posts', 'bando_handle_query' );
function bando_handle_query( WP_Query $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    $bando_year = $query->get( 'bando_year' );
    $bando_slug = $query->get( 'bando_slug' );

    // ── Singolo bando ────────────────────────────────────────────────────────
    if ( $bando_slug ) {
        $query->set( 'post_type', 'bando' );
        $query->set( 'name', $bando_slug );
        // Puliamo le var extra per non interferire
        $query->set( 'bando_year', '' );
        $query->set( 'bando_slug', '' );

        $query->is_single            = true;
        $query->is_singular          = true;
        $query->is_archive           = false;
        $query->is_post_type_archive = false;
        return;
    }

    // ── Archivio (generale o per anno) ───────────────────────────────────────
    if ( $query->get( 'post_type' ) === 'bando' || $bando_year ) {
        $query->set( 'post_type', 'bando' );
        $query->set( 'posts_per_page', 10 );

        // Meta query base con named clauses per supportare l'ordinamento
        // anche quando il campo _dci_bando_data_publish non esiste (LEFT JOIN)
        $meta_query = array(
            'relation' => 'OR',
            'data_publish_clause' => array(
                'key'     => '_dci_bando_data_publish',
                'compare' => 'EXISTS',
            ),
            'data_publish_missing' => array(
                'key'     => '_dci_bando_data_publish',
                'compare' => 'NOT EXISTS',
            ),
        );

        // ── Filtro per anno ──────────────────────────────────────────────────
        if ( $bando_year ) {
            $year = intval( $bando_year );

            $meta_query = array(
                'relation' => 'OR',
                'data_publish_clause' => array(
                    'relation' => 'AND',
                    array(
                        'key'     => '_dci_bando_data_publish',
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'key'     => '_dci_bando_data_publish',
                        'value'   => array( "{$year}-01-01", "{$year}-12-31" ),
                        'compare' => 'BETWEEN',
                        'type'    => 'DATE',
                    ),
                ),
                'data_publish_missing' => array(
                    'relation' => 'AND',
                    array(
                        'key'     => '_dci_bando_data_publish',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'year' => $year,
                    ),
                ),
            );

            $query->is_archive           = true;
            $query->is_post_type_archive = true;
            $query->is_single            = false;
            $query->is_singular          = false;
        }

        $query->set( 'meta_query', $meta_query );

        // Ordina per meta (named clause) in discesa, poi per data post come fallback
        $query->set( 'orderby', array(
            'data_publish_clause' => 'DESC',
            'date'                => 'DESC',
        ) );
    }
}


// ─── METABOX ─────────────────────────────────────────────────────────────────

add_action( 'cmb2_init', 'dci_add_bando_metaboxes' );
function dci_add_bando_metaboxes(): void
{
    $prefix = '_dci_bando_';

    $cmb_dati = new_cmb2_box( array(
        'id'           => $prefix . 'box_dati',
        'title'        => __( 'Dettagli Bando', 'wp-digital-italia' ),
        'object_types' => array( 'bando' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    $cmb_dati->add_field( array(
        'id'   => $prefix . 'tipo',
        'name' => __( 'Tipo', 'wp-digital-italia' ),
        'desc' => __( 'es: bando, avviso, esito', 'wp-digital-italia' ),
        'type' => 'text',
    ) );

    $cmb_dati->add_field( array(
        'id'   => $prefix . 'contratto',
        'name' => __( 'Contratto', 'wp-digital-italia' ),
        'desc' => __( 'es: forniture, servizi, lavori', 'wp-digital-italia' ),
        'type' => 'text',
    ) );

    $cmb_dati->add_field( array(
        'id'   => $prefix . 'amministrazione_aggiudicatrice',
        'name' => __( 'Amministrazione Aggiudicatrice', 'wp-digital-italia' ),
        'type' => 'text',
    ) );

    $cmb_dati->add_field( array(
        'id'   => $prefix . 'tipo_amministrazione',
        'name' => __( 'Tipo di Amministrazione', 'wp-digital-italia' ),
        'type' => 'text',
    ) );

    $cmb_dati->add_field( array(
        'id'   => $prefix . 'importo',
        'name' => __( 'Importo', 'wp-digital-italia' ),
        'desc' => __( 'es: € 650.000.000,00 IVA Esclusa', 'wp-digital-italia' ),
        'type' => 'text',
    ) );

    $cmb_dati->add_field( array(
        'id'   => $prefix . 'cig',
        'name' => __( 'CIG', 'wp-digital-italia' ),
        'type' => 'text',
    ) );

    $cmb_dati->add_field( array(
        'id'   => $prefix . 'cpv',
        'name' => __( 'Codice CPV', 'wp-digital-italia' ),
        'type' => 'text',
    ) );

    $cmb_dati->add_field( array(
        'id'   => $prefix . 'data_publish',
        'name' => __( 'Data di pubblicazione', 'wp-digital-italia' ),
        'type' => 'text_date',
    ) );

    $cmb_dati->add_field( array(
        'id'   => $prefix . 'data_scadenza',
        'name' => __( 'Data di scadenza del bando', 'wp-digital-italia' ),
        'type' => 'text_date',
    ) );

    $cmb_documentazione = new_cmb2_box( array(
        'id'           => $prefix . 'box_documentazione',
        'title'        => __( 'Documentazione', 'wp-digital-italia' ),
        'object_types' => array( 'bando' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    $group_allegati = $cmb_documentazione->add_field( array(
        'id'          => $prefix . 'allegati',
        'type'        => 'group',
        'description' => __( 'Allegati al bando', 'wp-digital-italia' ),
        'options'     => array(
            'group_title'   => __( 'Allegato {#}', 'wp-digital-italia' ),
            'add_button'    => __( 'Aggiungi Allegato', 'wp-digital-italia' ),
            'remove_button' => __( 'Rimuovi Allegato', 'wp-digital-italia' ),
            'sortable'      => true,
        ),
    ) );

    $cmb_documentazione->add_group_field( $group_allegati, array(
        'name' => __( 'Titolo', 'wp-digital-italia' ),
        'id'   => 'titolo',
        'type' => 'text',
    ) );

    $cmb_documentazione->add_group_field( $group_allegati, array(
        'name' => __( 'Data', 'wp-digital-italia' ),
        'id'   => 'data',
        'type' => 'text_date',
    ) );

    $cmb_documentazione->add_group_field( $group_allegati, array(
        'name'    => __( 'File', 'wp-digital-italia' ),
        'id'      => 'file',
        'type'    => 'file',
        'options' => array( 'url' => false ),
    ) );

    $cmb_termini = new_cmb2_box( array(
        'id'           => $prefix . 'box_termini',
        'title'        => __( 'Termini', 'wp-digital-italia' ),
        'object_types' => array( 'bando' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    $group_termini = $cmb_termini->add_field( array(
        'id'          => $prefix . 'termini',
        'type'        => 'group',
        'description' => __( 'Termini e scadenze del bando', 'wp-digital-italia' ),
        'options'     => array(
            'group_title'   => __( 'Termine {#}', 'wp-digital-italia' ),
            'add_button'    => __( 'Aggiungi Termine', 'wp-digital-italia' ),
            'remove_button' => __( 'Rimuovi Termine', 'wp-digital-italia' ),
            'sortable'      => true,
        ),
    ) );

    $cmb_termini->add_group_field( $group_termini, array(
        'name' => __( 'Titolo', 'wp-digital-italia' ),
        'id'   => 'titolo',
        'type' => 'text',
    ) );

    $cmb_termini->add_group_field( $group_termini, array(
        'name' => __( 'Data', 'wp-digital-italia' ),
        'id'   => 'data',
        'type' => 'text_date',
    ) );

    $cmb_aggiudicatari = new_cmb2_box( array(
        'id'           => $prefix . 'box_aggiudicatari',
        'title'        => __( 'Aggiudicatari', 'wp-digital-italia' ),
        'object_types' => array( 'bando' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    $cmb_aggiudicatari->add_field( array(
        'id'   => $prefix . 'data_aggiudicazione',
        'name' => __( 'Data di aggiudicazione definitiva', 'wp-digital-italia' ),
        'desc' => __( 'Data di aggiudicazione in VIA DEFINITIVA EFFICACE', 'wp-digital-italia' ),
        'type' => 'text',
    ) );

    $group_aggiudicatari = $cmb_aggiudicatari->add_field( array(
        'id'          => $prefix . 'aggiudicatari',
        'type'        => 'group',
        'description' => __( 'Lotti e aggiudicatari', 'wp-digital-italia' ),
        'options'     => array(
            'group_title'   => __( 'Lotto {#}', 'wp-digital-italia' ),
            'add_button'    => __( 'Aggiungi Lotto', 'wp-digital-italia' ),
            'remove_button' => __( 'Rimuovi Lotto', 'wp-digital-italia' ),
            'sortable'      => true,
        ),
    ) );

    $cmb_aggiudicatari->add_group_field( $group_aggiudicatari, array(
        'name' => __( 'Lotto', 'wp-digital-italia' ),
        'id'   => 'lotto',
        'type' => 'text',
        'desc' => __( 'es: Lotto 1, Lotto 2', 'wp-digital-italia' ),
    ) );

    $cmb_aggiudicatari->add_group_field( $group_aggiudicatari, array(
        'name' => __( 'Valore di aggiudicazione', 'wp-digital-italia' ),
        'id'   => 'valore',
        'type' => 'text',
        'desc' => __( 'es: € 15.725.500,00', 'wp-digital-italia' ),
    ) );

    $cmb_aggiudicatari->add_group_field( $group_aggiudicatari, array(
        'name'       => __( 'Aggiudicatari', 'wp-digital-italia' ),
        'id'         => 'aggiudicatari',
        'type'       => 'textarea',
        'desc'       => __( 'Elenco degli aggiudicatari (uno per riga)', 'wp-digital-italia' ),
        'attributes' => array( 'rows' => 5 ),
    ) );
}