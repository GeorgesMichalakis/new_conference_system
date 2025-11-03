<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT id, password, first_name, last_name, role FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    redirect('/admin/');
                    break;
                case 'reviewer':
                    redirect('/reviewer/');
                    break;
                case 'author':
                    redirect('/author/');
                    break;
                default:
                    redirect('/index.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>

<div class="auth-form">
    <h2>Login</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    
    <p><a href="/register.php">Don't have an account? Register here</a></p>
    
    <div class="demo-credentials">
        <h4>Demo Credentials:</h4>
        <p><strong>Admin:</strong> admin@conference.com / password</p>
        <p><strong>Author:</strong> author@conference.com / password</p>
        <p><strong>Reviewer:</strong> reviewer@conference.com / password</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>