<?php
    /**
     * Plugin Name: WP Page Admin Widget
     * Plugin URI: http://github.com/danielrsmith/wppaw
     * Description: Adds recently updated pages to the admin dashboard
     * Version: 0.1.3
     * Author: Daniel Smith
     * Author URI: http://danielrs.com
     * License: MIT License
     */
    defined('ABSPATH') or die("No script kiddies please!");

    include_once('wppaw-update.php');

    function wppaw_widget_hook()
    {
        wp_add_dashboard_widget('wppaw_recent_dashboard_widget',
                                'Recently Updated Pages',
                                'wppaw_recent_widget_content');
    }

    add_action('wp_dashboard_setup', 'wppaw_widget_hook');

    function wppaw_recent_widget_content()
    {
        echo wppaw_get_recent_content();
    }

    function wppaw_recent_widget_content_ajax()
    {
        $current_page = $_POST['page'];
        echo wppaw_get_recent_content($current_page);
        die();
    }


    function wppaw_get_recent_content($current_page = 1, $number = 5)
    {
        $params = array(
            'posts_per_page' => $number,
            'paged' => $current_page,
            'post_type' => 'page',
            'orderby' => 'modified'
        );

        $query = new WP_Query($params);

        $response = '';

        while($query->have_posts())
        {
            $query->the_post();
            $response .= page_row($query->post);
        }

        $response = "<ul>$response</ul>";

        $show_prev = ($current_page > 1);
        $show_next = ($current_page != $query->max_num_pages);
        $response .= '<div style="clear: both;"></div><div class="wppaw-pagination">';
        if($show_prev)
        {
            $response .= '<a href="#" id="wppaw-prev-page">Prev</a>';
        }

        if($show_prev && $show_next)
        {
            $response .= ' | ';
        }

        if($show_next)
        {
            $response .= '<a href="#" id="wppaw-next-page">Next</a>';
        }

        $response .= '<span>(' . $current_page . ' of ' . $query->max_num_pages . ')</span>';
        $response .= '<span class="loading" style="display:none;"><img src="' . plugins_url( '/img/ajax-loader.gif', __FILE__ ) . '" /></span>';
        $response .= '</div>';
        $response .= '<span id="wppaw-current-page" class="hidden">' . $current_page . '</span>';
        $response .= '<span id="wppaw-recent-nonce" class="hidden">' . wp_create_nonce( 'wppaw-recent-nonce' ) . '</span>';

        return $response;
    }

    function page_row($page)
    {
        $title = $page->post_title != "" ? $page->post_title : '(no title)';
        return '<li><span class="wppaw-title">' . $title . '</span><span class="wppaw-actions"><a href="' . get_edit_post_link($page->ID) . '">Edit</a> | <a href="' . get_permalink($page->ID) . '">View</a></span></li>';
    }

    add_action('wp_ajax_get_recent_pages', 'wppaw_recent_widget_content_ajax');

    function wppaw_admin_javascript()
    {
        wp_register_script('wppaw_admin_javascript', plugins_url('/js/admin.js', __FILE__));
        wp_enqueue_script('wppaw_admin_javascript');
    }

    add_action('admin_enqueue_scripts', 'wppaw_admin_javascript');

    function wppaw_admin_stylesheet()
    {
      	wp_register_style( 'wppaw_admin_stylesheet', plugins_url( '/css/main.css', __FILE__ ));
      	wp_enqueue_style( 'wppaw_admin_stylesheet' );
    }

    add_action('admin_enqueue_scripts', 'wppaw_admin_stylesheet');
