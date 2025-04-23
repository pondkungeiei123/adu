<?php
require_once 'config.php';

$sql = "SELECT * FROM audio_records ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $conn->error);
}

// เตรียมข้อมูลสำหรับส่งไปยัง JavaScript
$rows = [];
$result->data_seek(0); // รีเซ็ต pointer กลับไปที่จุดเริ่มต้น
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

// ฟังก์ชันแปลงเดือนเป็นชื่อเดือนภาษาไทย
function getThaiMonth($month)
{
    $thaiMonths = [
        '01' => 'มกราคม',
        '02' => 'กุมภาพันธ์',
        '03' => 'มีนาคม',
        '04' => 'เมษายน',
        '05' => 'พฤษภาคม',
        '06' => 'มิถุนายน',
        '07' => 'กรกฎาคม',
        '08' => 'สิงหาคม',
        '09' => 'กันยายน',
        '10' => 'ตุลาคม',
        '11' => 'พฤศจิกายน',
        '12' => 'ธันวาคม'
    ];

    return isset($thaiMonths[$month]) ? $thaiMonths[$month] : $month;
}

// ฟังก์ชันแปลงวันที่เป็นรูปแบบที่ต้องการ "วันที่ตรวจ: 1 เมษายน 2568"
function formatThaiDate($dateStr)
{
    if (empty($dateStr)) return "";

    list($year_ce, $month, $day) = explode('-', $dateStr);
    $year_be = intval($year_ce) + 543;
    $day = intval($day); // ลบเลข 0 ข้างหน้า
    $thaiMonth = getThaiMonth($month);

    return "วันที่ตรวจ: $day $thaiMonth $year_be";
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายการข้อมูล Audiogram</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2c3e50;
            color: #fff;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        a {
            color: #fff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .details-btn {
            padding: 5px 10px;
            background-color: #1abc9c;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }

        .details-btn:hover {
            background-color: #16a085;
        }

        .edit-btn {
            padding: 5px 10px;
            background-color: #3498db;
            /* สีน้ำเงิน */
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }

        .edit-btn:hover {
            background-color: #2980b9;
            /* สีน้ำเงินเข้มขึ้นเมื่อเมาส์ชี้ */
        }

        .delete-btn {
            padding: 5px 10px;
            background-color: #e74c3c;
            /* สีแดง */
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #c0392b;
            /* สีแดงเข้มขึ้นเมื่อเมาส์ชี้ */
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 1000px;
            max-height: 80vh;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .modal-content h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
        }

        .modal-content p {
            margin: 10px 0;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #333;
        }

        .close-btn:hover {
            color: #e74c3c;
        }

        .audiogram-right {
            color: #e74c3c;
            font-weight: bold;
        }

        .audiogram-left {
            color: #3498db;
            font-weight: bold;
        }

        .hearing-status-normal {
            color: #2ecc71;
            font-weight: bold;
        }

        .hearing-status-abnormal {
            color: #e74c3c;
            font-weight: bold;
        }

        .hearing-summary {
            font-weight: bold;
            color: #333;
        }

        .recommendation {
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 4px solid #1abc9c;
            border-radius: 4px;
        }

        .recommendation p {
            margin: 5px 0;
        }

        .recommendation ol {
            margin: 5px 0 10px 20px;
            padding: 0;
        }

        .recommendation li {
            margin-bottom: 5px;
        }

        td .hearing-summary {
            display: block;
            margin-bottom: 5px;
        }

        td .recommendation {
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1>รายการข้อมูล Audiogram</h1>
        <div style="margin-bottom: 20px;">
            <br>
        </div>
        <?php if ($result->num_rows > 0) { ?>
            <table id="dataTable">
                <thead>
                    <tr>
                        <th>ชื่อ-นามสกุล</th>
                        <th>เพศ</th>
                        <th>อายุ</th>
                        <th>HN</th>
                        <th>วันที่ตรวจ</th>
                        <th>การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row) {
                        // แปลงวันที่เป็นรูปแบบใหม่
                        $formatted_date = formatThaiDate($row['exam_date']);
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($row['gender']); ?></td>
                            <td><?php echo htmlspecialchars($row['age']); ?></td>
                            <td><?php echo htmlspecialchars($row['hn']); ?></td>
                            <td><?php echo $formatted_date; ?></td>
                            <td>
                                <button class="details-btn" onclick="showDetails1(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)">แสดงข้อมูลทั้งหมด</button>
                                <button class="edit-btn" onclick="editRecord(<?php echo $row['id']; ?>)">แก้ไข</button>
                                <button class="delete-btn" onclick="deleteRecord(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName'], ENT_QUOTES, 'UTF-8'); ?>')">ลบ</button>
                            </td>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p style="text-align: center; color: #555;">ยังไม่มีข้อมูลในระบบ</p>
        <?php } ?>

        <!-- Modal สำหรับแสดงข้อมูลทั้งหมด -->
        <div id="detailsModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">×</span>
                <h2>ข้อมูลพนักงาน</h2>
                <div id="modalDetails"></div>
            </div>
        </div>
    </div>

    <!-- ส่งข้อมูล rows ไปยัง JavaScript -->
    <script>
        window.rows = <?php echo json_encode($rows); ?>;
    </script>

    <!-- เรียกใช้ script.js -->
    <script src="script.js"></script>

    <!-- เพิ่ม Script สำหรับการแสดงวันที่ในรูปแบบไทยในโมดอล -->
    <script>
        // ฟังก์ชันแปลงเดือนเป็นชื่อเดือนภาษาไทย
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

        // ฟังก์ชันแปลงวันที่เป็นรูปแบบไทย สำหรับใช้ใน JavaScript
        function formatThaiDate(dateStr) {
            if (!dateStr) return "";

            const [year, month, day] = dateStr.split('-');
            const yearBE = parseInt(year) + 543;
            const dayNum = parseInt(day); // ลบเลข 0 ข้างหน้า
            const thaiMonth = getThaiMonth(month);

            return `วันที่ตรวจ: ${dayNum} ${thaiMonth} ${yearBE}`;
        }

        // ถ้าฟังก์ชัน showDetails1 อยู่ใน script.js คุณอาจต้องแก้ไขหรือแทนที่ฟังก์ชันนั้น
        // หรือคุณสามารถแก้ไขฟังก์ชัน showDetails1 ที่นี่เพื่อใช้รูปแบบวันที่ใหม่
        function showDetails1(row) {
            const modal = document.getElementById('detailsModal');
            const modalDetails = document.getElementById('modalDetails');

            // แปลงวันที่เป็นรูปแบบไทย
            const formattedDate = formatThaiDate(row.exam_date);

            let detailsHTML = `
                <p><strong>ชื่อ-นามสกุล:</strong> ${row.firstName} ${row.lastName}</p>
                <p><strong>เพศ:</strong> ${row.gender}</p>
                <p><strong>อายุ:</strong> ${row.age} ปี</p>
                <p><strong>HN:</strong> ${row.hn}</p>
                <p><strong>${formattedDate}</strong></p>
                <!-- เพิ่มข้อมูลอื่นๆ ตามที่ต้องการ -->
            `;

            modalDetails.innerHTML = detailsHTML;
            modal.style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        function editRecord(id) {
            window.location.href = `edit_aud.php?id=${id}`;
        }

        // ฟังก์ชันสำหรับลบข้อมูล
        function deleteRecord(id, name) {
            if (confirm(`คุณแน่ใจหรือไม่ที่จะลบข้อมูลของ ${name}?`)) {
                fetch('delete_aud.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('ลบข้อมูลสำเร็จ');
                            window.location.reload();
                        } else {
                            alert('เกิดข้อผิดพลาดในการลบข้อมูล: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('เกิดข้อผิดพลาด: ' + error.message);
                    });
            }
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>