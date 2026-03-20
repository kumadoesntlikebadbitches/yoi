<?php
// Enable error display for debugging (remove later in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ========== CONFIGURATION ==========
$domain_url            = 'https://smss67.nanpanya.ac.th/video/';
$base_url              = 'https://smss67.nanpanya.ac.th/video/?go=';
$sitemap_name          = 'sitemap'; // Will generate sitemap-1.xml, sitemap-2.xml, etc.
$max_links_per_sitemap = 30000;
$local_file            = 'car.txt';
// ====================================

$script_dir = dirname(__FILE__);

// 1) Check if directory is writable
if (!is_writable($script_dir)) {
    die("❌ Directory not writable: {$script_dir}");
}

// 2) Load keywords from file
$file_path = $script_dir . '/' . $local_file;
if (!file_exists($file_path)) {
    die("❌ File not found: {$local_file}");
}
if (!is_readable($file_path)) {
    die("❌ Cannot read: {$local_file}");
}

$lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!$lines || count($lines) == 0) {
    die("❌ No keywords found in file.");
}

$use_mbstring = extension_loaded('mbstring');
$sitemap_index = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$sitemap_index .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
$sitemap_files = array();

foreach ($lines as $i => $keyword) {
    $keyword = trim(rawurldecode($keyword));
    if ($keyword == '') continue;

    // Percent-encode the entire keyword and replace spaces with '+'
    $encoded_keyword = rawurlencode($keyword);
    $formatted_keyword = str_replace('%20', '+', $encoded_keyword);

    $file_index = (int)ceil(($i + 1) / $max_links_per_sitemap);

    if (!isset($sitemap_files[$file_index])) {
        $sitemap_files[$file_index] = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap_files[$file_index] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    }

    $full_url = htmlspecialchars($base_url . $formatted_keyword, ENT_QUOTES, 'UTF-8');
    $sitemap_files[$file_index] .= "  <url>\n    <loc>{$full_url}</loc>\n  </url>\n";
}

foreach ($sitemap_files as $num => $xml) {
    $xml .= "</urlset>\n";
    $file_name = $sitemap_name . '-' . $num . '.xml';
    $file_path = $script_dir . '/' . $file_name;

    if (file_put_contents($file_path, $xml) === false) {
        die("❌ Cannot write sitemap chunk: {$file_name}");
    }

    $sitemap_url = htmlspecialchars($domain_url . $file_name, ENT_QUOTES, 'UTF-8');
    $sitemap_index .= "  <sitemap>\n    <loc>{$sitemap_url}</loc>\n  </sitemap>\n";
}

$sitemap_index .= "</sitemapindex>\n";

$index_path = $script_dir . '/sitemap-index.xml';
if (file_put_contents($index_path, $sitemap_index) === false) {
    die("❌ Cannot write sitemap index file.");
}

echo "✅ Sitemap(s) Udah Jadi Tod.";
?>