<?php
session_start();

require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
	header('Location: login.php');
	exit;
}

$adminId = (int) $_SESSION['user_id'];
$adminCheck = $pdo->prepare("SELECT full_name FROM users WHERE id = :id AND role = 'ADMIN' AND status = 'ACTIVE'");
$adminCheck->execute(['id' => $adminId]);
$adminName = $adminCheck->fetchColumn();

if ($adminName === false) {
	$_SESSION = [];
	session_destroy();
	header('Location: login.php');
	exit;
}

if (empty($_SESSION['admin_csrf'])) {
	$_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

function e(mixed $value): string
{
	return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function postText(string $key): string
{
	$value = $_POST[$key] ?? '';
	return is_string($value) ? trim($value) : '';
}

function postInt(string $key, int $minimum = 0): int
{
	$value = postText($key);
	if ($value === '' || !ctype_digit($value)) {
		throw new RuntimeException('Enter a valid whole number for all quantity fields.');
	}
	$number = filter_var($value, FILTER_VALIDATE_INT);
	if ($number === false || $number < $minimum) {
		throw new RuntimeException('One or more quantity values are outside the allowed range.');
	}
	return $number;
}

function adminLog(PDO $pdo, int $adminId, string $event, string $description): void
{
	$statement = $pdo->prepare(
		'INSERT INTO activity_logs (user_id, action_event, description, ip_address)
		 VALUES (:user_id, :action_event, :description, :ip_address)'
	);
	$statement->execute([
		'user_id' => $adminId,
		'action_event' => $event,
		'description' => $description,
		'ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
	]);
}

function redirectSaved(string $tab): never
{
	header('Location: admindashboard.php?tab=' . rawurlencode($tab) . '&saved=1');
	exit;
}

function updateOrderPaymentStatus(PDO $pdo, int $orderId): void
{
	$paymentTotals = $pdo->prepare(
		"SELECT
			COALESCE(SUM(CASE WHEN payment_status = 'PAID' THEN amount_paid ELSE 0 END), 0) AS paid_total,
			COALESCE(SUM(CASE WHEN payment_status = 'REFUNDED' THEN amount_paid ELSE 0 END), 0) AS refunded_total,
			SUM(payment_status = 'FAILED') AS failed_count
		 FROM payments WHERE order_id = :order_id"
	);
	$paymentTotals->execute(['order_id' => $orderId]);
	$totals = $paymentTotals->fetch(PDO::FETCH_ASSOC);
	$orderStatement = $pdo->prepare('SELECT total_amount FROM orders WHERE id = :id');
	$orderStatement->execute(['id' => $orderId]);
	$totalAmount = (float) $orderStatement->fetchColumn();
	$netPaid = (float) $totals['paid_total'] - (float) $totals['refunded_total'];

	if ($netPaid >= $totalAmount) {
		$status = 'PAID';
	} elseif ((float) $totals['refunded_total'] > 0 && $netPaid <= 0) {
		$status = 'REFUNDED';
	} elseif ((int) $totals['failed_count'] > 0 && $netPaid <= 0) {
		$status = 'FAILED';
	} else {
		$status = 'PENDING';
	}

	$update = $pdo->prepare('UPDATE orders SET payment_status = :status WHERE id = :id');
	$update->execute(['status' => $status, 'id' => $orderId]);
}

$tabs = [
	'overview' => 'Overview',
	'users' => 'User management',
	'products' => 'Products & pricing',
	'inventory' => 'Inventory',
	'batches' => 'Production batches',
	'orders' => 'Orders',
	'payments' => 'Payments',
	'audit' => 'Audit log',
];
$tab = isset($_GET['tab']) && is_string($_GET['tab']) && isset($tabs[$_GET['tab']]) ? $_GET['tab'] : 'overview';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$submittedToken = $_POST['csrf_token'] ?? '';
	$postedTab = postText('tab');
	if (!isset($tabs[$postedTab])) {
		$postedTab = 'overview';
	}

	try {
		if (!is_string($submittedToken) || !hash_equals($_SESSION['admin_csrf'], $submittedToken)) {
			throw new RuntimeException('Your session expired. Refresh the page and try again.');
		}

		$action = postText('action');
		if ($action === 'logout') {
			adminLog($pdo, $adminId, 'USER_LOGOUT', 'Administrator signed out.');
			$_SESSION = [];
			session_destroy();
			header('Location: login.php');
			exit;
		}

		switch ($action) {
			case 'create_user':
				$username = postText('username');
				$email = postText('email');
				$fullName = postText('full_name');
				$phone = postText('phone');
				$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
				$role = postText('role');
				$deliveryAddress = postText('delivery_address');
				$allowedRoles = ['ADMIN', 'STORE_MANAGER', 'CUSTOMER'];

				if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username) || strlen($fullName) < 1 || strlen($fullName) > 100) {
					throw new RuntimeException('Enter a valid username and full name.');
				}
				if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100 || strlen($phone) > 20) {
					throw new RuntimeException('Enter a valid email address and phone number.');
				}
				if (!in_array($role, $allowedRoles, true) || strlen($password) < 8) {
					throw new RuntimeException('Choose a valid role and a password with at least 8 characters.');
				}
				if ($role === 'CUSTOMER' && $deliveryAddress === '') {
					throw new RuntimeException('A delivery address is required for customer accounts.');
				}

				$pdo->beginTransaction();
				$insertUser = $pdo->prepare(
					'INSERT INTO users (username, email, password_hash, full_name, phone, role, status)
					 VALUES (:username, :email, :password_hash, :full_name, :phone, :role, \'ACTIVE\')'
				);
				$insertUser->execute([
					'username' => $username,
					'email' => $email,
					'password_hash' => password_hash($password, PASSWORD_DEFAULT),
					'full_name' => $fullName,
					'phone' => $phone !== '' ? $phone : null,
					'role' => $role,
				]);
				if ($role === 'CUSTOMER') {
					$insertCustomer = $pdo->prepare('INSERT INTO customers (user_id, delivery_address) VALUES (:user_id, :address)');
					$insertCustomer->execute(['user_id' => $pdo->lastInsertId(), 'address' => $deliveryAddress]);
				}
				adminLog($pdo, $adminId, 'USER_CREATED', 'Created user account ' . $username . ' (' . $role . ').');
				$pdo->commit();
				redirectSaved('users');

			case 'update_user':
				$userId = postInt('user_id', 1);
				$username = postText('username');
				$email = postText('email');
				$fullName = postText('full_name');
				$phone = postText('phone');
				$role = postText('role');
				$status = postText('status');
				$deliveryAddress = postText('delivery_address');
				if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username) || strlen($fullName) < 1 || strlen($fullName) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100 || strlen($phone) > 20) {
					throw new RuntimeException('Enter valid user account details.');
				}
				if (!in_array($role, ['ADMIN', 'STORE_MANAGER', 'CUSTOMER'], true) || !in_array($status, ['ACTIVE', 'INACTIVE', 'SUSPENDED'], true)) {
					throw new RuntimeException('Select a valid role and account status.');
				}
				if ($userId === $adminId && ($role !== 'ADMIN' || $status !== 'ACTIVE')) {
					throw new RuntimeException('You cannot remove your own active administrator access.');
				}
				if ($role === 'CUSTOMER' && $deliveryAddress === '') {
					throw new RuntimeException('A delivery address is required for customer accounts.');
				}

				$pdo->beginTransaction();
				$updateUser = $pdo->prepare(
					'UPDATE users SET username = :username, email = :email, full_name = :full_name,
					 phone = :phone, role = :role, status = :status WHERE id = :id'
				);
				$updateUser->execute([
					'username' => $username,
					'email' => $email,
					'full_name' => $fullName,
					'phone' => $phone !== '' ? $phone : null,
					'role' => $role,
					'status' => $status,
					'id' => $userId,
				]);
				if ($role === 'CUSTOMER') {
					$customer = $pdo->prepare(
						'INSERT INTO customers (user_id, delivery_address) VALUES (:user_id, :address)
						 ON DUPLICATE KEY UPDATE delivery_address = VALUES(delivery_address)'
					);
					$customer->execute(['user_id' => $userId, 'address' => $deliveryAddress]);
				}
				adminLog($pdo, $adminId, 'USER_UPDATED', 'Updated user account ID ' . $userId . '.');
				$pdo->commit();
				redirectSaved('users');

			case 'adjust_inventory':
				$inventoryType = postText('inventory_type');
				$sizeId = postInt('bottle_size_id', 1);
				$direction = postText('direction');
				$quantity = postInt('quantity', 1);
				$thresholdText = postText('threshold');
				$threshold = $thresholdText === '' ? null : filter_var($thresholdText, FILTER_VALIDATE_INT);
				$remarks = postText('remarks');
				if (!in_array($inventoryType, ['EMPTY', 'FILLED'], true) || !in_array($direction, ['IN', 'OUT'], true) || ($thresholdText !== '' && ($threshold === false || $threshold < 0))) {
					throw new RuntimeException('Select valid stock movement and threshold values.');
				}
				$table = $inventoryType === 'EMPTY' ? 'empty_bottle_inventory' : 'filled_bottle_inventory';
				$pdo->beginTransaction();
				$stockQuery = $pdo->prepare("SELECT quantity_available, min_stock_threshold FROM {$table} WHERE bottle_size_id = :size_id FOR UPDATE");
				$stockQuery->execute(['size_id' => $sizeId]);
				$stock = $stockQuery->fetch(PDO::FETCH_ASSOC);
				if (!$stock) {
					throw new RuntimeException('Inventory was not found for that bottle size.');
				}
				$current = (int) $stock['quantity_available'];
				if ($direction === 'OUT' && $quantity > $current) {
					throw new RuntimeException('Stock cannot be reduced below zero.');
				}
				$newQuantity = $direction === 'IN' ? $current + $quantity : $current - $quantity;
				$newThreshold = $threshold ?? (int) $stock['min_stock_threshold'];
				$stockUpdate = $pdo->prepare("UPDATE {$table} SET quantity_available = :quantity, min_stock_threshold = :threshold WHERE bottle_size_id = :size_id");
				$stockUpdate->execute(['quantity' => $newQuantity, 'threshold' => $newThreshold, 'size_id' => $sizeId]);
				$movement = $pdo->prepare(
					'INSERT INTO inventory_transactions (inventory_type, bottle_size_id, quantity, transaction_type, processed_by_user_id, remarks)
					 VALUES (:type, :size_id, :quantity, :transaction_type, :user_id, :remarks)'
				);
				$movement->execute([
					'type' => $inventoryType,
					'size_id' => $sizeId,
					'quantity' => $quantity,
					'transaction_type' => $direction === 'IN' ? 'STOCK_IN' : 'STOCK_OUT',
					'user_id' => $adminId,
					'remarks' => $remarks !== '' ? $remarks : 'Manual stock adjustment',
				]);
				adminLog($pdo, $adminId, 'INVENTORY_ADJUSTED', 'Adjusted ' . $inventoryType . ' stock for size ID ' . $sizeId . '.');
				$pdo->commit();
				redirectSaved('inventory');

			case 'create_batch':
				$sizeId = postInt('bottle_size_id', 1);
				$quantityFilled = postInt('quantity_filled');
				$quantityDamaged = postInt('quantity_damaged');
				$fillingDate = postText('filling_date');
				$remarks = postText('remarks');
				if ($quantityFilled + $quantityDamaged < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fillingDate)) {
					throw new RuntimeException('Enter a valid filling date and at least one filled or damaged unit.');
				}
				$parsedDate = DateTime::createFromFormat('Y-m-d', $fillingDate);
				if (!$parsedDate || $parsedDate->format('Y-m-d') !== $fillingDate) {
					throw new RuntimeException('Enter a valid filling date.');
				}
				$pdo->beginTransaction();
				$emptyStock = $pdo->prepare('SELECT quantity_available FROM empty_bottle_inventory WHERE bottle_size_id = :size_id FOR UPDATE');
				$emptyStock->execute(['size_id' => $sizeId]);
				$emptyAvailable = $emptyStock->fetchColumn();
				$consumed = $quantityFilled + $quantityDamaged;
				if ($emptyAvailable === false || (int) $emptyAvailable < $consumed) {
					throw new RuntimeException('Not enough empty containers are available for this batch.');
				}
				$filledStock = $pdo->prepare('SELECT quantity_available FROM filled_bottle_inventory WHERE bottle_size_id = :size_id FOR UPDATE');
				$filledStock->execute(['size_id' => $sizeId]);
				if ($filledStock->fetchColumn() === false) {
					throw new RuntimeException('Filled inventory was not found for that bottle size.');
				}
				$batchCode = 'AF-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2)));
				$insertBatch = $pdo->prepare(
					'INSERT INTO filling_batches (batch_code, bottle_size_id, quantity_filled, quantity_damaged, filling_date, processed_by_user_id, remarks)
					 VALUES (:code, :size_id, :filled, :damaged, :filling_date, :user_id, :remarks)'
				);
				$insertBatch->execute([
					'code' => $batchCode,
					'size_id' => $sizeId,
					'filled' => $quantityFilled,
					'damaged' => $quantityDamaged,
					'filling_date' => $fillingDate,
					'user_id' => $adminId,
					'remarks' => $remarks !== '' ? $remarks : null,
				]);
				$batchId = (int) $pdo->lastInsertId();
				$pdo->prepare('UPDATE empty_bottle_inventory SET quantity_available = quantity_available - :amount WHERE bottle_size_id = :size_id')
					->execute(['amount' => $consumed, 'size_id' => $sizeId]);
				$pdo->prepare('UPDATE filled_bottle_inventory SET quantity_available = quantity_available + :amount WHERE bottle_size_id = :size_id')
					->execute(['amount' => $quantityFilled, 'size_id' => $sizeId]);
				$insertMovement = $pdo->prepare(
					'INSERT INTO inventory_transactions (inventory_type, bottle_size_id, quantity, transaction_type, reference_id, processed_by_user_id, remarks)
					 VALUES (:type, :size_id, :quantity, :transaction_type, :reference_id, :user_id, :remarks)'
				);
				$insertMovement->execute([
					'type' => 'EMPTY', 'size_id' => $sizeId, 'quantity' => $consumed,
					'transaction_type' => 'FILLING_DEDUCTION', 'reference_id' => $batchId,
					'user_id' => $adminId, 'remarks' => 'Consumed by batch ' . $batchCode,
				]);
				if ($quantityFilled > 0) {
					$insertMovement->execute([
						'type' => 'FILLED', 'size_id' => $sizeId, 'quantity' => $quantityFilled,
						'transaction_type' => 'FILLING_ADDITION', 'reference_id' => $batchId,
						'user_id' => $adminId, 'remarks' => 'Produced by batch ' . $batchCode,
					]);
				}
				adminLog($pdo, $adminId, 'BATCH_CREATED', 'Processed filling batch ' . $batchCode . '.');
				$pdo->commit();
				redirectSaved('batches');

			case 'update_product_price':
				$productId = postInt('product_id', 1);
				$priceText = postText('selling_price');
				if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $priceText) || (float) $priceText <= 0) {
					throw new RuntimeException('Enter a product price greater than zero with no more than two decimal places.');
				}
				$pdo->beginTransaction();
				$productQuery = $pdo->prepare('SELECT product_name FROM products WHERE id = :id FOR UPDATE');
				$productQuery->execute(['id' => $productId]);
				$productName = $productQuery->fetchColumn();
				if ($productName === false) {
					throw new RuntimeException('The selected product was not found.');
				}
				$pdo->prepare('UPDATE products SET selling_price = :price WHERE id = :id')
					->execute(['price' => number_format((float) $priceText, 2, '.', ''), 'id' => $productId]);
				adminLog($pdo, $adminId, 'PRODUCT_PRICE_UPDATED', 'Updated the selling price for ' . $productName . ' to ' . number_format((float) $priceText, 2, '.', '') . '.');
				$pdo->commit();
				redirectSaved('products');

			case 'update_order':
				$orderId = postInt('order_id', 1);
				$orderStatus = postText('order_status');
				if (!in_array($orderStatus, ['PENDING', 'CONFIRMED', 'PREPARING', 'DISPATCHED', 'DELIVERED', 'CANCELLED', 'REJECTED'], true)) {
					throw new RuntimeException('Select a valid order status.');
				}
				$pdo->beginTransaction();
				$orderQuery = $pdo->prepare('SELECT order_status FROM orders WHERE id = :id FOR UPDATE');
				$orderQuery->execute(['id' => $orderId]);
				$oldStatus = $orderQuery->fetchColumn();
				if ($oldStatus === false) {
					throw new RuntimeException('Order not found.');
				}
				if ($orderStatus === 'DISPATCHED' && $oldStatus !== 'DISPATCHED') {
					$priorDispatch = $pdo->prepare("SELECT COUNT(*) FROM inventory_transactions WHERE transaction_type = 'ORDER_DISPATCH' AND reference_id = :order_id");
					$priorDispatch->execute(['order_id' => $orderId]);
					if ((int) $priorDispatch->fetchColumn() === 0) {
						$itemsQuery = $pdo->prepare(
							' SELECT oi.quantity, p.units_per_pack, p.bottle_size_id
							  FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :order_id'
						);
						$itemsQuery->execute(['order_id' => $orderId]);
						$items = $itemsQuery->fetchAll(PDO::FETCH_ASSOC);
						if (!$items) {
							throw new RuntimeException('This order has no items to dispatch.');
						}
						foreach ($items as $item) {
							$units = (int) $item['quantity'] * (int) $item['units_per_pack'];
						$stockQuery = $pdo->prepare('SELECT quantity_available FROM filled_bottle_inventory WHERE bottle_size_id = :size_id FOR UPDATE');
						$stockQuery->execute(['size_id' => $item['bottle_size_id']]);
						$available = $stockQuery->fetchColumn();
						if ($available === false || (int) $available < $units) {
							throw new RuntimeException('Insufficient filled stock to dispatch this order.');
						}
						$pdo->prepare('UPDATE filled_bottle_inventory SET quantity_available = quantity_available - :units WHERE bottle_size_id = :size_id')
							->execute(['units' => $units, 'size_id' => $item['bottle_size_id']]);
						$movement = $pdo->prepare(
							"INSERT INTO inventory_transactions (inventory_type, bottle_size_id, quantity, transaction_type, reference_id, processed_by_user_id, remarks)
							 VALUES ('FILLED', :size_id, :quantity, 'ORDER_DISPATCH', :order_id, :user_id, 'Order dispatched')"
						);
						$movement->execute(['size_id' => $item['bottle_size_id'], 'quantity' => $units, 'order_id' => $orderId, 'user_id' => $adminId]);
						}
					}
				}
				$pdo->prepare('UPDATE orders SET order_status = :status WHERE id = :id')->execute(['status' => $orderStatus, 'id' => $orderId]);
				adminLog($pdo, $adminId, 'ORDER_STATUS_UPDATED', 'Updated order ID ' . $orderId . ' from ' . $oldStatus . ' to ' . $orderStatus . '.');
				$pdo->commit();
				redirectSaved('orders');

			case 'record_payment':
				$orderId = postInt('order_id', 1);
				$paymentMethod = postText('payment_method');
				$paymentStatus = postText('payment_status');
				$amount = postText('amount_paid');
				$reference = postText('transaction_reference');
				if (!in_array($paymentMethod, ['COD', 'BANK_TRANSFER', 'ONLINE_CARD'], true) || !in_array($paymentStatus, ['PENDING', 'PAID', 'FAILED', 'REFUNDED'], true)) {
					throw new RuntimeException('Select a valid payment method and status.');
				}
				if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $amount) || (float) $amount <= 0 || strlen($reference) > 100) {
					throw new RuntimeException('Enter a valid payment amount and transaction reference.');
				}
				$pdo->beginTransaction();
				$orderExists = $pdo->prepare('SELECT id FROM orders WHERE id = :id FOR UPDATE');
				$orderExists->execute(['id' => $orderId]);
				if ($orderExists->fetchColumn() === false) {
					throw new RuntimeException('Order not found.');
				}
				$insertPayment = $pdo->prepare(
					'INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, transaction_reference, recorded_by_user_id, paid_at)
					 VALUES (:order_id, :method, :amount, :status, :reference, :user_id, :paid_at)'
				);
				$insertPayment->execute([
					'order_id' => $orderId,
					'method' => $paymentMethod,
					'amount' => $amount,
					'status' => $paymentStatus,
					'reference' => $reference !== '' ? $reference : null,
					'user_id' => $adminId,
					'paid_at' => $paymentStatus === 'PAID' ? date('Y-m-d H:i:s') : null,
				]);
				updateOrderPaymentStatus($pdo, $orderId);
				adminLog($pdo, $adminId, 'PAYMENT_RECORDED', 'Recorded payment for order ID ' . $orderId . '.');
				$pdo->commit();
				redirectSaved('payments');

			case 'update_payment':
				$paymentId = postInt('payment_id', 1);
				$paymentStatus = postText('payment_status');
				if (!in_array($paymentStatus, ['PENDING', 'PAID', 'FAILED', 'REFUNDED'], true)) {
					throw new RuntimeException('Select a valid payment status.');
				}
				$pdo->beginTransaction();
				$paymentOrder = $pdo->prepare('SELECT order_id FROM payments WHERE id = :id FOR UPDATE');
				$paymentOrder->execute(['id' => $paymentId]);
				$orderId = $paymentOrder->fetchColumn();
				if ($orderId === false) {
					throw new RuntimeException('Payment record not found.');
				}
				$pdo->prepare('UPDATE payments SET payment_status = :status, paid_at = :paid_at WHERE id = :id')
					->execute(['status' => $paymentStatus, 'paid_at' => $paymentStatus === 'PAID' ? date('Y-m-d H:i:s') : null, 'id' => $paymentId]);
				updateOrderPaymentStatus($pdo, (int) $orderId);
				adminLog($pdo, $adminId, 'PAYMENT_UPDATED', 'Updated payment ID ' . $paymentId . ' to ' . $paymentStatus . '.');
				$pdo->commit();
				redirectSaved('payments');

			default:
				throw new RuntimeException('Unknown admin action.');
		}
	} catch (RuntimeException $exception) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		$errorMessage = $exception->getMessage();
		$tab = $postedTab;
	} catch (PDOException $exception) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		$errorMessage = $exception->getCode() === '23000'
			? 'That username or email is already in use, or the selected record is referenced elsewhere.'
			: 'The requested change could not be saved. Check the database connection and try again.';
		$tab = $postedTab;
	}
}

