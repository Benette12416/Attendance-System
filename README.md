# RFID Attendance Monitoring System (PHP + MySQL)

A simple, self-contained RFID-based attendance system. Every card tap is
recorded with date & time, and an SMS notification is sent automatically.

## How it works

- **1st tap of the day** → records **Time In** (marked *Present* or *Late*
  based on your cutoff time) and sends an SMS.
- **2nd tap of the day** → records **Time Out** and sends an SMS.
- **3rd+ tap** → the system tells the reader "already completed today".

The RFID reader is used as a **USB keyboard-emulator** (the common, cheap
kind — no special driver needed). It just types the card's UID + Enter into
whatever text box is focused. The Live Scan Station on the Attendance page
(and the Register RFID page) listen for that Enter key automatically.

## Setup (XAMPP / WAMP / any Apache+MySQL+PHP stack)

1. Copy the `attendance-system` folder into your `htdocs` (XAMPP) or `www` (WAMP) folder.
2. Start Apache and MySQL.
3. Open **phpMyAdmin**, create nothing manually — just go to the **Import** tab
   and import `database/attendance.sql`. This creates the `rfid_attendance`
   database with all tables.
4. Open `includes/config.php` and confirm the DB credentials match your
   MySQL setup (default XAMPP: user `root`, no password).
5. Visit `http://localhost/attendance-system/login.php` in your browser.
   - A default admin account is created automatically on first visit:
     - **Username:** `admin`
     - **Password:** `admin123`
   - Change this password right away in **Settings**.

## Using the system

1. **Students** → add your students (Student ID, name, course, year, section,
   phone number, optional photo).
2. **Register RFID** → select a student from the dropdown, then tap their
   blank card on the reader. The UID gets linked to that student.
3. **Attendance** → the Live Scan Station here is your everyday tap
   station. Tap any registered card to record time in/out.
4. **Reports** → filter by date range / course and export to CSV.
5. **SMS Logs** → see every SMS that was sent (or would have been sent).
6. **Settings** → set your school name, the "late" cutoff time, and your
   SMS provider (Semaphore) API key.

## Setting up real SMS sending (optional)

This project ships wired up for **Semaphore** (https://semaphore.co), a
popular SMS API in the Philippines with a simple pay-as-you-go credit system:

1. Create a free Semaphore account and get your API Key from your dashboard.
2. Go to **Settings** in the system, paste the API key, check
   **Enable SMS Sending**, and save.
3. From then on, every tap-in/tap-out sends a real SMS to the student's
   phone number.

If SMS is left disabled (default), the system still logs every message
under **SMS Logs** with status "Skipped", so you can test the whole flow
without spending any SMS credits. If you prefer a different SMS gateway
(e.g. Twilio, iTexMo, GlobeLabs), only the `sendSMS()` function in
`includes/functions.php` needs to be edited — the rest of the app is
provider-agnostic.

## Folder structure

```
attendance-system/
├── admin/              Admin panel pages (protected, requires login)
│   ├── dashboard.php
│   ├── students.php
│   ├── register_rfid.php
│   ├── attendance.php
│   ├── reports.php
│   ├── sms_logs.php
│   └── settings.php
├── api/                 Endpoints called by JavaScript (no page reload)
│   ├── scan.php          <- the core tap in/out + SMS logic
│   └── register_card.php <- links a scanned UID to a student
├── includes/
│   ├── config.php        <- DB connection, EDIT THIS for your setup
│   ├── auth.php           login guard
│   ├── functions.php      SMS sending, settings, helpers
│   ├── header.php / sidebar.php / footer.php   shared layout
├── assets/
│   ├── css/style.css
│   ├── js/scan.js
│   └── images/            uploaded student photos land here
├── database/attendance.sql
├── login.php
├── logout.php
└── index.php
```

## Notes / things you may want to customize

- **Time zone** is set to `Asia/Manila` in `includes/config.php` — change it
  if you're elsewhere.
- Attendance is **one row per student per day** (`time_in` + `time_out`
  columns), which is the cleanest way to track a proper tap-in/tap-out pair.
- Card UIDs must be unique across students — the register endpoint blocks
  assigning a UID that's already in use.
