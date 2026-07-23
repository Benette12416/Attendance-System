<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$pageTitle = 'Register RFID';

if (!isset($conn)) {
    die('Database connection not initialized.');
}

$students = mysqli_query($conn, "SELECT id, student_id, fullname, rfid_uid FROM students ORDER BY fullname");

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

    /* Fixed Layout Wrapper */
    .page-content {
        flex: 1;
        min-width: 0;
        width: 100%;
        display: flex;
        flex-direction: column;
        padding: 24px;
        box-sizing: border-box;
    }

    /* Ultra-Clean Card */
    .clean-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 10px 20px -5px rgba(0,0,0,0.03);
        border: 1px solid var(--slate-100);
        padding: 32px;
        max-width: 500px; /* Constrain width for a better form appearance */
    }

    /* Form Styling */
    .form-group { margin-bottom: 24px; }
    label { 
        display: block; 
        font-size: 0.85rem; 
        font-weight: 600; 
        color: var(--slate-700); 
        margin-bottom: 8px; 
    }
    
    .clean-select {
        width: 100%; 
        padding: 12px 14px; 
        border: 1px solid var(--slate-200);
        border-radius: 10px; 
        font-size: 0.95rem; 
        color: var(--slate-900); 
        background-color: #ffffff; 
        transition: all 0.2s ease;
        appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%2394a3b8"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>');
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 16px;
        box-sizing: border-box;
    }
    .clean-select:focus { 
        outline: none; 
        border-color: var(--blue-500); 
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); 
    }

    /* Modern Scan Box */
    .scan-box {
        background-color: #eff6ff; /* Very light blue */
        border: 2px dashed #93c5fd; /* Soft blue dashed border */
        border-radius: 16px;
        padding: 32px 24px;
        text-align: center;
        transition: all 0.3s ease;
    }
    .scan-box:focus-within {
        background-color: #e0f2fe;
        border-color: var(--blue-500);
    }
    
    .scan-title {
        margin: 0 0 16px 0; 
        font-weight: 700; 
        color: var(--blue-600);
        font-size: 1.1rem;
    }

    .scan-input {
        width: 100%;
        max-width: 280px;
        padding: 12px 16px;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        font-size: 1rem;
        text-align: center;
        color: var(--slate-900);
        background-color: #ffffff;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }
    .scan-input:focus {
        outline: none;
        border-color: var(--blue-500);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .scan-input::placeholder { color: var(--slate-400); }

    .scan-result {
        margin-top: 16px;
        font-size: 0.9rem;
        font-weight: 500;
        min-height: 20px;
    }

    /* Responsive Design */
    @media (max-width: 640px) {
        .page-content { padding: 16px; }
        .clean-card { padding: 24px; border-radius: 12px; }
        .scan-box { padding: 24px 16px; }
    }
</style>

<!-- MAIN CONTENT WRAPPER -->
<div class="page-content">

    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--slate-900); margin: 0 0 4px 0;">Register RFID Card</h1>
        <p style="color: var(--slate-500); font-size: 0.95rem; margin: 0;">Select a student, then tap their card on the reader to link it.</p>
    </div>

    <div class="clean-card">
        <div class="form-group">
            <label>Select Student</label>
            <select id="studentSelect" class="clean-select">
                <option value="">-- Choose a student --</option>
                <?php while ($s = mysqli_fetch_assoc($students)): ?>
                    <option value="<?php echo $s['id']; ?>">
                        <?php echo h($s['student_id']) . ' - ' . h($s['fullname']); ?>
                        <?php echo $s['rfid_uid'] ? ' (currently: ' . h($s['rfid_uid']) . ')' : ''; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="scan-box">
            <p class="scan-title">Tap the RFID card now</p>
            <input type="text" id="scanInput" class="scan-input" placeholder="Waiting for RFID to scan..." autocomplete="off" inputmode="none">
            <div id="scanResult" class="scan-result" style="color: var(--slate-500);">Select a student above, then tap a card.</div>
        </div>
    </div>

</div>

<script>
    const scanInput = document.getElementById('scanInput');
    const scanResult = document.getElementById('scanResult');
    const studentSelect = document.getElementById('studentSelect'); // Target the dropdown

    // No auto-focus on page load, and nothing steals focus away from the
    // Select Student dropdown. You tap scanInput yourself when you're ready
    // to scan; inputmode="none" keeps the on-screen mobile keyboard from
    // popping up over it, but the physical/HID RFID reader still types into
    // it normally once it's focused.
    scanInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const rfid = scanInput.value.trim();
            const studentId = studentSelect.value; // Get the selected student ID
            
            scanInput.value = ''; // Clear display

            // Validate that a student is actually selected
            if (!studentId) {
                scanResult.style.color = '#dc2626';
                scanResult.innerText = 'Please select a student first.';
                return;
            }

            if (!rfid) return;

            scanResult.innerText = 'Registering...';
            scanResult.style.color = 'var(--slate-500)';

            fetch('../api/scan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-Key': 'ESP32RFID2026'
                },
                // Send BOTH the uid and the student_id to the server
                body: JSON.stringify({ uid: rfid, student_id: studentId }) 
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
                        scanInput.focus();
                    }
                } catch (err) {
                    scanResult.style.color = '#dc2626';
                    scanResult.innerText = 'Server Error! Press F12 and check Console.';
                    scanInput.focus();
                }
            })
            .catch(error => {
                scanResult.style.color = '#dc2626';
                scanResult.innerText = 'Network Error';
                scanInput.focus();
            });
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>