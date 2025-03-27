<!DOCTYPE html>
<html>
<head>
<title>Product List</title>

<style>
html{background-color:#25282A;font-family:'Zen Dots', cursive;}
h1{text-align:center;font-weight:bold;user-select:none;background:-webkit-linear-gradient(grey, white);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}

.container{position:relative;width:80%;background-image:linear-gradient(45deg, #1F2020, #A5282C);margin:auto;margin-top:30px;border:10px solid white;border-radius:20px;padding:15px;}
.container table{width:85%;color:white;margin:auto;border:3px solid white;user-select:none;}
.container th, .container td{border:3px solid white;color:#1F2020;}
.container th{background-color:#F3D060;}
.container td{color:white;}
.container button{display:block;background-image:linear-gradient(45deg, #1F2020, #5EB0E5);color:white;width:50%;height:auto;margin:auto;margin-top:50px;font-size:20px;border:5px solid white;border-radius:15px;font-weight:bold;}
.container button:hover{cursor:pointer;background-image:linear-gradient(45deg, #5EB0E5, #5EB0E5);}

</style>

<!--link to favicon-->
<link rel="icon" type="image/png" sizes="36x36" href="assets/icon.png">

<!-- link to FontAwesome-->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.2.1/css/all.css">
 
<!-- link to font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Dots&display=swap" rel="stylesheet">

<!--link to jQuery script-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

</head>
<body>
<?php ini_set('display_errors',1);?> 
<h1><i class="fa-solid fa-cart-arrow-down"></i> Product List</h1>

<div class="container">
<table>
<tr>
<th>Product Name</th>
<th>Product ID</th>
<th>Product Price</th>
</tr>
</table>
<button><i class="fa-solid fa-credit-card"></i> Purchase</button>
</div>

<!--Add the index.js file-->
<script type="text/javascript" src="assets/index.js"></script>

</body>
</html>