<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ไม่พบรหัสข้อมูล");
}

$id = intval($_GET['id']);

// ดึงข้อมูลสถานประกอบการจากตาราง Establishment
$establishments = $conn->query("SELECT * FROM Establishment ORDER BY name");

// ดึงข้อมูลจากตาราง audio_records
$sql = "SELECT * FROM audio_records WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบข้อมูลที่ต้องการแก้ไข");
}

$row = $result->fetch_assoc();
$stmt->close();

function convertToThaiDate($dateStr)
{
    if (empty($dateStr) || $dateStr == '0000-00-00') return "";
    list($year_ce, $month, $day) = explode('-', $dateStr);
    $year_be = intval($year_ce) + 543;
    return sprintf("%02d/%02d/%04d", intval($day), intval($month), $year_be);
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $establishment_id = trim($_POST['establishment'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $hn = trim($_POST['hn'] ?? '');
    $weight = trim($_POST['weight'] ?? '');
    $height = trim($_POST['height'] ?? '');
    $sbp = trim($_POST['sbp'] ?? '');
    $dbp = trim($_POST['dbp'] ?? '');
    $heart_rate = trim($_POST['heart_rate'] ?? '');
    $exam_date = trim($_POST['exam_date_ce'] ?? ''); // รับวันที่ ค.ศ. โดยตรง
    // ผล Audiogram หูขวา
    $right_500 = trim($_POST['right_500'] ?? '');
    $right_1000 = trim($_POST['right_1000'] ?? '');
    $right_2000 = trim($_POST['right_2000'] ?? '');
    $right_3000 = trim($_POST['right_3000'] ?? '');
    $right_4000 = trim($_POST['right_4000'] ?? '');
    $right_6000 = trim($_POST['right_6000'] ?? '');
    $right_8000 = trim($_POST['right_8000'] ?? '');
    // ผล Audiogram หูซ้าย
    $left_500 = trim($_POST['left_500'] ?? '');
    $left_1000 = trim($_POST['left_1000'] ?? '');
    $left_2000 = trim($_POST['left_2000'] ?? '');
    $left_3000 = trim($_POST['left_3000'] ?? '');
    $left_4000 = trim($_POST['left_4000'] ?? '');
    $left_6000 = trim($_POST['left_6000'] ?? '');
    $left_8000 = trim($_POST['left_8000'] ?? '');

    // ตรวจสอบข้อมูล
    if (empty($establishment_id) || !is_numeric($establishment_id)) {
        $errors[] = "กรุณาเลือกสถานประกอบการที่ถูกต้อง";
    }
    if (empty($department)) $errors[] = "กรุณากรอกแผนก";
    if (empty($firstName)) $errors[] = "กรุณากรอกชื่อ";
    if (empty($lastName)) $errors[] = "กรุณากรอกนามสกุล";
    if (empty($gender)) $errors[] = "กรุณาเลือกเพศ";
    if (empty($age) || !is_numeric($age) || $age <= 0) $errors[] = "กรุณากรอกอายุที่ถูกต้อง";
    if (empty($hn)) $errors[] = "กรุณากรอก HN";
    if (empty($weight) || !is_numeric($weight) || $weight <= 0) $errors[] = "กรุณากรอกน้ำหนักที่ถูกต้อง";
    if (empty($height) || !is_numeric($height) || $height <= 0) $errors[] = "กรุณากรอกส่วนสูงที่ถูกต้อง";
    if (empty($sbp) || !is_numeric($sbp) || $sbp <= 0) $errors[] = "กรุณากรอกความดันโลหิต (SBP) ที่ถูกต้อง";
    if (empty($dbp) || !is_numeric($dbp) || $dbp <= 0) $errors[] = "กรุณากรอกความดันโลหิต (DBP) ที่ถูกต้อง";
    if (empty($heart_rate) || !is_numeric($heart_rate) || $heart_rate <= 0) $errors[] = "กรุณากรอกการเต้นของหัวใจที่ถูกต้อง";
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
    // ตรวจสอบผล Audiogram
    if (empty($right_500) || !is_numeric($right_500)) $errors[] = "กรุณากรอกผล Audiogram หูขวา 500 Hz";
    if (empty($right_1000) || !is_numeric($right_1000)) $errors[] = "กรุณากรอกผล Audiogram หูขวา 1000 Hz";
    if (empty($right_2000) || !is_numeric($right_2000)) $errors[] = "กรุณากรอกผล Audiogram หูขวา 2000 Hz";
    if (empty($right_3000) || !is_numeric($right_3000)) $errors[] = "กรุณากรอกผล Audiogram หูขวา 3000 Hz";
    if (empty($right_4000) || !is_numeric($right_4000)) $errors[] = "กรุณากรอกผล Audiogram หูขวา 4000 Hz";
    if (empty($right_6000) || !is_numeric($right_6000)) $errors[] = "กรุณากรอกผล Audiogram หูขวา 6000 Hz";
    if (empty($right_8000) || !is_numeric($right_8000)) $errors[] = "กรุณากรอกผล Audiogram หูขวา 8000 Hz";
    if (empty($left_500) || !is_numeric($left_500)) $errors[] = "กรุณากรอกผล Audiogram หูซ้าย 500 Hz";
    if (empty($left_1000) || !is_numeric($left_1000)) $errors[] = "กรุณากรอกผล Audiogram หูซ้าย 1000 Hz";
    if (empty($left_2000) || !is_numeric($left_2000)) $errors[] = "กรุณากรอกผล Audiogram หูซ้าย 2000 Hz";
    if (empty($left_3000) || !is_numeric($left_3000)) $errors[] = "กรุณากรอกผล Audiogram หูซ้าย 3000 Hz";
    if (empty($left_4000) || !is_numeric($left_4000)) $errors[] = "กรุณากรอกผล Audiogram หูซ้าย 4000 Hz";
    if (empty($left_6000) || !is_numeric($left_6000)) $errors[] = "กรุณากรอกผล Audiogram หูซ้าย 6000 Hz";
    if (empty($left_8000) || !is_numeric($left_8000)) $errors[] = "กรุณากรอกผล Audiogram หูซ้าย 8000 Hz";

    if (empty($errors)) {
        $sql = "UPDATE audio_records 
                SET establishment_id = ?, department = ?, firstName = ?, lastName = ?, gender = ?, age = ?, hn = ?, weight = ?, height = ?, sbp = ?, dbp = ?, heart_rate = ?, exam_date = ?, right_500 = ?, right_1000 = ?, right_2000 = ?, right_3000 = ?, right_4000 = ?, right_6000 = ?, right_8000 = ?, left_500 = ?, left_1000 = ?, left_2000 = ?, left_3000 = ?, left_4000 = ?, left_6000 = ?, left_8000 = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $errors[] = "Prepare failed: " . $conn->error;
        } else {
            $exam_date = trim($_POST['exam_date_ce'] ?? '');

// ตรวจสอบว่าค่าวันที่ไม่ว่างและมีรูปแบบที่ถูกต้อง
if (!empty($exam_date) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $exam_date)) {
    $exam_date = $exam_date; // ใช้ค่าที่ส่งมา
} else {
    $exam_date = null; // หากไม่ถูกต้องให้ตั้งค่าเป็น NULL
}
            file_put_contents('debug_sql.txt', "exam_date: $exam_date\n", FILE_APPEND);
            $stmt->bind_param(
                "issssisiddiiisiiiiiiiiiiiiii",
                $establishment_id,
                $department,
                $firstName,
                $lastName,
                $gender,
                $age,
                $hn,
                $weight,
                $height,
                $sbp,
                $dbp,
                $heart_rate,
                $exam_date, // ตรวจสอบว่าค่านี้ไม่ใช่ NULL หรือ ''
                $right_500,
                $right_1000,
                $right_2000,
                $right_3000,
                $right_4000,
                $right_6000,
                $right_8000,
                $left_500,
                $left_1000,
                $left_2000,
                $left_3000,
                $left_4000,
                $left_6000,
                $left_8000,
                $id
            );
            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Execute failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$exam_date_display = convertToThaiDate($row['exam_date']);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูล Audiogram</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .form-container {
            max-width: 800px;
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

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-row .form-group.col-12 {
            flex: 0 0 100%;
        }

        .form-row .form-group.col-6 {
            flex: 0 0 calc(50% - 7.5px);
        }

        .form-row .form-group.col-4 {
            flex: 0 0 calc(33.33% - 10px);
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="form-container">
            <a href="list_aud.php" class="back-btn">กลับไปยังรายการ</a>
            <h2>แก้ไขข้อมูล Audiogram</h2>
            <?php if (!empty($errors)) { ?>
                <div class="error">
                    <?php foreach ($errors as $error) { ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php } ?>
                </div>
            <?php } ?>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group col-6">
                        <label for="establishment">สถานประกอบการ:</label>
                        <select id="establishment" name="establishment" required>
                            <option value="">เลือกสถานประกอบการ</option>
                            <?php
                            if ($establishments->num_rows > 0):
                                while ($est_row = $establishments->fetch_assoc()):
                                    $selected = (isset($row['establishment_id']) && $row['establishment_id'] == $est_row['id']) ? 'selected' : '';
                            ?>
                                    <option value="<?php echo htmlspecialchars($est_row['id']); ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($est_row['name']); ?>
                                    </option>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <option value="">ไม่มีสถานประกอบการ</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group col-6">
                        <label for="department">แผนก:</label>
                        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($row['department'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-4">
                        <label for="firstName">ชื่อ:</label>
                        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($row['firstName']); ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="lastName">นามสกุล:</label>
                        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($row['lastName']); ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="gender">เพศ:</label>
                        <select id="gender" name="gender" required>
                            <option value="">-- เลือกเพศ --</option>
                            <option value="ชาย" <?php if ($row['gender'] === 'ชาย') echo 'selected'; ?>>ชาย</option>
                            <option value="หญิง" <?php if ($row['gender'] === 'หญิง') echo 'selected'; ?>>หญิง</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="age">อายุ:</label>
                        <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($row['age']); ?>" required min="1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="hn">HN:</label>
                        <input type="text" id="hn" name="hn" value="<?php echo htmlspecialchars($row['hn']); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="weight">น้ำหนัก (กิโลกรัม):</label>
                        <input type="number" id="weight" name="weight" step="0.1" value="<?php echo htmlspecialchars($row['weight'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="height">ส่วนสูง (เซนติเมตร):</label>
                        <input type="number" id="height" name="height" step="0.1" value="<?php echo htmlspecialchars($row['height'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-6">
                        <label for="sbp">ความดันโลหิต (SBP):</label>
                        <input type="number" id="sbp" name="sbp" value="<?php echo htmlspecialchars($row['sbp'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group col-6">
                        <label for="dbp">ความดันโลหิต (DBP):</label>
                        <input type="number" id="dbp" name="dbp" value="<?php echo htmlspecialchars($row['dbp'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-6">
                        <label for="heart_rate">การเต้นของหัวใจ (ครั้ง/นาที):</label>
                        <input type="number" id="heart_rate" name="heart_rate" value="<?php echo htmlspecialchars($row['heart_rate'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exam_date_ce">วันที่ตรวจ (พ.ศ.):</label>
                    <input type="date" id="exam_date_ce" name="exam_date_ce" required value="<?php echo htmlspecialchars($row['exam_date'] ?? ''); ?>">
                    <small>วันที่ในรูปแบบ พ.ศ.: <?php echo htmlspecialchars($exam_date_display); ?></small>
                </div>

                <!-- เพิ่มส่วน Audiogram -->
                <h2 style="margin-top: 20px;">ผลการตรวจการได้ยิน (Audiogram)</h2>
                <!-- หูขวา -->
                <div class="form-row">
                    <div class="form-group col-12">
                        <label>หูขวา (dB):</label>
                    </div>
                    <div class="form-group col-4">
                        <label for="right_500">500 Hz:</label>
                        <input type="number" id="right_500" name="right_500" step="1" value="<?php echo htmlspecialchars($row['right_500'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="right_1000">1000 Hz:</label>
                        <input type="number" id="right_1000" name="right_1000" step="1" value="<?php echo htmlspecialchars($row['right_1000'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="right_2000">2000 Hz:</label>
                        <input type="number" id="right_2000" name="right_2000" step="1" value="<?php echo htmlspecialchars($row['right_2000'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-4">
                        <label for="right_3000">3000 Hz:</label>
                        <input type="number" id="right_3000" name="right_3000" step="1" value="<?php echo htmlspecialchars($row['right_3000'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="right_4000">4000 Hz:</label>
                        <input type="number" id="right_4000" name="right_4000" step="1" value="<?php echo htmlspecialchars($row['right_4000'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="right_6000">6000 Hz:</label>
                        <input type="number" id="right_6000" name="right_6000" step="1" value="<?php echo htmlspecialchars($row['right_6000'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-4">
                        <label for="right_8000">8000 Hz:</label>
                        <input type="number" id="right_8000" name="right_8000" step="1" value="<?php echo htmlspecialchars($row['right_8000'] ?? ''); ?>" required>
                    </div>
                </div>

                <!-- หูซ้าย -->
                <div class="form-row">
                    <div class="form-group col-12">
                        <label>หูซ้าย (dB):</label>
                    </div>
                    <div class="form-group col-4">
                        <label for="left_500">500 Hz:</label>
                        <input type="number" id="left_500" name="left_500" step="1" value="<?php echo htmlspecialchars($row['left_500'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="left_1000">1000 Hz:</label>
                        <input type="number" id="left_1000" name="left_1000" step="1" value="<?php echo htmlspecialchars($row['left_1000'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="left_2000">2000 Hz:</label>
                        <input type="number" id="left_2000" name="left_2000" step="1" value="<?php echo htmlspecialchars($row['left_2000'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-4">
                        <label for="left_3000">3000 Hz:</label>
                        <input type="number" id="left_3000" name="left_3000" step="1" value="<?php echo htmlspecialchars($row['left_3000'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="left_4000">4000 Hz:</label>
                        <input type="number" id="left_4000" name="left_4000" step="1" value="<?php echo htmlspecialchars($row['left_4000'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="left_6000">6000 Hz:</label>
                        <input type="number" id="left_6000" name="left_6000" step="1" value="<?php echo htmlspecialchars($row['left_6000'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-4">
                        <label for="left_8000">8000 Hz:</label>
                        <input type="number" id="left_8000" name="left_8000" step="1" value="<?php echo htmlspecialchars($row['left_8000'] ?? ''); ?>" required>
                    </div>
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
                window.location.href = 'list_aud.php';
            });
        </script>
    <?php endif; ?>
</body>

</html>

<?php $conn->close(); ?>