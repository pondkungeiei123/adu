<!-- sidebar.php -->
<!-- Hamburger Menu Button -->
<button class="hamburger" onclick="toggleSidebar()">☰</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Menu</h3>
    </div>
    <div class="sidebar-menu">
        <a href="index.php" class="menu-item">
            <span class="menu-text">บันทึกผลตรวจหู</span>
        </a>
        <a href="list_aud.php" class="menu-item">
            <span class="menu-text">แสดงรายชื่อผลตรวจหู</span>
        </a>
        <a href="eye.php" class="menu-item">
            <span class="menu-text">บันทึกผลตรวจตา</span>
        </a>
        <a href="list_eye.php" class="menu-item">
            <span class="menu-text">แสดงข้อมูลผลตรวจตา</span>
        </a>
        <!-- Dropdown for Add Data -->
        <div class="menu-item sidebar-dropdown">
            <a href="#" class="dropdown-toggle" onclick="toggleDropdown(this)">
                <span class="menu-text">เพิ่มข้อมูล</span>
                <i class="chevron bi bi-chevron-down"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li>
                        <a href="manage_departments.php" class="dropdown-item">เพิ่มแผนกใหม่</a>
                    </li>
                    <li>
                        <a href="manage_organizations.php" class="dropdown-item">เพิ่มกลุ่มอาชีพใหม่</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="sidebar-footer">
        <button class="reset-btn" type="button" onclick="document.getElementById('eyeForm') ? document.getElementById('eyeForm').reset() : document.getElementById('dataForm').reset(); calculateBMI && calculateBMI();">Reset</button>
    </div>
</div>

<script>
    // Toggle Sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }

    // Toggle Dropdown
    function toggleDropdown(element) {
        const dropdown = element.parentElement;
        dropdown.classList.toggle('active');
    }
</script>