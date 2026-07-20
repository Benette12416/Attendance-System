<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
/** @var mysqli $conn */
$pageTitle = 'Attendance';

$date = $_GET['date'] ?? date('Y-m-d');
$search = trim($_GET['search'] ?? '');

$query = "
    SELECT a.*, s.fullname, s.student_id AS sid, s.course, s.year, s.section
    FROM attendance a
    JOIN students s ON s.id = a.student_id
    WHERE a.date = '" . mysqli_real_escape_string($conn, $date) . "'
";
if ($search !== '') {
    $searchEsc = mysqli_real_escape_string($conn, $search);
    $query .= " AND (s.fullname LIKE '%$searchEsc%' OR s.student_id LIKE '%$searchEsc%')";
}
$query .= " ORDER BY a.time_in DESC";

$records = mysqli_query($conn, $query);

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Minimalist & Modern UI CSS -->
<style>
    :root {
        --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0; 
        --slate-300: #cbd5e1; --slate-400: #94a3b8; --slate-500: #64748b; 
        --slate-700: #334155; --slate-800: #1e293b; --slate-900: #0f172a;
        --blue-500: #3b82f6; --blue-600: #2563eb; --red-500:  #ef4444;
    }

    /* GLOBAL FIX: Prevents padding from breaking 100% widths */
    *, *::before, *::after {
        box-sizing: border-box;
    }

    /* Fixed Layout Wrapper */
    .page-content {
        flex: 1;
        min-width: 0;
        width: 100%;
        display: flex;
        flex-direction: column;
        padding: 24px;
    }

    /* Grid Layout for Attendance */
    .attendance-layout {
        display: grid;
        grid-template-columns: 360px minmax(0, 1fr); /* Prevents right column from blowing out */
        gap: 24px;
        align-items: start;
    }

    /* Ultra-Clean Cards */
    .clean-card {
        background: #ffffff; border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 20px -5px rgba(0,0,0,0.03);
        border: 1px solid var(--slate-100); padding: 28px;
        min-width: 0; /* CRITICAL FIX: Stops the card from stretching past screen width */
    }
    .clean-card h2 {
        font-size: 1.15rem; font-weight: 600; color: var(--slate-900);
        margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;
    }

    /* Modern Scan Box */
    .scan-box {
        background-color: #eff6ff;
        border: 2px dashed #93c5fd;
        border-radius: 16px;
        padding: 32px 24px;
        text-align: center;
        transition: all 0.3s ease;
    }
    .scan-box:focus-within {
        background-color: #e0f2fe;
        border-color: var(--blue-500);
    }
    .scan-title { margin: 0 0 16px 0; font-weight: 700; color: var(--blue-600); font-size: 1rem; line-height: 1.4; }
    
    .scan-input {
        width: 100%; max-width: 280px; padding: 12px 16px;
        border: 1px solid #bfdbfe; border-radius: 10px; font-size: 1rem;
        text-align: center; color: var(--slate-900); background-color: #ffffff;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s;
        display: block; 
        margin: 0 auto; 
    }
    .scan-input:focus { outline: none; border-color: var(--blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
    .scan-result { margin-top: 16px; font-size: 0.9rem; font-weight: 600; min-height: 20px; color: var(--slate-500); }

    /* Filters Form */
    .filters-wrapper {
        display: flex; gap: 12px; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap;
    }
    .form-group { display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 150px; }
    .form-group label { font-size: 0.8rem; font-weight: 600; color: var(--slate-700); }
    
    .clean-input {
        width: 100%; padding: 10px 14px; border: 1px solid var(--slate-200);
        border-radius: 10px; font-size: 0.95rem; background-color: #ffffff; transition: all 0.2s;
    }
    .clean-input:focus { outline: none; border-color: var(--blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    
    /* Action Buttons Container */
    .action-buttons {
        display: flex; gap: 8px;
    }

    .btn-solid {
        padding: 10px 20px; border-radius: 10px; font-size: 0.9rem; font-weight: 600;
        color: #ffffff; background-color: var(--blue-600); border: none; cursor: pointer; transition: all 0.2s;
        height: 42px; display: inline-flex; align-items: center; justify-content: center;
    }
    .btn-solid:hover { background-color: var(--blue-500); }
    
    .btn-light {
        padding: 10px 20px; border-radius: 10px; font-size: 0.9rem; font-weight: 600; text-decoration: none;
        color: var(--slate-700); background-color: var(--slate-100); border: 1px solid var(--slate-200); 
        cursor: pointer; transition: all 0.2s; height: 42px; display: inline-flex; align-items: center; justify-content: center;
    }
    .btn-light:hover { background-color: var(--slate-200); }

    /* Elegant Table */
    .table-container { overflow-x: auto; padding-bottom: 12px; width: 100%; }
    .table-container::-webkit-scrollbar { height: 8px; }
    .table-container::-webkit-scrollbar-track { background: transparent; }
    .table-container::-webkit-scrollbar-thumb { background-color: var(--slate-200); border-radius: 10px; }
    table { width: 100%; border-collapse: collapse; min-width: 600px; }
    th {
        color: var(--slate-400); font-weight: 600; font-size: 0.7rem; text-transform: uppercase;
        letter-spacing: 0.08em; padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--slate-100);
    }
    td { padding: 16px; border-bottom: 1px solid var(--slate-50); color: var(--slate-700); vertical-align: middle; font-size: 0.9rem; }
    tbody tr:hover { background-color: var(--slate-50); }

    /* Badges */
    .status-badge {
        display: inline-flex; align-items: center; padding: 4px 10px;
        border-radius: 9999px; font-size: 0.75rem; font-weight: 600;
    }
    .badge-Present { background-color: #ecfdf5; color: #059669; }
    .badge-Late { background-color: #fffbeb; color: #d97706; }
    .badge-default { background-color: var(--slate-100); color: var(--slate-600); }

    /* Responsive Constraints */
    @media (max-width: 1024px) {
        /* minmax(0, 1fr) is the key to preventing grid blowouts on mobile */
        .attendance-layout { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 640px) {
        .page-content { padding: 16px; }
        .clean-card { padding: 20px; }
        
        /* Adjust scan box padding for small screens */
        .scan-box { padding: 24px 16px; }
        
        /* Stack the filter form inputs and buttons properly */
        .filters-wrapper { flex-direction: column; align-items: stretch; }
        .form-group { max-width: 100% !important; }
        
        .action-buttons { flex-direction: column; width: 100%; }
        .btn-solid, .btn-light { width: 100%; }
    }
</style>

<!-- MAIN CONTENT WRAPPER -->
<div class="page-content">

    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--slate-900); margin: 0 0 4px 0;">Attendance</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">Live RFID scan station and daily attendance logs.</p>
    </div>

    <div class="attendance-layout">
        
        <!-- LEFT/TOP: Live Scan Station -->
        <div class="clean-card">
            <h2>Live Scan Station</h2>
            <div class="scan-box">
                <p class="scan-title">Tap a registered card to record time in / time out</p>
                <input type="text" id="scanInput" class="scan-input" placeholder="Waiting for RFID to scan..." autocomplete="off" inputmode="none">
                <div id="scanResult" class="scan-result">Ready to scan.</div>
            </div>
        </div>

        <!-- RIGHT/BOTTOM: Attendance Log Table -->
        <div class="clean-card">
            <h2>Attendance Log</h2>
            
            <form method="GET" class="filters-wrapper">
                <div class="form-group" style="max-width: 200px;">
                    <label>Date</label>
                    <input type="date" name="date" class="clean-input" value="<?php echo h($date); ?>">
                </div>
                <div class="form-group">
                    <label>Search Student</label>
                    <input type="text" name="search" class="clean-input" placeholder="Name or student ID..." value="<?php echo h($search); ?>">
                </div>
                
                <!-- Updated Button Container -->
                <div class="action-buttons">
                    <button type="submit" class="btn-solid">Filter</button>
                    <a href="attendance.php" class="btn-light">Reset</a>
                </div>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                    <?php if (mysqli_num_rows($records) === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px 0; color: var(--slate-400);">
                                No attendance records found for this date.
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php while ($r = mysqli_fetch_assoc($records)): 
                        // Determine badge class
                        $statusClass = 'badge-default';
                        if($r['status'] === 'Present') $statusClass = 'badge-Present';
                        if($r['status'] === 'Late') $statusClass = 'badge-Late';
                    ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--slate-500);"><?php echo h($r['sid']); ?></td>
                            <td style="font-weight: 600; color: var(--slate-900);"><?php echo h($r['fullname']); ?></td>
                            <td style="color: var(--slate-500);"><?php echo h($r['course']) . ' ' . h($r['year']) . '-' . h($r['section']); ?></td>
                            <td style="font-weight: 500;"><?php echo formatTime($r['time_in']); ?></td>
                            <td style="font-weight: 500;"><?php echo formatTime($r['time_out']); ?></td>
                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo h($r['status']); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Custom Diagnostic Scan Logic -->
<script>
    const scanInput = document.getElementById('scanInput');
    const scanResult = document.getElementById('scanResult');

    // No auto-focus on page load, and nothing ever steals focus from Date/Search.
    // You tap scanInput yourself when you're ready to scan; inputmode="none" keeps
    // the on-screen mobile keyboard from popping up over it, but the physical/HID
    // RFID reader still types into it normally once it's focused.
    scanInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const rfid = scanInput.value.trim();
            scanInput.value = ''; // Clear display

            if (!rfid) return;

            scanResult.innerText = 'Verifying...';
            scanResult.style.color = 'var(--slate-500)';

            fetch('/attendancesystem/api/scan.php', {
                method: 'POST',
                body: JSON.stringify({ rfid: rfid })
            })
            .then(async response => {
                const text = await response.text();
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        scanResult.style.color = '#059669';
                        scanResult.innerText = 'Success!';
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        scanResult.style.color = '#dc2626';
                        scanResult.innerText = data.message || 'Incorrect RFID';
                        scanInput.value = '';
                        scanInput.focus();
                    }
                } catch (err) {
                    scanResult.style.color = '#dc2626';
                    scanResult.innerText = 'Server Error! Press F12 and check Console.';
                    scanInput.value = '';
                    scanInput.focus();
                }
            })
            .catch(error => {
                scanResult.style.color = '#dc2626';
                scanResult.innerText = 'Network Error';
                scanInput.value = '';
                scanInput.focus();
            });
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>