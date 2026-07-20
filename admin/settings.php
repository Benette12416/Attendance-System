<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
/** @var mysqli $conn */

$pageTitle = 'Settings';
$message = '';
$messageType = 'success';

// Get the user's current IP address for safety
$current_ip = $_SERVER['REMOTE_ADDR'];
// If working locally (localhost), it might return ::1. Let's map it to standard IPv4 localhost format for clarity.
if ($current_ip == '::1') $current_ip = '127.0.0.1';

// ---------------------------------------------------------
// Handle General Settings Update
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $school_name        = trim($_POST['school_name'] ?? '');
    $late_time          = $_POST['late_time'] ?? '08:00';
    $email_enabled      = isset($_POST['email_enabled']) ? 1 : 0;
    $ip_whitelist       = trim($_POST['ip_whitelist'] ?? '');

    $stmt = mysqli_prepare($conn, "UPDATE settings SET school_name=?, late_time=?, email_enabled=?, ip_whitelist=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ssis', $school_name, $late_time, $email_enabled, $ip_whitelist);
    
    if(mysqli_stmt_execute($stmt)) {
        $message = 'Settings saved successfully.';
    } else {
        $message = 'Error saving settings.';
        $messageType = 'danger';
    }
}

// ---------------------------------------------------------
// Handle Change Admin Password
// ---------------------------------------------------------
$pwMessage = '';
$pwMessageType = 'success';
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $res = mysqli_query($conn, "SELECT * FROM admins WHERE id = " . (int)$_SESSION['admin_id']);
    $admin = mysqli_fetch_assoc($res);

    if (!password_verify($current, $admin['password'])) {
        $pwMessage = 'Current password is incorrect.';
        $pwMessageType = 'danger';
    } elseif (strlen($new) < 6) {
        $pwMessage = 'New password must be at least 6 characters.';
        $pwMessageType = 'danger';
    } elseif ($new !== $confirm) {
        $pwMessage = 'New password and confirmation do not match.';
        $pwMessageType = 'danger';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $hash, $_SESSION['admin_id']);
        mysqli_stmt_execute($stmt);
        $pwMessage = 'Password updated successfully.';
    }
}

