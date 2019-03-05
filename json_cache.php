<?php
include getData.php;

/**
* This function retreves the information for a spesificed cache 
* or creates a new cache if it does not exist
* @param String cache_dir path for cache to be collected
*/
function json_cached_results($cache_dir) {
    //include config file
    $config = include('configuration.php');

    $expires = time() - $config['expireTime']*60; // 2 hours

    // fopen will create or open as needed
    $cfh = fopen($cache_dir, 'wb');

    // Check if cache entry exists for collection
    // Check that the file is older than the expire time and that it's not empty
    if (!file_exists($cache_dir) || filectime($cache_dir) < $expires || filesize($cache_dir) <= 0) {

        // Refresh cache
        getApiResults();
        $api_results = json_encode(makeAllData());

        // Write back to cache if results are valid
        if ($api_results != null && $api_results != '')
            fwrite($cfh, $api_results);
        else
            fwrite($cfh, '');

    } else {
        // Fetch cache
        $api_results = (file_get_contents($cache_dir));
    }

    // Always close files
    fclose($cfh);

    return (($api_results));
}
?>