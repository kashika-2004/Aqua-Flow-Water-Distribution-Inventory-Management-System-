<div class="section-heading">
    <div>
        <h2>Product Pricing</h2>
        <p>Prices apply to one unit or one pack. Availability reflects current filled stock.</p>
    </div>
</div>

<!-- Single Units Table -->
<section class="product-pricing-group" style="margin-bottom: 32px;">
    <h3>Single Units</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Units Available</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $hasSingle = false; 
            foreach ($productPrices as $product): 
                if ($product['sales_unit'] !== 'SINGLE') { continue; } 
                $hasSingle = true; 
                $unitsPerPack = max(1, (int) $product['units_per_pack']); 
                $availableQuantity = intdiv((int) $product['filled_quantity'], $unitsPerPack); 
            ?>
                <tr>
                    <td><strong><?= e($product['product_name']) ?></strong></td>
                    <td><?= e($product['size_name']) ?></td>
                    <td><?= number_format($availableQuantity) ?> units</td>
                    <td>
                        <form class="product-price-form" method="post" action="admindashboard.php?tab=products">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['admin_csrf']) ?>">
                            <input type="hidden" name="tab" value="products">
                            <input type="hidden" name="action" value="update_product_price">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <label for="product-price-<?= (int) $product['id'] ?>" style="transform: translateX(-390px);">Price</label>
                            <input id="product-price-<?= (int) $product['id'] ?>" name="selling_price" type="number" min="0.01" max="99999999.99" step="0.01" value="<?= number_format((float) $product['selling_price'], 2, '.', '') ?>" required style="transform: translateX(-380px);">
                            <button class="primary" type="submit" style="transform: translateX(-50px);">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$hasSingle): ?>
                <tr>
                    <td colspan="4" class="empty">No single units are configured.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Packs Table -->
<section class="product-pricing-group">
    <h3>Packs</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Packs Available</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $hasPacks = false; 
            foreach ($productPrices as $product): 
                if ($product['sales_unit'] !== 'PACK') { continue; } 
                $hasPacks = true; 
                $unitsPerPack = max(1, (int) $product['units_per_pack']); 
                $availableQuantity = intdiv((int) $product['filled_quantity'], $unitsPerPack); 
            ?>
                <tr>
                    <td><strong><?= e($product['product_name']) ?></strong></td>
                    <td><?= e($product['size_name']) ?> · <?= number_format($unitsPerPack) ?> per pack</td>
                    <td><?= number_format($availableQuantity) ?> packs</td>
                    <td>
                        <form class="product-price-form" method="post" action="admindashboard.php?tab=products">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['admin_csrf']) ?>">
                            <input type="hidden" name="tab" value="products">
                            <input type="hidden" name="action" value="update_product_price">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <label for="product-price-<?= (int) $product['id'] ?>" style="transform: translateX(-320px);">Price</label>
                            <input id="product-price-<?= (int) $product['id'] ?>" name="selling_price" type="number" min="0.01" max="99999999.99" step="0.01" value="<?= number_format((float) $product['selling_price'], 2, '.', '') ?>" required style="transform: translateX(-310px);">
                            <button class="primary" type="submit" style="transform: translateX(-50px);">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$hasPacks): ?>
                <tr>
                    <td colspan="4" class="empty">No packs are configured.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>