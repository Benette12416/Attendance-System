<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
/** @var mysqli $conn */
$pageTitle = 'Students';
$message = '';
$messageType = 'success';

// ---------------------------------------------------------
// DELETE
// ---------------------------------------------------------
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM students WHERE id = $id");
    header('Location: students.php?msg=deleted');
    exit;
}

// ---------------------------------------------------------
// ADD / EDIT
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)($_POST['id'] ?? 0);
    $student_id = trim($_POST['student_id'] ?? '');
    $fullname   = trim($_POST['fullname'] ?? '');
    $course     = trim($_POST['course'] ?? '');
    $year       = trim($_POST['year'] ?? '');
    $section    = trim($_POST['section'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    if ($student_id === '' || $fullname === '') {
        $message = 'Student ID and Full Name are required.';
        $messageType = 'danger';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'danger';
    } else {
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE students SET student_id=?, fullname=?, course=?, year=?, section=?, phone=?, email=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'sssssssi', $student_id, $fullname, $course, $year, $section, $phone, $email, $id);
            mysqli_stmt_execute($stmt);
            $message = 'Student updated successfully.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO students (student_id, fullname, course, year, section, phone, email) VALUES (?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'sssssss', $student_id, $fullname, $course, $year, $section, $phone, $email);
            if (!mysqli_stmt_execute($stmt)) {
                $message = 'Error: Student ID may already exist.';
                $messageType = 'danger';
            } else {
                $message = 'Student added successfully.';
            }
        }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $message = 'Student deleted.';
}

// ---------------------------------------------------------
// Edit mode - fetch existing record
// ---------------------------------------------------------
$editStudent = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM students WHERE id = $id");
    $editStudent = mysqli_fetch_assoc($res);
}

