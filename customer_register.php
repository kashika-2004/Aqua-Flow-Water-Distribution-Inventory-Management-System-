<?php
session_start();

require_once __DIR__ . '/db.php';

if (empty($_SESSION['register_csrf'])) {
	$_SESSION['register_csrf'] = bin2hex(random_bytes(32));
}

$errorMessage = '';
$fullName = '';
$username = '';
$email = '';
$phone = '';
$deliveryAddress = '';
$city = '';
$postalCode = '';

$postValue = static fn (string $key): string => isset($_POST[$key]) && is_string($_POST[$key])
	? trim($_POST[$key])
	: '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$submittedToken = $_POST['csrf_token'] ?? '';
	$fullName = $postValue('full_name');
	$username = $postValue('username');
	$email = $postValue('email');
	$phone = $postValue('phone');
	$deliveryAddress = $postValue('delivery_address');
	$city = $postValue('city');
	$postalCode = $postValue('postal_code');
	$password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
	$confirmPassword = isset($_POST['confirm_password']) && is_string($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

	if (!is_string($submittedToken) || !hash_equals($_SESSION['register_csrf'], $submittedToken)) {
		$errorMessage = 'Your session expired. Refresh the page and try again.';
	} elseif ($fullName === '' || $username === '' || $email === '' || $deliveryAddress === '' || $password === '') {
		$errorMessage = 'Complete all required fields to create your account.';
	} elseif (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
		$errorMessage = 'Choose a username with 3 to 50 letters, numbers, dots, hyphens, or underscores.';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
		$errorMessage = 'Enter a valid email address (up to 100 characters).';
	} elseif (strlen($fullName) > 100 || strlen($phone) > 20 || strlen($city) > 50 || strlen($postalCode) > 20) {
		$errorMessage = 'One or more fields exceed the allowed length.';
	} elseif (strlen($password) < 8) {
		$errorMessage = 'Use a password with at least 8 characters.';
	} elseif ($password !== $confirmPassword) {
		$errorMessage = 'The passwords do not match.';
	} else {
		try {
			$pdo->beginTransaction();
			$userStatement = $pdo->prepare(
				'INSERT INTO users (username, email, password_hash, full_name, phone, role, status)
				 VALUES (:username, :email, :password_hash, :full_name, :phone, :role, :status)'
			);
			$userStatement->execute([
				'username' => $username,
				'email' => $email,
				'password_hash' => password_hash($password, PASSWORD_DEFAULT),
				'full_name' => $fullName,
				'phone' => $phone !== '' ? $phone : null,
				'role' => 'CUSTOMER',
				'status' => 'ACTIVE',
			]);

			$customerStatement = $pdo->prepare(
				'INSERT INTO customers (user_id, delivery_address, city, postal_code)
				 VALUES (:user_id, :delivery_address, :city, :postal_code)'
			);
			$customerStatement->execute([
				'user_id' => $pdo->lastInsertId(),
				'delivery_address' => $deliveryAddress,
				'city' => $city !== '' ? $city : null,
				'postal_code' => $postalCode !== '' ? $postalCode : null,
			]);

			$pdo->commit();
			header('Location: customer_register.php?registered=1');
			exit;
		} catch (PDOException $exception) {
			if ($pdo->inTransaction()) {
				$pdo->rollBack();
			}

			$errorMessage = $exception->getCode() === '23000'
				? 'That username or email is already registered.'
				: 'Registration could not be completed. Please try again.';
		}
	}
}

