<?php
require_once '../includes/config.php';
requireRole('admin');

$paper_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Get paper details with reviews
$stmt = $pdo->prepare("
    SELECT p.*, u.first_name as author_first, u.last_name as author_last, u.email as author_email
    FROM papers p
    JOIN users u ON p.author_id = u.id
    WHERE p.id = ? AND p.is_active = 1
");
$stmt->execute([$paper_id]);
$paper = $stmt->fetch();

if (!$paper) {
    $_SESSION['error'] = 'Paper not found.';
    redirect('/admin/papers.php');
}

// Get all reviews for this paper
$stmt = $pdo->prepare("
    SELECT r.*, u.first_name, u.last_name
    FROM reviews r
    JOIN users u ON r.reviewer_id = u.id
    WHERE r.paper_id = ? AND r.review_status = 'completed'
    ORDER BY r.submitted_date DESC
");
$stmt->execute([$paper_id]);
$reviews = $stmt->fetchAll();

// Calculate average ratings
$avg_ratings = [
    'overall' => 0,
    'technical' => 0,
    'novelty' => 0,
    'significance' => 0,
    'clarity' => 0
];

if (!empty($reviews)) {
    foreach ($reviews as $review) {
        $avg_ratings['overall'] += $review['overall_rating'];
        $avg_ratings['technical'] += $review['technical_quality'];
        $avg_ratings['novelty'] += $review['novelty'];
        $avg_ratings['significance'] += $review['significance'];
        $avg_ratings['clarity'] += $review['clarity'];
    }
    $count = count($reviews);
    $avg_ratings['overall'] = round($avg_ratings['overall'] / $count, 1);
    $avg_ratings['technical'] = round($avg_ratings['technical'] / $count, 1);
    $avg_ratings['novelty'] = round($avg_ratings['novelty'] / $count, 1);
    $avg_ratings['significance'] = round($avg_ratings['significance'] / $count, 1);
    $avg_ratings['clarity'] = round($avg_ratings['clarity'] / $count, 1);
}

// Handle decision submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decision = $_POST['decision'];
    $decision_comments = sanitizeInput($_POST['decision_comments']);
    
    if (!in_array($decision, ['accepted', 'rejected', 'revision_required'])) {
        $error = 'Invalid decision.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE papers SET 
                status = ?, 
                decision_date = NOW(), 
                decision_by = ?, 
                decision_comments = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([$decision, $_SESSION['user_id'], $decision_comments, $paper_id])) {
            $_SESSION['success'] = 'Decision saved successfully!';
            redirect('/admin/papers.php');
        } else {
            $error = 'Failed to save decision. Please try again.';
        }
    }
}

$pageTitle = 'Make Decision';
include '../includes/header.php';
?>

<h2>Make Decision: <?php echo htmlspecialchars($paper['title']); ?></h2>

