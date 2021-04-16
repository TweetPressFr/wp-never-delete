<?php

/**
 * Plugin name: WP Never Delete
 * Author: Julien Maury
 * Author URI: https://www.julien-maury.dev
 * Version: 0.2
 * Description: I don't encourage the use of this plugin in production. It's quite experimental.
 */

defined('DB_USER')
    or die;

define('WP_ND_DIR', plugin_dir_path(__FILE__));

add_filter('post_row_actions', function ($actions) {
    unset($actions['trash']);
    unset($actions['delete']);

    return $actions;
});

add_filter('page_row_actions', function ($actions) {
    unset($actions['trash']);
    unset($actions['delete']);

    return $actions;
});

add_action('admin_init', function () {

    $post_types = apply_filters('wp_nd_disallowed_post_types', array_keys(get_post_types()));

    foreach ((array) $post_types as $post_type) {
        add_filter('bulk_actions-edit-' . $post_type, function ($bulk_actions) {
            unset($bulk_actions['trash']);
            unset($bulk_actions['delete']);

            return $bulk_actions;
        });
    }

    // because for attachments => upload, logically ^^
    add_filter('bulk_actions-upload', function ($bulk_actions) {
        unset($bulk_actions['trash']);
        unset($bulk_actions['delete']);
        return $bulk_actions;
    });
});

add_action('init', function () {
    remove_action('wp_scheduled_delete', 'wp_scheduled_delete');
});

add_filter('map_meta_cap', function ($caps, $cap, $user_id, $args) {
    // Nothing to do
    if ('delete_post' !== $cap || empty($args[0])) {
        return $caps;
    }

    $post_types = apply_filters('wp_nd_disallowed_post_types', array_keys(get_post_types()));

    if (in_array(get_post_type($args[0]), $post_types, true)) {
        $caps[] = 'do_not_allow';
    }

    return $caps;
}, 10, 4);
