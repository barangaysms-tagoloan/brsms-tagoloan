<?php
session_start();
require __DIR__ . "/../database/db.php";
require_once __DIR__ . "/../../phpqrcode/qrlib.php";
require_once __DIR__ . "/../logging.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$current_brgy_id = $_SESSION["brgy_id"];
$current_user_id = $_SESSION["user_id"];

$js_success_message = "";
$js_error_message = "";

if (isset($_SESSION["success_message"])) {
    $js_success_message = $_SESSION["success_message"];
    unset($_SESSION["success_message"]);
}

if (isset($_SESSION["error_message"])) {
    $js_error_message = $_SESSION["error_message"];
    unset($_SESSION["error_message"]);
}

function generateItemQRCode($item_id, $res_id, $res_name)
{
    $qrcodesDir = __DIR__ . "/qrcodes/";
    if (!is_dir($qrcodesDir)) {
        mkdir($qrcodesDir, 0755, true);
    }

    $qrContent = "https://brsms-tagoloan.com/get/item_details.php?item_id=$item_id";
    $filename = "qr_item_{$item_id}.png";
    $filepath = $qrcodesDir . $filename;
    $logoPath = __DIR__ . "/uploads/BRSMS_logo_transparent.png";

    QRcode::png($qrContent, $filepath, QR_ECLEVEL_H, 12, 4);

    if (file_exists($logoPath)) {
        $QR = imagecreatefrompng($filepath);
        $logo = imagecreatefrompng($logoPath);

        imagealphablending($QR, true);
        imagesavealpha($QR, true);
        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);
        $logo_qr_width = $QR_width / 4;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        $from_height = ($QR_height - $logo_qr_height) / 2;
        imagecopyresampled(
            $QR,
            $logo,
            $from_width,
            $from_height,
            0,
            0,
            $logo_qr_width,
            $logo_qr_height,
            $logo_width,
            $logo_height
        );
        imagepng($QR, $filepath);
        imagedestroy($QR);
        imagedestroy($logo);
    }

    return $filename;
}

function generateSerialNumber($res_id, $item_id, $res_name)
{
    return strtoupper(
        substr(preg_replace("/[^a-zA-Z0-9]/", "", $res_name), 0, 5)
    ) .
        "-" .
        str_pad($res_id, 3, "0", STR_PAD_LEFT) .
        "-" .
        str_pad($item_id, 4, "0", STR_PAD_LEFT);
}

function handleResourceAddition($conn, $current_brgy_id, $current_user_id)
{
    if (
        $_SERVER["REQUEST_METHOD"] !== "POST" ||
        !isset($_POST["add_resource"])
    ) {
        return;
    }

    $res_name = trim($_POST["name"]);
    $res_description = trim($_POST["description"]);
    $res_quantity = (int) $_POST["quantity"];
    $res_category_id = (int) $_POST["category_id"];
    $is_bulk = 1;
    $res_photo = "";

    if (
        isset($_FILES["photo"]) &&
        $_FILES["photo"]["error"] === UPLOAD_ERR_OK
    ) {
        $ext = strtolower(
            pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION)
        );
        if (in_array($ext, ["jpg", "jpeg", "png", "gif"])) {
            $filename = uniqid() . "." . $ext;
            $uploadsDir = __DIR__ . "/uploads/";
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            $uploadPath = $uploadsDir . $filename;
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadPath)) {
                $res_photo = $filename;
            } else {
                $_SESSION["error_message"] =
                    "Failed to upload photo. Please try again.";
                header("Location: inventory.php");
                exit();
            }
        } else {
            $_SESSION["error_message"] =
                "Invalid file type for photo. Only JPG, JPEG, PNG, GIF are allowed.";
            header("Location: inventory.php");
            exit();
        }
    }

    $conn->begin_transaction();
    try {
        $main_res_status = "Available";

        $stmt = $conn->prepare(
            "INSERT INTO resources (res_photo, res_name, res_description, res_quantity, res_status, is_bulk, brgy_id, res_category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "sssisiii",
            $res_photo,
            $res_name,
            $res_description,
            $res_quantity,
            $main_res_status,
            $is_bulk,
            $current_brgy_id,
            $res_category_id
        );
        $stmt->execute();
        $res_id = $conn->insert_id;

        $item_stmt = $conn->prepare(
            "INSERT INTO resource_items (res_id, item_status, qr_code, serial_number) VALUES (?, ?, ?, ?)"
        );
        $status_for_items = "Available";

        for ($i = 0; $i < $res_quantity; $i++) {
            $item_stmt_temp = $conn->prepare(
                "INSERT INTO resource_items (res_id, item_status) VALUES (?, ?)"
            );
            $item_stmt_temp->bind_param("is", $res_id, $status_for_items);
            $item_stmt_temp->execute();
            $item_id = $conn->insert_id;
            $item_stmt_temp->close();

            $qr_filename = generateItemQRCode($item_id, $res_id, $res_name);
            $serial_number = generateSerialNumber($res_id, $item_id, $res_name);

            $update_item_stmt = $conn->prepare(
                "UPDATE resource_items SET qr_code = ?, serial_number = ? WHERE item_id = ?"
            );
            $update_item_stmt->bind_param(
                "ssi",
                $qr_filename,
                $serial_number,
                $item_id
            );
            $update_item_stmt->execute();
            $update_item_stmt->close();
        }

        $conn->commit();
        $_SESSION["success_message"] = "Resource added successfully!";
        logResourceAdd($current_user_id, $res_name);
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION["error_message"] =
            "Error adding resource: " . $e->getMessage();
    }

    header("Location: inventory.php");
    exit();
}

