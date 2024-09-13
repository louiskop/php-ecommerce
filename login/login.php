<?php 
  
// handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") { 

    // get login fields
    $username = $_POST["username"]; 
    $password = $_POST["password"]; 
  
    // Connect to the database 
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
  
        // check if the user exists in the database 
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username"); 
        $stmt->bindParam(":username", $username); 
        $stmt->execute(); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC); 
  
        // verify password if user exists
        if ($user) { 
            if (password_verify($password, $user["password"])) { 

                // save this session with user information
                session_start(); 
                $_SESSION["user"] = $user; 
  
                if ($user["role"] == "merchant") {
                    echo '<script type="text/javascript"> 
                            window.onload = function () { 
                                window.location.href = "/agora/merchant/merchant.php";  
                            }; 
                        </script> '; 
                }
                else if ($user["role"] == "customer") {
                    echo '<script type="text/javascript"> 
                            window.onload = function () { 
                                window.location.href = "/agora/customer/customer.php";  
                            }; 
                        </script> '; 
                }
                else if ($user["role"] == "admin") {
                    echo '<script type="text/javascript"> 
                            window.onload = function () { 
                                window.location.href = "/agora/admin/admin.php";  
                            }; 
                        </script> '; 
                }
        


            } else { 
                echo "<h2>Login Failed</h2>"; 
                echo "Invalid email or password."; 
            } 
        } else { 
            echo "<h2>Login Failed</h2>"; 
            echo "User doesn't exist"; 
        } 
    } catch (PDOException $e) { 
        echo "Connection failed: " . $e->getMessage(); 
    } 
} 
?>
