<?php
include 'config.php';

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_establishment'])) {
        $name = $conn->real_escape_string($_POST['establishment_name']);
        $conn->query("INSERT INTO Establishment (name) VALUES ('$name')");

        header("Location: manage_Establishment.php?success=1");
        exit();
    } elseif (isset($_POST['delete_establishment'])) {
        $id = (int)$_POST['establishment_id'];
        $conn->query("DELETE FROM Establishment WHERE id = $id");

        header("Location: manage_Establishment.php?delete=1");
        exit();
    } elseif (isset($_POST['edit_establishment'])) {
        $id = (int)$_POST['establishment_id'];
        $name = $conn->real_escape_string($_POST['establishment_name']);
        $conn->query("UPDATE Establishment SET name = '$name' WHERE id = $id");

        header("Location: manage_Establishment.php?update=1");
        exit();
    }
}

// ดึงข้อมูลสถานประกอบการทั้งหมด
$establishments = $conn->query("SELECT * FROM Establishment ORDER BY name");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสถานประกอบการ</title>
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
        <h1>จัดการสถานประกอบการ</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                เพิ่มสถานประกอบการใหม่เรียบร้อยแล้ว
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['delete'])): ?>
            <div class="alert alert-success">
                ลบสถานประกอบการเรียบร้อยแล้ว
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['update'])): ?>
            <div class="alert alert-success">
                อัพเดตสถานประกอบการเรียบร้อยแล้ว
            </div>
        <?php endif; ?>

        <div class="add-form">
            <h2>เพิ่มสถานประกอบการใหม่</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="establishment_name">ชื่อสถานประกอบการ:</label>
                    <input type="text" id="establishment_name" name="establishment_name" required>
                </div>
                <button type="submit" name="add_establishment">เพิ่มสถานประกอบการ</button>
            </form>
        </div>

        <h2>รายการสถานประกอบการ</h2>
        <table>
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อสถานประกอบการ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                if ($establishments->num_rows > 0):
                    while ($row = $establishments->fetch_assoc()):
                ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td class="action-buttons">
                                <!-- ปุ่มแก้ไข -->
                                <button style="width: 80px;" onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')">แก้ไข</button>
                                <!-- ปุ่มลบ -->
                                <form method="POST" action="" class="delete-form" style="display: contents;">
                                    <input type="hidden" name="establishment_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="delete_establishment" value="1">
                                    <button type="button" class="delete-btn" onclick="confirmDelete(this)">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    <?php
                    endwhile;
                else:
                    ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">ไม่มีข้อมูลสถานประกอบการ</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <!-- Modal สำหรับแก้ไขสถานประกอบการ -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">×</span>
            <h2>แก้ไขสถานประกอบการ</h2>
            <form method="POST" action="">
                <input type="hidden" id="edit_establishment_id" name="establishment_id">
                <div class="form-group">
                    <label for="edit_establishment_name">ชื่อสถานประกอบการ:</label>
                    <input type="text" id="edit_establishment_name" name="establishment_name" required>
                </div>
                <button type="submit" name="edit_establishment">บันทึกการแก้ไข</button>
            </form>
        </div>
    </div>

    <script>
        // JavaScript สำหรับ Modal
        function openEditModal(id, name) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_establishment_id').value = id;
            document.getElementById('edit_establishment_name').value = name;
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

        function confirmDelete(button) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "การลบนี้ไม่สามารถกู้คืนได้!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // ส่งฟอร์มการลบ
                    button.closest('form').submit();
                }
            });
        }

        // แสดง SweetAlert ถ้ามีข้อความสำเร็จ
        <?php if (isset($_GET['success'])): ?>
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'เพิ่มสถานประกอบการใหม่เรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>

        <?php if (isset($_GET['delete'])): ?>
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'ลบสถานประกอบการเรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>

        <?php if (isset($_GET['update'])): ?>
            Swal.fire({
                title: 'สำเร็จ!',
                text: 'อัพเดตสถานประกอบการเรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>
    </script>
    <script src="script.js"></script>
</body>

</html>