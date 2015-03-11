<?php
class FECC_Cache_File {

    private static $messages = array();
    
    /**
     * 
     * @param bool $absolute
     * @return string The file name of the cache file
     */
    public static function getFileName($absolute = true) {
        $version = self::getAllInOneEventCalVersionNumber();
        $hash = sha1(self::getOriginalJavascriptUrl());
        $file = "event-cal-$version-$hash.js";
        if ($absolute) {
            return __DIR__ . '/' . $file;
        } else {
            return $file;
        }
    }
    
    /**
     * 
     * @return string Absolute url of the original Event Calendar dynamic javascript
     */
    public static function getOriginalJavascriptUrl() {
        $version = self::getAllInOneEventCalVersionNumber();
        global $wp_scripts;
        $original = $wp_scripts->registered['ai1ec_requirejs']->src;
        return $original . "&ver=$version";//This is where we can load the original dynamic js
    }

    /**
     * 
     * @return string The version number of the All-In-One Event Calendar
     */
    public static function getAllInOneEventCalVersionNumber() {
        if (defined('AI1EC_VERSION')) {//This will be faster if available
            return AI1EC_VERSION;
        } else {
            if (!function_exists('get_plugins')) {//We need this to check version of the event cal plugin
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $pluginDir = __DIR__ . "/..";
            $pluginName = "all-in-one-event-calendar";
            $version = get_plugin_data("$pluginDir/$pluginName/$pluginName.php")['Version'];
            return $version;
        }
    }

    /**
     * Generate the cached javascript from the original javascript
     * 
     * @return boolean true on success. false on failure.
     */
    public static function createCacheFile() {
        $javascript = file_get_contents(self::getOriginalJavascriptUrl());
        if ($javascript) {//make sure we were able to load the javascript
            if (file_put_contents(self::getFileName(), $javascript)) {
                return true;
            }
        }
        return false;
    }

    public static function isCached() {
        return file_exists(self::getFileName());
    }

    /**
     * 
     * @return boolean true if the enqueue succeded; false otherwise.
     */
    public static function enqueueCachedJavascript() {
        if (self::isCached()) {//only replace javascript if we were able to create the cache file.
            $filename = self::getFileName(false);
            $hash = substr(hash_file('sha256', self::getFileName()), 0, 10);
            wp_dequeue_script('ai1ec_requirejs'); //remove the dynamic js script
            wp_enqueue_script('event_cal_replace', plugins_url($filename, __FILE__) . "?hash=$hash", array(), null, true); //add our static js
            return true;
        }
        return false;
    }

    /**
     * Enqueue an admin message
     * 
     * @param string $message
     * @param string $type one of "updated", "error", or "update-nag"
     */
    public static function addAdminMessage($message, $type = 'updated') {
        self::$messages[] = array('type'=>$type,'message'=>$message);
    }
    
    /**
     * Function to print the admin messages.
     */
    public static function printAdminMessages() {
        foreach (self::$messages as $message) {
            ?>
            <div class="<?php echo $message['type'];?>">
                <p>Fix Event Cal Plugin: <?php echo $message['message']; ?></p>
            </div>
            <?php
        }
    }

}
