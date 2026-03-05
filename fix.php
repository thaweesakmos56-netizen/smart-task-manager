<?php
require_once 'config/db.php';

// PHP สร้าง hash เองเลย
$hash = password_hash('demo123', PASSWORD_DEFAULT);

// อัพเดท users ทุกคนให้ใช้ password: demo123
$conn->query("UPDATE users SET password = '$hash' WHERE id = 4");
$conn->query("UPDATE users SET password = '$hash' WHERE id = 5");

// แสดงผล
echo "Hash: " . $hash . "<br>";
echo "Verify: " . (password_verify('demo123', $hash) ? 'TRUE ✅' : 'FALSE ❌');
echo "<br><br>Done! Now login with demo / demo123";
?>
```

### ขั้นที่ 2 — เปิด browser ไปที่:
```
http://localhost/smart-task-manager1/fix.php
```

### ขั้นที่ 3 — ลบโค้ด debug ออกจาก `index.php` แล้วลอง login:
```
http://localhost/smart-task-manager1/index.php