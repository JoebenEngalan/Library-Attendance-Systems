<?php
session_start();
error_reporting(0);
include "includes/config.php";
require_once "includes/session_check.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$month = $_GET['month'] ?? date('n');
$year  = $_GET['year'] ?? date('Y');

$monthlyYear = $_GET['year_monthly'] ?? date('Y');  
$currentYear  = date('Y');
?>

<!DOCTYPE html>
<html lang="en"> 
	<?php include('pages/head.php');?>
    <body class="sb-nav-fixed">
		<?php include('pages/nav.php');?>
        <div id="layoutSidenav">
			<?php include('pages/side.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Charts</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="admin.php" style="color: #d63384" >Dashboard</a></li>
                            <li class="breadcrumb-item active">Charts</li>
                        </ol>
                        
                        <!-- MONTHLY ATTENDANCE -->
                        <div class="card mb-4">
                            <!-- HEADER -->
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div class="fw-semibold">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Total Monthly Attendance (<?php echo htmlspecialchars($monthlyYear); ?>)
                                    </div>

                                    <!-- Year Filter -->
                                    <form method="GET" class="d-flex align-items-center gap-2">
                                        <select
                                            name="year_monthly"
                                            class="form-select form-select-sm text-nowrap"
                                            style="min-width: 70px;"
                                            
                                        >
                                            <?php
                                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                                $selected = ($y == $monthlyYear) ? 'selected' : '';
                                                echo "<option value='$y' $selected>$y</option>";
                                            }
                                            ?>
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-1 text-nowrap">
                                            <i class="fa-duotone fa-light fa-filter"></i>
                                            <span class="d-none d-sm-inline">Filter</span>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success d-flex align-items-center gap-1 text-nowrap"
                                            onclick="exportHighResChartPNG({
                                                canvasId: 'myBarChart',
                                                title: 'Total Monthly Attendance (<?php echo htmlspecialchars($monthlyYear); ?>)',
                                                subtitle: 'MonCast Learning Resource Center',
                                                logoPath: 'assets/img/Logo-chrome-192x192.png',
                                                scale: 4
                                            })"
                                        >
                                            <i class="fa-duotone fa-solid fa-file-image"></i>
                                            <span class="d-none d-sm-inline">Export PNG</span>
                                        </button>

                                    </form>
                                </div>
                            </div>

                            <!-- BODY -->
                            <div class="card-body" style="height: 420px;">
                                <canvas id="myBarChart"></canvas>
                            </div>

                            <!-- FOOTER -->
                            <div class="card-footer small text-muted">
                                <i class="fa-regular fa-clock me-1"></i>
                                Updated: <?php echo date("F d, Y h:i A"); ?>
                            </div>
                        </div>

                        <!-- ATTENDANCE BY COURSE -->
                        <div class="card mb-4">
                            <!-- HEADER -->
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div class="fw-semibold">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Attendance by Course
                                    </div>

                                    <form method="GET" class="d-flex align-items-center gap-2">
                                        <select
                                            name="month"
                                            class="form-select form-select-sm w-100 w-md-auto"
                                            aria-label="Select Month"
                                            required
                                        >
                                            <?php
                                            for ($m = 1; $m <= 12; $m++) {
                                                $selected = ($m == $month) ? 'selected' : '';
                                                echo "<option value='$m' $selected>" .
                                                    date('F', mktime(0, 0, 0, $m, 1)) .
                                                "</option>";
                                            }
                                            ?>
                                        </select>

                                        <select name="year" class="form-select form-select-sm" 
                                                style="min-width: 70px;" required>
                                            <?php
                                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                                $selected = ($y == $year) ? 'selected' : '';
                                                echo "<option value='$y' $selected>$y</option>";
                                            }
                                            ?>
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-1 text-nowrap">
                                            <i class="fa-duotone fa-light fa-filter"></i>
                                            <span class="d-none d-sm-inline">Filter</span>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success d-flex align-items-center gap-1 text-nowrap"
                                            onclick="exportHighResChartPNG({
                                                canvasId: 'attendanceBarChart',
                                                title: 'Attendance by Course (<?php echo htmlspecialchars(date('F', mktime(0,0,0,$month,1)) . ' ' . $year); ?>)',
                                                subtitle: 'MonCast Learning Resource Center',
                                                logoPath: 'assets/img/Logo-chrome-192x192.png',
                                                scale: 4
                                            })"> 
                                            <i class="fa-duotone fa-solid fa-file-image"></i>
                                            <span class="d-none d-sm-inline">Export PNG</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- BODY -->
                            <div class="card-body" style="height: 420px;">
                                <canvas id="attendanceBarChart"></canvas>
                            </div>

                            <!-- FOOTER -->
                            <div class="card-footer small text-muted">
                                <i class="fa-regular fa-clock me-1"></i>
                                Updated: <?php echo date("F d, Y h:i A"); ?>
                            </div>
                        </div>

                        <div class="card mb-4">

                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                                    <div class="fw-semibold">
                                        <i class="fas fa-chart-pie me-1"></i>
                                        Attendance Distribution by Course
                                    </div>

                                    <form method="GET" class="d-flex align-items-center gap-2">
                                        <select
                                            name="month"
                                            class="form-select form-select-sm w-100 w-md-auto"
                                            aria-label="Select Month"
                                            required
                                        >
                                            <?php
                                            for ($m = 1; $m <= 12; $m++) {
                                                $selected = ($m == $month) ? 'selected' : '';
                                                echo "<option value='$m' $selected>" .
                                                    date('F', mktime(0, 0, 0, $m, 1)) .
                                                "</option>";
                                            }
                                            ?>
                                        </select>

                                        <select name="year" class="form-select form-select-sm" 
                                                style="min-width: 70px;" required>
                                            <?php
                                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                                $selected = ($y == $year) ? 'selected' : '';
                                                echo "<option value='$y' $selected>$y</option>";
                                            }
                                            ?>
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-1 text-nowrap">
                                            <i class="fa-duotone fa-light fa-filter"></i>
                                            <span class="d-none d-sm-inline">Filter</span>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success d-flex align-items-center gap-1 text-nowrap"
                                            onclick="exportHighResChartPNG({
                                                canvasId: 'attendancePieChart',
                                                title: 'Attendance by Course (<?php echo htmlspecialchars(date('F', mktime(0,0,0,$month,1)) . ' ' . $year); ?>)',
                                                subtitle: 'MonCast Learning Resource Center',
                                                logoPath: 'assets/img/Logo-chrome-192x192.png',
                                                scale: 4
                                            })"> 
                                            <i class="fa-duotone fa-solid fa-file-image"></i>
                                            <span class="d-none d-sm-inline">Export PNG</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            

                            <!-- BODY -->
                            <div class="card-body" style="height: 420px;">
                                <canvas id="attendancePieChart"></canvas>
                            </div>

                            <!-- FOOTER -->
                            <div class="card-footer small text-muted">
                                <i class="fa-regular fa-clock me-1"></i>
                                Updated: <?php echo date("F d, Y h:i A"); ?>
                            </div>
                        </div>

                    </div>
                </main>
                <?php include('pages/footer.php');?>
            </div>
        </div>

        <!-- Charts Function -->
        <script>
            /* monthly_attendance.php */ 
            Chart.defaults.font.family =
            '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
            Chart.defaults.color = '#000000';

            document.addEventListener("DOMContentLoaded", () => {

                fetch(`pages/monthly_attendance.php?year_monthly=<?php echo (int)$monthlyYear; ?>`)
                    .then(response => response.json())
                    .then(result => {

                        const canvas = document.getElementById('myBarChart');
                        if (!canvas || !result.labels) return;

                        const ctx = canvas.getContext('2d');

                        /* Retina scaling */
                        const dpr = window.devicePixelRatio || 1;
                        const rect = canvas.getBoundingClientRect();
                        canvas.width  = rect.width * dpr;
                        canvas.height = rect.height * dpr;
                        ctx.scale(dpr, dpr);

                        if (window.monthlyAttendanceChart instanceof Chart) {
                            window.monthlyAttendanceChart.destroy();
                        }

                        window.monthlyAttendanceChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: result.labels,
                                datasets: [{
                                    label: `Total Monthly Attendance (<?php echo (int)$monthlyYear; ?>)`,
                                    data: result.data,
                                    backgroundColor: '#0d6efd',
                                    borderRadius: 6,
                                    barThickness: 32
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: {
                                            color: '#000',
                                            font: {
                                                size: 13,
                                                weight: 'bold'
                                            },
                                            padding: 12
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: { precision: 0 }
                                    }
                                },
                                plugins: {
                                    legend: { display: false }
                                }
                            },
                            plugins: [{
                                /* VALUES BELOW BARS */
                                id: 'valueBelowBar',
                                afterDatasetsDraw(chart) {
                                    const { ctx } = chart;

                                    chart.data.datasets.forEach((dataset, i) => {
                                        chart.getDatasetMeta(i).data.forEach((bar, index) => {
                                            const value = dataset.data[index];
                                            if (value === 0) return;

                                            ctx.save();
                                            ctx.fillStyle = '#000000';
                                            ctx.font = 'bold 12px Arial';
                                            ctx.textAlign = 'center';
                                            ctx.textBaseline = 'top';

                                            /* Position just below bar */
                                            ctx.fillText(value, bar.x, bar.base + 10);
                                            ctx.restore();
                                        });
                                    });
                                }
                            }]
                        });
                    })
                    .catch(err => console.error("Monthly chart error:", err));
            });

            document.addEventListener("DOMContentLoaded", function () {

            const month = <?php echo json_encode($month); ?>;
            const year  = <?php echo json_encode($year); ?>;

            fetch(`pages/course_attendance_month.php?month=${month}&year=${year}`)
                .then(response => {
                    if (!response.ok) throw new Error("Server error");
                    return response.json();
                })
                .then(result => {

                    if (!result.labels || result.labels.length === 0) return;

                    /* ================= BAR CHART ================= */
                    const barCanvas = document.getElementById("attendanceBarChart");

                    if (barCanvas) {

                        const ctxBar = barCanvas.getContext("2d");

                        if (window.barChart instanceof Chart) {
                            window.barChart.destroy();
                        }

                        window.barChart = new Chart(ctxBar, {
                            type: "bar",
                            data: {
                                labels: result.labels,
                                datasets: [{
                                    label: "Attendance Count",
                                    data: result.data,
                                    backgroundColor: result.colors,
                                    borderRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: {
                                            color: '#000',
                                            font: {
                                                size: 13,
                                                weight: 'bold'
                                            },
                                            padding: 12
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: { precision: 0 }
                                    }
                                },
                                plugins: {
                                    legend: { display: false }
                                }
                            },
                            plugins: [{
                                id: 'valueBelowBar',
                                afterDatasetsDraw(chart) {

                                    const { ctx } = chart;

                                    chart.data.datasets.forEach((dataset, i) => {
                                        chart.getDatasetMeta(i).data.forEach((bar, index) => {

                                            const value = dataset.data[index];
                                            if (!value) return;

                                            ctx.save();
                                            ctx.fillStyle = '#212529';
                                            ctx.font = 'bold 12px Arial';
                                            ctx.textAlign = 'center';
                                            ctx.textBaseline = 'top';
                                            ctx.fillText(value, bar.x, bar.base + 8);
                                            ctx.restore();
                                        });
                                    });
                                }
                            }]
                        });
                    }

                    /* ================= PIE CHART ================= */
                    const pieCanvas = document.getElementById("attendancePieChart");

                    if (pieCanvas) {

                        const ctxPie = pieCanvas.getContext("2d");

                        if (window.pieChart instanceof Chart) {
                            window.pieChart.destroy();
                        }

                        window.pieChart = new Chart(ctxPie, {
                            type: "pie",
                            data: {
                                labels: result.labels,
                                datasets: [{
                                    label: "Total Attendance",
                                    data: result.data,
                                    backgroundColor: result.colors,
                                    borderWidth: 1
                                }],
                                datalabels: {
                                color: '#fff',
                                font: {
                                    size: 22,
                                    weight: 'bold'
                                },
                                anchor: 'center',
                                align: 'center'
                            }
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: "right"
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {

                                                let total =
                                                    context.dataset.data.reduce((a,b)=>a+b,0);

                                                let value = context.raw;
                                                let percent =
                                                    ((value / total) * 100).toFixed(1);

                                                return `${context.label}: ${value} (${percent}%)`;
                                            }
                                        }
                                    }
                                }
                            },
                            plugins: [{
                                id: 'pieValues',
                                afterDatasetsDraw(chart) {

                                    const { ctx } = chart;
                                    const dataset = chart.data.datasets[0];
                                    const meta = chart.getDatasetMeta(0);

                                    meta.data.forEach((slice, i) => {

                                        const value = dataset.data[i];
                                        if (!value) return;

                                        const pos = slice.tooltipPosition();

                                        ctx.save();
                                        ctx.fillStyle = "#ffffff";
                                        ctx.font = "bold 12px Arial";
                                        ctx.textAlign = "center";
                                        ctx.textBaseline = "middle";
                                        ctx.fillText(value, pos.x, pos.y);
                                        ctx.restore();
                                    });
                                }
                            }]
                        });
                    }

                })
                .catch(err => console.error("Chart error:", err));
            });


            /* Export High-Res PNG Function */
            function exportHighResChartPNG({canvasId,title,subtitle,logoPath,
                scale = 1 /* ⭐ 3 = high resolution (2–4 recommended) */ }) 
                {
                const sourceCanvas = document.getElementById(canvasId);
                if (!sourceCanvas) {
                    alert("Canvas not found: " + canvasId);
                    return;
                }
                const headerHeight = 120;

                /* Create export canvas */  
                const exportCanvas = document.createElement("canvas");
                const ctx = exportCanvas.getContext("2d");

                const width = sourceCanvas.width;
                const height = sourceCanvas.height;

                exportCanvas.width = width * scale;
                exportCanvas.height = (height + headerHeight) * scale;

                ctx.scale(scale, scale);

                /* White background */
                ctx.fillStyle = "#ffffff";
                ctx.fillRect(0, 0, width, height + headerHeight);

                const logo = new Image();
                logo.crossOrigin = "anonymous";

                logo.onload = function () {

                    /* Logo */
                    ctx.drawImage(logo, 20, 20, 80, 80);

                    /* Header text */
                    ctx.fillStyle = "#000";
                    ctx.font = "bold 22px Arial";
                    ctx.fillText(title, 120, 55);

                    ctx.font = "16px Arial";
                    ctx.fillText(subtitle, 120, 80);

                    /* Chart */
                    ctx.drawImage(sourceCanvas, 0, headerHeight);

                    /* Export */
                    const link = document.createElement("a");
                    link.download = `${canvasId}_HD.png`;
                    link.href = exportCanvas.toDataURL("image/png", 1.0);
                    link.click();
                };

                logo.onerror = function () {
                    alert("Logo failed to load. Check logo path.");
                };

                logo.src = logoPath;
            }        
        </script>
        <?php include('pages/scripts.php');?>
    </body>
</html>
