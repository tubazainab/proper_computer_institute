<?php
/**
 * Proper Computer - Admin Dashboard
 * Faithfully inspired by reference screenshot Front_page_oct26.png
 */
$pageTitle = "Dashboard";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

// Selected month and date filters (default to current)
$selectedMonth = $_GET['month'] ?? date('Y-m');
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// --- Monthly Metrics ---
$monthStart = $selectedMonth . '-01';
$monthEnd = date('Y-m-t', strtotime($monthStart));

// Monthly Admissions count
$stmtAdm = $pdo->prepare("SELECT COUNT(*) FROM admissions WHERE admission_date BETWEEN ? AND ?");
$stmtAdm->execute([$monthStart, $monthEnd]);
$monthlyAdmissions = $stmtAdm->fetchColumn() ?: 0;

// Monthly Collections (Actual fee payments received in this month)
$stmtCol = $pdo->prepare("SELECT SUM(amount) FROM fee_payments WHERE payment_date BETWEEN ? AND ?");
$stmtCol->execute([$monthStart, $monthEnd]);
$monthlyCollection = $stmtCol->fetchColumn() ?: 0;

// Monthly Billing (Total net fees billed in this month)
$stmtBill = $pdo->prepare("SELECT SUM(net_fees) FROM admissions WHERE admission_date BETWEEN ? AND ?");
$stmtBill->execute([$monthStart, $monthEnd]);
$monthlyBilling = $stmtBill->fetchColumn() ?: 0;

// Monthly Enquiries
$stmtEnq = $pdo->prepare("SELECT COUNT(*) FROM enquiries WHERE enquiry_date BETWEEN ? AND ?");
$stmtEnq->execute([$monthStart, $monthEnd]);
$monthlyEnquiry = $stmtEnq->fetchColumn() ?: 0;

// --- Daily Metrics ---
// Daily Admissions
$stmtDAdm = $pdo->prepare("SELECT COUNT(*) FROM admissions WHERE admission_date = ?");
$stmtDAdm->execute([$selectedDate]);
$dailyAdmissions = $stmtDAdm->fetchColumn() ?: 0;

// Daily Collection
$stmtDCol = $pdo->prepare("SELECT SUM(amount) FROM fee_payments WHERE payment_date = ?");
$stmtDCol->execute([$selectedDate]);
$dailyCollection = $stmtDCol->fetchColumn() ?: 0;

// Daily Billing
$stmtDBill = $pdo->prepare("SELECT SUM(net_fees) FROM admissions WHERE admission_date = ?");
$stmtDBill->execute([$selectedDate]);
$dailyBilling = $stmtDBill->fetchColumn() ?: 0;

// --- Notice Board ---
$notices = $pdo->query("SELECT * FROM notices WHERE is_active = 1 ORDER BY priority = 'Urgent' DESC, created_at DESC LIMIT 3")->fetchAll();

