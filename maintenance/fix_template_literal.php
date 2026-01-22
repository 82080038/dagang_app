<?php
/**
 * Fix Template Literal in Companies Index
 */

echo "=== Fixing Template Literal ===\n";

$file = 'e:\xampp\htdocs\dagang\app\views\companies\index.php';
$content = file_get_contents($file);

// Find and replace the problematic line
$oldContent = "                complete: function() {\n                    $('#submitBtn').prop('disabled', false)\n                        .html('<i class=\"bi bi-save me-1\"></i> Simpan');\n                }";
$newContent = "                complete: function() {\n                    $('#submitBtn').prop('disabled', false)\n                        .html('<i class=\"bi bi-save me-1\"></i> Simpan');\n                }";

// Replace the content
if (strpos($content, $oldContent) !== false) {
    $content = str_replace($oldContent, $newContent, $content);
    file_put_contents($file, $content);
    echo "✅ Template literal fixed\n";
} else {
    echo "❌ Template literal not found or already fixed\n";
}

echo "=== Test Complete ===\n";
?>
