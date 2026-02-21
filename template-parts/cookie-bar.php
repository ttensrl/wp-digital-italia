<?php
 // Verifica se i campi dei cookies sono popolati
    $banner_text = get_theme_mod('banner_text_setting');
    $enable_analytics = get_theme_mod('cookies_settings_consent_analytics', false);
    $enable_marketing_cookies = get_theme_mod('cookies_settings_consent_marketing', false);
    $enable_social_cookies = get_theme_mod('cookies_settings_consent_social', false);
    $enable_youtube_cookies = get_theme_mod('cookies_settings_consent_youtube', false);
?>

<!-- COOKIE SETTINGS -->
<div class="modal fade" id="cookieModal" tabindex="-1" aria-labelledby="cookieModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cookieModalLabel"><?php echo esc_html__('Preferenze Cookies', 'wp-digital-italia'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Chiudi', 'wp-digital-italia'); ?>"></button>
            </div>
            <div class="modal-body">
                <?php if($enable_analytics): ?>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input cookie-option" id="cookies_settings_consent_analytics">
                        <label class="form-check-label" for="cookies_settings_consent_analytics"><?php echo esc_html__('Abilita cookies di Analytics', 'wp-digital-italia'); ?></label>
                    </div>
                <?php endif; ?>
                <?php if($enable_marketing_cookies): ?>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input cookie-option" id="cookies_settings_consent_marketing">
                        <label class="form-check-label" for="cookies_settings_consent_marketing"><?php echo esc_html__('Abilita cookies di Marketing', 'wp-digital-italia'); ?></label>
                    </div>
                <?php endif; ?>
                <?php if($enable_social_cookies): ?>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input cookie-option" id="cookies_settings_consent_social">
                        <label class="form-check-label" for="cookies_settings_consent_social"><?php echo esc_html__('Abilita cookies Social', 'wp-digital-italia'); ?></label>
                    </div>
                <?php endif; ?>
                <?php if($enable_youtube_cookies): ?>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input cookie-option" id="cookies_settings_consent_youtube">
                        <label class="form-check-label" for="cookies_settings_consent_youtube"><?php echo esc_html__('Abilita cookies di YouTube', 'wp-digital-italia'); ?></label>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo esc_html__('Chiudi', 'wp-digital-italia'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- /COOKIES SETTINGS -->

<section class="cookiebar fade" aria-label="<?php echo esc_attr__('Gestione dei cookies', 'wp-digital-italia'); ?>">
    <?php if(!empty($banner_text)): ?>
        <p><?php echo esc_html__($banner_text, 'wp-digital-italia'); ?></p>
    <?php endif; ?>
    <div class="cookiebar-buttons">
        <a href="#" id="cookies-settings" class="cookiebar-btn"><?php echo esc_html__('Preferenze', 'wp-digital-italia'); ?><span class="visually-hidden"><?php echo esc_html__('cookies', 'wp-digital-italia'); ?></span></a>
        <button data-bs-accept="cookiebar" class="cookiebar-btn cookiebar-confirm"><?php echo esc_html__('Accetto', 'wp-digital-italia'); ?><span class="visually-hidden"><?php echo esc_html__('i cookies', 'wp-digital-italia'); ?></span></button>
    </div>
</section>

