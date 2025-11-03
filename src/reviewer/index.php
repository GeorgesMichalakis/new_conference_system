<?php
require_once '../includes/config.php';
requireRole('reviewer');

$pageTitle = 'Review Papers';
include '../includes/header.php';

// Get assigned papers for review
$stmt = $pdo->prepare("
    SELECT p.id, p.title, p.submission_date, p.author_id, p.file_name,
           u.first_name, u.last_name,
           r.id as review_id, r.review_status, r.deadline, r.submitted_date
    FROM papers p
    JOIN users u ON p.author_id = u.id
    JOIN reviews r ON p.id = r.paper_id
    WHERE r.reviewer_id = ? AND p.is_active = 1
    ORDER BY r.deadline ASC, p.submission_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$papers = $stmt->fetchAll();
?>

<h2>Papers to Review</h2>

<?php if (empty($papers)): ?>
    <p>No papers assigned for review yet.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Submitted</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($papers as $paper): ?>
                <tr>
                    <td><?php echo htmlspecialchars($paper['title']); ?></td>
                    <td><?php echo htmlspecialchars($paper['first_name'] . ' ' . $paper['last_name']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($paper['submission_date'])); ?></td>
                    <td>
                        <?php if ($paper['deadline']): ?>
                            <?php echo date('M j, Y', strtotime($paper['deadline'])); ?>
                        <?php else: ?>
                            No deadline set
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status status-<?php echo $paper['review_status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $paper['review_status'])); ?>
                        </span>
                    </td>
                    <td>
                        <a href="view.php?id=<?php echo $paper['id']; ?>" class="btn btn-small">View Paper</a>
                        <?php if ($paper['review_status'] === 'completed'): ?>
                            <a href="edit_review.php?id=<?php echo $paper['review_id']; ?>" class="btn btn-small">Edit Review</a>
                        <?php else: ?>
                            <a href="review.php?id=<?php echo $paper['id']; ?>" class="btn btn-small btn-primary">Submit Review</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>