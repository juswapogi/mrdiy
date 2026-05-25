<?php
require_once '_auth.php';
include 'db.php';
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    mysqli_query($conn, "UPDATE users SET status='Rejected' WHERE id='$id' AND role='user'");
}

header('Location: applicants.php?success=Applicant rejected');
exit;
?>
