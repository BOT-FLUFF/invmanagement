<?php
require_once('../../inc/config/constants.php');
require_once('../../inc/config/db.php');

if (isset($_POST['saleDetailsSaleID'])) {

    $saleDetailsItemNumber = htmlentities($_POST['saleDetailsItemNumber']);
    $saleDetailsSaleDate = htmlentities($_POST['saleDetailsSaleDate']);
    $saleDetailsItemName = htmlentities($_POST['saleDetailsItemName']);
    $saleDetailsQuantity = htmlentities($_POST['saleDetailsQuantity']);
    $saleDetailsUnitPrice = htmlentities($_POST['saleDetailsUnitPrice']);
    $saleDetailsSaleID = htmlentities($_POST['saleDetailsSaleID']);
    $saleDetailsCustomerName = htmlentities($_POST['saleDetailsCustomerName']);
    $saleDetailsDiscount = htmlentities($_POST['saleDetailsDiscount']);
    $saleDetailsCustomerID = htmlentities($_POST['saleDetailsCustomerID']);
    $itemMan = htmlentities($_POST['itemDetailsItemManufacturing']);
    $itemExp = htmlentities($_POST['itemDetailsItemExpiration']);

    $quantityInOriginalOrder = 0;
    $quantityInNewOrder = 0;
    $originalStockInItemTable = 0;
    $newStock = 0;

    // Check if mandatory fields are not empty
    if (
        isset($saleDetailsItemNumber) && isset($saleDetailsSaleDate) &&
        isset($saleDetailsQuantity) && isset($saleDetailsUnitPrice) &&
        isset($saleDetailsCustomerID) && !empty($itemMan) && !empty($itemExp)
    ) {

        // Validate manufacturing and expiration dates
        $dateRegex = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($dateRegex, $itemMan) || !preg_match($dateRegex, $itemExp)) {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter valid dates in YYYY-MM-DD format for manufacturing and expiration dates.</div>';
            exit();
        }

        // Sanitize item number
        $saleDetailsItemNumber = filter_var($saleDetailsItemNumber, FILTER_SANITIZE_STRING);

        // Validate item quantity
        if (filter_var($saleDetailsQuantity, FILTER_VALIDATE_INT) === 0 || filter_var($saleDetailsQuantity, FILTER_VALIDATE_INT)) {
            // Valid quantity
        } else {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a valid number for Quantity.</div>';
            exit();
        }

        // Validate unit price
        if (filter_var($saleDetailsUnitPrice, FILTER_VALIDATE_FLOAT) === 0.0 || filter_var($saleDetailsUnitPrice, FILTER_VALIDATE_FLOAT)) {
            // Valid unit price
        } else {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a valid number for Unit Price.</div>';
            exit();
        }

        // Validate discount
        if ($saleDetailsDiscount !== '') {
            if (filter_var($saleDetailsDiscount, FILTER_VALIDATE_FLOAT) === 0.0 || filter_var($saleDetailsDiscount, FILTER_VALIDATE_FLOAT)) {
                // Valid discount
            } else {
                echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a valid number for Discount.</div>';
                exit();
            }
        }

        // Get original sale details
        $orginalSaleQuantitySql = 'SELECT * FROM sale WHERE saleID = :saleID';
        $originalSaleQuantityStatement = $conn->prepare($orginalSaleQuantitySql);
        $originalSaleQuantityStatement->execute(['saleID' => $saleDetailsSaleID]);

        // Check if sale exists
        if ($originalSaleQuantityStatement->rowCount() > 0) {
            $originalQtyRow = $originalSaleQuantityStatement->fetch(PDO::FETCH_ASSOC);
            $quantityInOriginalOrder = $originalQtyRow['quantity'];
            $originalOrderItemNumber = $originalQtyRow['itemNumber'];

            // Check if item exists in item table
            $stockSql = 'SELECT * FROM item WHERE itemNumber=:itemNumber';
            $stockStatement = $conn->prepare($stockSql);
            $stockStatement->execute(['itemNumber' => $saleDetailsItemNumber]);

            if ($stockStatement->rowCount() > 0) {
                $stockRow = $stockStatement->fetch(PDO::FETCH_ASSOC);
                $originalStockInItemTable = $stockRow['stock'];
                $quantityInNewOrder = $saleDetailsQuantity;
                $newStock = $originalStockInItemTable - ($quantityInNewOrder - $quantityInOriginalOrder);

                // Update the stock and dates in item table
                $updateStockSql = 'UPDATE item SET stock = :stock, itemMan = :itemMan, itemExp = :itemExp WHERE itemNumber = :itemNumber';
                $updateStockStatement = $conn->prepare($updateStockSql);
                $updateStockStatement->execute([
                    'stock' => $newStock,
                    'itemMan' => $itemMan,
                    'itemExp' => $itemExp,
                    'itemNumber' => $saleDetailsItemNumber
                ]);

                // Update the sale table
                $updateSaleDetailsSql = 'UPDATE sale SET itemNumber = :itemNumber, saleDate = :saleDate, itemName = :itemName, unitPrice = :unitPrice, discount = :discount, quantity = :quantity, customerName = :customerName, customerID = :customerID WHERE saleID = :saleID';
                $updateSaleDetailsStatement = $conn->prepare($updateSaleDetailsSql);
                $updateSaleDetailsStatement->execute([
                    'itemNumber' => $saleDetailsItemNumber,
                    'saleDate' => $saleDetailsSaleDate,
                    'itemName' => $saleDetailsItemName,
                    'unitPrice' => $saleDetailsUnitPrice,
                    'discount' => $saleDetailsDiscount,
                    'quantity' => $saleDetailsQuantity,
                    'customerName' => $saleDetailsCustomerName,
                    'customerID' => $saleDetailsCustomerID,
                    'saleID' => $saleDetailsSaleID
                ]);

                echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>Sale details updated successfully.</div>';
                exit();
            } else {
                echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Item does not exist in DB. Please add it first.</div>';
                exit();
            }
        } else {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Sale details do not exist for the given Sale ID. Update not possible.</div>';
            exit();
        }
    } else {
        echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please fill in all required fields marked with a (*).</div>';
        exit();
    }
}
?>
