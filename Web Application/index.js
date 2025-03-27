// Every 3 seconds, retrieve the list of the products added to the cart from the database table to inform the customer concurrently via the product list (payment) page.
setInterval(function(){
	$.ajax({
		url: "./assets/update_list.php",
		type: "GET",
		success: (response) => {
			$(".container table").html(response);
		}
	});
}, 3000);
