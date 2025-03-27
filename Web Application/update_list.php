<?php

include_once "class.php";

// Define the new 'customer' object:
$customer = new _main();
$customer->__init__($conn);

// Get the latest registered customer's table name.
$table = $customer->get_table_name(true);

// Obtain the list of the products added to the cart from the customer's database table.
$product_names = []; $product_ids = []; $product_prices = [];
list($product_names, $product_ids, $product_prices) = $customer->get_purchased_product_list($table);
$list = "<tr><th>Product Name</th><th>Product ID</th><th>Product Price</th></tr>";
for($i=0; $i<count($product_names); $i++){
	$list .= '<tr>
				<td>'.$product_names[$i].'</td>
				<td>'.$product_ids[$i].'</td>
				<td>'.$product_prices[$i].'</td>
			  </tr>
			 ';   
}	

// Return the generated product list.
echo($list);

?>