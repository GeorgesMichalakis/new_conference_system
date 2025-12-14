<?php
require_once 'includes/config.php';
requireLogin();

$error = '';
$success = '';
$user_id = $_SESSION['user_id'];

// Get current user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $affiliation = sanitizeInput($_POST['affiliation']);
        $bio = sanitizeInput($_POST['bio']);
        $research_interests = sanitizeInput($_POST['research_interests']);
        $website = sanitizeInput($_POST['website']);
        
        // Validation
        if (empty($full_name) || empty($email)) {
            $error = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } else {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            
            if ($stmt->fetch()) {
                $error = 'Email is already in use by another user.';
            } else {
                // Update user profile
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = ?, email = ?, affiliation = ?, bio = ?, 
                        research_interests = ?, website = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$full_name, $email, $affiliation, $bio, $research_interests, $website, $user_id])) {
                    $success = 'Profile updated successfully!';
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All password fields are required.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $user_id])) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password.';
            }
        }
    }
}

$pageTitle = 'My Profile';
include 'includes/header.php';
?>

<h2>My Profile</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="profile-container">
    <div class="profile-section">
        <h3>Account Information</h3>
        <form method="POST" class="profile-form">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-group">
                <label for="full_name">Full Name: *</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?php echo htmlspecialchars($user['full_name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email: *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            
            <div class="form-group">
                <label for="affiliation">Affiliation:</label>
                <input type="text" id="affiliation" name="affiliation" 
                       value="<?php echo htmlspecialchars($user['affiliation'] ?? ''); ?>"
                       placeholder="University or Organization">
            </div>
            
            <div class="form-group">
                <label for="website">Website:</label>
                <input type="url" id="website" name="website" 
                       value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>"
                       placeholder="https://example.com">
            </div>
            
            <div class="form-group">
                <label for="research_interests">Research Interests:</label>
                <textarea id="research_interests" name="research_interests" rows="4"
                          placeholder="Machine Learning, Natural Language Processing, etc."><?php echo htmlspecialchars($user['research_interests'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="bio">Biography:</label>
                <textarea id="bio" name="bio" rows="6" 
                          placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Role:</label>
                <input type="text" value="<?php echo ucfirst($user['role']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label>Member Since:</label>
                <input type="text" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" disabled>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
    
    <div class="profile-section">
        <h3>Change Password</h3>
        <form method="POST" class="profile-form">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label for="current_password">Current Password: *</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password: *</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
                <small>Minimum 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password: *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>

<style>
.profile-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    max-width: 1200px;
}

.profile-section {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-section h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e1e8ed;
}

.profile-form .form-group {
    margin-bottom: 20px;
}

.profile-form label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.profile-form input[type="text"],
.profile-form input[type="email"],
.profile-form input[type="url"],
.profile-form input[type="password"],
.profile-form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.profile-form input[disabled] {
    background: #f5f5f5;
    color: #666;
}

.profile-form textarea {
    resize: vertical;
    font-family: inherit;
}

.profile-form small {
    display: block;
    color: #666;
    margin-top: 5px;
}

.profile-form button {
    margin-top: 10px;
}

@media (max-width: 768px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
