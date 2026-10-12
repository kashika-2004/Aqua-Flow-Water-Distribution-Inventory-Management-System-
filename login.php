<?php
session_start();

require_once __DIR__ . '/db.php';

if (empty($_SESSION['login_csrf'])) {
	$_SESSION['login_csrf'] = bin2hex(random_bytes(32));
}

$errorMessage = '';
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$submittedToken = $_POST['csrf_token'] ?? '';

	if (!is_string($submittedToken) || !hash_equals($_SESSION['login_csrf'], $submittedToken)) {
		$errorMessage = 'Your session expired. Refresh the page and try again.';
	} elseif (isset($_POST['logout'])) {
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
		$_SESSION = [];
		session_destroy();
		header('Location: login.php');
		exit;
	} else {
		$identifier = trim((string) ($_POST['identifier'] ?? ''));
		$password = (string) ($_POST['password'] ?? '');

		if ($identifier === '' || $password === '') {
			$errorMessage = 'Enter your username or email and password.';
		} else {
			$statement = $pdo->prepare(
				'SELECT id, username, email, password_hash, full_name, role, status
				 FROM users
				 WHERE username = :username OR email = :email
				 LIMIT 1'
			);
			$statement->execute([
				'username' => $identifier,
				'email' => $identifier,
			]);
			$account = $statement->fetch(PDO::FETCH_ASSOC);

			if ($account && password_verify($password, $account['password_hash']) && $account['status'] === 'ACTIVE') {
				session_regenerate_id(true);
				$_SESSION['user_id'] = (int) $account['id'];
				$_SESSION['username'] = $account['username'];
				$_SESSION['full_name'] = $account['full_name'];
				$_SESSION['role'] = $account['role'];
				$_SESSION['login_csrf'] = bin2hex(random_bytes(32));
				$loginLog = $pdo->prepare(
					"INSERT INTO activity_logs (user_id, action_event, description, ip_address)
					 VALUES (:user_id, 'USER_LOGIN', 'User signed in.', :ip_address)"
				);
				$loginLog->execute([
					'user_id' => (int) $account['id'],
					'ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
				]);
				if ($account['role'] === 'ADMIN') {
					header('Location: admin_workspace.php');
					exit;
				} elseif ($account['role'] === 'CUSTOMER') {
					header('Location: customer_dashboard.php');
					exit;
				}
			} else {
				$errorMessage = 'We could not sign you in with those details.';
			}
		}
	}
}

$isSignedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#e9f5f1">
	<title>Sign in | AquaFlow</title>
	<style>
		:root {
			color-scheme: light;
			--ink: #163b3a;
			--muted: #647e79;
			--teal: #087e76;
			--teal-dark: #07665f;
			--line: #d7e5df;
			--paper: #fffefa;
			--coral: #df765d;
		}

		* { box-sizing: border-box; }

		body {
			min-height: 100vh;
			margin: 0;
			display: grid;
			place-items: center;
			padding: 32px 20px;
			color: var(--ink);
			background-color: #e9f5f1;
			background-image: radial-gradient(#b9d9cf 0.8px, transparent 0.8px);
			background-size: 18px 18px;
			font-family: "Segoe UI", Tahoma, sans-serif;
		}

		.shell {
			width: min(100%, 920px);
			min-height: 560px;
			display: grid;
			grid-template-columns: 0.92fr 1.08fr;
			overflow: hidden;
			background: var(--paper);
			border: 1px solid rgba(22, 59, 58, 0.08);
			border-radius: 8px;
			box-shadow: 0 24px 70px rgba(23, 74, 64, 0.14);
			animation: arrive 500ms ease-out both;
		}

		.brand-panel {
			position: relative;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			min-height: 100%;
			overflow: hidden;
			padding: 38px;
			color: #f5fffa;
			background: #176e66;
		}

		.brand-panel::before,
		.brand-panel::after {
			position: absolute;
			content: "";
			width: 320px;
			height: 320px;
			border: 1px solid rgba(231, 250, 240, 0.2);
			border-radius: 50%;
			pointer-events: none;
		}

		.brand-panel::before { right: -184px; bottom: -90px; }
		.brand-panel::after { right: -142px; bottom: -48px; }

		.wordmark {
			display: flex;
			align-items: center;
			gap: 11px;
			font-size: 17px;
			font-weight: 700;
			letter-spacing: 0.04em;
		}

		.mark {
			width: 31px;
			height: 31px;
			display: grid;
			place-items: center;
			border: 1px solid rgba(255, 255, 255, 0.55);
			border-radius: 50% 50% 50% 8px;
			color: #f2b49b;
			font-family: Georgia, serif;
			font-size: 18px;
			transform: rotate(-45deg);
		}

		.brand-copy { position: relative; z-index: 1; margin: auto 0; padding: 50px 0; }

		.eyebrow {
			margin: 0 0 15px;
			color: #b8e4d5;
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 0.15em;
			text-transform: uppercase;
		}

		h1 {
			max-width: 320px;
			margin: 0;
			font-family: Georgia, "Times New Roman", serif;
			font-size: clamp(36px, 5vw, 52px);
			font-weight: 400;
			line-height: 1.06;
		}

		.brand-copy p:last-child {
			max-width: 290px;
			margin: 20px 0 0;
			color: #d5eee5;
			font-size: 14px;
			line-height: 1.7;
		}

		.brand-foot {
			position: relative;
			z-index: 1;
			margin: 0;
			color: #c0e4d7;
			font-size: 12px;
		}

		.form-panel {
			display: flex;
			flex-direction: column;
			justify-content: center;
			padding: 64px clamp(30px, 6vw, 76px);
		}

		.form-panel h2 {
			margin: 0;
			font-family: Georgia, "Times New Roman", serif;
			font-size: 34px;
			font-weight: 400;
			letter-spacing: 0;
		}

		.intro { margin: 9px 0 32px; color: var(--muted); font-size: 14px; }
		.field { margin-bottom: 19px; }

		label {
			display: block;
			margin-bottom: 8px;
			color: #365d57;
			font-size: 12px;
			font-weight: 700;
		}

		input {
			width: 100%;
			min-height: 48px;
			padding: 0 14px;
			border: 1px solid var(--line);
			border-radius: 4px;
			outline: none;
			background: #fff;
			color: var(--ink);
			font: inherit;
			font-size: 14px;
			transition: border-color 150ms ease, box-shadow 150ms ease;
		}

		input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(8, 126, 118, 0.12); }

		.submit {
			width: 100%;
			min-height: 49px;
			margin-top: 5px;
			border: 0;
			border-radius: 4px;
			background: var(--teal);
			color: white;
			cursor: pointer;
			font: inherit;
			font-size: 14px;
			font-weight: 700;
			transition: background 150ms ease, transform 150ms ease;
		}

		.submit:hover { background: var(--teal-dark); }
		.submit:active { transform: translateY(1px); }

		.message {
			margin: 0 0 20px;
			padding: 12px 13px;
			border-left: 3px solid var(--coral);
			background: #fff3ee;
			color: #8a4434;
			font-size: 13px;
			line-height: 1.5;
		}

		.signed-in { padding: 16px 0; }
		.signed-in .eyebrow { color: var(--teal); }
		.signed-in h2 { line-height: 1.2; }
		.role { margin: 12px 0 26px; color: var(--muted); font-size: 14px; }
		.signout { margin-top: 0; }
		.register-link { margin: 19px 0 0; color: var(--muted); text-align: center; font-size: 13px; }
		.register-link a { color: var(--teal-dark); font-weight: 700; text-decoration-thickness: 1px; text-underline-offset: 3px; }

		@keyframes arrive {
			from { opacity: 0; transform: translateY(8px); }
			to { opacity: 1; transform: translateY(0); }
		}

		@media (max-width: 680px) {
			body { padding: 16px; }
			.shell { grid-template-columns: 1fr; min-height: 0; }
			.brand-panel { min-height: 220px; padding: 24px; }
			.brand-copy { padding: 32px 0 18px; }
			.brand-copy h1 { font-size: 38px; }
			.brand-copy p:last-child { margin-top: 10px; }
			.brand-foot { display: none; }
			.form-panel { padding: 34px 24px 40px; }
		}

		@media (prefers-reduced-motion: reduce) {
			*, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
		}
	</style>
