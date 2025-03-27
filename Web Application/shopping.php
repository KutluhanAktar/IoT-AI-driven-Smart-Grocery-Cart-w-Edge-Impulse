<?php

include_once "assets/class.php";

// Define the new 'customer' object:
$customer = new _main();
$customer->__init__($conn);

// Create a new database table for the customer and insert the given customer information.
if(isset($_GET['table']) && isset($_GET['firstname']) && isset($_GET['lastname']) && isset($_GET['card_info']) && isset($_GET['email'])){
	$customer->database_create_customer_table($_GET['table'], $_GET['firstname'], $_GET['lastname'], $_GET['card_info'], $_GET['email']);
}

// Obtain the transferred product information from the Beetle ESP32-C3.
// Then, insert the received product data into the customer's database table.
if(isset($_GET['add']) && isset($_GET['table']) && isset($_GET['product_id']) && isset($_GET['product_name']) && isset($_GET['product_price'])){
	if($customer->insert_new_data($_GET['table'], $_GET['product_id'], $_GET['product_name'], $_GET['product_price'])){
		echo("Product information saved successfully to the customer's database table!");
	}else{
		echo("Database error!");
	}
}

// Delete the product information from the database table, that the customer removed from the cart.
if(isset($_GET['remove']) && isset($_GET['table']) && isset($_GET['product_id'])){
	if($customer->remove_data($_GET['table'], $_GET['product_id'])){
		echo("Product information removed successfully from the customer's database table!");
	}else{
		echo("Database error!");
	}
}

// Send an HTML email to the customer's registered email address via the SendGrid Email PHP API, including
// the list of the products added to the cart and the link to the payment page. 
if(isset($_GET['table']) && isset($_GET['send_email']) && isset($_GET['tag'])){
	$customer->send_product_list_email($_GET['table'], $_GET['tag']);
}

// Get the latest registered table name from the database.
if(isset($_GET["deviceChangeTable"])){
	$customer->get_table_name(false);
}

?>