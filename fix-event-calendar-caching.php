<?php

/**
 * Plugin Name: Fix Event Calendar Caching
 * Description: This plugin makes the All-in-One Event Calendar's javascript cacheable to improve page load speed.
 * Version: 1.0.1
 * Author: Daniel Mahaffy
 * License: GPLv2 or later
 */
function fix_event_cal_init() {
    if (!is_user_logged_in()) {// only use the static script file if no one is logged in
        if (!function_exists('get_plugins')) {//We need this to check version of the event cal plugin
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $pluginDir = __DIR__ . "/..";
        $pluginName = "all-in-one-event-calendar";
        $version = get_plugin_data("$pluginDir/$pluginName/$pluginName.php")['Version'];

        //embed the version number of the Event Cal plugin in the static js name
        $jsFile = __DIR__ . "/event-cal-$version.js";
        $originalUrl = get_site_url() . "/?ai1ec_render_js=common_frontend&is_backend=false&ver=$version"; //This is where we can load the original dynamic js
        if (!file_exists($jsFile)) {//first time running or first time since a new version of the event calendar
            $javascript = file_get_contents($originalUrl);
            if ($javascript) {//make sure we were able to load the javascript
                file_put_contents($jsFile, $javascript);
            }
        }

        if (file_exists($jsFile)) {//only replace javascript if we were able to create the cache file.
            wp_dequeue_script('ai1ec_requirejs'); //remove the dynamic js script
            wp_enqueue_script('event_cal_replace', plugins_url("event-cal-$version.js", __FILE__), array(), null, true); //add our static js
        }
    }
}

/**
 * Call our funciton.  The event cal plugin doesn't enqueue it's script until 
 * the wp_footer, so we need to use that action too.
 */
add_action('wp_footer', 'fix_event_cal_init', 19);
