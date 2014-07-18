<?php
    /**
     * Plugin Name: WP Page Admin Widget
     * Plugin URI: http://github.com/danielrsmith/wppaw
     * Description: Adds recently updated pages to the admin dashboard
     * Version: 0.1.1
     * Author: Daniel Smith
     * Author URI: http://danielrs.com
     * License: MIT License
     */
    defined('ABSPATH') or die("No script kiddies please!");

    include_once('lib/updater.php');

    if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
      $config = array(
          'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
          'proper_folder_name' => 'wp-page-admin-widget', // this is the name of the folder your plugin lives in
          'api_url' => 'https://api.github.com/repos/danielrsmith/WPPageAdminWidget', // the github API url of your github repo
          'raw_url' => 'https://raw.github.com/danielrsmith/WPPageAdminWidget/master', // the github raw url of your github repo
          'github_url' => 'https://github.com/danielrsmith/WPPageAdminWidget', // the github url of your github repo
          'zip_url' => 'https://github.com/danielrsmith/WPPageAdminWidget/zipball/master', // the zip url of the github repo
          'sslverify' => true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
          'requires' => '3.0', // which version of WordPress does your plugin require?
          'tested' => '3.9', // which version of WordPress is your plugin tested up to?
          'readme' => 'VERSION' // which file to use as the readme for the version number
      );
      new WP_GitHub_Updater($config);
    }

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

        $response .= ' (' . $current_page . ' of ' . $query->max_num_pages . ')';

        $response .= '<span id="wppaw-current-page" class="hidden">' . $current_page . '</span>';
        $response .= '<span id="wppaw-recent-nonce" class="hidden">' . wp_create_nonce( 'wppaw-recent-nonce' ) . '</span>';

        return $response;
    }

    function page_row($page)
    {
        $title = $page->post_title != "" ? $page->post_title : '(no title)';
        return '<li><a href="' . get_edit_post_link($page->ID) . '">' . $title .'</a></li>';
    }

    add_action('wp_ajax_get_recent_pages', 'wppaw_recent_widget_content_ajax');


    function wppaw_dashboard_javascript()
    {
    ?>
    <script type="text/javascript" >
    jQuery(document).ready(function() {
      addOnClick($);
    });

    function addOnClick() {
      jQuery('#wppaw-next-page').click(function(evt){
        evt.preventDefault();
        var data = {
          'action': 'get_recent_pages',
          'page': parseInt(jQuery('#wppaw-current-page').text()) + 1
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function(response) {
          jQuery('#wppaw_recent_dashboard_widget .inside').html(response);
          addOnClick();
        });
      });

      jQuery('#wppaw-prev-page').click(function(evt){
        evt.preventDefault();
        var data = {
          'action': 'get_recent_pages',
          'page': parseInt(jQuery('#wppaw-current-page').text()) - 1
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function(response) {
          jQuery('#wppaw_recent_dashboard_widget .inside').html(response);
          addOnClick();
        });
      });
    }

    </script>
    <?php
    }

    add_action( 'admin_footer', 'wppaw_dashboard_javascript' );
