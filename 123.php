<?php
// manage_department_org.php
include 'config.php';

// เพิ่มแผนก
if (isset($_POST['add_department'])) {
    $dep_name = $_POST['department_name'];
    if (!empty($dep_name)) {
        $stmt = $conn->prepare("INSERT INTO organizational_units (name, type) VALUES (?, 'department')");
        $stmt->bind_param("s", $dep_name);
        if ($stmt->execute()) {
            $stmt->close();
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'เพิ่มแผนกเรียบร้อยแล้ว',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            window.location = 'manage_department_org.php';
                        });
                    });
                  </script>";
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด!',
                            text: 'ไม่สามารถเพิ่มแผนกได้: " . $stmt->error . "',
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    });
                  </script>";
        }
    }
}

// เพิ่มกลุ่มงาน
if (isset($_POST['add_organization'])) {
    $org_name = $_POST['organization_name'];
    if (!empty($org_name)) {
        $stmt = $conn->prepare("INSERT INTO organizational_units (name, type) VALUES (?, 'organization')");
        $stmt->bind_param("s", $org_name);
        if ($stmt->execute()) {
            $stmt->close();
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'เพิ่มกลุ่มงานเรียบร้อยแล้ว',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            window.location = 'manage_department_org.php';
                        });
                    });
                  </script>";
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด!',
                            text: 'ไม่สามารถเพิ่มกลุ่มงานได้: " . $stmt->error . "',
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    });
                  </script>";
        }
    }
}

// ดึงข้อมูลแผนกทั้งหมด
$departments = [];
$dept_stmt = $conn->prepare("SELECT * FROM organizational_units WHERE type = 'department' ORDER BY id");
if ($dept_stmt) {
    $dept_stmt->execute();
    $dept_result = $dept_stmt->get_result();
    while ($row = $dept_result->fetch_assoc()) {
        $departments[] = $row;
    }
    $dept_stmt->close();
}

// ดึงข้อมูลกลุ่มงานทั้งหมด
$organizations = [];
$org_stmt = $conn->prepare("SELECT * FROM organizational_units WHERE type = 'organization' ORDER BY id");
if ($org_stmt) {
    $org_stmt->execute();
    $org_result = $org_stmt->get_result();
    while ($row = $org_result->fetch_assoc()) {
        $organizations[] = $row;
    }
    $org_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการแผนกและกลุ่มงาน</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 40px;
        }
        .section h2 {
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        .form-container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button[type="submit"] {
            background-color: #24bfa5;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1em;
        }
        button[type="submit"]:hover {
            background-color: #1da88f;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .empty-message {
            text-align: center;
            color: #777;
            padding: 20px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1>จัดการแผนกและกลุ่มงาน</h1>
        
        <!-- ส่วนเพิ่มแผนก -->
        <div class="section">
            <h2>เพิ่มแผนก</h2>
            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="department_name">ชื่อแผนก:</label>
                        <input type="text" id="department_name" name="department_name" required>
                    </div>
                    <button type="submit" name="add_department">เพิ่มแผนก</button>
                </form>
            </div>
            
            <!-- แสดงรายการแผนก -->
            <h3>รายการแผนก</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อแผนก</th>
                        <th>ไม่มีข้อมูลแผนก</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dept['id']); ?></td>
                            <td><?php echo htmlspecialchars($dept['name']); ?></td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="empty-message">ไม่พบข้อมูลแผนก</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ส่วนเพิ่มกลุ่มงาน -->
        <div class="section">
            <h2>เพิ่มกลุ่มงาน</h2>
            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="organization_name">ชื่อกลุ่มงาน:</label>
                        <input type="text" id="organization_name" name="organization_name" required>
                    </div>
                    <button type="submit" name="add_organization">เพิ่มกลุ่มงาน</button>
                </form>
            </div>
            
            <!-- แสดงรายการกลุ่มงาน -->
            <h3>รายการกลุ่มงาน</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อกลุ่มงาน</th>
                        <th>ไม่มีข้อมูลกลุ่มงาน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($organizations)): ?>
                        <?php foreach ($organizations as $org): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($org['id']); ?></td>
                            <td><?php echo htmlspecialchars($org['name']); ?></td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="empty-message">ไม่พบข้อมูลกลุ่มงาน</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>