// ---------------------------------------------------------
// List all students
// ---------------------------------------------------------
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $searchEsc = mysqli_real_escape_string($conn, $search);
    $students = mysqli_query($conn, "SELECT * FROM students WHERE fullname LIKE '%$searchEsc%' OR student_id LIKE '%$searchEsc%' ORDER BY fullname");
} else {
    $students = mysqli_query($conn, "SELECT * FROM students ORDER BY fullname");
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
        --blue-500: #3b82f6; --blue-600: #2563eb; --red-500:  #ef4444;
    }

    /* GLOBAL FIX: Prevents padding from breaking 100% widths */
    *, *::before, *::after {
        box-sizing: border-box;
    }

    /* Fixed Layout Wrapper - Centered on PC and Mobile */
    .page-content {
        flex: 1;
        min-width: 0;
        width: 100%;
        max-width: 1400px; /* Centers layout beautifully on wide screens */
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        padding: 24px;
    }

    /* Upgraded to CSS Grid to prevent blowout */
    .students-container { 
        display: grid; 
        grid-template-columns: minmax(0, 1fr) 360px; /* Left: Table, Right: Form */
        gap: 32px; 
        align-items: start; 
    }

    .table-section { min-width: 0; width: 100%; }
    .form-section { min-width: 0; width: 100%; position: sticky; top: 24px; }

    /* Ultra-Clean Cards */
    .clean-card {
        background: #ffffff; border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 20px -5px rgba(0,0,0,0.03);
        border: 1px solid var(--slate-100); padding: 28px; margin-bottom: 24px;
        min-width: 0; /* CRITICAL FIX */
        width: 100%;
    }
    .clean-card h2 {
        font-size: 1.15rem; font-weight: 600; color: var(--slate-900);
        margin: 0 0 24px 0; display: flex; align-items: center; gap: 10px;
    }

    /* Search Bar */
    .search-wrapper { display: flex; gap: 12px; margin-bottom: 24px; width: 100%; flex-wrap: wrap; }
    .search-input {
        flex: 1; min-width: 150px;
        background: var(--slate-50) url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%2394a3b8"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>') no-repeat 14px center;
        background-size: 18px 18px; padding: 10px 16px 10px 42px !important;
        border: 1px solid var(--slate-200); border-radius: 10px; font-size: 0.95rem; transition: all 0.2s ease;
    }
    .search-input:focus {
        background-color: #ffffff; border-color: var(--blue-500);
        outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Elegant Table */
    .table-container { width: 100%; overflow-x: auto; padding-bottom: 12px; }
    .table-container::-webkit-scrollbar { height: 8px; }
    .table-container::-webkit-scrollbar-track { background: transparent; }
    .table-container::-webkit-scrollbar-thumb { background-color: var(--slate-200); border-radius: 10px; }
    .table-container::-webkit-scrollbar-thumb:hover { background-color: var(--slate-300); }

    table { width: 100%; border-collapse: collapse; min-width: 500px; }
    th {
        color: var(--slate-400); font-weight: 600; font-size: 0.7rem;
        text-transform: uppercase; letter-spacing: 0.08em; padding: 12px 16px;
        text-align: left; border-bottom: 1px solid var(--slate-100);
    }
    td { padding: 16px; border-bottom: 1px solid var(--slate-50); color: var(--slate-700); vertical-align: middle; }
    tbody tr { transition: background-color 0.15s ease; }
    tbody tr:hover { background-color: var(--slate-50); }

    /* Clickable Name Styling */
    .student-name-link {
        font-weight: 600; color: var(--blue-600); cursor: pointer; text-decoration: none;
        display: inline-block; transition: color 0.2s;
    }
    .student-name-link:hover { color: var(--blue-500); text-decoration: underline; }

    /* Action Tags */
    .action-tag {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 10px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 600;
        text-decoration: none; transition: all 0.2s ease; border: none; cursor: pointer;
    }
    .tag-edit { background-color: #eff6ff; color: var(--blue-600); }
    .tag-edit:hover { background-color: #dbeafe; }
    .tag-delete { background-color: #fef2f2; color: var(--red-500); }
    .tag-delete:hover { background-color: #fee2e2; }
    
    .actions-flex { display: flex; gap: 8px; }

    /* Form Fields */
    .form-group { margin-bottom: 20px; width: 100%; }
    .form-row { display: flex; gap: 16px; width: 100%; }
    .form-row .form-group { flex: 1; margin-bottom: 20px; min-width: 0; }
    label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--slate-700); margin-bottom: 8px; }
    .clean-input {
        width: 100%; padding: 11px 14px; border: 1px solid var(--slate-200);
        border-radius: 10px; font-size: 0.95rem; color: var(--slate-900); background-color: #ffffff; transition: all 0.2s ease;
    }
    .clean-input:focus { outline: none; border-color: var(--blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    
    .btn-solid {
        display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 12px;
        border-radius: 10px; font-size: 0.9rem; font-weight: 600; color: #ffffff; background-color: var(--blue-600);
        border: none; cursor: pointer; transition: all 0.2s ease;
    }
    .btn-solid:hover { background-color: var(--blue-500); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
    
    .clean-alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 12px; font-size: 0.95rem; }
    .alert-success { background-color: #ecfdf5; color: #047857; }
    .alert-danger { background-color: #fef2f2; color: #b91c1c; }

    /* MODERN STUDENT PROFILE MODAL */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(15, 23, 42, 0.5); backdrop-filter: blur(6px);
        z-index: 2000; display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    
    .modal-container {
        background: #ffffff; width: 100%; max-width: 440px; border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transform: scale(0.95) translateY(20px); opacity: 0;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex; flex-direction: column; overflow: hidden;
    }
    .modal-overlay.active .modal-container { transform: scale(1) translateY(0); opacity: 1; }
    
    /* Profile Header */
    .modal-header-profile {
        background: linear-gradient(135deg, var(--slate-50) 0%, #ffffff 100%);
        padding: 32px 24px 24px; text-align: center; border-bottom: 1px solid var(--slate-100);
        position: relative;
    }
    .modal-close-btn {
        position: absolute; top: 16px; right: 16px; background: var(--slate-100); border: none;
        color: var(--slate-500); width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: all 0.2s; font-size: 1.2rem;
    }
    .modal-close-btn:hover { background: var(--slate-200); color: var(--slate-900); }
    
    .profile-avatar {
        width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 16px;
        background: linear-gradient(135deg, var(--blue-500) 0%, var(--blue-600) 100%);
        color: white; font-size: 1.75rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
    }
    .profile-name { font-size: 1.25rem; font-weight: 700; color: var(--slate-900); margin: 0 0 4px; }
    .profile-id { font-size: 0.9rem; font-family: monospace; color: var(--slate-500); background: var(--slate-100); padding: 4px 10px; border-radius: 99px; display: inline-block; }
    
    /* Body & Data Rows */
    .modal-body { padding: 24px; }
    .data-list { display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px; }
    .data-row { display: flex; align-items: center; gap: 16px; }
    .data-icon {
        width: 40px; height: 40px; border-radius: 12px; background: var(--slate-50);
        display: flex; align-items: center; justify-content: center; color: var(--blue-500);
    }
    .data-text { display: flex; flex-direction: column; }
    .data-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 600; color: var(--slate-400); letter-spacing: 0.05em; }
    .data-value { font-size: 0.95rem; font-weight: 600; color: var(--slate-800); }
    
    /* Modern RFID Copy Box */
    .rfid-copy-box {
        background: var(--slate-900); border-radius: 16px; padding: 16px;
        display: flex; align-items: center; justify-content: space-between;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .rfid-copy-box .uid-wrapper { display: flex; flex-direction: column; }
    .rfid-copy-box .uid-label { font-size: 0.7rem; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px; }
    .rfid-copy-box .uid-text { font-family: monospace; font-size: 1.1rem; color: #ffffff; font-weight: 700; letter-spacing: 0.1em; }
    
    .btn-copy {
        background: rgba(255,255,255,0.1); border: none; padding: 8px 14px;
        border-radius: 10px; font-size: 0.8rem; font-weight: 600; color: #ffffff;
        cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px;
    }
    .btn-copy:hover { background: rgba(255,255,255,0.2); }
    .btn-copy.copied { background: #10b981; color: #ffffff; }

    /* Status Badges */
    .status-badge {
        display: inline-flex; align-items: center; padding: 6px 12px;
        border-radius: 9999px; font-size: 0.75rem; font-weight: 600;
    }
    .badge-active { background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .badge-none { background-color: var(--slate-100); color: var(--slate-500); }

    /* Responsive Design Constraints */
    @media (max-width: 1024px) {
        .students-container { 
            grid-template-columns: minmax(0, 1fr); 
            gap: 24px;
        }
        .form-section { position: static; width: 100%; }
        .form-section.is-editing { order: -1; } /* Brings edit form to top when editing on mobile */
    }
    
    @media (max-width: 640px) {
        .page-content { padding: 16px; }
        .clean-card { padding: 20px; }
        .form-row { flex-direction: column; gap: 0; }
        .search-wrapper { flex-direction: column; align-items: stretch; }
        .search-input { min-width: 0; width: 100%; }
        .action-tag { width: 100%; padding: 12px; text-align: center; } /* Makes buttons easier to tap on mobile */
        
        /* Mobile Bottom Sheet Modal Override */
        .modal-overlay { align-items: flex-end; padding: 0; }
        .modal-container {
            width: 100%; max-width: 100%; border-radius: 28px 28px 0 0;
            transform: translateY(100%) scale(1); opacity: 1; margin: 0;
            transition: transform 0.4s cubic-bezier(0.32, 0.72, 0, 1);
            padding-bottom: env(safe-area-inset-bottom);
        }
        .modal-overlay.active .modal-container { transform: translateY(0) scale(1); }
    }
</style>

<!-- MAIN CONTENT WRAPPER -->
<div class="page-content">

    <!-- Header Section -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--slate-900); margin: 0 0 4px 0;">Student Directory</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">Manage records and RFID assignments.</p>
    </div>

    <?php if ($message): ?>
        <div class="clean-alert alert-<?php echo $messageType; ?>">
            <?php echo h($message); ?>
        </div>
    <?php endif; ?>

    <div class="students-container">
        
        <!-- LEFT: Data Table -->
        <div class="table-section">
            <div class="clean-card">
                
                <form method="GET" class="search-wrapper">
                    <input type="text" name="search" class="search-input" placeholder="Search by name or ID..." value="<?php echo h($search); ?>">
                    <?php if($search): ?>
                        <a href="students.php" class="action-tag tag-edit" style="padding: 10px 16px; border-radius: 10px;">Clear</a>
                    <?php endif; ?>
                    <button type="submit" class="action-tag" style="background:var(--slate-800); color:white; padding: 10px 20px; border-radius: 10px;">Search</button>
                </form>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>RFID Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($students) === 0): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 60px 0; color: var(--slate-400);">
                                    No students found matching your criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php while ($s = mysqli_fetch_assoc($students)): 
                            $isEditingRow = ($editStudent && $editStudent['id'] == $s['id']); 
                            $rfidText = $s['rfid_uid'] ? h($s['rfid_uid']) : 'Unregistered';
                        ?>
                            <tr style="<?php echo $isEditingRow ? 'background-color: var(--slate-50); box-shadow: inset 3px 0 0 var(--blue-500);' : ''; ?>">
                                <td style="font-family: monospace; font-size: 0.9rem; color: var(--slate-500);">
                                    <?php echo h($s['student_id']); ?>
                                </td>
                                <td>
                                    <!-- Clickable Name opens the Modal -->
                                    <span class="student-name-link" 
                                          data-id="<?php echo h($s['student_id']); ?>"
                                          data-name="<?php echo h($s['fullname']); ?>"
                                          data-course="<?php echo h($s['course']); ?>"
                                          data-year="<?php echo h($s['year']); ?>"
                                          data-section="<?php echo h($s['section']); ?>"
                                          data-phone="<?php echo h($s['phone']); ?>"
                                          data-email="<?php echo h($s['email']); ?>"
                                          data-rfid="<?php echo $rfidText; ?>"
                                          onclick="openStudentModal(this)">
                                        <?php echo h($s['fullname']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($s['rfid_uid']): ?>
                                        <span class="status-badge badge-active">Linked</span>
                                    <?php else: ?>
                                        <span class="status-badge badge-none">Unlinked</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions-flex">
                                        <a class="action-tag tag-edit" href="students.php?edit=<?php echo $s['id']; ?>">Edit</a>
                                        <?php if(!$isEditingRow): ?>
                                            <a class="action-tag tag-delete" href="students.php?delete=<?php echo $s['id']; ?>" onclick="return confirm('Delete this record entirely?');">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT: Form -->
        <div class="form-section <?php echo $editStudent ? 'is-editing' : ''; ?>">
            <div class="clean-card" style="border-top: 4px solid var(--blue-500);">
                <h2>
                    <?php echo $editStudent ? 'Edit Student Details' : 'Register New Student'; ?>
                </h2>
                
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $editStudent['id'] ?? ''; ?>">
                    
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" class="clean-input" required value="<?php echo h($editStudent['student_id'] ?? ''); ?>" placeholder="2023-0001">
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" class="clean-input" required value="<?php echo h($editStudent['fullname'] ?? ''); ?>" placeholder="Juan Dela Cruz">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Course</label>
                            <input type="text" name="course" class="clean-input" value="<?php echo h($editStudent['course'] ?? ''); ?>" placeholder="BSIT">
                        </div>
                        <div class="form-group">
                            <label>Year</label>
                            <input type="text" name="year" class="clean-input" value="<?php echo h($editStudent['year'] ?? ''); ?>" placeholder="3">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Section</label>
                            <input type="text" name="section" class="clean-input" value="<?php echo h($editStudent['section'] ?? ''); ?>" placeholder="A">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="clean-input" value="<?php echo h($editStudent['phone'] ?? ''); ?>" placeholder="09XXXXXXXXX">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="clean-input" value="<?php echo h($editStudent['email'] ?? ''); ?>" placeholder="student@example.com">
                    </div>
                    
                    <div style="margin-top: 28px;">
                        <button type="submit" class="btn-solid">
                            <?php echo $editStudent ? 'Save Changes' : '+ Add Student'; ?>
                        </button>
                        
                        <?php if ($editStudent): ?>
                            <div style="text-align: center; margin-top: 12px;">
                                <a href="students.php" style="color: var(--slate-500); font-size: 0.85rem; font-weight: 500; text-decoration: none;">Cancel Editing</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div> 
<!-- END OF MAIN CONTENT WRAPPER -->


<!-- ==========================================
     MODERN STUDENT PROFILE MODAL
     ========================================== -->
<div class="modal-overlay" id="studentInfoModal" onclick="closeModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        
        <!-- Profile Header -->
        <div class="modal-header-profile">
            <button class="modal-close-btn" onclick="closeModal(event)">&times;</button>
            <div class="profile-avatar" id="modal-avatar">--</div>
            <h3 class="profile-name" id="modal-name">Student Name</h3>
            <span class="profile-id" id="modal-id">0000-0000</span>
        </div>
        
        <div class="modal-body">
            <!-- Data Rows -->
            <div class="data-list">
                <div class="data-row">
                    <div class="data-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z" /><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div class="data-text">
                        <span class="data-label">Program & Section</span>
                        <span class="data-value" id="modal-course-info">-</span>
                    </div>
                </div>
                
                <div class="data-row">
                    <div class="data-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    </div>
                    <div class="data-text">
                        <span class="data-label">Phone Number</span>
                        <span class="data-value" id="modal-phone">-</span>
                    </div>
                </div>

                <div class="data-row">
                    <div class="data-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <div class="data-text">
                        <span class="data-label">Email Address</span>
                        <span class="data-value" id="modal-email">-</span>
                    </div>
                </div>
            </div>
            
            <!-- RFID Tech Box -->
            <div class="rfid-copy-box">
                <div class="uid-wrapper">
                    <span class="uid-label">RFID UID</span>
                    <span class="uid-text" id="modal-rfid">Unregistered</span>
                </div>
                <button class="btn-copy" id="copyRfidBtn" onclick="copyRFID()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    <span id="copy-text">Copy</span>
                </button>
            </div>
        </div>
        
    </div>
</div>

<!-- Scripts for Modal & Copy Feature -->
<script>
function openStudentModal(element) {
    const name = element.getAttribute('data-name') || '-';
    const id = element.getAttribute('data-id') || '-';
    const course = element.getAttribute('data-course') || '';
    const year = element.getAttribute('data-year') || '';
    const section = element.getAttribute('data-section') || '';
    const phone = element.getAttribute('data-phone') || '-';
    const email = element.getAttribute('data-email') || '-';
    const rfid = element.getAttribute('data-rfid') || 'Unregistered';

    let initials = '--';
    if(name !== '-') {
        const parts = name.trim().split(' ');
        if(parts.length >= 2) {
            initials = (parts[0][0] + parts[parts.length-1][0]).toUpperCase();
        } else if (parts.length === 1) {
            initials = parts[0].substring(0, 2).toUpperCase();
        }
    }

    let courseInfo = 'Not specified';
    if(course || year || section) {
        courseInfo = `${course} ${year}${section ? '-' + section : ''}`.trim();
    }

    document.getElementById('modal-avatar').innerText = initials;
    document.getElementById('modal-name').innerText = name;
    document.getElementById('modal-id').innerText = id;
    document.getElementById('modal-course-info').innerText = courseInfo;
    document.getElementById('modal-phone').innerText = phone || 'Not specified';
    document.getElementById('modal-email').innerText = email || 'Not specified';
    document.getElementById('modal-rfid').innerText = rfid;
    
    const copyBtn = document.getElementById('copyRfidBtn');
    const copyText = document.getElementById('copy-text');
    copyBtn.classList.remove('copied');
    copyText.innerText = 'Copy';
    
    if(rfid === 'Unregistered' || rfid === '') {
        copyBtn.style.opacity = '0.3';
        copyBtn.style.pointerEvents = 'none';
    } else {
        copyBtn.style.opacity = '1';
        copyBtn.style.pointerEvents = 'auto';
    }

    document.getElementById('studentInfoModal').classList.add('active');
}

function closeModal(event) {
    if(event) event.preventDefault();
    document.getElementById('studentInfoModal').classList.remove('active');
}

function copyRFID() {
    const rfidText = document.getElementById('modal-rfid').innerText;
    
    if(rfidText && rfidText !== 'Unregistered') {
        navigator.clipboard.writeText(rfidText).then(() => {
            const btn = document.getElementById('copyRfidBtn');
            const txt = document.getElementById('copy-text');
            
            btn.classList.add('copied');
            txt.innerText = 'Copied!';
            
            setTimeout(() => {
                btn.classList.remove('copied');
                txt.innerText = 'Copy';
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>