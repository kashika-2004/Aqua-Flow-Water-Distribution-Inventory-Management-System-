<?php
session_start();

require_once __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) {
	$logoutLog = $pdo->prepare(
		"INSERT INTO activity_logs (user_id, action_event, description, ip_address)
		 VALUES (:user_id, 'USER_LOGOUT', 'User signed out.', :ip_address)"
	);
	$logoutLog->execute([
		'user_id' => (int) $_SESSION['user_id'],
		'ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
	]);
}

session_unset();
session_destroy();
header("Location: login.php");
exit;
?>