$settings = getSettings($conn);

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
        --emerald-500: #10b981;
    }

    /* Fixed Layout Wrapper */
    .page-content {
        flex: 1; min-width: 0; width: 100%;
        display: flex; flex-direction: column;
        padding: 24px; box-sizing: border-box;
    }

    /* Grid Layout for Settings */
    .settings-layout {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr; 
        gap: 24px;
        align-items: start;
    }

    /* Ultra-Clean Cards */
    .clean-card {
        background: #ffffff; border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 20px -5px rgba(0,0,0,0.03);
        border: 1px solid var(--slate-100); padding: 32px;
    }
    .clean-card h2 {
        font-size: 1.15rem; font-weight: 600; color: var(--slate-900);
        margin: 0 0 20px 0; border-bottom: 1px solid var(--slate-100); 
        padding-bottom: 12px;
    }

    /* Form Styles */
    .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
    .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--slate-700); }
    .form-group p.help-text { font-size: 0.8rem; color: var(--slate-500); margin: 0; line-height: 1.4; }
    
    .clean-input {
        width: 100%; padding: 12px 14px; border: 1px solid var(--slate-200);
        border-radius: 10px; font-size: 0.95rem; background-color: #ffffff; transition: all 0.2s;
        box-sizing: border-box; color: var(--slate-900);
    }
    .clean-input:focus { outline: none; border-color: var(--blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    textarea.clean-input { resize: vertical; min-height: 80px; font-family: monospace; font-size: 0.85rem; }

    /* Custom Checkbox */
    .custom-checkbox {
        display: flex; align-items: center; gap: 12px;
        background: var(--slate-50); padding: 16px; border-radius: 12px;
        border: 1px solid var(--slate-200); cursor: pointer; transition: all 0.2s;
    }
    .custom-checkbox:hover { border-color: var(--blue-500); background: #eff6ff; }
    .custom-checkbox input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--blue-600); }
    .custom-checkbox span { font-size: 0.95rem; font-weight: 600; color: var(--slate-800); }

    /* Test Email Feature Box */
    .test-email-box {
        background: #f8fafc; border: 1px dashed var(--slate-300);
        border-radius: 12px; padding: 20px; margin-top: 16px;
    }
    .test-email-box h3 { font-size: 0.95rem; font-weight: 600; margin: 0 0 10px 0; color: var(--slate-800); }
    .test-email-flex { display: flex; gap: 10px; }
    
    /* Buttons */
    .btn-solid {
        padding: 12px 24px; border-radius: 10px; font-size: 0.95rem; font-weight: 600;
        color: #ffffff; background-color: var(--blue-600); border: none; cursor: pointer; transition: all 0.2s;
        display: inline-flex; align-items: center; justify-content: center; width: 100%;
    }
    .btn-solid:hover { background-color: var(--blue-500); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
    
    .btn-secondary {
        padding: 12px 16px; border-radius: 10px; font-size: 0.9rem; font-weight: 600;
        color: var(--slate-700); background-color: #ffffff; border: 1px solid var(--slate-300); 
        cursor: pointer; transition: all 0.2s; white-space: nowrap;
    }
    .btn-secondary:hover { background-color: var(--slate-50); border-color: var(--slate-400); }

    /* Alert Boxes */
    .clean-alert { 
        padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; 
        font-weight: 500; display: flex; align-items: center; gap: 12px; font-size: 0.95rem; 
    }
    .alert-success { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .alert-danger { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

    /* Responsive */
    @media (max-width: 1024px) {
        .settings-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .page-content { padding: 16px; }
        .clean-card { padding: 20px; }
        .test-email-flex { flex-direction: column; }
    }
</style>

<!-- MAIN CONTENT WRAPPER -->
<div class="page-content">

    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--slate-900); margin: 0 0 4px 0;">Settings</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">System configuration, email notification, and access control.</p>
    </div>

    <!-- Global Alerts -->
    <?php if ($message): ?>
        <div class="clean-alert alert-<?php echo $messageType; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <?php echo h($message); ?>
        </div>
    <?php endif; ?>

    <div class="settings-layout">
        
        <!-- LEFT COLUMN: General, Email, & Access Settings -->
        <div class="clean-card">
            <form method="POST">
                <h2>General Settings</h2>
                
                <div class="form-group">
                    <label>School / Institution Name</label>
                    <input type="text" name="school_name" class="clean-input" value="<?php echo h($settings['school_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Late Cutoff Time</label>
                    <input type="time" name="late_time" class="clean-input" value="<?php echo substr($settings['late_time'] ?? '08:00', 0, 5); ?>" required>
                    <p class="help-text">Taps recorded after this time will automatically be marked as "Late".</p>
                </div>

                <h2 style="margin-top: 40px;">Email Notification</h2>
                <div class="form-group">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="email_enabled" <?php echo !empty($settings['email_enabled']) ? 'checked' : ''; ?>>
                        <span>Enable Automated Email Sending</span>
                    </label>
                </div>

                <div class="test-email-box">
                    <h3>Diagnostics: Test Email Delivery</h3>
                    <div class="test-email-flex">
                        <input type="email" id="testEmailAddress" class="clean-input" placeholder="Enter your email address...">
                        <button type="button" class="btn-secondary" onclick="sendTestEmail()">Send Test</button>
                    </div>
                    <div id="testEmailStatus" style="margin-top: 10px; font-size: 0.85rem; font-weight: 500; display: none;"></div>
                </div>

                <!-- NEW FEATURE: Access Control -->
                <h2 style="margin-top: 40px;">Access Control (Security)</h2>
                <div class="form-group">
                    <label>IP Whitelist</label>
                    <textarea name="ip_whitelist" class="clean-input" placeholder="e.g. 192.168.1.100, 10.0.0.5"><?php echo h($settings['ip_whitelist'] ?? ''); ?></textarea>
                    <p class="help-text" style="margin-top: 8px;">
                        Enter allowed IP addresses separated by commas. Leave entirely blank to allow access from anywhere.<br>
                        <span style="color: var(--blue-600); font-weight: 500;">Your current IP Address is: <?php echo $current_ip; ?></span>
                    </p>
                </div>

                <div style="margin-top: 32px;">
                    <button type="submit" name="save_settings" class="btn-solid">Save Configuration</button>
                </div>
            </form>
        </div>

        <!-- RIGHT COLUMN: Security / Password -->
        <div class="clean-card">
            <h2>Change Admin Password</h2>
            
            <?php if ($pwMessage): ?>
                <div class="clean-alert alert-<?php echo $pwMessageType; ?>" style="padding: 12px 16px; font-size: 0.85rem;">
                    <?php echo h($pwMessage); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="clean-input" required>
                </div>
                
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="clean-input" required>
                    <p class="help-text">Must be at least 6 characters long.</p>
                </div>
                
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="clean-input" required>
                </div>
                
                <div style="margin-top: 28px;">
                    <button type="submit" name="change_password" class="btn-solid" style="background-color: var(--slate-800);">Update Password</button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
function sendTestEmail() {
    const emailInput = document.getElementById('testEmailAddress');
    const statusText = document.getElementById('testEmailStatus');
    const email = emailInput.value.trim();

    if (!email) {
        statusText.style.display = 'block';
        statusText.style.color = 'var(--red-500)';
        statusText.innerText = 'Please enter an email address first.';
        return;
    }

    statusText.style.display = 'block';
    statusText.style.color = 'var(--slate-500)';
    statusText.innerText = 'Sending test email... please wait.';

    fetch('../api/test_email.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            statusText.style.color = 'var(--emerald-500)';
            statusText.innerText = 'Success! Test email sent to ' + email;
            emailInput.value = '';
        } else {
            statusText.style.color = 'var(--red-500)';
            statusText.innerText = 'Failed to send: ' + (data.message || 'Server mail function error.');
        }
    })
    .catch(err => {
        statusText.style.color = 'var(--red-500)';
        statusText.innerText = 'Error contacting the server. Ensure the API endpoint exists.';
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>