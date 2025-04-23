<?php
ob_start(); // เริ่ม buffer
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

// รับข้อมูลจากฟอร์ม
$establishment = $_POST['establishment'];
$department = $_POST['department'];
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$gender = $_POST['gender'];
$age = $_POST['age'];
$hn = $_POST['hn'];
$weight = $_POST['weight'];
$height = $_POST['height'];
$bmi = $_POST['bmi'];
$bmi_category = $_POST['bmi_category'];
$sbp = $_POST['sbp'];
$dbp = $_POST['dbp'];
$heartRate = $_POST['heartRate'];
$heartRate_category = "";
$bloodPressure_category = "";

$right_500 = $_POST['right_500'];
$right_1000 = $_POST['right_1000'];
$right_2000 = $_POST['right_2000'];
$right_3000 = $_POST['right_3000'];
$right_4000 = $_POST['right_4000'];
$right_6000 = $_POST['right_6000'];
$right_8000 = $_POST['right_8000'];

$left_500 = $_POST['left_500'];
$left_1000 = $_POST['left_1000'];
$left_2000 = $_POST['left_2000'];
$left_3000 = $_POST['left_3000'];
$left_4000 = $_POST['left_4000'];
$left_6000 = $_POST['left_6000'];
$left_8000 = $_POST['left_8000'];

$exam_date = $_POST['exam_date'];

// ตรวจสอบว่าวันที่ถูกส่งมาหรือไม่
if (empty($exam_date)) {
    $response = [
        'status' => 'error',
        'message' => 'กรุณากรอกวันที่ตรวจ'
    ];
    ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response);
    exit();
}

// แยกปี เดือน วัน จากข้อมูลวันที่ (รูปแบบ YYYY-MM-DD)
list($year, $month, $day) = explode('-', $exam_date);

// ตรวจสอบว่าปีเป็น พ.ศ. หรือ ค.ศ. 
// ถ้าปีมากกว่า 2400 ให้ถือว่าเป็น พ.ศ. แล้วแปลงเป็น ค.ศ.
if ((int)$year > 2400) {
    $year_ce = (int)$year - 543;
} else {
    $year_ce = (int)$year;
}

// ตรวจสอบความถูกต้องของวันที่
if (!checkdate($month, $day, $year_ce)) {
    $response = [
        'status' => 'error',
        'message' => 'วันที่ไม่ถูกต้อง กรุณาตรวจสอบวัน/เดือน/ปี'
    ];
    ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response);
    exit();
}

// สร้างวันที่ใหม่ในรูปแบบ ค.ศ. สำหรับเก็บในฐานข้อมูล
$exam_date_db = sprintf("%04d-%02d-%02d", $year_ce, $month, $day);

// แปลงวันที่เป็น พ.ศ. สำหรับแสดงผล
$year_be = $year_ce + 543;
$exam_date_display = "$day/$month/$year_be";

// ต่อจากนี้ให้ใช้ $exam_date_db ในการบันทึกลงฐานข้อมูลแทน $exam_date

// ในส่วนของการบันทึกข้อมูล ให้เปลี่ยนจาก $exam_date เป็น $exam_date_db
// ...
// ส่วนการเตรียม parameter สำหรับ bind_param
$params = [
    $establishment, $department, $firstName, $lastName, $gender, $age, $hn,
    $weight, $height, $bmi,
    $heartRate, $heartRate_category,
    $sbp, $dbp, $bloodPressure_category,
    $bmi_category,
    $right_500, $right_1000, $right_2000, $right_3000, $right_4000, $right_6000, $right_8000,
    $left_500, $left_1000, $left_2000, $left_3000, $left_4000, $left_6000, $left_8000,
    $exam_date_db  // เปลี่ยนจาก $exam_date เป็น $exam_date_db
];

// ตรวจสอบน้ำหนักและส่วนสูง
if (!is_numeric($weight) || !is_numeric($height) || $weight <= 0 || $height <= 0 || $weight >= 500 || $height >= 300) {
    $response = [
        'status' => 'error',
        'message' => 'กรุณากรอกน้ำหนักและส่วนสูงให้ถูกต้อง (น้ำหนัก < 500 กก., ส่วนสูง < 300 ซม.)'
    ];
    ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response);
    exit();
}