$sizes = $pdo->query('SELECT id, size_name, volume_liters FROM bottle_sizes WHERE is_active = 1 ORDER BY volume_liters')->fetchAll(PDO::FETCH_ASSOC);
$productPrices = $pdo->query(
	'SELECT p.id, p.product_name, p.selling_price, p.sales_unit, p.units_per_pack,
	 bs.size_name, bs.volume_liters, COALESCE(f.quantity_available, 0) AS filled_quantity
	 FROM products p JOIN bottle_sizes bs ON bs.id = p.bottle_size_id
	 LEFT JOIN filled_bottle_inventory f ON f.bottle_size_id = p.bottle_size_id
	 ORDER BY bs.volume_liters, p.sales_unit'
)->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query(
	'SELECT u.id, u.username, u.email, u.full_name, u.phone, u.role, u.status, c.delivery_address
	 FROM users u LEFT JOIN customers c ON c.user_id = u.id ORDER BY u.created_at DESC LIMIT 200'
)->fetchAll(PDO::FETCH_ASSOC);
$inventory = $pdo->query(
	'SELECT bs.id AS size_id, bs.size_name,
	 e.quantity_available AS empty_quantity, e.min_stock_threshold AS empty_threshold,
	 f.quantity_available AS filled_quantity, f.min_stock_threshold AS filled_threshold
	 FROM bottle_sizes bs
	 LEFT JOIN empty_bottle_inventory e ON e.bottle_size_id = bs.id
	 LEFT JOIN filled_bottle_inventory f ON f.bottle_size_id = bs.id
	 WHERE bs.is_active = 1 ORDER BY bs.volume_liters'
)->fetchAll(PDO::FETCH_ASSOC);
$batches = $pdo->query(
	'SELECT b.batch_code, b.quantity_filled, b.quantity_damaged, b.filling_date, b.remarks,
	 bs.size_name, u.full_name AS processed_by
	 FROM filling_batches b JOIN bottle_sizes bs ON bs.id = b.bottle_size_id
	 JOIN users u ON u.id = b.processed_by_user_id ORDER BY b.created_at DESC LIMIT 100'
)->fetchAll(PDO::FETCH_ASSOC);
$orders = $pdo->query(
	'SELECT o.id, o.order_number, o.subtotal, o.delivery_fee, o.total_amount, o.order_status, o.payment_status,
	 o.delivery_address, o.contact_phone, o.notes, o.created_at, u.full_name AS customer_name,
	 GROUP_CONCAT(CONCAT(p.product_name, " (", CASE WHEN p.sales_unit = "PACK" THEN CONCAT("Pack of ", p.units_per_pack) ELSE "Single" END, ") × ", oi.quantity) ORDER BY oi.id SEPARATOR ", ") AS items
	 FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = c.user_id
	 LEFT JOIN order_items oi ON oi.order_id = o.id LEFT JOIN products p ON p.id = oi.product_id
	 GROUP BY o.id ORDER BY o.created_at DESC LIMIT 100'
)->fetchAll(PDO::FETCH_ASSOC);
$payments = $pdo->query(
	'SELECT p.id, p.order_id, p.payment_method, p.amount_paid, p.payment_status, p.transaction_reference,
	 p.paid_at, p.created_at, o.order_number, u.full_name AS recorded_by
	 FROM payments p JOIN orders o ON o.id = p.order_id
	 LEFT JOIN users u ON u.id = p.recorded_by_user_id ORDER BY p.created_at DESC LIMIT 100'
)->fetchAll(PDO::FETCH_ASSOC);
$transactions = $pdo->query(
	'SELECT t.inventory_type, t.quantity, t.transaction_type, t.reference_id, t.remarks, t.created_at,
	 bs.size_name, u.full_name AS processed_by
	 FROM inventory_transactions t JOIN bottle_sizes bs ON bs.id = t.bottle_size_id
	 JOIN users u ON u.id = t.processed_by_user_id ORDER BY t.created_at DESC LIMIT 100'
)->fetchAll(PDO::FETCH_ASSOC);
$activity = $pdo->query(
	'SELECT a.action_event, a.description, a.ip_address, a.created_at, u.username
	 FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 150'
)->fetchAll(PDO::FETCH_ASSOC);
$stats = [
	'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
	'open_orders' => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('PENDING','CONFIRMED','PREPARING')")->fetchColumn(),
	'low_stock' => (int) $pdo->query(
		'SELECT COUNT(*) FROM empty_bottle_inventory WHERE quantity_available <= min_stock_threshold'
	)->fetchColumn() + (int) $pdo->query(
		'SELECT COUNT(*) FROM filled_bottle_inventory WHERE quantity_available <= min_stock_threshold'
	)->fetchColumn(),
	'revenue' => (float) $pdo->query("SELECT COALESCE(SUM(amount_paid), 0) FROM payments WHERE payment_status = 'PAID'")->fetchColumn(),
];
$orderChoices = $pdo->query('SELECT id, order_number, total_amount FROM orders ORDER BY created_at DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
$saved = isset($_GET['saved']) && $_GET['saved'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#f1f5f1">
	<title>AquaFlow Admin</title>

        <link rel="stylesheet" href="css/style.css">

</head>
<body>
	<div class="layout">
		<aside class="sidebar">
			<div class="brand"><span class="brand-mark" aria-hidden="true">A</span> AQUAFLOW ADMIN</div>
			<div class="nav-label">Operations</div>
			<nav aria-label="Admin sections">
				<?php foreach ($tabs as $key => $label): ?>
					<a href="admindashboard.php?tab=<?= e($key) ?>" <?= $tab === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
				<?php endforeach; ?>
			</nav>
			<div class="sidebar-foot">Signed in as administrator<br><?= e($_SESSION['username'] ?? '') ?></div>
		</aside>
		<main class="main">
			<header class="topbar">
				<div><h1><?= e($tabs[$tab]) ?></h1><p>AquaFlow operations console</p></div>
				<div class="top-actions"><span class="admin-name"><?= e($adminName) ?></span>
					<form method="post" action="admindashboard.php" style="margin:0">
						<input type="hidden" name="csrf_token" value="<?= e($_SESSION['admin_csrf']) ?>">
						<input type="hidden" name="tab" value="<?= e($tab) ?>">
						<input type="hidden" name="action" value="logout">
						<button type="submit">Sign out</button>
					</form>
				</div>
			</header>
			<div class="content">
				<?php if ($saved): ?><p class="notice" role="status">Changes saved successfully.</p><?php endif; ?>
				<?php if ($errorMessage !== ''): ?><p class="notice error" role="alert"><?= e($errorMessage) ?></p><?php endif; ?>

				<?php if ($tab === 'overview'): ?>
					<div class="stats">
						<div class="stat"><div class="stat-label">System users</div><div class="stat-value"><?= number_format($stats['users']) ?></div></div>
						<div class="stat"><div class="stat-label">Open orders</div><div class="stat-value"><?= number_format($stats['open_orders']) ?></div></div>
						<div class="stat"><div class="stat-label">Low-stock lines</div><div class="stat-value"><?= number_format($stats['low_stock']) ?></div></div>
						<div class="stat"><div class="stat-label">Recorded paid</div><div class="stat-value">$<?= number_format($stats['revenue'], 2) ?></div></div>
					</div>
					
					<div class="section-heading"><div><h2>Inventory watch</h2><p>Stock levels compared with configured reorder thresholds.</p></div><a class="button" href="admindashboard.php?tab=inventory">Manage stock</a></div>
					
					<!-- Table 1: Empty Bottle Inventory -->
					<div class="panel" style="margin-bottom:20px;">
						<h3 style="margin-bottom:8px;">Empty Bottle Inventory</h3>
						<div class="table-wrap"><table><thead><tr><th>Bottle size</th><th>Current Quantity</th><th>Minimum Threshold</th></tr></thead><tbody>
						<?php foreach ($inventory as $row): ?>
							<tr>
								<td><strong><?= e($row['size_name']) ?></strong></td>
								<td class="<?= (int) $row['empty_quantity'] <= (int) $row['empty_threshold'] ? 'low' : '' ?>"><?= number_format((int) $row['empty_quantity']) ?></td>
								<td><?= number_format((int) $row['empty_threshold']) ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody></table></div>
					</div>

					<!-- Table 2: Filled Bottle Inventory -->
					<div class="panel">
						<h3 style="margin-bottom:8px;">Filled Bottle Inventory</h3>
						<div class="table-wrap"><table><thead><tr><th>Bottle size</th><th>Current Quantity</th><th>Minimum Threshold</th></tr></thead><tbody>
						<?php foreach ($inventory as $row): ?>
							<tr>
								<td><strong><?= e($row['size_name']) ?></strong></td>
								<td class="<?= (int) $row['filled_quantity'] <= (int) $row['filled_threshold'] ? 'low' : '' ?>"><?= number_format((int) $row['filled_quantity']) ?></td>
								<td><?= number_format((int) $row['filled_threshold']) ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody></table></div>
					</div>
				<?php elseif ($tab === 'users'): ?>
                        <?php include __DIR__ . '/usermanagement.php'; ?>    
				<?php elseif ($tab === 'products'): ?>
                        <?php include __DIR__ . '/productpricing.php'; ?>
				<?php elseif ($tab === 'inventory'): ?>
                        <?php include __DIR__ . '/inventory.php'; ?>
				<?php elseif ($tab === 'batches'): ?>
                    <?php include __DIR__ . '/batches.php'; ?>
				<?php elseif ($tab === 'orders'): ?>
                    <?php include __DIR__ . '/orders.php'; ?>
				<?php elseif ($tab === 'payments'): ?>
                    <?php include __DIR__ . '/payments.php'; ?>
				<?php elseif ($tab === 'audit'): ?>
                    <?php include __DIR__ . '/audit.php'; ?>
    <?php if (empty($activity)): ?>
        <tr><td colspan="5" class="empty">No activity recorded.</td></tr>
    <?php else: ?>
        <?php foreach ($activity as $row): ?>
            <tr>
                <td><?= e($row['created_at']) ?></td>
                <td><?= e($row['username'] ?: 'Deleted account') ?></td>
                <td><span class="pill"><?= e($row['action_event']) ?></span></td>
                <td><?= e($row['description']) ?></td>
                <td><?= e($row['ip_address']) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody></table></div>
<?php endif; ?>
			</div>
		</main>
	</div>
</body>
</html>