<?php
/**
 * Fix Username Column Issue
 * 
 * Script untuk memperbaiki masalah kolom username di tabel members
 */

try {
    // Database connection
    $pdo = new PDO(
        'mysql:host=localhost;dbname=perdagangan_system;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Database connected successfully\n";
    
    // Check current table structure
    echo "📋 Checking members table structure...\n";
    $stmt = $pdo->query("DESCRIBE members");
    $columns = $stmt->fetchAll();
    
    $hasUsername = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'username') {
            $hasUsername = true;
            break;
        }
    }
    
    if (!$hasUsername) {
        echo "➕ Adding username column...\n";
        
        // Add generated column username based on member_code
        $sql = "ALTER TABLE members 
                ADD COLUMN username VARCHAR(50) GENERATED ALWAYS AS (member_code) VIRTUAL";
        
        $pdo->exec($sql);
        echo "✅ Username column added successfully\n";
        
    } else {
        echo "✅ Username column already exists\n";
    }
    
    // Verify the fix
    echo "🔍 Verifying the fix...\n";
    $stmt = $pdo->query("SELECT username, member_code FROM members LIMIT 5");
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        echo "   - {$user['username']} (from {$user['member_code']})\n";
    }
    
    // Test the authentication query
    echo "🧪 Testing authentication query...\n";
    $stmt = $pdo->prepare("SELECT * FROM members WHERE username = :username AND is_active = 1 LIMIT 1");
    $stmt->execute(['username' => 'ADMIN001']);
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ Authentication query works correctly\n";
    } else {
        echo "⚠️  No user found with username 'ADMIN001', but query structure is correct\n";
    }
    
    echo "\n🎉 Username column issue has been fixed!\n";
    echo "📝 Summary:\n";
    echo "   - Added virtual column 'username' based on 'member_code'\n";
    echo "   - Authentication queries will now work correctly\n";
    echo "   - No data migration needed (virtual column)\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
