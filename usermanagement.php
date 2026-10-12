					<div class="section-heading"><div>
						<h2>System users</h2>
						<p>Edit profile details, assign roles, and control access status.</p>
					</div>
				</div>
					<div class="table-wrap">
						<table>
							<thead>
								<tr>
									<th>User</th>
									<th>Account profile, role and status</th>
									<th>Delivery address</th>
								</tr>
							</thead>
							<tbody>
					<?php foreach ($users as $user): ?>
						<tr>
							<td>
								<strong><?= e($user['full_name']) ?></strong>
								<br>
								<span class="muted">ID <?= (int) $user['id'] ?> · <?= e($user['username']) ?></span>
							</td>
							<td>
						<form method="post" action="admindashboard.php?tab=users" class="row-form">
							<input type="hidden" name="csrf_token" value="<?= e($_SESSION['admin_csrf']) ?>">
							<input type="hidden" name="tab" value="users">
							<input type="hidden" name="action" value="update_user">
							<input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
							<div>
								<label>Name</label>
								<input name="full_name" value="<?= e($user['full_name']) ?>" maxlength="100" required>
							</div>
							<div>
								<label>Username</label>
								<input name="username" value="<?= e($user['username']) ?>" maxlength="50" required>
							</div>
							<div>
								<label>Email</label>
								<input name="email" type="email" value="<?= e($user['email']) ?>" maxlength="100" required>
							</div>
							<div>
								<label>Phone</label>
								<input name="phone" value="<?= e($user['phone']) ?>" maxlength="20">
							</div>
							<div>
								<label>Role</label>
								<select name="role">
									<?php foreach (['ADMIN','CUSTOMER'] as $roleOption): ?>
										<option value="<?= e($roleOption) ?>" <?= $user['role'] === $roleOption ? 'selected' : '' ?>>
											<?= e(str_replace('_', ' ', $roleOption)) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div>
								<label>Status</label>
								<select name="status">
									<?php foreach (['ACTIVE','INACTIVE','SUSPENDED'] as $statusOption): ?>
										<option <?= $user['status'] === $statusOption ? 'selected' : '' ?>>
											<?= e($statusOption) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div>
								<label>Save</label>
								<button type="submit">Update</button>
							</div>
							<div style="grid-column:1/-1">
								<label>Customer delivery address</label>
								<input name="delivery_address" value="<?= e($user['delivery_address']) ?>" maxlength="500" placeholder="Required when role is CUSTOMER">
							</div>
						</form>
					</td>
					<td><?= e($user['delivery_address'] ?: '—') ?></td>
				</tr>
				<?php endforeach; ?>
					</tbody>
				</table>
			</div>