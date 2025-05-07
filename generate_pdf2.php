<?php
// เปิด Error Reporting ชัดเจน
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// กำหนด Path Cache ของ TCPDF
if (!defined('K_PATH_CACHE')) {
    define('K_PATH_CACHE', __DIR__ . '/tcpdf/cache/');
}

require_once 'config.php';
require_once 'tcpdf/tcpdf.php'; // Adjust path as needed

// Function to format date in Thai
function formatThaiDate($date)
{
    if (empty($date)) return "-";

    // Convert DATETIME to DateTime object
    try {
        $dateTime = new DateTime($date);
        $year = $dateTime->format('Y');
        // Check if date is unreasonably old
        if ($year < 1900 || $year > date('Y') + 1) {
            return "-";
        }
        $thai_months = [
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
        $year = (int)$dateTime->format('Y') + 543; // Convert to Buddhist era
        $month = $thai_months[$dateTime->format('m')] ?? $dateTime->format('m');
        $day = (int)$dateTime->format('d'); // Remove leading zero
        return "$day $month $year";
    } catch (Exception $e) {
        return "-";
    }
}

// Function to evaluate vision data
function evaluateVision($value, $min, $max, $type = "range", $naLabel = "ไม่เป็นไร", $normalLabel = "ปกติ", $abnormalLabel = "ผิดปกติ")
{
    if ($value === "N/A" || is_null($value) || $value === "") return $naLabel;

    // Check if value is numeric
    if (!is_numeric($value)) {
        return $abnormalLabel;
    }

    if ($type === "range") {
        $num = floatval($value);
        return ($num >= $min && $num <= $max) ? $normalLabel : $abnormalLabel;
    }

    if ($type === "select") {
        return $value === "3" ? $normalLabel : $abnormalLabel;
    }

    if ($type === "field") {
        return floatval($value) > 7 ? $normalLabel : $abnormalLabel;
    }

    return $abnormalLabel;
}

// Evaluation functions for each occupational group
function evaluateOfficeGroup($data)
{
    return [
        'binocularFar' => evaluateVision($data['binocular_vision_far'], null, null, "select"),
        'farBoth' => evaluateVision($data['far_vision_both'], 9, 12),
        'farRight' => evaluateVision($data['far_vision_right'], 8, 12),
        'farLeft' => evaluateVision($data['far_vision_left'], 8, 12),
        'stereoDepth' => evaluateVision($data['stereo_depth'], 5, 9),
        'color' => evaluateVision($data['color_discrimination'], 5, 8),
        'farVertical' => evaluateVision($data['far_vertical_phoria'], 3, 5),
        'farLateral' => evaluateVision($data['far_lateral_phoria'], 4, 13),
        'binocularNear' => evaluateVision($data['binocular_vision_near'], null, null, "select"),
        'nearBoth' => evaluateVision($data['near_vision_both'], 10, 12),
        'nearRight' => evaluateVision($data['near_vision_right'], 9, 12),
        'nearLeft' => evaluateVision($data['near_vision_left'], 9, 12),
        'nearVertical' => evaluateVision($data['near_vertical_phoria'], 3, 5),
        'nearLateral' => evaluateVision($data['near_lateral_phoria'], 4, 13),
        'visualField' => evaluateVision($data['visual_field'], null, null, "field"),
    ];
}

function evaluateQualityGroup($data)
{
    return [
        'binocularFar' => evaluateVision($data['binocular_vision_far'], null, null, "select"),
        'farBoth' => evaluateVision($data['far_vision_both'], 9, 12),
        'farRight' => evaluateVision($data['far_vision_right'], 8, 12),
        'farLeft' => evaluateVision($data['far_vision_left'], 8, 12),
        'stereoDepth' => evaluateVision($data['stereo_depth'], 5, 9),
        'color' => evaluateVision($data['color_discrimination'], 5, 8),
        'farVertical' => evaluateVision($data['far_vertical_phoria'], 3, 5),
        'farLateral' => evaluateVision($data['far_lateral_phoria'], 4, 13),
        'binocularNear' => evaluateVision($data['binocular_vision_near'], null, null, "select"),
        'nearBoth' => evaluateVision($data['near_vision_both'], 10, 12),
        'nearRight' => evaluateVision($data['near_vision_right'], 9, 12),
        'nearLeft' => evaluateVision($data['near_vision_left'], 9, 12),
        'nearVertical' => evaluateVision($data['near_vertical_phoria'], 3, 5),
        'nearLateral' => evaluateVision($data['near_lateral_phoria'], 4, 13),
        'visualField' => evaluateVision($data['visual_field'], null, null, "field"),
    ];
}

function evaluateDriverGroup($data)
{
    return [
        'binocularFar' => evaluateVision($data['binocular_vision_far'], null, null, "select"),
        'farBoth' => evaluateVision($data['far_vision_both'], 10, 12),
        'farRight' => evaluateVision($data['far_vision_right'], 9, 12),
        'farLeft' => evaluateVision($data['far_vision_left'], 9, 12),
        'stereoDepth' => evaluateVision($data['stereo_depth'], 5, 9),
        'color' => evaluateVision($data['color_discrimination'], 5, 8),
        'farVertical' => evaluateVision($data['far_vertical_phoria'], 3, 5),
        'farLateral' => evaluateVision($data['far_lateral_phoria'], 4, 13),
        'binocularNear' => evaluateVision($data['binocular_vision_near'], null, null, "select"),
        'nearBoth' => evaluateVision($data['near_vision_both'], 8, 12),
        'nearRight' => evaluateVision($data['near_vision_right'], 7, 12),
        'nearLeft' => evaluateVision($data['near_vision_left'], 7, 12),
        'nearVertical' => evaluateVision($data['near_vertical_phoria'], 3, 5),
        'nearLateral' => evaluateVision($data['near_lateral_phoria'], 4, 13),
        'visualField' => evaluateVision($data['visual_field'], null, null, "field"),
    ];
}

function evaluateMachineryGroup($data)
{
    return [
        'binocularFar' => evaluateVision($data['binocular_vision_far'], null, null, "select"),
        'farBoth' => evaluateVision($data['far_vision_both'], 9, 12),
        'farRight' => evaluateVision($data['far_vision_right'], 8, 12),
        'farLeft' => evaluateVision($data['far_vision_left'], 8, 12),
        'stereoDepth' => evaluateVision($data['stereo_depth'], 5, 9),
        'color' => evaluateVision($data['color_discrimination'], 5, 8),
        'farVertical' => evaluateVision($data['far_vertical_phoria'], 3, 5),
        'farLateral' => evaluateVision($data['far_lateral_phoria'], 4, 13),
        'binocularNear' => evaluateVision($data['binocular_vision_near'], null, null, "select"),
        'nearBoth' => evaluateVision($data['near_vision_both'], 9, 12),
        'nearRight' => evaluateVision($data['near_vision_right'], 8, 12),
        'nearLeft' => evaluateVision($data['near_vision_left'], 8, 12),
        'nearVertical' => evaluateVision($data['near_vertical_phoria'], 3, 5),
        'nearLateral' => evaluateVision($data['near_lateral_phoria'], 4, 13),
        'visualField' => evaluateVision($data['visual_field'], null, null, "field"),
    ];
}

function evaluateEngineerGroup($data)
{
    return [
        'binocularFar' => evaluateVision($data['binocular_vision_far'], null, null, "select"),
        'farBoth' => evaluateVision($data['far_vision_both'], 9, 12),
        'farRight' => evaluateVision($data['far_vision_right'], 8, 12),
        'farLeft' => evaluateVision($data['far_vision_left'], 8, 12),
        'stereoDepth' => evaluateVision($data['stereo_depth'], 5, 8),
        'color' => evaluateVision($data['color_discrimination'], 5, 8),
        'farVertical' => evaluateVision($data['far_vertical_phoria'], 3, 5),
        'farLateral' => evaluateVision($data['far_lateral_phoria'], 4, 13),
        'binocularNear' => evaluateVision($data['binocular_vision_near'], null, null, "select"),
        'nearBoth' => evaluateVision($data['near_vision_both'], 10, 12),
        'nearRight' => evaluateVision($data['near_vision_right'], 9, 12),
        'nearLeft' => evaluateVision($data['near_vision_left'], 9, 12),
        'nearVertical' => evaluateVision($data['near_vertical_phoria'], 3, 5),
        'nearLateral' => evaluateVision($data['near_lateral_phoria'], 4, 13),
        'visualField' => evaluateVision($data['visual_field'], null, null, "field"),
    ];
}

//งานที่ใช้ไม่ต้องใช้ทักษะ
function evaluateLaborGroup($data)
{
    return [
        'binocularFar' => evaluateVision($data['binocular_vision_far'], null, null, "select"),
        'farBoth' => evaluateVision($data['far_vision_both'], 9, 12),
        'farRight' => evaluateVision($data['far_vision_right'], 8, 12),
        'farLeft' => evaluateVision($data['far_vision_left'], 8, 12),
        'stereoDepth' => evaluateVision($data['stereo_depth'], 8, 12),
        'color' => evaluateVision($data['color_discrimination'], 5, 8),
        'farVertical' => evaluateVision($data['far_vertical_phoria'], 2, 6),
        'farLateral' => evaluateVision($data['far_lateral_phoria'], 4, 13),
        'binocularNear' => evaluateVision($data['binocular_vision_near'], null, null, "select"),
        'nearBoth' => evaluateVision($data['near_vision_both'], 8, 12),
        'nearRight' => evaluateVision($data['near_vision_right'], 9, 12),
        'nearLeft' => evaluateVision($data['near_vision_left'], 7, 12),
        'nearVertical' => evaluateVision($data['near_vertical_phoria'], 3, 5),
        'nearLateral' => evaluateVision($data['near_lateral_phoria'], 4, 13),
        'visualField' => evaluateVision($data['visual_field'], null, null, "field"),
    ];
}

// Create PDF class extending TCPDF
class PDF extends TCPDF
{
    public function Header()
    {
        $this->Ln(10); // Space from top

        // Title
        $this->SetFont('THSarabunNew', 'B', 18);
        $this->Cell(0, 8, 'ใบรายงานผลตรวจสมรรถภาพการมองเห็น', 0, 1, 'C');
        $this->Cell(0, 8, '(Vision Test Report) ปี 2568', 0, 1, 'C');

        // Hospital info (right-aligned)
        $this->SetFont('THSarabunNew', '', 16);
        $hospitalInfo = "โรงพยาบาลร้อยเอ็ด\n111 ถ.รณชัยชาญยุทธ\nอ.เมือง จ.ร้อยเอ็ด 45000\nโทร 043-518200";
        $this->SetXY(-80, 10);
        $this->MultiCell(70, 6, $hospitalInfo, 0, 'R');

        $this->Ln(5);
    }

    public function CalculateVisionSummary($row, $visionResults)
    {
        // ตรวจสอบว่ามีข้อมูลใน $visionResults หรือไม่
        $hasData = array_filter($visionResults, fn($result) => !empty($result) && $result !== 'ไม่เป็นไร');
        if (empty($hasData)) {
            $summary = "";
        } else {
            // นับจำนวนผลที่ "ผิดปกติ" หรือ "ไม่ชัดเจน"
            $abnormalCount = count(array_filter($visionResults, fn($result) => $result === 'ผิดปกติ' || $result === 'ไม่ชัดเจน'));
            if ($abnormalCount > 0) {
                $summary = "สามารถทำงานตำแหน่งนี้ โดยปรับแก้ไขตามคำแนะนำ";
            } else {
                $summary = "สามารถทำงานตำแหน่งนี้ได้ โดยไม่มีข้อห้าม";
            }
        }

        $recommendation = ($abnormalCount > 0) ?
            "▪ ควรตัดแว่นสายตาให้เหมาะสม และสวมใส่แว่นสายตาขณะทำงาน เพื่อการมองเห็นที่ชัดเจนขึ้น\n" .
            "▪ ควรพบจักษุแพทย์เพื่อตรวจหาสาเหตุและทำการแก้ไข\n" .
            "▪ ควรทำงานในตำแหน่งที่ไม่ต้องใช้ความสามารถในการจำแนกสีอย่างละเอียด\n" .
            "อื่นๆ........................................................................................................" :
            "";

        return [
            'summary' => $summary,
            'recommendation' => $recommendation
        ];
    }

    public function VisionReport($vision_records)
    {
        $this->SetFont('THSarabunNew', 'B', 16);
        $this->Ln(5);

        $groupEvaluators = [
            "งานสำนักงานและธุรการ" => 'evaluateOfficeGroup',
            "งานตรวจสอบและงานที่ท้าใกล้เครื่องจักร" => 'evaluateQualityGroup',
            "งานควบคุมอุปกรณ์ที่เคลื่อนที่" => 'evaluateDriverGroup',
            "งานควบคุมเครื่องจักร" => 'evaluateMachineryGroup',
            "งานช่างเครื่องและงานที่ใช้ทักษะสูง" => 'evaluateEngineerGroup',
            "งานที่ใช้ไม่ต้องใช้ทักษะ" => 'evaluateLaborGroup'
        ];

        foreach ($vision_records as $row) {
            // Validate required fields
            if (empty($row['first_name']) || empty($row['last_name'])) {
                $this->SetFont('THSarabunNew', 'B', 16);
                $this->Cell(0, 8, 'ข้อผิดพลาด: ข้อมูลชื่อ-นามสกุลไม่ครบถ้วน', 0, 1, 'L');
                continue;
            }   

            // Evaluate vision data
            $evaluateFunction = isset($groupEvaluators[$row['organization_name']]) ? $groupEvaluators[$row['organization_name']] : 'evaluateLaborGroup';
            $visionResults = call_user_func($evaluateFunction, $row);

            // Calculate summary and recommendations
            $summaryData = $this->CalculateVisionSummary($row, $visionResults);

            // Personal information
            $this->SetFont('THSarabunNew', 'B', 18);
            $this->Ln(10);
            $this->SetFont('THSarabunNew', '', 16);
            $this->Cell(60, 8, 'กลุ่มอาชีพ: ' . ($row['organization_name'] ?? '-'), 0);
            $this->Cell(60, 8, '                        แผนก: ' . ($row['department_name'] ?? '-'), 0);
            $this->Ln();
            $this->Cell(60, 8, "ชื่อ: " . $row['first_name'] . " " . $row['last_name'], 0);
            $this->Ln(10);
            $this->Cell(60, 8, 'วันที่ตรวจ: ' . formatThaiDate($row['exam_date'] ?? ''), 0);
            $this->Ln(6);

            // เอาไว้ไว้ก่อน1
            // $this->SetFont('THSarabunNew', 'B', 16);
            // $this->Cell(100, 8, 'รายการตรวจ', 1, 0, 'C');
            // $this->Cell(30, 8, 'ผลลัพธ์', 1, 0, 'C');
            // $this->Cell(30, 8, 'สถานะ', 1, 0, 'C');
            // $this->Ln();

            $visionFields = [
                '1.การมองวัตถุสองตาระยะไกล' => ['field' => 'binocular_vision_far', 'result' => 'binocularFar'],
                '2.การมองภาพระยะไกลด้วยสองตา' => ['field' => 'far_vision_both', 'result' => 'farBoth'],
                '3.การมองภาพระยะไกลด้วยตาขวา' => ['field' => 'far_vision_right', 'result' => 'farRight'],
                '4.การมองภาพระยะไกลด้วยตาซ้าย' => ['field' => 'far_vision_left', 'result' => 'farLeft'],
                '5.การมองภาพ 3 มิติ' => ['field' => 'stereo_depth', 'result' => 'stereoDepth'],
                '6.การมองจำแนกสี' => ['field' => 'color_discrimination', 'result' => 'color'],
                '7.ความเบี่ยงคลาดในแนวตั้งระยะไกล' => ['field' => 'far_vertical_phoria', 'result' => 'farVertical'],
                '8.ความเบี่ยงคลาดในแนวนอนระยะไกล' => ['field' => 'far_lateral_phoria', 'result' => 'farLateral'],
                '9.การมองภาพระยะใกล้ด้วยสองตา' => ['field' => 'near_vision_both', 'result' => 'nearBoth'],
                '10.การมองภาพระยะใกล้ด้วยตาขวา' => ['field' => 'near_vision_right', 'result' => 'nearRight'],
                '11.การมองภาพระยะใกล้ด้วยตาซ้าย' => ['field' => 'near_vision_left', 'result' => 'nearLeft'],
                '12.ความเบี่ยงคลาดในแนวตั้งระยะใกล้' => ['field' => 'near_vertical_phoria', 'result' => 'nearVertical'],
                '13.ความเบี่ยงคลาดในแนวนอนระยะใกล้' => ['field' => 'near_lateral_phoria', 'result' => 'nearLateral'],
                '14.ลานสายตา' => ['field' => 'visual_field', 'result' => 'visualField']
            ];

            //เอาไว้ไว้ก่อน2
            // $this->SetFont('THSarabunNew', '', 16);
            // foreach ($visionFields as $label => $info) {
            //     $this->Cell(100, 8, $label, 1, 0, 'L');
            //     $this->Cell(30, 8, $row[$info['field']] ?? '-', 1, 0, 'C');
            //     $this->Cell(30, 8, $visionResults[$info['result']] ?? '-', 1, 0, 'C');
            //     $this->Ln();
            // }

            // เเบบตามฟอร์ม
            $this->SetFont('THSarabunNew', 'B', 16);
            $this->Cell(100, 8, 'รายการตรวจ', 1, 0, 'C'); // ชื่อรายการตรวจ
            $this->Cell(30, 8, 'ผลลัพธ์', 1, 0, 'C'); // คอลัมน์ ผลลัพธ์
            $this->Cell(30, 8, 'ปกติ', 1, 0, 'C'); // คอลัมน์ "ปกติ"
            $this->Cell(30, 8, 'ผิดปกติ', 1, 0, 'C'); // คอลัมน์ "ผิดปกติ"
            $this->SetFont('wingdng2', 'B', 16);
            $this->Ln();
            
            $this->SetFont('THSarabunNew', '', 16);
            foreach ($visionFields as $label => $info) {
                $this->Cell(100, 8, $label, 1, 0, 'L'); // ชื่อรายการตรวจ
                $this->Cell(30, 8, $row[$info['field']] ?? '-', 1, 0, 'C'); //ช่องนี้เอาไว้สำหรับดึงข้อมูล ผลลัพธ์
            
                $status = $visionResults[$info['result']] ?? '-';
            
                // วาดกล่องเปล่าๆสำหรับปกติและผิดปกติ
                $this->Cell(30, 8, '', 1, 0, 'C'); // ช่องปกติ
                $this->Cell(30, 8, '', 1, 0, 'C'); // ช่องผิดปกติ
            
                // ตรวจสอบและวาดเครื่องหมายติ๊กถูก
                if ($status === 'ปกติ') {
                    $this->SetFont('DejaVuSans', '', 16); // เปลี่ยนฟอนต์เป็น DejaVu
                    $this->SetXY($this->GetX() - 60, $this->GetY()); // ย้ายตำแหน่ง cursor ไปที่ช่องปกติ
                    $this->Cell(30, 8, '✔', 0, 0, 'C'); // วาดเครื่องหมายติ๊กถูก
                    $this->SetFont('THSarabunNew', '', 16); // เปลี่ยนกลับเป็นฟอนต์เดิม
                } elseif ($status === 'ผิดปกติ') {
                    $this->SetFont('DejaVuSans', '', 16); // เปลี่ยนฟอนต์เป็น DejaVu
                    $this->SetXY($this->GetX() - 30, $this->GetY()); // ย้ายตำแหน่ง cursor ไปที่ช่องผิดปกติ
                    $this->Cell(30, 8, '✔', 0, 0, 'C'); // วาดเครื่องหมายติ๊กถูก
                    $this->SetFont('THSarabunNew', '', 16); // เปลี่ยนกลับเป็นฟอนต์เดิม
                }
                $this->Ln();
            }
            $this->Ln(5);

            // Summary and recommendations
            $this->SetFont('THSarabunNew', 'B', 16);
            $this->Cell(0, 8, 'สรุปผลตรวจ:', 0, 1);
            $this->SetFont('THSarabunNew', '', 16);
            $this->MultiCell(0, 8, $summaryData['summary'], 0, 'L');
            // แสดงคำแนะนำเฉพาะเมื่อมีผลผิดปกติ
            if (!empty($summaryData['recommendation'])) {
                $this->SetFont('THSarabunNew', 'B', 16);
                $this->Cell(0, 8, 'คำแนะนำ:', 0, 1);
                $this->SetFont('THSarabunNew', '', 16);
                $this->MultiCell(0, 8, $summaryData['recommendation'], 0, 'L');
            }

            $this->Ln(30);
            // Doctor's signature
            $this->SetFont('THSarabunNew', '', 16);
            $this->SetY(-65);
            $this->Ln(5);
            $this->Cell(0, 6, '                                                                                                                 แพทย์ผู้แปลผล', 0, 1);
            $this->Ln(5);
            $this->Cell(0, 8, '                                                                                                      ...................................................', 0, 1);
            $this->Ln(3);
            $this->Cell(0, 8, '                                                                                                       (พญ.นภัฐมณ มโนรัตน์ ว.42781)', 0, 1);
            $this->Cell(0, 6, '                                                                                                             แพทย์อาชีวเวชศาสตร์', 0, 1);
            $this->Ln(5);

            // // Check for new page
            // if ($this->GetY() > 250) {
            //     $this->AddPage();
            // }
        }
    }
}

// Prevent output before PDF creation
ob_start();

// Fetch data from database
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $sql = "SELECT er.*, o.name AS organization_name, d.name AS department_name
            FROM eye_records er
            LEFT JOIN organizations o ON er.organization_id = o.id
            LEFT JOIN departments d ON er.department_id = d.id
            WHERE er.id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT er.*, o.name AS organization_name, d.name AS department_name
            FROM eye_records er
            LEFT JOIN organizations o ON er.organization_id = o.id
            LEFT JOIN departments d ON er.department_id = d.id
            ORDER BY er.id DESC";
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
}

$eye_records = [];
while ($row = $result->fetch_assoc()) {
    $eye_records[] = $row;
}

if (empty($eye_records)) {
    die("ไม่มีข้อมูลสำหรับสร้าง PDF: ไม่พบระเบียนที่ตรงกับ ID หรือไม่มีข้อมูลในตาราง eye_records");
}
// Create PDF
$pdf = new PDF();
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();
$pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
$pdf->AddFont('THSarabunNew', 'B', 'thsarabunnewb.php');
$pdf->VisionReport($eye_records);

// Clear buffer and output PDF
if (ob_get_length()) {
    ob_clean();
}
ob_end_clean();
$pdf->Output('vision_report.pdf', 'D');
$conn->close();
exit();
