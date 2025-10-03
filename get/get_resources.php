<?php
session_start();
require '../logic/database/db.php';

if (!isset($_GET['brgy_id']) || !is_numeric($_GET['brgy_id'])) {
    echo '<div class="no-resources-message"><i class="fas fa-exclamation-triangle me-2"></i> Invalid request.</div>';
    exit();
}

$brgy_id = (int)$_GET['brgy_id'];
$current_brgy_id = $_SESSION['brgy_id'] ?? 0;
$view_type = $_GET['view_type'] ?? 'cards';
$stmt = $conn->prepare("SELECT brgy_name FROM barangays WHERE brgy_id = ?");
$stmt->bind_param("i", $brgy_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="no-resources-message"><i class="fas fa-exclamation-triangle me-2"></i> Barangay not found.</div>';
    exit();
}

$barangay = $result->fetch_assoc();



$query = "
    SELECT
        r.res_id,
        r.res_photo,
        r.res_name,
        r.res_description,
        r.brgy_id,
        r.is_bulk,
        r.res_quantity, -- Get total quantity for display in modal (this will be adjusted later)
        r.res_status
    FROM
        resources r
    WHERE
        r.brgy_id = ?
        AND r.res_status = 'Available' -- Only show resources that are generally 'Available' (not under maintenance, etc.)
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $brgy_id);
$stmt->execute();
$resources = $stmt->get_result();

if ($resources->num_rows > 0):
    if ($view_type === 'table') {
        
?>
        <div class="table-responsive">
            <table class="table table-hover resource-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Resource Name</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        <?php while ($item = $resources->fetch_assoc()):
            $photo_src = !empty($item['res_photo']) ? '/logic/inventory/uploads/'.htmlspecialchars($item['res_photo']) : 'images/default-item.jpg';

            
            $displayed_quantity = $item['res_quantity']; 
            if ((bool)$item['is_bulk']) {
                $item_quantity_stmt = $conn->prepare("
                    SELECT COUNT(*) as count_available_borrowed
                    FROM resource_items
                    WHERE res_id = ? AND item_status IN ('Available', 'Borrowed')
                ");
                $item_quantity_stmt->bind_param("i", $item['res_id']);
                $item_quantity_stmt->execute();
                $item_quantity_result = $item_quantity_stmt->get_result();
                if ($row = $item_quantity_result->fetch_assoc()) {
                    $displayed_quantity = (int)$row['count_available_borrowed'];
                }
                $item_quantity_stmt->close();
            }
            
        ?>
                    <tr>
                        <td><img src="<?= $photo_src ?>" alt="<?= htmlspecialchars($item['res_name']) ?>" class="resource-table-img"></td>
                        <td><?= htmlspecialchars($item['res_name']) ?></td>
                        <td><?= htmlspecialchars($item['res_description'] ?: 'No description') ?></td>
                        <td><?= $displayed_quantity ?></td> <!-- Display adjusted quantity -->
                        <td>
                            <button class="btn btn-request btn-sm"
                                    onclick="showNoticeModal(
                                        <?= $item['res_id'] ?>,
                                        '<?= htmlspecialchars($item['res_name']) ?>',
                                        '<?= htmlspecialchars($item['res_description'] ?: 'No description') ?>',
                                        <?= $displayed_quantity ?>, <!-- Pass adjusted quantity -->
                                        <?= $item['brgy_id'] ?>,
                                        '<?= htmlspecialchars($barangay['brgy_name']) ?>'
                                    )">
                                <i class="fas fa-hand-holding me-1"></i> Request
                            </button>
                        </td>
                    </tr>
        <?php endwhile; ?>
                </tbody>
            </table>
        </div>
<?php
    } else {
        
        while ($item = $resources->fetch_assoc()):
            $photo_src = !empty($item['res_photo']) ? '/logic/inventory/uploads/'.htmlspecialchars($item['res_photo']) : 'images/default-item.jpg';

            
            $displayed_quantity = $item['res_quantity']; 
            if ((bool)$item['is_bulk']) {
                $item_quantity_stmt = $conn->prepare("
                    SELECT COUNT(*) as count_available_borrowed
                    FROM resource_items
                    WHERE res_id = ? AND item_status IN ('Available', 'Borrowed')
                ");
                $item_quantity_stmt->bind_param("i", $item['res_id']);
                $item_quantity_stmt->execute();
                $item_quantity_result = $item_quantity_stmt->get_result();
                if ($row = $item_quantity_result->fetch_assoc()) {
                    $displayed_quantity = (int)$row['count_available_borrowed'];
                }
                $item_quantity_stmt->close();
            }
            
?>
            <div class="resource-card">
                <img src="<?= $photo_src ?>" alt="<?= htmlspecialchars($item['res_name']) ?>">
                <div class="resource-card-body">
                    <h5><?= htmlspecialchars($item['res_name']) ?></h5>
                    <p><?= htmlspecialchars($item['res_description'] ?: 'No description') ?></p>
                </div>
                <div class="resource-card-footer">
                    <span class="badge status-available">
                        Qty: <?= $displayed_quantity ?> <!-- Display adjusted quantity -->
                    </span>
                    <button class="btn btn-request btn-sm"
                            onclick="showNoticeModal(
                                <?= $item['res_id'] ?>,
                                '<?= htmlspecialchars($item['res_name']) ?>',
                                '<?= htmlspecialchars($item['res_description'] ?: 'No description') ?>',
                                <?= $displayed_quantity ?>, <!-- Pass adjusted quantity -->
                                <?= $item['brgy_id'] ?>,
                                '<?= htmlspecialchars($barangay['brgy_name']) ?>'
                            )">
                        <i class="fas fa-hand-holding me-1"></i> Request
                    </button>
                </div>
            </div>
<?php
        endwhile;
    }
else:
?>
    <div class="no-resources-message">
        <i class="fas fa-exclamation-circle fa-3x mb-3 text-warning"></i>
        <h5>No Available Resources</h5>
        <p class="mb-0">No available resources found from <?= htmlspecialchars($barangay['brgy_name']) ?>.</p>
    </div>
<?php
endif;
$conn->close();
?>
