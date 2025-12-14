<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Conference System</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1><a href="/">Conference System</a></h1>
            <ul class="nav-menu">
                <?php if (isLoggedIn()): ?>
                    <li><span>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?></span></li>
                    <?php if (hasRole('admin')): ?>
                        <li><a href="/admin/">Admin Panel</a></li>
                    <?php elseif (hasRole('reviewer')): ?>
                        <li><a href="/reviewer/">Review Papers</a></li>
                    <?php elseif (hasRole('author')): ?>
                        <li><a href="/author/">My Papers</a></li>
                        <li><a href="/author/submit.php">Submit Paper</a></li>
                    <?php endif; ?>
                    <li><a href="/profile.php">Profile</a></li>
                    <li><a href="/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/login.php">Login</a></li>
                    <li><a href="/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>