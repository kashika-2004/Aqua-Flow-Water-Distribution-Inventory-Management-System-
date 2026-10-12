<div class="section-heading">
    <div>
        <h2>Inventory Control</h2>
        <p>Monitor container stock levels, manage stock-in/out movements, and track thresholds.</p>
    </div>
</div>

<!-- Table 1: Empty Bottle Inventory -->
<div class="panel" style="margin-bottom: 28px;">
    <h3>Empty Bottle Inventory</h3>
    <p class="hint">Tracks returned or blank empty containers awaiting production filling batches.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Bottle Size</th>
                    <th style="text-align: right;">Current Quantity</th>
                    <th style="text-align: right;">Minimum Threshold</th>
                    <th style="text-align: right;">Update Stock</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($inventory as $row): ?>
                <?php $isLowEmpty = (int) $row['empty_quantity'] <= (int) $row['empty_threshold']; ?>
                <tr>
                    <td><strong><?= e($row['size_name']) ?></strong></td>
                    <td style="text-align: right;" class="<?= $isLowEmpty ? 'low' : '' ?>">
                        <?= number_format((int) $row['empty_quantity']) ?>
                        <?php if ($isLowEmpty): ?>
                            <span class="pill warn" style="margin-left: 6px; font-size: 9px;">Low</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;" class="muted"><?= number_format((int) $row['empty_threshold']) ?></td>
                    <td style="text-align: right;">
                        <form method="post" action="admindashboard.php?tab=inventory" style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px; margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['admin_csrf']) ?>">
                            <input type="hidden" name="tab" value="inventory">
                            <input type="hidden" name="action" value="adjust_inventory">
                            <input type="hidden" name="bottle_size_id" value="<?= (int) $row['size_id'] ?>">
                            <input type="hidden" name="inventory_type" value="EMPTY">
                            <select name="direction" style="width: auto; min-height: 32px; padding: 4px 6px; font-size: 11px; transform: translateX(-150px);">
                                <option value="IN">Stock In (+)</option>
                                <option value="OUT">Stock Out (-)</option>
                            </select>
                            <input name="quantity" type="number" min="1" placeholder="Qty" required style="width: 90px; min-height: 32px; padding: 4px 6px; font-size: 11px; transform: translateX(-100px);">
                            <button type="submit" class="primary" style="min-height: 32px; width: 70px; padding: 4px 10px; font-size: 11px; transform: translateX(-20px);">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Table 2: Filled Bottle Inventory -->
<div class="panel" style="margin-bottom: 28px;">
    <h3>Filled Bottle Inventory</h3>
    <p class="hint">Tracks ready-to-sell sealed water bottles and dispenser jars available for customer orders.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Bottle Size</th>
                    <th style="text-align: right;">Current Quantity</th>
                    <th style="text-align: right;">Minimum Threshold</th>
                    <th style="text-align: right;">Update Stock</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($inventory as $row): ?>
                <?php $isLowFilled = (int) $row['filled_quantity'] <= (int) $row['filled_threshold']; ?>
                <tr>
                    <td><strong><?= e($row['size_name']) ?></strong></td>
                    <td style="text-align: right;" class="<?= $isLowFilled ? 'low' : '' ?>">
                        <?= number_format((int) $row['filled_quantity']) ?>
                        <?php if ($isLowFilled): ?>
                            <span class="pill warn" style="margin-left: 6px; font-size: 9px;">Low</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;" class="muted"><?= number_format((int) $row['filled_threshold']) ?></td>
                    <td style="text-align: right;">
                        <form method="post" action="admindashboard.php?tab=inventory" style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px; margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['admin_csrf']) ?>">
                            <input type="hidden" name="tab" value="inventory">
                            <input type="hidden" name="action" value="adjust_inventory">
                            <input type="hidden" name="bottle_size_id" value="<?= (int) $row['size_id'] ?>">
                            <input type="hidden" name="inventory_type" value="FILLED">
                            <select name="direction" style="width: auto; min-height: 32px; padding: 4px 6px; font-size: 11px; transform: translateX(-150px);">
                                <option value="IN">Stock In (+)</option>
                                <option value="OUT">Stock Out (-)</option>
                            </select>
                            <input name="quantity" type="number" min="1" placeholder="Qty" required style="width: 90px; min-height: 32px; padding: 4px 6px; font-size: 11px; transform: translateX(-100px);">
                            <button type="submit" class="primary" style="min-height: 32px; width: 70px; padding: 4px 10px; font-size: 11px; transform: translateX(-20px);">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Product Packaging & Filled Stock Overview -->
<div class="panel" style="margin-bottom: 28px;">
    <h3>Product Packaging & Stock Levels</h3>
    <p class="hint">Overview of product pack configurations and available stock quantities calculated from filled inventory.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product Name / Type</th>
                    <th>Bottle Size</th>
                    <th>Units Per Pack</th>
                    <th style="text-align: right;">Packs Available</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $packOverviewQuery = $pdo->query(
                "SELECT p.product_name, bs.size_name, p.units_per_pack, p.sales_unit,
                 COALESCE(f.quantity_available, 0) AS filled_quantity
                 FROM products p 
                 JOIN bottle_sizes bs ON bs.id = p.bottle_size_id
                 LEFT JOIN filled_bottle_inventory f ON f.bottle_size_id = p.bottle_size_id
                 WHERE p.is_active = 1 AND p.sales_unit = 'PACK'
                 ORDER BY bs.volume_liters, p.product_name"
            );
            $packProducts = $packOverviewQuery->fetchAll(PDO::FETCH_ASSOC);
            foreach ($packProducts as $prod): 
                $unitsPerPack = max(1, (int) $prod['units_per_pack']);
                $packsAvailable = intdiv((int) $prod['filled_quantity'], $unitsPerPack);
            ?>
                <tr>
                    <td><strong><?= e($prod['product_name']) ?></strong></td>
                    <td><?= e($prod['size_name']) ?></td>
                    <td><?= number_format($unitsPerPack) ?> units / pack</td>
                    <td style="text-align: right; font-weight: 600;"><?= number_format($packsAvailable) ?> packs</td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$packProducts): ?>
                <tr><td colspan="4" class="empty">No product packs configured.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Stock Movements -->
<div class="panel">
    <h3>Recent Stock Movements</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>When</th>
                    <th>Type</th>
                    <th>Movement</th>
                    <th>Size</th>
                    <th style="text-align: right;">Quantity</th>
                    <th>Reference / Note</th>
                    <th>Staff</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($transactions, 0, 30) as $row): ?>
                <tr>
                    <td><?= e($row['created_at']) ?></td>
                    <td><span class="pill"><?= e($row['inventory_type']) ?></span></td>
                    <td><?= e(str_replace('_', ' ', $row['transaction_type'])) ?></td>
                    <td><?= e($row['size_name']) ?></td>
                    <td style="text-align: right; font-weight: 600;"><?= number_format((int) $row['quantity']) ?></td>
                    <td><?= e($row['reference_id'] ?: $row['remarks']) ?></td>
                    <td><?= e($row['processed_by']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$transactions): ?>
                <tr><td colspan="7" class="empty">No inventory movements recorded.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>