<?php
require_once '../includes/config.php';
requireRole('author');

$paper_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Get paper details
$stmt = $pdo->prepare("SELECT * FROM papers WHERE id = ? AND author_id = ? AND is_active = 1");
$stmt->execute([$paper_id, $_SESSION['user_id']]);
$paper = $stmt->fetch();

if (!$paper) {
    $_SESSION['error'] = 'Paper not found or you do not have permission to edit it.';
    redirect('/author/');
}

// Check if paper can be edited
if (!in_array($paper['status'], ['submitted', 'revision_required'])) {
    $_SESSION['error'] = 'This paper cannot be edited in its current status.';
    redirect('/author/view.php?id=' . $paper_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $abstract = sanitizeInput($_POST['abstract']);
    $keywords = sanitizeInput($_POST['keywords']);
    $co_authors = sanitizeInput($_POST['co_authors']);
    $category = sanitizeInput($_POST['category']);
    $conference_track = sanitizeInput($_POST['conference_track']);
    
    // Validation
    if (empty($title) || empty($abstract)) {
        $error = 'Title and abstract are required.';
    } else {
        // Check if new file is uploaded
        $update_file = false;
        $file_path = $paper['file_path'];
        $file_name = $paper['file_name'];
        $file_size = $paper['file_size'];
        
        if (!empty($_FILES['paper_file']['name'])) {
            $file = $_FILES['paper_file'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Validate file
            if ($file['size'] > MAX_FILE_SIZE) {
                $error = 'File size exceeds maximum allowed size (' . formatFileSize(MAX_FILE_SIZE) . ').';
            } elseif (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
                $error = 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_EXTENSIONS);
            } else {
                // Generate unique filename
                $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
                $new_file_path = UPLOAD_PATH . $unique_filename;
                
                if (move_uploaded_file($file['tmp_name'], $new_file_path)) {
                    // Delete old file
                    if (file_exists(UPLOAD_PATH . $paper['file_path'])) {
                        unlink(UPLOAD_PATH . $paper['file_path']);
                    }
                    
                    $file_path = $unique_filename;
                    $file_name = $file['name'];
                    $file_size = $file['size'];
                    $update_file = true;
                } else {
                    $error = 'Failed to upload new file.';
                }
            }
        }
        
        if (!$error) {
            // Update paper
            if ($update_file) {
                $stmt = $pdo->prepare("UPDATE papers SET title = ?, abstract = ?, keywords = ?, co_authors = ?, category = ?, conference_track = ?, file_path = ?, file_name = ?, file_size = ?, updated_at = NOW() WHERE id = ?");
                $success = $stmt->execute([$title, $abstract, $keywords, $co_authors, $category, $conference_track, $file_path, $file_name, $file_size, $paper_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE papers SET title = ?, abstract = ?, keywords = ?, co_authors = ?, category = ?, conference_track = ?, updated_at = NOW() WHERE id = ?");
                $success = $stmt->execute([$title, $abstract, $keywords, $co_authors, $category, $conference_track, $paper_id]);
            }
            
            if ($success) {
                $_SESSION['success'] = 'Paper updated successfully!';
                redirect('/author/view.php?id=' . $paper_id);
            } else {
                $error = 'Failed to update paper. Please try again.';
            }
        }
    }
}

$pageTitle = 'Edit Paper';
include '../includes/header.php';
?>

<h2>Edit Paper</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="paper-form">
    <div class="form-group">
        <label for="title">Paper Title: *</label>
        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($paper['title']); ?>">
    </div>
    
    <div class="form-group">
        <label for="abstract">Abstract: *</label>
        <textarea id="abstract" name="abstract" rows="10" required><?php echo htmlspecialchars($paper['abstract']); ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="keywords">Keywords (comma-separated):</label>
        <input type="text" id="keywords" name="keywords" value="<?php echo htmlspecialchars($paper['keywords'] ?? ''); ?>">
        <small>Example: machine learning, artificial intelligence, neural networks</small>
    </div>
    
    <div class="form-group">
        <label for="co_authors">Co-Authors (optional):</label>
        <textarea id="co_authors" name="co_authors" rows="4" placeholder="List co-authors names, one per line"><?php echo htmlspecialchars($paper['co_authors'] ?? ''); ?></textarea>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($paper['category'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="conference_track">Conference Track:</label>
            <input type="text" id="conference_track" name="conference_track" value="<?php echo htmlspecialchars($paper['conference_track'] ?? ''); ?>">
        </div>
    </div>
    
    <div class="form-group">
        <label>Current File:</label>
        <p><strong><?php echo htmlspecialchars($paper['file_name']); ?></strong> (<?php echo formatFileSize($paper['file_size']); ?>)</p>
    </div>
    
    <div class="form-group">
        <label for="paper_file">Replace File (optional):</label>
        <input type="file" id="paper_file" name="paper_file" accept=".pdf,.doc,.docx">
        <small>Maximum file size: <?php echo formatFileSize(MAX_FILE_SIZE); ?>. Allowed formats: PDF, DOC, DOCX</small>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="/author/view.php?id=<?php echo $paper_id; ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
.paper-form {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 900px;
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

.form-group input[type="text"],
.form-group textarea,
.form-group input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
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
