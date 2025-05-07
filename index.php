<?php
include 'config.php';

// ดึงข้อมูลสถานประกอบการสำหรับแสดงใน dropdown
$sql = "SELECT * FROM establishment";
$establishments = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>กรอกข้อมูล Audiogram</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1>กรอกข้อมูล Audiogram</h1>
        <form id="dataForm" method="post" action="submit.php">
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="establishment">สถานประกอบการ:</label>
                    <select id="establishment" name="establishment" required>
                        <option value="">เลือกสถานประกอบการ</option>
                        <?php
                        if ($establishments->num_rows > 0):
                            while ($row = $establishments->fetch_assoc()):
                        ?>
                                <option value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <?php echo htmlspecialchars($row['name']); ?>
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
                    <input type="text" id="department" name="department" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-4">
                    <label for="firstName">ชื่อ:</label>
                    <input type="text" id="firstName" name="firstName" required>
                </div>
                <div class="form-group col-4">
                    <label for="lastName">นามสกุล:</label>
                    <input type="text" id="lastName" name="lastName" required>
                </div>
                <div class="form-group col-4">
                    <label for="gender">เพศ:</label>
                    <select id="gender" name="gender" required>
                        <option value="">เลือกเพศ</option>
                        <option value="ชาย">ชาย</option>
                        <option value="หญิง">หญิง</option>
                        <option value="อื่นๆ">อื่นๆ</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-12">
                    <label for="age">อายุ (ปี):</label>
                    <input type="number" id="age" name="age" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-12">
                    <label for="hn">HN:</label>
                    <input type="text" id="hn" name="hn" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-12">
                    <label for="weight">น้ำหนัก (กิโลกรัม):</label>
                    <input type="number" id="weight" name="weight" step="0.1" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-12">
                    <label for="height">ส่วนสูง (เซนติเมตร):</label>
                    <input type="number" id="height" name="height" step="0.1" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-12">
                    <label for="bmi">ดัชนีมวลกาย (BMI):</label>
                    <input type="number" id="bmi" name="bmi" step="0.1" readonly>
                    <input type="hidden" id="bmi_category" name="bmi_category">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="sbp">ความดันโลหิต (SBP):</label>
                    <input type="number" id="sbp" name="sbp" required>
                </div>
                <div class="form-group col-6">
                    <label for="dbp">ความดันโลหิต (DBP):</label>
                    <input type="number" id="dbp" name="dbp" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="heartRate">การเต้นของหัวใจ (ครั้ง/นาที):</label>
                    <input type="number" id="heartRate" name="heartRate" required>
                </div>
            </div>
            <div class="form-group col-12">
                <label for="exam_date">วันที่ตรวจ (พ.ศ.):</label>
                <input type="date" id="exam_date" name="exam_date" required>
                <div id="exam_date_thai" style="margin-top: 5px; color: #555;"></div>
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
                    <input type="number" id="right_500" name="right_500" step="1" required>
                </div>
                <div class="form-group col-4">
                    <label for="right_1000">1000 Hz:</label>
                    <input type="number" id="right_1000" name="right_1000" step="1" required>
                </div>
                <div class="form-group col-4">
                    <label for="right_2000">2000 Hz:</label>
                    <input type="number" id="right_2000" name="right_2000" step="1" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-4">
                    <label for="right_3000">3000 Hz:</label>
                    <input type="number" id="right_3000" name="right_3000" step="1" required>
                </div>
                <div class="form-group col-4">
                    <label for="right_4000">4000 Hz:</label>
                    <input type="number" id="right_4000" name="right_4000" step="1" required>
                </div>
                <div class="form-group col-4">
                    <label for="right_6000">6000 Hz:</label>
                    <input type="number" id="right_6000" name="right_6000" step="1" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-4">
                    <label for="right_8000">8000 Hz:</label>
                    <input type="number" id="right_8000" name="right_8000" step="1" required>
                </div>
            </div>

            <!-- หูซ้าย -->
            <div class="form-row">
                <div class="form-group col-12">
                    <label>หูซ้าย (dB):</label>
                </div>
                <div class="form-group col-4">
                    <label for="left_500">500 Hz:</label>
                    <input type="number" id="left_500" name="left_500" step="1" required>
                </div>
                <div class="form-group col-4">
                    <label for="left_1000">1000 Hz:</label>
                    <input type="number" id="left_1000" name="left_1000" step="1" required>
                </div>
                <div class="form-group col-4">
                    <label for="left_2000">2000 Hz:</label>
                    <input type="number" id="left_2000" name="left_2000" step="1" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-4">
                    <label for="left_3000">3000 Hz:</label>
                    <input type="number" id="left_3000" name="left_3000" step="1" required>
                </div>
                <div class="form-group col-4">
                    <label for="left_4000">4000 Hz:</label>
                    <input type="number" id="left_4000" name="left_4000" step="1" required>
                </div>
                <div class="form-group col-4">
                    <label for="left_6000">6000 Hz:</label>
                    <input type="number" id="left_6000" name="left_6000" step="1" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-4">
                    <label for="left_8000">8000 Hz:</label>
                    <input type="number" id="left_8000" name="left_8000" step="1" required>
                </div>
            </div>

            <button type="submit">เพิ่มข้อมูล</button>
        </form>
    </div>
    <script src="script.js"></script>
</body>

</html>

<?php
$conn->close();
?>