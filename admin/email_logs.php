<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

global $conn;
$pageTitle = "Email Delivery Report";

// Fetch Email Logs with their delivery status
$sql = "
    SELECT e.created_at, s.fullname, s.email, e.subject, e.status
    FROM email_logs e
    LEFT JOIN students s ON s.id = e.student_id
    ORDER BY e.created_at DESC
";
$result = mysqli_query($conn, $sql);

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<style>
    :root {
        --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0; 
        --slate-300: #cbd5e1; --slate-400: #94a3b8; --slate-500: #64748b; 
        --slate-700: #334155; --slate-800: #1e293b; --slate-900: #0f172a;
        --blue-500: #3b82f6; --blue-600: #2563eb; 
        --emerald-600: #059669; --red-600: #dc2626;
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
        max-width: 1200px; /* Keeps it readable on ultra-wide screens */
        margin: 0 auto;
    }

    /* Modern Glass-Inspired Card */
    .clean-card { 
        background: #ffffff; 
        border-radius: 16px; 
        padding: 28px; 
        border: 1px solid var(--slate-100); 
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 20px -5px rgba(0,0,0,0.03);
        min-width: 0; /* CRITICAL FIX: Stops card from stretching past screen width */
    }

    /* Table styling for a clean look with horizontal scroll */
    .table-container { 
        width: 100%; 
        overflow-x: auto; 
        padding-bottom: 12px; 
    }
    .table-container::-webkit-scrollbar { height: 8px; }
    .table-container::-webkit-scrollbar-track { background: transparent; }
    .table-container::-webkit-scrollbar-thumb { background-color: var(--slate-200); border-radius: 10px; }
    
    table { 
        width: 100%; 
        border-collapse: collapse; 
        min-width: 700px; /* Forces scroll instead of blowout on mobile */
    }
    th { 
        padding: 12px 16px; 
        text-align: left; 
        color: var(--slate-400); 
        font-size: 0.7rem; 
        text-transform: uppercase; 
        letter-spacing: 0.08em; 
        border-bottom: 1px solid var(--slate-100); 
        font-weight: 600;
    }
    td { 
        padding: 16px; 
        border-bottom: 1px solid var(--slate-50); 
        color: var(--slate-900); 
        font-size: 0.9rem; 
        vertical-align: middle; 
    }
    tbody tr:hover { background-color: var(--slate-50); }
    
    /* Elegant Badges */
    .badge { 
        display: inline-flex; 
        align-items: center; 
        padding: 4px 10px; 
        border-radius: 9999px; 
        font-size: 0.75rem; 
        font-weight: 600; 
    }
    .badge-success { background: #ecfdf5; color: var(--emerald-600); }
    .badge-failed { background: #fef2f2; color: var(--red-600); }
    
    /* Responsive adjustment */
    @media (max-width: 640px) {
        .page-content { padding: 16px; }
        .clean-card { padding: 20px; }
    }
</style>

<div class="page-content">
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--slate-900); margin: 0 0 4px 0;">Email Delivery Report</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">A comprehensive audit log of all system-generated email notifications.</p>
    </div>

    <div class="clean-card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Recipient Name</th>
                        <th>Email Address</th>
                        <th>Subject</th>
                        <th>Delivery Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $statusClass = (strtolower($row['status']) === 'success') ? 'badge-success' : 'badge-failed';
                    ?>
                    <tr>
                        <td style="font-family: monospace; color: var(--slate-500); font-size: 0.85rem;">
                            <?= h(date('M d, Y h:i A', strtotime($row['created_at']))) ?>
                        </td>
                        <td style="font-weight: 600;">
                            <?= h($row['fullname'] ?? 'N/A') ?>
                        </td>
                        <td style="color: var(--slate-700);">
                            <?= h($row['email'] ?? 'N/A') ?>
                        </td>
                        <td style="color: var(--slate-700);">
                            <?= h($row['subject']) ?>
                        </td>
                        <td>
                            <span class="badge <?= $statusClass ?>"><?= h($row['status']) ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>