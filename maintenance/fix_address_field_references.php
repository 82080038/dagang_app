<?php
/**
 * Fix Address Field References in Maintenance Files
 * Update all street_address references to address_detail
 */

echo "🔧 Fixing address field references in maintenance files...\n\n";

$files = [
    'test_final_chrome.html',
    'test_final_all_fixed.html', 
    'test_chrome_error.html',
    'test_address_integration.php',
    'setup_centralized_addresses.php',
    'setup_addresses_simple.php',
    'verify_address_tables.php'
];

$replacements = [
    'street_address' => 'address_detail',
    'Street Address' => 'Address Detail',
    'streetAddress' => 'addressDetail'
];

foreach ($files as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        echo "📝 Processing: $file\n";
        
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        foreach ($replacements as $old => $new) {
            $content = str_replace($old, $new, $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            echo "   ✅ Updated successfully\n";
        } else {
            echo "   ⚪ No changes needed\n";
        }
    } else {
        echo "   ❌ File not found: $file\n";
    }
}

echo "\n🎉 Address field references fixed!\n";
echo "\n📋 Next steps:\n";
echo "1. Run database migrations:\n";
echo "   mysql -u root -p perdagangan_system < database_migrations/fix_address_field_names.sql\n";
echo "   mysql -u root -p perdagangan_system < database_migrations/add_address_fields_to_main_tables.sql\n";
echo "2. Test the application\n";
echo "3. Verify all address-related functionality works correctly\n";
?>
