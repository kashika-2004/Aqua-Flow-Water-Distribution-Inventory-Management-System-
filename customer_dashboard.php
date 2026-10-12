<?php
session_start();

require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
	header('Location: login.php');
	exit;
}

$userId = (int) $_SESSION['user_id'];
$profileQuery = $pdo->prepare(
	"SELECT u.id, u.full_name, u.email, u.phone, c.id AS customer_id,
	 c.delivery_address, c.city, c.postal_code
	 FROM users u JOIN customers c ON c.user_id = u.id
	 WHERE u.id = :user_id AND u.role = 'CUSTOMER' AND u.status = 'ACTIVE'"
);
$profileQuery->execute(['user_id' => $userId]);
$profile = $profileQuery->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
	$_SESSION = [];
	session_destroy();
	header('Location: login.php');
	exit;
}

if (empty($_SESSION['customer_csrf'])) {
	$_SESSION['customer_csrf'] = bin2hex(random_bytes(32));
}

function customerEscape(mixed $value): string
{
	return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function customerLog(PDO $pdo, int $userId, string $event, string $description): void
{
	$statement = $pdo->prepare(
		'INSERT INTO activity_logs (user_id, action_event, description, ip_address)
		 VALUES (:user_id, :event, :description, :ip_address)'
	);
	$statement->execute([
		'user_id' => $userId,
		'event' => $event,
		'description' => $description,
		'ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
	]);
}

$errorMessage = '';
$noticeMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$submittedToken = $_POST['csrf_token'] ?? '';
	try {
		if (!is_string($submittedToken) || !hash_equals($_SESSION['customer_csrf'], $submittedToken)) {
			throw new RuntimeException('Your session expired. Refresh the page and try again.');
		}

		$action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';
		if ($action === 'update_profile') {
			$fullName = isset($_POST['full_name']) && is_string($_POST['full_name']) ? trim($_POST['full_name']) : '';
			$phone = isset($_POST['phone']) && is_string($_POST['phone']) ? trim($_POST['phone']) : '';
			$address = isset($_POST['delivery_address']) && is_string($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
			$city = isset($_POST['city']) && is_string($_POST['city']) ? trim($_POST['city']) : '';
			$postalCode = isset($_POST['postal_code']) && is_string($_POST['postal_code']) ? trim($_POST['postal_code']) : '';

			if ($fullName === '' || strlen($fullName) > 100 || $address === '' || strlen($phone) > 20 || strlen($city) > 50 || strlen($postalCode) > 20) {
				throw new RuntimeException('Enter a name and delivery address, and keep each field within its allowed length.');
			}
			$pdo->beginTransaction();
			$pdo->prepare('UPDATE users SET full_name = :full_name, phone = :phone WHERE id = :id')
				->execute(['full_name' => $fullName, 'phone' => $phone !== '' ? $phone : null, 'id' => $userId]);
			$pdo->prepare('UPDATE customers SET delivery_address = :address, city = :city, postal_code = :postal_code WHERE id = :id')
				->execute([
					'address' => $address,
					'city' => $city !== '' ? $city : null,
					'postal_code' => $postalCode !== '' ? $postalCode : null,
					'id' => $profile['customer_id'],
				]);
			customerLog($pdo, $userId, 'CUSTOMER_PROFILE_UPDATED', 'Updated customer delivery profile.');
			$pdo->commit();
			header('Location: customer_dashboard.php?profile=saved');
			exit;
		}

		if ($action === 'place_order') {
			$quantitiesInput = $_POST['quantities'] ?? null;
			$paymentMethod = isset($_POST['payment_method']) && is_string($_POST['payment_method']) ? $_POST['payment_method'] : '';
			$paymentReference = isset($_POST['transaction_reference']) && is_string($_POST['transaction_reference']) ? trim($_POST['transaction_reference']) : '';
			$notes = isset($_POST['notes']) && is_string($_POST['notes']) ? trim($_POST['notes']) : '';
			$allowedMethods = ['COD', 'BANK_TRANSFER', 'ONLINE_CARD'];
			if (!is_array($quantitiesInput) || !in_array($paymentMethod, $allowedMethods, true) || strlen($paymentReference) > 100 || strlen($notes) > 2000) {
				throw new RuntimeException('Enter valid order quantities and payment details.');
			}
			if (trim((string) $profile['delivery_address']) === '' || trim((string) $profile['phone']) === '') {
				throw new RuntimeException('Add a delivery address and phone number to your profile before ordering.');
			}

			$quantities = [];
			foreach ($quantitiesInput as $productId => $rawQuantity) {
				if (!ctype_digit((string) $productId) || (!is_string($rawQuantity) && !is_int($rawQuantity))) {
					throw new RuntimeException('One or more product quantities are invalid.');
				}
				$rawQuantity = (string) $rawQuantity;
				if ($rawQuantity === '') {
					continue;
				}
				if (!ctype_digit($rawQuantity) || strlen($rawQuantity) > 5 || (int) $rawQuantity > 10000) {
					throw new RuntimeException('Product quantities must be whole numbers from 0 to 10,000.');
				}
				if ((int) $rawQuantity > 0) {
					$quantities[(int) $productId] = (int) $rawQuantity;
				}
			}
			if (!$quantities) {
				throw new RuntimeException('Choose at least one product quantity to place an order.');
			}

			$placeholders = implode(',', array_fill(0, count($quantities), '?'));
			$productQuery = $pdo->prepare(
				"SELECT p.id, p.product_name, p.selling_price, p.units_per_pack, p.bottle_size_id
				 FROM products p WHERE p.is_active = 1 AND p.id IN ({$placeholders})"
			);
			$productQuery->execute(array_keys($quantities));
			$productRows = $productQuery->fetchAll(PDO::FETCH_ASSOC);
			if (count($productRows) !== count($quantities)) {
				throw new RuntimeException('One or more selected products are no longer available.');
			}

			$productById = [];
			$requiredBySize = [];
			$subtotalCents = 0;
			foreach ($productRows as $product) {
				$productId = (int) $product['id'];
				$unitsPerPack = (int) $product['units_per_pack'];
				if ($unitsPerPack < 1) {
					throw new RuntimeException('A selected product has invalid pack information.');
				}
				$quantity = $quantities[$productId];
				$sizeId = (int) $product['bottle_size_id'];
				$requiredBySize[$sizeId] = ($requiredBySize[$sizeId] ?? 0) + $quantity * $unitsPerPack;
				$unitPriceCents = (int) round((float) $product['selling_price'] * 100);
				$lineTotalCents = $unitPriceCents * $quantity;
				$subtotalCents += $lineTotalCents;
				$productById[$productId] = [
					'product' => $product,
					'quantity' => $quantity,
					'unit_price' => number_format($unitPriceCents / 100, 2, '.', ''),
					'line_total' => number_format($lineTotalCents / 100, 2, '.', ''),
				];
			}
			if ($subtotalCents < 1 || $subtotalCents > 9999999999) {
				throw new RuntimeException('The order total is outside the allowed range.');
			}

			$pdo->beginTransaction();
			$sizeIds = array_keys($requiredBySize);
			sort($sizeIds, SORT_NUMERIC);
			$stockPlaceholders = implode(',', array_fill(0, count($sizeIds), '?'));
			$stockQuery = $pdo->prepare(
				"SELECT bottle_size_id, quantity_available FROM filled_bottle_inventory
				 WHERE bottle_size_id IN ({$stockPlaceholders}) ORDER BY bottle_size_id FOR UPDATE"
			);
			$stockQuery->execute($sizeIds);
			$stockBySize = [];
			foreach ($stockQuery->fetchAll(PDO::FETCH_ASSOC) as $stock) {
				$stockBySize[(int) $stock['bottle_size_id']] = (int) $stock['quantity_available'];
			}
			foreach ($requiredBySize as $sizeId => $requiredUnits) {
				if (($stockBySize[$sizeId] ?? 0) < $requiredUnits) {
					throw new RuntimeException('There is not enough filled stock available for one or more selected sizes.');
				}
			}

			$total = number_format($subtotalCents / 100, 2, '.', '');
			$orderNumber = 'AF-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
			$orderInsert = $pdo->prepare(
				"INSERT INTO orders (order_number, customer_id, subtotal, delivery_fee, total_amount,
				 order_status, payment_status, delivery_address, contact_phone, notes)
				 VALUES (:order_number, :customer_id, :subtotal, 0.00, :total_amount,
				 'PENDING', 'PENDING', :address, :phone, :notes)"
			);
			$orderInsert->execute([
				'order_number' => $orderNumber,
				'customer_id' => $profile['customer_id'],
				'subtotal' => $total,
				'total_amount' => $total,
				'address' => $profile['delivery_address'],
				'phone' => $profile['phone'],
				'notes' => $notes !== '' ? $notes : null,
			]);
			$orderId = (int) $pdo->lastInsertId();
			$itemInsert = $pdo->prepare(
				'INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total)
				 VALUES (:order_id, :product_id, :quantity, :unit_price, :line_total)'
			);
			foreach ($productById as $productId => $line) {
				$itemInsert->execute([
					'order_id' => $orderId,
					'product_id' => $productId,
					'quantity' => $line['quantity'],
					'unit_price' => $line['unit_price'],
					'line_total' => $line['line_total'],
				]);
			}
			$paymentInsert = $pdo->prepare(
				'INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, transaction_reference, recorded_by_user_id)
				 VALUES (:order_id, :method, :amount, \'PENDING\', :reference, :user_id)'
			);
			$paymentInsert->execute([
				'order_id' => $orderId,
				'method' => $paymentMethod,
				'amount' => $total,
				'reference' => $paymentReference !== '' ? $paymentReference : null,
				'user_id' => $userId,
			]);
			customerLog($pdo, $userId, 'ORDER_PLACED', 'Placed order ' . $orderNumber . '.');
			$pdo->commit();
			header('Location: customer_dashboard.php?ordered=1');
			exit;
		}

		if ($action === 'submit_payment') {
			$orderIdText = isset($_POST['order_id']) && is_string($_POST['order_id']) ? $_POST['order_id'] : '';
			$orderId = ctype_digit($orderIdText) ? (int) $orderIdText : 0;
			$paymentMethod = isset($_POST['payment_method']) && is_string($_POST['payment_method']) ? $_POST['payment_method'] : '';
			$paymentReference = isset($_POST['transaction_reference']) && is_string($_POST['transaction_reference']) ? trim($_POST['transaction_reference']) : '';
			if ($orderId < 1 || !in_array($paymentMethod, ['COD', 'BANK_TRANSFER', 'ONLINE_CARD'], true) || strlen($paymentReference) > 100) {
				throw new RuntimeException('Enter valid payment details.');
			}

			$pdo->beginTransaction();
			$orderQuery = $pdo->prepare(
				'SELECT id, total_amount, order_status, payment_status FROM orders WHERE id = :id AND customer_id = :customer_id FOR UPDATE'
			);
			$orderQuery->execute(['id' => $orderId, 'customer_id' => $profile['customer_id']]);
			$order = $orderQuery->fetch(PDO::FETCH_ASSOC);
			if (!$order || in_array($order['order_status'], ['CANCELLED', 'REJECTED'], true)) {
				throw new RuntimeException('This order cannot accept a payment submission.');
			}
			if ($order['payment_status'] === 'PAID') {
				throw new RuntimeException('This order is already marked paid.');
			}
			$pendingPayment = $pdo->prepare("SELECT id FROM payments WHERE order_id = :order_id AND payment_status = 'PENDING' ORDER BY id DESC LIMIT 1 FOR UPDATE");
			$pendingPayment->execute(['order_id' => $orderId]);
			$pendingPaymentId = $pendingPayment->fetchColumn();
			if ($pendingPaymentId !== false) {
				$pdo->prepare('UPDATE payments SET payment_method = :method, amount_paid = :amount, transaction_reference = :reference, recorded_by_user_id = :user_id WHERE id = :id')
					->execute([
						'method' => $paymentMethod,
						'amount' => $order['total_amount'],
						'reference' => $paymentReference !== '' ? $paymentReference : null,
						'user_id' => $userId,
						'id' => $pendingPaymentId,
					]);
			} else {
				$pdo->prepare(
					"INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, transaction_reference, recorded_by_user_id)
					 VALUES (:order_id, :method, :amount, 'PENDING', :reference, :user_id)"
				)->execute([
					'order_id' => $orderId,
					'method' => $paymentMethod,
					'amount' => $order['total_amount'],
					'reference' => $paymentReference !== '' ? $paymentReference : null,
					'user_id' => $userId,
				]);
			}
			customerLog($pdo, $userId, 'PAYMENT_SUBMITTED', 'Submitted payment details for order ID ' . $orderId . '.');
			$pdo->commit();
			header('Location: customer_dashboard.php?payment=submitted');
			exit;
		}

		throw new RuntimeException('Unknown customer action.');
	} catch (RuntimeException $exception) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		$errorMessage = $exception->getMessage();
	} catch (PDOException $exception) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		$errorMessage = 'The request could not be saved. Please check your details and try again.';
	}
}

