<?php
require_once '../includes/config.php';
requireRole('author');

$paper_id = $_GET['id'] ?? 0;
$error = '';

// Get paper details
$stmt = $pdo->prepare("SELECT * FROM papers WHERE id = ? AND author_id = ? AND is_active = 1 AND status = 'revision_required'");
$stmt->execute([$paper_id, $_SESSION['user_id']]);
$paper = $stmt->fetch();

if (!$paper) {
    $_SESSION['error'] = 'Paper not found or revision is not required.';
    redirect('/author/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $revision_notes = sanitizeInput($_POST['revision_notes']);
    
    // Validate file upload
    if (empty($_FILES['revision_file']['name'])) {
        $error = 'Please select a revised file to upload.';
    } else {
        $file = $_FILES['revision_file'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file
        if ($file['size'] > MAX_FILE_SIZE) {
            $error = 'File size exceeds maximum allowed size (' . formatFileSize(MAX_FILE_SIZE) . ').';
        } elseif (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
            $error = 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_EXTENSIONS);
        } else {
            // Generate unique filename
            $unique_filename = time() . '_' . uniqid() . '_revision.' . $file_extension;
            $file_path = UPLOAD_PATH . $unique_filename;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Update paper with new file and change status back to under_review
                $stmt = $pdo->prepare("UPDATE papers SET file_path = ?, file_name = ?, file_size = ?, status = 'under_review', updated_at = NOW() WHERE id = ?");
                
                if ($stmt->execute([$unique_filename, $file['name'], $file['size'], $paper_id])) {
                    // Store revision notes in a separate field or table (for now, we'll add to decision_comments)
                    if ($revision_notes) {
                        $pdo->prepare("UPDATE papers SET decision_comments = CONCAT(COALESCE(decision_comments, ''), '\n\n--- Author Revision Notes ---\n', ?) WHERE id = ?")
                            ->execute([$revision_notes, $paper_id]);
                    }
                    
                    $_SESSION['success'] = 'Revised paper submitted successfully!';
                    redirect('/author/view.php?id=' . $paper_id);
                } else {
                    unlink($file_path); // Remove uploaded file
                    $error = 'Failed to save revision information.';
                }
            } else {
                $error = 'Failed to upload file.';
            }
        }
    }
}

$pageTitle = 'Submit Revision';
include '../includes/header.php';
?>

<h2>Submit Revised Paper</h2>

<div class="paper-info">
    <h3><?php echo htmlspecialchars($paper['title']); ?></h3>
    <p><strong>Current File:</strong> <?php echo htmlspecialchars($paper['file_name']); ?></p>
    <p><strong>Original Submission:</strong> <?php echo date('F j, Y', strtotime($paper['submission_date'])); ?></p>
    
    <?php if ($paper['decision_comments']): ?>
        <div class="decision-feedback">
            <h4>Feedback from Editor:</h4>
            <p><?php echo nl2br(htmlspecialchars($paper['decision_comments'])); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="revision-form">
    <h3>Upload Revised Version</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="revision_file">Revised Paper File: *</label>
            <input type="file" id="revision_file" name="revision_file" accept=".pdf,.doc,.docx" required>
            <small>Maximum file size: <?php echo formatFileSize(MAX_FILE_SIZE); ?>. Allowed formats: PDF, DOC, DOCX</small>
        </div>
        
        <div class="form-group">
            <label for="revision_notes">Revision Notes (optional):</label>
            <textarea id="revision_notes" name="revision_notes" rows="6" placeholder="Describe the changes you made in response to the reviewers' comments..."><?php echo isset($_POST['revision_notes']) ? htmlspecialchars($_POST['revision_notes']) : ''; ?></textarea>
            <small>This will help reviewers understand what changes you made</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Submit Revision</button>
            <a href="/author/view.php?id=<?php echo $paper_id; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.paper-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.decision-feedback {
    background: #fff3cd;
    padding: 15px;
    margin-top: 15px;
    border-left: 4px solid #ffc107;
    border-radius: 4px;
}

.revision-form {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 800px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.form-group input[type="file"],
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-group textarea {
    resize: vertical;
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
