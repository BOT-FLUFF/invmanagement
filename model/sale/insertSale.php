<?php
require_once('../../inc/config/constants.php');
require_once('../../inc/config/db.php');

if (isset($_POST['saleDetailsItemNumber'])) {

    $itemNumber = htmlentities($_POST['saleDetailsItemNumber']);
    $itemName = htmlentities($_POST['saleDetailsItemName']);
    $discount = htmlentities($_POST['saleDetailsDiscount']);
    $quantity = htmlentities($_POST['saleDetailsQuantity']);
    $unitPrice = htmlentities($_POST['saleDetailsUnitPrice']);
    $customerID = htmlentities($_POST['saleDetailsCustomerID']);
    $customerName = htmlentities($_POST['saleDetailsCustomerName']);
    $saleDate = htmlentities($_POST['saleDetailsSaleDate']);

    // Check if mandatory fields are not empty
    if (!empty($itemNumber) && isset($customerID) && isset($saleDate) && isset($quantity) && isset($unitPrice)) {

        // Sanitize item number
        $itemNumber = filter_var($itemNumber, FILTER_SANITIZE_STRING);

        // Validate item quantity
        if (filter_var($quantity, FILTER_VALIDATE_INT) === 0 || filter_var($quantity, FILTER_VALIDATE_INT)) {
            // Valid quantity
        } else {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a valid number for quantity</div>';
            exit();
        }

        // Check if customerID is empty
        if ($customerID == '') {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a Customer ID.</div>';
            exit();
        }

        // Validate customerID
        if (filter_var($customerID, FILTER_VALIDATE_INT) === 0 || filter_var($customerID, FILTER_VALIDATE_INT)) {
            // Valid customerID
        } else {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a valid Customer ID</div>';
            exit();
        }

        // Check if itemNumber is empty
        if ($itemNumber == '') {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter Item Number.</div>';
            exit();
        }

        // Check if unit price is empty
        if ($unitPrice == '') {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter Unit Price.</div>';
            exit();
        }

        // Validate unit price
        if (filter_var($unitPrice, FILTER_VALIDATE_FLOAT) === 0.0 || filter_var($unitPrice, FILTER_VALIDATE_FLOAT)) {
            // Valid float (unit price)
        } else {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a valid number for unit price</div>';
            exit();
        }

        // Validate discount only if it's provided
        if (!empty($discount)) {
            if (filter_var($discount, FILTER_VALIDATE_FLOAT) === false) {
                echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter a valid discount amount</div>';
                exit();
            }
        }

        // Calculate the stock values and validate manufacturing/expiration dates
        $stockSql = 'SELECT stock, itemMan, itemExp FROM item WHERE itemNumber = :itemNumber';
        $stockStatement = $conn->prepare($stockSql);
        $stockStatement->execute(['itemNumber' => $itemNumber]);

        if ($stockStatement->rowCount() > 0) {
            $row = $stockStatement->fetch(PDO::FETCH_ASSOC);
            $currentQuantityInItemsTable = $row['stock'];
            $itemMan = $row['itemMan'];
            $itemExp = $row['itemExp'];

            // Validate dates
            if ($saleDate < $itemMan) {
                echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Sale date cannot be earlier than the manufacturing date (' . $itemMan . ').</div>';
                exit();
            }

            if ($saleDate > $itemExp) {
                echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Sale date cannot be later than the expiration date (' . $itemExp . ').</div>';
                exit();
            }

            if ($currentQuantityInItemsTable <= 0) {
                echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Stock is empty. Cannot proceed with the sale.</div>';
                exit();
            } elseif ($currentQuantityInItemsTable < $quantity) {
                echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Not enough stock available for this sale. Cannot proceed with the sale.</div>';
                exit();
            } else {
                $newQuantity = $currentQuantityInItemsTable - $quantity;

                // Check if the customer is in DB
                $customerSql = 'SELECT * FROM customer WHERE customerID = :customerID';
                $customerStatement = $conn->prepare($customerSql);
                $customerStatement->execute(['customerID' => $customerID]);

                if ($customerStatement->rowCount() > 0) {
                    $customerRow = $customerStatement->fetch(PDO::FETCH_ASSOC);
                    $customerName = $customerRow['fullName'];

                    // INSERT data into the sale table
                    $insertSaleSql = 'INSERT INTO sale(itemNumber, itemName, discount, quantity, unitPrice, customerID, customerName, saleDate) 
                                      VALUES(:itemNumber, :itemName, :discount, :quantity, :unitPrice, :customerID, :customerName, :saleDate)';
                    $insertSaleStatement = $conn->prepare($insertSaleSql);
                    $insertSaleStatement->execute([
                        'itemNumber' => $itemNumber,
                        'itemName' => $itemName,
                        'discount' => $discount,
                        'quantity' => $quantity,
                        'unitPrice' => $unitPrice,
                        'customerID' => $customerID,
                        'customerName' => $customerName,
                        'saleDate' => $saleDate
                    ]);

                    // UPDATE the stock in item table
                    $stockUpdateSql = 'UPDATE item SET stock = :stock WHERE itemNumber = :itemNumber';
                    $stockUpdateStatement = $conn->prepare($stockUpdateSql);
                    $stockUpdateStatement->execute(['stock' => $newQuantity, 'itemNumber' => $itemNumber]);

                    echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>Sale details added to DB and stocks updated.</div>';
                    exit();
                } else {
                    echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Customer does not exist.</div>';
                    exit();
                }
            }
        } else {
            echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Item does not exist in DB.</div>';
            exit();
        }
    } else {
        echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Please enter all fields marked with a (*).</div>';
        exit();
    }
}
?>
