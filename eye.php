<?php // eye.php
include 'config.php';

// ดึงข้อมูลแผนก
$departments = $conn->query("SELECT * FROM departments");

// ดึงข้อมูลกลุ่มอาชีพ (เดิมคือหน่วยงาน)
$organizations = $conn->query("SELECT * FROM organizations");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>บันทึกผลตรวจตา</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1>บันทึกผลตรวจตา</h1>
        <form id="eyeForm" method="POST" action="submit_eye.php">
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="first_name">ชื่อ:</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group col-6">
                    <label for="last_name">นามสกุล:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>
            <div class="form-group col-12">
                <label for="exam_date">วันที่ตรวจ (พ.ศ.):</label>
                <input type="date" id="exam_date" name="exam_date" required>
                <div id="exam_date_thai" style="margin-top: 5px; color: #555;"></div>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="organization_id">กลุ่มอาชีพ:</label>
                    <select id="organization_id" name="organization_id" required>
                        <option value="">-- เลือกกลุ่มอาชีพ --</option>
                        <?php while ($org = $organizations->fetch_assoc()): ?>
                            <option value="<?= $org['id'] ?>"><?= $org['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-6">
                    <label for="department_id">แผนก:</label>
                    <select id="department_id" name="department_id" required>
                        <option value="">-- เลือกแผนก --</option>
                        <?php while ($dep = $departments->fetch_assoc()): ?>
                            <option value="<?= $dep['id'] ?>"><?= $dep['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <h2 style="margin-top: 20px;">การมองภาพระยะไกล</h2>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="binocular_vision_far">การมองด้วยสองตาระยะไกล (Binocular Vision Far):</label>
                    <input type="text" id="binocular_vision_far" name="binocular_vision_far">
                </div>
                <div class="form-group col-6">
                    <label for="far_vision_both">การมองภาพระยะไกลด้วยสองตา (Far Vision Both):</label>
                    <input type="text" id="far_vision_both" name="far_vision_both">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="far_vision_right">การมองภาพระยะไกลด้วยตาขวา (Far Vision Right):</label>
                    <input type="text" id="far_vision_right" name="far_vision_right">
                </div>
                <div class="form-group col-6">
                    <label for="far_vision_left">การมองภาพระยะไกลด้วยตาซ้าย (Far Vision Left):</label>
                    <input type="text" id="far_vision_left" name="far_vision_left">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="stereo_depth">การมองภาพ 3 มิติ (Stereo Depth):</label>
                    <input type="text" id="stereo_depth" name="stereo_depth">
                </div>
                <div class="form-group col-6">
                    <label for="color_discrimination">การมองจำแนกสี (Color Discrimination):</label>
                    <input type="text" id="color_discrimination" name="color_discrimination">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="far_vertical_phoria">ความสมดุลกล้ามเนื้อตาระยะไกลแนวตั้ง (Far Vertical Phoria):</label>
                    <input type="text" id="far_vertical_phoria" name="far_vertical_phoria">
                </div>
                <div class="form-group col-6">
                    <label for="far_lateral_phoria">ความสมดุลกล้ามเนื้อตาระยะไกลแนวนอน (Far Lateral Phoria):</label>
                    <input type="text" id="far_lateral_phoria" name="far_lateral_phoria">
                </div>
            </div>

            <h2 style="margin-top: 20px;">การมองภาพระยะใกล้</h2>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="binocular_vision_near">การมองด้วยสองตาระยะใกล้ (Binocular Vision Near):</label>
                    <input type="text" id="binocular_vision_near" name="binocular_vision_near">
                </div>
                <div class="form-group col-6">
                    <label for="near_vision_both">การมองภาพระยะใกล้ด้วยสองตา (Near Vision Both):</label>
                    <input type="text" id="near_vision_both" name="near_vision_both">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="near_vision_right">การมองภาพระยะใกล้ด้วยตาขวา (Near Vision Right):</label>
                    <input type="text" id="near_vision_right" name="near_vision_right">
                </div>
                <div class="form-group col-6">
                    <label for="near_vision_left">การมองภาพระยะใกล้ด้วยตาซ้าย (Near Vision Left):</label>
                    <input type="text" id="near_vision_left" name="near_vision_left">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="near_vertical_phoria">ความสมดุลกล้ามเนื้อตาระยะใกล้แนวตั้ง (Near Vertical Phoria):</label>
                    <input type="text" id="near_vertical_phoria" name="near_vertical_phoria">
                </div>
                <div class="form-group col-6">
                    <label for="near_lateral_phoria">ความสมดุลกล้ามเนื้อตาระยะใกล้แนวนอน (Near Lateral Phoria):</label>
                    <input type="text" id="near_lateral_phoria" name="near_lateral_phoria">
                </div>
            </div>

            <h2 style="margin-top: 20px;">ลานสายตา (Visual Field)</h2>
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="visual_field">ลานสายตา (Visual Field):</label>
                    <input type="text" id="visual_field" name="visual_field">
                </div>
            </div>

            <button type="submit">บันทึกผล</button>
        </form>
        <div style="margin-top: 20px;">
            <a href="manage_departments.php">จัดการแผนก</a> |
            <a href="manage_organizations.php">จัดการกลุ่มอาชีพ</a>
        </div>
    </div>
    <script>
        function getThaiMonth(month) {
            const thaiMonths = {
                '01': 'มกราคม',
                '02': 'กุมภาพันธ์',
                '03': 'มีนาคม',
                '04': 'เมษายน',
                '05': 'พฤษภาคม',
                '06': 'มิถุนายน',
                '07': 'กรกฎาคม',
                '08': 'สิงหาคม',
                '09': 'กันยายน',
                '10': 'ตุลาคม',
                '11': 'พฤศจิกายน',
                '12': 'ธันวาคม'
            };
            return thaiMonths[month] || month;
        }

        function formatThaiDate(dateStr) {
            if (!dateStr) return "";
            const [year, month, day] = dateStr.split("-");
            const yearBE = parseInt(year) + 543;
            const dayNum = parseInt(day); // ตัด 0 ข้างหน้า
            const thaiMonth = getThaiMonth(month);
            return `วันที่ตรวจ: ${dayNum} ${thaiMonth} ${yearBE}`;
        }

        // อัปเดตวันที่แสดงแบบไทยเมื่อผู้ใช้เลือกวันที่
        document.getElementById('exam_date').addEventListener('change', function() {
            const dateStr = this.value;
            const display = formatThaiDate(dateStr);
            document.getElementById('exam_date_thai').innerText = display;
        });
    </script>

</body>

</html>