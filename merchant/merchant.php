<?php 

// start session 
session_start(); 

// redirect if session does not exist
if (!isset($_SESSION["user"])) {
    header("Location: /agora/login/login.html");
}

// check if order is confirmed and shipped
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // get all data
    $order_id = $_POST["orderid"];
    $merc_id = $_POST["mercid"];
    $product_id = $_POST["prodid"];
    $totalprice = $_POST["totalprice"];
    $amount = $_POST["amount"];

    // connect to database
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

        // confirm order
        $stmt = $db->prepare("UPDATE orders SET completed = 1 WHERE id = :ordid");
        $stmt->bindParam(":ordid", $order_id);
        $stmt->execute(); 

        // add 90% price to merchant wallet
        $stmt = $db->prepare("UPDATE users SET funds = funds + :pay WHERE id = :merid");
        $curprice = $totalprice * 0.9;
        $stmt->bindParam(":pay", $curprice);
        $stmt->bindParam(":merid", $merc_id);
        $stmt->execute(); 

        // add 10% price to admin wallet
        $stmt = $db->prepare("UPDATE users SET funds = funds + :pay WHERE role = :admin");
        $admin = "admin";
        $curprice = $totalprice * 0.1;
        $stmt->bindParam(":pay", $curprice);
        $stmt->bindParam(":admin", $admin);
        $stmt->execute(); 

        // decrement stock on product
        $stmt = $db->prepare("UPDATE product SET stock = stock - :amnt WHERE id = :prodid");
        $stmt->bindParam(":prodid", $product_id);
        $stmt->bindParam(":amnt", $amount);
        $stmt->execute(); 

    } catch (PDOException $e) { 
        echo "Connection failed: " . $e->getMessage(); 
    } 


}


?> 

<!DOCTYPE html> 
<html> 
	<head> 
		<title>Merchant</title> 
		<link rel="stylesheet"
				href="../global.css"> 
		<link rel="stylesheet"
				href="style.css"> 
	</head> 
    <body> 
        <header>
            <h1 id="logo" class="poppins-bold">AGORA</h1>
            <div class="fakelogo"></div>
            <div class="user-info">
                <div class="wallet" onclick="viewwallet()">
                    <img class="icon" src="../customer/assets/wallet.png">
                </div>
                <div class="logout" onclick="logout()">
                    <img class="icon" src="../customer/assets/logout.png" alt="logout"> 
                </div>
            </div>
        </header>
        <div class="merchant">
            <div class="products">
                <h1 class="poppins-bold">Manage Products</h1>
                <div class="product-cards"> 


                    <?php
                        
                        // create database connection
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
                      
                            // fetch all products and create div
                            $stmt = $db->prepare("SELECT * FROM product WHERE user_id = :id"); 
                            $stmt->bindParam(":id", $userid);
                            $stmt->execute(); 
                            $products = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                            foreach ($products as $prod) {

                                echo "
                                    <div class='product'> 
                                        <img src='../customer/assets/products/" . $prod["image"] . "' alt='product_image'/>
                                        <p class='product-name poppins-regular'>" . $prod["name"] . "</p>
                                        <p class='product-price poppins-bold'> R " . $prod["price"] . "</p>
                                        <a class='poppins-semibold' href='/agora/merchant/editproduct.php?id=" . $prod["id"] . "'>EDIT</a>
                                    </div> 
                                ";
                            }
     
                        } catch (PDOException $e) { 
                            echo "Connection failed: " . $e->getMessage(); 
                        } 
                    ?>
                    <div class='newproduct' onclick="addProduct()">
                        <img src="../cart/assets/plus.png" alt='plus'>
                    </div>
                </div>
            </div>
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
                            $stmt = $db->prepare("SELECT o.*, p.name, p.price, p.user_id AS merc_id FROM orders o JOIN product p ON o.product_id = p.id WHERE p.user_id = :cur_id AND o.completed = 0");
                            $stmt->bindParam(":cur_id", $userid);
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
                                        <form method='POST' action='merchant.php'>
                                            <input type=hidden name='orderid' value='" . $order["id"] . "'>
                                            <input type=hidden name='amount' value='" . $order["quantity"] . "'>
                                            <input type=hidden name='totalprice' value='" . $order["quantity"] * $order["price"] . "'>
                                            <input type=hidden name='prodid' value='" . $order["product_id"] . "'>
                                            <input type=hidden name='mercid' value='" . $order["merc_id"] . "'>
                                            <input class='order-conf poppins-bold' type=submit value='CONFIRM & SHIP'>
                                        </form> 
                                    </div>
                                ";
    
                            }


                        } catch (PDOException $e) { 
                            echo "Connection failed: " . $e->getMessage(); 
                        } 


                    ?>
                </div>
            </div>

        </div>
        <script src="script.js"></script>
	</body> 
</html>