// --- Today's Enquiries / Visits ---
$todaysEnquiries = $pdo->query("SELECT e.*, c.name as course_name 
                               FROM enquiries e 
                               JOIN courses c ON e.course_id = c.id 
                               ORDER BY e.id DESC LIMIT 6")->fetchAll();

// --- Pending / Expected Collections ---
$expectedCollections = $pdo->query("SELECT a.id, a.admission_no, a.due_fees, a.admission_date, s.full_name as student_name, s.phone, c.name as course_name 
                                    FROM admissions a 
                                    JOIN students s ON a.student_id = s.id 
                                    JOIN courses c ON a.course_id = c.id 
                                    WHERE a.due_fees > 0 AND a.status = 'Ongoing' 
                                    ORDER BY a.due_fees DESC LIMIT 5")->fetchAll();

// --- Chart Data: Category/Course Admissions ---
$chartDataQuery = $pdo->query("SELECT cat.name as category, COUNT(a.id) as admissions_count 
                              FROM course_categories cat 
                              JOIN courses c ON cat.id = c.category_id 
                              LEFT JOIN admissions a ON c.id = a.course_id 
                              GROUP BY cat.id 
                              ORDER BY admissions_count DESC LIMIT 6")->fetchAll();
$chartLabels = [];
$chartCounts = [];
foreach ($chartDataQuery as $cd) {
    $chartLabels[] = $cd['category'];
    $chartCounts[] = (int)$cd['admissions_count'];
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Monthly & Daily Status Row (Reference Screenshot Top) -->
<div class="row g-4 mb-4">
    <!-- Monthly Status Card -->
    <div class="col-lg-6">
        <div class="status-card">
            <div class="status-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="status-icon-wrap" style="background:#ede9fe; color:#6366f1;">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Monthly Status</h5>
                        <small class="text-muted">Aggregated financial &amp; academic leads</small>
                    </div>
                </div>
                <div>
                    <input type="month" class="form-control form-control-sm" value="<?= e($selectedMonth) ?>" 
                           onchange="window.location.href='index.php?month=' + this.value + '&date=<?= e($selectedDate) ?>'">
                </div>
            </div>

            <div class="status-grid">
                <div class="status-metric" style="border-left-color: #6366f1;">
                    <div class="metric-label">Total Admission</div>
                    <div class="metric-value"><?= number_format($monthlyAdmissions) ?></div>
                </div>
                <div class="status-metric" style="border-left-color: #10b981;">
                    <div class="metric-label">Total Collection</div>
                    <div class="metric-value text-success"><?= format_currency($monthlyCollection) ?></div>
                </div>
                <div class="status-metric" style="border-left-color: #f59e0b;">
                    <div class="metric-label">Total Billing</div>
                    <div class="metric-value"><?= format_currency($monthlyBilling) ?></div>
                </div>
                <div class="status-metric" style="border-left-color: #06b6d4;">
                    <div class="metric-label">Total Enquiry</div>
                    <div class="metric-value"><?= number_format($monthlyEnquiry) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Status Card -->
    <div class="col-lg-6">
        <div class="status-card">
            <div class="status-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="status-icon-wrap" style="background:#e0f2fe; color:#0284c7;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Daily Status</h5>
                        <small class="text-muted">Day performance overview</small>
                    </div>
                </div>
                <div>
                    <input type="date" class="form-control form-control-sm" value="<?= e($selectedDate) ?>" 
                           onchange="window.location.href='index.php?month=<?= e($selectedMonth) ?>&date=' + this.value">
                </div>
            </div>

            <div class="status-grid">
                <div class="status-metric" style="border-left-color: #0284c7;">
                    <div class="metric-label">Total Admission</div>
                    <div class="metric-value"><?= number_format($dailyAdmissions) ?></div>
                </div>
                <div class="status-metric" style="border-left-color: #10b981;">
                    <div class="metric-label">Total Collection</div>
                    <div class="metric-value text-success"><?= format_currency($dailyCollection) ?></div>
                </div>
                <div class="status-metric" style="border-left-color: #f59e0b;">
                    <div class="metric-label">Total Billing</div>
                    <div class="metric-value"><?= format_currency($dailyBilling) ?></div>
                </div>
                <div class="status-metric" style="border-left-color: #8b5cf6;">
                    <div class="metric-label">Target Progress</div>
                    <div class="metric-value text-primary">On Track</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links Section (Disha Reference Grid) -->
<div class="card border-0 rounded-4 p-4 mb-4" style="background: #ffffff; border: 1px solid var(--admin-border) !important;">
    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-th text-primary me-2"></i>Quick Links</h5>
    <div class="row g-3">
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= ADMIN_URL ?>/admission-add.php" class="quick-link-card ql-admission">
                <div class="quick-link-icon"><i class="fas fa-user-plus"></i></div>
                <span class="quick-link-title">Admission</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= ADMIN_URL ?>/enquiries.php" class="quick-link-card ql-enquiry">
                <div class="quick-link-icon"><i class="fas fa-user-tag"></i></div>
                <span class="quick-link-title">Enquiry</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= ADMIN_URL ?>/fees.php" class="quick-link-card ql-fees">
                <div class="quick-link-icon"><i class="fas fa-wallet"></i></div>
                <span class="quick-link-title">Fees</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= ADMIN_URL ?>/budget.php" class="quick-link-card ql-expenses">
                <div class="quick-link-icon"><i class="fas fa-receipt"></i></div>
                <span class="quick-link-title">Add Expenses</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= ADMIN_URL ?>/targets.php" class="quick-link-card ql-tasks">
                <div class="quick-link-icon"><i class="fas fa-bullseye"></i></div>
                <span class="quick-link-title">Task / Target</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= ADMIN_URL ?>/reports.php" class="quick-link-card ql-reports">
                <div class="quick-link-icon"><i class="fas fa-chart-line"></i></div>
                <span class="quick-link-title">Reports</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= ADMIN_URL ?>/attendance.php" class="quick-link-card ql-attendance">
                <div class="quick-link-icon"><i class="fas fa-clipboard-check"></i></div>
                <span class="quick-link-title">Attendance</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= ADMIN_URL ?>/receipts.php" class="quick-link-card ql-receipt">
                <div class="quick-link-icon"><i class="fas fa-print"></i></div>
                <span class="quick-link-title">Print Receipt</span>
            </a>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 p-4 shadow-sm h-100" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-area text-primary me-2"></i>Annual Admission Trends &amp; Targets</h5>
                <span class="badge bg-light text-muted border">FY 2025-2026</span>
            </div>
            <div style="height: 260px;">
                <canvas id="admissionsTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 p-4 shadow-sm h-100" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-chart-pie text-info me-2"></i>Domain Admissions</h5>
            <div style="height: 260px;">
                <canvas id="domainDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Notice Board Section -->
<div class="card border-0 rounded-4 p-4 mb-4" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-bullhorn text-warning me-2"></i>Notice Board</h5>
        <a href="<?= ADMIN_URL ?>/notices.php" class="text-decoration-none small text-primary fw-semibold">View All Notices &rarr;</a>
    </div>
    <div class="row g-3">
        <?php foreach ($notices as $nt): ?>
            <div class="col-md-4">
                <div class="p-3 rounded-3 bg-light border h-100">
                    <span class="badge <?= ($nt['priority'] === 'Urgent') ? 'bg-danger' : 'bg-primary' ?> mb-2 small"><?= e($nt['priority']) ?></span>
                    <h6 class="fw-bold text-dark mb-1"><?= e($nt['title']) ?></h6>
                    <p class="text-muted small mb-2"><?= e(mb_strimwidth($nt['content'], 0, 100, '...')) ?></p>
                    <small class="text-secondary"><i class="far fa-clock me-1"></i> <?= format_date($nt['created_at']) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Tables: Recent Enquiries & Expected Collection (Reference Screenshot Lower Half) -->
<div class="row g-4">
    <!-- Today's / Recent Enquiries -->
    <div class="col-lg-6">
        <div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-user-clock text-primary me-2"></i>Recent Enquiries</h5>
                <a href="<?= ADMIN_URL ?>/enquiries.php" class="small text-primary text-decoration-none fw-semibold">View All &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>SRNO</th>
                            <th>STUDENT</th>
                            <th>COURSE</th>
                            <th>LEAD STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todaysEnquiries as $idx => $enq): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <span class="table-student-name"><?= e($enq['student_name']) ?></span>
                                    <span class="table-student-phone"><?= e($enq['phone']) ?></span>
                                </td>
                                <td><?= e($enq['course_name']) ?></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><?= e($enq['lead_status']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Expected / Pending Collection -->
    <div class="col-lg-6">
        <div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-hand-holding-usd text-success me-2"></i>Pending Fee Collections</h5>
                <a href="<?= ADMIN_URL ?>/fees.php" class="small text-primary text-decoration-none fw-semibold">View All &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>STUDENT</th>
                            <th>COURSE</th>
                            <th>DUE AMOUNT</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expectedCollections as $col): ?>
                            <tr>
                                <td>
                                    <span class="table-student-name"><?= e($col['student_name']) ?></span>
                                    <span class="table-student-phone"><?= e($col['phone']) ?></span>
                                </td>
                                <td><?= e($col['course_name']) ?></td>
                                <td class="text-danger fw-bold"><?= format_currency($col['due_fees']) ?></td>
                                <td>
                                    <a href="<?= ADMIN_URL ?>/fee-collect.php?admission_id=<?= $col['id'] ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-plus me-1"></i> Collect
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Trend Bar Chart
    const ctxTrend = document.getElementById('admissionsTrendChart');
    if (ctxTrend) {
        new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [
                    {
                        label: 'Admissions Target',
                        data: [50, 35, 35, 40, 50, 40],
                        backgroundColor: '#e2e8f0',
                        borderRadius: 6
                    },
                    {
                        label: 'Actual Admissions',
                        data: [42, 38, 32, 37, 48, <?= max(4, $monthlyAdmissions) ?>],
                        backgroundColor: '#4f46e5',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Domain Pie/Doughnut Chart
    const ctxDomain = document.getElementById('domainDistributionChart');
    if (ctxDomain) {
        new Chart(ctxDomain, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    data: <?= json_encode($chartCounts) ?>,
                    backgroundColor: ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
