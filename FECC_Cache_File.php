<?php
class FECC_Cache_File {

    private static $messages = array();
    
    /**
     * 
     * @param bool $absolute
     * @return string The file name of the cache file
     */
    public static function getFileName($local = true) {
        $version = self::getAllInOneEventCalVersionNumber();
        $hash = sha1(self::getOriginalJavascriptUrl());
        $file = "js_cache/event-cal-$version-$hash.js";
        if ($local) {
            return plugin_dir_path( __FILE__ ) . $file;
        } else {
            return plugins_url($file, __FILE__);
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
        return add_query_arg( 'ver', $version, $original ); //This is where we can load the original dynamic js
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
        $javascript = self::getRemoteJavascript(self::getOriginalJavascriptUrl());
        if ($javascript) {//make sure we were able to load the javascript
            if (file_put_contents(self::getFileName(), $javascript)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Clear cache directory
     * 
     * @return Nothing.
     */
    public static function clearCacheFiles() {
      $files = glob(plugin_dir_path( __FILE__ ) . 'js_cache/*.js'); //get all file names
      foreach($files as $file){
          if(is_file($file))
          unlink($file); //delete file
      }
    }
    
    /**
    * Allow to get a remote url content from CURL
    * param url : The url to request
    * Return string the url body
    */
    public static function getRemoteJavascript($url) {
        $ch_rech = curl_init(); // Init curl
        curl_setopt($ch_rech, CURLOPT_URL, $url); // Set the URL to get
        curl_setopt($ch_rech, CURLOPT_HEADER, 0); // Do not put HEADER in the response
        curl_setopt($ch_rech, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:19.0) Gecko/20100101 Firefox/19.0"); // Set a normal user agent to avoid being forbidden to access remote resource
        ob_start(); // Open the buffer
        curl_exec($ch_rech); // Execute the request
        curl_close($ch_rech); // Quit curl
        $result = ob_get_contents(); // Save the result content
        ob_end_clean(); // Empty the buffer
        return $result;
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
            $hash = substr(hash_file('sha256', self::getFileName()), 0, 10);
            wp_dequeue_script('ai1ec_requirejs'); //remove the dynamic js script
            wp_enqueue_script('event_cal_replace', add_query_arg("hash", $hash, self::getFileName(false)), array(), null, true); //add our static js
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
