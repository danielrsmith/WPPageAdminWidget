<?php
include_once('lib/updater.php');

if (is_admin())
{
    $config = array(
        'slug' => plugin_basename(__FILE__),
        'proper_folder_name' => 'wp-page-admin-widget',
        'api_url' => 'https://api.github.com/repos/danielrsmith/WPPageAdminWidget',
        'raw_url' => 'https://raw.github.com/danielrsmith/WPPageAdminWidget/master',
        'github_url' => 'https://github.com/danielrsmith/WPPageAdminWidget',
        'zip_url' => 'https://github.com/danielrsmith/WPPageAdminWidget/zipball/master',
        'sslverify' => true,
        'requires' => '3.0',
        'tested' => '3.9',
        'readme' => 'VERSION'
    );
    new WP_GitHub_Updater($config);
}
