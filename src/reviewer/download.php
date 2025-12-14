<?php
require_once '../includes/config.php';
requireRole('reviewer');

$paper_id = $_GET['id'] ?? 0;

// Verify reviewer has access to this paper and file exists
$stmt = $pdo->prepare("
    SELECT p.file_path, p.file_name
    FROM papers p
    JOIN reviewer_assignments ra ON p.id = ra.paper_id
    WHERE p.id = ? AND ra.reviewer_id = ? AND p.is_active = 1
");
$stmt->execute([$paper_id, $_SESSION['user_id']]);
$paper = $stmt->fetch();

if (!$paper) {
    $_SESSION['error'] = 'Paper not found or access denied.';
    redirect('/reviewer/');
}

$file_path = UPLOAD_PATH . $paper['file_path'];

if (!file_exists($file_path)) {
    $_SESSION['error'] = 'File not found.';
    redirect('/reviewer/');
}

// Set appropriate headers for file download
$file_extension = pathinfo($paper['file_name'], PATHINFO_EXTENSION);
$content_type = 'application/octet-stream';

switch (strtolower($file_extension)) {
    case 'pdf':
        $content_type = 'application/pdf';
        break;
    case 'doc':
        $content_type = 'application/msword';
        break;
    case 'docx':
        $content_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        break;
}

header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $paper['file_name'] . '"');
header('Content-Length: ' . filesize($file_path));

readfile($file_path);
exit;
?>