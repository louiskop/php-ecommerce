<?php

// start session
session_start();

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

    try { 
        $db = new PDO( 
            "mysql:host=$host;dbname=$dbname", 
            $username_db, 
            $password_db
        ); 
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

        // insert product into database
        $stmt = $db->prepare("INSERT INTO product (category_id, name, price, user_id, image, stock) VALUES (:cat_id, :name, :price, :userid, :image, :stock)");
        $stmt->bindParam(":cat_id", $category);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":image", $image);
        $stmt->bindParam(":stock", $stock);
        $stmt->execute(); 

        // unset img var
        unset($_SESSION["curimgname"]);

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
            <h1 class="poppins-bold">Add Product</h1>
            <p class="poppins-regular" id="tagline">Fill in the details of your product below</p>
            <div class="form-body poppins-bold">

                <form id="uploadForm" action="addproduct.php" method="post" enctype="multipart/form-data" style="display: none;">
                    <input type="file" name="uploadedFile" id="fileInput" accept="image/*">
                </form>

                <div id="triggerDiv">
                    <?php
                        if (isset($_SESSION["curimgname"])){
                            echo "<img class='uploadedimg' src='../customer/assets/products/" . $_SESSION["curimgname"] . "'>";
                        } else {
                            echo "Click to Upload Image";
                        }
                    ?>
                </div>

                <script>
                    document.getElementById('triggerDiv').addEventListener('click', function() {
                        document.getElementById('fileInput').click();
                    });

                    document.getElementById('fileInput').addEventListener('change', function() {
                        document.getElementById('uploadForm').submit();
                    });
                </script>

                <form class="add-form" method="POST" action="addproduct.php">                    
                    <label for="productname">
                        Name
                    </label>
                    <input class="field" type=text name="productname">
                    <label for="productcategory">
                        Category 
                    </label>
                    <select class="field" name="productcategory">
                        <option value=1 >Clothing</option>
                        <option value=2 >Shoes</option>
                        <option value=3 >Accessories</option>
                    </select>
                    <label  for="productprice">
                        Price (R)
                    </label>
                    <input class="field" type=number name="productprice">
                    <label for="productstock">
                        Initial Stock
                    </label>
                    <input class="field" type=number name="productstock" value=1>
                    <input id="createbtn" type=submit value="CREATE">
                </form>
            </div>
        </div>
    </div> 
    <script src="script.js"></script> 
</body> 
  
</html>
