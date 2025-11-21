<?php

if (count($argv) != 2) {
    exit('Please specific a EJS script version' . PHP_EOL);
} else {
    $js_dir = 'js' . DIRECTORY_SEPARATOR;

    $ver = $argv[1];
    $hashes = array();
    $scripts = array(
        'yt.solver.core.min.js' => "https://github.com/yt-dlp/ejs/releases/download/{$ver}/yt.solver.core.min.js",
        'yt.solver.lib.min.js' => "https://github.com/yt-dlp/ejs/releases/download/{$ver}/yt.solver.lib.min.js",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    if (
        defined('CURLSSLOPT_NATIVE_CA')
        && version_compare(curl_version()['version'], '7.71', '>=')
    ) {
        curl_setopt($ch, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA);
    } else {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    }
    foreach ($scripts as $script => $url) {
        $type = explode('.', $script)[2];
        echo "Downloading challenge solver {$type} script from {$url}" . PHP_EOL;
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        $curl_info = curl_getinfo($ch);
        if (($curl_info['http_code'] ?? '') == 200) {
            if (file_put_contents("{$js_dir}{$script}", $data) === false) {
                echo "Failed to write \"{$script}\" file" . PHP_EOL;
            } else {
                $hashes[$script] = hash('sha3-512', $data);
            }
        } else {
            $error = curl_error($ch) ?: 'status code: ' . ($curl_info['http_code'] ?? '');
            echo "Failed to download challenge solver {$type} script ({$error})" . PHP_EOL;
        }
    }
    curl_close($ch);

    if (count($hashes) == 2) {
        if (file_put_contents("{$js_dir}_hashes.json", json_encode($hashes)) === false) {
            echo 'Failed to write file "_hashes.json"' . PHP_EOL;
            exit(1);
        }
    } else {
        exit(1);
    }
}
