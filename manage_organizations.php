<?php
include 'config.php';

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_organization'])) {
        $name = $conn->real_escape_string($_POST['organization_name']);
        $conn->query("INSERT INTO organizations (name) VALUES ('$name')");

        header("Location: manage_organizations.php?success=1");
        exit();
    } elseif (isset($_POST['delete_organization'])) {
        $id = (int)$_POST['organization_id'];
        $conn->query("DELETE FROM organizations WHERE id = $id");

        header("Location: manage_organizations.php?delete=1");
        exit();
    } elseif (isset($_POST['edit_organization'])) {
        $id = (int)$_POST['organization_id'];
        $name = $conn->real_escape_string($_POST['organization_name']);
        $conn->query("UPDATE organizations SET name = '$name' WHERE id = $id");

        header("Location: manage_organizations.php?update=1");
        exit();
    }
}

// ดึงข้อมูลกลุ่มอาชีพทั้งหมด
$organizations = $conn->query("SELECT * FROM organizations ORDER BY name");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการกลุ่มอาชีพ</title>
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
        <h1>จัดการกลุ่มอาชีพ</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                เพิ่มกลุ่มอาชีพใหม่เรียบร้อยแล้ว
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['delete'])): ?>
            <div class="alert alert-success">
                ลบกลุ่มอาชีพเรียบร้อยแล้ว
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['update'])): ?>
            <div class="alert alert-success">
                อัพเดตกลุ่มอาชีพเรียบร้อยแล้ว
            </div>
        <?php endif; ?>

        <div class="add-form">
            <h2>เพิ่มกลุ่มอาชีพใหม่</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="organization_name">ชื่อกลุ่มอาชีพ:</label>
                    <input type="text" id="organization_name" name="organization_name" required>
                </div>
                <button type="submit" name="add_organization">เพิ่มกลุ่มอาชีพ</button>
            </form>
        </div>

        <h2>รายการกลุ่มอาชีพ</h2>
        <table>
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อกลุ่มอาชีพ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                if ($organizations->num_rows > 0):
                    while ($row = $organizations->fetch_assoc()):
                ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td class="action-buttons">
                                <!-- ปุ่มแก้ไข -->
                                <button type="button" onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')">แก้ไข</button>

                                <!-- ปุ่มลบด้วย SweetAlert2 -->
                                <form method="POST" action="" class="delete-form" style="display: contents;">
                                    <input type="hidden" name="organization_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="delete_organization" value="1">
                                    <button type="button" class="delete-btn" onclick="confirmDelete(this)">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    <?php
                    endwhile;
                else:
                    ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">ไม่มีข้อมูลกลุ่มอาชีพ</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px;">
        </div>
    </div>

    <!-- Modal สำหรับแก้ไขกลุ่มอาชีพ -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>แก้ไขกลุ่มอาชีพ</h2>
            <form method="POST" action="">
                <input type="hidden" id="edit_organization_id" name="organization_id">
                <div class="form-group">
                    <label for="edit_organization_name">ชื่อกลุ่มอาชีพ:</label>
                    <input type="text" id="edit_organization_name" name="organization_name" required>
                </div>
                <button type="submit" name="edit_organization">บันทึกการแก้ไข</button>
            </form>
        </div>
    </div>

    <script>
        // JavaScript สำหรับ Modal
        function openEditModal(id, name) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_organization_id').value = id;
            document.getElementById('edit_organization_name').value = name;
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
                text: 'เพิ่มกลุ่มอาชีพใหม่เรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>

        <?php if (isset($_GET['delete'])): ?>
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'ลบกลุ่มอาชีพเรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>

        <?php if (isset($_GET['update'])): ?>
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'อัพเดตกลุ่มอาชีพเรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>
    </script>
    <script src="script.js"></script>

</body>

</html>