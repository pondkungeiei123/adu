document.addEventListener('DOMContentLoaded', function () {
    // ฟังก์ชันสำหรับหน้า index.php หรือหน้าที่มีฟอร์ม
    const weightField = document.getElementById('weight');
    const heightField = document.getElementById('height');
    const bmiField = document.getElementById('bmi');
    const bmiCategoryField = document.getElementById('bmi_category');
    const heartRateField = document.getElementById('heartRate');
    const form = document.getElementById('dataForm');
    const tableBody = document.getElementById('dataTableBody');
    let dataList = [];

    // ฟังก์ชันคำนวณ BMI
    window.calculateBMI = function (weight, height) {
        if (weight && height) {
            const heightInMeters = height / 100;
            const bmi = (weight / (heightInMeters * heightInMeters)).toFixed(2);
            let category = '';
            if (bmi < 18.5) category = 'น้ำหนักต่ำกว่าเกณฑ์';
            else if (bmi >= 18.5 && bmi < 23) category = 'สมส่วน';
            else if (bmi >= 23 && bmi < 25) category = 'น้ำหนักเกิน';
            else if (bmi >= 25 && bmi < 30) category = 'อ้วนระดับ 1';
            else category = 'อ้วนระดับ 2';
            return { bmi, category };
        }
        return { bmi: '', category: '' };
    };

    // ฟังก์ชันคำนวณหมวดหมู่ความดันโลหิต
    window.calculateBloodPressureCategory = function (sbp, dbp) {
        if (sbp < 120 && dbp < 80) return 'ปกติ';
        if (sbp >= 120 && sbp <= 129 && dbp < 80) return 'สูงกว่าปกติ';
        if ((sbp >= 130 && sbp <= 139) || (dbp >= 80 && dbp <= 89)) return 'ความดันโลหิตสูงระยะที่ 1';
        if (sbp >= 140 || dbp >= 90) return 'ความดันโลหิตสูงระยะที่ 2';
        return 'ไม่ระบุ';
    };

    // ฟังก์ชันคำนวณสถานะการเต้นของหัวใจ
    window.calculateHeartRateCategory = function (heartRate) {
        if (heartRate >= 60 && heartRate <= 100) return 'ปกติ';
        if (heartRate < 60) return 'ช้ากว่าปกติ';
        if (heartRate > 100) return 'เร็วกว่าปกติ';
        return 'ไม่ระบุ';
    };

    // ฟังก์ชันคำนวณสถานะการได้ยิน
    window.calculateHearingStatus = function (row) {
        const rightValues = [
            parseInt(row.right_500),
            parseInt(row.right_1000),
            parseInt(row.right_2000),
            parseInt(row.right_3000),
            parseInt(row.right_4000),
            parseInt(row.right_6000),
            parseInt(row.right_8000)
        ];
        const leftValues = [
            parseInt(row.left_500),
            parseInt(row.left_1000),
            parseInt(row.left_2000),
            parseInt(row.left_3000),
            parseInt(row.left_4000),
            parseInt(row.left_6000),
            parseInt(row.left_8000)
        ];

        const rightStatus = rightValues.some(val => val > 25)
            ? '<span class="hearing-status-abnormal">การได้ยินหูขวา: มีความผิดปกติ</span>'
            : '<span class="hearing-status-normal">การได้ยินหูขวา: ปกติ</span>';
        const leftStatus = leftValues.some(val => val > 25)
            ? '<span class="hearing-status-abnormal">การได้ยินหูซ้าย: มีความผิดปกติ</span>'
            : '<span class="hearing-status-normal">การได้ยินหูซ้าย: ปกติ</span>';

        return { rightStatus, leftStatus };
    };

    // ฟังก์ชันคำนวณสรุปผลการได้ยินและคำแนะนำ
    window.calculateHearingSummary = function (rowData) {
        const rightLowFreq = [
            parseFloat(rowData.right_500),
            parseFloat(rowData.right_1000),
            parseFloat(rowData.right_2000)
        ];
        const leftLowFreq = [
            parseFloat(rowData.left_500),
            parseFloat(rowData.left_1000),
            parseFloat(rowData.left_2000)
        ];
        const rightHighFreq = [
            parseFloat(rowData.right_3000),
            parseFloat(rowData.right_4000),
            parseFloat(rowData.right_6000),
            parseFloat(rowData.right_8000)
        ];
        const leftHighFreq = [
            parseFloat(rowData.left_3000),
            parseFloat(rowData.left_4000),
            parseFloat(rowData.left_6000),
            parseFloat(rowData.left_8000)
        ];

        const maxRightLow = Math.max(...rightLowFreq);
        const maxRightHigh = Math.max(...rightHighFreq);
        const maxLeftLow = Math.max(...leftLowFreq);
        const maxLeftHigh = Math.max(...leftHighFreq);

        let summary = '';
        let recommendation = '';

        if (maxRightLow <= 25 && maxRightHigh <= 25 && maxLeftLow <= 25 && maxLeftHigh <= 25) {
            summary = "พนักงานมีระดับการได้ยินปกติทั้งสองข้าง";
            recommendation = `
                <div class="recommendation">
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow > 25 && maxRightHigh > 25 && maxLeftLow > 25 && maxLeftHigh > 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่ต่ำและความถี่สูงของหูข้างขวา และมีการสูญเสียการได้ยิน ความถี่ต่ำและความถี่สูงของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow > 25 && maxRightHigh > 25 && maxLeftLow <= 25 && maxLeftHigh > 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่ต่ำและความถี่สูงของหูข้างขวา และมีการสูญเสียการได้ยิน ความถี่สูงของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow > 25 && maxRightHigh > 25 && maxLeftLow > 25 && maxLeftHigh <= 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่ต่ำและความถี่สูงของหูข้างขวา และมีการสูญเสียการได้ยิน ความถี่ต่ำของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow > 25 && maxRightHigh > 25 && maxLeftLow <= 25 && maxLeftHigh <= 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่ต่ำและความถี่สูงของหูข้างขวา และมีระดับการได้ยินปกติของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow > 25 && maxRightHigh <= 25 && maxLeftLow > 25 && maxLeftHigh > 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่ต่ำของหูข้างขวา และมีการสูญเสียการได้ยิน ความถี่ต่ำและความถี่สูงของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow <= 25 && maxRightHigh > 25 && maxLeftLow > 25 && maxLeftHigh > 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่สูงของหูข้างขวา และมีการสูญเสียการได้ยิน ความถี่ต่ำและความถี่สูงของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow <= 25 && maxRightHigh <= 25 && maxLeftLow > 25 && maxLeftHigh > 25) {
            summary = "พนักงานมีระดับการได้ยินปกติของหูข้างขวา และมีการสูญเสียการได้ยิน ความถี่ต่ำและความถี่สูงของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow > 25 && maxRightHigh <= 25 && maxLeftLow <= 25 && maxLeftHigh > 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่ต่ำของหูข้างขวา และมีการสูญเสียการได้ยิน ความถี่สูงของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow > 25 && maxRightHigh <= 25 && maxLeftLow <= 25 && maxLeftHigh <= 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่ต่ำของหูข้างขวา และมีระดับการได้ยินปกติของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow <= 25 && maxRightHigh > 25 && maxLeftLow <= 25 && maxLeftHigh <= 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่สูงของหูข้างขวา และมีระดับการได้ยินปกติของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow <= 25 && maxRightHigh <= 25 && maxLeftLow > 25 && maxLeftHigh <= 25) {
            summary = "พนักงานมีระดับการได้ยินปกติของหูข้างขวา และมีการสูญเสียการได้ยิน ความถี่ต่ำของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow <= 25 && maxRightHigh <= 25 && maxLeftLow <= 25 && maxLeftHigh > 25) {
            summary = "พนักงานมีระดับการได้ยินปกติของหูข้างขวา และมีการสูญเสียการได้ยิน ความถี่สูงของหูข้างซ้าย";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow > 25 && maxLeftLow > 25 && maxRightHigh <= 25 && maxLeftHigh <= 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่ต่ำของหูทั้งสองข้าง";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else if (maxRightLow <= 25 && maxLeftLow <= 25 && maxRightHigh > 25 && maxLeftHigh > 25) {
            summary = "พนักงานมีการสูญเสียการได้ยิน ความถี่สูงของหูทั้งสองข้าง";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        } else {
            summary = "พนักงานมีการสูญเสียการได้ยินในรูปแบบที่ไม่สามารถระบุได้อย่างชัดเจน";
            recommendation = `
                <div class="recommendation">
                    <p><strong>คำแนะนำ:</strong></p>
                    <ol>
                        <li>ควรหลีกเลี่ยงเสียงดัง สวมอุปกรณ์ปกป้องการได้ยินทุกครั้งที่สัมผัสเสียงดัง และเข้ารับการตรวจ ติดตามการได้ยินต่อเนื่องทุกปี</li>
                        <li>ควรปรึกษาแพทย์ หู คอ จมูก เพื่อตรวจหาสาเหตุ</li>
                        <li>ควรนำผลที่ได้ไปเทียบกับ base line เดิมก่อนเข้าทำงาน หากมีการได้ยินลดลงมากกว่า 15 dB ที่ความถี่ใดความถี่หนึ่ง ให้ปฏิบัติตามมาตรการอนุรักษ์การได้ยินและควรมีการตรวจซ้ำ</li>
                    </ol>
                    <p><strong>หมายเหตุ:</strong></p>
                    <p>ก่อนรับการตรวจสมรรถภาพการได้ยิน ควรเตรียมตัวให้ผู้เข้ารับการตรวจ หลีกเลี่ยงการสัมผัสเสียงดัง ทุกชนิดอย่างน้อย 12 ชั่วโมง เพื่อป้องกันภาวะหูตึงชั่วคราว (Temporary threshold shift) ซึ่งอาจทำให้ผลการตรวจผิดพลาด</p>
                </div>
            `;
        }

        return `<span class="hearing-summary">${summary}</span>${recommendation}`;
    };

    // ฟังก์ชันโหลดข้อมูลจากเซิร์ฟเวอร์
    window.loadData = function () {
        fetch('list.php')
            .then(response => response.json())
            .then(data => {
                dataList = data;
                tableBody.innerHTML = '';
                data.forEach((row, index) => {
                    const { rightStatus, leftStatus } = window.calculateHearingStatus(row);
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${row.firstName} ${row.lastName}</td>
                        <td>${row.age}</td>
                        <td>${row.hn}</td>
                        <td class="hearing-summary-${row.id}"></td>
                        <td>
                            <button class="details-btn" onclick="showDetails(${JSON.stringify(row)})">แสดงข้อมูลทั้งหมด</button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                    document.querySelector(`.hearing-summary-${row.id}`).innerHTML = window.calculateHearingSummary(row);
                });
            })
            .catch(error => console.error('Error loading data:', error));
    };

    // ฟังก์ชันแสดงรายละเอียดใน Modal
    // ฟังก์ชันแปลงเดือนเป็นชื่อเดือนภาษาไทย
window.getThaiMonth = function(month) {
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
};

// ฟังก์ชันแปลงวันที่เป็นรูปแบบไทย
window.formatThaiDate = function(dateStr) {
    if (!dateStr) return "ไม่ระบุ";
    
    const [year, month, day] = dateStr.split('-');
    const yearBE = parseInt(year) + 543;
    const dayNum = parseInt(day); // ลบเลข 0 ข้างหน้า
    const thaiMonth = window.getThaiMonth(month);
    
    return `${dayNum} ${thaiMonth} ${yearBE}`;
};

window.showDetails1 = function (rowData) {
    const modal = document.getElementById('detailsModal');
    const modalDetails = document.getElementById('modalDetails');

    const { rightStatus, leftStatus } = window.calculateHearingStatus(rowData);
    const summaryWithRecommendation = window.calculateHearingSummary(rowData);
    
    // แปลงวันที่ให้เป็นรูปแบบไทย
    const formattedDate = window.formatThaiDate(rowData.exam_date);

    modalDetails.innerHTML = `
        <!-- เพิ่มปุ่มพิมพ์ PDF และ Export Excel -->
        <div style="margin-bottom: 20px;">
            <button class="details-btn" onclick="printPDF()">พิมพ์เป็น PDF</button>
            <button class="details-btn" onclick="exportToExcel()">Export เป็น Excel</button>
        </div>
        <div class="form-row">
            <div class="form-group col-6">
                <p><strong>สถานประกอบการ:</strong> ${rowData.establishment}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>แผนก:</strong> ${rowData.department}</p>
            </div>
            <div class="form-group col-4">
                <p><strong>วันที่ตรวจ:</strong> ${formattedDate}</p>
            </div>
            <div class="form-group col-4">
                <p><strong>ชื่อ-นามสกุล:</strong> ${rowData.firstName} ${rowData.lastName}</p>
            </div>
            <div class="form-group col-4">
                <p><strong>เพศ:</strong> ${rowData.gender}</p>
            </div>
            <div class="form-group col-4">
                <p><strong>อายุ:</strong> ${rowData.age} ปี</p>
            </div>
            <div class="form-group col-4">
                <p><strong>HN:</strong> ${rowData.hn}</p>
            </div>
            <div class="form-group col-4">
                <p><strong>น้ำหนัก:</strong> ${rowData.weight} กก.</p>
            </div>
            <div class="form-group col-4">
                <p><strong>ส่วนสูง:</strong> ${rowData.height} ซม.</p>
            </div>
            <div class="form-group col-4">
                <p><strong>BMI:</strong> ${rowData.bmi}</p>
            </div>
            <div class="form-group col-4">
                <p><strong>สถานะ:</strong> ${rowData.bmi_category || 'ไม่ระบุ'}</p>
            </div>
            <div class="form-group col-4">
                <p><strong>การเต้นของหัวใจ:</strong> ${rowData.heartRate} ครั้ง/นาที</p>
            </div>
            <div class="form-group col-4">
                <p><strong>สถานะการเต้นของหัวใจ:</strong> ${rowData.heartRate_category || 'ไม่ระบุ'}</p>
            </div>
            <div class="form-group col-4">
                <p><strong>ความดันโลหิต (SBP/DBP):</strong> ${rowData.sbp}/${rowData.dbp}</p>
            </div>
            <div class="form-group col-4">
                <p><strong>หมวดหมู่ความดันโลหิต:</strong> ${rowData.bloodPressure_category || 'ไม่ระบุ'}</p>
            </div>

        </div>
        <div class="form-row">
            <div class="form-group col-6">
                <p><strong>ผลการตรวจการได้ยิน - หูขวา (dB):</strong></p>
                <ul>
                    <li>500 Hz: <span class="audiogram-right">${rowData.right_500}</span></li>
                    <li>1000 Hz: <span class="audiogram-right">${rowData.right_1000}</span></li>
                    <li>2000 Hz: <span class="audiogram-right">${rowData.right_2000}</span></li>
                    <li>3000 Hz: <span class="audiogram-right">${rowData.right_3000}</span></li>
                    <li>4000 Hz: <span class="audiogram-right">${rowData.right_4000}</span></li>
                    <li>6000 Hz: <span class="audiogram-right">${rowData.right_6000}</span></li>
                    <li>8000 Hz: <span class="audiogram-right">${rowData.right_8000}</span></li>
                </ul>
                <p>${rightStatus}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>ผลการตรวจการได้ยิน - หูซ้าย (dB):</strong></p>
                <ul>
                    <li>500 Hz: <span class="audiogram-left">${rowData.left_500}</span></li>
                    <li>1000 Hz: <span class="audiogram-left">${rowData.left_1000}</span></li>
                    <li>2000 Hz: <span class="audiogram-left">${rowData.left_2000}</span></li>
                    <li>3000 Hz: <span class="audiogram-left">${rowData.left_3000}</span></li>
                    <li>4000 Hz: <span class="audiogram-left">${rowData.left_4000}</span></li>
                    <li>6000 Hz: <span class="audiogram-left">${rowData.left_6000}</span></li>
                    <li>8000 Hz: <span class="audiogram-left">${rowData.left_8000}</span></li>
                </ul>
                <p>${leftStatus}</p>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-12">
                <p><strong>สรุปผลตรวจ:</strong> ${summaryWithRecommendation}</p>
            </div>
        </div>
    `;

    // เก็บ rowData ใน dataset ของ modal-content
    modal.querySelector('.modal-content').dataset.rowData = JSON.stringify(rowData);

    modal.style.display = 'flex';
    document.querySelector(`.hearing-summary-${rowData.id}`).innerHTML = summaryWithRecommendation;
};

// ฟังก์ชันปิด Modal
window.closeModal = function () {
    document.getElementById('detailsModal').style.display = 'none';
};

// ปิด Modal เมื่อคลิกนอก Modal
window.onclick = function (event) {
    const modal = document.getElementById('detailsModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
};

    // ฟังก์ชันคำนวณ BMI และหมวดหมู่ (สำหรับหน้า index.php)
    if (weightField && heightField && bmiField && bmiCategoryField) {
        function calculateBMI() {
            const weight = parseFloat(weightField.value);
            const heightCm = parseFloat(heightField.value);
            let bmiCategory = document.getElementById('bmiCategory');

            if (!bmiCategory) {
                bmiCategory = document.createElement('small');
                bmiCategory.id = 'bmiCategory';
                bmiField.parentNode.appendChild(bmiCategory);
            }

            if (weight > 0 && heightCm > 0 && weight < 500 && heightCm < 300) {
                const { bmi, category } = window.calculateBMI(weight, heightCm);
                bmiField.value = bmi;
                bmiCategory.textContent = ` (${category})`;
                bmiCategoryField.value = category;

                if (category === 'น้ำหนักต่ำกว่าเกณฑ์') bmiCategory.style.color = '#e74c3c';
                else if (category === 'สมส่วน') bmiCategory.style.color = '#2ecc71';
                else if (category === 'น้ำหนักเกิน') bmiCategory.style.color = '#f1c40f';
                else if (category === 'อ้วนระดับ 1') bmiCategory.style.color = '#e67e22';
                else bmiCategory.style.color = '#c0392b';
            } else {
                bmiField.value = '';
                bmiCategory.textContent = '';
                bmiCategoryField.value = '';
                if ((weight >= 500 || heightCm >= 300) && (weight || heightCm)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'ข้อผิดพลาด',
                        text: 'กรุณากรอกน้ำหนักและส่วนสูงให้ถูกต้อง (น้ำหนัก < 500 กก., ส่วนสูง < 300 ซม.)'
                    });
                }
            }
        }

        function calculateHeartRateCategory() {
            if (!heartRateField) return;

            const heartRateValue = parseInt(heartRateField.value, 10);
            let heartRateCategoryEl = document.getElementById('heartRateCategory');

            if (!heartRateCategoryEl) {
                heartRateCategoryEl = document.createElement('small');
                heartRateCategoryEl.id = 'heartRateCategory';
                heartRateField.parentNode.appendChild(heartRateCategoryEl);
            }

            if (!isNaN(heartRateValue)) {
                const category = window.calculateHeartRateCategory(heartRateValue);
                heartRateCategoryEl.textContent = ` (${category})`;
                if (category === 'ช้ากว่าปกติ') heartRateCategoryEl.style.color = '#e74c3c';
                else if (category === 'ปกติ') heartRateCategoryEl.style.color = '#2ecc71';
                else heartRateCategoryEl.style.color = '#f39c12';
            } else {
                heartRateCategoryEl.textContent = '';
            }
        }

        weightField.addEventListener('input', calculateBMI);
        heightField.addEventListener('input', calculateBMI);
        calculateBMI();

        if (heartRateField) {
            heartRateField.addEventListener('input', calculateHeartRateCategory);
            calculateHeartRateCategory();
        }

        // ฟังก์ชันส่งข้อมูลฟอร์ม
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(form);

                const weight = parseFloat(formData.get('weight'));
                const height = parseFloat(formData.get('height'));
                const { bmi, category: bmiCategory } = window.calculateBMI(weight, height);
                formData.set('bmi', bmi);
                formData.set('bmi_category', bmiCategory);

                const sbp = parseInt(formData.get('sbp'));
                const dbp = parseInt(formData.get('dbp'));
                const bloodPressureCategory = window.calculateBloodPressureCategory(sbp, dbp);
                formData.set('bloodPressure_category', bloodPressureCategory);

                const heartRate = parseInt(formData.get('heartRate'));
                const heartRateCategory = window.calculateHeartRateCategory(heartRate);
                formData.set('heartRate_category', heartRateCategory);

                fetch('submit.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ',
                                text: data.message,
                                confirmButtonText: 'ตกลง'
                            }).then(() => {
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: data.message,
                                confirmButtonText: 'ตกลง'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้: ' + error,
                            confirmButtonText: 'ตกลง'
                        });
                    });
            });
        }
    } else {
        console.log('หน้าไม่มีฟอร์มสำหรับคำนวณ BMI และ Heart Rate (เช่น หน้า list.php)');
    }

    // ฟังก์ชัน Export เป็น Excel
    window.exportToExcel = function () {
        const modalContent = document.querySelector('.modal-content');
        const rowData = JSON.parse(modalContent.dataset.rowData || '{}');
        if (!rowData.id) {
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: 'ไม่พบข้อมูลสำหรับ Export'
            });
            return;
        }

        const data = [rowData];
        const ws = XLSX.utils.json_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Audiogram Data');
        XLSX.writeFile(wb, `audiogram_${rowData.firstName}_${rowData.lastName}.xlsx`);
    };

    // ฟังก์ชันพิมพ์ PDF
    window.printPDF = function () {
        const modalContent = document.querySelector('.modal-content');
        const rowData = JSON.parse(modalContent.dataset.rowData || '{}');
        if (rowData.id) {
            window.location.href = `generate_pdf.php?id=${rowData.id}`;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: 'ไม่พบ ID ของพนักงาน'
            });
        }
    };

    // โหลดข้อมูลเมื่อหน้าเว็บโหลด
    window.loadData();

    // เพิ่มการจัดการ Sidebar
    document.querySelector('.toggle-sidebar-btn')?.addEventListener('click', function () {
        document.querySelector('.sidebar').classList.toggle('collapsed');
    });

    // เพิ่มฟังก์ชันจัดการวันที่ตรวจ
    const examDateInput = document.getElementById('exam_date');
    const thaiDateDisplay = document.getElementById('exam_date_thai');

    if (examDateInput && thaiDateDisplay) {
        examDateInput.addEventListener('change', () => {
            const dateValue = examDateInput.value;
            if (dateValue) {
                const [year, month, day] = dateValue.split("-");
                const buddhistYear = parseInt(year) + 543;
                const formattedDate = `${parseInt(day)}/${parseInt(month)}/${buddhistYear}`;
                thaiDateDisplay.textContent = `วันที่ตรวจ (พ.ศ.): ${formattedDate}`;
            } else {
                thaiDateDisplay.textContent = "";
            }
        });
    }

    window.confirmDelete = function (button) {
        Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: "หากลบแล้วจะไม่สามารถกู้คืนได้!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                button.closest('form').submit();
            }
        });
    };
    
});

function showDetails2(rowData) {
    const modal = document.getElementById("detailsModal");
    const modalDetails = document.getElementById("modalDetails");

    // แปลงวันที่ให้เป็นรูปแบบไทย
    const formattedDate = window.formatThaiDate(rowData.exam_date);

    // Map organization_name to evaluation function
    const groupEvaluators = {
        "สำนักงาน / ธุรการ": evaluateOfficeGroup,
        "ตรวจสอบคุณภาพ": evaluateQualityGroup,
        "ขับขี่ยานพาหนะ": evaluateDriverGroup,
        "ใช้เครื่องจักรกล": evaluateMachineryGroup,
        "ช่างเทคนิค / วิศวกร": evaluateEngineerGroup,
        "แรงงานทั่วไป": evaluateLaborGroup
    };

    // Select the evaluation function based on organization_name, default to evaluateLaborGroup if not found
    const evaluateFunction = groupEvaluators[rowData.organization_name] || evaluateLaborGroup;

    // Evaluate vision data
    const visionResults = evaluateFunction({
        C5: rowData.binocular_vision_far,
        D5: rowData.far_vision_both,
        E5: rowData.far_vision_right,
        F5: rowData.far_vision_left,
        G5: rowData.stereo_depth,
        H5: rowData.color_discrimination,
        I5: rowData.far_vertical_phoria,
        J5: rowData.far_lateral_phoria,
        K5: rowData.binocular_vision_near,
        L5: rowData.near_vision_both,
        M5: rowData.near_vision_right,
        N5: rowData.near_vision_left,
        O5: rowData.near_vertical_phoria,
        P5: rowData.near_lateral_phoria,
        Q5: rowData.visual_field
    });

    // Generate HTML with evaluation results
    let html = `
        <!-- เพิ่มปุ่มพิมพ์ PDF และ Export Excel -->
        <div style="margin-bottom: 20px;">
            <button class="details-btn" onclick="printPDF()">พิมพ์เป็น PDF</button>
            <button class="details-btn" onclick="exportToExcel()">Export เป็น Excel</button>
        </div>
        <div class="form-row">
            <div class="form-group col-6">
                <p><strong>ชื่อ-นามสกุล:</strong> ${rowData.first_name} ${rowData.last_name}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>แผนก:</strong> ${rowData.department_name || "-"}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>กลุ่มอาชีพ:</strong> ${rowData.organization_name || "-"}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>วันที่ตรวจ:</strong> ${formattedDate}</p>
            </div>
            <hr>
            <div class="form-group col-12">
                <p><strong>การมองภาพระยะไกล:</strong></p>
            </div>
            <div class="form-group col-6">
                <p><strong>การมองวัตถุสองตาระยะไกล (Binocular vision):</strong> (${rowData.binocular_vision_far}) ${visionResults.binocularFar}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>การมองภาพระยะไกลด้วยสองตา (Far vision Both):</strong> (${rowData.far_vision_both}) ${visionResults.farBoth}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>การมองภาพระยะไกลด้วยตาขวา (Far vision Right):</strong> (${rowData.far_vision_right}) ${visionResults.farRight}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>การมองภาพระยะไกลด้วยตาซ้าย (Far vision Left):</strong> (${rowData.far_vision_left}) ${visionResults.farLeft}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>การมองภาพ 3 มิติ (Stereo depth):</strong> (${rowData.stereo_depth}) ${visionResults.stereoDepth}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>การมองจำแนกสี (Color discrimination):</strong> (${rowData.color_discrimination}) ${visionResults.color}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>ความเบี่ยงคลาดในแนวตั้งระยะไกล (Far vertical phoria):</strong> (${rowData.far_vertical_phoria}) ${visionResults.farVertical}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>ความเบี่ยงคลาดในแนวนอนระยะไกล (Far lateral phoria):</strong> (${rowData.far_lateral_phoria}) ${visionResults.farLateral}</p>
            </div>
            <div class="form-group col-12">
                <p><strong>การมองภาพระยะใกล้:</strong></p>
            </div>
            <div class="form-group col-6">
                <p><strong>การมองวัตถุสองตาระยะใกล้ (Binocular vision Near):</strong> (${rowData.binocular_vision_near}) ${visionResults.binocularNear}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>การมองภาพระยะใกล้ด้วยสองตา (Near vision Both):</strong> (${rowData.near_vision_both}) ${visionResults.nearBoth}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>การมองภาพระยะใกล้ด้วยตาขวา (Near vision Right):</strong> (${rowData.near_vision_right}) ${visionResults.nearRight}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>การมองภาพระยะใกล้ด้วยตาซ้าย (Near vision Left):</strong> (${rowData.near_vision_left}) ${visionResults.nearLeft}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>ความเบี่ยงคลาดในแนวตั้งระยะใกล้ (Near vertical phoria):</strong> (${rowData.near_vertical_phoria}) ${visionResults.nearVertical}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>ความเบี่ยงคลาดในแนวนอนระยะใกล้ (Near lateral phoria):</strong> (${rowData.near_lateral_phoria}) ${visionResults.nearLateral}</p>
            </div>
            <div class="form-group col-6">
                <p><strong>ลานสายตา (Visual field):</strong> (${rowData.visual_field}) ${visionResults.visualField}</p>
            </div>
        </div>
    `;

    modalDetails.innerHTML = html;
    modal.style.display = "flex";
}

function closeModal() {
    document.getElementById("detailsModal").style.display = "none";
}
function evaluateVision(value, min, max, type = "range", naLabel = "ไม่เป็นไร", normalLabel = "ปกติ", abnormalLabel = "ผิดปกติ") {
    if (value === "N/A") return naLabel;
    if (value === "") return "";
    
    if (type === "range") {
      const num = Number(value);
      return num >= min && num <= max ? normalLabel : abnormalLabel;
    }
  
    if (type === "select") {
      return (value === "3") ? normalLabel : (["2", "4"].includes(value) ? abnormalLabel : "");
    }
  
    if (type === "field") {
      return Number(value) > 7 ? normalLabel : abnormalLabel;
    }
  
    return "";
  }
  
  // กลุ่ม: สำนักงาน / ธุรการ
  function evaluateOfficeGroup(data) {
    return {
      binocularFar: evaluateVision(data.C5, null, null, "select"),
      farBoth: evaluateVision(data.D5, 9, 12, "range", "ไม่เป็นไร", "ชัดเจน", "ไม่ชัดเจน"),
      farRight: evaluateVision(data.E5, 8, 12, "range", "ไม่เป็นไร", "ชัดเจน", "ไม่ชัดเจน"),
      farLeft: evaluateVision(data.F5, 8, 12, "range", "ไม่เป็นไร", "ชัดเจน", "ไม่ชัดเจน"),
      stereoDepth: evaluateVision(data.G5, 8, 12, "range", "ไม่เป็นไร", "ชัดเจน", "ไม่ชัดเจน"),
      color: evaluateVision(data.H5, 5, 8, "range", "ไม่เป็นไร", "ปกติ", "ผิดปกติ"),
      farVertical: evaluateVision(data.I5, 3, 5),
      farLateral: evaluateVision(data.J5, 4, 13),
      binocularNear: evaluateVision(data.K5, null, null, "select"),
      nearBoth: evaluateVision(data.L5, 10, 12),
      nearRight: evaluateVision(data.M5, 9, 12),
      nearLeft: evaluateVision(data.N5, 9, 12),
      nearVertical: evaluateVision(data.O5, 3, 5),
      nearLateral: evaluateVision(data.P5, 4, 13),
      visualField: evaluateVision(data.Q5, null, null, "field"),
    };
  }
  
  // กลุ่ม: ตรวจสอบคุณภาพ
  function evaluateQualityGroup(data) {
    return {
      binocularFar: evaluateVision(data.C5, null, null, "select"),
      farBoth: evaluateVision(data.D5, 10, 12),
      farRight: evaluateVision(data.E5, 9, 12),
      farLeft: evaluateVision(data.F5, 9, 12),
      stereoDepth: evaluateVision(data.G5, 9, 12),
      color: evaluateVision(data.H5, 6, 8),
      farVertical: evaluateVision(data.I5, 4, 5),
      farLateral: evaluateVision(data.J5, 6, 13),
      binocularNear: evaluateVision(data.K5, null, null, "select"),
      nearBoth: evaluateVision(data.L5, 11, 12),
      nearRight: evaluateVision(data.M5, 10, 12),
      nearLeft: evaluateVision(data.N5, 10, 12),
      nearVertical: evaluateVision(data.O5, 4, 5),
      nearLateral: evaluateVision(data.P5, 6, 13),
      visualField: evaluateVision(data.Q5, null, null, "field"),
    };
  }
  
  // กลุ่ม: ขับขี่ยานพาหนะ
  function evaluateDriverGroup(data) {
    return {
      binocularFar: evaluateVision(data.C5, null, null, "select"),
      farBoth: evaluateVision(data.D5, 10, 12),
      farRight: evaluateVision(data.E5, 9, 12),
      farLeft: evaluateVision(data.F5, 9, 12),
      stereoDepth: evaluateVision(data.G5, 8, 12),
      color: evaluateVision(data.H5, 5, 8),
      farVertical: evaluateVision(data.I5, 3, 5),
      farLateral: evaluateVision(data.J5, 5, 13),
      binocularNear: evaluateVision(data.K5, null, null, "select"),
      nearBoth: evaluateVision(data.L5, 10, 12),
      nearRight: evaluateVision(data.M5, 9, 12),
      nearLeft: evaluateVision(data.N5, 9, 12),
      nearVertical: evaluateVision(data.O5, 3, 5),
      nearLateral: evaluateVision(data.P5, 5, 13),
      visualField: evaluateVision(data.Q5, null, null, "field"),
    };
  }
  
  // กลุ่ม: ใช้เครื่องจักรกล
  function evaluateMachineryGroup(data) {
    return {
      binocularFar: evaluateVision(data.C5, null, null, "select"),
      farBoth: evaluateVision(data.D5, 9, 12),
      farRight: evaluateVision(data.E5, 8, 12),
      farLeft: evaluateVision(data.F5, 8, 12),
      stereoDepth: evaluateVision(data.G5, 5, 9),
      color: evaluateVision(data.H5, 5, 8),
      farVertical: evaluateVision(data.I5, 3, 5),
      farLateral: evaluateVision(data.J5, 4, 13),
      binocularNear: evaluateVision(data.K5, null, null, "select"),
      nearBoth: evaluateVision(data.L5, 10, 12),
      nearRight: evaluateVision(data.M5, 9, 12),
      nearLeft: evaluateVision(data.N5, 9, 12),
      nearVertical: evaluateVision(data.O5, 3, 5),
      nearLateral: evaluateVision(data.P5, 4, 13),
      visualField: evaluateVision(data.Q5, null, null, "field"),
    };
  }
// กลุ่ม: ช่างเทคนิค / วิศวกร
function evaluateEngineerGroup(data) {
    return {
      binocularFar: evaluateVision(data.C5, null, null, "select"),
      farBoth: evaluateVision(data.D5, 10, 12),
      farRight: evaluateVision(data.E5, 9, 12),
      farLeft: evaluateVision(data.F5, 9, 12),
      stereoDepth: evaluateVision(data.G5, 8, 12),
      color: evaluateVision(data.H5, 6, 8),
      farVertical: evaluateVision(data.I5, 4, 5),
      farLateral: evaluateVision(data.J5, 6, 13),
      binocularNear: evaluateVision(data.K5, null, null, "select"),
      nearBoth: evaluateVision(data.L5, 10, 12),
      nearRight: evaluateVision(data.M5, 9, 12),
      nearLeft: evaluateVision(data.N5, 9, 12),
      nearVertical: evaluateVision(data.O5, 4, 5),
      nearLateral: evaluateVision(data.P5, 6, 13),
      visualField: evaluateVision(data.Q5, null, null, "field"),
    };
  }
  
  // กลุ่ม: แรงงานทั่วไป
  function evaluateLaborGroup(data) {
    return {
      binocularFar: evaluateVision(data.C5, null, null, "select"),
      farBoth: evaluateVision(data.D5, 9, 12),
      farRight: evaluateVision(data.E5, 8, 12),
      farLeft: evaluateVision(data.F5, 8, 12),
      stereoDepth: evaluateVision(data.G5, 6, 12),
      color: evaluateVision(data.H5, 5, 8),
      farVertical: evaluateVision(data.I5, 3, 5),
      farLateral: evaluateVision(data.J5, 4, 13),
      binocularNear: evaluateVision(data.K5, null, null, "select"),
      nearBoth: evaluateVision(data.L5, 9, 12),
      nearRight: evaluateVision(data.M5, 8, 12),
      nearLeft: evaluateVision(data.N5, 8, 12),
      nearVertical: evaluateVision(data.O5, 3, 5),
      nearLateral: evaluateVision(data.P5, 4, 13),
      visualField: evaluateVision(data.Q5, null, null, "field"),
    };
  }

  function evaluateVision(value, min, max, type = "range", naLabel = "ไม่เป็นไร", normalLabel = "ปกติ", abnormalLabel = "ผิดปกติ") {
    if (value === "N/A") return naLabel;
    if (value === "") return "";
    
    if (type === "range") {
        const num = Number(value);
        return num >= min && num <= max ? normalLabel : abnormalLabel;
    }
  
    if (type === "select") {
        return value === "3" ? normalLabel : abnormalLabel; // Treat all non-"3" values (including "7") as abnormal
    }
  
    if (type === "field") {
        return Number(value) > 7 ? normalLabel : abnormalLabel;
    }
  
    return "";
}