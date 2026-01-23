<?php
/**
 * Test Company Details JSON Response
 * Verify that company details API returns valid JSON without HTML errors
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Company.php';
require_once __DIR__ . '/../app/core/Controller.php';

echo "🧪 TESTING COMPANY DETAILS JSON RESPONSE\n";
echo "======================================\n\n";

// Mock controller for testing
class TestController extends Controller {
    public function testJsonResponse($data) {
        return $this->json($data);
    }
}

try {
    $db = Database::getInstance();
    $companyModel = new Company();
    $testController = new TestController();
    
    echo "📋 Test 1: Test Basic JSON Response\n";
    
    $testData = [
        'status' => 'success',
        'data' => [
            'test' => 'value',
            'number' => 123
        ]
    ];
    
    echo "   Testing basic JSON encoding...\n";
    ob_start();
    $testController->testJsonResponse($testData);
    $output = ob_get_clean();
    
    $decoded = json_decode($output, true);
    if ($decoded && $decoded['status'] === 'success') {
        echo "   ✅ Basic JSON response works correctly\n";
    } else {
        echo "   ❌ Basic JSON response failed\n";
        echo "   Output: " . substr($output, 0, 200) . "...\n";
    }
    
    echo "\n📋 Test 2: Test Company Model Methods\n";
    
    // Test getStatistics
    echo "   Testing getStatistics()...\n";
    $stats = $companyModel->getStatistics(1); // Test with company ID 1
    if (is_array($stats) && isset($stats['total_branches'])) {
        echo "   ✅ getStatistics() returns valid data\n";
        echo "   - Total branches: " . ($stats['total_branches'] ?? 0) . "\n";
        echo "   - Total members: " . ($stats['total_members'] ?? 0) . "\n";
    } else {
        echo "   ❌ getStatistics() failed\n";
    }
    
    // Test getWithBranches
    echo "   Testing getWithBranches()...\n";
    $branches = $companyModel->getWithBranches(1);
    if (is_array($branches) && isset($branches['branches_count'])) {
        echo "   ✅ getWithBranches() returns valid data\n";
        echo "   - Branches count: " . ($branches['branches_count'] ?? 0) . "\n";
    } else {
        echo "   ❌ getWithBranches() failed\n";
    }
    
    echo "\n📋 Test 3: Test Complete Company Details Response\n";
    
    // Simulate the details method logic
    $company = $companyModel->getById(1);
    
    if ($company) {
        echo "   Company found: " . $company['company_name'] . "\n";
        
        $companyWithBranches = $companyModel->getWithBranches(1);
        $statistics = $companyModel->getStatistics(1);
        
        $detailsData = [
            'status' => 'success',
            'data' => [
                'company' => $company,
                'branches' => $companyWithBranches,
                'statistics' => $statistics
            ]
        ];
        
        echo "   Testing complete details JSON response...\n";
        ob_start();
        $testController->testJsonResponse($detailsData);
        $detailsOutput = ob_get_clean();
        
        $detailsDecoded = json_decode($detailsOutput, true);
        if ($detailsDecoded && $detailsDecoded['status'] === 'success') {
            echo "   ✅ Complete details JSON response works correctly\n";
            echo "   - Company data: " . ($detailsDecoded['data']['company']['company_name'] ?? 'N/A') . "\n";
            echo "   - Branches count: " . ($detailsDecoded['data']['branches']['branches_count'] ?? 0) . "\n";
            echo "   - Statistics loaded: " . (isset($detailsDecoded['data']['statistics']['total_branches']) ? 'Yes' : 'No') . "\n";
        } else {
            echo "   ❌ Complete details JSON response failed\n";
            echo "   Output: " . substr($detailsOutput, 0, 200) . "...\n";
        }
    } else {
        echo "   ⚠️  No company found with ID 1, testing with null response\n";
        
        $nullData = [
            'status' => 'error',
            'message' => 'Company not found'
        ];
        
        ob_start();
        $testController->testJsonResponse($nullData);
        http_response_code(404); // Set status code separately
        $nullOutput = ob_get_clean();
        
        $nullDecoded = json_decode($nullOutput, true);
        if ($nullDecoded && $nullDecoded['status'] === 'error') {
            echo "   ✅ Null company response works correctly\n";
        } else {
            echo "   ❌ Null company response failed\n";
        }
    }
    
    echo "\n📋 Test 4: Test Error Handling\n";
    
    // Test with invalid company ID
    echo "   Testing with invalid company ID...\n";
    $invalidCompany = $companyModel->getById(999999);
    
    if (!$invalidCompany) {
        echo "   ✅ Invalid company ID correctly returns null\n";
        
        $errorData = [
            'status' => 'error',
            'message' => 'Company not found'
        ];
        
        ob_start();
        $testController->testJsonResponse($errorData);
        http_response_code(404); // Set status code separately
        $errorOutput = ob_get_clean();
        
        $errorDecoded = json_decode($errorOutput, true);
        if ($errorDecoded && $errorDecoded['status'] === 'error') {
            echo "   ✅ Error JSON response works correctly\n";
        } else {
            echo "   ❌ Error JSON response failed\n";
        }
    } else {
        echo "   ⚠️  Unexpected: Invalid company ID returned data\n";
    }
    
    echo "\n🎯 EXPECTED BEHAVIOR VERIFICATION\n";
    echo "=====================================\n";
    echo "✅ All JSON responses should be valid JSON\n";
    echo "✅ No HTML output should be mixed with JSON\n";
    echo "✅ Error responses should have proper status codes\n";
    echo "✅ All required fields should be present\n";
    echo "✅ Unicode characters should be properly encoded\n";
    
    echo "\n🎉 ALL TESTS COMPLETED!\n";
    echo "\n📝 MANUAL VERIFICATION STEPS:\n";
    echo "1. Open browser developer tools\n";
    echo "2. Navigate to company list\n";
    echo "3. Click 'Details' button on any company\n";
    echo "4. Check Network tab for the AJAX request\n";
    echo "5. Verify response is valid JSON (no HTML errors)\n";
    echo "6. Check that all expected fields are present\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
