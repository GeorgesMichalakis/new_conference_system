<?php
require_once '../includes/config.php';
requireRole('admin');

$paper_id = $_GET['id'] ?? 0;

// Get paper details
$stmt = $pdo->prepare("
    SELECT p.*, 
           u.first_name as author_first, u.last_name as author_last, u.email as author_email,
           u.institution, u.department,
           d.first_name as decider_first, d.last_name as decider_last
    FROM papers p
    JOIN users u ON p.author_id = u.id
    LEFT JOIN users d ON p.decision_by = d.id
    WHERE p.id = ? AND p.is_active = 1
");
$stmt->execute([$paper_id]);
$paper = $stmt->fetch();

if (!$paper) {
    $_SESSION['error'] = 'Paper not found.';
    redirect('/admin/papers.php');
}

// Get all reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.first_name, u.last_name, u.email, u.expertise
    FROM reviews r
    JOIN users u ON r.reviewer_id = u.id
    WHERE r.paper_id = ?
    ORDER BY r.submitted_date DESC
");
$stmt->execute([$paper_id]);
$reviews = $stmt->fetchAll();

$pageTitle = 'View Paper';
include '../includes/header.php';
?>

<div class="paper-view-admin">
    <div class="paper-header">
        <h2><?php echo htmlspecialchars($paper['title']); ?></h2>
        <div class="paper-status">
            <span class="status status-<?php echo $paper['status']; ?>">
                <?php echo ucfirst(str_replace('_', ' ', $paper['status'])); ?>
            </span>
        </div>
    </div>

    <div class="paper-details">
        <div class="section">
            <h3>Author Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($paper['author_first'] . ' ' . $paper['author_last']); ?></p>
            <p><strong>Email:</strong> <a href="mailto:<?php echo $paper['author_email']; ?>"><?php echo htmlspecialchars($paper['author_email']); ?></a></p>
            <?php if ($paper['institution']): ?>
                <p><strong>Institution:</strong> <?php echo htmlspecialchars($paper['institution']); ?></p>
            <?php endif; ?>
            <?php if ($paper['department']): ?>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($paper['department']); ?></p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h3>Submission Information</h3>
            <p><strong>Submitted:</strong> <?php echo date('F j, Y g:i A', strtotime($paper['submission_date'])); ?></p>
            <p><strong>File:</strong> <?php echo htmlspecialchars($paper['file_name']); ?> (<?php echo formatFileSize($paper['file_size']); ?>)</p>
            <?php if ($paper['category']): ?>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($paper['category']); ?></p>
            <?php endif; ?>
            <?php if ($paper['conference_track']): ?>
                <p><strong>Track:</strong> <?php echo htmlspecialchars($paper['conference_track']); ?></p>
            <?php endif; ?>
        </div>

        <?php if ($paper['decision_date']): ?>
            <div class="section decision-info">
                <h3>Decision</h3>
                <p><strong>Decision Date:</strong> <?php echo date('F j, Y', strtotime($paper['decision_date'])); ?></p>
                <?php if ($paper['decider_first']): ?>
                    <p><strong>Decided By:</strong> <?php echo htmlspecialchars($paper['decider_first'] . ' ' . $paper['decider_last']); ?></p>
                <?php endif; ?>
                <?php if ($paper['decision_comments']): ?>
                    <p><strong>Comments:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($paper['decision_comments'])); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="section">
            <h3>Abstract</h3>
            <p><?php echo nl2br(htmlspecialchars($paper['abstract'])); ?></p>
        </div>

        <?php if ($paper['keywords']): ?>
            <div class="section">
                <h3>Keywords</h3>
                <p><?php echo htmlspecialchars($paper['keywords']); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($paper['co_authors']): ?>
            <div class="section">
                <h3>Co-Authors</h3>
                <p><?php echo nl2br(htmlspecialchars($paper['co_authors'])); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="reviews-section">
        <h3>Reviews (<?php echo count($reviews); ?>)</h3>
        
        <?php if (empty($reviews)): ?>
            <p>No reviews submitted yet.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div>
                            <strong><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                            <br>
                            <small><?php echo htmlspecialchars($review['email']); ?></small>
                            <?php if ($review['expertise']): ?>
                                <br>
                                <small><strong>Expertise:</strong> <?php echo htmlspecialchars($review['expertise']); ?></small>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="review-status status-<?php echo $review['review_status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $review['review_status'])); ?>
                            </span>
                            <?php if ($review['submitted_date']): ?>
                                <br><small><?php echo date('M j, Y', strtotime($review['submitted_date'])); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($review['review_status'] === 'completed'): ?>
                        <div class="review-content">
                            <div class="ratings">
                                <h4>Ratings</h4>
                                <div class="rating-grid">
                                    <div>Overall: <strong><?php echo $review['overall_rating']; ?>/10</strong></div>
                                    <div>Technical Quality: <strong><?php echo $review['technical_quality']; ?>/5</strong></div>
                                    <div>Novelty: <strong><?php echo $review['novelty']; ?>/5</strong></div>
                                    <div>Significance: <strong><?php echo $review['significance']; ?>/5</strong></div>
                                    <div>Clarity: <strong><?php echo $review['clarity']; ?>/5</strong></div>
                                </div>
                            </div>

                            <div class="recommendation">
                                <h4>Recommendation</h4>
                                <span class="recommendation-badge recommendation-<?php echo $review['recommendation']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $review['recommendation'])); ?>
                                </span>
                            </div>

                            <div class="comments">
                                <h4>Comments for Authors</h4>
                                <p><?php echo nl2br(htmlspecialchars($review['detailed_comments'])); ?></p>
                            </div>

                            <?php if ($review['confidential_comments']): ?>
                                <div class="confidential-comments">
                                    <h4>Confidential Comments (Admin Only)</h4>
                                    <p><?php echo nl2br(htmlspecialchars($review['confidential_comments'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="pending-review">Review pending - deadline: <?php echo $review['deadline'] ? date('M j, Y', strtotime($review['deadline'])) : 'Not set'; ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="actions-panel">
        <a href="/admin/papers.php" class="btn btn-secondary">Back to Papers</a>
        <a href="assign_reviewers.php?paper_id=<?php echo $paper_id; ?>" class="btn">Assign Reviewers</a>
        <?php if ($paper['status'] === 'under_review' || $paper['status'] === 'submitted'): ?>
            <a href="make_decision.php?id=<?php echo $paper_id; ?>" class="btn btn-primary">Make Decision</a>
        <?php endif; ?>
    </div>
</div>

<style>
.paper-view-admin {
    max-width: 1200px;
    margin: 0 auto;
}

.paper-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 30px;
}

.paper-details, .reviews-section {
    background: #fff;
    padding: 25px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.section:last-child {
    border-bottom: none;
}

.decision-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}

.review-card {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
}

.review-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
}

.ratings {
    margin: 15px 0;
}

.rating-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.recommendation-badge {
    padding: 5px 12px;
    border-radius: 5px;
    font-weight: bold;
    display: inline-block;
}

.confidential-comments {
    background: #fff3cd;
    padding: 15px;
    margin-top: 15px;
    border-left: 4px solid #ffc107;
    border-radius: 5px;
}

.actions-panel {
    display: flex;
    gap: 10px;
    margin-top: 30px;
}

.pending-review {
    color: #666;
    font-style: italic;
}
</style>

<?php include '../includes/footer.php'; ?>
