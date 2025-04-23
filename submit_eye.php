<?php // submit_eye.php
include 'config.php';

// ตรวจสอบว่าเป็นการส่งข้อมูลแบบ POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: eye.php");
    exit();
}

// ดึงชื่อและนามสกุล
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$exam_date = $_POST['exam_date'] ?? null; // เพิ่มการดึง exam_date

$department_id = (int)($_POST['department_id'] ?? 0);
$organization_id = (int)($_POST['organization_id'] ?? 0);

// ฟิลด์ผลตรวจตา
$fields = [
    'binocular_vision_far', 'far_vision_both', 'far_vision_right', 'far_vision_left',
    'stereo_depth', 'color_discrimination', 'far_vertical_phoria', 'far_lateral_phoria',
    'binocular_vision_near', 'near_vision_both', 'near_vision_right', 'near_vision_left',
    'near_vertical_phoria', 'near_lateral_phoria', 'visual_field' // เปลี่ยนจาก visual_field_right, visual_field_left เป็น visual_field
];

$data = [];
foreach ($fields as $field) {
    $data[$field] = $_POST[$field] ?? null;
}

// ตรวจสอบว่า department_id มีอยู่ในตาราง departments หรือไม่
$dept_check = $conn->prepare("SELECT id FROM departments WHERE id = ?");
$dept_check->bind_param("i", $department_id);
$dept_check->execute();
$dept_result = $dept_check->get_result();
if ($dept_result->num_rows == 0) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่พบแผนกที่ระบุในระบบ',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location = 'eye.php';
                });
            });
          </script>";
    exit;
}
$dept_check->close();

// ตรวจสอบว่า organization_id มีอยู่ในตาราง organizations หรือไม่
$org_check = $conn->prepare("SELECT id FROM organizations WHERE id = ?");
$org_check->bind_param("i", $organization_id);
$org_check->execute();
$org_result = $org_check->get_result();
if ($org_result->num_rows == 0) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่พบกลุ่มงานที่ระบุในระบบ',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location = 'eye.php';
                });
            });
          </script>";
    exit;
}
$org_check->close();

// บันทึกข้อมูลลงตาราง eye_records
$stmt = $conn->prepare("
    INSERT INTO eye_records (
        first_name, last_name, exam_date, department_id, organization_id,
        binocular_vision_far, far_vision_both, far_vision_right, far_vision_left,
        stereo_depth, color_discrimination, far_vertical_phoria, far_lateral_phoria,
        binocular_vision_near, near_vision_both, near_vision_right, near_vision_left,
        near_vertical_phoria, near_lateral_phoria, visual_field
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssiisssssssssssssss", // ลดจาก 22 เป็น 21 ตัว เพราะเปลี่ยนจาก visual_field_right, visual_field_left เป็น visual_field
    $first_name, $last_name, $exam_date, $department_id, $organization_id,
    $data['binocular_vision_far'], $data['far_vision_both'],
    $data['far_vision_right'], $data['far_vision_left'], $data['stereo_depth'],
    $data['color_discrimination'], $data['far_vertical_phoria'], $data['far_lateral_phoria'],
    $data['binocular_vision_near'], $data['near_vision_both'], $data['near_vision_right'],
    $data['near_vision_left'], $data['near_vertical_phoria'], $data['near_lateral_phoria'],
    $data['visual_field']
);

if ($stmt->execute()) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'สำเร็จ!',
                    text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location = 'eye.php';
                });
            });
          </script>";
} else {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถบันทึกข้อมูลได้: " . $conn->error . "',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location = 'eye.php';
                });
            });
          </script>";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>กำลังประมวลผล...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
</body>
</html>