$registered = isset($_GET['registered']) && $_GET['registered'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#e9f5f1">
	<title>Create customer account | AquaFlow</title>
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
			padding: 28px 20px;
			color: var(--ink);
			background-color: #e9f5f1;
			background-image: radial-gradient(#b9d9cf 0.8px, transparent 0.8px);
			background-size: 18px 18px;
			font-family: "Segoe UI", Tahoma, sans-serif;
		}

		.shell {
			width: min(100%, 1000px);
			min-height: 680px;
			display: grid;
			grid-template-columns: 0.78fr 1.22fr;
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
			padding: 36px;
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

		.wordmark { display: flex; align-items: center; gap: 11px; font-size: 17px; font-weight: 700; letter-spacing: 0.04em; }
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
		.eyebrow { margin: 0 0 15px; color: #b8e4d5; font-size: 11px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; }
		h1 { max-width: 320px; margin: 0; font-family: Georgia, "Times New Roman", serif; font-size: 48px; font-weight: 400; line-height: 1.06; }
		.brand-copy p:last-child { max-width: 290px; margin: 20px 0 0; color: #d5eee5; font-size: 14px; line-height: 1.7; }
		.brand-foot { position: relative; z-index: 1; margin: 0; color: #c0e4d7; font-size: 12px; }

		.form-panel { display: flex; flex-direction: column; justify-content: center; padding: 44px clamp(28px, 5vw, 58px); }
		.form-panel h2 { margin: 0; font-family: Georgia, "Times New Roman", serif; font-size: 32px; font-weight: 400; }
		.intro { margin: 8px 0 23px; color: var(--muted); font-size: 14px; }
		.fields { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 16px; }
		.field { min-width: 0; }
		.field.wide { grid-column: 1 / -1; }
		label { display: block; margin-bottom: 6px; color: #365d57; font-size: 12px; font-weight: 700; }
		input, textarea {
			width: 100%;
			min-height: 43px;
			padding: 10px 12px;
			border: 1px solid var(--line);
			border-radius: 4px;
			outline: none;
			background: #fff;
			color: var(--ink);
			font: inherit;
			font-size: 13px;
			transition: border-color 150ms ease, box-shadow 150ms ease;
		}
		textarea { min-height: 68px; resize: vertical; }
		input:focus, textarea:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(8, 126, 118, 0.12); }
		.submit {
			width: 100%;
			min-height: 47px;
			margin-top: 19px;
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
		.message { margin: 0 0 16px; padding: 11px 13px; border-left: 3px solid var(--coral); background: #fff3ee; color: #8a4434; font-size: 13px; line-height: 1.5; }
		.message.success { border-color: var(--teal); background: #edf8f2; color: #276252; }
		.signin { margin: 19px 0 0; color: var(--muted); text-align: center; font-size: 13px; }
		.signin a { color: var(--teal-dark); font-weight: 700; text-decoration-thickness: 1px; text-underline-offset: 3px; }

		@keyframes arrive { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
		@media (max-width: 720px) {
			body { padding: 16px; }
			.shell { grid-template-columns: 1fr; min-height: 0; }
			.brand-panel { min-height: 210px; padding: 24px; }
			.brand-copy { padding: 28px 0 16px; }
			h1 { font-size: 38px; }
			.brand-copy p:last-child { margin-top: 10px; }
			.brand-foot { display: none; }
			.form-panel { padding: 32px 24px 36px; }
		}
		@media (max-width: 460px) { .fields { grid-template-columns: 1fr; } .field.wide { grid-column: auto; } }
		@media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; } }
	</style>
</head>
<body>
	<main class="shell">
		<section class="brand-panel" aria-label="AquaFlow">
			<div class="wordmark"><span class="mark" aria-hidden="true">A</span> AQUAFLOW</div>
			<div class="brand-copy">
				<p class="eyebrow">Customer account</p>
				<h1>Water, made simpler.</h1>
				<p>Create your account to make ordering and delivery details easy to manage.</p>
			</div>
			<p class="brand-foot">Pure water. Thoughtful service.</p>
		</section>

		<section class="form-panel" aria-labelledby="form-title">
			<h2 id="form-title"><?= $registered ? 'Account created' : 'Create your account' ?></h2>
			<p class="intro"><?= $registered ? 'Your AquaFlow customer profile is ready.' : 'Add your details to get started with AquaFlow.' ?></p>

			<?php if ($registered): ?>
				<p class="message success" role="status">Your customer account was created successfully. You can now sign in.</p>
				<p class="signin"><a href="login.php">Go to sign in</a></p>
			<?php else: ?>
				<?php if ($errorMessage !== ''): ?>
					<p class="message" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
				<?php endif; ?>

				<form method="post" action="customer_register.php">
					<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['register_csrf'], ENT_QUOTES, 'UTF-8') ?>">
					<div class="fields">
						<div class="field">
							<label for="full_name">Full name *</label>
							<input id="full_name" name="full_name" type="text" maxlength="100" autocomplete="name" value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>" required>
						</div>
						<div class="field">
							<label for="username">Username *</label>
							<input id="username" name="username" type="text" minlength="3" maxlength="50" pattern="[A-Za-z0-9_.-]{3,50}" autocomplete="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required>
						</div>
						<div class="field">
							<label for="email">Email *</label>
							<input id="email" name="email" type="email" maxlength="100" autocomplete="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
						</div>
						<div class="field">
							<label for="phone">Phone</label>
							<input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>">
						</div>
						<div class="field wide">
							<label for="delivery_address">Delivery address *</label>
							<textarea id="delivery_address" name="delivery_address" autocomplete="street-address" required><?= htmlspecialchars($deliveryAddress, ENT_QUOTES, 'UTF-8') ?></textarea>
						</div>
						<div class="field">
							<label for="city">City</label>
							<input id="city" name="city" type="text" maxlength="50" autocomplete="address-level2" value="<?= htmlspecialchars($city, ENT_QUOTES, 'UTF-8') ?>">
						</div>
						<div class="field">
							<label for="postal_code">Postal code</label>
							<input id="postal_code" name="postal_code" type="text" maxlength="20" autocomplete="postal-code" value="<?= htmlspecialchars($postalCode, ENT_QUOTES, 'UTF-8') ?>">
						</div>
						<div class="field">
							<label for="password">Password *</label>
							<input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
						</div>
						<div class="field">
							<label for="confirm_password">Confirm password *</label>
							<input id="confirm_password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required>
						</div>
					</div>
					<button class="submit" type="submit">Create customer account</button>
				</form>
				<p class="signin">Already registered? <a href="login.php">Sign in</a></p>
			<?php endif; ?>
		</section>
	</main>
</body>
</html>