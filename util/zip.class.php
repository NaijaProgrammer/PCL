function get_ip() {
    if( !empty($_SERVER['HTTP_CLIENT_IP']) ) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
        $ip_array = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach($ip_array as $ip) {
            return trim($ip);
        }
    }

    return $_SERVER['REMOTE_ADDR'];
}
/**
 * Unpack a ZIP file into the specific path in the second parameter.
 * @return true on success.
 * @credits: osclass' utils.php
 */
function osc_packageExtract($zipPath, $path) {
    if(!file_exists($path)) {
        if (!@mkdir($path, 0666)) {
            return false;
        }
    }

    @chmod($path, 0777);

    $zip = new ZipArchive;
    if ($zip->open($zipPath) === true) {
        $zip->extractTo($path);
        $zip->close();
        return true;
    } else {
        return false;
    }
}

/**
 * Unzip's a specified ZIP file to a location
 *
 * @param string $file Full path of the zip file
 * @param string $to Full path where it is going to be unzipped
 * @return int
 */
function osc_unzip_file($file, $to) {
    if (!file_exists($to)) {
        if (!@mkdir($to, 0766)) {
            return 0;
        }
    }

    @chmod($to, 0777);

    if (!is_writable($to)) {
        return 0;
    }

    if (class_exists('ZipArchive')) {
        return _unzip_file_ziparchive($file, $to);
    }

    // if ZipArchive class doesn't exist, we use PclZip
    return _unzip_file_pclzip($file, $to);
}