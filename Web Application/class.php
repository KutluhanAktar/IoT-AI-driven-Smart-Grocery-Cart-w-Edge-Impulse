<?php

// Include the Twilio SendGrid Email PHP API:
// https://github.com/sendgrid/sendgrid-php/releases
require("sendgrid-php/sendgrid-php.php");

// Define the _main class and its functions:
class _main {
	public $conn;
	
	private $sendgrid_API_Key = "<_API_KEY_>";
	private $from_email = "<_FROM_EMAIL_>";
	
	public function __init__($conn){
		$this->conn = $conn;
	}
	
	// Database -> Create Table (New Customer)
	public function database_create_customer_table($table, $firstname, $lastname, $card_info, $email){
		// Create a new database table.
		$sql_create = "CREATE TABLE `$table`(		
							id int AUTO_INCREMENT PRIMARY KEY NOT NULL,
							firstname varchar(255) NOT NULL,
							lastname varchar(255) NOT NULL,
							card_info varchar(255) NOT NULL,
							email varchar(255) NOT NULL,
							product_id varchar(255) NOT NULL,
							product_name varchar(255) NOT NULL,
							product_price varchar(255) NOT NULL
					   );";
		if(mysqli_query($this->conn, $sql_create)){
			echo("<br>Database Table Created Successfully!");
			// Add the customer information to the recently created database table.
			$sql_insert = "INSERT INTO `$table`(`firstname`, `lastname`, `card_info`, `email`, `product_id`, `product_name`, `product_price`) 
						   VALUES ('$firstname', '$lastname', '$card_info', '$email', 'X', 'X', 'X')"
					      ;
			if(mysqli_query($this->conn, $sql_insert)) echo("<br><br>Customer information added to the database table successfully!");
			// If the customer information is added to the database table successfully, redirect the customer to the product list page.
            header("Location: product_list.php");
			exit();
		}else{
			// Redirect the customer to the home page if there is an error. 
			header("Location: ./?databaseTableAlreadyCreated");
			exit();
		}
	}
	
    // Database -> Insert Product Data:
	public function insert_new_data($table, $product_id, $product_name, $product_price){
		$sql_insert = "INSERT INTO `$table`(`firstname`, `lastname`, `card_info`, `email`, `product_id`, `product_name`, `product_price`) 
		               SELECT `firstname`, `lastname`, `card_info`, `email`, '$product_id', '$product_name', '$product_price'
				       FROM `$table` WHERE id=1"
			          ;
		if(mysqli_query($this->conn, $sql_insert)){ return true; } else{ return false; }
	}
	
	// Database -> Remove Product Data:
	public function remove_data($table, $product_id){
		$sql_delete = "DELETE FROM `$table` WHERE `product_id`='$product_id' limit 1";
		if(mysqli_query($this->conn, $sql_delete)){ return true; } else{ return false; }
	}
	
	// Retrieve the products added to the cart by the customer as a list.
	public function get_purchased_product_list($table){
		$product_names = []; $product_ids = []; $product_prices = [];
		$sql_list = "SELECT * FROM `$table` WHERE id!=1 ORDER BY `id` ASC";
		$result = mysqli_query($this->conn, $sql_list);
		$check = mysqli_num_rows($result);
		if($check > 0){
			while($row = mysqli_fetch_assoc($result)){
				array_push($product_names, $row["product_name"]);
				array_push($product_ids, $row["product_id"]);
				array_push($product_prices, $row["product_price"]);
			}
			return array($product_names, $product_ids, $product_prices);
		}else{
			return array(["Not Found!"], ["Not Found!"], ["Not Found!"]);
		}
	}
	
	// Obtain the latest registered customer's assigned table name from the database.
	public function get_table_name($return){
		$sql_get = "SELECT `table_name`, `create_time` FROM `information_schema`.`TABLES` WHERE `table_schema` = 'smart_grocery_cart' ORDER BY `CREATE_TIME` DESC limit 1";
		$result = mysqli_query($this->conn, $sql_get);
		$check = mysqli_num_rows($result);
		if($check > 0){
			while($row = mysqli_fetch_assoc($result)){
				if(!$return) echo("%".$row["table_name"]."%".$row["create_time"]."%");
				else return $row["table_name"];
			}
		}
	}
    
    // Obtain the email address of the customer from the database.
    private function get_email($table){
		$sql_email = "SELECT * FROM `$table` WHERE id=1";
		$result = mysqli_query($this->conn, $sql_email);
		$check = mysqli_num_rows($result);
		if($check > 0){
			if($row = mysqli_fetch_assoc($result)){ return $row["email"]; }
			else{ return "Not Found!"; }
		}
	}	
	
	// Send an email to the customer's registered email address, including the list of the products added to the cart and the link to the payment page.
	public function send_product_list_email($table, $tag){
		// Get the customer's email address.
		$to_email = $this->get_email($table);
		// Obtain the list of the products added to the cart from the customer's database table.
	    $product_names = []; $product_ids = []; $product_prices = [];
	    list($product_names, $product_ids, $product_prices) = $this->get_purchased_product_list($_GET['table']);
		$list = "";
		for($i=0; $i<count($product_names); $i++){
			$list .= '<tr>
						<td>'.$product_names[$i].'</td>
						<td>'.$product_ids[$i].'</td>
						<td>'.$product_prices[$i].'</td>
					  </tr>
					 ';   
		}	
		// Send an HTML email via the SendGrid Email PHP API.
		$email = new \SendGrid\Mail\Mail(); 
		$email->setFrom($this->from_email, "Smart Grocery Cart");
		$email->setSubject("Cart Product List");
		$email->addTo($to_email, "Customer");
		$email->addContent("text/html",
		                   '<!DOCTYPE html>
							<html>
							<head>
							<style>
								h1{text-align:center;font-weight:bold;color:#25282A;}
								table{background-color:#043458;width:100%;border:10px solid #25282A;}
								th{background-color:#D1CDDA;color:#25282A;border:5px solid #25282A;font-size:25px;font-weight:bold;}
								td{color:#AEE1CD;border:5px solid #25282A;text-align:left;font-size:20px;font-weight:bold;}
                                a{text-decoration:none;background-color:#5EB0E5;}
							</style>
							</head>
							<body>
						    <h1>Thanks for shopping at our store :)</h1>
						    <h1>Your Customer Tag: '.$tag.'</h1>
						    <table>
						     <tr>
						      <th>Product Name</th>
						      <th>Product ID</th>
						      <th>Product Price</th>
						     </tr>
							 '.$list.'
							 </table>
							 <a href="http://localhost/smart_grocery_cart/product_list.php"><h1>🛒 Checkout 🛒</h1></a>
							 </body>
							 </html>
						   '
                          );
			 
		$sendgrid = new \SendGrid($this->sendgrid_API_Key);
		try{
			$response = $sendgrid->send($email);
			print $response->statusCode() . "\n";
			print_r($response->headers());
			print $response->body() . "\n";
		}catch(Exception $e){
			echo 'Caught exception: '. $e->getMessage() ."\n";
		}
	} 
}

// Define database and server settings:
$server = array(
	"name" => "localhost",
	"username" => "root",
	"password" => "",
	"database" => "smart_grocery_cart"
);

$conn = mysqli_connect($server["name"], $server["username"], $server["password"], $server["database"]);

?>