<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package digital-italia
 */

// Recupera i dati dai campi personalizzati
$white_logo = get_theme_mod('white_logo');
$city = get_theme_mod('site_contact_city');
$phone = get_theme_mod('site_contact_phone');
$address = get_theme_mod('site_contact_address');
$vat = get_theme_mod('site_contact_vat');
$urp_url = get_theme_mod('site_contact_urp');
$admin_transparent_url = get_theme_mod('site_contact_administration_transparent');

?>
<footer class="it-footer">
    <div class="it-footer-main">
        <div class="container">
            <section>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="it-brand-wrapper">
                            <a href="#">
                                <?php if ( $white_logo ) : ?>
                                    <div class="it-brand-text">
                                        <img src="<?php echo esc_url( $white_logo ); ?>" alt="TODO" height="120">
                                    </div>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            <section>
                <div class="row">
                    <?php if(has_nav_menu('footer-one') && !empty(get_menu_by_location('footer-one'))): ?>
                    <div class="col-lg-3 col-md-3 col-sm-6 pb-2">
                        <h4 class="h6 text-uppercase">
                            <?php echo wp_get_nav_menu_name('footer-one') ?? ''; ?>
                        </h4>
                        <div class="link-list-wrapper">
                            <?php
                            wp_nav_menu(
                                [
                                    'theme_location' => 'footer-one',
                                    'container' => false,
                                    'menu_class' => 'list-item',
                                    'fallback_cb' => '__return_false',
                                    'items_wrap' => '<ul id="%1$s" class="footer-list link-list clearfix %2$s">%3$s</ul>',
                                    'depth' => 1,
                                    'walker' => new bootstrap_5_wp_simple_menu_walker()
                                ]
                            );
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if(has_nav_menu('footer-two') && !empty(get_menu_by_location('footer-two'))): ?>
                    <div class="col-lg-3 col-md-3 col-sm-6 pb-2">
                        <h4 class="h6 text-uppercase">
                            <?php echo wp_get_nav_menu_name('footer-two') ?? ''; ?>
                        </h4>
                        <div class="link-list-wrapper">
                            <ul class="footer-list link-list clearfix">
                                <?php
                                wp_nav_menu(
                                    [
                                        'theme_location' => 'footer-two',
                                        'container' => false,
                                        'menu_class' => 'list-item',
                                        'fallback_cb' => '__return_false',
                                        'items_wrap' => '<ul id="%1$s" class="footer-list link-list clearfix %2$s">%3$s</ul>',
                                        'depth' => 1,
                                        'walker' => new bootstrap_5_wp_simple_menu_walker()
                                    ]
                                );
                                ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if(has_nav_menu('footer-three') && !empty(get_menu_by_location('footer-three'))): ?>
                    <div class="col-lg-3 col-md-3 col-sm-6 pb-2">
                        <h4 class="h6 text-uppercase">
                            <?php echo wp_get_nav_menu_name('footer-three') ?? ''; ?>
                        </h4>
                        <div class="link-list-wrapper">
                            <ul class="footer-list link-list clearfix">
                                <?php
                                wp_nav_menu(
                                    [
                                        'theme_location' => 'footer-three',
                                        'container' => false,
                                        'menu_class' => 'list-item',
                                        'fallback_cb' => '__return_false',
                                        'items_wrap' => '<ul id="%1$s" class="footer-list link-list clearfix %2$s">%3$s</ul>',
                                        'depth' => 1,
                                        'walker' => new bootstrap_5_wp_simple_menu_walker()
                                    ]
                                );
                                ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if(has_nav_menu('footer-four') && !empty(get_menu_by_location('footer-four'))): ?>
                    <div class="col-lg-3 col-md-3 col-sm-6">
                        <h4 class="h6 text-uppercase">
                            <?php echo wp_get_nav_menu_name('footer-four') ?? ''; ?>
                        </h4>
                        <div class="link-list-wrapper">
                            <ul class="footer-list link-list clearfix">
                                <?php
                                wp_nav_menu(
                                    [
                                        'theme_location' => 'footer-four',
                                        'container' => false,
                                        'menu_class' => 'list-item',
                                        'fallback_cb' => '__return_false',
                                        'items_wrap' => '<ul id="%1$s" class="footer-list link-list clearfix %2$s">%3$s</ul>',
                                        'depth' => 1,
                                        'walker' => new bootstrap_5_wp_simple_menu_walker()
                                    ]
                                );
                                ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <section class="py-4 border-white border-top">
                <div class="row">
                    <div class="col-lg-4 col-md-4 pb-2">
                        <h4>
                            <a href="#" title="Vai alla pagina: Amministrazione">Amministrazione trasparente</a>
                        </h4>
                        <p>I dati personali pubblicati sono riutilizzabili solo alle condizioni previste dalla direttiva comunitaria 2003/98/CE e dal d.lgs. 36/2006</p>
                    </div>
                    <div class="col-lg-4 col-md-4 pb-2">
                        <p>
                            <strong><?php echo esc_html($city); ?></strong><br />
                            <?php echo esc_html($address); ?> - <?php echo esc_html($phone); ?>
                        </p>
                        <div class="link-list-wrapper">
                            <ul class="footer-list link-list clearfix">
                                <?php if (!empty($urp_url)) : ?>
                                    <li>
                                        <a class="list-item" href="<?php echo esc_url($urp_url); ?>" title="Vai alla pagina: URP - Ufficio Relazioni con il Pubblico">URP - Ufficio Relazioni con il Pubblico</a>
                                    </li>
                                <?php endif; ?>

                                <?php if (!empty($admin_transparent_url)) : ?>
                                    <li>
                                        <a class="list-item" href="<?php echo esc_url($admin_transparent_url); ?>" title="Vai alla pagina: Amministrazione Trasparente">Amministrazione Trasparente</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 pb-2">
                        <h4>
                            <a href="#" title="Vai alla pagina: Seguici su">Seguici su</a>
                        </h4>
                        <ul class="list-inline text-start social">
                            <?php
                            // Verifica se i campi dei social sono popolati
                            $facebook_url = get_theme_mod('site_socials_fb');
                            $instagram_url = get_theme_mod('site_socials_instagram');
                            $twitter_url = get_theme_mod('site_socials_twitter');

                            // Facebook
                            if (!empty($facebook_url)) {
                                echo '<li class="list-inline-item"><a class="p-2 text-white" href="' . esc_url($facebook_url) . '" target="_blank"><svg class="icon icon-sm icon-white align-top"><use xlink:href="' . esc_url(get_template_directory_uri()) . '/bootstrap-italia/svg/sprites.svg#it-facebook"></use></svg><span class="visually-hidden">Facebook</span></a></li>';
                            }

                            // Instagram
                            if (!empty($instagram_url)) {
                                echo '<li class="list-inline-item"><a class="p-2 text-white" href="' . esc_url($instagram_url) . '" target="_blank"><svg class="icon icon-sm icon-white align-top"><use xlink:href="' . esc_url(get_template_directory_uri()) . '/bootstrap-italia/svg/sprites.svg#it-instagram"></use></svg><span class="visually-hidden">Instagram</span></a></li>';
                            }

                            // Twitter
                            if (!empty($twitter_url)) {
                                echo '<li class="list-inline-item"><a class="p-2 text-white" href="' . esc_url($twitter_url) . '" target="_blank"><svg class="icon icon-sm icon-white align-top"><use xlink:href="' . esc_url(get_template_directory_uri()) . '/bootstrap-italia/svg/sprites.svg#it-twitter"></use></svg><span class="visually-hidden">Twitter</span></a></li>';
                            }
                            ?>
                        </ul>

                    </div>
                </div>
            </section>
        </div>
    </div>
    <?php if(has_nav_menu('footer-bottom') && !empty(get_menu_by_location('footer-bottom'))): ?>
    <div class="it-footer-small-prints clearfix">
        <div class="container">
            <h3 class="visually-hidden"><?php echo wp_get_nav_menu_name('footer-bottom') ?? ''; ?></h3>
            <ul class="it-footer-small-prints-list list-inline mb-0 d-flex flex-column flex-md-row">
                <?php
                wp_nav_menu(
                    [
                        'theme_location' => 'footer-bottom',
                        'container' => false,
                        'menu_class' => 'list-inline-item',
                        'fallback_cb' => '__return_false',
                        'items_wrap' => '<ul id="%1$s" class="footer-list link-list clearfix %2$s">%3$s</ul>',
                        'depth' => 1,
                        'walker' => new bootstrap_5_wp_inline_menu_walker()
                    ]
                );
                ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</footer>

<a href="#" aria-hidden="true" tabindex="-1" data-attribute="back-to-top" class="back-to-top">
    <svg class="icon icon-light">
        <use xlink:href="<?php echo get_template_directory_uri(); ?>/bootstrap-italia/svg/sprites.svg#it-arrow-up"></use>
    </svg>
</a>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
