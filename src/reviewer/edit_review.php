<?php
require_once '../includes/config.php';
requireRole('reviewer');

$review_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Get review details with paper info
$stmt = $pdo->prepare("
    SELECT r.*, p.title as paper_title, p.abstract, ra.status as assignment_status
    FROM reviews r
    JOIN papers p ON r.paper_id = p.id
    JOIN reviewer_assignments ra ON r.paper_id = ra.paper_id AND r.reviewer_id = ra.reviewer_id
    WHERE r.id = ? AND r.reviewer_id = ?
");
$stmt->execute([$review_id, $_SESSION['user_id']]);
$review = $stmt->fetch();

if (!$review) {
    $_SESSION['error'] = 'Review not found or you do not have permission to edit it.';
    redirect('/reviewer/');
}

// Check if review can be edited (paper should still be under review)
$stmt = $pdo->prepare("
    SELECT p.status 
    FROM papers p 
    WHERE p.id = ?
");
$stmt->execute([$review['paper_id']]);
$paper_status = $stmt->fetchColumn();

if (!in_array($paper_status, ['under_review', 'revision_required'])) {
    $_SESSION['error'] = 'This review cannot be edited as the paper has been finalized.';
    redirect('/reviewer/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $overall_rating = (int)$_POST['overall_rating'];
    $technical_quality = (int)$_POST['technical_quality'];
    $novelty = (int)$_POST['novelty'];
    $significance = (int)$_POST['significance'];
    $clarity = (int)$_POST['clarity'];
    $recommendation = sanitizeInput($_POST['recommendation']);
    $detailed_comments = sanitizeInput($_POST['detailed_comments']);
    $confidential_comments = sanitizeInput($_POST['confidential_comments']);
    
    // Validation
    if ($overall_rating < 1 || $overall_rating > 10) {
        $error = 'Overall rating must be between 1 and 10.';
    } elseif ($technical_quality < 1 || $technical_quality > 5 || 
              $novelty < 1 || $novelty > 5 ||
              $significance < 1 || $significance > 5 ||
              $clarity < 1 || $clarity > 5) {
        $error = 'All criteria ratings must be between 1 and 5.';
    } elseif (!in_array($recommendation, ['accept', 'minor_revision', 'major_revision', 'reject'])) {
        $error = 'Invalid recommendation.';
    } elseif (empty($detailed_comments)) {
        $error = 'Comments for authors are required.';
    } else {
        // Update review
        $stmt = $pdo->prepare("
            UPDATE reviews 
            SET overall_rating = ?, technical_quality = ?, novelty = ?, significance = ?, 
                clarity = ?, recommendation = ?, detailed_comments = ?, confidential_comments = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        if ($stmt->execute([$overall_rating, $technical_quality, $novelty, $significance, $clarity, 
                           $recommendation, $detailed_comments, $confidential_comments, $review_id])) {
            $_SESSION['success'] = 'Review updated successfully!';
            redirect('/reviewer/');
        } else {
            $error = 'Failed to update review. Please try again.';
        }
    }
}

$pageTitle = 'Edit Review';
include '../includes/header.php';
?>

<h2>Edit Review</h2>

<div class="paper-info">
    <h3><?php echo htmlspecialchars($review['paper_title']); ?></h3>
    <p><strong>Original Review Date:</strong> <?php echo date('F j, Y', strtotime($review['created_at'])); ?></p>
    <?php if (!empty($review['updated_at']) && $review['updated_at'] != $review['created_at']): ?>
        <p><strong>Last Updated:</strong> <?php echo date('F j, Y g:i A', strtotime($review['updated_at'])); ?></p>
    <?php endif; ?>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" class="review-form">
    <div class="form-section">
        <h3>Rating & Evaluation</h3>
        
        <div class="form-group">
            <label for="overall_rating">Overall Rating (1-10): *</label>
            <input type="number" id="overall_rating" name="overall_rating" min="1" max="10" required 
                   value="<?php echo isset($review['overall_rating']) ? htmlspecialchars($review['overall_rating']) : ''; ?>">
            <small>1 = Poor, 10 = Excellent</small>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="technical_quality">Technical Quality (1-5): *</label>
                <input type="number" id="technical_quality" name="technical_quality" min="1" max="5" required 
                       value="<?php echo isset($review['technical_quality']) ? htmlspecialchars($review['technical_quality']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="novelty">Novelty (1-5): *</label>
                <input type="number" id="novelty" name="novelty" min="1" max="5" required 
                       value="<?php echo isset($review['novelty']) ? htmlspecialchars($review['novelty']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="significance">Significance (1-5): *</label>
                <input type="number" id="significance" name="significance" min="1" max="5" required 
                       value="<?php echo isset($review['significance']) ? htmlspecialchars($review['significance']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="clarity">Clarity (1-5): *</label>
                <input type="number" id="clarity" name="clarity" min="1" max="5" required 
                       value="<?php echo isset($review['clarity']) ? htmlspecialchars($review['clarity']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="recommendation">Recommendation: *</label>
            <select id="recommendation" name="recommendation" required>
                <option value="">Select...</option>
                <option value="accept" <?php echo (isset($review['recommendation']) && $review['recommendation'] == 'accept') ? 'selected' : ''; ?>>Accept</option>
                <option value="minor_revision" <?php echo (isset($review['recommendation']) && $review['recommendation'] == 'minor_revision') ? 'selected' : ''; ?>>Minor Revision</option>
                <option value="major_revision" <?php echo (isset($review['recommendation']) && $review['recommendation'] == 'major_revision') ? 'selected' : ''; ?>>Major Revision</option>
                <option value="reject" <?php echo (isset($review['recommendation']) && $review['recommendation'] == 'reject') ? 'selected' : ''; ?>>Reject</option>
            </select>
        </div>
    </div>
    
    <div class="form-section">
        <h3>Comments</h3>
        
        <div class="form-group">
            <label for="detailed_comments">Comments for Authors: *</label>
            <textarea id="detailed_comments" name="detailed_comments" rows="12" required><?php echo isset($review['detailed_comments']) && $review['detailed_comments'] !== null ? htmlspecialchars($review['detailed_comments']) : ''; ?></textarea>
            <small>These comments will be shared with the authors</small>
        </div>
        
        <div class="form-group">
            <label for="confidential_comments">Confidential Comments for Editors:</label>
            <textarea id="confidential_comments" name="confidential_comments" rows="6"><?php echo htmlspecialchars(!empty($review['confidential_comments']) ? $review['confidential_comments'] : ''); ?></textarea>
            <small>These comments are only visible to editors and program chairs</small>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Update Review</button>
        <a href="/reviewer/" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
.paper-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.review-form {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 900px;
}

.form-section {
    margin-bottom: 30px;
}

.form-section h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e1e8ed;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    font-family: inherit;
}

.form-group small {
    display: block;
    color: #666;
    margin-top: 5px;
}

.form-actions {
    margin-top: 30px;
    display: flex;
    gap: 10px;
}
</style>

<?php include '../includes/footer.php'; ?>
