<?php
// Test companies endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/dagang/index.php?page=companies');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=elcnkpas5pjs0c4rdoroim9sm4');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Requested-With: XMLHttpRequest',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "=== COMPANIES PAGE TEST ===<br>";
echo "HTTP Code: $httpCode<br>";
if ($error) {
    echo "CURL Error: $error<br>";
} else {
    echo "Response Length: " . strlen($response) . " characters<br>";
    
    // Check if response contains HTML (indicates successful page load)
    if (strpos($response, '<!DOCTYPE html') !== false) {
        echo "✅ Page loaded successfully (HTML detected)<br>";
        
        // Check for specific elements
        if (strpos($response, 'companiesTableBody') !== false) {
            echo "✅ Companies table found<br>";
        } else {
            echo "❌ Companies table not found<br>";
        }
        
        if (strpos($response, 'loadCompanies()') !== false) {
            echo "✅ loadCompanies function found in JavaScript<br>";
        } else {
            echo "❌ loadCompanies function not found<br>";
        }
        
        // Check for error messages
        if (strpos($response, 'Terjadi kesalahan') !== false) {
            echo "❌ Error message found in page<br>";
        } else {
            echo "✅ No error messages found<br>";
        }
        
    } elseif (strpos($response, '{"status"') !== false) {
        echo "📄 JSON response detected<br>";
        echo "Response: $response<br>";
    } else {
        echo "❓ Unexpected response format<br>";
        echo "First 500 chars: " . substr($response, 0, 500) . "...<br>";
    }
}

echo "<br>=== AJAX LOAD COMPANIES TEST ===<br>";

// Test AJAX load companies
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, 'http://localhost/dagang/index.php?page=companies&page_num=1&q=&type=');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIE, 'PHPSESSID=elcnkpas5pjs0c4rdoroim9sm4');
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'X-Requested-With: XMLHttpRequest',
    'Accept: application/json'
]);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch2);
curl_close($ch2);

echo "HTTP Code: $httpCode2<br>";
if ($error2) {
    echo "CURL Error: $error2<br>";
} else {
    echo "Response: $response2<br>";
}
?>
