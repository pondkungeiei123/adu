<?php
require_once 'config.php';

$sql = "SELECT ar.*, e.name AS establishment_name 
        FROM audio_records ar 
        LEFT JOIN establishment e ON ar.establishment_id = e.id 
        ORDER BY ar.id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $conn->error);
}

// เตรียมข้อมูลสำหรับส่งไปยัง JavaScript
$rows = [];
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
    return $thaiMonths[$month] ?? $month;
}

// ฟังก์ชันแปลงวันที่เป็นรูปแบบที่ต้องการ
function formatThaiDate($dateStr)
{
    if (empty($dateStr) || $dateStr === '0000-00-00' || $dateStr === null) {
        return "ไม่ระบุ";
    }
    try {
        [$year, $month, $day] = explode('-', $dateStr);
        $year = intval($year);
        $day = intval($day);
        if ($year < 1000 || $month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return "วันที่ไม่ถูกต้อง";
        }
        $year += 543;
        return $day . " " . getThaiMonth(str_pad($month, 2, '0', STR_PAD_LEFT)) . " " . $year;
    } catch (Exception $e) {
        return "ไม่ระบุ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายการข้อมูล Audiogram</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }

        .edit-btn:hover {
            background-color: #2980b9;
        }

        .delete-btn {
            padding: 5px 10px;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #c0392b;
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

        <?php if (count($rows) > 0): ?>
            <table id="dataTable">
                <thead>
                    <tr>
                        <th>ชื่อ-นามสกุล</th>
                        <th>เพศ</th>
                        <th>อายุ</th>
                        <th>HN</th>
                        <th>วันที่ตรวจ</th>
                        <th>สถานประกอบการ</th> <!-- เพิ่มคอลัมน์นี้ -->
                        <th>การดำเนินการ</th>
                        <th>
                            <div style="margin-bottom: 10px; text-align: right;">
                                <input type="text" id="searchInput" placeholder="ค้นหา..." style="padding: 6px; width: 250px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></td>
                            <td><?= htmlspecialchars($row['gender']) ?></td>
                            <td><?= htmlspecialchars($row['age']) ?></td>
                            <td><?= htmlspecialchars($row['hn']) ?></td>
                            <td><?= formatThaiDate($row['exam_date']) ?></td>
                            <td><?= htmlspecialchars($row['establishment_name'] ?? 'ไม่ระบุ') ?></td> <!-- แสดงชื่อสถานประกอบการ -->
                            <td>
                                <button class="details-btn" onclick='showDetails1(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>แสดงข้อมูลทั้งหมด</button>
                                <button class="edit-btn" onclick="editRecord(<?= $row['id'] ?>)">แก้ไข</button>
                                <button class="delete-btn" onclick="deleteRecord(<?= $row['id'] ?>, '<?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName'], ENT_QUOTES, 'UTF-8') ?>')">ลบ</button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #555;">ยังไม่มีข้อมูลในระบบ</p>
        <?php endif ?>

        <!-- Modal สำหรับแสดงข้อมูลทั้งหมด -->
        <div id="detailsModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">×</span>
                <h2>ข้อมูลพนักงาน</h2>
                <div id="modalDetails"></div>
            </div>
        </div>
    </div>

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

        // ฟังก์ชันแปลงวันที่เป็นรูปแบบไทย
        function formatThaiDate(dateStr) {
            if (!dateStr || dateStr === '0000-00-00' || dateStr === null) {
                return "ไม่ระบุ";
            }
            try {
                const [year, month, day] = dateStr.split('-');
                return `${parseInt(day)} ${getThaiMonth(month)} ${parseInt(year) + 543}`;
            } catch (e) {
                return "ไม่ระบุ";
            }
        }

        // แสดงรายละเอียดใน Modal
        function showDetails1(row) {
            const modal = document.getElementById('detailsModal');
            const modalDetails = document.getElementById('modalDetails');
            const formattedDate = formatThaiDate(row.exam_date);

            let detailsHTML = `
                <p><strong>ชื่อ-นามสกุล:</strong> ${row.firstName} ${row.lastName}</p>
                <p><strong>เพศ:</strong> ${row.gender}</p>
                <p><strong>อายุ:</strong> ${row.age} ปี</p>
                <p><strong>HN:</strong> ${row.hn}</p>
                <p><strong>วันที่ตรวจ:</strong> ${formattedDate}</p>
                <p><strong>สถานประกอบการ:</strong> ${row.establishment_name || 'ไม่ระบุ'}</p>
            `;

            modalDetails.innerHTML = detailsHTML;
            modal.style.display = 'flex';
        }

        // ปิด Modal
        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        // แก้ไขข้อมูล
        function editRecord(id) {
            window.location.href = `edit_aud.php?id=${id}`;
        }

        // ลบข้อมูลด้วย SweetAlert2
        function deleteRecord(id, name) {
            Swal.fire({
                title: 'คุณแน่ใจไหม?',
                text: `คุณต้องการลบข้อมูลของ ${name} จริงหรือไม่?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete_aud.php?id=${id}`;
                }
            });
        }

        // ฟังก์ชันกรองข้อมูลในตาราง
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#dataTable tbody tr');

            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchValue)) {
                    row.style.display = ''; // แสดงแถวที่ตรงกับคำค้นหา
                } else {
                    row.style.display = 'none'; // ซ่อนแถวที่ไม่ตรงกับคำค้นหา
                }
            });
        });
    </script>
    <script src="script.js"></script>
</body>

</html>

<?php $conn->close(); ?>