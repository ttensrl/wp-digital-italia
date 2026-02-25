<?php
/**
 * Template part for displaying bando in archive/list views
 *
 * @package digital-italia
 */

$id            = get_the_ID();
$tipo          = get_post_meta( $id, '_dci_bando_tipo', true );
$data_publish  = get_post_meta( $id, '_dci_bando_data_publish', true );
$data_scadenza = get_post_meta( $id, '_dci_bando_data_scadenza', true );
$importo       = get_post_meta( $id, '_dci_bando_importo', true );
$stato         = '';

if ( ! empty( $data_scadenza ) ) {
    $scadenza_ts = strtotime( $data_scadenza );
    $oggi_ts    = current_time( 'timestamp' );
    
    if ( $scadenza_ts < $oggi_ts ) {
        $stato = 'scaduto';
    } else {
        $stato = 'attivo';
    }
}

?>

<div class="card-wrapper flex-grow-1">
    <article id="post-<?php the_ID(); ?>" <?php post_class('card card-bg border-bottom-card mt-4 mx-0'); ?>>
        <?php if ( has_post_thumbnail() ) : ?>
        <div class="img-responsive-wrapper overflow-hidden">
            <div class="img-responsive ratio ratio-21x9 pb-0 h-auto">
                <figure class="img-wrapper">
                    <?php digital_italia_post_thumbnail('img-cover', 'w-100 h-100'); ?>
                </figure>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card-body pb-3 d-flex flex-column flex-hd-row">
            <header>
                <div class="category-top">
                    <?php if ( ! empty( $tipo ) ) : ?>
                    <span class="category"><?php echo esc_html( $tipo ); ?></span>
                    <?php else : ?>
                    <span class="category"><?php esc_html_e( 'Bando', 'wp-digital-italia' ); ?></span>
                    <?php endif; ?>
                    
                    <?php if ( ! empty( $stato ) ) : ?>
                    <?php if ( $stato === 'scaduto' ) : ?>
                    <span class="data text-danger fw-semibold"><?php esc_html_e( 'Scaduto', 'wp-digital-italia' ); ?></span>
                    <?php else : ?>
                    <span class="data text-success fw-semibold"><?php esc_html_e( 'Attivo', 'wp-digital-italia' ); ?></span>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if ( $data_publish ) : ?>
                    <span class="data text-muted"><?php esc_html_e( 'Pubblicato:', 'wp-digital-italia' ); ?> <?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $data_publish ) ) ); ?></span>
                    <?php endif; ?>
                </div>
                
                <?php the_title( sprintf( '<h3 class="card-title h3"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' ); ?>
            </header>

            <div class="entry-summary">
                <?php the_excerpt(); ?>
                
                <div class="mt-3">
                    <?php if ( ! empty( $data_scadenza ) ) : ?>
                    <div class="small <?php echo $stato === 'scaduto' ? 'text-danger' : ''; ?>">
                        <strong><?php esc_html_e( 'Scadenza:', 'wp-digital-italia' ); ?></strong>
                        <?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $data_scadenza ) ) ); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( ! empty( $importo ) ) : ?>
                    <div class="small text-muted">
                        <strong><?php esc_html_e( 'Importo:', 'wp-digital-italia' ); ?></strong>
                        <?php echo esc_html( $importo ); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </article>
</div>
