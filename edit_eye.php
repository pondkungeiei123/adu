<?php 
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ไม่พบรหัสข้อมูล");
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM eye_records WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบข้อมูลที่ต้องการแก้ไข");
}

$row = $result->fetch_assoc();
$stmt->close();

function convertToThaiDate($dateStr) {
    if (empty($dateStr)) return "";
    list($year_ce, $month, $day) = explode('-', $dateStr);
    $year_be = intval($year_ce) + 543;
    return "$year_be-$month-$day";
}

function convertToGregorianDate($dateStr) {
    if (empty($dateStr)) return "";
    list($year_be, $month, $day) = explode('-', $dateStr);
    $year_ce = intval($year_be) - 543;
    return "$year_ce-$month-$day";
}

$errors = [];
$success = false; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $binocular_vision_far = trim($_POST['binocular_vision_far'] ?? '');
    $far_vision_both = trim($_POST['far_vision_both'] ?? '');
    $organization_id = trim($_POST['organization_id'] ?? '');
    $department_id = trim($_POST['department_id'] ?? '');
    $exam_date = trim($_POST['exam_date'] ?? '');
    // และข้อมูลที่เหลือgit remote add origin https://github.com/pondkungeiei123/adu.git

    if (empty($binocular_vision_far)) $errors[] = "กรุณากรอกการมองเห็นสองตาในระยะไกล";
    // ตรวจสอบข้อมูลที่เหลือ

    if (!empty($exam_date)) {
        $exam_date = convertToGregorianDate($exam_date);
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $exam_date)) {
            $errors[] = "รูปแบบวันที่ไม่ถูกต้อง";
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE eye_records SET binocular_vision_far = ?, far_vision_both = ?, organization_id = ?, department_id = ?, exam_date = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $binocular_vision_far, $far_vision_both, $organization_id, $department_id, $exam_date, $id);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $conn->error;
        }

        $stmt->close();
    }
}

$exam_date_display = convertToThaiDate($row['exam_date']);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลการตรวจสายตา</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Styles สำหรับฟอร์ม */
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .form-container h2 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group select {
            height: 36px;
        }

        .form-group input[type="submit"] {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .error {
            color: #e74c3c;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background-color: #95a5a6;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .back-btn:hover {
            background-color: #7f8c8d;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="form-container">
            <a href="index.php" class="back-btn">กลับไปยังรายการ</a>
            <h2>แก้ไขข้อมูลการตรวจสายตา</h2>
            <?php if (!empty($errors)) { ?>
                <div class="error">
                    <?php foreach ($errors as $error) { ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php } ?>
                </div>
            <?php } ?>
            <form method="POST">
                <div class="form-group">
                    <label for="binocular_vision_far">การมองเห็นสองตา (ระยะไกล):</label>
                    <input type="text" id="binocular_vision_far" name="binocular_vision_far" value="<?php echo htmlspecialchars($row['binocular_vision_far']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="far_vision_both">การมองเห็นทั้งสองตา (ระยะไกล):</label>
                    <input type="text" id="far_vision_both" name="far_vision_both" value="<?php echo htmlspecialchars($row['far_vision_both']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="organization_id">รหัสองค์กร:</label>
                    <input type="number" id="organization_id" name="organization_id" value="<?php echo htmlspecialchars($row['organization_id']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="department_id">รหัสแผนก:</label>
                    <input type="number" id="department_id" name="department_id" value="<?php echo htmlspecialchars($row['department_id']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="exam_date">วันที่ตรวจ (พ.ศ.):</label>
                    <input type="date" id="exam_date" name="exam_date" value="<?php echo htmlspecialchars($exam_date_display); ?>" required>
                </div>
                <div class="form-group">
                    <input type="submit" value="บันทึกการแก้ไข">
                </div>
            </form>
        </div>
    </div>

    <?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: 'แก้ไขข้อมูลเรียบร้อยแล้ว',
            confirmButtonText: 'ตกลง'
        }).then(() => {
            window.location.href = 'list_eye.php';
        });
    </script>
    <?php endif; ?>
</body>
</html>

<?php $conn->close(); ?>
