<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
/** @var mysqli $conn */
$pageTitle = 'Reports';

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');
$course = trim($_GET['course'] ?? '');

$where = "a.date BETWEEN '" . mysqli_real_escape_string($conn, $from) . "' AND '" . mysqli_real_escape_string($conn, $to) . "'";
if ($course !== '') {
    $where .= " AND s.course = '" . mysqli_real_escape_string($conn, $course) . "'";
}

$query = "
    SELECT a.*, s.fullname, s.student_id AS sid, s.course, s.year, s.section
    FROM attendance a
    JOIN students s ON s.id = a.student_id
    WHERE $where
    ORDER BY a.date DESC, a.time_in DESC
";
$records = mysqli_query($conn, $query);
$courses = mysqli_query($conn, "SELECT DISTINCT course FROM students WHERE course IS NOT NULL AND course != '' ORDER BY course");

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

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
        padding: 24px; 
        display: flex;
        flex-direction: column;
    }

    /* Grid Layout */
    .attendance-layout { 
        display: grid; 
        grid-template-columns: 360px minmax(0, 1fr); /* Prevents table from blowing out grid */
        gap: 24px; 
        align-items: start; 
    }

    /* Ultra-Clean Cards */
    .clean-card { 
        background: #ffffff; 
        border-radius: 16px; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 20px -5px rgba(0,0,0,0.03); 
        border: 1px solid var(--slate-100); 
        padding: 28px; 
        min-width: 0; /* CRITICAL FIX: Stops card from stretching past screen width */
    }
    .clean-card h2 { 
        font-size: 1.15rem; 
        font-weight: 600; 
        color: var(--slate-900); 
        margin: 0 0 20px 0; 
    }

    /* Form & Input Styling */
    .filters-form { display: flex; flex-direction: column; gap: 15px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label { font-size: 0.8rem; font-weight: 600; color: var(--slate-700); }
    
    .clean-input { 
        width: 100%; 
        padding: 10px 14px; 
        border: 1px solid var(--slate-200); 
        border-radius: 10px; 
        font-size: 0.95rem; 
        background-color: #ffffff;
        transition: all 0.2s;
    }
    .clean-input:focus { outline: none; border-color: var(--blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

    /* Action Buttons */
    .btn-solid { 
        width: 100%; padding: 12px 20px; border-radius: 10px; 
        background: var(--blue-600); color: #fff; border: none; cursor: pointer; 
        font-weight: 600; font-size: 0.9rem; transition: background 0.2s; 
    }
    .btn-solid:hover { background: var(--blue-500); }
    
    .btn-delete { 
        width: 100%; padding: 12px 20px; border-radius: 10px; 
        background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; cursor: pointer; 
        font-weight: 600; font-size: 0.9rem; transition: background 0.2s; 
    }
    .btn-delete:hover { background: #fca5a5; }

    /* Elegant Table with Horizontal Scroll */
    .table-container { width: 100%; overflow-x: auto; padding-bottom: 12px; }
    .table-container::-webkit-scrollbar { height: 8px; }
    .table-container::-webkit-scrollbar-track { background: transparent; }
    .table-container::-webkit-scrollbar-thumb { background-color: var(--slate-200); border-radius: 10px; }
    
    table { width: 100%; border-collapse: collapse; min-width: 600px; }
    th { 
        color: var(--slate-400); font-size: 0.7rem; text-transform: uppercase; 
        padding: 12px 16px; border-bottom: 1px solid var(--slate-100); 
        text-align: left; font-weight: 600; letter-spacing: 0.08em; 
    }
    td { 
        padding: 16px; border-bottom: 1px solid var(--slate-50); 
        color: var(--slate-700); font-size: 0.9rem; vertical-align: middle; 
    }
    tbody tr:hover { background-color: var(--slate-50); }

    /* Action Tag inside Table */
    .action-box { 
        display: inline-block; background-color: #fef2f2; border: 1px solid #fecaca; 
        border-radius: 8px; padding: 6px 12px; transition: all 0.2s; cursor: pointer; 
    }
    .action-box:hover { background-color: #fee2e2; border-color: #f87171; }
    .delete-action { color: #ef4444; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; }

    /* Responsive Constraints */
    @media (max-width: 1024px) { 
        .attendance-layout { grid-template-columns: minmax(0, 1fr); } 
    }
    @media (max-width: 640px) {
        .page-content { padding: 16px; }
        .clean-card { padding: 20px; }
    }
</style>

<div class="page-content">
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--slate-900); margin: 0 0 4px 0;">Reports</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">Attendance summary and management.</p>
    </div>

    <div class="attendance-layout">
        <div class="clean-card">
            <h2>Actions</h2>
            <form method="GET" class="filters-form">
                <div class="form-group">
                    <label>From</label>
                    <input type="date" name="from" class="clean-input" value="<?php echo h($from); ?>">
                </div>
                <div class="form-group">
                    <label>To</label>
                    <input type="date" name="to" class="clean-input" value="<?php echo h($to); ?>">
                </div>
                <button type="submit" class="btn-solid">Filter Report</button>
                <button type="button" onclick="deleteAll()" class="btn-delete">Delete All Records</button>
            </form>
        </div>

        <div class="clean-card">
            <h2>Attendance Log</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($records) === 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px 0; color: var(--slate-400);">
                                No attendance records found for this period.
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php while ($r = mysqli_fetch_assoc($records)): ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--slate-500); font-size: 0.85rem;"><?php echo h($r['date']); ?></td>
                            <td style="font-weight:600; color: var(--slate-900);"><?php echo h($r['fullname']); ?></td>
                            <td style="font-weight: 500; color: var(--slate-700);"><?php echo formatTime($r['time_in']); ?></td>
                            <td style="font-weight: 500; color: var(--slate-700);"><?php echo formatTime($r['time_out']); ?></td>
                            <td>
                                <div class="action-box" onclick="deleteRecord(<?php echo $r['id']; ?>)">
                                    <span class="delete-action">Delete</span>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function deleteRecord(id) { 
    if(confirm('Delete record?')) {
        fetch('../api/delete_attendance.php', { 
            method: 'POST', 
            body: JSON.stringify({ action: 'delete', id: id }), 
            headers: {'Content-Type': 'application/json'} 
        }).then(() => location.reload()); 
    }
}

function deleteAll() { 
    if(confirm('WARNING: Delete ALL?')) {
        fetch('../api/delete_attendance.php', { 
            method: 'POST', 
            body: JSON.stringify({ action: 'delete_all' }), 
            headers: {'Content-Type': 'application/json'} 
        }).then(() => location.reload()); 
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>