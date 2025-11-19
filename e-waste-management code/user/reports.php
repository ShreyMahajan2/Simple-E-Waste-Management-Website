<?php
include('../includes/config.php');
session_start();

// Redirect to login if not authenticated
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get filter parameters
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : 0;

// Build query for filtered data
$where_conditions = ["user_id = $user_id"];
if($year > 0) {
    $where_conditions[] = "YEAR(submission_date) = $year";
}
if($month > 0) {
    $where_conditions[] = "MONTH(submission_date) = $month";
}
$where_clause = implode(' AND ', $where_conditions);

// Get user's submission statistics
$stats_sql = "SELECT 
    COUNT(*) as total_submissions,
    SUM(quantity) as total_devices,
    AVG(quantity) as avg_per_submission,
    COUNT(DISTINCT device_type) as unique_device_types,
    SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as collected_count,
    SUM(CASE WHEN status = 'collected' THEN quantity ELSE 0 END) as collected_devices
    FROM e_waste_submissions 
    WHERE $where_clause";

$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Get monthly breakdown for current year
$monthly_sql = "SELECT 
    MONTH(submission_date) as month,
    COUNT(*) as submission_count,
    SUM(quantity) as device_count
    FROM e_waste_submissions 
    WHERE user_id = $user_id AND YEAR(submission_date) = $year
    GROUP BY MONTH(submission_date)
    ORDER BY month";

$monthly_result = $conn->query($monthly_sql);
$monthly_data = [];
while($row = $monthly_result->fetch_assoc()) {
    $monthly_data[$row['month']] = $row;
}

// Get device type distribution
$device_sql = "SELECT 
    device_type,
    COUNT(*) as count,
    SUM(quantity) as total_quantity
    FROM e_waste_submissions 
    WHERE $where_clause
    GROUP BY device_type
    ORDER BY total_quantity DESC";

$device_result = $conn->query($device_sql);

// Prepare device data for chart
$device_labels = [];
$device_data = [];
$device_colors_array = [];
$device_colors = [
    '#28a745', '#20c997', '#17a2b8', '#6f42c1', 
    '#e83e8c', '#fd7e14', '#ffc107', '#dc3545',
    '#6610f2', '#6f42c1', '#e83e8c', '#fd7e14'
];

if($device_result) {
    $color_index = 0;
    while($device = $device_result->fetch_assoc()) {
        $device_labels[] = $device['device_type'];
        $device_data[] = $device['total_quantity'];
        $device_colors_array[] = $device_colors[$color_index % count($device_colors)];
        $color_index++;
    }
    // Reset pointer for later use
    $device_result->data_seek(0);
}
?>

<?php include('../includes/header.php'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📊 My E-Waste Reports</h2>
    <div>
        <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Report Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    <option value="0">All Years</option>
                    <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    <option value="0">All Months</option>
                    <?php 
                    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $month == $m ? 'selected' : ''; ?>>
                            <?php echo $months[$m-1]; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5>Total Submissions</h5>
                <h3><?php echo $stats['total_submissions'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5>Total Devices</h5>
                <h3><?php echo $stats['total_devices'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h5>Device Types</h5>
                <h3><?php echo $stats['unique_device_types'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h5>Collected</h5>
                <h3><?php echo $stats['collected_count'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Chart -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Monthly Submissions for <?php echo $year; ?></h5>
            </div>
            <div class="card-body">
                <?php if($monthly_result->num_rows > 0): ?>
                    <div style="height: 300px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No data available for the selected period.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Device Types</h5>
            </div>
            <div class="card-body">
                <?php if(count($device_data) > 0): ?>
                    <div style="height: 300px;">
                        <canvas id="deviceChart"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No device data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Device Type Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Device Type Breakdown</h5>
    </div>
    <div class="card-body">
        <?php if($device_result && $device_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Device Type</th>
                            <th>Number of Submissions</th>
                            <th>Total Quantity</th>
                            <th>Average per Submission</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $device_result->data_seek(0); // Reset pointer
                        while($device = $device_result->fetch_assoc()): 
                            // Safe division to prevent division by zero
                            $average = $device['count'] > 0 ? round($device['total_quantity'] / $device['count'], 1) : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($device['device_type']); ?></td>
                            <td><?php echo $device['count']; ?></td>
                            <td><?php echo $device['total_quantity']; ?></td>
                            <td><?php echo $average; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No device data available for the selected period.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
<?php if($monthly_result->num_rows > 0): ?>
// Monthly Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Number of Submissions',
            data: [
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <?php echo isset($monthly_data[$m]) ? $monthly_data[$m]['submission_count'] : 0; ?>,
                <?php endfor; ?>
            ],
            backgroundColor: 'rgba(40, 167, 69, 0.8)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
<?php endif; ?>

<?php if(count($device_data) > 0): ?>
// Device Type Chart
const deviceCtx = document.getElementById('deviceChart').getContext('2d');
const deviceChart = new Chart(deviceCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($device_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($device_data); ?>,
            backgroundColor: <?php echo json_encode($device_colors_array); ?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>
</script>

<?php include('../includes/footer.php'); ?>