<?php $current = basename($_SERVER['PHP_SELF']); ?>
<!-- Modern Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0; 
        --slate-300: #cbd5e1; --slate-400: #94a3b8; --slate-500: #64748b; 
        --slate-700: #334155; --slate-800: #1e293b; --slate-900: #0f172a;
        --blue-500: #3b82f6; --blue-600: #2563eb; --red-500:  #ef4444;
    }

    /* ---------------------------------------------------
       MOBILE TOP HEADER (Matches your image)
       --------------------------------------------------- */
    .mobile-header {
        display: none; /* Hidden on PC */
        background-color: var(--slate-900);
        color: #ffffff;
        padding: 16px 20px;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 999;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .mobile-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        font-size: 1.15rem;
    }

    .hamburger-btn {
        background: none;
        border: none;
        color: #ffffff;
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ---------------------------------------------------
       MODERN SIDEBAR DRAWER
       --------------------------------------------------- */
    .sidebar-overlay {
        display: none;
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);
        z-index: 1040; opacity: 0; transition: opacity 0.3s ease;
    }

    .sidebar {
        background-color: #ffffff; /* Clean white modern sidebar */
        font-family: 'Inter', sans-serif;
        display: flex;
        flex-direction: column;
        border-right: 1px solid var(--slate-200);
        height: 100vh;
        width: 260px;
        flex-shrink: 0;
        z-index: 1050;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 4px 0 24px rgba(0,0,0,0.02);
    }

    /* Sidebar Brand inside Drawer */
    .sidebar-brand-drawer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px 20px;
        border-bottom: 1px solid var(--slate-100);
        margin-bottom: 16px;
    }

    .sidebar-brand-drawer .brand-text {
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -0.025em;
        color: var(--slate-900);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Close Button (Mobile Only) */
    .close-sidebar-btn {
        display: none;
        background: var(--slate-100);
        border: none;
        color: var(--slate-500);
        width: 32px; height: 32px;
        border-radius: 8px;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .close-sidebar-btn:hover { background: #fee2e2; color: var(--red-500); }

    /* Navigation Links */
    .sidebar-nav {
        display: flex; flex-direction: column; gap: 4px;
        flex-grow: 1; padding: 0 12px 24px; overflow-y: auto;
    }
    
    .sidebar-nav::-webkit-scrollbar { width: 4px; }
    .sidebar-nav::-webkit-scrollbar-thumb { background: var(--slate-200); border-radius: 4px; }

    .sidebar-nav a {
        display: flex; align-items: center; padding: 12px 16px;
        border-radius: 12px; color: var(--slate-500); text-decoration: none;
        font-size: 0.95rem; font-weight: 600; transition: all 0.2s ease;
    }

    .sidebar-nav a svg {
        width: 20px; height: 20px; margin-right: 12px;
        stroke: var(--slate-400); transition: all 0.2s ease;
    }

    /* Hover State */
    .sidebar-nav a:hover {
        background-color: var(--slate-50);
        color: var(--slate-800);
    }
    .sidebar-nav a:hover svg { stroke: var(--slate-700); }

    /* Active State (Blue Pill) */
    .sidebar-nav a.active {
        background-color: #eff6ff;
        color: var(--blue-600);
    }
    .sidebar-nav a.active svg { stroke: var(--blue-600); }

    /* Spacer above Logout - grows to push logout to the bottom when there's
       room, but never shrinks below 1 inch so there's always real separation
       from Settings. */
    .sidebar-nav-spacer {
        flex-grow: 0;
        min-height: 0px; /* 1 inch at 96dpi */
    }

    /* Logout Link */
    .sidebar-nav a.logout-link {
        color: var(--red-500);
    }
    .sidebar-nav a.logout-link svg { stroke: var(--red-500); }
    .sidebar-nav a.logout-link:hover { background-color: #fef2f2; }

    /* ---------------------------------------------------
       RESPONSIVE BEHAVIOR
       --------------------------------------------------- */
    
    /* Mobile / Tablet */
    @media (max-width: 1024px) {
        .mobile-header {
            display: flex; /* Show the dark top bar */
        }
        
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            transform: translateX(-100%); /* Hidden off-screen */
        }
        
        .sidebar.open { transform: translateX(0); }
        .sidebar-overlay.active { display: block; opacity: 1; }
        .close-sidebar-btn { display: flex; }
    }
    
    /* Desktop */
    @media (min-width: 1025px) {
        .sidebar {
            position: sticky;
            top: 0;
            transform: translateX(0) !important;
        }
        .sidebar-overlay { display: none !important; }
    }
</style>

<!-- MOBILE TOP HEADER (Visible only on small screens) -->
<div class="mobile-header">
    <div class="mobile-brand">
        RFID Attendance System
    </div>
    <button class="hamburger-btn" onclick="toggleSidebar()">
        <!-- Hamburger Icon matching image_a6ace8.png -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
</div>

<!-- SIDEBAR OVERLAY (For closing sidebar when clicking outside on mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- MAIN SIDEBAR DRAWER -->
<aside class="sidebar" id="mainSidebar">
    <div class="sidebar-brand-drawer">
        <div class="brand-text">
            RFID Attendance System
        </div>
        <!-- Mobile Close Button -->
        <button class="close-sidebar-btn" onclick="toggleSidebar()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo $current === 'dashboard.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            Dashboard
        </a>
        <a href="students.php" class="<?php echo $current === 'students.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            Students
        </a>
        <a href="register_rfid.php" class="<?php echo $current === 'register_rfid.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
            Register RFID
        </a>
        <a href="attendance.php" class="<?php echo $current === 'attendance.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
            Attendance
        </a>
        <a href="reports.php" class="<?php echo $current === 'reports.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            Reports
        </a>
        <a href="email_logs.php" class="<?php echo $current === 'email_logs.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            Email Logs
        </a>
        <a href="settings.php" class="<?php echo $current === 'settings.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            Settings
        </a>

        <div class="sidebar-nav-spacer"></div>

        <a href="../logout.php" class="logout-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            Logout
        </a>
    </nav>
</aside>

<!-- JavaScript to Handle Menu Toggling -->
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Toggle the 'open' and 'active' classes
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
    
    // Lock body scrolling when mobile menu is open
    if (sidebar.classList.contains('open')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
}
</script>