// ตรวจสอบความดันโลหิต
if (!is_numeric($sbp) || !is_numeric($dbp) || $sbp <= 0 || $dbp <= 0 || $sbp >= 300 || $dbp >= 200) {
    $response = [
        'status' => 'error',
        'message' => 'กรุณากรอกความดันโลหิตให้ถูกต้อง (SBP < 300, DBP < 200)'
    ];
    ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response);
    exit();
}

// ตรวจสอบข้อมูล Audiogram
$audiogram_fields = [
    'right_500' => $right_500, 'right_1000' => $right_1000, 'right_2000' => $right_2000,
    'right_3000' => $right_3000, 'right_4000' => $right_4000, 'right_6000' => $right_6000, 'right_8000' => $right_8000,
    'left_500' => $left_500, 'left_1000' => $left_1000, 'left_2000' => $left_2000,
    'left_3000' => $left_3000, 'left_4000' => $left_4000, 'left_6000' => $left_6000, 'left_8000' => $left_8000
];

foreach ($audiogram_fields as $field => $value) {
    if (!is_numeric($value) || $value < 0 || $value > 120) {
        $response = [
            'status' => 'error',
            'message' => "กรุณากรอกค่า $field ให้ถูกต้อง (0-120 dB)"
        ];
        ob_end_clean();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($response);
        exit();
    }
}

// คำนวณ BMI
$heightM = $height / 100;
$bmi = $weight / ($heightM * $heightM);

// คำนวณหมวดหมู่การเต้นของหัวใจ
if ($heartRate < 60) {
    $heartRate_category = 'ช้า';
} elseif ($heartRate >= 60 && $heartRate <= 100) {
    $heartRate_category = 'ปกติ';
} else {
    $heartRate_category = 'เร็ว';
}

// คำนวณหมวดหมู่ความดันโลหิต
if ($sbp && $dbp) {
    if ($sbp < 120 && $dbp < 80) {
        $bloodPressure_category = 'ปกติ';
    } elseif (($sbp >= 120 && $sbp <= 139) || ($dbp >= 80 && $dbp <= 89)) {
        $bloodPressure_category = 'ระยะก่อนความดันโลหิตสูง';
    } else {
        $bloodPressure_category = 'ความดันโลหิตสูง';
    }
}

// ป้องกัน SQL Injection
$establishment = $conn->real_escape_string($establishment);
$department = $conn->real_escape_string($department);
$firstName = $conn->real_escape_string($firstName);
$lastName = $conn->real_escape_string($lastName);
$gender = $conn->real_escape_string($gender);
$age = (int)$age;
$hn = $conn->real_escape_string($hn);
$weight = (float)$weight;
$height = (float)$height;
$bmi = (float)$bmi;
$heartRate = (int)$heartRate;
$sbp = (int)$sbp;
$dbp = (int)$dbp;
$bmi_category = $conn->real_escape_string($bmi_category);
$heartRate_category = $conn->real_escape_string($heartRate_category);
$bloodPressure_category = $conn->real_escape_string($bloodPressure_category);

// แปลงข้อมูล Audiogram เป็น integer
$right_500 = (int)$right_500;
$right_1000 = (int)$right_1000;
$right_2000 = (int)$right_2000;
$right_3000 = (int)$right_3000;
$right_4000 = (int)$right_4000;
$right_6000 = (int)$right_6000;
$right_8000 = (int)$right_8000;
$left_500 = (int)$left_500;
$left_1000 = (int)$left_1000;
$left_2000 = (int)$left_2000;
$left_3000 = (int)$left_3000;
$left_4000 = (int)$left_4000;
$left_6000 = (int)$left_6000;
$left_8000 = (int)$left_8000;

