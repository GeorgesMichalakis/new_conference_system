<?php
require_once '../includes/config.php';
requireRole('author');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $abstract = sanitizeInput($_POST['abstract']);
    $keywords = sanitizeInput($_POST['keywords']);
    $co_authors = sanitizeInput($_POST['co_authors']);
    
    // Validation
    if (empty($title) || empty($abstract)) {
        $error = 'Title and abstract are required.';
    } elseif (empty($_FILES['paper_file']['name'])) {
        $error = 'Please select a file to upload.';
    } else {
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
            $file_path = UPLOAD_PATH . $unique_filename;
            
            // Create upload directory if it doesn't exist
            if (!is_dir(UPLOAD_PATH)) {
                mkdir(UPLOAD_PATH, 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Save to database
                $stmt = $pdo->prepare("INSERT INTO papers (title, abstract, keywords, author_id, co_authors, file_path, file_name, file_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$title, $abstract, $keywords, $_SESSION['user_id'], $co_authors, $unique_filename, $file['name'], $file['size']])) {
                    $_SESSION['success'] = 'Paper submitted successfully!';
                    redirect('/author/');
                } else {
                    unlink($file_path); // Remove uploaded file
                    $error = 'Failed to save paper information.';
                }
            } else {
                $error = 'Failed to upload file.';
            }
        }
    }
}

$pageTitle = 'Submit Paper';
include '../includes/header.php';
?>

<h2>Submit New Paper</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="paper-form">
    <div class="form-group">
        <label for="title">Paper Title:</label>
        <input type="text" id="title" name="title" required value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
    </div>
    
    <div class="form-group">
        <label for="abstract">Abstract:</label>
        <textarea id="abstract" name="abstract" rows="8" required><?php echo isset($abstract) ? htmlspecialchars($abstract) : ''; ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="keywords">Keywords (comma-separated):</label>
        <input type="text" id="keywords" name="keywords" value="<?php echo isset($keywords) ? htmlspecialchars($keywords) : ''; ?>">
    </div>
    
    <div class="form-group">
        <label for="co_authors">Co-Authors (optional):</label>
        <textarea id="co_authors" name="co_authors" rows="3" placeholder="List co-authors names, one per line"><?php echo isset($co_authors) ? htmlspecialchars($co_authors) : ''; ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="paper_file">Paper File:</label>
        <input type="file" id="paper_file" name="paper_file" accept=".pdf,.doc,.docx" required>
        <small>Maximum file size: <?php echo formatFileSize(MAX_FILE_SIZE); ?>. Allowed formats: PDF, DOC, DOCX</small>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Submit Paper</button>
        <a href="/author/" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php include '../includes/footer.php'; ?>