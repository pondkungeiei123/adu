<?php
ob_start();
require_once 'config.php';

// ปิดการแสดงข้อผิดพลาดใน output แต่ให้บันทึกใน log
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// ตรวจสอบว่า $conn ถูกกำหนดหรือไม่
if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    $response = [
        'status' => 'error',
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'
    ];
    ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response);
    exit();
}

// ตรวจสอบว่าเป็นการร้องขอแบบ POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = [
        'status' => 'error',
        'message' => 'คำขอไม่ถูกต้อง'
    ];
    ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response);
    exit();
}

// ตรวจสอบว่า ID ถูกส่งมาหรือไม่
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $response = [
        'status' => 'error',
        'message' => 'ไม่พบรหัสข้อมูล'
    ];
    ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response);
    exit();
}

$id = intval($_POST['id']);

// รับข้อมูลจากฟอร์ม
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$binocular_vision_far = trim($_POST['binocular_vision_far'] ?? '');
$far_vision_both = trim($_POST['far_vision_both'] ?? '');
$far_vision_right = trim($_POST['far_vision_right'] ?? '');
$far_vision_left = trim($_POST['far_vision_left'] ?? '');
$stereo_depth = trim($_POST['stereo_depth'] ?? '');
$color_discrimination = trim($_POST['color_discrimination'] ?? '');
$far_vertical_phoria = trim($_POST['far_vertical_phoria'] ?? '');
$far_lateral_phoria = trim($_POST['far_lateral_phoria'] ?? '');
$binocular_vision_near = trim($_POST['binocular_vision_near'] ?? '');
$near_vision_both = trim($_POST['near_vision_both'] ?? '');
$near_vision_right = trim($_POST['near_vision_right'] ?? '');
$near_vision_left = trim($_POST['near_vision_left'] ?? '');
$near_vertical_phoria = trim($_POST['near_vertical_phoria'] ?? '');
$near_lateral_phoria = trim($_POST['near_lateral_phoria'] ?? '');
$visual_field = trim($_POST['visual_field'] ?? '');
$organization_id = trim($_POST['organization_id'] ?? '');
$department_id = trim($_POST['department_id'] ?? '');
$exam_date = trim($_POST['exam_date_ce'] ?? '');

// ตรวจสอบข้อมูล
$errors = [];

if (empty($first_name)) $errors[] = "กรุณากรอกชื่อ";
if (empty($last_name)) $errors[] = "กรุณากรอกนามสกุล";
if (empty($binocular_vision_far)) $errors[] = "กรุณากรอกการมองเห็นสองตา (ระยะไกล)";
if (empty($far_vision_both)) $errors[] = "กรุณากรอกการมองเห็นทั้งสองตา (ระยะไกล)";
if (empty($far_vision_right)) $errors[] = "กรุณากรอกการมองภาพระยะไกลด้วยตาขวา";
if (empty($far_vision_left)) $errors[] = "กรุณากรอกการมองภาพระยะไกลด้วยตาซ้าย";
if (empty($stereo_depth)) $errors[] = "กรุณากรอกการมองภาพ 3 มิติ";
if (empty($color_discrimination)) $errors[] = "กรุณากรอกการมองจำแนกสี";
if (empty($far_vertical_phoria)) $errors[] = "กรุณากรอกความเบี่ยงคลาดในแนวตั้งระยะไกล";
if (empty($far_lateral_phoria)) $errors[] = "กรุณากรอกความเบี่ยงคลาดในแนวนอนระยะไกล";
if (empty($binocular_vision_near)) $errors[] = "กรุณากรอกการมองวัตถุสองตาระยะใกล้";
if (empty($near_vision_both)) $errors[] = "กรุณากรอกการมองภาพระยะใกล้ด้วยสองตา";
if (empty($near_vision_right)) $errors[] = "กรุณากรอกการมองภาพระยะใกล้ด้วยตาขวา";
if (empty($near_vision_left)) $errors[] = "กรุณากรอกการมองภาพระยะใกล้ด้วยตาซ้าย";
if (empty($near_vertical_phoria)) $errors[] = "กรุณากรอกความเบี่ยงคลาดในแนวตั้งระยะใกล้";
if (empty($near_lateral_phoria)) $errors[] = "กรุณากรอกความเบี่ยงคลาดในแนวนอนระยะใกล้";
if (empty($visual_field)) $errors[] = "กรุณากรอกลานสายตา";
if (empty($organization_id) || !is_numeric($organization_id)) $errors[] = "กรุณากรอกรหัสองค์กรให้ถูกต้อง";
if (empty($department_id) || !is_numeric($department_id)) $errors[] = "กรุณากรอกรหัสแผนกให้ถูกต้อง";
if (empty($exam_date)) {
    $errors[] = "กรุณากรอกวันที่ตรวจ";
} else {
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $exam_date)) {
        $errors[] = "รูปแบบวันที่ไม่ถูกต้อง";
    } else {
        list($year, $month, $day) = explode('-', $exam_date);
        if (!checkdate(intval($month), intval($day), intval($year))) {
            $errors[] = "วันที่ไม่ถูกต้องตามปฏิทิน";
        }
    }
}