function handleResourceEdit($conn, $current_user_id)
{
    if (
        $_SERVER["REQUEST_METHOD"] !== "POST" ||
        !isset($_POST["edit_resource"])
    ) {
        return;
    }

    $res_id = (int) $_POST["edit_id"];
    $res_name = trim($_POST["edit_name"]);
    $res_description = trim($_POST["edit_description"]);
    $new_quantity = (int) $_POST["edit_quantity"];
    $res_category_id = (int) $_POST["edit_category_id"];
    $is_bulk_from_form = 1;

    $check_stmt = $conn->prepare(
        "SELECT res_quantity, res_photo, res_name FROM resources WHERE res_id = ? AND brgy_id = ?"
    );
    $check_stmt->bind_param("ii", $res_id, $_SESSION["brgy_id"]);
    $check_stmt->execute();
    $resource_data = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();

    if (!$resource_data) {
        $_SESSION["error_message"] =
            "Resource not found or not authorized for editing.";
        header("Location: inventory.php");
        exit();
    }

    $current_quantity = $resource_data["res_quantity"];
    $new_photo = $resource_data["res_photo"];
    $current_res_name = $resource_data["res_name"];

    $conn->begin_transaction();
    try {
        if (
            isset($_FILES["edit_photo"]) &&
            $_FILES["edit_photo"]["error"] === UPLOAD_ERR_OK
        ) {
            $ext = strtolower(
                pathinfo($_FILES["edit_photo"]["name"], PATHINFO_EXTENSION)
            );
            if (in_array($ext, ["jpg", "jpeg", "png", "gif"])) {
                if (!empty($resource_data["res_photo"])) {
                    $old_photo_path = __DIR__ . "/uploads/";
                        $resource_data["res_photo"];
                    if (file_exists($old_photo_path)) {
                        unlink($old_photo_path);
                    }
                }

                $filename = uniqid() . "." . $ext;
                $uploadsDir = __DIR__ . "/uploads/";
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }
                $uploadPath = $uploadsDir . $filename;
                if (
                    move_uploaded_file(
                        $_FILES["edit_photo"]["tmp_name"],
                        $uploadPath
                    )
                ) {
                    $new_photo = $filename;
                } else {
                    throw new Exception(
                        "Failed to upload new photo. Please try again."
                    );
                }
            } else {
                throw new Exception(
                    "Invalid file type for new photo. Only JPG, JPEG, PNG, GIF are allowed."
                );
            }
        }

        $update_res_stmt = $conn->prepare(
            "UPDATE resources SET res_name = ?, res_description = ?, res_photo = ?, is_bulk = ?, res_category_id = ? WHERE res_id = ?"
        );
        $update_res_stmt->bind_param(
            "sssiii",
            $res_name,
            $res_description,
            $new_photo,
            $is_bulk_from_form,
            $res_category_id,
            $res_id
        );
        $update_res_stmt->execute();
        $update_res_stmt->close();

        $quantity_diff = $new_quantity - $current_quantity;

        if ($quantity_diff > 0) {
            $item_stmt = $conn->prepare(
                "INSERT INTO resource_items (res_id, item_status, qr_code, serial_number) VALUES (?, ?, ?, ?)"
            );
            $status = "Available";

            for ($i = 0; $i < $quantity_diff; $i++) {
                $item_stmt_temp = $conn->prepare(
                    "INSERT INTO resource_items (res_id, item_status) VALUES (?, ?)"
                );
                $item_stmt_temp->bind_param("is", $res_id, $status);
                $item_stmt_temp->execute();
                $item_id = $conn->insert_id;
                $item_stmt_temp->close();

                $qr_filename = generateItemQRCode($item_id, $res_id, $res_name);
                $serial_number = generateSerialNumber(
                    $res_id,
                    $item_id,
                    $res_name
                );

                $update_qr_stmt = $conn->prepare(
                    "UPDATE resource_items SET qr_code = ?, serial_number = ? WHERE item_id = ?"
                );
                $update_qr_stmt->bind_param(
                    "ssi",
                    $qr_filename,
                    $serial_number,
                    $item_id
                );
                $update_qr_stmt->execute();
                $update_qr_stmt->close();
            }
        } elseif ($quantity_diff < 0) {
            $items_to_remove = abs($quantity_diff);

            $get_qr_stmt = $conn->prepare(
                "SELECT qr_code FROM resource_items WHERE res_id = ? AND item_status = 'Available' LIMIT ?"
            );
            $get_qr_stmt->bind_param("ii", $res_id, $items_to_remove);
            $get_qr_stmt->execute();
            $qr_to_delete_result = $get_qr_stmt->get_result();
            $qrs_to_delete = [];
            while ($row = $qr_to_delete_result->fetch_assoc()) {
                if (!empty($row["qr_code"])) {
                    $qrs_to_delete[] = "qrcodes/" . $row["qr_code"];
                }
            }
            $get_qr_stmt->close();

            $delete_available_stmt = $conn->prepare(
                "DELETE FROM resource_items WHERE res_id = ? AND item_status = 'Available' LIMIT ?"
            );
            $delete_available_stmt->bind_param("ii", $res_id, $items_to_remove);
            $delete_available_stmt->execute();
            $deleted_count = $delete_available_stmt->affected_rows;
            $delete_available_stmt->close();

            foreach ($qrs_to_delete as $qr_path) {
                if (file_exists($qr_path)) {
                    unlink($qr_path);
                }
            }

            $remaining_to_remove = $items_to_remove - $deleted_count;

            if ($remaining_to_remove > 0) {
                $get_qr_stmt = $conn->prepare(
                    "SELECT qr_code FROM resource_items WHERE res_id = ? ORDER BY FIELD(item_status, 'Available', 'Under Maintenance', 'Borrowed', 'Lost') DESC LIMIT ?"
                );
                $get_qr_stmt->bind_param("ii", $res_id, $remaining_to_remove);
                $get_qr_stmt->execute();
                $qr_to_delete_result = $get_qr_stmt->get_result();
                $qrs_to_delete = [];
                while ($row = $qr_to_delete_result->fetch_assoc()) {
                    if (!empty($row["qr_code"])) {
                        $qrs_to_delete[] = "logic/qrcodes/" . $row["qr_code"];
                    }
                }
                $get_qr_stmt->close();

                $delete_other_stmt = $conn->prepare(
                    "DELETE FROM resource_items WHERE res_id = ? ORDER BY FIELD(item_status, 'Available', 'Under Maintenance', 'Borrowed', 'Lost') DESC LIMIT ?"
                );
                $delete_other_stmt->bind_param(
                    "ii",
                    $res_id,
                    $remaining_to_remove
                );
                $delete_other_stmt->execute();
                $delete_other_stmt->close();

                foreach ($qrs_to_delete as $qr_path) {
                    if (file_exists($qr_path)) {
                        unlink($qr_path);
                    }
                }
            }
        }

        $update_quantity_stmt = $conn->prepare(
            "UPDATE resources SET res_quantity = ? WHERE res_id = ?"
        );
        $update_quantity_stmt->bind_param("ii", $new_quantity, $res_id);
        $update_quantity_stmt->execute();
        $update_quantity_stmt->close();

        $update_main_res_status_stmt = $conn->prepare("
            UPDATE resources
            SET res_status = CASE
                WHEN (SELECT COUNT(*) FROM resource_items WHERE res_id = ? AND item_status = 'Available') > 0 THEN 'Available'
                ELSE 'Unavailable'
            END
            WHERE res_id = ? AND is_bulk = 1
        ");
        $update_main_res_status_stmt->bind_param("ii", $res_id, $res_id);
        $update_main_res_status_stmt->execute();
        $update_main_res_status_stmt->close();

        $conn->commit();
        $_SESSION["success_message"] = "Resource updated successfully!";
        logResourceEdit($current_user_id, $res_name);
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION["error_message"] =
            "Error updating resource: " . $e->getMessage();
    }

    header("Location: inventory.php");
    exit();
}

