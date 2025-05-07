<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด!',
            text: 'ไม่พบรหัสข้อมูลที่ต้องการลบ',
            confirmButtonText: 'ตกลง'
        }).then(() => {
            window.location.href = 'list_eye.php';
        });
    });
    </script>";
    exit;
}

$id = intval($_GET['id']);

$sql = "DELETE FROM eye_records WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: 'ลบข้อมูลสำเร็จ',
            confirmButtonText: 'ตกลง'
        }).then(() => {
            window.location.href = 'list_eye.php';
        });
    });
    </script>";
} else {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: 'ไม่สามารถลบข้อมูลได้: " . addslashes($stmt->error) . "',
            confirmButtonText: 'ตกลง'
        });
    });
    </script>";
}

$stmt->close();
$conn->close();
?>