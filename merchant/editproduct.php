
<?php

// start session
session_start();

if (isset($_GET["id"])) {
    $_SESSION["product_id"] = $_GET["id"];
}

// handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploadedFile'])) {

    $uploadDir = '../customer/assets/products/'; 
    $uploadFile = $uploadDir . basename($_FILES['uploadedFile']['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

    if (move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $uploadFile)) {
        $_SESSION["curimgname"] = $_FILES['uploadedFile']['name'];
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}


// handle product creation
else if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // connect to database
    $host = "localhost"; 
    $dbname = "agora"; 
    $username_db = "root"; 
    $password_db = ""; 

    $userid = $_SESSION["user"]["id"];
    $image = $_SESSION["curimgname"];
    $name = $_POST["productname"];
    $category = $_POST["productcategory"];
    $price = $_POST["productprice"];
    $stock = $_POST["productstock"];
    $product_id = $_POST["prodid"];

    try { 
        $db = new PDO( 
            "mysql:host=$host;dbname=$dbname", 
            $username_db, 
            $password_db
        ); 
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

        // insert product into database
        $stmt = $db->prepare("UPDATE product SET category_id = :cat_id, name = :name, price = :price, image = :image, stock = :stock WHERE id = :prodid");
        $stmt->bindParam(":cat_id", $category, PDO::PARAM_INT); 
        $stmt->bindParam(":name", $name, PDO::PARAM_STR); 
        $stmt->bindParam(":price", $price, PDO::PARAM_INT); 
        $stmt->bindParam(":image", $image, PDO::PARAM_STR); 
        $stmt->bindParam(":prodid", $product_id, PDO::PARAM_INT); 
        $stmt->bindParam(":stock", $stock, PDO::PARAM_INT); 
        $stmt->execute(); 

        // unset img var
        unset($_SESSION["curimgname"]);
        unset($_SESSION["product_id"]);

        // redirect back to merchant page
        header("Location: /agora/merchant/merchant.php");

    } catch (PDOException $e) { 
        echo "Connection failed: " . $e->getMessage(); 
    } 
}


?>

<!DOCTYPE html> 
<html> 
  
<head> 
    <title>Add Product</title> 
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
        <div class="modal">
            <h1 class="poppins-bold">Edit Product</h1>
            <p class="poppins-regular" id="tagline">Edit the details of your product below</p>
            <div class="form-body poppins-bold">

                <form id="uploadForm" action="editproduct.php" method="post" enctype="multipart/form-data" style="display: none;">
                    <input type="file" name="uploadedFile" id="fileInput" accept="image/*">
                </form>



                <?php
                    
                    // get product
                    $product_id = $_SESSION["product_id"];

                    // connect to database
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

                        // insert product into database
                        $stmt = $db->prepare("SELECT * FROM product WHERE id = :prodid");
                        $stmt->bindParam(":prodid", $product_id);
                        $stmt->execute(); 
                        $product = $stmt->fetch(PDO::FETCH_ASSOC); 

                        // add image
                        if (!isset($_SESSION["curimgname"])) {
                            $_SESSION["curimgname"] = $product["image"];
                        }

                        echo "

                        <div id='triggerDiv'>
                            <img class='uploadedimg' src='../customer/assets/products/" . $_SESSION["curimgname"] . "'>;
                        </div>

                        <script>
                            document.getElementById('triggerDiv').addEventListener('click', function() {
                                document.getElementById('fileInput').click();
                            });

                            document.getElementById('fileInput').addEventListener('change', function() {
                                document.getElementById('uploadForm').submit();
                            });
                        </script>


                        <form class='add-form' method='POST' action='editproduct.php'>                    
                            <label for='productname'>
                                Name
                            </label>
                            <input class='field' type=text name='productname' value='" . $product["name"] . "'>
                            <label for='productcategory'>
                                Category 
                            </label>
                            <select class='field' name='productcategory' default='" . $product["category_id"] . "'>
                                <option value=1 >Clothing</option>
                                <option value=2 >Shoes</option>
                                <option value=3 >Accessories</option>
                            </select>
                            <label  for='productprice'>
                                Price (R)
                            </label>
                            <input class='field' type=number name='productprice' value='" . $product["price"] . "'>
                            <label for='productstock'>
                                Stock
                            </label>
                            <input class='field' type=number name='productstock' value='" . $product["stock"] . "'>
                            <input type=hidden name='prodid' value='" .$product["id"]. "'>
                            <input id='createbtn' type=submit value='UPDATE'>
                        </form>
                        ";
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
