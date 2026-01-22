<?php
/**
 * Test Companies Page with Error Parameter
 * Test accessing companies page with problematic parameters
 */

echo "=== Test Companies Page with Error Parameter ===\n\n";

// Test 1: Access companies page with problematic parameter
$url = 'http://localhost/dagang/index.php?page=companies:616';
echo "Testing URL: $url\n";

// Use curl to test the URL
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_NOBODY, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);

curl_close($curl);

echo "HTTP Status Code: $httpCode\n";
echo "Curl Error: " . ($curlError ?: 'None') . "\n";

if ($httpCode == 200) {
    echo "✅ Page loaded successfully\n";
    
    // Check for syntax errors in response
    if (strpos($response, 'Parse error') !== false || strpos($response, 'Fatal error') !== false) {
        echo "❌ Syntax errors found in response:\n";
        echo $response . "\n";
    } else {
        echo "✅ No syntax errors in response\n";
    }
} else {
    echo "❌ HTTP Error: $httpCode\n";
    echo "Response:\n";
    echo $response . "\n";
}

echo "\n=== Test Selesai! ===\n";
?>
