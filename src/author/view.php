<?php
require_once '../includes/config.php';
requireRole('author');

$paper_id = $_GET['id'] ?? 0;

// Get paper details
$stmt = $pdo->prepare("SELECT * FROM papers WHERE id = ? AND author_id = ? AND is_active = 1");
$stmt->execute([$paper_id, $_SESSION['user_id']]);
$paper = $stmt->fetch();

if (!$paper) {
    $_SESSION['error'] = 'Paper not found.';
    redirect('/author/');
}

// Get reviews for this paper
$stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name FROM reviews r 
    JOIN users u ON r.reviewer_id = u.id 
    WHERE r.paper_id = ? AND r.review_status = 'completed' 
    ORDER BY r.submitted_date DESC");
$stmt->execute([$paper_id]);
$reviews = $stmt->fetchAll();

$pageTitle = 'View Paper';
include '../includes/header.php';
?>

<h2><?php echo htmlspecialchars($paper['title']); ?></h2>

<div class="paper-details">
    <div class="paper-meta">
        <p><strong>Status:</strong> 
            <span class="status status-<?php echo $paper['status']; ?>">
                <?php echo ucfirst(str_replace('_', ' ', $paper['status'])); ?>
            </span>
        </p>
        <p><strong>Submitted:</strong> <?php echo date('F j, Y g:i A', strtotime($paper['submission_date'])); ?></p>
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

<?php if (!empty($reviews)): ?>
    <div class="reviews-section">
        <h3>Reviews (<?php echo count($reviews); ?>)</h3>
        
        <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="review-header">
                    <strong>Review by <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                    <span class="review-date"><?php echo date('M j, Y', strtotime($review['submitted_date'])); ?></span>
                </div>
                
                <div class="review-ratings">
                    <div class="rating-item">
                        <span>Overall Rating:</span> 
                        <strong><?php echo $review['overall_rating']; ?>/10</strong>
                    </div>
                    <div class="rating-item">
                        <span>Technical Quality:</span> 
                        <?php echo $review['technical_quality']; ?>/5
                    </div>
                    <div class="rating-item">
                        <span>Novelty:</span> 
                        <?php echo $review['novelty']; ?>/5
                    </div>
                    <div class="rating-item">
                        <span>Significance:</span> 
                        <?php echo $review['significance']; ?>/5
                    </div>
                    <div class="rating-item">
                        <span>Clarity:</span> 
                        <?php echo $review['clarity']; ?>/5
                    </div>
                </div>
                
                <div class="review-recommendation">
                    <strong>Recommendation:</strong> 
                    <span class="recommendation recommendation-<?php echo $review['recommendation']; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $review['recommendation'])); ?>
                    </span>
                </div>
                
                <div class="review-comments">
                    <h4>Comments:</h4>
                    <p><?php echo nl2br(htmlspecialchars($review['detailed_comments'])); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-reviews">
        <p>No reviews available yet.</p>
    </div>
<?php endif; ?>

<div class="actions">
    <a href="/author/" class="btn btn-secondary">Back to My Papers</a>
</div>

<?php include '../includes/footer.php'; ?>