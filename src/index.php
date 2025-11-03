<?php
require_once 'includes/config.php';

$pageTitle = 'Home';
include 'includes/header.php';
?>

<div class="hero">
    <h2>Welcome to the Conference Paper Submission System</h2>
    <p>A simple platform for managing academic paper submissions and reviews</p>
</div>

<div class="features">
    <div class="feature-card">
        <h3>For Authors</h3>
        <p>Submit your papers and track their review status</p>
        <?php if (!isLoggedIn()): ?>
            <a href="/register.php" class="btn btn-primary">Register as Author</a>
        <?php endif; ?>
    </div>
    
    <div class="feature-card">
        <h3>For Reviewers</h3>
        <p>Review assigned papers and provide feedback</p>
        <?php if (!isLoggedIn()): ?>
            <a href="/login.php" class="btn btn-secondary">Reviewer Login</a>
        <?php endif; ?>
    </div>
    
    <div class="feature-card">
        <h3>For Administrators</h3>
        <p>Manage users, assignments, and make decisions</p>
        <?php if (!isLoggedIn()): ?>
            <a href="/login.php" class="btn btn-secondary">Admin Login</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isLoggedIn()): ?>
<div class="dashboard">
    <h3>Quick Stats</h3>
    <?php
    if (hasRole('admin')) {
        $stmt = $pdo->query("SELECT 
            (SELECT COUNT(*) FROM papers WHERE is_active = 1) as total_papers,
            (SELECT COUNT(*) FROM users WHERE role = 'author' AND is_active = 1) as total_authors,
            (SELECT COUNT(*) FROM users WHERE role = 'reviewer' AND is_active = 1) as total_reviewers,
            (SELECT COUNT(*) FROM reviews WHERE review_status = 'completed') as completed_reviews
        ");
        $stats = $stmt->fetch();
        ?>
        <div class="stats">
            <div class="stat">Papers: <?php echo $stats['total_papers']; ?></div>
            <div class="stat">Authors: <?php echo $stats['total_authors']; ?></div>
            <div class="stat">Reviewers: <?php echo $stats['total_reviewers']; ?></div>
            <div class="stat">Reviews: <?php echo $stats['completed_reviews']; ?></div>
        </div>
        <?php
    } elseif (hasRole('author')) {
        $stmt = $pdo->prepare("SELECT 
            COUNT(*) as my_papers,
            SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
            SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as under_review
            FROM papers WHERE author_id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $stats = $stmt->fetch();
        ?>
        <div class="stats">
            <div class="stat">My Papers: <?php echo $stats['my_papers']; ?></div>
            <div class="stat">Accepted: <?php echo $stats['accepted']; ?></div>
            <div class="stat">Under Review: <?php echo $stats['under_review']; ?></div>
        </div>
        <?php
    } elseif (hasRole('reviewer')) {
        $stmt = $pdo->prepare("SELECT 
            COUNT(*) as assigned_papers,
            SUM(CASE WHEN review_status = 'completed' THEN 1 ELSE 0 END) as completed_reviews
            FROM reviews WHERE reviewer_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $stats = $stmt->fetch();
        ?>
        <div class="stats">
            <div class="stat">Assigned Papers: <?php echo $stats['assigned_papers']; ?></div>
            <div class="stat">Completed Reviews: <?php echo $stats['completed_reviews']; ?></div>
        </div>
        <?php
    }
    ?>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>