$products = $pdo->query(
	'SELECT p.id, p.product_name, p.selling_price, p.sales_unit, p.units_per_pack, p.image_url,
	 bs.size_name, COALESCE(f.quantity_available, 0) AS filled_quantity
	 FROM products p JOIN bottle_sizes bs ON bs.id = p.bottle_size_id
	 LEFT JOIN filled_bottle_inventory f ON f.bottle_size_id = p.bottle_size_id
	 WHERE p.is_active = 1 AND bs.is_active = 1 ORDER BY bs.volume_liters, p.product_name'
)->fetchAll(PDO::FETCH_ASSOC);
$singleProducts = array_values(array_filter($products, static fn(array $product): bool => $product['sales_unit'] === 'SINGLE'));
$packProducts = array_values(array_filter($products, static fn(array $product): bool => $product['sales_unit'] === 'PACK'));
$ordersQuery = $pdo->prepare(
	'SELECT o.id, o.order_number, o.subtotal, o.delivery_fee, o.total_amount, o.order_status,
	 o.payment_status, o.delivery_address, o.contact_phone, o.notes, o.created_at,
	 GROUP_CONCAT(CONCAT(p.product_name, " (", CASE WHEN p.sales_unit = "PACK" THEN CONCAT("Pack of ", p.units_per_pack) ELSE "Single" END, ") × ", oi.quantity) ORDER BY oi.id SEPARATOR ", ") AS items
	 FROM orders o LEFT JOIN order_items oi ON oi.order_id = o.id
	 LEFT JOIN products p ON p.id = oi.product_id
	 WHERE o.customer_id = :customer_id GROUP BY o.id ORDER BY o.created_at DESC LIMIT 100'
);
$ordersQuery->execute(['customer_id' => $profile['customer_id']]);
$orders = $ordersQuery->fetchAll(PDO::FETCH_ASSOC);
$paymentsQuery = $pdo->prepare(
	'SELECT p.id, p.payment_method, p.amount_paid, p.payment_status, p.transaction_reference,
	 p.paid_at, p.created_at, o.order_number
	 FROM payments p JOIN orders o ON o.id = p.order_id
	 WHERE o.customer_id = :customer_id ORDER BY p.created_at DESC LIMIT 100'
);
$paymentsQuery->execute(['customer_id' => $profile['customer_id']]);
$payments = $paymentsQuery->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['profile']) && $_GET['profile'] === 'saved') {
	$noticeMessage = 'Your delivery profile has been updated.';
} elseif (isset($_GET['ordered']) && $_GET['ordered'] === '1') {
	$noticeMessage = 'Your order was placed. Its current status is pending.';
} elseif (isset($_GET['payment']) && $_GET['payment'] === 'submitted') {
	$noticeMessage = 'Your payment details were submitted and are pending verification.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#e9f5f1">
	<title>AquaFlow | Customer</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
	<div class="app-shell">
		<aside class="sidebar">
			<div class="brand"><span class="mark" aria-hidden="true">A</span> AQUAFLOW</div>
			<div class="nav-label">My account</div>
			<nav role="tablist" aria-label="Customer sections" aria-orientation="vertical">
				<a id="catalog-tab" href="#catalog" role="tab" aria-controls="catalog" aria-selected="true">Shop products</a>
				<a id="orders-tab" href="#orders" role="tab" aria-controls="orders" aria-selected="false" tabindex="-1">My orders</a>
				<a id="payments-tab" href="#payments" role="tab" aria-controls="payments" aria-selected="false" tabindex="-1">Payments</a>
				<a id="profile-tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Delivery profile</a>
			</nav>
			<div class="sidebar-account">
				<span><?= customerEscape($profile['full_name']) ?><br><?= customerEscape($profile['email']) ?></span>
				<form method="post" action="login.php"><input type="hidden" name="csrf_token" value="<?= customerEscape($_SESSION['login_csrf'] ?? '') ?>"><button type="submit" name="logout" value="1">Sign out</button></form>
			</div>
		</aside>
		<div class="content">
			<header></header>
			<main>
				<div class="welcome"><div><h1>Good to see you, <?= customerEscape($profile['full_name']) ?>.</h1><p>Your AquaFlow orders and delivery details, together in one place.</p></div></div>
		<?php if ($noticeMessage !== ''): ?><p class="notice" role="status"><?= customerEscape($noticeMessage) ?></p><?php endif; ?>
		<?php if ($errorMessage !== ''): ?><p class="notice error" role="alert"><?= customerEscape($errorMessage) ?></p><?php endif; ?>

		<section class="section" id="catalog" role="tabpanel" aria-labelledby="catalog-tab" tabindex="0">
			<div class="section-title"><div><h2 id="catalog-title">Water products</h2><p>Choose a single unit or a pack. Pack prices cover the full pack.</p></div></div>
			<form method="post" action="customer_dashboard.php#catalog">
				<input type="hidden" name="csrf_token" value="<?= customerEscape($_SESSION['customer_csrf']) ?>"><input type="hidden" name="action" value="place_order">
				<section class="product-table-group" aria-labelledby="single-products-title">
					<h3 id="single-products-title">Single Units</h3>
					<div class="table-wrap"><table><thead><tr><th>Product</th><th>Price per unit</th><th>Available units</th><th>Quantity</th></tr></thead><tbody>
					<?php foreach ($singleProducts as $product): ?>
						<tr><td><strong><?= customerEscape($product['product_name']) ?></strong><br><span class="size"><?= customerEscape($product['size_name']) ?></span></td>
						<td>$<?= number_format((float) $product['selling_price'], 2) ?></td><td><?= number_format((int) $product['filled_quantity']) ?></td>
						<td><input aria-label="Quantity of <?= customerEscape($product['size_name']) ?> single units" id="qty-<?= (int) $product['id'] ?>" name="quantities[<?= (int) $product['id'] ?>]" type="number" min="0" max="10000" value="0" inputmode="numeric"></td></tr>
					<?php endforeach; ?>
					<?php if (!$singleProducts): ?><tr><td colspan="4" class="empty">No single units are available.</td></tr><?php endif; ?>
					</tbody></table></div>
				</section>
				<section class="product-table-group" aria-labelledby="pack-products-title">
					<h3 id="pack-products-title">Packs</h3>
					<div class="table-wrap"><table><thead><tr><th>Product</th><th>Units per pack</th><th>Pack price</th><th>Available packs</th><th>Quantity</th></tr></thead><tbody>
					<?php foreach ($packProducts as $product): $unitsPerPack = max(1, (int) $product['units_per_pack']); ?>
						<tr><td><strong><?= customerEscape($product['product_name']) ?></strong><br><span class="size"><?= customerEscape($product['size_name']) ?></span></td>
						<td><?= number_format($unitsPerPack) ?></td><td>$<?= number_format((float) $product['selling_price'], 2) ?></td>
						<td><?= number_format(intdiv((int) $product['filled_quantity'], $unitsPerPack)) ?></td>
						<td><input aria-label="Quantity of <?= customerEscape($product['size_name']) ?> packs" id="qty-<?= (int) $product['id'] ?>" name="quantities[<?= (int) $product['id'] ?>]" type="number" min="0" max="10000" value="0" inputmode="numeric"></td></tr>
					<?php endforeach; ?>
					<?php if (!$packProducts): ?><tr><td colspan="5" class="empty">No packs are available.</td></tr><?php endif; ?>
					</tbody></table></div>
				</section>
				<div class="panel" style="margin-top:14px">
					<div class="inline-checkout">
						<div class="field"><label for="checkout-method">Payment method</label><select id="checkout-method" name="payment_method"><option value="COD">Cash on delivery</option><option value="BANK_TRANSFER">Bank transfer</option><option value="ONLINE_CARD">Online card</option></select></div>
						<div class="field"><label for="checkout-reference">Transfer / payment reference</label><input id="checkout-reference" name="transaction_reference" maxlength="100" placeholder="Optional"></div>
						<div class="field"><label for="order-notes">Order note</label><input id="order-notes" name="notes" maxlength="2000" placeholder="Optional delivery details"></div>
						<button class="primary" type="submit">Place order</button>
					</div>
					<p class="hint">Payments begin as pending until verified. Online card processing is not connected in this app; selecting it will not charge a card.</p>
				</div>
			</form>
		</section>

		<section class="section" id="orders" role="tabpanel" aria-labelledby="orders-tab" tabindex="0">
			<div class="section-title"><div><h2 id="orders-title">My orders</h2><p>Order progress is updated by AquaFlow staff.</p></div></div>
			<div class="table-wrap"><table><thead><tr><th>Order</th><th>Items</th><th>Delivery snapshot</th><th>Total</th><th>Order status</th><th>Payment</th></tr></thead><tbody>
			<?php foreach ($orders as $order): ?>
				<tr><td><strong><?= customerEscape($order['order_number']) ?></strong><br><span class="muted"><?= customerEscape($order['created_at']) ?></span></td>
				<td><?= customerEscape($order['items']) ?></td><td><?= customerEscape($order['delivery_address']) ?><br><span class="muted"><?= customerEscape($order['contact_phone']) ?></span></td>
				<td>$<?= number_format((float) $order['total_amount'], 2) ?></td><td><span class="status <?= strtolower(customerEscape($order['order_status'])) ?>"><?= customerEscape($order['order_status']) ?></span></td>
				<td><span class="status <?= strtolower(customerEscape($order['payment_status'])) ?>"><?= customerEscape($order['payment_status']) ?></span>
					<?php if (!in_array($order['order_status'], ['CANCELLED', 'REJECTED'], true) && $order['payment_status'] !== 'PAID'): ?>
						<form class="pay-form" method="post" action="customer_dashboard.php#orders" style="margin-top:8px">
							<input type="hidden" name="csrf_token" value="<?= customerEscape($_SESSION['customer_csrf']) ?>"><input type="hidden" name="action" value="submit_payment"><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
							<div><label>Method</label><select name="payment_method"><option value="COD">COD</option><option value="BANK_TRANSFER">Bank transfer</option><option value="ONLINE_CARD">Online card</option></select></div>
							<div><label>Reference</label><input name="transaction_reference" maxlength="100" placeholder="Optional"></div><button type="submit">Submit</button>
						</form>
					<?php endif; ?>
				</td></tr>
			<?php endforeach; ?>
			<?php if (!$orders): ?><tr><td colspan="6" class="empty">You have not placed an order yet.</td></tr><?php endif; ?>
			</tbody></table></div>
		</section>

		<section class="section" id="payments" role="tabpanel" aria-labelledby="payments-tab" tabindex="0">
			<div class="section-title"><div><h2 id="payments-title">Payment history</h2><p>Payment submissions remain pending until staff verify the transfer or receipt.</p></div></div>
			<div class="table-wrap"><table><thead><tr><th>Date</th><th>Order</th><th>Method</th><th>Amount</th><th>Reference</th><th>Status</th></tr></thead><tbody>
			<?php foreach ($payments as $payment): ?><tr><td><?= customerEscape($payment['created_at']) ?></td><td><strong><?= customerEscape($payment['order_number']) ?></strong></td><td><?= customerEscape(str_replace('_', ' ', $payment['payment_method'])) ?></td><td>$<?= number_format((float) $payment['amount_paid'], 2) ?></td><td><?= customerEscape($payment['transaction_reference'] ?: '—') ?></td><td><span class="status <?= strtolower(customerEscape($payment['payment_status'])) ?>"><?= customerEscape($payment['payment_status']) ?></span></td></tr><?php endforeach; ?>
			<?php if (!$payments): ?><tr><td colspan="6" class="empty">No payments to show yet.</td></tr><?php endif; ?>
			</tbody></table></div>
		</section>

		<section class="section" id="profile" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
			<div class="section-title"><div><h2 id="profile-title">Delivery profile</h2><p>These details are copied onto each new order.</p></div></div>
			<div class="panel"><form method="post" action="customer_dashboard.php#profile">
				<input type="hidden" name="csrf_token" value="<?= customerEscape($_SESSION['customer_csrf']) ?>"><input type="hidden" name="action" value="update_profile">
				<div class="fields">
					<div class="field"><label for="full-name">Full name</label><input id="full-name" name="full_name" maxlength="100" value="<?= customerEscape($profile['full_name']) ?>" autocomplete="name" required></div>
					<div class="field"><label for="phone">Phone number</label><input id="phone" name="phone" type="tel" maxlength="20" value="<?= customerEscape($profile['phone']) ?>" autocomplete="tel"></div>
					<div class="field wide"><label for="delivery-address">Delivery address</label><textarea id="delivery-address" name="delivery_address" autocomplete="street-address" required><?= customerEscape($profile['delivery_address']) ?></textarea></div>
					<div class="field"><label for="city">City</label><input id="city" name="city" maxlength="50" value="<?= customerEscape($profile['city']) ?>" autocomplete="address-level2"></div>
					<div class="field"><label for="postal-code">Postal code</label><input id="postal-code" name="postal_code" maxlength="20" value="<?= customerEscape($profile['postal_code']) ?>" autocomplete="postal-code"></div>
				</div><div class="form-actions"><button class="primary" type="submit">Save delivery profile</button></div>
			</form></div>
		</section>
			</main>
		</div>
	</div>
	<script>
		const customerTabs = [...document.querySelectorAll('[role="tab"]')];
		const customerPanels = customerTabs.map((tab) => document.getElementById(tab.getAttribute('aria-controls')));

		function selectCustomerTab(tab, updateHistory = false) {
			customerTabs.forEach((item, index) => {
				const selected = item === tab;
				item.setAttribute('aria-selected', String(selected));
				item.tabIndex = selected ? 0 : -1;
				customerPanels[index].hidden = !selected;
			});
			if (updateHistory) {
				history.pushState(null, '', tab.getAttribute('href'));
			}
		}

		function selectTabFromHash() {
			const matchingTab = customerTabs.find((tab) => tab.hash === window.location.hash);
			selectCustomerTab(matchingTab || customerTabs[0]);
		}

		customerTabs.forEach((tab, index) => {
			tab.addEventListener('click', (event) => {
				event.preventDefault();
				selectCustomerTab(tab, true);
			});
			tab.addEventListener('keydown', (event) => {
				const direction = tab.closest('[role="tablist"]').getAttribute('aria-orientation') === 'vertical' ? 'ArrowDown' : 'ArrowRight';
				const reverseDirection = direction === 'ArrowDown' ? 'ArrowUp' : 'ArrowLeft';
				let nextIndex = index;
				if (event.key === direction) {
					nextIndex = (index + 1) % customerTabs.length;
				} else if (event.key === reverseDirection) {
					nextIndex = (index - 1 + customerTabs.length) % customerTabs.length;
				} else if (event.key === 'Home') {
					nextIndex = 0;
				} else if (event.key === 'End') {
					nextIndex = customerTabs.length - 1;
				} else {
					return;
				}
				event.preventDefault();
				customerTabs[nextIndex].focus();
				selectCustomerTab(customerTabs[nextIndex], true);
			});
		});
		window.addEventListener('popstate', selectTabFromHash);
		selectTabFromHash();
	</script>
</body>
</html>