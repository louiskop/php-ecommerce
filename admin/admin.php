<?php

// start the session
session_start();

// check user permissions
if ($_SESSION["user"]["role"] != "admin") {
    header("Location: /agora/login/login.html");
}

// handle user update
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // get all user fields
    $userid = $_POST["user_id"];
    $username = $_POST["username"];
    $role = $_POST["role"];
    $email = $_POST["email"];

    // connect to db
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

        // update user
        $stmt = $db->prepare("UPDATE users SET username = :username, email = :email, role = :role WHERE id = :user_id");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":user_id", $userid);
        $stmt->execute();

    } catch (PDOException $e) { 
        echo "Connection failed: " . $e->getMessage(); 
    } 


}


?>


<!DOCTYPE html> 
<html> 
  
<head> 
    <title>Admin</title> 
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
        <a href="/agora/login/logout.php"><img src = "../customer/assets/logout.png" class="logout"></a>

        <?php 
    
            // connect to db
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

                // get current admin balance
                $stmt = $db->prepare("SELECT funds FROM users WHERE id = :user_id");
                $stmt->bindParam(":user_id", $userid);
                $stmt->execute();
                $funds = $stmt->fetch(PDO::FETCH_ASSOC);

                echo "<h1 class='funds poppins-bold'> R " . $funds["funds"] . "</h1>";

            } catch (PDOException $e) { 
                echo "Connection failed: " . $e->getMessage(); 
            } 

        ?>

        <div class="main poppins-bold">

            <div class='manage-users'>
                <h1>Manage Users</h1>
                <p class='tagline poppins-regular'>Add, remove or edit users below</p>
                <div class='users'>
                    <?php

                        // get the whole users table    
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

                            // fetch all users
                            $stmt = $db->prepare("SELECT * FROM users");
                            $stmt->execute();
                            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            // display div for each user
                            foreach ($users as $usr) {

                                echo "
                                    <form class='user' method='POST' action='admin.php'>
                                        <label for='username' >Username:</label>
                                        <input name='username' type=text value='". $usr["username"] ."'>
                                        <label for='email' >Email:</label>
                                        <input name='email' type=email value='". $usr["email"] ."'>
                                        <label for='role' >Role:</label>
                                        <select name='role'>
                                            <option value='customer' " . ($usr["role"] == "customer" ? "selected": "") ." >customer</option>
                                            <option value='merchant' " . ($usr["role"] == "merchant" ? "selected": "") .">merchant</option>
                                            <option value='admin' " . ($usr["role"] == "admin" ? "selected": "") .">admin</option>
                                        </select>
                                        <input type='hidden' name='user_id' value='". $usr["id"] ."'>
                                        <input class='update poppins-bold' type=submit value='UPDATE'> 
                                        
                                    </form>

                                "; 

                            } 

                        } catch (PDOException $e) { 
                            echo "Connection failed: " . $e->getMessage(); 
                        } 
                    ?>
                </div>
                <h1 class='newhead'>Add a new User</h1>
                <form class='new-user' method="POST" action="../register/register.php">
                    <label for="username">Username:</label> 
                    <input type=text name='username'>
                    <label for="email">Email:</label>
                    <input type=text name='email'>
                    <label for="role">Role:</label>
                    <select name='role'>
                        <option value='customer'>customer</option>
                        <option value='merchant'>merchant</option>
                        <option value='admin'>admin</option>
                    </select>
                    <label for='password'>Password:</label>
                    <input type=password name='password'>
                    <input class="update poppins-bold" type=submit value="CREATE NEW USER">
                </form>
            </div>
            <div class='top-merchants'>
                <h1>Top Merchants</h1> 
                <p class='tagline poppins-regular'>View the best performing sellers</p> 
                <div class="merchants">
                    <?php
                        
                        // connect to db
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

                            // get all users in order 
                            $stmt = $db->prepare("SELECT u.username , COUNT(o.id) AS comp_orders FROM users u JOIN product p on u.id = p.user_id JOIN orders o ON p.id = o.product_id GROUP BY u.username ORDER BY comp_orders DESC");
                            $stmt->execute();
                            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);                            

                            // add div for each user
                            $standing = 1;
                            foreach ($users as $user) {

                                echo "

                                    <div class='ranked-user'>
                                        <h1>#".$standing."</h1>
                                        <p>".$user["username"]."</p>
                                        <p class='poppins-regular'>".$user["comp_orders"]." orders</p>
                                    </div>                        
            
                                ";
                                $standing = $standing + 1;

                            }

                        } catch (PDOException $e) { 
                            echo "Connection failed: " . $e->getMessage(); 
                        } 


                    ?>
                </div>
            </div>

        </div>


    </div> 
    <script src="script.js"></script> 
</body> 
  
</html>
