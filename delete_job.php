<?php
require_once '_auth.php';
include 'db.php';
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    mysqli_query($conn, "DELETE FROM jobs WHERE id='$id'");
}

header('Location: dashboard.php?success=Job deleted successfully');
exit;
?>