function handleResourceDeletion($conn, $current_user_id)
{
    if (
        $_SERVER["REQUEST_METHOD"] !== "POST" ||
        !isset($_POST["delete_resource"])
    ) {
        return;
    }

    $res_id = (int) $_POST["delete_id"];

    $conn->begin_transaction();
    try {
        $photo_query = $conn->prepare(
            "SELECT res_photo, res_name FROM resources WHERE res_id = ? AND brgy_id = ?"
        );
        $photo_query->bind_param("ii", $res_id, $_SESSION["brgy_id"]);
        $photo_query->execute();
        $photo_result = $photo_query->get_result();

        if ($photo_result->num_rows > 0) {
            $resource_info = $photo_result->fetch_assoc();
            $photo = $resource_info["res_photo"];
            $res_name = $resource_info["res_name"];
            $photo_path = "logic/uploads/" . $photo;
            if (!empty($photo) && file_exists($photo_path)) {
                unlink($photo_path);
            }
        }
        $photo_query->close();

        $get_qr_stmt = $conn->prepare(
            "SELECT qr_code FROM resource_items WHERE res_id = ?"
        );
        $get_qr_stmt->bind_param("i", $res_id);
        $get_qr_stmt->execute();
        $qr_to_delete_result = $get_qr_stmt->get_result();
        $qrs_to_delete = [];
        while ($row = $qr_to_delete_result->fetch_assoc()) {
            if (!empty($row["qr_code"])) {
                $qrs_to_delete[] = "logic/qrcodes/" . $row["qr_code"];
            }
        }
        $get_qr_stmt->close();

        $delete_items = $conn->prepare(
            "DELETE FROM resource_items WHERE res_id = ?"
        );
        $delete_items->bind_param("i", $res_id);
        $delete_items->execute();
        $delete_items->close();

        foreach ($qrs_to_delete as $qr_path) {
            if (file_exists($qr_path)) {
                unlink($qr_path);
            }
        }

        $delete_stmt = $conn->prepare(
            "DELETE FROM resources WHERE res_id = ? AND brgy_id = ?"
        );
        $delete_stmt->bind_param("ii", $res_id, $_SESSION["brgy_id"]);
        $delete_stmt->execute();
        $delete_stmt->close();

        $conn->commit();
        $_SESSION["success_message"] = "Resource deleted successfully!";
        logResourceDelete($current_user_id, $res_name);
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION["error_message"] =
            "Error deleting resource: " . $e->getMessage();
    }

    header("Location: inventory.php");
    exit();
}

