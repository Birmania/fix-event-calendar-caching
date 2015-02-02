=== Plugin Name ===
Contributors: DanielMahaffy
Tags: calendar, javascript, cache
Requires at least: 4.1
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin makes the All-in-One Event Calendar's javascript cacheable to improve page load speed.

== Description ==

The "All-in-One Event Calendar" by Time.ly works great, but uses dynamic javascript.
This plugin creates a static version of the javascript that can be cached by
the browser. 

I have tested this plugin with version 2.1.9 of the event calendar plugin.  
It may work with future releases, but use it at your own risk.

This plugin only uses the static javascript on the frontend.  If a user is 
logged in, the original dynamic script will be used.  As far as I can tell
caching the javascript does not interfere with the plugin's functionality, but
there may be features of the event calendar that do not work with this plugin.

== Installation ==

1. Upload the fix-event-calendar-caching directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin needs write access to it's folder to create the static js file.