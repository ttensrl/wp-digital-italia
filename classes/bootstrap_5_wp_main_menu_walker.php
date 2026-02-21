<?php

class bootstrap_5_wp_main_menu_walker extends Walker_Nav_Menu
{
    private $current_item;

    private $menu_name;

    private $dropdown_id_counter = 1;
    private $dropdown_menu_alignment_values = [
        'dropdown-menu-start',
        'dropdown-menu-end',
        'dropdown-menu-sm-start',
        'dropdown-menu-sm-end',
        'dropdown-menu-md-start',
        'dropdown-menu-md-end',
        'dropdown-menu-lg-start',
        'dropdown-menu-lg-end',
        'dropdown-menu-xl-start',
        'dropdown-menu-xl-end',
        'dropdown-menu-xxl-start',
        'dropdown-menu-xxl-end'
    ];

    function start_lvl(&$output, $depth = 0, $args = null)
    {
        $dropdown_menu_class[] = '';
        foreach($this->current_item->classes as $class) {
            if(in_array($class, $this->dropdown_menu_alignment_values)) {
                $dropdown_menu_class[] = $class;
            }
        }
        $dropdown_id = 'dropdown-' . $this->menu_name . '-' . ($this->dropdown_id_counter - 1);
        $indent = str_repeat("\t", $depth);
        $submenu = ($depth > 0) ? ' sub-menu' : '';
        $output .= "\n$indent<ul class=\"dropdown-menu$submenu " . esc_attr(implode(" ",$dropdown_menu_class)) . " depth_$depth\" role=\"region\" aria-labelledby=\"$dropdown_id\">\n";
        $output .= "$indent  <div class=\"link-list-wrapper\">\n";
        $output .= "$indent    <ul class=\"link-list\">\n";
    }

    function end_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent    </ul>\n"; // Chiudi ul.link-list
        $output .= "$indent  </div>\n";   // Chiudi div.link-list-wrapper
        $output .= "$indent</ul>\n";      // Chiudi ul.dropdown-menu
    }

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $this->current_item = $item;
        if (isset($args->menu)) {
           $this->menu_name = $args->menu->slug;
        }
        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        $li_attributes = '';
        $class_names = $value = '';

        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $classes[] = ($args->walker->has_children) ? 'dropdown' : '';
        /**
         * CONTROLLIAMO SE IL MENU HA UNA CLASSE PREDEFINITA
         */
        if(empty($args->menu_class)) {
            $classes[] = 'nav-item';
            $classes[] = 'nav-item-' . $item->ID;
        }
        if ($depth && $args->walker->has_children) {
            $classes[] = 'dropdown-menu dropdown-menu-end';
        }

        $class_names =  join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = ' class="' . esc_attr($class_names) . '"';

        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $id = strlen($id) ? ' id="' . esc_attr($id) . '"' : '';
        $output .= $indent . '<li ' . $id . $value . $class_names . $li_attributes . '>';

        $attributes = !empty($item->attr_title) ? (' title="' . esc_attr($item->attr_title) . '"') : '';
        $attributes .= !empty($item->target) ? (' target="' . esc_attr($item->target) . '"') : '';
        $attributes .= !empty($item->xfn) ? (' rel="' . esc_attr($item->xfn) . '"') : '';
        $attributes .= !empty($item->url) ? (' href="' . esc_attr($item->url) . '"') : '';

        if ($args->walker->has_children) {
            $dropdown_id = 'dropdown-' . $this->menu_name . '-' . $this->dropdown_id_counter;
            $this->dropdown_id_counter++;
            $attributes .= ' id="' . esc_attr($dropdown_id) . '"';
        }

        $active_class = ($item->current || $item->current_item_ancestor || in_array("current_page_parent", $item->classes, true) || in_array("current-post-ancestor", $item->classes, true)) ? 'active' : '';

        $nav_link_class = ( $depth > 0 ) ? 'dropdown-item ' : 'nav-link ';

        $attributes .= ( $args->walker->has_children ) ? ' class="'. $nav_link_class . $active_class . ' dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"' : ' class="'. $nav_link_class . $active_class . '"';

        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        if ($args->walker->has_children) {
            $template_directory = get_template_directory_uri();
            $svg_path = $template_directory . '/dist/images/sprites.svg#it-expand';

            $item_output .= '<span>'.apply_filters('the_title', $item->title, $item->ID).'</span>
                    <svg class="icon icon-xs">
                      <use href="' . esc_url($svg_path) . '"></use>
                    </svg>';
        } else {
            $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        }
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}