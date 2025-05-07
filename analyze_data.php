<!-- filepath: c:\xampp\htdocs\dashboard\audio\analyze_data.php -->
<?php
require_once 'config.php'; // เชื่อมต่อฐานข้อมูล

// ฟังก์ชันเพื่อดึงข้อมูลจากฐานข้อมูล
function fetchData($query)
{
    global $conn;
    $result = $conn->query($query);
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// 1. ข้อมูลสถิติการได้ยินตามแผนก
$hearingByDepartmentQuery = "
    SELECT 
        department,
        COUNT(*) AS total_employees,
        AVG(right_500 + right_1000 + right_2000 + right_3000 + right_4000 + right_6000 + right_8000) / 7 AS avg_right_hearing,
        AVG(left_500 + left_1000 + left_2000 + left_3000 + left_4000 + left_6000 + left_8000) / 7 AS avg_left_hearing
    FROM 
        audio_records
    GROUP BY 
        department
    ORDER BY 
        department
";
$hearingByDepartmentData = fetchData($hearingByDepartmentQuery);

// 2. ข้อมูล BMI ตามแผนก
$bmiByDepartmentQuery = "
    SELECT 
        department,
        AVG(bmi) AS avg_bmi,
        COUNT(CASE WHEN bmi_category = 'น้ำหนักต่ำกว่าเกณฑ์' THEN 1 END) AS underweight,
        COUNT(CASE WHEN bmi_category = 'สมส่วน' THEN 1 END) AS normal_weight,
        COUNT(CASE WHEN bmi_category = 'น้ำหนักเกิน' THEN 1 END) AS overweight,
        COUNT(CASE WHEN bmi_category = 'อ้วนระดับ 1' THEN 1 END) AS obese_level1,
        COUNT(CASE WHEN bmi_category = 'อ้วนระดับ 2' THEN 1 END) AS obese_level2
    FROM 
        audio_records
    GROUP BY 
        department
    ORDER BY 
        department
";
$bmiByDepartmentData = fetchData($bmiByDepartmentQuery);

// 3. ข้อมูลความดันโลหิตตามแผนก
$bpByDepartmentQuery = "
    SELECT 
        department,
        AVG(sbp) AS avg_sbp,
        AVG(dbp) AS avg_dbp,
        COUNT(CASE WHEN bloodPressure_category = 'ปกติ' THEN 1 END) AS normal_bp,
        COUNT(CASE WHEN bloodPressure_category = 'ระยะก่อนความดันโลหิตสูง' THEN 1 END) AS pre_hypertension,
        COUNT(CASE WHEN bloodPressure_category = 'ความดันโลหิตสูง' THEN 1 END) AS hypertension
    FROM 
        audio_records
    GROUP BY 
        department
    ORDER BY 
        department
";
$bpByDepartmentData = fetchData($bpByDepartmentQuery);

// 4. ข้อมูลเปรียบเทียบความสูญเสียการได้ยินระหว่างเพศ
$hearingByGenderQuery = "
    SELECT 
        gender,
        COUNT(*) AS total,
        AVG(right_500) AS avg_right_500,
        AVG(right_1000) AS avg_right_1000,
        AVG(right_2000) AS avg_right_2000,
        AVG(right_4000) AS avg_right_4000,
        AVG(right_8000) AS avg_right_8000,
        AVG(left_500) AS avg_left_500,
        AVG(left_1000) AS avg_left_1000,
        AVG(left_2000) AS avg_left_2000,
        AVG(left_4000) AS avg_left_4000,
        AVG(left_8000) AS avg_left_8000
    FROM 
        audio_records
    GROUP BY 
        gender
";
$hearingByGenderData = fetchData($hearingByGenderQuery);

// 5. ข้อมูลความสัมพันธ์ระหว่าง BMI กับความดันโลหิต
$bmiAndBpQuery = "
    SELECT 
        bmi_category,
        COUNT(*) AS total,
        AVG(sbp) AS avg_sbp,
        AVG(dbp) AS avg_dbp,
        COUNT(CASE WHEN bloodPressure_category = 'ปกติ' THEN 1 END) AS normal_bp,
        COUNT(CASE WHEN bloodPressure_category = 'ระยะก่อนความดันโลหิตสูง' THEN 1 END) AS pre_hypertension,
        COUNT(CASE WHEN bloodPressure_category = 'ความดันโลหิตสูง' THEN 1 END) AS hypertension
    FROM 
        audio_records
    GROUP BY 
        bmi_category
    ORDER BY 
        CASE
            WHEN bmi_category = 'น้ำหนักต่ำกว่าเกณฑ์' THEN 1
            WHEN bmi_category = 'สมส่วน' THEN 2
            WHEN bmi_category = 'น้ำหนักเกิน' THEN 3
            WHEN bmi_category = 'อ้วนระดับ 1' THEN 4
            WHEN bmi_category = 'อ้วนระดับ 2' THEN 5
            ELSE 6
        END
";
$bmiAndBpData = fetchData($bmiAndBpQuery);

// 6. ข้อมูลตารางเปรียบเทียบสายตาตามประเภทงาน
$eyesByOrganizationQuery = "
    SELECT 
        o.name AS organization_name,
        COUNT(e.id) AS total_employees,
        AVG(e.far_vision_right) AS avg_far_vision_right,
        AVG(e.far_vision_left) AS avg_far_vision_left,
        AVG(e.near_vision_right) AS avg_near_vision_right,
        AVG(e.near_vision_left) AS avg_near_vision_left,
        AVG(e.color_discrimination) AS avg_color_discrimination
    FROM 
        eye_records e
    JOIN 
        organizations o ON e.organization_id = o.id
    GROUP BY 
        o.id
    ORDER BY 
        o.name
";
$eyesByOrganizationData = fetchData($eyesByOrganizationQuery);

// 7. ข้อมูลอายุเฉลี่ยและความดันเฉลี่ยในแต่ละโรงพยาบาล
$avgAgeAndBpByEstablishmentQuery = "
    SELECT 
        e.name AS establishment_name,
        COUNT(a.id) AS total_employees,
        AVG(a.age) AS avg_age,
        AVG(a.sbp) AS avg_sbp,
        AVG(a.dbp) AS avg_dbp
    FROM 
        audio_records a
    JOIN 
        establishment e ON a.establishment_id = e.id
    GROUP BY 
        e.id
    ORDER BY 
        e.name
";
$avgAgeAndBpByEstablishmentData = fetchData($avgAgeAndBpByEstablishmentQuery);

// 8. ข้อมูลการสูญเสียการได้ยินตามช่วงอายุ
$hearingByAgeGroupQuery = "
    SELECT 
        CASE
            WHEN age < 30 THEN 'น้อยกว่า 30 ปี'
            WHEN age BETWEEN 30 AND 40 THEN '30-40 ปี'
            WHEN age > 40 THEN 'มากกว่า 40 ปี'
        END AS age_group,
        COUNT(*) AS total,
        AVG(right_500) AS avg_right_500,
        AVG(right_1000) AS avg_right_1000,
        AVG(right_2000) AS avg_right_2000,
        AVG(right_4000) AS avg_right_4000,
        AVG(right_8000) AS avg_right_8000,
        AVG(left_500) AS avg_left_500,
        AVG(left_1000) AS avg_left_1000,
        AVG(left_2000) AS avg_left_2000,
        AVG(left_4000) AS avg_left_4000,
        AVG(left_8000) AS avg_left_8000
    FROM 
        audio_records
    GROUP BY 
        age_group
    ORDER BY 
        CASE
            WHEN age_group = 'น้อยกว่า 30 ปี' THEN 1
            WHEN age_group = '30-40 ปี' THEN 2
            WHEN age_group = 'มากกว่า 40 ปี' THEN 3
        END
";
$hearingByAgeGroupData = fetchData($hearingByAgeGroupQuery);

// 9. ข้อมูลเปรียบเทียบการได้ยินระหว่างหูซ้ายและหูขวา
$hearingRightVsLeftQuery = "
    SELECT 
        AVG(right_500) AS avg_right_500,
        AVG(right_1000) AS avg_right_1000,
        AVG(right_2000) AS avg_right_2000,
        AVG(right_3000) AS avg_right_3000,
        AVG(right_4000) AS avg_right_4000,
        AVG(right_6000) AS avg_right_6000,
        AVG(right_8000) AS avg_right_8000,
        AVG(left_500) AS avg_left_500,
        AVG(left_1000) AS avg_left_1000,
        AVG(left_2000) AS avg_left_2000,
        AVG(left_3000) AS avg_left_3000,
        AVG(left_4000) AS avg_left_4000,
        AVG(left_6000) AS avg_left_6000,
        AVG(left_8000) AS avg_left_8000
    FROM 
        audio_records
";
$hearingRightVsLeftData = fetchData($hearingRightVsLeftQuery);

// แปลงข้อมูลให้อยู่ในรูปแบบที่ใช้กับ Chart.js ได้
function prepareChartData($data, $labelKey, $valueKeys)
{
    $labels = [];
    $datasets = [];

    // สร้าง datasets สำหรับแต่ละ valueKey
    foreach ($valueKeys as $key => $properties) {
        $datasets[] = [
            'label' => $properties['label'],
            'data' => [],
            'backgroundColor' => $properties['backgroundColor'],
            'borderColor' => $properties['borderColor'],
            'borderWidth' => 1
        ];
    }

    // เพิ่มข้อมูลเข้าไปใน datasets
    foreach ($data as $item) {
        $labels[] = $item[$labelKey];

        $i = 0;
        foreach ($valueKeys as $key => $properties) {
            $datasets[$i]['data'][] = $item[$key];
            $i++;
        }
    }

    return [
        'labels' => $labels,
        'datasets' => $datasets
    ];
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>วิเคราะห์ข้อมูล</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .analysis-section {
            margin-bottom: 40px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .chart-container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            height: 400px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
            border-radius: 5px 5px 0 0;
        }

        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
        }

        .tab button:hover {
            background-color: #ddd;
        }

        .tab button.active {
            background-color: #ccc;
        }

        .tabcontent {
            display: none;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }

        .tab select {
            color: black;
            background-color: #f1f1f1;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            font-size: 16px;
            width: 100%;
            max-width: 300px;
            cursor: pointer;
        }

        .tab select:focus {
            outline: none;
            border-color: #666;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1>วิเคราะห์ข้อมูล</h1>
        <p>ระบบวิเคราะห์ข้อมูลสุขภาพการได้ยิน สายตา และข้อมูลสุขภาพอื่นๆ</p>

        <div class="tab">
            <select onchange="openTab(event, this.value)">
                <option value="tab1">การได้ยินตามแผนก</option>
                <option value="tab2">BMI ตามแผนก</option>
                <option value="tab3">ความดันโลหิตตามแผนก</option>
                <option value="tab4">การได้ยินตามเพศ</option>
                <option value="tab5">BMI และความดันโลหิต</option>
                <option value="tab6">สายตาตามประเภทงาน</option>
                <option value="tab7">ข้อมูลตามโรงพยาบาล</option>
                <option value="tab8">การได้ยินตามอายุ</option>
                <option value="tab9">หูซ้ายและหูขวา</option>
            </select>
        </div>

        <!-- Tab 1: การได้ยินตามแผนก -->
        <div id="tab1" class="tabcontent" style="display: block;">
            <div class="analysis-section">
                <h2>การวิเคราะห์การได้ยินตามแผนก</h2>
                <p>กราฟนี้แสดงค่าเฉลี่ยการได้ยินของหูซ้ายและหูขวาในแต่ละแผนก</p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>แผนก</th>
                                <th>จำนวนพนักงาน</th>
                                <th>ค่าเฉลี่ยการได้ยินหูขวา (dB)</th>
                                <th>ค่าเฉลี่ยการได้ยินหูซ้าย (dB)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hearingByDepartmentData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo $row['total_employees']; ?></td>
                                    <td><?php echo round($row['avg_right_hearing'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_hearing'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-container">
                    <canvas id="hearingByDepartmentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tab 2: BMI ตามแผนก -->
        <div id="tab2" class="tabcontent">
            <div class="analysis-section">
                <h2>การวิเคราะห์ BMI ตามแผนก</h2>
                <p>กราฟนี้แสดงการกระจายของ BMI ในแต่ละแผนก</p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>แผนก</th>
                                <th>BMI เฉลี่ย</th>
                                <th>น้ำหนักต่ำกว่าเกณฑ์</th>
                                <th>สมส่วน</th>
                                <th>น้ำหนักเกิน</th>
                                <th>อ้วนระดับ 1</th>
                                <th>อ้วนระดับ 2</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bmiByDepartmentData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo round($row['avg_bmi'], 2); ?></td>
                                    <td><?php echo $row['underweight']; ?></td>
                                    <td><?php echo $row['normal_weight']; ?></td>
                                    <td><?php echo $row['overweight']; ?></td>
                                    <td><?php echo $row['obese_level1']; ?></td>
                                    <td><?php echo $row['obese_level2']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-container">
                    <canvas id="bmiByDepartmentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tab 3: ความดันโลหิตตามแผนก -->
        <div id="tab3" class="tabcontent">
            <div class="analysis-section">
                <h2>การวิเคราะห์ความดันโลหิตตามแผนก</h2>
                <p>กราฟนี้แสดงความดันโลหิตเฉลี่ยในแต่ละแผนก</p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>แผนก</th>
                                <th>SBP เฉลี่ย</th>
                                <th>DBP เฉลี่ย</th>
                                <th>ความดันปกติ</th>
                                <th>ระยะก่อนความดันโลหิตสูง</th>
                                <th>ความดันโลหิตสูง</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bpByDepartmentData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo round($row['avg_sbp'], 2); ?></td>
                                    <td><?php echo round($row['avg_dbp'], 2); ?></td>
                                    <td><?php echo $row['normal_bp']; ?></td>
                                    <td><?php echo $row['pre_hypertension']; ?></td>
                                    <td><?php echo $row['hypertension']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-container">
                    <canvas id="bpByDepartmentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tab 4: การได้ยินตามเพศ -->
        <div id="tab4" class="tabcontent">
            <div class="analysis-section">
                <h2>การวิเคราะห์การได้ยินแยกตามเพศ</h2>
                <p>กราฟนี้แสดงความแตกต่างของการได้ยินระหว่างเพศชายและเพศหญิง</p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>เพศ</th>
                                <th>จำนวน</th>
                                <th>500 Hz (R)</th>
                                <th>1000 Hz (R)</th>
                                <th>2000 Hz (R)</th>
                                <th>4000 Hz (R)</th>
                                <th>8000 Hz (R)</th>
                                <th>500 Hz (L)</th>
                                <th>1000 Hz (L)</th>
                                <th>2000 Hz (L)</th>
                                <th>4000 Hz (L)</th>
                                <th>8000 Hz (L)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hearingByGenderData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                    <td><?php echo $row['total']; ?></td>
                                    <td><?php echo round($row['avg_right_500'], 2); ?></td>
                                    <td><?php echo round($row['avg_right_1000'], 2); ?></td>
                                    <td><?php echo round($row['avg_right_2000'], 2); ?></td>
                                    <td><?php echo round($row['avg_right_4000'], 2); ?></td>
                                    <td><?php echo round($row['avg_right_8000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_500'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_1000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_2000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_4000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_8000'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-container">
                    <canvas id="hearingByGenderChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tab 5: BMI และความดันโลหิต -->
        <div id="tab5" class="tabcontent">
            <div class="analysis-section">
                <h2>ความสัมพันธ์ระหว่าง BMI และความดันโลหิต</h2>
                <p>กราฟนี้แสดงความสัมพันธ์ระหว่างระดับ BMI และความดันโลหิต</p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>หมวดหมู่ BMI</th>
                                <th>จำนวน</th>
                                <th>SBP เฉลี่ย</th>
                                <th>DBP เฉลี่ย</th>
                                <th>ความดันปกติ</th>
                                <th>ระยะก่อนความดันโลหิตสูง</th>
                                <th>ความดันโลหิตสูง</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bmiAndBpData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['bmi_category']); ?></td>
                                    <td><?php echo $row['total']; ?></td>
                                    <td><?php echo round($row['avg_sbp'], 2); ?></td>
                                    <td><?php echo round($row['avg_dbp'], 2); ?></td>
                                    <td><?php echo $row['normal_bp']; ?></td>
                                    <td><?php echo $row['pre_hypertension']; ?></td>
                                    <td><?php echo $row['hypertension']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-container">
                    <canvas id="bmiAndBpChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tab 6: สายตาตามประเภทงาน -->
        <div id="tab6" class="tabcontent">
            <div class="analysis-section">
                <h2>การวิเคราะห์สายตาตามประเภทงาน</h2>
                <p>กราฟนี้แสดงความสัมพันธ์ระหว่างประเภทงานและสายตา</p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ประเภทงาน</th>
                                <th>จำนวน</th>
                                <th>สายตาไกลเฉลี่ย (ขวา)</th>
                                <th>สายตาไกลเฉลี่ย (ซ้าย)</th>
                                <th>สายตาใกล้เฉลี่ย (ขวา)</th>
                                <th>สายตาใกล้เฉลี่ย (ซ้าย)</th>
                                <th>การแยกสีเฉลี่ย</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eyesByOrganizationData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['organization_name']); ?></td>
                                    <td><?php echo $row['total_employees']; ?></td>
                                    <td><?php echo round($row['avg_far_vision_right'], 2); ?></td>
                                    <td><?php echo round($row['avg_far_vision_left'], 2); ?></td>
                                    <td><?php echo round($row['avg_near_vision_right'], 2); ?></td>
                                    <td><?php echo round($row['avg_near_vision_left'], 2); ?></td>
                                    <td><?php echo round($row['avg_color_discrimination'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-container">
                    <canvas id="eyesByOrganizationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tab 7: ข้อมูลตามโรงพยาบาล -->
        <div id="tab7" class="tabcontent">
            <div class="analysis-section">
                <h2>การวิเคราะห์ข้อมูลตามโรงพยาบาล</h2>
                <p>ตารางนี้แสดงข้อมูลอายุเฉลี่ยและความดันโลหิตเฉลี่ยในแต่ละโรงพยาบาล</p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>โรงพยาบาล</th>
                                <th>จำนวนพนักงาน</th>
                                <th>อายุเฉลี่ย (ปี)</th>
                                <th>SBP เฉลี่ย (mmHg)</th>
                                <th>DBP เฉลี่ย (mmHg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($avgAgeAndBpByEstablishmentData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['establishment_name']); ?></td>
                                    <td><?php echo $row['total_employees']; ?></td>
                                    <td><?php echo round($row['avg_age'], 2); ?></td>
                                    <td><?php echo round($row['avg_sbp'], 2); ?></td>
                                    <td><?php echo round($row['avg_dbp'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-container">
                    <canvas id="avgAgeAndBpByEstablishmentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tab 8: การได้ยินตามอายุ -->
        <div id="tab8" class="tabcontent">
            <div class="analysis-section">
                <h2>การวิเคราะห์การได้ยินตามช่วงอายุ</h2>
                <p>กราฟนี้แสดงค่าเฉลี่ยการได้ยินในแต่ละช่วงอายุ</p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ช่วงอายุ</th>
                                <th>จำนวน</th>
                                <th>500 Hz (R)</th>
                                <th>1000 Hz (R)</th>
                                <th>2000 Hz (R)</th>
                                <th>4000 Hz (R)</th>
                                <th>8000 Hz (R)</th>
                                <th>500 Hz (L)</th>
                                <th>1000 Hz (L)</th>
                                <th>2000 Hz (L)</th>
                                <th>4000 Hz (L)</th>
                                <th>8000 Hz (L)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hearingByAgeGroupData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['age_group']); ?></td>
                                    <td><?php echo $row['total']; ?></td>
                                    <td><?php echo round($row['avg_right_500'], 2); ?></td>
                                    <td><?php echo round($row['avg_right_1000'], 2); ?></td>
                                    <td><?php echo round($row['avg_right_2000'], 2); ?></td>
                                    <td><?php echo round($row['avg_right_4000'], 2); ?></td>
                                    <td><?php echo round($row['avg_right_8000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_500'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_1000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_2000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_4000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_8000'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-container">
                    <canvas id="hearingByAgeGroupChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tab 9: หูซ้ายและหูขวา -->
        <div id="tab9" class="tabcontent">
            <div class="analysis-section">
                <h2>การเปรียบเทียบการได้ยินระหว่างหูซ้ายและหูขวา</h2>
                <p>กราฟนี้แสดงค่าเฉลี่ยการได้ยินของหูซ้ายและหูขวาที่ความถี่ต่างๆ</p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ความถี่ (Hz)</th>
                                <th>หูขวา (dB)</th>
                                <th>หูซ้าย (dB)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($hearingRightVsLeftData)): ?>
                                <?php $row = $hearingRightVsLeftData[0]; ?>
                                <tr>
                                    <td>500</td>
                                    <td><?php echo round($row['avg_right_500'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_500'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>1000</td>
                                    <td><?php echo round($row['avg_right_1000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_1000'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>2000</td>
                                    <td><?php echo round($row['avg_right_2000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_2000'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>3000</td>
                                    <td><?php echo round($row['avg_right_3000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_3000'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>4000</td>
                                    <td><?php echo round($row['avg_right_4000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_4000'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>6000</td>
                                    <td><?php echo round($row['avg_right_6000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_6000'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>8000</td>
                                    <td><?php echo round($row['avg_right_8000'], 2); ?></td>
                                    <td><?php echo round($row['avg_left_8000'], 2); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-container">
                    <canvas id="hearingRightVsLeftChart"></canvas>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // ฟังก์ชันจัดการการสลับ Tab
            function openTab(evt, tabName) {
                var i, tabcontent;
                tabcontent = document.getElementsByClassName("tabcontent");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }
                document.getElementById(tabName).style.display = "block";
            }

            // ข้อมูลสำหรับกราฟจาก PHP
            <?php
            // Tab 1: การได้ยินตามแผนก
            $hearingByDepartmentChartData = prepareChartData($hearingByDepartmentData, 'department', [
                'avg_right_hearing' => [
                    'label' => 'หูขวา (dB)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)'
                ],
                'avg_left_hearing' => [
                    'label' => 'หูซ้าย (dB)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)'
                ]
            ]);
            echo "const hearingByDepartmentChartData = " . json_encode($hearingByDepartmentChartData) . ";\n";

            // Tab 2: BMI ตามแผนก
            $bmiByDepartmentChartData = prepareChartData($bmiByDepartmentData, 'department', [
                'underweight' => [
                    'label' => 'น้ำหนักต่ำกว่าเกณฑ์',
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                    'borderColor' => 'rgba(255, 206, 86, 1)'
                ],
                'normal_weight' => [
                    'label' => 'สมส่วน',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)'
                ],
                'overweight' => [
                    'label' => 'น้ำหนักเกิน',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)'
                ],
                'obese_level1' => [
                    'label' => 'อ้วนระดับ 1',
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                    'borderColor' => 'rgba(153, 102, 255, 1)'
                ],
                'obese_level2' => [
                    'label' => 'อ้วนระดับ 2',
                    'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                    'borderColor' => 'rgba(255, 159, 64, 1)'
                ]
            ]);
            echo "const bmiByDepartmentChartData = " . json_encode($bmiByDepartmentChartData) . ";\n";

            // Tab 3: ความดันโลหิตตามแผนก
            $bpByDepartmentChartData = prepareChartData($bpByDepartmentData, 'department', [
                'avg_sbp' => [
                    'label' => 'SBP เฉลี่ย (mmHg)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)'
                ],
                'avg_dbp' => [
                    'label' => 'DBP เฉลี่ย (mmHg)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)'
                ]
            ]);
            echo "const bpByDepartmentChartData = " . json_encode($bpByDepartmentChartData) . ";\n";

            // Tab 4: การได้ยินตามเพศ
            $hearingByGenderChartData = prepareChartData($hearingByGenderData, 'gender', [
                'avg_right_500' => [
                    'label' => '500 Hz หูขวา (dB)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)'
                ],
                'avg_left_500' => [
                    'label' => '500 Hz หูซ้าย (dB)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)'
                ]
            ]);
            echo "const hearingByGenderChartData = " . json_encode($hearingByGenderChartData) . ";\n";

            // Tab 5: BMI และความดันโลหิต
            $bmiAndBpChartData = prepareChartData($bmiAndBpData, 'bmi_category', [
                'avg_sbp' => [
                    'label' => 'SBP เฉลี่ย (mmHg)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)'
                ],
                'avg_dbp' => [
                    'label' => 'DBP เฉลี่ย (mmHg)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)'
                ]
            ]);
            echo "const bmiAndBpChartData = " . json_encode($bmiAndBpChartData) . ";\n";

            // Tab 6: สายตาตามประเภทงาน
            $eyesByOrganizationChartData = prepareChartData($eyesByOrganizationData, 'organization_name', [
                'avg_far_vision_right' => [
                    'label' => 'สายตาไกล (ขวา)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)'
                ],
                'avg_far_vision_left' => [
                    'label' => 'สายตาไกล (ซ้าย)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)'
                ],
                'avg_near_vision_right' => [
                    'label' => 'สายตาใกล้ (ขวา)',
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                    'borderColor' => 'rgba(255, 206, 86, 1)'
                ],
                'avg_near_vision_left' => [
                    'label' => 'สายตาใกล้ (ซ้าย)',
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                    'borderColor' => 'rgba(153, 102, 255, 1)'
                ]
            ]);
            echo "const eyesByOrganizationChartData = " . json_encode($eyesByOrganizationChartData) . ";\n";

            // Tab 7: ข้อมูลตามโรงพยาบาล
            $avgAgeAndBpByEstablishmentChartData = prepareChartData($avgAgeAndBpByEstablishmentData, 'establishment_name', [
                'avg_age' => [
                    'label' => 'อายุเฉลี่ย (ปี)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)'
                ],
                'avg_sbp' => [
                    'label' => 'SBP เฉลี่ย (mmHg)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)'
                ],
                'avg_dbp' => [
                    'label' => 'DBP เฉลี่ย (mmHg)',
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                    'borderColor' => 'rgba(255, 206, 86, 1)'
                ]
            ]);
            echo "const avgAgeAndBpByEstablishmentChartData = " . json_encode($avgAgeAndBpByEstablishmentChartData) . ";\n";

            // Tab 8: การได้ยินตามช่วงอายุ
            $hearingByAgeGroupChartData = prepareChartData($hearingByAgeGroupData, 'age_group', [
                'avg_right_500' => [
                    'label' => '500 Hz หูขวา (dB)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)'
                ],
                'avg_left_500' => [
                    'label' => '500 Hz หูซ้าย (dB)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)'
                ]
            ]);
            echo "const hearingByAgeGroupChartData = " . json_encode($hearingByAgeGroupChartData) . ";\n";

            // Tab 9: หูซ้ายและหูขวา
            $hearingRightVsLeftChartData = [
                'labels' => ['500 Hz', '1000 Hz', '2000 Hz', '3000 Hz', '4000 Hz', '6000 Hz', '8000 Hz'],
                'datasets' => [
                    [
                        'label' => 'หูขวา (dB)',
                        'data' => !empty($hearingRightVsLeftData) ? [
                            $hearingRightVsLeftData[0]['avg_right_500'],
                            $hearingRightVsLeftData[0]['avg_right_1000'],
                            $hearingRightVsLeftData[0]['avg_right_2000'],
                            $hearingRightVsLeftData[0]['avg_right_3000'],
                            $hearingRightVsLeftData[0]['avg_right_4000'],
                            $hearingRightVsLeftData[0]['avg_right_6000'],
                            $hearingRightVsLeftData[0]['avg_right_8000']
                        ] : [],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'หูซ้าย (dB)',
                        'data' => !empty($hearingRightVsLeftData) ? [
                            $hearingRightVsLeftData[0]['avg_left_500'],
                            $hearingRightVsLeftData[0]['avg_left_1000'],
                            $hearingRightVsLeftData[0]['avg_left_2000'],
                            $hearingRightVsLeftData[0]['avg_left_3000'],
                            $hearingRightVsLeftData[0]['avg_left_4000'],
                            $hearingRightVsLeftData[0]['avg_left_6000'],
                            $hearingRightVsLeftData[0]['avg_left_8000']
                        ] : [],
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ];
            echo "const hearingRightVsLeftChartData = " . json_encode($hearingRightVsLeftChartData) . ";\n";
            ?>

            // สร้างกราฟด้วย Chart.js
            // Tab 1: การได้ยินตามแผนก
            const hearingByDepartmentCtx = document.getElementById('hearingByDepartmentChart').getContext('2d');
            new Chart(hearingByDepartmentCtx, {
                type: 'bar',
                data: hearingByDepartmentChartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'ระดับการได้ยิน (dB)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'แผนก'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            // Tab 2: BMI ตามแผนก
            const bmiByDepartmentCtx = document.getElementById('bmiByDepartmentChart').getContext('2d');
            new Chart(bmiByDepartmentCtx, {
                type: 'bar',
                data: bmiByDepartmentChartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'จำนวนพนักงาน'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'แผนก'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            // Tab 3: ความดันโลหิตตามแผนก
            const bpByDepartmentCtx = document.getElementById('bpByDepartmentChart').getContext('2d');
            new Chart(bpByDepartmentCtx, {
                type: 'bar',
                data: bpByDepartmentChartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'ความดันโลหิต (mmHg)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'แผนก'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            // Tab 4: การได้ยินตามเพศ
            const hearingByGenderCtx = document.getElementById('hearingByGenderChart').getContext('2d');
            new Chart(hearingByGenderCtx, {
                type: 'bar',
                data: hearingByGenderChartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'ระดับการได้ยิน (dB)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'เพศ'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            // Tab 5: BMI และความดันโลหิต
            const bmiAndBpCtx = document.getElementById('bmiAndBpChart').getContext('2d');
            new Chart(bmiAndBpCtx, {
                type: 'bar',
                data: bmiAndBpChartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'ความดันโลหิต (mmHg)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'หมวดหมู่ BMI'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            // Tab 6: สายตาตามประเภทงาน
            const eyesByOrganizationCtx = document.getElementById('eyesByOrganizationChart').getContext('2d');
            new Chart(eyesByOrganizationCtx, {
                type: 'bar',
                data: eyesByOrganizationChartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'ค่าสายตา'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'ประเภทงาน'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            // Tab 7: ข้อมูลตามโรงพยาบาล
            const avgAgeAndBpByEstablishmentCtx = document.getElementById('avgAgeAndBpByEstablishmentChart').getContext('2d');
            new Chart(avgAgeAndBpByEstablishmentCtx, {
                type: 'bar',
                data: avgAgeAndBpByEstablishmentChartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'ค่าเฉลี่ย'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'โรงพยาบาล'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            // Tab 8: การได้ยินตามช่วงอายุ
            const hearingByAgeGroupCtx = document.getElementById('hearingByAgeGroupChart').getContext('2d');
            new Chart(hearingByAgeGroupCtx, {
                type: 'bar',
                data: hearingByAgeGroupChartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'ระดับการได้ยิน (dB)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'ช่วงอายุ'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            // Tab 9: หูซ้ายและหูขวา
            const hearingRightVsLeftCtx = document.getElementById('hearingRightVsLeftChart').getContext('2d');
            new Chart(hearingRightVsLeftCtx, {
                type: 'line',
                data: hearingRightVsLeftChartData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'ระดับการได้ยิน (dB)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'ความถี่ (Hz)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });
        </script>
</body>

</html>