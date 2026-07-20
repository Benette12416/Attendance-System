<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?php echo isset($pageTitle) ? h($pageTitle) . ' - RFID Attendance' : 'RFID Attendance System'; ?></title>
<link rel="stylesheet" href="../assets/css/style.css">

<!-- Core Layout & Responsive CSS -->
<style>
    body, html { 
        margin: 0; padding: 0; overflow-x: hidden; font-family: 'Inter', sans-serif; background-color: #f3f4f6;
    }
    
    .layout { 
        display: flex; min-height: 100vh; width: 100%; position: relative; 
    }
    
    /* Main Content Wrapper */
    .main-content {
        flex-grow: 1;
        min-width: 0; /* Prevents flexbox overflow issues */
        display: flex;
        flex-direction: column;
    }
    
    /* Unified Responsive Breakpoint - Matches your sidebar and page content */
    @media (max-width: 1024px) {
        .layout { flex-direction: column; }
    }
</style>
</head>
<body>
<div class="layout">