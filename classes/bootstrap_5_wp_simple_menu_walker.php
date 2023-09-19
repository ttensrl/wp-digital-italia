<?php

class bootstrap_5_wp_simple_menu_walker extends Walker_Nav_Menu
{
    private $current_item;

    private $menu_name;

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $this->current_item = $item;
        if (isset($args->menu)) {
           $this->menu_name = $args->menu->slug;
        }
        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        $li_attributes = '';
        $value = '';

        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $id = strlen($id) ? (' id="' . esc_attr($id) . '"') : '';
        $output .= $indent . '<li ' . $id . $value . $li_attributes . '>';

        $attributes = !empty($item->attr_title) ? (' title="' . esc_attr($item->attr_title) . '"') : '';
        $attributes .= !empty($item->target) ? (' target="' . esc_attr($item->target) . '"') : '';
        $attributes .= !empty($item->xfn) ? (' rel="' . esc_attr($item->xfn) . '"') : '';
        $attributes .= !empty($item->url) ? (' href="' . esc_attr($item->url) . '"') : '';

        $active_class = ($item->current || $item->current_item_ancestor || in_array("current_page_parent", $item->classes, true) || in_array("current-post-ancestor", $item->classes, true)) ? 'active' : '';
        /**
         * CLASSI ELEMENTO <a>
         */
        $a_classes = empty($args->menu_class) ? 'list-item' : $args->menu_class;

        $attributes .= ' class="'. $a_classes . $active_class . '"';

        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}