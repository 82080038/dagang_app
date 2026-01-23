<?php
/**
 * Test Company Address Details in Detail View
 * Verify that company details modal shows complete address information
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Company.php';

echo "🧪 TESTING COMPANY ADDRESS DETAILS\n";
echo "=================================\n\n";

try {
    $db = Database::getInstance();
    $companyModel = new Company();
    
    echo "📋 Test 1: Check getCompanyWithAddress Method\n";
    
    // Test with a company ID (assuming company ID 1 exists)
    $testCompanyId = 1;
    $companyWithAddress = $companyModel->getCompanyWithAddress($testCompanyId);
    
    if ($companyWithAddress) {
        echo "   ✅ Company found: " . $companyWithAddress['company_name'] . "\n";
        
        // Check address fields
        $addressFields = [
            'address_detail' => $companyWithAddress['address_detail'] ?? 'MISSING',
            'province_id' => $companyWithAddress['province_id'] ?? 'MISSING',
            'province_name' => $companyWithAddress['province_name'] ?? 'MISSING',
            'regency_id' => $companyWithAddress['regency_id'] ?? 'MISSING',
            'regency_name' => $companyWithAddress['regency_name'] ?? 'MISSING',
            'district_id' => $companyWithAddress['district_id'] ?? 'MISSING',
            'district_name' => $companyWithAddress['district_name'] ?? 'MISSING',
            'village_id' => $companyWithAddress['village_id'] ?? 'MISSING',
            'village_name' => $companyWithAddress['village_name'] ?? 'MISSING',
            'postal_code' => $companyWithAddress['postal_code'] ?? 'MISSING'
        ];
        
        echo "   📍 Address Fields:\n";
        foreach ($addressFields as $field => $value) {
            $status = ($value !== 'MISSING' && $value !== '') ? '✅' : '❌';
            echo "   {$status} {$field}: " . ($value === 'MISSING' ? 'MISSING' : ($value === '' ? 'EMPTY' : $value)) . "\n";
        }
        
        // Build full address like the JavaScript does
        $addressParts = [];
        if (!empty($companyWithAddress['address_detail'])) $addressParts[] = $companyWithAddress['address_detail'];
        if (!empty($companyWithAddress['village_name'])) $addressParts[] = $companyWithAddress['village_name'];
        if (!empty($companyWithAddress['district_name'])) $addressParts[] = $companyWithAddress['district_name'];
        if (!empty($companyWithAddress['regency_name'])) $addressParts[] = $companyWithAddress['regency_name'];
        if (!empty($companyWithAddress['province_name'])) $addressParts[] = $companyWithAddress['province_name'];
        if (!empty($companyWithAddress['postal_code'])) $addressParts[] = $companyWithAddress['postal_code'];
        
        $fullAddress = implode(', ', $addressParts);
        echo "\n   📄 Full Address (as displayed in modal):\n";
        echo "   " . ($fullAddress ?: 'No address data') . "\n";
        
    } else {
        echo "   ❌ Company not found with ID: $testCompanyId\n";
        
        // Try to find any company
        $allCompanies = $companyModel->getAll(1);
        if (!empty($allCompanies)) {
            $firstCompany = $allCompanies[0];
            echo "   💡 Trying with company ID: " . $firstCompany['id_company'] . "\n";
            
            $companyWithAddress = $companyModel->getCompanyWithAddress($firstCompany['id_company']);
            if ($companyWithAddress) {
                echo "   ✅ Company found: " . $companyWithAddress['company_name'] . "\n";
            } else {
                echo "   ❌ Still no company found with address data\n";
            }
        }
    }
    
    echo "\n📋 Test 2: Compare with getById Method\n";
    
    $companyBasic = $companyModel->getById($testCompanyId);
    
    if ($companyBasic) {
        echo "   ✅ Basic company data found\n";
        
        $basicFields = array_keys($companyBasic);
        $addressFieldsInBasic = array_filter($basicFields, function($field) {
            return strpos($field, 'province') !== false || 
                   strpos($field, 'regency') !== false || 
                   strpos($field, 'district') !== false || 
                   strpos($field, 'village') !== false ||
                   strpos($field, 'address') !== false ||
                   strpos($field, 'postal') !== false;
        });
        
        echo "   📍 Address fields in basic data: " . count($addressFieldsInBasic) . "\n";
        foreach ($addressFieldsInBasic as $field) {
            echo "   - {$field}: " . ($companyBasic[$field] ?? 'NULL') . "\n";
        }
        
        if (empty($addressFieldsInBasic)) {
            echo "   ⚠️  Basic getById() method doesn't include address fields\n";
            echo "   ✅ That's why we need getCompanyWithAddress() for details\n";
        }
    } else {
        echo "   ❌ Basic company data not found\n";
    }
    
    echo "\n📋 Test 3: Verify Database Joins\n";
    
    // Check if alamat_db tables are accessible
    try {
        $provinceCount = $db->query("SELECT COUNT(*) as count FROM alamat_db.provinces")->fetch();
        echo "   ✅ alamat_db.provinces accessible: " . $provinceCount['count'] . " provinces\n";
        
        $regencyCount = $db->query("SELECT COUNT(*) as count FROM alamat_db.regencies")->fetch();
        echo "   ✅ alamat_db.regencies accessible: " . $regencyCount['count'] . " regencies\n";
        
        $districtCount = $db->query("SELECT COUNT(*) as count FROM alamat_db.districts")->fetch();
        echo "   ✅ alamat_db.districts accessible: " . $districtCount['count'] . " districts\n";
        
        $villageCount = $db->query("SELECT COUNT(*) as count FROM alamat_db.villages")->fetch();
        echo "   ✅ alamat_db.villages accessible: " . $villageCount['count'] . " villages\n";
        
    } catch (Exception $e) {
        echo "   ❌ Error accessing alamat_db: " . $e->getMessage() . "\n";
    }
    
    echo "\n📋 Test 4: Test Address Display Logic\n";
    
    if ($companyWithAddress) {
        // Simulate JavaScript address building logic
        echo "   🔍 Testing address display logic:\n";
        
        $testCases = [
            'complete_address' => [
                'address_detail' => 'Jl. Test No. 123',
                'village_name' => 'Test Village',
                'district_name' => 'Test District',
                'regency_name' => 'Test Regency',
                'province_name' => 'Test Province',
                'postal_code' => '12345'
            ],
            'missing_village' => [
                'address_detail' => 'Jl. Test No. 123',
                'village_name' => '',
                'district_name' => 'Test District',
                'regency_name' => 'Test Regency',
                'province_name' => 'Test Province',
                'postal_code' => '12345'
            ],
            'only_street' => [
                'address_detail' => 'Jl. Test No. 123',
                'village_name' => '',
                'district_name' => '',
                'regency_name' => '',
                'province_name' => '',
                'postal_code' => ''
            ],
            'empty_address' => [
                'address_detail' => '',
                'village_name' => '',
                'district_name' => '',
                'regency_name' => '',
                'province_name' => '',
                'postal_code' => ''
            ]
        ];
        
        foreach ($testCases as $caseName => $testData) {
            $addressParts = [];
            if (!empty($testData['address_detail'])) $addressParts[] = $testData['address_detail'];
            if (!empty($testData['village_name'])) $addressParts[] = $testData['village_name'];
            if (!empty($testData['district_name'])) $addressParts[] = $testData['district_name'];
            if (!empty($testData['regency_name'])) $addressParts[] = $testData['regency_name'];
            if (!empty($testData['province_name'])) $addressParts[] = $testData['province_name'];
            if (!empty($testData['postal_code'])) $addressParts[] = $testData['postal_code'];
            
            $result = implode(', ', $addressParts) ?: '-';
            echo "   {$caseName}: {$result}\n";
        }
    }
    
    echo "\n🎯 EXPECTED BEHAVIOR VERIFICATION\n";
    echo "=====================================\n";
    echo "✅ Company details modal should show complete address\n";
    echo "✅ Address should include: street, village, district, regency, province, postal code\n";
    echo "✅ Empty fields should be skipped in address display\n";
    echo "✅ If no address data, should show '-'\n";
    echo "✅ Edit form should populate all address dropdowns correctly\n";
    
    echo "\n🎉 TESTS COMPLETED!\n";
    echo "\n📝 MANUAL VERIFICATION STEPS:\n";
    echo "1. Open company list\n";
    echo "2. Click 'Details' button on a company with address data\n";
    echo "3. Verify address section shows complete information\n";
    echo "4. Check that all address components are displayed\n";
    echo "5. Click 'Edit' button and verify dropdowns are populated\n";
    echo "6. Test with companies that have no address data\n";
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
