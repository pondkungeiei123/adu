<?php
require_once 'config.php';

// ดึงข้อมูลจากตาราง eye_records พร้อมชื่อหน่วยงานและแผนก
$sql = "SELECT e.*, d.name AS department_name, o.name AS organization_name 
        FROM eye_records e 
        LEFT JOIN departments d ON e.department_id = d.id 
        LEFT JOIN organizations o ON e.organization_id = o.id
        ORDER BY e.id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $conn->error);
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

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

function formatThaiDate($dateStr)
{
    if (empty($dateStr)) return "";
    [$year, $month, $day] = explode('-', $dateStr);
    $year += 543;
    return intval($day) . " " . getThaiMonth($month) . " " . $year;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายการข้อมูลตรวจตา</title>
    <link rel="stylesheet" href="style.css"> <!-- ใช้ style.css -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ตาราง */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead th {
            background-color: #2c3e50;
            color: #fff;
            padding: 10px;
            text-align: left;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tbody tr:hover {
            background-color: #dcdcdc;
        }

        td,
        th {
            padding: 10px;
            border: 1px solid #ddd;
        }

        /* ปุ่ม */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 5px 10px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .details-btn {
            padding: 6px 14px;
            background-color: #1abc9c;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .details-btn:hover {
            background-color: #16a085;
        }

        #detailsModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            /* ความกว้างของ Modal */
            max-width: 1000px;
            /* ความกว้างสูงสุด */
            max-height: 80vh;
            /* ความสูงสูงสุด */
            overflow-y: auto;
            /* เพิ่ม scroll แนวตั้ง */
            overflow-x: hidden;
            /* ซ่อน scroll แนวนอน */
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .details-btn:hover {
            background-color: #16a085;
        }

        .edit-btn {
            background-color: #3498db;
        }

        .edit-btn:hover {
            background-color: #2980b9;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1>รายการข้อมูลตรวจตา</h1>

        <?php if (count($rows) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อ-นามสกุล</th>
                        <th>แผนก</th>
                        <th>หน่วยงาน</th>
                        <th>วันที่ตรวจ</th>
                        <th>การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['department_name']) ?></td>
                            <td><?= htmlspecialchars($row['organization_name']) ?></td>
                            <td><?= formatThaiDate($row['exam_date']) ?></td>
                            <td>
                                <button class="details-btn" onclick='showDetails2(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>แสดงรายละเอียด</button>
                                <button class="edit-btn" onclick="window.location.href='edit_eye.php?id=<?= $row['id'] ?>'">แก้ไข</button>
                                <button class="delete-btn" onclick="deleteRecord(<?= $row['id'] ?>)">ลบ</button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #555;">ยังไม่มีข้อมูลในระบบ</p>
        <?php endif ?>
    </div>

    <!-- Modal -->
    <div id="detailsModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">×</span>
            <h2>ข้อมูลผลตรวจตา</h2>
            <div id="modalDetails"></div>
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
            const [year, month, day] = dateStr.split('-');
            return `${parseInt(day)} ${getThaiMonth(month)} ${parseInt(year) + 543}`;
        }
    </script>
    <script src="script.js"></script>
</body>

</html>

<?php $conn->close(); ?>