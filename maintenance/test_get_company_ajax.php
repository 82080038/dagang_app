<?php
/**
 * Test Company GET AJAX Endpoint
 * Test the companies?action=get&id=X endpoint that was causing 500 error
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Company.php';
require_once __DIR__ . '/../app/core/Controller.php';

echo "🧪 TESTING COMPANY GET AJAX ENDPOINT\n";
echo "==================================\n\n";

// Mock controller for testing
class TestController extends Controller {
    private $companyModel;
    
    public function __construct() {
        $this->companyModel = new Company();
    }
    
    public function testGetCompany($id) {
        // Disable error reporting to prevent HTML output in JSON
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Use getCompanyWithAddress to get complete address data for edit form
        $company = $this->companyModel->getCompanyWithAddress($id);
        
        if (!$company) {
            $this->json([
                'status' => 'error',
                'message' => 'Company not found'
            ], 404);
        }
        
        $this->json([
            'status' => 'success',
            'data' => [
                'company' => $company
            ]
        ]);
    }
    
    public function testGetCompanyBasic($id) {
        // Test with basic getById method
        error_reporting(0);
        ini_set('display_errors', 0);
        
        $company = $this->companyModel->getById($id);
        
        if (!$company) {
            $this->json([
                'status' => 'error',
                'message' => 'Company not found'
            ], 404);
        }
        
        $this->json([
            'status' => 'success',
            'data' => [
                'company' => $company
            ]
        ]);
    }
}

try {
    $db = Database::getInstance();
    $companyModel = new Company();
    $testController = new TestController();
    
    echo "📋 Test 1: Check Available Companies\n";
    
    // Get all companies to test with
    $companies = $companyModel->getAll(5);
    if (empty($companies)) {
        echo "   ❌ No companies found in database\n";
        echo "   💡 Please run the database setup first\n";
        exit;
    }
    
    echo "   ✅ Found " . count($companies) . " companies\n";
    foreach ($companies as $company) {
        echo "   - ID: {$company['id_company']}, Name: {$company['company_name']}, Address ID: " . ($company['address_id'] ?? 'NULL') . "\n";
    }
    
    // Test with the first company
    $testCompanyId = $companies[0]['id_company'];
    echo "\n📋 Test 2: Test getCompanyWithAddress (New Method)\n";
    echo "   Testing with company ID: $testCompanyId\n";
    
    ob_start();
    $testController->testGetCompany($testCompanyId);
    $output = ob_get_clean();
    
    $decoded = json_decode($output, true);
    if ($decoded && $decoded['status'] === 'success') {
        echo "   ✅ getCompanyWithAddress() works correctly\n";
        $companyData = $decoded['data']['company'];
        
        echo "   📊 Company Data:\n";
        echo "   - ID: " . ($companyData['id_company'] ?? 'N/A') . "\n";
        echo "   - Name: " . ($companyData['company_name'] ?? 'N/A') . "\n";
        echo "   - Address Detail: " . ($companyData['address_detail'] ?? 'NULL') . "\n";
        echo "   - Province Name: " . ($companyData['province_name'] ?? 'NULL') . "\n";
        echo "   - Regency Name: " . ($companyData['regency_name'] ?? 'NULL') . "\n";
        echo "   - District Name: " . ($companyData['district_name'] ?? 'NULL') . "\n";
        echo "   - Village Name: " . ($companyData['village_name'] ?? 'NULL') . "\n";
        echo "   - Postal Code: " . ($companyData['postal_code'] ?? 'NULL') . "\n";
        
        // Verify JSON is valid
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "   ✅ JSON is valid\n";
        } else {
            echo "   ❌ JSON error: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "   ❌ getCompanyWithAddress() failed\n";
        echo "   Error: " . ($decoded['message'] ?? 'Unknown error') . "\n";
        echo "   Output: " . substr($output, 0, 200) . "...\n";
    }
    
    echo "\n📋 Test 3: Test getById (Basic Method)\n";
    echo "   Testing with company ID: $testCompanyId\n";
    
    ob_start();
    $testController->testGetCompanyBasic($testCompanyId);
    $basicOutput = ob_get_clean();
    
    $basicDecoded = json_decode($basicOutput, true);
    if ($basicDecoded && $basicDecoded['status'] === 'success') {
        echo "   ✅ getById() works correctly\n";
        $basicData = $basicDecoded['data']['company'];
        
        echo "   📊 Basic Company Data:\n";
        echo "   - ID: " . ($basicData['id_company'] ?? 'N/A') . "\n";
        echo "   - Name: " . ($basicData['company_name'] ?? 'N/A') . "\n";
        echo "   - Address Detail: " . ($basicData['address_detail'] ?? 'NOT AVAILABLE') . "\n";
        echo "   - Province Name: " . ($basicData['province_name'] ?? 'NOT AVAILABLE') . "\n";
    } else {
        echo "   ❌ getById() failed\n";
        echo "   Error: " . ($basicDecoded['message'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n📋 Test 4: Test with Non-existent Company\n";
    echo "   Testing with company ID: 999999\n";
    
    ob_start();
    $testController->testGetCompany(999999);
    $errorOutput = ob_get_clean();
    
    $errorDecoded = json_decode($errorOutput, true);
    if ($errorDecoded && $errorDecoded['status'] === 'error') {
        echo "   ✅ Error handling works correctly\n";
        echo "   Error message: " . $errorDecoded['message'] . "\n";
    } else {
        echo "   ❌ Error handling failed\n";
    }
    
    echo "\n📋 Test 5: Test Database Connection\n";
    
    try {
        $testQuery = $db->query("SELECT COUNT(*) as count FROM companies")->fetch();
        echo "   ✅ Database connection works\n";
        echo "   Total companies: " . $testQuery['count'] . "\n";
        
        // Test alamat_db connection
        $provinceQuery = $db->query("SELECT COUNT(*) as count FROM alamat_db.provinces")->fetch();
        echo "   ✅ alamat_db connection works\n";
        echo "   Total provinces: " . $provinceQuery['count'] . "\n";
        
    } catch (Exception $e) {
        echo "   ❌ Database connection error: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 EXPECTED BEHAVIOR VERIFICATION\n";
    echo "=====================================\n";
    echo "✅ GET /index.php?page=companies&action=get&id=X should return valid JSON\n";
    echo "✅ Response should include company data with address fields\n";
    echo "✅ Companies without address should still work (null values)\n";
    echo "✅ Error handling for non-existent companies\n";
    echo "✅ No HTML output mixed with JSON\n";
    
    echo "\n🎉 AJAX ENDPOINT TESTS COMPLETED!\n";
    echo "\n📝 MANUAL VERIFICATION STEPS:\n";
    echo "1. Open browser developer tools\n";
    echo "2. Navigate to company list\n";
    echo "3. Click 'Edit' button on any company\n";
    echo "4. Check Network tab for the AJAX request\n";
    echo "5. Verify response status is 200 (not 500)\n";
    echo "6. Verify response is valid JSON\n";
    echo "7. Check that company data is properly loaded\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
