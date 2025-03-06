<?php
require_once('../../inc/config/constants.php');
require_once('../../inc/config/db.php');

$initialStock = 0;
$baseImageFolder = '../../data/item_images/';
$itemImageFolder = '';

if (isset($_POST['itemDetailsItemNumber'])) {
    // Fetch and validate POST data
    $itemNumber = isset($_POST['itemDetailsItemNumber']) ? htmlentities($_POST['itemDetailsItemNumber']) : null;
    $itemName = isset($_POST['itemDetailsItemName']) ? htmlentities($_POST['itemDetailsItemName']) : null;
    $discount = isset($_POST['itemDetailsDiscount']) ? htmlentities($_POST['itemDetailsDiscount']) : '0';
    $quantity = isset($_POST['itemDetailsQuantity']) ? htmlentities($_POST['itemDetailsQuantity']) : null;
    $unitPrice = isset($_POST['itemDetailsUnitPrice']) ? htmlentities($_POST['itemDetailsUnitPrice']) : '0.00';
    $status = isset($_POST['itemDetailsStatus']) ? htmlentities($_POST['itemDetailsStatus']) : null;
    $description = isset($_POST['itemDetailsDescription']) ? htmlentities($_POST['itemDetailsDescription']) : null;

    // Debug log to check received inputs
    // Uncomment the next line to debug input values
    // var_dump($_POST);

    // Validate required fields
    if (!empty($itemNumber) && !empty($itemName) && isset($quantity)) {
        
        // Sanitize and validate input
        $itemNumber = filter_var($itemNumber, FILTER_SANITIZE_STRING);
        
        if (!filter_var($quantity, FILTER_VALIDATE_INT)) {
            echo '<div class="alert alert-danger">Please enter a valid quantity.</div>';
            exit();
        }
        if (!filter_var($unitPrice, FILTER_VALIDATE_FLOAT)) {
            echo '<div class="alert alert-danger">Please enter a valid unit price.</div>';
            exit();
        }
        if (!empty($discount) && !filter_var($discount, FILTER_VALIDATE_FLOAT)) {
            echo '<div class="alert alert-danger">Please enter a valid discount.</div>';
            exit();
        }

        // Validate dates
        $dateRegex = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($dateRegex, $itemExp) || !preg_match($dateRegex, $itemMan)) {
            echo '<div class="alert alert-danger">Enter valid dates in YYYY-MM-DD format.</div>';
            exit();
        }

        // Create folder for images
        $itemImageFolder = $baseImageFolder . $itemNumber;
        if (!is_dir($itemImageFolder)) {
            mkdir($itemImageFolder);
        }

        // Check if item already exists
        $stockSql = 'SELECT stock FROM item WHERE itemNumber = :itemNumber';
        $stockStatement = $conn->prepare($stockSql);
        $stockStatement->execute(['itemNumber' => $itemNumber]);
        if ($stockStatement->rowCount() > 0) {
            echo '<div class="alert alert-danger">Item already exists in the database.</div>';
            exit();
        }

        // Insert new item into the database
        $insertItemSql = 'INSERT INTO item(itemNumber, itemName, discount, stock, unitPrice, status, description, itemExp, itemMan)
                          VALUES(:itemNumber, :itemName, :discount, :stock, :unitPrice, :status, :description, :itemExp, :itemMan)';
        $insertItemStatement = $conn->prepare($insertItemSql);
        $insertItemStatement->execute([
            'itemNumber' => $itemNumber,
            'itemName' => $itemName,
            'discount' => $discount,
            'stock' => $quantity,
            'status' => $status,
            'description' => $description,
        ]);

        echo '<div class="alert alert-success">Item added to database.</div>';
        exit();
    } else {
        // Identify which field is causing the failure
        echo '<div class="alert alert-danger">Please fill all required fields. Debug Info:</div>';
        echo '<ul>';
        echo '<li>Item Number: ' . ($itemNumber ?: 'Not provided') . '</li>';
        echo '<li>Item Name: ' . ($itemName ?: 'Not provided') . '</li>';
        echo '<li>Quantity: ' . ($quantity !== null ? $quantity : 'Not provided') . '</li>';
        echo '</ul>';
        exit();
    }
}
?>
