<?php
include('../includes/config.php');
session_start();

// Redirect to login if not authenticated
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : 0;
$center_id = isset($_GET['center_id']) ? intval($_GET['center_id']) : 0;

// Build query conditions
$where_conditions = ["1=1"];
if($year > 0) {
    $where_conditions[] = "YEAR(es.submission_date) = $year";
}
if($month > 0) {
    $where_conditions[] = "MONTH(es.submission_date) = $month";
}
if($center_id > 0) {
    $where_conditions[] = "es.center_id = $center_id";
}
$where_clause = implode(' AND ', $where_conditions);

// Get overall statistics
$stats_sql = "SELECT 
    COUNT(DISTINCT u.id) as total_users,
    COUNT(DISTINCT cc.id) as total_centers,
    COUNT(es.id) as total_submissions,
    SUM(es.quantity) as total_devices,
    AVG(es.quantity) as avg_devices_per_submission,
    SUM(CASE WHEN es.status = 'collected' THEN 1 ELSE 0 END) as collected_submissions,
    SUM(CASE WHEN es.status = 'collected' THEN es.quantity ELSE 0 END) as collected_devices
    FROM e_waste_submissions es
    LEFT JOIN users u ON es.user_id = u.id
    LEFT JOIN collection_centers cc ON es.center_id = cc.id
    WHERE $where_clause";

$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Get monthly statistics
$monthly_sql = "SELECT 
    MONTH(es.submission_date) as month,
    COUNT(es.id) as submission_count,
    SUM(es.quantity) as device_count,
    COUNT(DISTINCT es.user_id) as unique_users
    FROM e_waste_submissions es
    WHERE YEAR(es.submission_date) = $year
    GROUP BY MONTH(es.submission_date)
    ORDER BY month";

$monthly_result = $conn->query($monthly_sql);
$monthly_data = [];
while($row = $monthly_result->fetch_assoc()) {
    $monthly_data[$row['month']] = $row;
}

// Get center statistics
$center_sql = "SELECT 
    cc.id,
    cc.name,
    cc.city,
    COUNT(es.id) as submission_count,
    SUM(es.quantity) as device_count,
    AVG(es.quantity) as avg_per_submission
    FROM collection_centers cc
    LEFT JOIN e_waste_submissions es ON cc.id = es.center_id
    WHERE $where_clause
    GROUP BY cc.id, cc.name, cc.city
    ORDER BY device_count DESC";

$center_result = $conn->query($center_sql);

// Get device type statistics
$device_sql = "SELECT 
    es.device_type,
    COUNT(es.id) as submission_count,
    SUM(es.quantity) as device_count,
    AVG(es.quantity) as avg_per_submission
    FROM e_waste_submissions es
    WHERE $where_clause
    GROUP BY es.device_type
    ORDER BY device_count DESC
    LIMIT 10";

$device_result = $conn->query($device_sql);

// Get all centers for filter dropdown
$centers_filter_sql = "SELECT id, name, city FROM collection_centers ORDER BY name";
$centers_filter_result = $conn->query($centers_filter_sql);
?>

<?php include('../includes/header.php'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📈 Admin Reports & Analytics</h2>
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
            <div class="col-md-3">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
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
            <div class="col-md-3">
                <label class="form-label">Collection Center</label>
                <select name="center_id" class="form-select">
                    <option value="0">All Centers</option>
                    <?php while($center = $centers_filter_result->fetch_assoc()): ?>
                        <option value="<?php echo $center['id']; ?>" <?php echo $center_id == $center['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($center['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5>Total Users</h5>
                <h3><?php echo $stats['total_users'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
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
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h5>Collected Rate</h5>
                <h3>
                    <?php 
                    $collected_rate = ($stats['total_submissions'] > 0) 
                        ? round(($stats['collected_submissions'] / $stats['total_submissions']) * 100, 1) 
                        : 0;
                    echo $collected_rate; 
                    ?>%
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Monthly Overview - <?php echo $year; ?></h5>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Device Types</h5>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="deviceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Centers Performance -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Collection Centers Performance</h5>
            </div>
            <div class="card-body">
                <?php if($center_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Center Name</th>
                                    <th>City</th>
                                    <th>Submissions</th>
                                    <th>Total Devices</th>
                                    <th>Avg per Submission</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $max_devices = 0;
                                $center_data = [];
                                while($center = $center_result->fetch_assoc()) {
                                    $center_data[] = $center;
                                    if($center['device_count'] > $max_devices) {
                                        $max_devices = $center['device_count'];
                                    }
                                }
                                
                                foreach($center_data as $center): 
                                    $performance = $max_devices > 0 ? ($center['device_count'] / $max_devices) * 100 : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($center['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($center['city']); ?></td>
                                    <td><?php echo $center['submission_count']; ?></td>
                                    <td><?php echo $center['device_count']; ?></td>
                                    <td><?php echo round($center['avg_per_submission'], 1); ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: <?php echo $performance; ?>%"
                                                 title="<?php echo round($performance, 1); ?>%">
                                                <?php if($performance > 20): ?>
                                                    <?php echo round($performance, 1); ?>%
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No center data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Device Type Breakdown -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Device Type Breakdown</h5>
    </div>
    <div class="card-body">
        <?php if($device_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Device Type</th>
                            <th>Submissions</th>
                            <th>Total Quantity</th>
                            <th>Average per Submission</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_all_devices = $stats['total_devices'] ?? 1;
                        $device_result->data_seek(0);
                        while($device = $device_result->fetch_assoc()): 
                            $percentage = ($device['device_count'] / $total_all_devices) * 100;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($device['device_type']); ?></td>
                            <td><?php echo $device['submission_count']; ?></td>
                            <td><?php echo $device['device_count']; ?></td>
                            <td><?php echo round($device['avg_per_submission'], 1); ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo round($percentage, 1); ?>%</span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No device data available.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Submissions',
            data: [
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <?php echo isset($monthly_data[$m]) ? $monthly_data[$m]['submission_count'] : 0; ?>,
                <?php endfor; ?>
            ],
            borderColor: 'rgba(40, 167, 69, 1)',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 2,
            fill: true
        }, {
            label: 'Devices',
            data: [
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <?php echo isset($monthly_data[$m]) ? $monthly_data[$m]['device_count'] : 0; ?>,
                <?php endfor; ?>
            ],
            borderColor: 'rgba(23, 162, 184, 1)',
            backgroundColor: 'rgba(23, 162, 184, 0.1)',
            borderWidth: 2,
            fill: true
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

// Device Type Chart
<?php
$device_labels = [];
$device_data = [];
$device_colors = [
    '#28a745', '#20c997', '#17a2b8', '#6f42c1', '#e83e8c',
    '#fd7e14', '#ffc107', '#dc3545', '#6610f2', '#6f42c1'
];

$device_result->data_seek(0);
$color_index = 0;
while($device = $device_result->fetch_assoc()) {
    $device_labels[] = $device['device_type'];
    $device_data[] = $device['device_count'];
    $color_index++;
}
?>

const deviceCtx = document.getElementById('deviceChart').getContext('2d');
const deviceChart = new Chart(deviceCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($device_labels); ?>,
        datasets: [{
            label: 'Devices Collected',
            data: <?php echo json_encode($device_data); ?>,
            backgroundColor: <?php echo json_encode(array_slice($device_colors, 0, count($device_labels))); ?>,
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
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>

<?php include('../includes/footer.php'); ?>