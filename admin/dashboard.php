<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
/** @var mysqli $conn */
$pageTitle = 'Dashboard';
$today = date('Y-m-d');

// ---- Stat cards ----
$presentToday = mysqli_query($conn, "SELECT COUNT(*) c FROM attendance WHERE date = '$today' AND status = 'Present'");
$presentToday = mysqli_fetch_assoc($presentToday)['c'];

$lateToday = mysqli_query($conn, "SELECT COUNT(*) c FROM attendance WHERE date = '$today' AND status = 'Late'");
$lateToday = mysqli_fetch_assoc($lateToday)['c'];

$totalStudents = mysqli_query($conn, "SELECT COUNT(*) c FROM students");
$totalStudents = mysqli_fetch_assoc($totalStudents)['c'];

// ---- Recent attendance (today, most recent first) ----
$recent = mysqli_query($conn, "
    SELECT a.*, s.fullname, s.course, s.year, s.section
    FROM attendance a
    JOIN students s ON s.id = a.student_id
    ORDER BY a.date DESC, GREATEST(COALESCE(a.time_out,'00:00:00'), COALESCE(a.time_in,'00:00:00')) DESC
    LIMIT 10
");

// ---- Chart data: last 7 days ----
$chartLabels = [];
$chartPresent = [];
$chartLate = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('M d', strtotime($d));

    $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM attendance WHERE date='$d' AND status='Present'"))['c'];
    $l = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM attendance WHERE date='$d' AND status='Late'"))['c'];
    $chartPresent[] = (int)$p;
    $chartLate[] = (int)$l;
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Minimalist & Modern UI CSS -->
<style>
    :root {
        --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0; 
        --slate-300: #cbd5e1; --slate-400: #94a3b8; --slate-500: #64748b; 
        --slate-700: #334155; --slate-800: #1e293b; --slate-900: #0f172a;
        --blue-500: #3b82f6; --blue-600: #2563eb; 
        --emerald-500: #10b981; --emerald-600: #059669;
        --amber-500: #f59e0b; --amber-600: #d97706;
    }

    /* GLOBAL FIX: Prevents padding from breaking 100% widths */
    *, *::before, *::after {
        box-sizing: border-box;
    }

    /* Fixed Layout Wrapper */
    .page-content {
        flex: 1; min-width: 0; width: 100%;
        display: flex; flex-direction: column;
        padding: 24px;
    }

    /* Stat Cards Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: #ffffff; border-radius: 16px; padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 20px -5px rgba(0,0,0,0.03);
        border: 1px solid var(--slate-100);
        display: flex; flex-direction: column; position: relative; overflow: hidden;
        min-width: 0; /* CRITICAL FIX */
    }
    
    .stat-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
    }
    .stat-card.success::before { background-color: var(--emerald-500); }
    .stat-card.warning::before { background-color: var(--amber-500); }
    .stat-card.info::before { background-color: var(--blue-500); }

    .stat-label { font-size: 0.85rem; font-weight: 600; color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
    .stat-value { font-size: 2.5rem; font-weight: 700; color: var(--slate-900); line-height: 1; }

    /* Main Content Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); /* CRITICAL FIX: prevents blowout */
        gap: 24px;
        margin-bottom: 24px;
        align-items: start;
    }

    /* Ultra-Clean Cards */
    .clean-card {
        background: #ffffff; border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 20px -5px rgba(0,0,0,0.03);
        border: 1px solid var(--slate-100); padding: 28px;
        min-width: 0; /* CRITICAL FIX */
    }
    .clean-card h2 {
        font-size: 1.15rem; font-weight: 600; color: var(--slate-900);
        margin: 0 0 20px 0; border-bottom: 1px solid var(--slate-100); padding-bottom: 12px;
    }

    /* Elegant Table */
    .table-container { width: 100%; overflow-x: auto; }
    .table-container::-webkit-scrollbar { height: 8px; }
    .table-container::-webkit-scrollbar-thumb { background-color: var(--slate-200); border-radius: 10px; }
    table { width: 100%; border-collapse: collapse; min-width: 400px; }
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
    .badge-Present { background-color: #ecfdf5; color: var(--emerald-600); }
    .badge-Late { background-color: #fffbeb; color: var(--amber-600); }
    
    /* Quick Actions Feature */
    .live-clock-text {
        font-size: 2.75rem; font-weight: 700; color: var(--slate-900);
        font-variant-numeric: tabular-nums; margin: 10px 0; letter-spacing: -0.02em;
    }
    
    .btn-quick-action {
        display: flex; justify-content: space-between; align-items: center;
        padding: 14px 18px; background: var(--slate-50); border: 1px solid var(--slate-200);
        border-radius: 12px; color: var(--slate-700); font-weight: 600; font-size: 0.95rem;
        text-decoration: none; transition: all 0.2s;
    }
    .btn-quick-action:hover {
        background: #ffffff; border-color: var(--blue-500); color: var(--blue-600);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1); transform: translateY(-1px);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .dashboard-grid { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 640px) {
        .page-content { padding: 16px; }
        .clean-card { padding: 20px; }
        .live-clock-text { font-size: 2.25rem; }
    }
</style>

<!-- MAIN CONTENT WRAPPER -->
<div class="page-content">

    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--slate-900); margin: 0 0 4px 0;">Dashboard</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">Overview of today's attendance activity - <?php echo date('l, F d, Y'); ?></p>
    </div>

    <!-- Top Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-label">Present Today</div>
            <div class="stat-value"><?php echo $presentToday; ?></div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">Late Today</div>
            <div class="stat-value"><?php echo $lateToday; ?></div>
        </div>
        <div class="stat-card info">
            <div class="stat-label">Total Students</div>
            <div class="stat-value"><?php echo $totalStudents; ?></div>
        </div>
    </div>

    <!-- Main Two-Column Grid -->
    <div class="dashboard-grid">
        
        <!-- LEFT: Recent Attendance & Chart -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <div class="clean-card">
                <h2>Recent Attendance</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($recent) === 0): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px 0; color: var(--slate-400);">
                                    No attendance records yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php while ($row = mysqli_fetch_assoc($recent)): 
                            $statusClass = $row['status'] === 'Present' ? 'badge-Present' : 'badge-Late';
                        ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--slate-900);"><?php echo h($row['fullname']); ?></td>
                                <td style="font-weight: 500; color: var(--slate-600);"><?php echo formatTime($row['time_in']); ?></td>
                                <td style="font-weight: 500; color: var(--slate-600);"><?php echo formatTime($row['time_out']); ?></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo h($row['status']); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="clean-card">
                <h2>Attendance Chart (Last 7 Days)</h2>
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

        </div>

        <!-- RIGHT: System Overview & Quick Actions -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- System Clock Card -->
            <div class="clean-card" style="text-align: center; padding: 32px 24px;">
                <h2 style="border: none; margin-bottom: 0; color: var(--slate-500); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em;">Current System Time</h2>
                <div id="liveClock" class="live-clock-text">--:--:--</div>
                <div style="color: var(--slate-500); font-weight: 500;"><?php echo date('l, F d, Y'); ?></div>
            </div>

            <!-- Quick Actions Card -->
            <div class="clean-card">
                <h2>Quick Actions</h2>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="students.php" class="btn-quick-action">
                        <span style="display: flex; align-items: center; gap: 10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: var(--blue-500);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Manage Students
                        </span>
                        <span style="color: var(--slate-400);">&rarr;</span>
                    </a>
                    
                    <a href="register_rfid.php" class="btn-quick-action">
                        <span style="display: flex; align-items: center; gap: 10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: var(--blue-500);"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                            Register New RFID
                        </span>
                        <span style="color: var(--slate-400);">&rarr;</span>
                    </a>

                    <a href="reports.php" class="btn-quick-action">
                        <span style="display: flex; align-items: center; gap: 10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: var(--blue-500);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Export Reports
                        </span>
                        <span style="color: var(--slate-400);">&rarr;</span>
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
// Live System Clock Function
function updateClock() {
    const now = new Date();
    let h = now.getHours();
    let m = now.getMinutes();
    let s = now.getSeconds();
    const ampm = h >= 12 ? 'PM' : 'AM';
    
    h = h % 12;
    h = h ? h : 12; // the hour '0' should be '12'
    m = m < 10 ? '0' + m : m;
    s = s < 10 ? '0' + s : s;
    
    document.getElementById('liveClock').textContent = h + ':' + m + ':' + s + ' ' + ampm;
}
setInterval(updateClock, 1000);
updateClock(); // Initialize immediately

// Chart.js Configuration
const ctx = document.getElementById('attendanceChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [
            {
                label: 'Present',
                data: <?php echo json_encode($chartPresent); ?>,
                backgroundColor: '#10b981', // emerald-500
                borderRadius: 4,
                barPercentage: 0.6
            },
            {
                label: 'Late',
                data: <?php echo json_encode($chartLate); ?>,
                backgroundColor: '#f59e0b', // amber-500
                borderRadius: 4,
                barPercentage: 0.6
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8 } }
        },
        scales: {
            y: { 
                beginAtZero: true, 
                ticks: { stepSize: 1, color: '#94a3b8' },
                grid: { color: '#f1f5f9', drawBorder: false }
            },
            x: {
                ticks: { color: '#64748b' },
                grid: { display: false, drawBorder: false }
            }
        },
        interaction: { mode: 'index', intersect: false }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>