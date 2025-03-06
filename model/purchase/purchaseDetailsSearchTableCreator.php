<?php
require_once('../../inc/config/constants.php');
require_once('../../inc/config/db.php');

$uPrice = 0;
$qty = 0;
$totalPrice = 0;

$purchaseDetailsSearchSql = 'SELECT * FROM purchase';
$purchaseDetailsSearchStatement = $conn->prepare($purchaseDetailsSearchSql);
$purchaseDetailsSearchStatement->execute();

$output = '<table id="purchaseDetailsTable" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Purchase ID</th>
                    <th>Generic Name</th>
                    <th>Purchase Date</th>
                    <th>Medicine Name</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Vendor Name</th>
                    <th>Vendor ID</th>
                    <th>Total Price</th>
                    <th>Manufacturing Date</th>
                    <th>Expiration Date</th>
                </tr>
            </thead>
            <tbody>';

// Create table rows from the selected data
while ($row = $purchaseDetailsSearchStatement->fetch(PDO::FETCH_ASSOC)) {
    $uPrice = $row['unitPrice'];
    $qty = $row['quantity'];
    $totalPrice = $uPrice * $qty;

    $output .= '<tr>' .
                   '<td>' . $row['purchaseID'] . '</td>' .
                   '<td>' . $row['itemNumber'] . '</td>' .
                   '<td>' . $row['purchaseDate'] . '</td>' .
                   '<td>' . $row['itemName'] . '</td>' .
                   '<td>' . $row['unitPrice'] . '</td>' .
                   '<td>' . $row['quantity'] . '</td>' .
                   '<td>' . $row['vendorName'] . '</td>' .
                   '<td>' . $row['vendorID'] . '</td>' .
                   '<td>' . $totalPrice . '</td>' .
                   '<td>' . $row['itemMan'] . '</td>' . // Manufacturing Date
                   '<td>' . $row['itemExp'] . '</td>' . // Expiration Date
               '</tr>';
}

$purchaseDetailsSearchStatement->closeCursor();

$output .= '</tbody>
             <tfoot>
                <tr>
                    <th>Purchase ID</th>
                    <th>Generic Name</th>
                    <th>Purchase Date</th>
                    <th>Medicine Name</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Vendor Name</th>
                    <th>Vendor ID</th>
                    <th>Total Price</th>
                    <th>Manufacturing Date</th>
                    <th>Expiration Date</th>
                </tr>
             </tfoot>
         </table>';

echo $output;
?>