</head>
<body>
	<main class="shell">
		<section class="brand-panel" aria-label="AquaFlow">
			<div class="wordmark"><span class="mark" aria-hidden="true">A</span> AQUAFLOW</div>
			<div class="brand-copy">
				<p class="eyebrow">A better flow, every day</p>
				<h1>Welcome back to AquaFlow.</h1>
				<p>Sign in to continue managing your water business with clarity and care.</p>
			</div>
			<p class="brand-foot">Pure water. Thoughtful service.</p>
		</section>

		<section class="form-panel" aria-labelledby="form-title">
			<?php if ($isSignedIn): ?>
				<div class="signed-in">
					<p class="eyebrow">You're signed in</p>
					<h2 id="form-title">Welcome, <?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?>.</h2>
					<p class="role"><?= htmlspecialchars(str_replace('_', ' ', $_SESSION['role']), ENT_QUOTES, 'UTF-8') ?></p>
					<form method="post" action="login.php">
						<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['login_csrf'], ENT_QUOTES, 'UTF-8') ?>">
						<button class="submit signout" type="submit" name="logout" value="1">Sign out</button>
					</form>
					<?php if ($_SESSION['role'] === 'ADMIN'): ?>
						<p class="register-link"><a href="admindashboard.php">Open admin dashboard</a></p>
					<?php elseif ($_SESSION['role'] === 'CUSTOMER'): ?>
						<p class="register-link"><a href="customer_dashboard.php">Open customer portal</a></p>
					<?php endif; ?>
				</div>
			<?php else: ?>
				<h2 id="form-title">Sign in</h2>
				<p class="intro">Use your AquaFlow account to continue.</p>

				<?php if ($errorMessage !== ''): ?>
					<p class="message" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
				<?php endif; ?>

				<form method="post" action="login.php">
					<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['login_csrf'], ENT_QUOTES, 'UTF-8') ?>">
					<div class="field">
						<label for="identifier">Username or email</label>
						<input id="identifier" name="identifier" type="text" autocomplete="username" value="<?= htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8') ?>" required autofocus>
					</div>
					<div class="field">
						<label for="password">Password</label>
						<input id="password" name="password" type="password" autocomplete="current-password" required>
					</div>
					<button class="submit" type="submit">Sign in</button>
				</form>
				<p class="register-link">New to AquaFlow? <a href="customer_register.php">Create a customer account</a></p>
			<?php endif; ?>
		</section>
	</main>
</body>
</html>
