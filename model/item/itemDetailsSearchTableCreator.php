<?php
require_once('../../inc/config/constants.php');
require_once('../../inc/config/db.php');

$itemDetailsSearchSql = 'SELECT * FROM item';
$itemDetailsSearchStatement = $conn->prepare($itemDetailsSearchSql);
$itemDetailsSearchStatement->execute();

$output = '<table id="itemDetailsTable" class="table table-sm table-striped table-bordered table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Item Number</th>
                    <th>Item Name</th>
                    <th>Stock</th>
                    <th>Unit Price</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Manufacturing Date</th>
                    <th>Expiration Date</th>
                </tr>
            </thead>
            <tbody>';

// Create table rows from the selected data
while ($row = $itemDetailsSearchStatement->fetch(PDO::FETCH_ASSOC)) {
    $output .= '<tr>' .
                   '<td>' . $row['productID'] . '</td>' .
                   '<td>' . $row['itemNumber'] . '</td>' .
                   '<td><a href="#" class="itemDetailsHover" data-toggle="popover" id="' . $row['productID'] . '">' . $row['itemName'] . '</a></td>' .
                   '<td>' . $row['stock'] . '</td>' .
                   '<td>' . $row['unitPrice'] . '</td>' .
                   '<td>' . $row['status'] . '</td>' .
                   '<td>' . $row['description'] . '</td>' .
                   '<td>' . $row['itemMan'] . '</td>' . // Manufacturing Date
                   '<td>' . $row['itemExp'] . '</td>' . // Expiration Date
               '</tr>';
}

$itemDetailsSearchStatement->closeCursor();

$output .= '</tbody>
             <tfoot>
                <tr>
                    <th>Product ID</th>
                    <th>Item Number</th>
                    <th>Item Name</th>
                    <th>Stock</th>
                    <th>Unit Price</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Manufacturing Date</th>
                    <th>Expiration Date</th>
                </tr>
             </tfoot>
         </table>';

echo $output;
?>