function handleItemStatusUpdate($conn, $current_user_id)
{
    if (
        $_SERVER["REQUEST_METHOD"] !== "POST" ||
        !isset($_POST["update_item_status"])
    ) {
        return;
    }

    header("Content-Type: application/json");

    try {
        $item_ids = isset($_POST["item_id"]) ? (array) $_POST["item_id"] : [];
        $new_status = $_POST["status"];

        if (empty($item_ids)) {
            throw new Exception("No items selected for update.");
        }

        $conn->begin_transaction();
        $updated_res_ids = [];
        $logged_items = [];

        foreach ($item_ids as $item_id) {
            $item_id = (int) $item_id;

            $verify_stmt = $conn->prepare("
                SELECT ri.item_id, ri.res_id, ri.item_status, r.res_name
                FROM resource_items ri
                JOIN resources r ON ri.res_id = r.res_id
                WHERE ri.item_id = ? AND r.brgy_id = ?
            ");
            $verify_stmt->bind_param("ii", $item_id, $_SESSION["brgy_id"]);
            $verify_stmt->execute();
            $result = $verify_stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception(
                    "Item ID {$item_id} not found or not authorized."
                );
            }
            $item_data = $result->fetch_assoc();
            $res_id = $item_data["res_id"];
            $old_status = $item_data["item_status"];
            $resource_name = $item_data["res_name"];
            $verify_stmt->close();

            $stmt = $conn->prepare(
                "UPDATE resource_items SET item_status = ? WHERE item_id = ?"
            );
            $stmt->bind_param("si", $new_status, $item_id);
            $stmt->execute();
            $stmt->close();

            $updated_res_ids[] = $res_id;
            $logged_items[] = [
                "item_id" => $item_id,
                "old_status" => $old_status,
                "new_status" => $new_status,
                "resource_name" => $resource_name,
            ];
        }

        $updated_res_ids = array_unique($updated_res_ids);
        foreach ($updated_res_ids as $res_id) {
            $update_main_res_status_stmt = $conn->prepare("
                UPDATE resources
                SET res_status = CASE
                    WHEN (SELECT COUNT(*) FROM resource_items WHERE res_id = ? AND item_status = 'Available') > 0 THEN 'Available'
                    ELSE 'Unavailable'
                END
                WHERE res_id = ? AND is_bulk = 1
            ");
            $update_main_res_status_stmt->bind_param("ii", $res_id, $res_id);
            $update_main_res_status_stmt->execute();
            $update_main_res_status_stmt->close();
        }

        $conn->commit();
        echo json_encode([
            "success" => true,
            "message" => "Item status updated successfully!",
        ]);

        if (count($item_ids) === 1) {
            $item = $logged_items[0];
            logResourceItemStatusUpdate(
                $current_user_id,
                $item["item_id"],
                $item["old_status"],
                $item["new_status"],
                $item["resource_name"]
            );
        } else {
            $item_ids_only = array_column($logged_items, "item_id");
            $resource_name_for_bulk =
                $logged_items[0]["resource_name"] ?? "N/A";
            logResourceBulkItemStatusUpdate(
                $current_user_id,
                $item_ids_only,
                $new_status,
                $resource_name_for_bulk
            );
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit();
}

function getResourceStatusBreakdown($conn, $res_id)
{
    $stmt = $conn->prepare("
        SELECT
            COUNT(CASE WHEN item_status = 'Available' THEN 1 END) as available,
            COUNT(CASE WHEN item_status = 'Borrowed' THEN 1 END) as borrowed,
            COUNT(CASE WHEN item_status = 'Under Maintenance' THEN 1 END) as maintenance,
            COUNT(CASE WHEN item_status = 'Lost' THEN 1 END) as lost,
            COUNT(*) as total
        FROM resource_items
        WHERE res_id = ?
    ");
    $stmt->bind_param("i", $res_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

function handleCategoryAddition($conn, $current_brgy_id, $current_user_id)
{
    if (
        $_SERVER["REQUEST_METHOD"] !== "POST" ||
        !isset($_POST["add_category_submit"])
    ) {
        return;
    }

    header("Content-Type: application/json");

    $category_name = trim($_POST["category_name"]);

    if (empty($category_name)) {
        echo json_encode([
            "success" => false,
            "message" => "Category name cannot be empty.",
        ]);
        exit();
    }

    try {
        $check_stmt = $conn->prepare(
            "SELECT COUNT(*) FROM resource_categories WHERE category_name = ? AND brgy_id = ?"
        );
        $check_stmt->bind_param("si", $category_name, $current_brgy_id);
        $check_stmt->execute();
        $count = $check_stmt->get_result()->fetch_row()[0];
        $check_stmt->close();

        if ($count > 0) {
            echo json_encode([
                "success" => false,
                "message" => "Category '{$category_name}' already exists for your barangay.",
            ]);
            exit();
        }

        $stmt = $conn->prepare(
            "INSERT INTO resource_categories (category_name, brgy_id) VALUES (?, ?)"
        );
        $stmt->bind_param("si", $category_name, $current_brgy_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode([
            "success" => true,
            "message" => "Resource category added successfully!",
        ]);
        logCategoryAdd($current_user_id, $category_name);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => "Error adding category: " . $e->getMessage(),
        ]);
    }
    exit();
}

function handleCategoryDeletion($conn, $current_brgy_id, $current_user_id)
{
    if (
        $_SERVER["REQUEST_METHOD"] !== "POST" ||
        !isset($_POST["delete_category_submit"])
    ) {
        return;
    }

    header("Content-Type: application/json");

    $category_id = (int) $_POST["category_id_to_delete"];

    $conn->begin_transaction();
    try {
        $get_category_name_stmt = $conn->prepare(
            "SELECT category_name FROM resource_categories WHERE category_id = ? AND brgy_id = ?"
        );
        $get_category_name_stmt->bind_param(
            "ii",
            $category_id,
            $current_brgy_id
        );
        $get_category_name_stmt->execute();
        $category_name_result = $get_category_name_stmt
            ->get_result()
            ->fetch_assoc();
        $category_name =
            $category_name_result["category_name"] ?? "Unknown Category";
        $get_category_name_stmt->close();

        $check_resources_stmt = $conn->prepare(
            "SELECT COUNT(*) FROM resources WHERE res_category_id = ? AND brgy_id = ?"
        );
        $check_resources_stmt->bind_param("ii", $category_id, $current_brgy_id);
        $check_resources_stmt->execute();
        $resource_count = $check_resources_stmt->get_result()->fetch_row()[0];
        $check_resources_stmt->close();

        if ($resource_count > 0) {
            throw new Exception(
                "Cannot delete category. There are {$resource_count} resources currently assigned to this category. Please reassign or delete them first."
            );
        }

        $delete_stmt = $conn->prepare(
            "DELETE FROM resource_categories WHERE category_id = ? AND brgy_id = ?"
        );
        $delete_stmt->bind_param("ii", $category_id, $current_brgy_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        $conn->commit();
        echo json_encode([
            "success" => true,
            "message" => "Resource category deleted successfully!",
        ]);
        logCategoryDelete($current_user_id, $category_name);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            "success" => false,
            "message" => "Error deleting category: " . $e->getMessage(),
        ]);
    }
    exit();
}

function getResourceCategories($conn, $brgy_id)
{
    $categories = [];
    $stmt = $conn->prepare(
        "SELECT category_id, category_name FROM resource_categories WHERE brgy_id = ? ORDER BY category_name ASC"
    );
    $stmt->bind_param("i", $brgy_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $stmt->close();
    return $categories;
}

handleResourceAddition($conn, $current_brgy_id, $current_user_id);
handleResourceEdit($conn, $current_user_id);
handleResourceDeletion($conn, $current_user_id);
handleItemStatusUpdate($conn, $current_user_id);
handleCategoryAddition($conn, $current_brgy_id, $current_user_id);
handleCategoryDeletion($conn, $current_brgy_id, $current_user_id);

$resource_categories = getResourceCategories($conn, $current_brgy_id);
$search_query = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$category_filter = isset($_GET["category_filter"])
    ? (int) $_GET["category_filter"]
    : "All";
$base_query =
    "SELECT r.*, rc.category_name FROM resources r LEFT JOIN resource_categories rc ON r.res_category_id = rc.category_id WHERE r.brgy_id = ?";
$params = [$current_brgy_id];
$types = "i";

if ($category_filter !== "All" && $category_filter > 0) {
    $base_query .= " AND r.res_category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

$stmt = $conn->prepare($base_query);
if (!empty($params)) {
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array([$stmt, "bind_param"], $bind_params);
}
$stmt->execute();
$result = $stmt->get_result();
?>