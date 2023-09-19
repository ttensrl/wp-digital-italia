<?php
 // Verifica se i campi dei cookies sono popolati
    $banner_text = get_theme_mod('banner_text_setting');
    $enable_analytics = get_theme_mod('enable_analytics_setting', false);
    $enable_marketing_cookies = get_theme_mod('enable_marketing_cookies_setting', false);
    $enable_social_cookies = get_theme_mod('enable_social_cookies_setting', false);
    $enable_youtube_cookies = get_theme_mod('enable_youtube_cookies_setting', false);
?>

<!-- COOKIE SETTINGS -->
<div class="modal fade" id="cookieModal" tabindex="-1" aria-labelledby="cookieModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cookieModalLabel">Preferenze Cookies</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if($enable_analytics): ?>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input cookie-option" id="enable_analytics_setting">
                    <label class="form-check-label" for="enable-analytics">Abilita cookies di Analytics</label>
                </div>
                <?php endif; ?>
                <?php if($enable_marketing_cookies): ?>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input cookie-option" id="enable_marketing_cookies_setting">
                    <label class="form-check-label" for="enable-marketing-cookies">Abilita cookies di Marketing</label>
                </div>
                <?php endif; ?>
                <?php if($enable_social_cookies): ?>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input cookie-option" id="enable_social_cookies_setting">
                    <label class="form-check-label" for="enable-social-cookies">Abilita cookies Social</label>
                </div>
                <?php endif; ?>
                <?php if($enable_youtube_cookies): ?>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input cookie-option" id="enable_youtube_cookies_setting">
                    <label class="form-check-label" for="enable-youtube-cookies">Abilita cookies di YouTube</label>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
<!-- /COOKIES SETTINGS -->

<section class="cookiebar fade" aria-label="Gestione dei cookies">
    <?php if(!empty($banner_text)): ?>
    <p><?php echo esc_html($banner_text); ?></p>
    <?php endif; ?>
    <div class="cookiebar-buttons">
        <a href="#" id="cookies-settings" class="cookiebar-btn">Preferenze<span class="visually-hidden">cookies</span></a>
        <button data-bs-accept="cookiebar" class="cookiebar-btn cookiebar-confirm">Accetto<span class="visually-hidden"> i cookies</span></button>
    </div>
</section>
