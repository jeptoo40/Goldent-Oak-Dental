<?php
session_start();
require_once __DIR__ . '/db.php'; 


if (!isset($_SESSION['user_id'])) {
    header('Location: ../Admin.html');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$fullname = $user ? $user['fullname'] : 'User';


try {
    $todaysAppointments = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status IN ('pending','confirmed')")->fetchColumn();
    $mobileClinicBookings = (int)$pdo->query("SELECT COUNT(*) FROM mobile_clinic_bookings")->fetchColumn();

    $totalPatients = (int)$pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $patientMessages = (int)$pdo->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()")->fetchColumn();

} catch (PDOException $e) {

    error_log("Dashboard stats error: " . $e->getMessage());
    $todaysAppointments = $mobileClinicBookings = $totalPatients = $patientMessages = 0;
}


$chartData = ['months' => [], 'totals' => []];
try {
    $chartQ = $pdo->query("
        SELECT 
            MONTH(appointment_date) AS month_num,
            DATE_FORMAT(MIN(appointment_date), '%b') AS month_name,
            COUNT(*) AS total
        FROM appointments
        WHERE YEAR(appointment_date) = YEAR(CURDATE())
        GROUP BY MONTH(appointment_date)
        ORDER BY MONTH(appointment_date)
    ");
    while ($r = $chartQ->fetch(PDO::FETCH_ASSOC)) {
        $chartData['months'][] = $r['month_name'];
        $chartData['totals'][] = (int)$r['total'];
    }
} catch (PDOException $e) {
    error_log("Chart data error: " . $e->getMessage());
    $chartData = ['months' => [], 'totals' => []];
}





$stmt = $pdo->prepare("SELECT fullname, profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$fullname = $user['fullname'] ?? 'Admin';
$profile_image = $user['profile_image'] ?? 'profile.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Golden Oak — Dashboard</title>

 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --sidebar-width: 250px;
            --right-width: 320px;
            --primary-blue: #002b5c;
            --accent-blue: #0d6efd;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f4f6f9;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        }

  
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary-blue);
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        .sidebar .logo img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid #fff;
            object-fit: cover;
            background: #fff;
            display:block;
            margin: 0 auto 8px;
        }
        .sidebar h4 { text-align:center; margin-bottom: 8px; font-weight:700; }
        .sidebar nav { margin-top: 8px; }
        .sidebar a {
            color: #fff;
            display:block;
            padding: 8px 10px;
            border-radius: 8px;
            margin-bottom:6px;
            text-decoration:none;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.08);
        }
        .sidebar .logout {
            margin-top: auto;
        }

    
        .right-sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: var(--right-width);
            height: 100vh;
            background: #fff;
            border-left: 1px solid #e6e9ee;
            padding: 20px;
            overflow-y: auto;
            z-index: 900;
        }

        
        .main-wrapper {
            margin-left: calc(var(--sidebar-width) + 20px);
            margin-right: calc(var(--right-width) + 20px);
            padding: 24px;
            min-height: 100vh;
        }

        .welcome-header {
            background: var(--accent-blue);
            color: #fff;
            padding: 18px;
            border-radius: 10px;
            display:flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

      
        .stats-grid .card {
            border-radius: 10px;
        }

        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap:6px; }
        .calendar-cell { width: 100%; height:36px; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:0.85rem; box-sizing: border-box; }
        .calendar-cell.today { background: var(--accent-blue); color: #fff; font-weight:700; }

     
        .card .card-body i { display:block; margin-bottom: 6px; }
        /* SPA visibility control */
        #spaContent { display: none; }
        .spa-active .main-wrapper #homeContent { display: none !important; }
        .spa-active .main-wrapper #spaContent { display: block !important; }
        /* Strong guard: when SPA active, hide everything in main except spaContent */
        .spa-active .main-wrapper > *:not(#spaContent) { display: none !important; }
        /* Fade transitions */
        #homeContent, #spaContent { transition: opacity .2s ease-in-out; }
        /* Prevent stat text overflow */
        .stats-grid .card-body { overflow-wrap: anywhere; word-break: break-word; }
        .stats-grid h4 { font-size: 1.5rem; }
        @media (max-width: 768px) {
            .stats-grid h4 { font-size: 1.25rem; }
            .stats-grid h6 { font-size: 0.95rem; }
        }
        /* Make chart card compact */
        .chart-card .card-body { padding: 12px; }
        .chart-card canvas { max-height: 160px !important; }
        @media (max-width: 1200px) {
            .right-sidebar {
                position: relative;
                width: 100%;
                height: auto;
                border-left: none;
                margin-top: 16px;
            }
            .main-wrapper { margin-right: 0; margin-left: calc(var(--sidebar-width) + 12px); }
        }
        @media (max-width: 768px) {
            .sidebar { display:none; }
            .main-wrapper { margin-left: 0; margin-right: 0; padding: 12px; }
            .right-sidebar { display:none; }
            .welcome-header { flex-direction: column; gap: 8px; align-items: flex-start; }
            .calendar-grid { grid-template-columns: repeat(7, minmax(28px, 1fr)); }
            .calendar-cell { width: 100%; height: 28px; font-size: 0.75rem; }
        }


        .footer {
  position: relative;
  bottom: 0;
  width: 100%;
  font-size: 0.9rem;
  letter-spacing: 0.3px;
}
.footer a:hover {
  color: #ffeb3b !important;
  color: dark;
}

        /* Mobile-only top bar and menu */
        .mobile-topbar { display: none; }
        .mobile-menu { display: none; }
        .mobile-only { display: none; }
        @media (max-width: 768px) {
            .mobile-topbar { display: block; background: #fff; padding: 10px 12px; border-bottom: 1px solid #e6e9ee; }
            .mobile-topbar .brand { display:flex; align-items:center; justify-content: space-between; }
            .mobile-topbar .brand .logo { display:flex; align-items:center; gap:8px; }
            .mobile-topbar .brand img.logo-img { width:40px; height:40px; border-radius: 50%; object-fit:cover; background:#fff; }
            .mobile-topbar .brand img.avatar { width:40px; height:40px; border-radius: 50%; object-fit:cover; }
            .mobile-topbar .mobile-menu-toggle { padding-top: 6px; }
            .mobile-topbar .mobile-menu-toggle .menu-toggle-link { display:inline-block; text-decoration:none; color: var(--primary-blue); font-size: 22px; }
            .mobile-topbar .mobile-menu-toggle .menu-toggle-link:focus { outline: none; }
            .mobile-menu { display: block; background: #f9fafb; padding: 8px 12px; border-bottom: 1px solid #e6e9ee; }
            .mobile-menu .nav a { font-size: 0.95rem; }
            .mobile-only { display: block; }
        }
    </style>
</head>
<body>

    <!-- Mobile top bar (logo left, profile right) -->
    <div class="mobile-topbar mobile-only">
        <div class="brand">
            <div class="logo">
                <img class="logo-img" src="../images/logo-removebg-preview.png" alt="Golden Oak Logo">
                <strong>Goldent Oak</strong>
            </div>
            <img class="avatar" src="../images/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile">
        </div>
        <!-- Menu icon below the company icon -->
        <div class="mobile-menu-toggle">
            <a class="menu-toggle-link" data-bs-toggle="collapse" href="#mobileNav" aria-expanded="false" aria-controls="mobileNav" aria-label="Main Menu">
                <i class="fa fa-bars"></i>
            </a>
        </div>
    </div>
    <!-- Mobile menu under top bar -->
    <div class="mobile-menu mobile-only">
        <div class="collapse mt-2" id="mobileNav">
            <nav class="nav nav-pills flex-column">
                <a class="nav-link active" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="appointments.php">Appointments</a>
                <a class="nav-link" href="patients.php">Patients</a>
                <a class="nav-link" href="lab_cases.php">Lab Cases</a>
                <a class="nav-link" href="invoices.php">Invoices</a>
                <a class="nav-link" href="files.php">Files/Reports</a>
            </nav>
        </div>
    </div>

    <aside class="sidebar">
        <div class="logo">
            <img src="../images/logo-removebg-preview.png" alt="Golden Oak Logo">
        </div>
        <h4>Goldent Oak</h4>
        <nav>
            <a href="dashboard.php" class="active"><i class="fa fa-home me-2"></i>Dashboard</a>
            <a href="appointments.php"><i class="fa fa-calendar me-2"></i>Appointments</a>
            <a href="patients.php"><i class="fa fa-users me-2"></i>Patients</a>
            <a href="lab_cases.php"><i class="fa fa-flask me-2"></i>Lab Cases</a>
            <a href="invoices.php"><i class="fa fa-file-invoice-dollar me-2"></i>Invoices</a>
            <a href="files.php"><i class="fa fa-folder-open me-2"></i>Files/Repots</a>
        </nav>

        <div class="logout mt-3">
        <a href="logout.php" class="btn btn-light text-primary w-100 hover-red">
  <i class="fa fa-sign-out-alt me-2"></i> Logout
</a>

    </aside>

  
    <main class="main-wrapper">

        <div id="homeContent">
        <!-- Welcome -->
        <div class="welcome-header">
            <div>
                <h5 class="mb-0">Welcome back, <strong><?php echo htmlspecialchars($fullname); ?></strong> 👋</h5>
                <small style="opacity:.85">Here’s what’s happening today in your clinic</small>
            </div>
            <div><i class="fa fa-tooth fa-2x"></i></div>
        </div>

        <!-- Mobile: Upcoming appointments and actions (calendar moved near footer) -->
        <section class="mobile-only mb-3">
            <div class="card shadow-sm mb-3">
                <div class="card-body p-3">
                    <h6 class="mb-2 text-primary">Upcoming Appointments</h6>
                    <?php
            try {
                $up = $pdo->query("\n                    SELECT a.appointment_date, a.time_slot, a.status, p.first_name, p.last_name, p.email\n                    FROM appointments a\n                    JOIN patients p ON a.patient_id = p.id\n                    WHERE a.appointment_date >= CURDATE()\n                      AND a.status IN ('pending','confirmed')\n                    ORDER BY a.appointment_date ASC, a.time_slot ASC\n                    LIMIT 3\n                ");
                        if ($up && $up->rowCount() > 0) {
                            while ($row = $up->fetch(PDO::FETCH_ASSOC)) {
                                echo '<div class="card mb-2"><div class="card-body p-2">';
                                echo '<strong>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</strong>';
                                echo '<div class="small text-muted">' . date('M d, Y', strtotime($row['appointment_date'])) . ' • ' . htmlspecialchars($row['time_slot']) . '</div>';
                                echo '<div class="small text-secondary">' . htmlspecialchars($row['email']) . '</div>';
                                echo '</div></div>';
                            }
                        } else {
                            echo '<p class="text-muted mb-0">No upcoming appointments.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p class="text-danger mb-0">Error loading appointments.</p>';
                    }
                    ?>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="patients.php?open=add" class="btn btn-primary btn-sm spa-link"><i class="fa fa-user-plus me-1"></i> Add Patient</a>
                <a href="appointments.php" class="btn btn-success btn-sm spa-link"><i class="fa fa-calendar-plus me-1"></i> Reschedule Appointment</a>
            </div>
        </section>
        </div>

        <!-- SPA content container -->
        <div id="spaContent" style="display:none"></div>

        <section class="stats-grid mb-4">
            <div class="row g-3">
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fa fa-calendar-check fa-2x text-primary"></i>
                            <h6 class="mt-2">Today's Appointments</h6>
                            <h4 class="mb-0"><?php echo htmlspecialchars($todaysAppointments); ?></h4>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fa fa-truck-medical fa-2x text-warning"></i>
                            <h6 class="mt-2">Mobile Clinic Bookings</h6>
                            <h4 class="mb-0"><?php echo htmlspecialchars($mobileClinicBookings); ?></h4>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fa fa-users fa-2x text-success"></i>
                            <h6 class="mt-2">Total Patients</h6>
                            <h4 class="mb-0"><?php echo htmlspecialchars($totalPatients); ?></h4>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center position-relative" style="cursor:pointer">
                            <i class="fa fa-envelope fa-2x text-danger"></i>
                            <h6 class="mt-2">Messages Today</h6>
                            <h4 class="mb-0"><?php echo htmlspecialchars($patientMessages); ?></h4>
                            <a href="messages.php" class="stretched-link spa-link" aria-label="View messages"></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    
        <section class="mb-4">
            <div class="card shadow-sm chart-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Appointment Statistics (This Year)</h5>
                        <small class="text-muted">Monthly totals</small>
                    </div>
                    <canvas id="appointmentsChart" height="130"></canvas>
                </div>
            </div>
        </section>

        <section class="mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fa fa-user me-2"></i>Latest Registered Patients</h6>
                    <a href="patients.php" class="btn btn-sm btn-light text-dark spa-link">View All</a>
                </div>
                <div class="card-body">
                    <?php
                        try {
                            $lp = $pdo->query("SELECT id, first_name, last_name, email, phone, created_at FROM patients ORDER BY created_at DESC LIMIT 5");
                            if ($lp && $lp->rowCount() > 0) {
                                echo '<div class="table-responsive"><table class="table table-striped align-middle mb-0"><thead class="table-primary"><tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Registered</th></tr></thead><tbody>';
                                while ($p = $lp->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($p['id']) . '</td>';
                                    echo '<td>' . htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($p['email']) . '</td>';
                                    echo '<td>' . htmlspecialchars($p['phone']) . '</td>';
                                    echo '<td>' . htmlspecialchars(date('M d, Y', strtotime($p['created_at']))) . '</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody></table></div>';
                            } else {
                                echo '<p class="text-muted mb-0">No patients found.</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p class="text-danger">Error loading patients.</p>';
                        }
                    ?>
                </div>
            </div>
        </section>

    </main>


    <aside class="right-sidebar">
        <div class="text-center mb-3">
        <img src="../images/<?php echo htmlspecialchars($profile_image); ?>" 
     alt="Profile" class="rounded-circle mb-2" width="80" height="80">

<h6 class="mb-0"><?php echo htmlspecialchars($fullname); ?></h6>
<small class="text-muted">Administrator</small>

        </div>

        <div class="d-grid gap-2 mb-3">
            <a href="patients.php?open=add" class="btn btn-primary btn-sm spa-link"><i class="fa fa-user-plus me-1"></i> Add Patient</a>
            <a href="appointments.php" class="btn btn-success btn-sm spa-link"><i class="fa fa-calendar-plus me-1"></i> Reschedule Appointment</a>
        </div>

        
        <div class="card mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <button id="prevMonth" class="btn btn-sm btn-outline-primary">&lt;</button>
                    <h6 id="monthYear" class="mb-0 text-primary"></h6>
                    <button id="nextMonth" class="btn btn-sm btn-outline-primary">&gt;</button>
                </div>
                <div class="calendar-grid text-center mb-2">
                    <div class="small text-muted">Sun</div><div class="small text-muted">Mon</div><div class="small text-muted">Tue</div><div class="small text-muted">Wed</div><div class="small text-muted">Thu</div><div class="small text-muted">Fri</div><div class="small text-muted">Sat</div>
                </div>
                <div id="calendarDays" class="calendar-grid"></div>
            </div>
        </div>

     
        <div>
            <h6 class="text-primary">Upcoming Appointments</h6>
            <?php
            try {
                $up = $pdo->query("
                    SELECT a.appointment_date, a.time_slot, p.first_name, p.last_name, p.email
                    FROM appointments a
                    JOIN patients p ON a.patient_id = p.id
                    WHERE a.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                    ORDER BY a.appointment_date ASC
                    LIMIT 6
                ");
                if ($up && $up->rowCount() > 0) {
                    while ($row = $up->fetch(PDO::FETCH_ASSOC)) {
                        echo '<div class="card mb-2"><div class="card-body p-2">';
                        echo '<strong>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</strong>';
                        echo '<div class="small text-muted">' . date('M d, Y', strtotime($row['appointment_date'])) . ' • ' . htmlspecialchars($row['time_slot']) . '</div>';
            
                        echo '<div class="small text-secondary">' . htmlspecialchars($row['email']) . '</div>';
                        echo '</div></div>';
                    }
                } else {
                    echo '<p class="text-muted">No upcoming appointments.</p>';
                }
            } catch (PDOException $e) {
                echo '<p class="text-danger">Error loading appointments.</p>';
            }
            ?>
        </div>
</aside>

    <!-- Mobile calendar placed second-last before footer -->
    <section class="mobile-only mt-3">
        <div class="card shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <button id="mPrevMonth" class="btn btn-sm btn-outline-primary">&lt;</button>
                    <h6 id="mMonthYear" class="mb-0 text-primary"></h6>
                    <button id="mNextMonth" class="btn btn-sm btn-outline-primary">&gt;</button>
                </div>
                <div class="calendar-grid text-center mb-2">
                    <div class="small text-muted">Sun</div><div class="small text-muted">Mon</div><div class="small text-muted">Tue</div><div class="small text-muted">Wed</div><div class="small text-muted">Thu</div><div class="small text-muted">Fri</div><div class="small text-muted">Sat</div>
                </div>
                <div id="mCalendarDays" class="calendar-grid"></div>
            </div>
        </div>
    </section>


<footer class="footer text-center text-white mt-5 py-3" 
        style="
            background-color: #001f3f; 
            border-top-left-radius: 20px; 
            border-top-right-radius: 20px; 
            position: relative;
            bottom: 0;
            width: 100%;
        ">
    
    <div class="container">
        <hr class="border-light my-3">

        <p class="mb-0 small">
            &copy; <?php echo date('Y'); ?> <strong>Golden Oak Dental Clinic</strong>. All rights reserved |
            <a href="https://vebrasolutions.fwh.is" target="_blank" 
               style="color: #13aac4; text-decoration: none;">
               Designs by vebrasolutions.fwh.is
            </a>
        </p>
    </div>
</footer>




    <script>
 
    document.addEventListener('DOMContentLoaded', function () {
        const monthYear = document.getElementById('monthYear');
        const calendarDays = document.getElementById('calendarDays');
        const prevMonth = document.getElementById('prevMonth');
        const nextMonth = document.getElementById('nextMonth');
        let date = new Date();

        function renderMonth() {
            const year = date.getFullYear();
            const month = date.getMonth();
            const firstDay = new Date(year, month, 1).getDay();
            const days = new Date(year, month + 1, 0).getDate();
            const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
            monthYear.textContent = monthNames[month] + ' ' + year;

            calendarDays.innerHTML = '';
            // blanks
            for (let i = 0; i < firstDay; i++) calendarDays.innerHTML += '<div></div>';
            for (let d = 1; d <= days; d++) {
                const dt = new Date(year, month, d);
                const isToday = dt.toDateString() === (new Date()).toDateString();
                const cell = document.createElement('div');
                cell.className = 'calendar-cell' + (isToday ? ' today' : '');
                cell.textContent = d;
                calendarDays.appendChild(cell);
            }
        }

        prevMonth.addEventListener('click', function(){ date.setMonth(date.getMonth() - 1); renderMonth(); });
        nextMonth.addEventListener('click', function(){ date.setMonth(date.getMonth() + 1); renderMonth(); });
        renderMonth();
    });
    </script>

    <script>
    // Chart
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('appointmentsChart').getContext('2d');
        const months = <?php echo json_encode($chartData['months']); ?>;
        const totals = <?php echo json_encode($chartData['totals']); ?>;

        if (!months || months.length === 0) {
            //  placeholder text
            ctx.canvas.parentNode.insertAdjacentHTML('afterbegin', '<p class="text-muted">No appointment data for this year.</p>');
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Appointments',
                    data: totals,
                    backgroundColor: '#3b82f6',
                    borderColor: '#1d4ed8',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 3,
                scales: {
                    x: {
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 12,
                            font: { size: 10 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Count' },
                        ticks: { precision: 0, font: { size: 10 } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Appointments by Month' }
                }
            }
        });
    });
    </script>

    <script>
    // Simple SPA loader: load pages into #spaContent without full navigation
    (function(){
        const home = document.getElementById('homeContent');
        const spa = document.getElementById('spaContent');
        const mobileNavEl = document.getElementById('mobileNav');

        function setActive(link){
            document.querySelectorAll('.sidebar nav a, .mobile-menu nav a').forEach(a=>a.classList.remove('active'));
            if (link) link.classList.add('active');
        }
        function findLinkByHref(url){
            return document.querySelector(`.sidebar nav a[href="${url}"]`) || document.querySelector(`.mobile-menu nav a[href="${url}"]`);
        }
        function collapseMobile(){
            if (!mobileNavEl) return;
            try {
                const c = bootstrap.Collapse.getOrCreateInstance(mobileNavEl);
                c.hide();
            } catch(_) {}
        }
        function showHome(){
            if (spa) { spa.style.display = 'none'; spa.innerHTML=''; }
            if (home) { home.style.display = ''; }
            collapseMobile();
            document.body.classList.remove('spa-active');
            try { history.replaceState({spa:false}, '', '#!home'); } catch(_) {}
            try { localStorage.setItem('spaLast', 'home'); } catch(_) {}
        }
        async function loadSpa(url, link, replace=false){
            document.body.classList.add('spa-active');
            if (home) home.style.display = 'none';
            if (spa) {
                spa.style.display = '';
                spa.style.opacity = 0;
                spa.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
            }
            try {
                const res = await fetch(url, { credentials:'same-origin' });
                const html = await res.text();
                const match = html.match(/<body[\s\S]*?>([\s\S]*?)<\/body>/i);
                const content = match ? match[1] : html;
                if (spa) {
                    spa.innerHTML = content;
                    // Execute scripts contained in the loaded content
                    spa.querySelectorAll('script').forEach(old => {
                        const s = document.createElement('script');
                        if (old.src) { s.src = old.src; } else { s.textContent = old.textContent; }
                        document.body.appendChild(s);
                        old.remove();
                    });
                    requestAnimationFrame(()=>{ spa.style.opacity = 1; });
                }
                setActive(link);
                collapseMobile();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                try {
                    const hash = '#!' + encodeURIComponent(url);
                    if (replace) history.replaceState({spa:true,url}, '', hash); else history.pushState({spa:true,url}, '', hash);
                    localStorage.setItem('spaLast', url);
                } catch(_) {}
            } catch (e) {
                if (spa) spa.innerHTML = '<div class="alert alert-danger">Failed to load content.</div>';
                console.error('SPA load error', e);
            }
        }

        // Delegate clicks so it always works
        document.addEventListener('click', function(ev){
            const a = ev.target.closest('.sidebar nav a, .mobile-menu nav a, a.spa-link');
            if (!a) return;
            const href = a.getAttribute('href');
            if (!href || href === '#') { ev.preventDefault(); return; }
            if (href.indexOf('dashboard.php') !== -1) { ev.preventDefault(); showHome(); setActive(a); return; }
            if (href.startsWith('http')) return; // allow external
            ev.preventDefault();
            loadSpa(href, a);
        });

        // Expose for manual triggers if needed
        window._spaLoad = loadSpa;
        window._spaShowHome = showHome;

        // Initial route from hash or last view
        document.addEventListener('DOMContentLoaded', function(){
            const h = location.hash || '';
            if (h.startsWith('#!')) {
                const url = decodeURIComponent(h.slice(2));
                if (url && url !== 'home') {
                    const link = findLinkByHref(url);
                    loadSpa(url, link, true);
                    return;
                }
            }
            try {
                const last = localStorage.getItem('spaLast');
                if (last && last !== 'home') {
                    const link = findLinkByHref(last);
                    loadSpa(last, link, true);
                }
            } catch(_) {}
        });

        // Back/forward support
        window.addEventListener('popstate', function(ev){
            const state = ev.state || {};
            if (state.spa && state.url) {
                const link = findLinkByHref(state.url);
                loadSpa(state.url, link, true);
            } else {
                showHome();
                const link = findLinkByHref('dashboard.php');
                setActive(link);
            }
        });
    })();
    </script>

    <!-- Bootstrap JS for mobile menu collapse -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Mobile calendar
    document.addEventListener('DOMContentLoaded', function () {
        const mMonthYear = document.getElementById('mMonthYear');
        const mCalendarDays = document.getElementById('mCalendarDays');
        const mPrevMonth = document.getElementById('mPrevMonth');
        const mNextMonth = document.getElementById('mNextMonth');
        if (!mMonthYear || !mCalendarDays) return;

        let mDate = new Date();
        function mRenderMonth() {
            const y = mDate.getFullYear();
            const m = mDate.getMonth();
            const first = new Date(y, m, 1).getDay();
            const days = new Date(y, m + 1, 0).getDate();
            const names = ["January","February","March","April","May","June","July","August","September","October","November","December"];
            mMonthYear.textContent = names[m] + ' ' + y;
            mCalendarDays.innerHTML = '';
            for (let i = 0; i < first; i++) mCalendarDays.innerHTML += '<div></div>';
            for (let d = 1; d <= days; d++) {
                const dt = new Date(y, m, d);
                const isToday = dt.toDateString() === (new Date()).toDateString();
                const cell = document.createElement('div');
                cell.className = 'calendar-cell' + (isToday ? ' today' : '');
                cell.textContent = d;
                mCalendarDays.appendChild(cell);
            }
        }
        mPrevMonth && mPrevMonth.addEventListener('click', function(){ mDate.setMonth(mDate.getMonth() - 1); mRenderMonth(); });
        mNextMonth && mNextMonth.addEventListener('click', function(){ mDate.setMonth(mDate.getMonth() + 1); mRenderMonth(); });
        mRenderMonth();
    });
    </script>

</body>
</html>