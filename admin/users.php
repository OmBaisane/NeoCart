<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ .'/includes/header.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php'); 
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting user!";
    }
    header('Location: users.php');
    exit();
}

// Get user statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
?>

<style>
.user-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #007bff;
}
.user-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
}
.user-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
}
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    transition: transform 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.delete-btn {
    border-radius: 20px;
    transition: all 0.3s ease;
}
.delete-btn:hover {
    transform: scale(1.05);
}
</style>

<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-primary">
                <i class="fas fa-users me-2"></i>Manage Users
            </h2>
            <p class="text-muted mb-0">Total <?php echo $total_users; ?> registered users</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center py-4">
                    <i class="fas fa-users fa-2x mb-2 opacity-75"></i>
                    <h3 class="mb-0"><?php echo $total_users; ?></h3>
                    <small>Total Users</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Users List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Users List</h5>
        </div>
        <div class="card-body">
            <?php
            $res = $conn->query("SELECT id, name, email, created_at FROM users ORDER BY id DESC");
            if ($res->num_rows === 0) {
                echo '<div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted mb-3">No Users Found</h4>
                        <p class="text-muted">Users will appear here when they register on your site.</p>
                      </div>';
            } else {
                echo '<div class="row">';
                while ($u = $res->fetch_assoc()) {
                    $initial = strtoupper(substr($u['name'], 0, 1));
                    $join_date = date('M j, Y', strtotime($u['created_at']));
                    $join_time = date('g:i A', strtotime($u['created_at']));
                    
                    echo '<div class="col-md-6 col-lg-4 mb-3">
                            <div class="card user-card h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="user-avatar">
                                                '.$initial.'
                                            </div>
                                        </div>
                                        <div class="col">
                                            <h6 class="mb-1 text-dark">'.htmlspecialchars($u['name']).'</h6>
                                            <p class="mb-1 text-muted small">'.htmlspecialchars($u['email']).'</p>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>'.$join_date.'<br>
                                                <i class="fas fa-clock me-1"></i>'.$join_time.'
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <a class="btn btn-sm btn-danger delete-btn" 
                                               href="users.php?delete='.urlencode($u['id']).'" 
                                               onclick="return confirm(\'Delete user \\\''.htmlspecialchars(addslashes($u['name'])).'\\\'?\\n\\nThis action cannot be undone!\')"
                                               title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          </div>';
                }
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <!-- User Management Info -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>User Statistics</h6>
                </div>
                <div class="card-body text-center">
                    <h1 class="text-primary"><?php echo $total_users; ?></h1>
                    <p class="text-muted mb-0">Registered Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Quick Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Monitor user activity regularly</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Remove inactive accounts</li>
                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i>Keep user data secure</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ .'/includes/footer.php'; ?>