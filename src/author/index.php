<?php
require_once '../includes/config.php';
requireRole('author');

$pageTitle = 'My Papers';
include '../includes/header.php';

// Get author's papers
$stmt = $pdo->prepare("SELECT id, title, status, submission_date, file_name FROM papers WHERE author_id = ? AND is_active = 1 ORDER BY submission_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$papers = $stmt->fetchAll();
?>

<h2>My Papers</h2>

<div class="actions">
    <a href="submit.php" class="btn btn-primary">Submit New Paper</a>
</div>

<?php if (empty($papers)): ?>
    <p>You haven't submitted any papers yet. <a href="submit.php">Submit your first paper</a></p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>File</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($papers as $paper): ?>
                <tr>
                    <td><?php echo htmlspecialchars($paper['title']); ?></td>
                    <td>
                        <span class="status status-<?php echo $paper['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $paper['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($paper['submission_date'])); ?></td>
                    <td><?php echo htmlspecialchars($paper['file_name']); ?></td>
                    <td>
                        <a href="view.php?id=<?php echo $paper['id']; ?>" class="btn btn-small">View</a>
                        <?php if (in_array($paper['status'], ['submitted', 'revision_required'])): ?>
                            <a href="edit.php?id=<?php echo $paper['id']; ?>" class="btn btn-small btn-secondary">Edit</a>
                        <?php endif; ?>
                        <?php if ($paper['status'] === 'revision_required'): ?>
                            <a href="revise.php?id=<?php echo $paper['id']; ?>" class="btn btn-small" style="background: #f39c12;">Revise</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>