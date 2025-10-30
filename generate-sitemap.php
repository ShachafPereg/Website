<?php
// ===============================
//  Automatic Sitemap Generator
//  Developed by mvn.co.il
// ===============================

// === הגדרות בסיסיות ===
$baseUrl = 'https://www.example.com'; // שנה לכתובת האתר שלך
$outputFile = __DIR__ . '/sitemap.xml'; // מיקום הקובץ שייבנה

// === שלב 1: איסוף קבצי PHP/HTML ===
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS)
);

$urls = [];
foreach ($iterator as $file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if (in_array($ext, ['php', 'html'])) {
        $path = str_replace(__DIR__, '', $file);
        // דלג על קבצים שלא אמורים להיות גלוים
        if (preg_match('/(generate-sitemap|config|admin|includes|partials)/i', $path)) continue;
        if (strpos($path, '/_') !== false) continue;

        $url = rtrim($baseUrl . str_replace('\\', '/', $path), '/');
        $url = str_replace('index.php', '', $url);
        $urls[] = $url;
    }
}

// === שלב 2: יצירת XML ===
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

foreach ($urls as $url) {
    $urlTag = $xml->addChild('url');
    $urlTag->addChild('loc', htmlspecialchars($url));
    $urlTag->addChild('lastmod', date('Y-m-d'));
    $urlTag->addChild('changefreq', 'weekly');
    $urlTag->addChild('priority', '0.8');
}

// === שלב 3: שמירה לקובץ ===
$xmlContent = $xml->asXML();
file_put_contents($outputFile, $xmlContent);

// === שלב 4: הצגה בזמן אמת ===
header('Content-Type: text/xml; charset=utf-8');
echo $xmlContent;

// הודעה ב-console
error_log("✅ Sitemap generated successfully (" . count($urls) . " URLs)");