if (!empty($errors)) {
    $response = [
        'status' => 'error',
        'message' => implode("\n", $errors)
    ];
    ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response);
    exit();
}

// ป้องกัน SQL Injection
$first_name = $conn->real_escape_string($first_name);
$last_name = $conn->real_escape_string($last_name);
$binocular_vision_far = $conn->real_escape_string($binocular_vision_far);
$far_vision_both = $conn->real_escape_string($far_vision_both);
$far_vision_right = $conn->real_escape_string($far_vision_right);
$far_vision_left = $conn->real_escape_string($far_vision_left);
$stereo_depth = $conn->real_escape_string($stereo_depth);
$color_discrimination = $conn->real_escape_string($color_discrimination);
$far_vertical_phoria = $conn->real_escape_string($far_vertical_phoria);
$far_lateral_phoria = $conn->real_escape_string($far_lateral_phoria);
$binocular_vision_near = $conn->real_escape_string($binocular_vision_near);
$near_vision_both = $conn->real_escape_string($near_vision_both);
$near_vision_right = $conn->real_escape_string($near_vision_right);
$near_vision_left = $conn->real_escape_string($near_vision_left);
$near_vertical_phoria = $conn->real_escape_string($near_vertical_phoria);
$near_lateral_phoria = $conn->real_escape_string($near_lateral_phoria);
$visual_field = $conn->real_escape_string($visual_field);
$organization_id = (int)$organization_id;
$department_id = (int)$department_id;
$exam_date = empty($exam_date) ? null : $exam_date;

// บันทึกค่าที่จะอัพเดทเพื่อดีบัก
$debug_data = [
    'id' => $id,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'binocular_vision_far' => $binocular_vision_far,
    'far_vision_both' => $far_vision_both,
    'far_vision_right' => $far_vision_right,
    'far_vision_left' => $far_vision_left,
    'stereo_depth' => $stereo_depth,
    'color_discrimination' => $color_discrimination,
    'far_vertical_phoria' => $far_vertical_phoria,
    'far_lateral_phoria' => $far_lateral_phoria,
    'binocular_vision_near' => $binocular_vision_near,
    'near_vision_both' => $near_vision_both,
    'near_vision_right' => $near_vision_right,
    'near_vision_left' => $near_vision_left,
    'near_vertical_phoria' => $near_vertical_phoria,
    'near_lateral_phoria' => $near_lateral_phoria,
    'visual_field' => $visual_field,
    'organization_id' => $organization_id,
    'department_id' => $department_id,
    'exam_date' => $exam_date
];
file_put_contents('debug_data.txt', print_r($debug_data, true));

// อัพเดตข้อมูล
try {
    $sql = "UPDATE eye_records 
            SET first_name = ?, 
                last_name = ?, 
                binocular_vision_far = ?, 
                far_vision_both = ?, 
                far_vision_right = ?, 
                far_vision_left = ?, 
                stereo_depth = ?, 
                color_discrimination = ?, 
                far_vertical_phoria = ?, 
                far_lateral_phoria = ?, 
                binocular_vision_near = ?, 
                near_vision_both = ?, 
                near_vision_right = ?, 
                near_vision_left = ?, 
                near_vertical_phoria = ?, 
                near_lateral_phoria = ?, 
                visual_field = ?, 
                organization_id = ?, 
                department_id = ?, 
                exam_date = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error);
    }

    $stmt->bind_param(
        "sssssssssssssssssssis",
        $first_name,
        $last_name,
        $binocular_vision_far,
        $far_vision_both,
        $far_vision_right,
        $far_vision_left,
        $stereo_depth,
        $color_discrimination,
        $far_vertical_phoria,
        $far_lateral_phoria,
        $binocular_vision_near,
        $near_vision_both,
        $near_vision_right,
        $near_vision_left,
        $near_vertical_phoria,
        $near_lateral_phoria,
        $visual_field,
        $organization_id,
        $department_id,
        $exam_date,
        $id
    );

    if (!$stmt->execute()) {
        throw new Exception("เกิดข้อผิดพลาดในการอัพเดตข้อมูล: " . $stmt->error);
    }

    $response = [
        'status' => 'success',
        'message' => 'แก้ไขข้อมูลการตรวจสายตาเรียบร้อยแล้ว',
        'redirect' => 'list_eye.php'
    ];
} catch (Exception $e) {
    file_put_contents('error_log.txt', "Error: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}

ob_end_clean();
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($response);
exit();
?>