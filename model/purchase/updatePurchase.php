<?php

// Updated script - 2023-12-03

require_once('../../inc/config/constants.php');
require_once('../../inc/config/db.php');

if (isset($_POST['purchaseDetailsPurchaseID'])) {

    $purchaseDetailsItemNumber = htmlentities($_POST['purchaseDetailsItemNumber']);
    $purchaseDetailsPurchaseDate = htmlentities($_POST['purchaseDetailsPurchaseDate']);
    $purchaseDetailsItemName = htmlentities($_POST['purchaseDetailsItemName']);
    $purchaseDetailsQuantity = htmlentities($_POST['purchaseDetailsQuantity']);
    $purchaseDetailsUnitPrice = htmlentities($_POST['purchaseDetailsUnitPrice']);
    $purchaseDetailsPurchaseID = htmlentities($_POST['purchaseDetailsPurchaseID']);
    $purchaseDetailsVendorName = htmlentities($_POST['purchaseDetailsVendorName']);
    $itemMan = htmlentities($_POST['itemDetailsItemManufacturing']);
    $itemExp = htmlentities($_POST['itemDetailsItemExpiration']);

    $quantityInOriginalOrder = 0;
    $quantityInNewOrder = 0;
    $originalStockInItemTable = 0;
    $newStock = 0;
    $originalPurchaseItemNumber = '';

    // Check if mandatory fields are not empty
    if (
        isset($purchaseDetailsItemNumber) && isset($purchaseDetailsPurchaseDate) &&
        isset($purchaseDetailsQuantity) && isset($purchaseDetailsUnitPrice) &&
        !empty($itemMan) && !empty($itemExp)
    ) {

        // Validate dates
        $dateRegex = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($dateRegex, $itemMan) || !preg_match($dateRegex, $itemExp)) {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter valid dates in YYYY-MM-DD format for manufacturing and expiration dates.</div>';
            exit();
        }

        // Sanitize item number
        $purchaseDetailsItemNumber = filter_var($purchaseDetailsItemNumber, FILTER_SANITIZE_STRING);

        // Validate item quantity
        if (filter_var($purchaseDetailsQuantity, FILTER_VALIDATE_INT) === 0 || filter_var($purchaseDetailsQuantity, FILTER_VALIDATE_INT)) {
            // Valid quantity
        } else {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a valid number for quantity.</div>';
            exit();
        }

        // Validate unit price
        if (filter_var($purchaseDetailsUnitPrice, FILTER_VALIDATE_FLOAT) === 0.0 || filter_var($purchaseDetailsUnitPrice, FILTER_VALIDATE_FLOAT)) {
            // Valid unit price
        } else {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a valid number for unit price.</div>';
            exit();
        }

        // Check if purchaseID exists in the purchase table
        $originalPurchaseQuantitySql = 'SELECT * FROM purchase WHERE purchaseID = :purchaseID';
        $originalPurchaseQuantityStatement = $conn->prepare($originalPurchaseQuantitySql);
        $originalPurchaseQuantityStatement->execute(['purchaseID' => $purchaseDetailsPurchaseID]);

        if ($originalPurchaseQuantityStatement->rowCount() > 0) {

            // Fetch original purchase details
            $originalQtyRow = $originalPurchaseQuantityStatement->fetch(PDO::FETCH_ASSOC);
            $quantityInOriginalOrder = $originalQtyRow['quantity'];
            $originalOrderItemNumber = $originalQtyRow['itemNumber'];

            // Get the vendorId for the given vendorName
            $vendorIDsql = 'SELECT * FROM vendor WHERE fullName = :fullName';
            $vendorIDStatement = $conn->prepare($vendorIDsql);
            $vendorIDStatement->execute(['fullName' => $purchaseDetailsVendorName]);
            $vendorRow = $vendorIDStatement->fetch(PDO::FETCH_ASSOC);
            $vendorID = $vendorRow['vendorID'];

            // Check if the item exists in item table
            $stockSql = 'SELECT * FROM item WHERE itemNumber=:itemNumber';
            $stockStatement = $conn->prepare($stockSql);
            $stockStatement->execute(['itemNumber' => $purchaseDetailsItemNumber]);

            if ($stockStatement->rowCount() > 0) {

                // Calculate the new stock value
                $stockRow = $stockStatement->fetch(PDO::FETCH_ASSOC);
                $originalStockInItemTable = $stockRow['stock'];
                $quantityInNewOrder = $purchaseDetailsQuantity;
                $newStock = $originalStockInItemTable + ($quantityInNewOrder - $quantityInOriginalOrder);

                // Update the item table with new stock, manufacturing, and expiration dates
                $updateStockSql = 'UPDATE item SET stock = :stock, itemMan = :itemMan, itemExp = :itemExp WHERE itemNumber = :itemNumber';
                $updateStockStatement = $conn->prepare($updateStockSql);
                $updateStockStatement->execute([
                    'stock' => $newStock,
                    'itemMan' => $itemMan,
                    'itemExp' => $itemExp,
                    'itemNumber' => $purchaseDetailsItemNumber
                ]);

                // Update the purchase table
                $updatePurchaseDetailsSql = 'UPDATE purchase SET purchaseDate = :purchaseDate, itemName = :itemName, unitPrice = :unitPrice, quantity = :quantity, vendorName = :vendorName, vendorID = :vendorID, itemMan = :itemMan, itemExp = :itemExp WHERE purchaseID = :purchaseID';
                $updatePurchaseDetailsStatement = $conn->prepare($updatePurchaseDetailsSql);
                $updatePurchaseDetailsStatement->execute([
                    'purchaseDate' => $purchaseDetailsPurchaseDate,
                    'itemName' => $purchaseDetailsItemName,
                    'unitPrice' => $purchaseDetailsUnitPrice,
                    'quantity' => $purchaseDetailsQuantity,
                    'vendorName' => $purchaseDetailsVendorName,
                    'vendorID' => $vendorID,
                    'itemMan' => $itemMan,
                    'itemExp' => $itemExp,
                    'purchaseID' => $purchaseDetailsPurchaseID
                ]);

                echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>Purchase details updated in the database and stock values adjusted.</div>';
                exit();
            } else {
                echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Item does not exist in DB. Please add it to the DB first.</div>';
                exit();
            }
        } else {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Purchase ID does not exist in the database. Update not possible.</div>';
            exit();
        }
    } else {
        echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please fill in all required fields marked with a (*).</div>';
        exit();
    }
}
?>
