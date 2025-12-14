<?php
require_once '../includes/config.php';
requireRole('admin');

$error = '';
$success = '';
$action = $_GET['action'] ?? 'list';
$user_id = $_GET['id'] ?? 0;

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_user'])) {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $first_name = sanitizeInput($_POST['first_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $role = $_POST['role'];
        $institution = sanitizeInput($_POST['institution']);
        $department = sanitizeInput($_POST['department']);
        $expertise = sanitizeInput($_POST['expertise']);
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, role, institution, department, expertise) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$email, $hashed_password, $first_name, $last_name, $role, $institution, $department, $expertise])) {
                $_SESSION['success'] = 'User created successfully!';
                redirect('/admin/users.php');
            } else {
                $error = 'Failed to create user.';
            }
        }
    } elseif (isset($_POST['update_user'])) {
        $user_id = (int)$_POST['user_id'];
        $first_name = sanitizeInput($_POST['first_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $role = $_POST['role'];
        $institution = sanitizeInput($_POST['institution']);
        $department = sanitizeInput($_POST['department']);
        $country = sanitizeInput($_POST['country']);
        $phone = sanitizeInput($_POST['phone']);
        $expertise = sanitizeInput($_POST['expertise']);
        $bio = sanitizeInput($_POST['bio']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, role = ?, institution = ?, department = ?, country = ?, phone = ?, expertise = ?, bio = ?, is_active = ? WHERE id = ?");
        
        if ($stmt->execute([$first_name, $last_name, $role, $institution, $department, $country, $phone, $expertise, $bio, $is_active, $user_id])) {
            $_SESSION['success'] = 'User updated successfully!';
            redirect('/admin/users.php');
        } else {
            $error = 'Failed to update user.';
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        
        // Don't allow deleting yourself
        if ($user_id == $_SESSION['user_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            // Soft delete - set is_active to 0
            $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            
            if ($stmt->execute([$user_id])) {
                $_SESSION['success'] = 'User deactivated successfully!';
                redirect('/admin/users.php');
            } else {
                $error = 'Failed to deactivate user.';
            }
        }
    } elseif (isset($_POST['reset_password'])) {
        $user_id = (int)$_POST['user_id'];
        $new_password = $_POST['new_password'];
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($stmt->execute([$hashed_password, $user_id])) {
            $_SESSION['success'] = 'Password reset successfully!';
            redirect('/admin/users.php?action=edit&id=' . $user_id);
        } else {
            $error = 'Failed to reset password.';
        }
    }
}

// Get user for editing
$user = null;
if ($action === 'edit' && $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        redirect('/admin/users.php');
    }
}

// Get all users for list view
$users = [];
if ($action === 'list') {
    $search = $_GET['search'] ?? '';
    $role_filter = $_GET['role'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    
    $query = "SELECT id, email, first_name, last_name, role, institution, is_active, created_at FROM users WHERE 1=1";
    $params = [];
    
    if ($search) {
        $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR institution LIKE ?)";
        $search_term = "%$search%";
        $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    }
    
    if ($role_filter) {
        $query .= " AND role = ?";
        $params[] = $role_filter;
    }
    
    if ($status_filter !== '') {
        $query .= " AND is_active = ?";
        $params[] = $status_filter;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
}

$pageTitle = 'Manage Users';
include '../includes/header.php';
?>

<h2>User Management</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- User List View -->
    <div class="user-management">
        <div class="actions-bar">
            <a href="?action=create" class="btn btn-primary">Create New User</a>
            
            <form method="GET" class="search-form" style="display: inline-block; margin-left: 20px;">
                <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                <select name="role">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo ($role_filter ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="author" <?php echo ($role_filter ?? '') === 'author' ? 'selected' : ''; ?>>Author</option>
                    <option value="reviewer" <?php echo ($role_filter ?? '') === 'reviewer' ? 'selected' : ''; ?>>Reviewer</option>
                </select>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="1" <?php echo ($status_filter ?? '') === '1' ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ($status_filter ?? '') === '0' ? 'selected' : ''; ?>>Inactive</option>
                </select>
                <button type="submit" class="btn">Filter</button>
                <a href="?" class="btn btn-secondary">Clear</a>
            </form>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Institution</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr class="<?php echo !$u['is_active'] ? 'inactive-row' : ''; ?>">
                        <td><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="role-badge role-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                        <td><?php echo htmlspecialchars($u['institution'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="status-badge status-active">Active</span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                        <td class="actions-cell">
                            <a href="?action=edit&id=<?php echo $u['id']; ?>" class="btn btn-small">Edit</a>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to deactivate this user?');">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-small btn-danger">Deactivate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($action === 'create'): ?>
    <!-- Create User Form -->
    <div class="user-form">
        <h3>Create New User</h3>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="author">Author</option>
                    <option value="reviewer">Reviewer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="institution">Institution:</label>
                <input type="text" id="institution" name="institution">
            </div>
            
            <div class="form-group">
                <label for="department">Department:</label>
                <input type="text" id="department" name="department">
            </div>
            
            <div class="form-group">
                <label for="expertise">Expertise/Research Interests:</label>
                <textarea id="expertise" name="expertise" rows="3"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="create_user" class="btn btn-primary">Create User</button>
                <a href="?" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

<?php elseif ($action === 'edit' && $user): ?>
    <!-- Edit User Form -->
    <div class="user-form">
        <h3>Edit User: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
        
        <form method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                <small>Email cannot be changed</small>
            </div>
            
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="author" <?php echo $user['role'] === 'author' ? 'selected' : ''; ?>>Author</option>
                    <option value="reviewer" <?php echo $user['role'] === 'reviewer' ? 'selected' : ''; ?>>Reviewer</option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="institution">Institution:</label>
                    <input type="text" id="institution" name="institution" value="<?php echo htmlspecialchars($user['institution'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="department">Department:</label>
                    <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="country">Country:</label>
                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="expertise">Expertise/Research Interests:</label>
                <textarea id="expertise" name="expertise" rows="3"><?php echo htmlspecialchars($user['expertise'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="bio">Bio:</label>
                <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                    Account Active
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                <a href="?" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
        
        <!-- Password Reset Section -->
        <div class="password-reset-section">
            <h3>Reset Password</h3>
            <form method="POST" onsubmit="return confirm('Are you sure you want to reset this user\'s password?');">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                </div>
                <button type="submit" name="reset_password" class="btn btn-warning">Reset Password</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<style>
.user-management {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.actions-bar {
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.search-form input[type="text"] {
    padding: 8px;
    width: 200px;
    margin-right: 10px;
}

.search-form select {
    padding: 8px;
    margin-right: 10px;
}

.role-badge {
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: bold;
}

.role-admin {
    background: #dc3545;
    color: white;
}

.role-author {
    background: #007bff;
    color: white;
}

.role-reviewer {
    background: #28a745;
    color: white;
}

.status-badge {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 0.85em;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.inactive-row {
    opacity: 0.6;
}

.actions-cell {
    white-space: nowrap;
}

.user-form {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 800px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.password-reset-section {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #eee;
}
</style>

<?php include '../includes/footer.php'; ?>
