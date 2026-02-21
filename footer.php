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
        <div class="container-fluid">
            <section class="p-3">
                <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-center gap-4">
                    <!-- Logo -->
                    <div class="it-brand-wrapper flex-shrink-0">
                        <a href="#">
                            <?php if ($white_logo) : ?>
                                <div class="it-brand-text pe-0">
                                    <img src="<?php echo esc_url($white_logo); ?>" alt="TODO" height="120">
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- Menu -->
                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-4 w-100">
                        <?php if (has_nav_menu('footer-one') && !empty(get_menu_by_location('footer-one'))): ?>
                            <div class="pb-2">
                                <h4 class="h6 text-uppercase"><?php echo wp_get_nav_menu_name('footer-one') ?? ''; ?></h4>
                                <div class="link-list-wrapper">
                                    <?php wp_nav_menu(['theme_location' => 'footer-one', 'container' => false, 'fallback_cb' => '__return_false', 'items_wrap' => '<ul id="%1$s" class="footer-list link-list clearfix %2$s">%3$s</ul>', 'depth' => 1, 'walker' => new bootstrap_5_wp_simple_menu_walker()]); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (has_nav_menu('footer-two') && !empty(get_menu_by_location('footer-two'))): ?>
                            <div class="pb-2">
                                <h4 class="h6 text-uppercase"><?php echo wp_get_nav_menu_name('footer-two') ?? ''; ?></h4>
                                <div class="link-list-wrapper">
                                    <?php wp_nav_menu(['theme_location' => 'footer-two', 'container' => false, 'fallback_cb' => '__return_false', 'items_wrap' => '<ul id="%1$s" class="footer-list link-list clearfix %2$s">%3$s</ul>', 'depth' => 1, 'walker' => new bootstrap_5_wp_simple_menu_walker()]); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (has_nav_menu('footer-three') && !empty(get_menu_by_location('footer-three'))): ?>
                            <div class="pb-2">
                                <h4 class="h6 text-uppercase"><?php echo wp_get_nav_menu_name('footer-three') ?? ''; ?></h4>
                                <div class="link-list-wrapper">
                                    <?php wp_nav_menu(['theme_location' => 'footer-three', 'container' => false, 'fallback_cb' => '__return_false', 'items_wrap' => '<ul id="%1$s" class="footer-list link-list clearfix %2$s">%3$s</ul>', 'depth' => 1, 'walker' => new bootstrap_5_wp_simple_menu_walker()]); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (has_nav_menu('footer-four') && !empty(get_menu_by_location('footer-four'))): ?>
                            <div class="pb-2">
                                <h4 class="h6 text-uppercase"><?php echo wp_get_nav_menu_name('footer-four') ?? ''; ?></h4>
                                <div class="link-list-wrapper">
                                    <?php wp_nav_menu(['theme_location' => 'footer-four', 'container' => false, 'fallback_cb' => '__return_false', 'items_wrap' => '<ul id="%1$s" class="footer-list link-list clearfix %2$s">%3$s</ul>', 'depth' => 1, 'walker' => new bootstrap_5_wp_simple_menu_walker()]); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <div class="px-3">
                <section class="py-4 px-0 border-white border-top">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 pb-2">
                            <h4>
                                <a href="#" title="Vai alla pagina: Amministrazione">Amministrazione trasparente</a>
                            </h4>
                            <p>I dati personali pubblicati sono riutilizzabili solo alle condizioni previste dalla direttiva
                                comunitaria 2003/98/CE e dal d.lgs. 36/2006</p>
                        </div>
                        <div class="col-lg-4 col-md-4 offset-lg-1 pb-2">
                            <p>
                                <strong><?php echo esc_html($city); ?></strong><br/>
                                <?php echo esc_html($address); ?> - <?php echo esc_html($phone); ?>
                            </p>
                            <div class="link-list-wrapper">
                                <ul class="footer-list link-list clearfix">
                                    <?php if (!empty($urp_url)) : ?>
                                        <li>
                                            <a class="list-item" href="<?php echo esc_url($urp_url); ?>"
                                               title="Vai alla pagina: URP - Ufficio Relazioni con il Pubblico">URP -
                                                Ufficio Relazioni con il Pubblico</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (!empty($admin_transparent_url)) : ?>
                                        <li>
                                            <a class="list-item" href="<?php echo esc_url($admin_transparent_url); ?>"
                                               title="Vai alla pagina: Amministrazione Trasparente">Amministrazione
                                                Trasparente</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <?php
                        // Verifica se almeno un campo dei social è popolato
                        $facebook_url = get_theme_mod('site_socials_fb');
                        $instagram_url = get_theme_mod('site_socials_instagram');
                        $twitter_url = get_theme_mod('site_socials_twitter');

                        if (!empty($facebook_url) && !empty($instagram_url) && !empty($twitter_url)) {
                            ?>
                            <div class="col-md-auto ms-md-auto pb-2">
                                <h4>
                                    <a href="#" title="Vai alla pagina: Seguici su">Seguici su</a>
                                </h4>
                                <ul class="list-inline text-start social">
                                    <?php
                                    // Facebook
                                    if (!empty($facebook_url)) {
                                        echo '<li class="list-inline-item"><a class="p-2 text-white" href="' . esc_url($facebook_url) . '" target="_blank"><svg class="icon icon-sm icon-white align-top"><use xlink:href="' . esc_url(get_template_directory_uri()) . '/dist/images/sprites.svg#it-facebook"></use></svg><span class="visually-hidden">Facebook</span></a></li>';
                                    }

                                    // Instagram
                                    if (!empty($instagram_url)) {
                                        echo '<li class="list-inline-item"><a class="p-2 text-white" href="' . esc_url($instagram_url) . '" target="_blank"><svg class="icon icon-sm icon-white align-top"><use xlink:href="' . esc_url(get_template_directory_uri()) . '/dist/images/sprites.svg#it-instagram"></use></svg><span class="visually-hidden">Instagram</span></a></li>';
                                    }

                                    // Twitter
                                    if (!empty($twitter_url)) {
                                        echo '<li class="list-inline-item"><a class="p-2 text-white" href="' . esc_url($twitter_url) . '" target="_blank"><svg class="icon icon-sm icon-white align-top"><use xlink:href="' . esc_url(get_template_directory_uri()) . '/dist/images/sprites.svg#it-twitter"></use></svg><span class="visually-hidden">Twitter</span></a></li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php
                        }
                        ?>

                    </div>
                </section>
            </div>
        </div>
    </div>
    <div class="it-footer-small-prints clearfix py-3">
        <div class="container-fluid">
            <h3 class="visually-hidden"><?php echo wp_get_nav_menu_name('footer-bottom') ?? ''; ?></h3>
            <!-- Wrapper per il menu e il credit -->
            <div class="d-flex justify-content-between flex-wrap">
                <ul class="it-footer-small-prints-list list-inline mb-0">
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
                <!-- Credit -->
                <div class="credit text-white">
                    <a class="d-inline-flex align-items-center text-white fw-normal text-nowrap" href="https://github.com/ttensrl/wp-digital-italia" target="_blank">
                        <?php echo __(
                            'Realizzato con il Tema',
                            'wp-digital-italia'
                        ); ?>
                        <span class="strong fw-bold ms-2"><?php echo __(
                                'Digital Italia',
                                'wp-digital-italia'
                            ); ?></span>
                        <svg class="icon icon-sm icon-white align-top ms-2" aria-hidden="true"><use xlink:href="<?php echo get_template_directory_uri() . '/dist/images/sprites.svg#it-github'; ?>"></use></svg>
                        <span class="visually-hidden"> (GitHub)</span>
                    </a>
                </div>
                <!-- /Credit -->

            </div>
        </div>
    </div>

</footer>

<a href="#" aria-hidden="true" tabindex="-1" data-attribute="back-to-top" class="back-to-top">
    <svg class="icon icon-light">
        <use xlink:href="<?php echo get_template_directory_uri(); ?>/dist/images/sprites.svg#it-arrow-up"></use>
    </svg>
</a>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
