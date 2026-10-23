<?php # Upload to the webroot temporarily, open https://buildingthebridge.eu/server-check.php once, then DELETE it.
header('Content-Type: text/plain; charset=utf-8');
$root = is_dir(__DIR__ . '/images') ? __DIR__ : dirname(__DIR__);

function row($label, $value, $status) {
    echo str_pad($label, 34) . str_pad($value, 28) . $status . "\n";
}

echo "Server check (no secrets shown)\n\n";
row('PHP version', PHP_VERSION, version_compare(PHP_VERSION, '8.3.0', '>=') && version_compare(PHP_VERSION, '8.4.0', '<') ? 'OK' : 'WARN');
row('PHP SAPI', PHP_SAPI, strpos(PHP_SAPI, 'fpm') !== false ? 'OK' : 'WARN');

foreach (array('mysqli', 'gd', 'mbstring', 'zip', 'xml', 'dom', 'simplexml', 'xmlwriter', 'fileinfo', 'iconv', 'curl', 'json', 'opcache') as $ext) {
    $loaded = $ext === 'opcache' ? (extension_loaded('Zend OPcache') || extension_loaded('opcache')) : extension_loaded($ext);
    row('ext: ' . $ext, $loaded ? 'loaded' : '-', $loaded ? 'OK' : 'MISSING');
}

if (function_exists('gd_info')) {
    $gd = gd_info();
    row('gd: JPEG', !empty($gd['JPEG Support']) ? 'yes' : 'no', !empty($gd['JPEG Support']) ? 'OK' : 'MISSING');
    row('gd: PNG', !empty($gd['PNG Support']) ? 'yes' : 'no', !empty($gd['PNG Support']) ? 'OK' : 'MISSING');
    row('gd: FreeType', !empty($gd['FreeType Support']) ? 'yes' : 'no', !empty($gd['FreeType Support']) ? 'OK' : 'MISSING');
} else {
    foreach (array('JPEG', 'PNG', 'FreeType') as $f) {
        row('gd: ' . $f, 'gd missing', 'MISSING');
    }
}

row('short_open_tag', ini_get('short_open_tag') ? 'On' : 'Off', 'OK'); # value only, not required on 8.3
row('upload_max_filesize', ini_get('upload_max_filesize'), 'OK');
row('memory_limit', ini_get('memory_limit'), 'OK');

foreach (array('images', 'files', 'a-side_logs') as $dir) {
    $path = $root . '/' . $dir;
    if (!is_dir($path)) {
        row('writable: ' . $dir . '/', 'no such folder', 'MISSING');
    } else {
        row('writable: ' . $dir . '/', is_writable($path) ? 'writable' : 'not writable', is_writable($path) ? 'OK' : 'WARN');
    }
}

row('inc/secrets.inc.php exists', is_file($root . '/inc/secrets.inc.php') ? 'yes' : 'no', is_file($root . '/inc/secrets.inc.php') ? 'OK' : 'MISSING');
row('vendor/autoload.php exists', is_file($root . '/vendor/autoload.php') ? 'yes' : 'no', is_file($root . '/vendor/autoload.php') ? 'OK' : 'MISSING');

echo "\nRemember to DELETE server-check.php now.\n";
