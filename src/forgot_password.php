<?php
require_once 'includes/config.php';

$error = '';
$success = '';
$step = $_GET['step'] ?? 'request';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 'request') {
        $email = sanitizeInput($_POST['email']);
        
        if (empty($email)) {
            $error = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } else {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $reset_token = bin2hex(random_bytes(32));
                $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                $stmt->execute([$reset_token, $reset_expiry, $user['id']]);
                
                // In a real application, send email here
                // For now, we'll just show the reset link
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/forgot_password.php?step=reset&token=" . $reset_token;
                
                $success = "Password reset instructions have been sent to your email. 
                           <br><br><strong>For testing purposes, use this link:</strong><br>
                           <a href='$reset_link'>$reset_link</a>";
            } else {
                // Don't reveal if email exists or not (security best practice)
                $success = "If an account with that email exists, you will receive password reset instructions.";
            }
        }
    } elseif ($step === 'reset') {
        $token = sanitizeInput($_POST['token']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($new_password) || empty($confirm_password)) {
            $error = 'All fields are required.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Verify token
            $stmt = $pdo->prepare("
                SELECT id FROM users 
                WHERE reset_token = ? 
                AND reset_token_expiry > NOW() 
                AND is_active = 1
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Update password and clear reset token
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET password = ?, reset_token = NULL, reset_token_expiry = NULL, updated_at = NOW()
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$hashed_password, $user['id']])) {
                    $_SESSION['success'] = 'Password reset successfully! Please log in with your new password.';
                    redirect('/login.php');
                } else {
                    $error = 'Failed to reset password. Please try again.';
                }
            } else {
                $error = 'Invalid or expired reset token. Please request a new password reset.';
            }
        }
    }
}

// If viewing reset page, verify token
if ($step === 'reset' && !empty($token) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare("
        SELECT id FROM users 
        WHERE reset_token = ? 
        AND reset_token_expiry > NOW() 
        AND is_active = 1
    ");
    $stmt->execute([$token]);
    
    if (!$stmt->fetch()) {
        $error = 'Invalid or expired reset token. Please request a new password reset.';
        $step = 'request';
    }
}

$pageTitle = 'Forgot Password';
include 'includes/header.php';
?>

<div class="forgot-password-container">
    <?php if ($step === 'request'): ?>
        <div class="forgot-password-form">
            <h2>Forgot Password</h2>
            <p>Enter your email address and we'll send you instructions to reset your password.</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" required placeholder="your.email@example.com">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Send Reset Instructions</button>
                </form>
            <?php endif; ?>
            
            <div class="form-footer">
                <a href="/login.php">Back to Login</a>
            </div>
        </div>
    
    <?php elseif ($step === 'reset'): ?>
        <div class="forgot-password-form">
            <h2>Reset Password</h2>
            <p>Enter your new password below.</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <small>Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
            
            <div class="form-footer">
                <a href="/login.php">Back to Login</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.forgot-password-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 60vh;
    padding: 40px 20px;
}

.forgot-password-form {
    background: #fff;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    max-width: 450px;
    width: 100%;
}

.forgot-password-form h2 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.forgot-password-form > p {
    color: #666;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group small {
    display: block;
    color: #666;
    margin-top: 5px;
}

.btn-block {
    width: 100%;
}

.form-footer {
    margin-top: 20px;
    text-align: center;
}

.form-footer a {
    color: #3498db;
    text-decoration: none;
}

.form-footer a:hover {
    text-decoration: underline;
}
</style>

<?php include 'includes/footer.php'; ?>