<div class="paper-info">
    <h3>Paper Information</h3>
    <p><strong>Author:</strong> <?php echo htmlspecialchars($paper['author_first'] . ' ' . $paper['author_last']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($paper['author_email']); ?></p>
    <p><strong>Submitted:</strong> <?php echo date('F j, Y', strtotime($paper['submission_date'])); ?></p>
    <p><strong>Current Status:</strong> 
        <span class="status status-<?php echo $paper['status']; ?>">
            <?php echo ucfirst(str_replace('_', ' ', $paper['status'])); ?>
        </span>
    </p>
    
    <h4>Abstract</h4>
    <p><?php echo nl2br(htmlspecialchars($paper['abstract'])); ?></p>
    
    <?php if ($paper['keywords']): ?>
        <p><strong>Keywords:</strong> <?php echo htmlspecialchars($paper['keywords']); ?></p>
    <?php endif; ?>
</div>

<?php if (!empty($reviews)): ?>
    <div class="reviews-summary">
        <h3>Review Summary (<?php echo count($reviews); ?> reviews)</h3>
        
        <div class="average-ratings">
            <h4>Average Ratings</h4>
            <div class="rating-grid">
                <div class="rating-item">
                    <span>Overall Rating:</span>
                    <strong><?php echo $avg_ratings['overall']; ?>/10</strong>
                </div>
                <div class="rating-item">
                    <span>Technical Quality:</span>
                    <strong><?php echo $avg_ratings['technical']; ?>/5</strong>
                </div>
                <div class="rating-item">
                    <span>Novelty:</span>
                    <strong><?php echo $avg_ratings['novelty']; ?>/5</strong>
                </div>
                <div class="rating-item">
                    <span>Significance:</span>
                    <strong><?php echo $avg_ratings['significance']; ?>/5</strong>
                </div>
                <div class="rating-item">
                    <span>Clarity:</span>
                    <strong><?php echo $avg_ratings['clarity']; ?>/5</strong>
                </div>
            </div>
        </div>
        
        <div class="individual-reviews">
            <h4>Individual Reviews</h4>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <strong>Review by <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                        <span class="review-date"><?php echo date('M j, Y', strtotime($review['submitted_date'])); ?></span>
                    </div>
                    
                    <div class="review-ratings">
                        <span>Overall: <strong><?php echo $review['overall_rating']; ?>/10</strong></span> |
                        <span>Technical: <?php echo $review['technical_quality']; ?>/5</span> |
                        <span>Novelty: <?php echo $review['novelty']; ?>/5</span> |
                        <span>Significance: <?php echo $review['significance']; ?>/5</span> |
                        <span>Clarity: <?php echo $review['clarity']; ?>/5</span>
                    </div>
                    
                    <div class="review-recommendation">
                        <strong>Recommendation:</strong>
                        <span class="recommendation recommendation-<?php echo $review['recommendation']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $review['recommendation'])); ?>
                        </span>
                    </div>
                    
                    <div class="review-comments">
                        <h5>Comments for Authors:</h5>
                        <p><?php echo nl2br(htmlspecialchars($review['detailed_comments'])); ?></p>
                    </div>
                    
                    <?php if ($review['confidential_comments']): ?>
                        <div class="confidential-comments">
                            <h5>Confidential Comments (Admin Only):</h5>
                            <p><?php echo nl2br(htmlspecialchars($review['confidential_comments'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        <strong>No reviews submitted yet.</strong> You should wait for reviews before making a decision.
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="decision-form">
    <h3>Make Your Decision</h3>
    <form method="POST">
        <div class="form-group">
            <label for="decision">Decision:</label>
            <select id="decision" name="decision" required>
                <option value="">Select decision</option>
                <option value="accepted">Accept</option>
                <option value="revision_required">Revision Required</option>
                <option value="rejected">Reject</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="decision_comments">Decision Comments:</label>
            <textarea id="decision_comments" name="decision_comments" rows="6" required placeholder="Provide feedback to the author about your decision..."></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Submit Decision</button>
            <a href="/admin/papers.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.paper-info, .reviews-summary, .decision-form {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.average-ratings {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.rating-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.rating-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: white;
    border-radius: 5px;
}

.review-card {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 5px;
}

.review-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.review-ratings {
    margin: 10px 0;
    color: #666;
}

.confidential-comments {
    background: #fff3cd;
    padding: 10px;
    margin-top: 10px;
    border-left: 3px solid #ffc107;
}

.recommendation {
    padding: 3px 8px;
    border-radius: 3px;
    font-weight: bold;
}

.recommendation-accept {
    background: #d4edda;
    color: #155724;
}

.recommendation-minor_revision {
    background: #d1ecf1;
    color: #0c5460;
}

.recommendation-major_revision {
    background: #fff3cd;
    color: #856404;
}

.recommendation-reject {
    background: #f8d7da;
    color: #721c24;
}
</style>

<?php include '../includes/footer.php'; ?>
