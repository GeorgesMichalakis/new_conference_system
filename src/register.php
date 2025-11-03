<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $institution = sanitizeInput($_POST['institution']);
    $role = $_POST['role'];
    
    // Validation
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!in_array($role, ['author', 'reviewer'])) {
        $error = 'Invalid role selected.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            // Create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, role, institution) VALUES (?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$email, $hashed_password, $first_name, $last_name, $role, $institution])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register';
include 'includes/header.php';
?>

<div class="auth-form">
    <h2>Register</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="institution">Institution:</label>
            <input type="text" id="institution" name="institution" value="<?php echo isset($institution) ? htmlspecialchars($institution) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="">Select Role</option>
                <option value="author" <?php echo (isset($role) && $role === 'author') ? 'selected' : ''; ?>>Author</option>
                <option value="reviewer" <?php echo (isset($role) && $role === 'reviewer') ? 'selected' : ''; ?>>Reviewer</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
        
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    
    <p><a href="/login.php">Already have an account? Login here</a></p>
</div>

<?php include 'includes/footer.php'; ?>