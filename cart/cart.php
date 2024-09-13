
<?php

// start session 
session_start(); 

// handle checkout or cart change
if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
    
    // connect to db
    $host = "localhost"; 
    $dbname = "agora"; 
    $username_db = "root"; 
    $password_db = ""; 
    $userId = $_SESSION["user"]["id"]; 
  
    try { 
        $db = new PDO( 
            "mysql:host=$host;dbname=$dbname", 
            $username_db, 
            $password_db
        ); 
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

        // add another to cart
        if (isset($_POST['inc'])) {
            $stmt = $db->prepare("INSERT INTO cart (user_id, product_id) VALUES (:id, :prodid)");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':prodid', $_POST['inc'], PDO::PARAM_INT);
            $stmt->execute();
            
        }

        // delete one of these from cart
        else if (isset($_POST['dec'])) {

            $stmt = $db->prepare("DELETE FROM cart WHERE user_id = :id AND product_id = :prodid LIMIT 1");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':prodid', $_POST['dec'], PDO::PARAM_INT);
            $stmt->execute();

        } else {


            // get amount to be paid
            $paid = $_POST["total"];

            // get all items in cart with quantities
            $stmt = $db->prepare("
                SELECT p.*, COUNT(c.product_id) AS quantity
                FROM cart c
                INNER JOIN product p ON c.product_id = p.id
                WHERE c.user_id = :user_id
                GROUP BY c.product_id
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // add each item to the orders table
            foreach ($products as $product) {
                $stmt = $db->prepare("INSERT INTO orders (product_id, user_id, quantity) VALUES (:prodid, :userid, :quant)");
                $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':prodid', $product["id"], PDO::PARAM_INT);
                $stmt->bindParam(':quant', $product["quantity"], PDO::PARAM_INT);
                $stmt->execute();
            }

            // empty cart completely
            $stmt = $db->prepare("DELETE FROM cart WHERE user_id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            // decrement user balance
            $stmt = $db->prepare("UPDATE users SET funds = funds - :total WHERE id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':total', $paid, PDO::PARAM_INT);
            $stmt->execute();

            // redirect to store home
            header("Location: /agora/customer/customer.php"); 

        }
    } catch (PDOException $e) { 
        echo "Connection failed: " . $e->getMessage(); 
    } 
}

?>

<!DOCTYPE html> 
<html> 
  
<head> 
    <title>Cart</title> 
    <link rel="stylesheet" 
          type="text/css" 
          href="../global.css"> 
    <link rel="stylesheet" 
          type="text/css" 
          href="./style.css"> 
</head> 
  
<body> 
    <div class="container"> 
        <h1 id="logo" class="poppins-bold">AGORA</h1>
        <div class="back poppins-bold">
            <a href="/agora/customer/customer.php">BACK TO STORE</a>
        </div>
        <div class="modal poppins-bold">
            
            <h1>Cart</h1>            
            <div class="cart">
                
                <?php
                    
                    // create database connection
                    $host = "localhost"; 
                    $dbname = "agora"; 
                    $username_db = "root"; 
                    $password_db = ""; 
                    $userId = $_SESSION["user"]["id"]; 
                  
                    try { 
                        $db = new PDO( 
                            "mysql:host=$host;dbname=$dbname", 
                            $username_db, 
                            $password_db
                        ); 
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

                        // prepare and execute the query
                        $stmt = $db->prepare("
                            SELECT p.*, COUNT(c.product_id) AS quantity
                            FROM cart c
                            INNER JOIN product p ON c.product_id = p.id
                            WHERE c.user_id = :user_id
                            GROUP BY c.product_id
                        ");
                        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                        $stmt->execute();

                        // fetch all the results
                        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // calculate total
                        $total = 0;

                        // output the results
                        foreach ($products as $product) {

                            $total = $total + ($product["price"] * $product["quantity"]);                               

                            echo "
                                <div class='cart-item'>
                                    <img class='cart-img' src='../customer/assets/products/" . $product["image"] . "' alt='cart-item'/>
                                    <div class='cart-item-info'>
                                        <h1>" . $product["name"] . "</h1>
                                        <h1>R " . $product["price"] . "</h1>
                                        <p class='poppins-regular'>Quantity: " . $product["quantity"] . "</p>
                                        <div class='item-total'>
                                            <div class='edit'>
                                                <form method='POST' action='cart.php'>
                                                    <input type='hidden' name='dec' value='" . $product["id"] . "'>
                                                    <input type=image src='./assets/minus.png' class='icon' alt='a'>
                                                </form>
                                                <form method='POST' action='cart.php'>
                                                    <input type='hidden' name='inc' value='" . $product["id"] . "'>
                                                    <input type=image src='./assets/plus.png' class='icon' alt='a'>
                                                </form>
                                            </div>
                                            <p>Total: R " . $product["quantity"] * $product["price"] . "</p>
                                        </div>
                                    </div>
                                </div>
                                

                            ";

                        }

                        echo "
                            </div>
                            <div class='actions poppins-bold'>
                                <h1>Cart Total: R " . $total . "</h1>
                                <form method='POST' action='cart.php'>
                                    <input type='hidden' name='total' value='" . $total . "'>
                                    <input type=submit value='CHECKOUT'> 
                                </form>
                            </div>
                        ";


                    } catch (PDOException $e) { 
                        echo "Connection failed: " . $e->getMessage(); 
                    } 
                    
                ?>

        </div>

    </div> 
    <script src="script.js"></script> 
</body> 
  
</html>