// บันทึกค่าที่จะ insert เพื่อดีบัก
$debug_data = [
    'establishment' => $establishment,
    'department' => $department,
    'firstName' => $firstName,
    'lastName' => $lastName,
    'gender' => $gender,
    'age' => $age,
    'hn' => $hn,
    'weight' => $weight,
    'height' => $height,
    'bmi' => $bmi,
    'heartRate' => $heartRate,
    'heartRate_category' => $heartRate_category,
    'sbp' => $sbp,
    'dbp' => $dbp,
    'bloodPressure_category' => $bloodPressure_category,
    'bmi_category' => $bmi_category,
    'right_500' => $right_500,
    'right_1000' => $right_1000,
    'right_2000' => $right_2000,
    'right_3000' => $right_3000,
    'right_4000' => $right_4000,
    'right_6000' => $right_6000,
    'right_8000' => $right_8000,
    'left_500' => $left_500,
    'left_1000' => $left_1000,
    'left_2000' => $left_2000,
    'left_3000' => $left_3000,
    'left_4000' => $left_4000,
    'left_6000' => $left_6000,
    'left_8000' => $left_8000,
    'exam_date' => $exam_date
];
file_put_contents('debug_data.txt', print_r($debug_data, true));

// แก้ไขคำสั่ง SQL และตรวจสอบความถูกต้อง
try {
    // คำสั่ง SQL ใหม่ที่ตรวจสอบจำนวนคอลัมน์และ placeholders ให้ตรงกัน
    $sql = "INSERT INTO audio_records (
        establishment, department, firstName, lastName, gender, age, hn,
        weight, height, bmi,
        heartRate, heartRate_category,
        sbp, dbp, bloodPressure_category,
        bmi_category,
        right_500, right_1000, right_2000, right_3000, right_4000, right_6000, right_8000,
        left_500, left_1000, left_2000, left_3000, left_4000, left_6000, left_8000,
        exam_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // นับจำนวนคอลัมน์และ placeholders
    $columns = substr_count($sql, ',', strpos($sql, '(') + 1, strpos($sql, ')') - strpos($sql, '(') - 1) + 1;
    $placeholders = substr_count($sql, '?');

    file_put_contents('error_log.txt', "Columns: $columns, Placeholders: $placeholders" . PHP_EOL, FILE_APPEND);

    if ($columns != $placeholders) {
        throw new Exception("จำนวนคอลัมน์ ($columns) ไม่ตรงกับจำนวน placeholders ($placeholders)");
    }

    // Prepare statement
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error);
    }

    // สร้าง array ของค่าที่จะ bind เพื่อเช็คจำนวน
    $params = [
        $establishment, $department, $firstName, $lastName, $gender, $age, $hn,
        $weight, $height, $bmi,
        $heartRate, $heartRate_category,
        $sbp, $dbp, $bloodPressure_category,
        $bmi_category,
        $right_500, $right_1000, $right_2000, $right_3000, $right_4000, $right_6000, $right_8000,
        $left_500, $left_1000, $left_2000, $left_3000, $left_4000, $left_6000, $left_8000,
        $exam_date
    ];
    
    file_put_contents('error_log.txt', "จำนวนพารามิเตอร์: " . count($params) . PHP_EOL, FILE_APPEND);
    
    // สร้างสตริง types สำหรับ bind_param ให้เท่ากับจำนวนพารามิเตอร์
    $types = str_repeat('s', count($params)); // กำหนดให้ทุกค่าเป็น string เพื่อความปลอดภัย
    
    // ใช้ call_user_func_array เพื่อเรียก bind_param โดยส่งพารามิเตอร์เป็น array
    $bind_params = array_merge([$types], $params);
    call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    // Execute statement
    if (!$stmt->execute()) {
        throw new Exception("เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt->error);
    }
    
    $response = [
        'status' => 'success',
        'message' => "บันทึกข้อมูลสำเร็จ\nวันที่ตรวจ: $exam_date_display\nBMI: $bmi\nหมวดหมู่ BMI: $bmi_category\nหมวดหมู่การเต้นของหัวใจ: $heartRate_category\nหมวดหมู่ความดันโลหิต: $bloodPressure_category",
        'redirect' => 'index.php'
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