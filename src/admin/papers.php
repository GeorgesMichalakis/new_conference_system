<?php
require_once '../includes/config.php';
requireRole('admin');

$pageTitle = 'Manage Papers';
include '../includes/header.php';

// Get all papers
$stmt = $pdo->query("
    SELECT p.id, p.title, p.status, p.submission_date,
           u.first_name, u.last_name,
           (SELECT COUNT(*) FROM reviews WHERE paper_id = p.id) as review_count
    FROM papers p
    JOIN users u ON p.author_id = u.id
    WHERE p.is_active = 1
    ORDER BY p.submission_date DESC
");
$papers = $stmt->fetchAll();
?>

<h2>Manage Papers</h2>

<?php if (!empty($papers)): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Reviews</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($papers as $paper): ?>
                <tr>
                    <td><?php echo htmlspecialchars($paper['title']); ?></td>
                    <td><?php echo htmlspecialchars($paper['first_name'] . ' ' . $paper['last_name']); ?></td>
                    <td>
                        <span class="status status-<?php echo $paper['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $paper['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($paper['submission_date'])); ?></td>
                    <td><?php echo $paper['review_count']; ?></td>
                    <td>
                        <a href="view_paper.php?id=<?php echo $paper['id']; ?>" class="btn btn-small">View</a>
                        <a href="assign_reviewers.php?paper_id=<?php echo $paper['id']; ?>" class="btn btn-small">Assign</a>
                        <?php if ($paper['status'] === 'under_review'): ?>
                            <a href="make_decision.php?id=<?php echo $paper['id']; ?>" class="btn btn-small btn-primary">Decide</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No papers submitted yet.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>