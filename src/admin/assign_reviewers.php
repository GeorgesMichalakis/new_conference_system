<?php
require_once '../includes/config.php';
requireRole('admin');

$paper_id = $_GET['paper_id'] ?? 0;
$success = '';
$error = '';

// Get paper details if paper_id is provided
$paper = null;
if ($paper_id) {
    $stmt = $pdo->prepare("SELECT p.*, u.first_name, u.last_name FROM papers p JOIN users u ON p.author_id = u.id WHERE p.id = ? AND p.is_active = 1");
    $stmt->execute([$paper_id]);
    $paper = $stmt->fetch();
    
    if (!$paper) {
        $_SESSION['error'] = 'Paper not found.';
        redirect('/admin/papers.php');
    }
}

// Handle reviewer assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_reviewer'])) {
    $paper_id = (int)$_POST['paper_id'];
    $reviewer_id = (int)$_POST['reviewer_id'];
    $deadline = $_POST['deadline'];
    
    // Check if assignment already exists
    $stmt = $pdo->prepare("SELECT id FROM reviewer_assignments WHERE paper_id = ? AND reviewer_id = ?");
    $stmt->execute([$paper_id, $reviewer_id]);
    
    if ($stmt->fetch()) {
        $error = 'This reviewer is already assigned to this paper.';
    } else {
        // Create reviewer assignment
        $stmt = $pdo->prepare("INSERT INTO reviewer_assignments (paper_id, reviewer_id, assigned_by, assigned_date, deadline, status) VALUES (?, ?, ?, NOW(), ?, 'pending')");
        
        if ($stmt->execute([$paper_id, $reviewer_id, $_SESSION['user_id'], $deadline])) {
            // Update paper status to under_review if it's still submitted
            $pdo->prepare("UPDATE papers SET status = 'under_review' WHERE id = ? AND status = 'submitted'")->execute([$paper_id]);
            
            $success = 'Reviewer assigned successfully!';
        } else {
            $error = 'Failed to assign reviewer.';
        }
    }
}

// Get all reviewers
$stmt = $pdo->query("SELECT id, first_name, last_name, email, expertise FROM users WHERE role = 'reviewer' AND is_active = 1 ORDER BY last_name, first_name");
$reviewers = $stmt->fetchAll();

// Get papers needing reviewers if no specific paper selected
$papers_needing_reviewers = [];
if (!$paper_id) {
    $stmt = $pdo->query("
        SELECT p.id, p.title, u.first_name, u.last_name,
               (SELECT COUNT(*) FROM reviewer_assignments WHERE paper_id = p.id) as reviewer_count
        FROM papers p
        JOIN users u ON p.author_id = u.id
        WHERE p.is_active = 1 AND p.status IN ('submitted', 'under_review')
        ORDER BY p.submission_date ASC
    ");
    $papers_needing_reviewers = $stmt->fetchAll();
}

// Get current assignments for the paper
$current_assignments = [];
if ($paper_id) {
    $stmt = $pdo->prepare("
        SELECT ra.id, ra.status, ra.deadline, ra.assigned_date,
               u.first_name, u.last_name, u.email,
               CASE WHEN r.id IS NOT NULL THEN 'completed' ELSE ra.status END as review_status
        FROM reviewer_assignments ra
        JOIN users u ON ra.reviewer_id = u.id
        LEFT JOIN reviews r ON ra.paper_id = r.paper_id AND ra.reviewer_id = r.reviewer_id AND r.review_status = 'completed'
        WHERE ra.paper_id = ?
        ORDER BY ra.assigned_date DESC
    ");
    $stmt->execute([$paper_id]);
    $current_assignments = $stmt->fetchAll();
}

$pageTitle = 'Assign Reviewers';
include '../includes/header.php';
?>

<h2>Assign Reviewers</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($paper): ?>
    <div class="paper-info">
        <h3>Paper: <?php echo htmlspecialchars($paper['title']); ?></h3>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($paper['first_name'] . ' ' . $paper['last_name']); ?></p>
        <p><strong>Submitted:</strong> <?php echo date('F j, Y', strtotime($paper['submission_date'])); ?></p>
    </div>
    
    <?php if (!empty($current_assignments)): ?>
        <div class="current-assignments">
            <h4>Current Reviewers</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        <th>Email</th>
                        <th>Assigned</th>
                        <th>Deadline</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_assignments as $assignment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($assignment['assigned_date'])); ?></td>
                            <td><?php echo $assignment['deadline'] ? date('M j, Y', strtotime($assignment['deadline'])) : 'No deadline'; ?></td>
                            <td>
                                <span class="status status-<?php echo $assignment['review_status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $assignment['review_status'])); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <div class="assign-form">
        <h4>Assign New Reviewer</h4>
        <form method="POST">
            <input type="hidden" name="paper_id" value="<?php echo $paper['id']; ?>">
            
            <div class="form-group">
                <label for="reviewer_id">Select Reviewer:</label>
                <select id="reviewer_id" name="reviewer_id" required>
                    <option value="">Choose a reviewer</option>
                    <?php foreach ($reviewers as $reviewer): ?>
                        <option value="<?php echo $reviewer['id']; ?>">
                            <?php echo htmlspecialchars($reviewer['first_name'] . ' ' . $reviewer['last_name'] . ' (' . $reviewer['email'] . ')'); ?>
                            <?php if ($reviewer['expertise']): ?>
                                - <?php echo htmlspecialchars($reviewer['expertise']); ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="deadline">Review Deadline:</label>
                <input type="date" id="deadline" name="deadline" min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <button type="submit" name="assign_reviewer" class="btn btn-primary">Assign Reviewer</button>
            <a href="/admin/papers.php" class="btn btn-secondary">Back to Papers</a>
        </form>
    </div>
    
<?php else: ?>
    <div class="papers-list">
        <h3>Papers Needing Reviewers</h3>
        
        <?php if (!empty($papers_needing_reviewers)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Current Reviewers</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($papers_needing_reviewers as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['title']); ?></td>
                            <td><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                            <td><?php echo $p['reviewer_count']; ?></td>
                            <td>
                                <a href="?paper_id=<?php echo $p['id']; ?>" class="btn btn-small btn-primary">Assign Reviewers</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No papers need reviewer assignments at this time.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>