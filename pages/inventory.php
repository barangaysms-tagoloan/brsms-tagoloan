<?php
require '../logic/inventory/inventory_logic.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - BRSMS</title>
    <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/inventory.css">
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 sidebar d-flex flex-column">
        <div class="logo-container">
            <div class="logo-circle">
                <img src="../uploads/BRSMS.png" alt="BRSMS Logo">
            </div>
            <div class="logo-text">BRSMS</div>
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="inventory.php" class="active"><i class="fas fa-boxes"></i> Inventory</a>
        <a href="request.php"><i class="fas fa-hand-holding"></i> Request Resource</a>
        <a href="request_status.php"><i class="fas fa-history"></i> Request Status</a>
        <a href="incoming.php"><i class="fas fa-inbox"></i> Incoming Request</a>
        <a href="issued_resources.php"><i class="fas fa-hand-holding-heart"></i> <span>Issued Resources</span></a>
        <a href="returning.php"><i class="fas fa-exchange-alt"></i> Returning</a>
        <a href="report.php"><i class="fas fa-chart-bar"></i> Report</a>
        <a href="user_settings.php"><i class="fas fa-cog"></i> Settings</a>
        <div class="mt-auto">
            <a href="logout.php" class="logout-btn mt-4" id="logoutButton"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>

    <div class="col-md-9 col-lg-10 p-4 main-content">
        <!-- Hidden inputs for PHP messages -->
        <input type="hidden" id="php_success_message" value="<?= htmlspecialchars($js_success_message) ?>">
        <input type="hidden" id="php_error_message" value="<?= htmlspecialchars($js_error_message) ?>">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-header">Inventory Management</h1>
            <!-- Actions Dropdown Button - Moved here -->
            <div class="actions-dropdown">
                <button class="btn dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-plus-circle me-1"></i> Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown">

                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal"><i class="fas fa-layer-group me-2"></i> Manage Categories</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i> Add Resource</a></li>
                </ul>
            </div>
        </div>

        <div class="table-wrapper">
            <div class="search-filter-row">
                <div class="search-input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search resources..." value="<?= htmlspecialchars($search_query) ?>">
                </div>
                <div class="category-filter-group">
                    <label for="category_filter" class="form-label me-1 mb-0">Category:</label>
                    <select id="category_filter" class="form-select">
                        <option value="All">All CATEGORY</option>
                        <?php foreach ($resource_categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>" <?= ($category_filter == $category['category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <table class="table table-bordered table-hover" id="inventoryTable"> <!-- Added id="inventoryTable" -->
                <thead>
                    <tr>
                        <th style="width: 120px;">Photo</th>
                        <th>Resource Name</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Resource Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()):
                            $status_breakdown = getResourceStatusBreakdown($conn, $row['res_id']);
                            $photo_src = !empty($row['res_photo']) ? "/logic/inventory/uploads/".htmlspecialchars($row['res_photo']) : "https://via.placeholder.com/120x80?text=No+Image";
                        ?>
                        <tr>
                            <td class="photo-cell">
                                <img src="../<?= $photo_src ?>" class="res-img" alt="<?= htmlspecialchars($row['res_name']) ?>">
                            </td>
                            <td><?= htmlspecialchars($row['res_name']) ?></td>
                            <td>
                                <?= htmlspecialchars($row['res_description']) ?>
                            </td>
                            <td><?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></td>
                            <td>
                                <?= $row['res_quantity'] ?>
                                <?php if (($status_breakdown['borrowed'] ?? 0) > 0): ?>
                                    / <span class="borrowed-quantity"><?= $status_breakdown['borrowed'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                               <button class="btn btn-sm btn-violet-text-grey-bg view-items mb-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#itemsModal"
                                        data-resid="<?= $row['res_id'] ?>"
                                        data-name="<?= htmlspecialchars($row['res_name']) ?>"
                                        data-available="<?= $status_breakdown['available'] ?? 0 ?>"
                                        data-borrowed="<?= $status_breakdown['borrowed'] ?? 0 ?>"
                                        data-maintenance="<?= $status_breakdown['maintenance'] ?? 0 ?>"
                                        data-lost="<?= $status_breakdown['lost'] ?? 0 ?>"
                                        data-total="<?= $status_breakdown['total'] ?? 0 ?>">
                                    <i class="fas fa-list me-1"></i> Manage Items
                                </button>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn btn-sm btn-warning edit-resource"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="<?= $row['res_id'] ?>"
                                            data-name="<?= htmlspecialchars($row['res_name']) ?>"
                                            data-description="<?= htmlspecialchars($row['res_description']) ?>"
                                            data-quantity="<?= $row['res_quantity'] ?>"
                                            data-photo="<?= $photo_src ?>"
                                            data-categoryid="<?= $row['res_category_id'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-resource-btn"
                                            data-id="<?= $row['res_id'] ?>"
                                            data-name="<?= htmlspecialchars($row['res_name']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <?php if (!empty($search_query) || $category_filter !== 'All'): ?>
                                    No resources found matching your criteria.
                                <?php else: ?>
                                    No resources found in your inventory. Click "Actions" to add a resource!
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Footer (copied from dashboard.php) -->
        <footer class="footer mt-auto py-1">
            <div class="container-fluid">
                <span>&copy; <?= date('Y') ?> BRSMS. All rights reserved.</span>
            </div>
        </footer>
    </div>
</div>

<!-- Add Resource Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addResourceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <!-- Added modal-dialog-centered -->
        <form class="modal-content" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="addResourceLabel">Add New Resource</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="add_resource" value="1">
                <div class="mb-3">
                    <label for="photo" class="form-label">Resource Photo</label>
                    <input type="file" class="form-control" name="photo" accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Resource Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">Select a Category</option>
                        <?php foreach ($resource_categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="quantity" class="form-label">Initial Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
                    <small class="form-text text-muted">Each quantity will create an individual item, all starting as 'Available'.</small>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="submit" class="btn btn-primary w-100">Add Resource</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Resource Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editResourceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <!-- Added modal-dialog-centered -->
        <form class="modal-content" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="editResourceLabel">Edit Resource Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="edit_resource" value="1">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Current Photo</label>
                    <img src="" id="current_photo" class="res-img mb-2 d-block img-thumbnail">
                    <label for="edit_photo" class="form-label">Change Photo (optional)</label>
                    <input type="file" class="form-control" name="edit_photo" id="edit_photo" accept="image/*">
                </div>
                <div class="mb-3">
                    <label for="edit_name" class="form-label">Resource Name</label>
                    <input type="text" name="edit_name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="edit_description" class="form-label">Description</label>
                    <textarea name="edit_description" id="edit_description" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="edit_category_id" class="form-label">Category</label>
                    <select name="edit_category_id" id="edit_category_id" class="form-select" required>
                        <option value="">Select a Category</option>
                        <?php foreach ($resource_categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="edit_quantity" class="form-label">Total Quantity</label>
                    <input type="number" name="edit_quantity" id="edit_quantity" class="form-control" min="1" required>
                </div>
            </div>
            <div class="modal-footer justify-content-center"> <!-- Added justify-content-center -->
                <button type="submit" class="btn btn-primary w-100">Update Resource</button> <!-- Added w-100 -->
            </div>
        </form>
    </div>
</div>

<!-- NEW: Manage Categories Modal -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1" aria-labelledby="manageCategoriesLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered"> <!-- Added modal-dialog-centered -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageCategoriesLabel">Manage Resource Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="mb-3">Add New Category</h6>
                <form id="addCategoryForm" method="POST" class="mb-4">
                    <input type="hidden" name="add_category_submit" value="1">
                    <div class="mb-3">
                        <label for="new_category_name" class="form-label visually-hidden">Category Name</label>
                        <input type="text" name="category_name" id="new_category_name" class="form-control" placeholder="Enter new category name" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> Add Category</button>
                </form>

                <h6 class="mb-3">Existing Categories</h6>
                <div class="table-responsive category-list-table">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Category Name</th>
                                <th style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesTableBody">
                            <!-- Categories will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<!-- Items Management Modal -->
<div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- Added modal-dialog-centered -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemsModalLabel">MANAGE ITEMS FOR <span id="itemsResourceName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3 status-card" data-filter-status="Available">
                            <div class="card-body text-center">
                                <h5 class="card-title">Available</h5>
                                <h3 id="availableItemsCount" class="card-text status-count">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3 status-card" data-filter-status="Borrowed">
                            <div class="card-body text-center">
                                <h5 class="card-title">Borrowed</h5>
                                <h3 id="borrowedItemsCount" class="card-text status-count">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger mb-3 status-card" data-filter-status="Under Maintenance">
                            <div class="card-body text-center">
                                <h5 class="card-title">Maintenance</h5>
                                <h3 id="maintenanceItemsCount" class="card-text status-count">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-danger mb-3 status-card" data-filter-status="Lost" style="background-color: var(--lost-color) !important;">
                            <div class="card-body text-center">
                                <h5 class="card-title">Lost</h5>
                                <h3 id="lostItemsCount" class="card-text status-count">0</h3>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-end align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <label for="bulk_status_select" class="form-label mb-0">Update:</label>
                        <select id="bulk_status_select" class="form-select form-select-sm w-auto">
                            <option value="">Select Status</option>
                            <option value="Available">Available</option>
                            <option value="Borrowed">Borrowed</option>
                            <option value="Under Maintenance">Under Maintenance</option>
                            <option value="Lost">Lost</option>
                        </select>
                        <button type="button" class="btn btn-primary btn-sm" id="bulkUpdateBtn">Apply to Selected</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;"><input type="checkbox" id="selectAllItems"></th>
                                <!-- Removed Item Name column header -->
                                <th>Serial Number</th>
                                <th>Status</th>
                                <th style="width: 120px;">QR Code</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <!-- The "Close" button was here and has been removed -->
            </div>
        </div>
    </div>
</div>

<!-- Custom Alert Modal Overlay (Unified for all custom alerts) -->
<div class="custom-modal-overlay" id="unifiedCustomAlertOverlay">
    <div class="custom-modal" id="unifiedCustomAlertModal">
        <button type="button" class="close-btn" aria-label="Close" onclick="hideCustomAlert()">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-icon"></div>
        <h4 class="modal-title"></h4>
        <p class="modal-message"></p>
        <div class="modal-actions">
            <!-- Buttons will be dynamically added/removed by JS -->
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="../assets/js/inventory.js"></script>
</body>
</html>
