<?php
/**
 * Script di verifica per il menu Appuntamenti
 *
 * Esegui questo script da WordPress admin per verificare la configurazione
 * Aggiungi ?verifica_menu=1 all'URL di admin
 */

if (isset($_GET['verifica_menu']) && current_user_can('manage_options')) {
    echo '<div style="margin: 20px; padding: 20px; background: #fff; border: 2px solid #0073aa;">';
    echo '<h2>Verifica Configurazione Menu Appuntamenti</h2>';

    // 1. Verifica costante SWB_PLUGIN_URL
    echo '<h3>1. Verifica Costante SWB_PLUGIN_URL</h3>';
    if (defined('SWB_PLUGIN_URL')) {
        echo '✅ Costante SWB_PLUGIN_URL definita: <code>' . SWB_PLUGIN_URL . '</code><br>';

        // Verifica file CSS
        $css_file = str_replace(get_template_directory_uri(), get_template_directory(), SWB_PLUGIN_URL) . 'assets/css/admin.css';
        if (file_exists($css_file)) {
            echo '✅ File CSS esiste: <code>' . $css_file . '</code><br>';
        } else {
            echo '❌ File CSS NON esiste: <code>' . $css_file . '</code><br>';
        }

        // Verifica file JS
        $js_file = str_replace(get_template_directory_uri(), get_template_directory(), SWB_PLUGIN_URL) . 'assets/js/admin.js';
        if (file_exists($js_file)) {
            echo '✅ File JS esiste: <code>' . $js_file . '</code><br>';
        } else {
            echo '❌ File JS NON esiste: <code>' . $js_file . '</code><br>';
        }
    } else {
        echo '❌ Costante SWB_PLUGIN_URL NON definita<br>';
    }

    // 2. Verifica classe SWB_Admin
    echo '<h3>2. Verifica Classe SWB_Admin</h3>';
    if (class_exists('SWB_Admin')) {
        echo '✅ Classe SWB_Admin caricata<br>';
    } else {
        echo '❌ Classe SWB_Admin NON caricata<br>';
    }

    // 3. Verifica post type appuntamento
    echo '<h3>3. Verifica Post Type Appuntamento</h3>';
    if (post_type_exists('appuntamento')) {
        echo '✅ Post type "appuntamento" registrato<br>';
        $post_type_obj = get_post_type_object('appuntamento');
        echo 'Show in menu: ' . ($post_type_obj->show_in_menu ? 'true' : 'false') . '<br>';
    } else {
        echo '❌ Post type "appuntamento" NON registrato<br>';
    }

    // 4. Verifica hook admin_menu
    echo '<h3>4. Verifica Hook admin_menu</h3>';
    global $wp_filter;
    if (isset($wp_filter['admin_menu'])) {
        echo '✅ Hook admin_menu presente<br>';
        echo 'Callbacks registrati:<br>';
        foreach ($wp_filter['admin_menu']->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function'])) {
                    $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                    $method = $callback['function'][1];
                    if ($class === 'SWB_Admin' && $method === 'add_admin_menu') {
                        echo '✅ SWB_Admin::add_admin_menu registrato con priorità ' . $priority . '<br>';
                    }
                }
            }
        }
    }

    // 5. Verifica menu WordPress
    echo '<h3>5. Verifica Menu WordPress</h3>';
    global $menu;
    $menu_trovato = false;
    foreach ($menu as $menu_item) {
        if (isset($menu_item[2]) && $menu_item[2] === 'appuntamenti') {
            echo '✅ Menu "Appuntamenti" trovato<br>';
            echo 'Titolo: ' . $menu_item[0] . '<br>';
            echo 'Slug: ' . $menu_item[2] . '<br>';
            echo 'Capability: ' . $menu_item[1] . '<br>';
            $menu_trovato = true;
            break;
        }
    }
    if (!$menu_trovato) {
        echo '❌ Menu "Appuntamenti" NON trovato<br>';
    }

    // 6. Verifica capacità utente
    echo '<h3>6. Verifica Capacità Utente</h3>';
    if (current_user_can('manage_options')) {
        echo '✅ Utente corrente ha capacità "manage_options"<br>';
    } else {
        echo '❌ Utente corrente NON ha capacità "manage_options"<br>';
    }

    echo '</div>';

    die();
}

