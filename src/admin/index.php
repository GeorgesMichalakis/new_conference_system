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
        </div>
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
        <a href="users.php" class="btn btn-primary">Manage Users</a>
        <a href="assign_reviewers.php" class="btn btn-primary">Assign Reviewers</a>
        <a href="reviews.php" class="btn btn-secondary">View Reviews</a>
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