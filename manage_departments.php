<?php
include 'config.php';

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_department'])) {
        $name = $conn->real_escape_string($_POST['department_name']);
        $conn->query("INSERT INTO departments (name) VALUES ('$name')");

        header("Location: manage_departments.php?success=1");
        exit();
    } elseif (isset($_POST['delete_department'])) {
        $id = (int)$_POST['department_id'];
        $conn->query("DELETE FROM departments WHERE id = $id");
    
        header("Location: manage_departments.php?delete=1");
        exit();
    
    } elseif (isset($_POST['edit_department'])) {
        $id = (int)$_POST['department_id'];
        $name = $conn->real_escape_string($_POST['department_name']);
        $conn->query("UPDATE departments SET name = '$name' WHERE id = $id");

        header("Location: manage_departments.php?update=1");
        exit();
    }
}

// ดึงข้อมูลแผนกทั้งหมด
$departments = $conn->query("SELECT * FROM departments ORDER BY name");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการแผนก</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 5px 10px;
            cursor: pointer;
        }

        .add-form {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 5px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1>จัดการแผนก</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                เพิ่มแผนกใหม่เรียบร้อยแล้ว
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['delete'])): ?>
            <div class="alert alert-success">
                ลบแผนกเรียบร้อยแล้ว
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['update'])): ?>
            <div class="alert alert-success">
                อัพเดตแผนกเรียบร้อยแล้ว
            </div>
        <?php endif; ?>

        <div class="add-form">
            <h2>เพิ่มแผนกใหม่</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="department_name">ชื่อแผนก:</label>
                    <input type="text" id="department_name" name="department_name" required>
                </div>
                <button type="submit" name="add_department">เพิ่มแผนก</button>
            </form>
        </div>

        <h2>รายการแผนก</h2>
        <table>
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อแผนก</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                if ($departments->num_rows > 0):
                    while ($row = $departments->fetch_assoc()):
                ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td class="action-buttons">
                                <!-- ปุ่มแก้ไข -->
                                <button style="width: 80px;" onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')">แก้ไข</button>
                                <!-- ปุ่มลบ -->
                                <form method="POST" action="" class="delete-form" style="display: contents;">
                                    <input type="hidden" name="department_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="delete_department" value="1">
                                    <button type="button" class="delete-btn" onclick="confirmDelete(this)">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    <?php
                    endwhile;
                else:
                    ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">ไม่มีข้อมูลแผนก</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <!-- Modal สำหรับแก้ไขแผนก -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>แก้ไขแผนก</h2>
            <form method="POST" action="">
                <input type="hidden" id="edit_department_id" name="department_id">
                <div class="form-group">
                    <label for="edit_department_name">ชื่อแผนก:</label>
                    <input type="text" id="edit_department_name" name="department_name" required>
                </div>
                <button type="submit" name="edit_department">บันทึกการแก้ไข</button>
            </form>
        </div>
    </div>

    <script>
        // JavaScript สำหรับ Modal
        function openEditModal(id, name) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_department_id').value = id;
            document.getElementById('edit_department_name').value = name;
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // ปิด Modal เมื่อคลิกนอกพื้นที่ Modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }

        // แสดง SweetAlert ถ้ามีข้อความสำเร็จ
        <?php if (isset($_GET['success'])): ?>
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'เพิ่มแผนกใหม่เรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>

        <?php if (isset($_GET['delete'])): ?>
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'ลบแผนกเรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>

        <?php if (isset($_GET['update'])): ?>
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'อัพเดตแผนกเรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>
    </script>
    <script src="script.js"></script>
</body>

</html>