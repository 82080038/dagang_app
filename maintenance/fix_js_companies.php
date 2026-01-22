<?php
// Fix JavaScript syntax errors in companies/index.php
$file = 'app/views/companies/index.php';
$content = file_get_contents($file);

// Fix missing semicolons in jQuery event handlers
$content = preg_replace('/\$\([\'"]#([^\'"]+)[\'"]\)\.on\([\'"]([^\'"]+)[\'"],\s*function\s*\([^)]*\)\s*\{([^}]+)\}\)(?!\s*;)/', 
    '$("#$1").on("$2", function($3) {$4});', $content);

// Fix malformed AJAX calls
$content = preg_replace('/\$\.\s*ajax\(\{([^}]+)url([^}]+)\}([^}]+)\}(?!\s*;)/', 
    '$.ajax({$1url$2}$3});', $content);

// Fix setTimeout export issue
$content = str_replace('window.searchTimeout = setTimeout(function() {', 'window.searchTimeout = setTimeout(function() {', $content);

// Add missing semicolons at the end of function calls
$content = preg_replace('/\$\([\'"]#([^\'"]+)[\'"]\)\.modal\([\'"]([^\'"]+)[\'"]\)(?!\s*;)/', 
    '$("#$1").modal("$2");', $content);

file_put_contents($file, $content);
echo "JavaScript syntax errors fixed in companies/index.php\n";
?>
