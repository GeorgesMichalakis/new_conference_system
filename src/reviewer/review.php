<?php
require_once '../includes/config.php';
requireRole('reviewer');

$paper_id = $_GET['id'] ?? 0;

// Verify reviewer has access to this paper
$stmt = $pdo->prepare("
    SELECT p.*, u.first_name, u.last_name,
           r.id as review_id, r.review_status
    FROM papers p
    JOIN users u ON p.author_id = u.id
    JOIN reviews r ON p.id = r.paper_id
    WHERE p.id = ? AND r.reviewer_id = ? AND p.is_active = 1
");
$stmt->execute([$paper_id, $_SESSION['user_id']]);
$paper = $stmt->fetch();

if (!$paper) {
    $_SESSION['error'] = 'Paper not found or access denied.';
    redirect('/reviewer/');
}

if ($paper['review_status'] === 'completed') {
    redirect("edit_review.php?id={$paper['review_id']}");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $overall_rating = (int)$_POST['overall_rating'];
    $technical_quality = (int)$_POST['technical_quality'];
    $novelty = (int)$_POST['novelty'];
    $significance = (int)$_POST['significance'];
    $clarity = (int)$_POST['clarity'];
    $recommendation = $_POST['recommendation'];
    $detailed_comments = sanitizeInput($_POST['detailed_comments']);
    $confidential_comments = sanitizeInput($_POST['confidential_comments']);
    
    // Validation
    if ($overall_rating < 1 || $overall_rating > 10) {
        $error = 'Overall rating must be between 1 and 10.';
    } elseif ($technical_quality < 1 || $technical_quality > 5 ||
              $novelty < 1 || $novelty > 5 ||
              $significance < 1 || $significance > 5 ||
              $clarity < 1 || $clarity > 5) {
        $error = 'Individual ratings must be between 1 and 5.';
    } elseif (!in_array($recommendation, ['accept', 'minor_revision', 'major_revision', 'reject'])) {
        $error = 'Invalid recommendation.';
    } elseif (empty($detailed_comments)) {
        $error = 'Detailed comments are required.';
    } else {
        // Update the review
        $stmt = $pdo->prepare("
            UPDATE reviews SET 
                overall_rating = ?, technical_quality = ?, novelty = ?, 
                significance = ?, clarity = ?, recommendation = ?,
                detailed_comments = ?, confidential_comments = ?,
                review_status = 'completed', submitted_date = NOW()
            WHERE id = ?
        ");
        
        if ($stmt->execute([$overall_rating, $technical_quality, $novelty, 
                           $significance, $clarity, $recommendation,
                           $detailed_comments, $confidential_comments, $paper['review_id']])) {
            $_SESSION['success'] = 'Review submitted successfully!';
            redirect('/reviewer/');
        } else {
            $error = 'Failed to submit review. Please try again.';
        }
    }
}

$pageTitle = 'Submit Review';
include '../includes/header.php';
?>

<h2>Review: <?php echo htmlspecialchars($paper['title']); ?></h2>

<div class="paper-summary">
    <p><strong>Author:</strong> <?php echo htmlspecialchars($paper['first_name'] . ' ' . $paper['last_name']); ?></p>
    <p><strong>Submitted:</strong> <?php echo date('F j, Y', strtotime($paper['submission_date'])); ?></p>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" class="review-form">
    <div class="rating-section">
        <h3>Ratings</h3>
        
        <div class="form-group">
            <label for="overall_rating">Overall Rating (1-10):</label>
            <select id="overall_rating" name="overall_rating" required>
                <option value="">Select rating</option>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="technical_quality">Technical Quality (1-5):</label>
            <select id="technical_quality" name="technical_quality" required>
                <option value="">Select rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="novelty">Novelty (1-5):</label>
            <select id="novelty" name="novelty" required>
                <option value="">Select rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="significance">Significance (1-5):</label>
            <select id="significance" name="significance" required>
                <option value="">Select rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="clarity">Clarity (1-5):</label>
            <select id="clarity" name="clarity" required>
                <option value="">Select rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <label for="recommendation">Recommendation:</label>
        <select id="recommendation" name="recommendation" required>
            <option value="">Select recommendation</option>
            <option value="accept">Accept</option>
            <option value="minor_revision">Minor Revision</option>
            <option value="major_revision">Major Revision</option>
            <option value="reject">Reject</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="detailed_comments">Detailed Comments (visible to authors):</label>
        <textarea id="detailed_comments" name="detailed_comments" rows="10" required 
                  placeholder="Provide detailed feedback about the paper's strengths, weaknesses, and suggestions for improvement..."></textarea>
    </div>
    
    <div class="form-group">
        <label for="confidential_comments">Confidential Comments (for editors only):</label>
        <textarea id="confidential_comments" name="confidential_comments" rows="5" 
                  placeholder="Optional confidential comments for the editorial board..."></textarea>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Submit Review</button>
        <a href="view.php?id=<?php echo $paper_id; ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php include '../includes/footer.php'; ?>