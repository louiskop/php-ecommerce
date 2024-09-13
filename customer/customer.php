<?php 

// start session 
session_start(); 

// init filter
$filter = "ALL";

// redirect if session does not exist
if (!isset($_SESSION["user"])) {
    header("Location: /agora/login/login.html");
}

// handle product searching
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search_term"])) {

    $search_term = $_POST["search_term"]; 
    $search_term = '%' . $search_term . '%';
}

// handle filters
else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["filter"])) {
    
    $filter = $_POST["filter"];


    if ($filter == "CLOTHING") {
        $filter_num = 1;
    } else if ($filter == "SHOES") {
        $filter_num = 2;
    } else if ($filter == "ACCESSORIES") {
        $filter_num = 3; 
    }

}

// check if the add to cart button is clicked 
else if ($_SERVER["REQUEST_METHOD"] == "POST") { 
	
	// Get the product ID from the form 
	$product_id = $_POST["product_id"]; 
    
    // add to cart in database 
    $host = "localhost"; 
    $dbname = "agora"; 
    $username_db = "root"; 
    $password_db = ""; 
  
    try { 
        $db = new PDO( 
            "mysql:host=$host;dbname=$dbname", 
            $username_db, 
            $password_db
        ); 
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
  
        // add item to cart 
        $stmt = $db->prepare("INSERT INTO cart (user_id, product_id) VALUES (:userid, :productid)"); 
        $stmt->bindParam(":userid", $_SESSION["user"]["id"]);
        $stmt->bindParam(":productid", $product_id);
        $stmt->execute(); 

    } catch (PDOException $e) { 
        echo "Connection failed: " . $e->getMessage(); 
    } 

} 
?> 

<!DOCTYPE html> 
<html> 
	<head> 
		<title>Agora</title> 
		<link rel="stylesheet"
				href="../global.css"> 
		<link rel="stylesheet"
				href="style.css"> 
	</head> 
    <body> 
        <header>
            <h1 id="logo" class="poppins-bold">AGORA</h1>
            <div class="fakelogo"></div>
            <form class="search" method="POST" action='customer.php'>
                <img src="assets/search.png" alt="search">
                <input type="text" placeholder="Search products" name="search_term" >
            </form>
            <div class="user-info">
                <div class="wallet" onclick="viewwallet()">
                    <img class="icon" src="assets/wallet.png">
                </div>
                <div class="cart" onclick="viewcart()">
                    <img class="icon" src="assets/shopping-cart.png" alt="shopping-cart">
                </div>
                <div class="logout" onclick="logout()">
                    <img class="icon" src="assets/logout.png" alt="logout"> 
                </div>
            </div>
        </header>
        <div class="categories">
            <ul class="poppins-bold">
            <li class="category <?php echo ($filter == "ALL" ? "active-category" : ""); ?>" id="ALL">ALL</li>
                <?php
                    
                    // create database connection
                    $host = "localhost"; 
                    $dbname = "agora"; 
                    $username_db = "root"; 
                    $password_db = ""; 
                    $user_id = $_SESSION["user"]["id"];

                    try { 
                        $db = new PDO( 
                            "mysql:host=$host;dbname=$dbname", 
                            $username_db, 
                            $password_db
                        ); 
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
                  
                        // fetch all categories
                        $stmt = $db->prepare("SELECT * FROM category"); 
                        $stmt->execute(); 
                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                        foreach ($categories as $cat) {
                            echo "<li class='category ". ($filter == $cat["name"] ? "active-category" : "") ."' id=" . $cat["name"] . ">" . $cat["name"] . "</li>";
                        }

                        // fetch current user funds
                        $stmt = $db->prepare("SELECT funds FROM users WHERE id = :usrid");
                        $stmt->bindParam(":usrid", $user_id);
                        $stmt->execute();
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);                        
                        
                        echo "
                            <div class='balancediv'>
                                <p>R ".$user["funds"]."</p>

                                <div class='order' onclick='vieworders()'>
                                    <img class='icon' src='assets/order.png'>
                                </div>
                            </div> 
                        ";

 
                    } catch (PDOException $e) { 
                        echo "Connection failed: " . $e->getMessage(); 
                    } 
                ?>
                
            </ul> 
        </div>
        <div class="shop">
            <div class="products">
                <?php
                    
                    // create database connection
                    $host = "localhost"; 
                    $dbname = "agora"; 
                    $username_db = "root"; 
                    $password_db = ""; 
                  
                    try { 
                        $db = new PDO( 
                            "mysql:host=$host;dbname=$dbname", 
                            $username_db, 
                            $password_db
                        ); 
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
                  
                        // fetch all products and create div
                        if (isset($search_term)) {
                            $stmt = $db->prepare("SELECT * FROM product WHERE name LIKE :search");
                            $stmt->bindParam(":search", $search_term); 
                        } else if (isset($filter_num)) {
                            $stmt = $db->prepare("SELECT * FROM product WHERE category_id = :catid");
                            $stmt->bindParam(":catid", $filter_num); 
                        } 
                        else {
                            $stmt = $db->prepare("SELECT * FROM product"); 
                        }


                        $stmt->execute(); 
                        $products = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                        foreach ($products as $prod) {
                            echo "
                                <div class='product'> 
                                    <img src='assets/products/" . $prod["image"] . "' alt='product_image'/>
                                    <p class='product-name poppins-regular'>" . $prod["name"] . "</p>
                                    <p class='product-price poppins-bold'> R " . $prod["price"] . "</p>
                                    <form method='POST' action='customer.php'>
                                        <input type=hidden value='" . $prod["id"] . "' name='product_id'>
                                        <input id='" . $prod["id"] . "' type=submit value='ADD TO CART' class='add-to-cart poppins-semibold'>
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
        <script src="script.js"></script>
	</body> 
</html>
