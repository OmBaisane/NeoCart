<?php

require_once __DIR__ .'/includes/header.php';

session_start();
if(!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

header('Location: dashboard.php');
exit();

?>

<h2>Welcome Admin</h2>
<a href="logout.php">Logout</a>

<?php require_once __DIR__ .'/includes/footer.php'; ?>