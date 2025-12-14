<?php
require_once '../includes/config.php';
requireRole('admin');

$pageTitle = 'Admin Dashboard';
include '../includes/header.php';

// Get statistics
$stats = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM papers WHERE is_active = 1) as total_papers,
    (SELECT COUNT(*) FROM papers WHERE status = 'submitted' AND is_active = 1) as submitted_papers,
    (SELECT COUNT(*) FROM papers WHERE status = 'under_review' AND is_active = 1) as under_review_papers,
    (SELECT COUNT(*) FROM papers WHERE status = 'accepted' AND is_active = 1) as accepted_papers,
    (SELECT COUNT(*) FROM papers WHERE status = 'rejected' AND is_active = 1) as rejected_papers,
    (SELECT COUNT(*) FROM users WHERE role = 'author' AND is_active = 1) as total_authors,
    (SELECT COUNT(*) FROM users WHERE role = 'reviewer' AND is_active = 1) as total_reviewers,
    (SELECT COUNT(*) FROM reviews WHERE review_status = 'completed') as completed_reviews,
    (SELECT COUNT(*) FROM reviews WHERE review_status = 'assigned') as pending_reviews
")->fetch();

// Get submissions over time (last 6 months)
$submissions_timeline = $pdo->query("
    SELECT DATE_FORMAT(submission_date, '%Y-%m') as month, COUNT(*) as count
    FROM papers
    WHERE is_active = 1 AND submission_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(submission_date, '%Y-%m')
    ORDER BY month
")->fetchAll();

// Get reviewer workload
$reviewer_workload = $pdo->query("
    SELECT u.first_name, u.last_name,
           COUNT(ra.id) as assigned,
           COUNT(r.id) as completed
    FROM users u
    LEFT JOIN review_assignments ra ON u.id = ra.reviewer_id
    LEFT JOIN reviews r ON ra.id = r.assignment_id AND r.review_status = 'completed'
    WHERE u.role = 'reviewer' AND u.is_active = 1
    GROUP BY u.id, u.first_name, u.last_name
    ORDER BY assigned DESC
    LIMIT 10
")->fetchAll();
?>

<h2>Admin Dashboard</h2>

<div class="admin-stats">
    <div class="stat-card">
        <h3>Papers</h3>
        <div class="stat-number"><?php echo $stats['total_papers']; ?></div>
        <div class="stat-details">
            <div>Submitted: <?php echo $stats['submitted_papers']; ?></div>
            <div>Under Review: <?php echo $stats['under_review_papers']; ?></div>
            <div>Accepted: <?php echo $stats['accepted_papers']; ?></div>
            <div>Rejected: <?php echo $stats['rejected_papers']; ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <h3>Users</h3>
        <div class="stat-number"><?php echo $stats['total_authors'] + $stats['total_reviewers']; ?></div>
        <div class="stat-details">
            <div>Authors: <?php echo $stats['total_authors']; ?></div>
            <div>Reviewers: <?php echo $stats['total_reviewers']; ?></div>
        </dicharts-section">
    <div class="chart-container">
        <h3>Paper Status Distribution</h3>
        <canvas id="statusChart"></canvas>
    </div>
    
    <div class="chart-container">
        <h3>Submissions Over Time</h3>
        <canvas id="submissionsChart"></canvas>
    </div>
    
    <div class="chart-container">
        <h3>Review Progress</h3>
        <canvas id="reviewsChart"></canvas>
    </div>
</div>

<div class="workload-section">
    <h3>Reviewer Workload</h3>
    <?php if (!empty($reviewer_workload)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Reviewer</th>
                    <th>Assigned</th>
                    <th>Completed</th>
                    <th>Pending</th>
                    <th>Completion Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviewer_workload as $reviewer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reviewer['first_name'] . ' ' . $reviewer['last_name']); ?></td>
                        <td><?php echo $reviewer['assigned']; ?></td>
                        <td><?php echo $reviewer['completed']; ?></td>
                        <td><?php echo $reviewer['assigned'] - $reviewer['completed']; ?></td>
                        <td>
                            <?php 
                            $rate = $reviewer['assigned'] > 0 ? round(($reviewer['completed'] / $reviewer['assigned']) * 100) : 0;
                            $color = $rate >= 80 ? 'green' : ($rate >= 50 ? 'orange' : 'red');
                            ?>
                            <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                <?php echo $rate; ?>%
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No reviewer data available.</p>
    <?php endif; ?>
</div>

<div class="v>
    </div>
    
    <div class="stat-card">
        <h3>Reviews</h3>
        <div class="stat-number"><?php echo $stats['completed_reviews']; ?></div>
        <div class="stat-details">
            <div>Completed: <?php echo $stats['completed_reviews']; ?></div>
            <div>Pending: <?php echo $stats['pending_reviews']; ?></div>
        </div>
    </div>
</div>

<div class="admin-actions">
    <h3>Quick Actions</h3>
    <div class="action-buttons">
        <a href="papers.php" class="btn btn-primary">Manage Papers</a>
 script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Paper Status Distribution Pie Chart
new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: ['Submitted', 'Under Review', 'Accepted', 'Rejected'],
        datasets: [{
            data: [
                <?php echo $stats['submitted_papers']; ?>,
                <?php echo $stats['under_review_papers']; ?>,
                <?php echo $stats['accepted_papers']; ?>,
                <?php echo $stats['rejected_papers']; ?>
            ],
            backgroundColor: ['#3498db', '#f39c12', '#2ecc71', '#e74c3c']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Submissions Over Time Line Chart
new Chart(document.getElementById('submissionsChart'), {
    type: 'line',
    data: {
        labels: [<?php 
            foreach ($submissions_timeline as $row) {
                echo "'" . date('M Y', strtotime($row['month'] . '-01')) . "',";
            }
        ?>],
        datasets: [{
            label: 'Submissions',
            data: [<?php 
                foreach ($submissions_timeline as $row) {
                    echo $row['count'] . ',';
                }
            ?>],
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// Review Progress Bar Chart
new Chart(document.getElementById('reviewsChart'), {
    type: 'bar',
    data: {
        labels: ['Completed', 'Pending'],
        datasets: [{
            label: 'Reviews',
            data: [
                <?php echo $stats['completed_reviews']; ?>,
                <?php echo $stats['pending_reviews']; ?>
            ],
            backgroundColor: ['#2ecc71', '#f39c12']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>

<style>
.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.chart-container {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: 300px;
}

.chart-container h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.chart-container canvas {
    max-height: 250px;
}

.workload-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 2rem 0;
}
</style>

<       <a href="users.php" class="btn btn-primary">Manage Users</a>
        <a href="assign_reviewers.php" class="btn btn-primary">Assign Reviewers</a>
        <a href="make_decision.php" class="btn btn-secondary">Make Decisions</a>
    </div>
</div>

<div class="recent-activity">
    <h3>Recent Submissions</h3>
    <?php
    $stmt = $pdo->query("
        SELECT p.id, p.title, p.status, p.submission_date,
               u.first_name, u.last_name
        FROM papers p
        JOIN users u ON p.author_id = u.id
        WHERE p.is_active = 1
        ORDER BY p.submission_date DESC
        LIMIT 5
    ");
    $recent_papers = $stmt->fetchAll();
    ?>
    
    <?php if (!empty($recent_papers)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_papers as $paper): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($paper['title']); ?></td>
                        <td><?php echo htmlspecialchars($paper['first_name'] . ' ' . $paper['last_name']); ?></td>
                        <td>
                            <span class="status status-<?php echo $paper['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $paper['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($paper['submission_date'])); ?></td>
                        <td>
                            <a href="view_paper.php?id=<?php echo $paper['id']; ?>" class="btn btn-small">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No papers submitted yet.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>