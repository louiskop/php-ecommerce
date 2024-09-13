<?php

// start session    
session_start();

// permission check 
if ($_SESSION["user"]["role"] != "customer") {
    header("Location: /agora/login/login.html");
}

?>


<!DOCTYPE html> 
<html> 
	<head> 
		<title>My Orders</title> 
		<link rel="stylesheet"
				href="../global.css"> 
		<link rel="stylesheet"
				href="style.css"> 
	</head> 
    <body> 
        <header>
            <a href="/agora/customer/customer.php"><h1 id="logo" class="poppins-bold">AGORA</h1></a>
            <div class="fakelogo"></div>
            <div class="user-info">
                <div class="wallet" onclick="viewwallet()">
                    <img class="icon" src="../customer/assets/wallet.png">
                </div>
                <div class="cart" onclick="viewcart()">
                    <img class="icon" src="../customer/assets/shopping-cart.png" alt="shopping-cart">
                </div>
                <div class="logout" onclick="logout()">
                    <img class="icon" src="../customer/assets/logout.png" alt="logout"> 
                </div>
            </div>
        </header>
        <div class="orders">
            <h1 class="poppins-bold">Manage Orders</h1>
            <div class="order-cards">
                <?php
                    
                    // get all orders to this merchant
                    $host = "localhost"; 
                    $dbname = "agora"; 
                    $username_db = "root"; 
                    $password_db = ""; 
                    $userid = $_SESSION["user"]["id"];

                    try { 
                        $db = new PDO( 
                            "mysql:host=$host;dbname=$dbname", 
                            $username_db, 
                            $password_db
                        ); 
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
                  
                        // fetch all orders and create divs
                        $stmt = $db->prepare("SELECT * FROM orders o JOIN product p ON o.product_id = p.id  WHERE o.user_id = :usid");
                        $stmt->bindParam(":usid", $userid);
                        $stmt->execute(); 
                        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC); 

                        foreach ($orders as $order) {
                            echo "
                                <div class='order'>
                                    <div class='order-info'>
                                        <h1 class='poppins-bold'>". $order["name"] . "</h1>
                                        <p class='poppins-regular'> Quantity: " . $order["quantity"].  "</p>
                                        <p class='poppins-semibold'> Total: R " . $order["price"] * $order["quantity"] . "</p>
                                    </div>
                                    <div>
                                        <p class='poppins-bold indicator'>" . ($order["completed"] == 1 ? "Shipped" : "Pending") . "</p>
                                    </div> 
                                </div>
                            ";

                        }


                    } catch (PDOException $e) { 
                        echo "Connection failed: " . $e->getMessage(); 
                    } 


                ?>
            </div>
        </div>
        <script src="script.js"></script>
	</body> 
</html>
