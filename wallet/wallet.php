<?php

// start session 
session_start(); 

if ($_SESSION["user"]["role"] == "merchant") {
    $prev = "/agora/merchant/merchant.php";
} else {
    $prev = "/agora/customer/customer.php";
}


// check if funds is made more or made less
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    // connect to the database
    $host = "localhost"; 
    $dbname = "agora"; 
    $username_db = "root"; 
    $password_db = ""; 
    $id = $_SESSION["user"]["id"];
  
    try { 
        $db = new PDO( 
            "mysql:host=$host;dbname=$dbname", 
            $username_db, 
            $password_db
        ); 
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
  
        // update funds
        if (isset($_POST["fundstoadd"])) {
            $funds = $_POST["fundstoadd"];
            $stmt = $db->prepare("UPDATE users SET funds = funds + :inc WHERE id = :id");
            $stmt->bindParam(":inc", $funds); 
            $stmt->bindParam(":id", $id); 
            $stmt->execute(); 

        } else {
            $funds = $_POST["fundstoremove"];

            $stmt = $db->prepare("UPDATE users SET funds = funds - :dec WHERE id = :id");
            $stmt->bindParam(":dec", $funds); 
            $stmt->bindParam(":id", $id); 
            $stmt->execute(); 

        }
        

    } catch (PDOException $e) { 
        echo "Connection failed: " . $e->getMessage(); 
    } 



}

?>

<!DOCTYPE html> 
<html> 
  
<head> 
    <title>Wallet</title> 
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
            <a href=<?php echo "'".$prev."'"?> >BACK TO STORE</a>
        </div>
        <div class="balance poppins-bold">
            <div class="greeting">
                <h1>Welcome, <?php echo $_SESSION["user"]["username"] ?></h1>
                <p id="tagline" class="poppins-regular">Update your balance here</p>
            </div>
            <div class="funds-container">
                <?php

                    // fetch funds from database 
                    $host = "localhost"; 
                    $dbname = "agora"; 
                    $username_db = "root"; 
                    $password_db = ""; 
                    $username = $_SESSION["user"]["username"];
                  
                    try { 
                        $db = new PDO( 
                            "mysql:host=$host;dbname=$dbname", 
                            $username_db, 
                            $password_db
                        ); 
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
                  
                        // check if the user exists in the database 
                        $stmt = $db->prepare("SELECT funds FROM users WHERE username = :username"); 
                        $stmt->bindParam(":username", $username); 
                        $stmt->execute(); 
                        $funds = $stmt->fetch(PDO::FETCH_ASSOC); 
                        echo "<h1 class='funds'> R " . $funds["funds"] . " </h1>";

                    } catch (PDOException $e) { 
                        echo "Connection failed: " . $e->getMessage(); 
                    } 

                ?>
            </div>
        </div>

        <div class="operations poppins-bold">
            <div class="deposit">
                <h1 class='underline'>Deposit<h1> 
                <form method="POST" action="wallet.php">
                    <input class="tt" type=number name="fundstoadd">
                    <input class="btn" type=submit value="DEPOSIT">
                </form>
            </div> 
            <div class="withdraw">
                <h1 class='underline'>Withdraw<h1> 
                <form method="POST" action="wallet.php">
                    <input class="tt" type=number name="fundstoremove">
                    <input class="btn" type=submit value="WITHDRAW">
                </form>
            </div>
        </div>

    </div> 
    <script src="script.js"></script> 
</body> 
  
</html>
