<?php

/**
 * Plugin Name: Fix Event Calendar Caching
 * Description: This plugin makes the All-in-One Event Calendar's javascript cacheable to improve page load speed.
 * Version: 2.0.0
 * Author: Daniel Mahaffy
 * License: GPLv2 or later
 */
require_once __DIR__.'/FECC_Cache_File.php';

function fix_event_cal_init() {
    if (!is_user_logged_in()) {// only use the static script file if no one is logged in
        if (!FECC_Cache_File::isCached()) {//first time running or first time since a new version of the event calendar
            FECC_Cache_File::createCacheFile();
        }
        FECC_Cache_File::enqueueCachedJavascript();
    }
}

/**
 * Call our funciton.  The event cal plugin doesn't enqueue it's script until 
 * the wp_footer, so we need to use that action too.
 */
add_action('wp_footer', 'fix_event_cal_init', 19);

/**
 * Clear the cache when the calendar settings are changed.
 */
function fix_event_cal_settings_updated(){
    FECC_Cache_File::createCacheFile();
    FECC_Cache_File::addAdminMessage("Event Calendar javascript cache cleared.");
}
add_action('ai1ec_settings_updated','fix_event_cal_settings_updated');
add_action('admin_notices','FECC_Cache_File::printAdminMessages');//print admin notices
