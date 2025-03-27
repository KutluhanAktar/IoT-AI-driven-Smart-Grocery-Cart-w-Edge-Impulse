<!DOCTYPE html>
<html>
<head>
<title>Smart Grocery Cart</title>

<!--link to index.css-->
<link rel="stylesheet" type="text/css" href="assets/index.css"></link>

<!--link to favicon-->
<link rel="icon" type="image/png" sizes="36x36" href="assets/icon.png">

<!-- link to FontAwesome-->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.2.1/css/all.css">
 
<!-- link to font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Dots&display=swap" rel="stylesheet">

</head>
<body>
<?php ini_set('display_errors',1);?> 
<h1><i class="fa-solid fa-cart-shopping"></i> Please create your account to continue shopping :)</h1>

<div class="container">
<form method="get" action="shopping.php">
<div><label>First name:</label><input name="firstname" placeholder="John"></input></div>
<div><label>Last name:</label><input name="lastname" placeholder="Doe"></input></div>
<div><label>Email:</label><input name="email" placeholder="johndoe@gmail.com"></input></div>
<div><label>Account name:</label><input name="table" placeholder="John_123"></input></div>
<div><label>Card number:</label><input name="card_info" placeholder="5236 8965 9824 5668"></input></div>
<button type="submit"><i class="fa-solid fa-user-check"></i> Create Account</button>
</form>
</div>
</body>
</html>