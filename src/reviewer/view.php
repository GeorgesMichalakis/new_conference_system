<?php
require_once '../includes/config.php';
requireRole('reviewer');

$paper_id = $_GET['id'] ?? 0;

// Verify reviewer has access to this paper
$stmt = $pdo->prepare("
    SELECT p.*, u.first_name, u.last_name, u.institution,
           r.id as review_id, r.review_status
    FROM papers p
    JOIN users u ON p.author_id = u.id
    JOIN reviewer_assignments ra ON p.id = ra.paper_id
    LEFT JOIN reviews r ON p.id = r.paper_id AND ra.reviewer_id = r.reviewer_id
    WHERE p.id = ? AND ra.reviewer_id = ? AND p.is_active = 1
");
$stmt->execute([$paper_id, $_SESSION['user_id']]);
$paper = $stmt->fetch();

if (!$paper) {
    $_SESSION['error'] = 'Paper not found or access denied.';
    redirect('/reviewer/');
}

$pageTitle = 'View Paper';
include '../includes/header.php';
?>

<h2><?php echo htmlspecialchars($paper['title']); ?></h2>

<div class="paper-details">
    <div class="paper-meta">
        <p><strong>Author:</strong> <?php echo htmlspecialchars($paper['first_name'] . ' ' . $paper['last_name']); ?></p>
        <?php if ($paper['institution']): ?>
            <p><strong>Institution:</strong> <?php echo htmlspecialchars($paper['institution']); ?></p>
        <?php endif; ?>
        <p><strong>Submitted:</strong> <?php echo date('F j, Y', strtotime($paper['submission_date'])); ?></p>
        <p><strong>File:</strong> <?php echo htmlspecialchars($paper['file_name']); ?> 
            (<?php echo formatFileSize($paper['file_size']); ?>)
        </p>
        <?php if ($paper['keywords']): ?>
            <p><strong>Keywords:</strong> <?php echo htmlspecialchars($paper['keywords']); ?></p>
        <?php endif; ?>
        <?php if ($paper['co_authors']): ?>
            <p><strong>Co-Authors:</strong><br><?php echo nl2br(htmlspecialchars($paper['co_authors'])); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="paper-content">
        <h3>Abstract</h3>
        <p><?php echo nl2br(htmlspecialchars($paper['abstract'])); ?></p>
    </div>
</div>

<div class="paper-actions">
    <a href="download.php?id=<?php echo $paper['id']; ?>" class="btn btn-secondary">Download Paper</a>
    <?php if ($paper['review_status'] === 'completed'): ?>
        <a href="edit_review.php?id=<?php echo $paper['review_id']; ?>" class="btn btn-primary">Edit My Review</a>
    <?php else: ?>
        <a href="review.php?id=<?php echo $paper['id']; ?>" class="btn btn-primary">Submit Review</a>
    <?php endif; ?>
    <a href="/reviewer/" class="btn btn-secondary">Back to Reviews</a>
</div>

<?php include '../includes/footer.php'; ?>