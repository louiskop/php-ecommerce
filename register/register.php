<?php 

// handle register form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") { 

    // get all fields from request
    $role = $_POST["role"];
	$username = $_POST["username"]; 
	$email = $_POST["email"]; 
	$password = $_POST["password"]; 
	
	// hash password before storing
	$hashed_password = password_hash($password, PASSWORD_BCRYPT); 

    // database configuration
	$host = "localhost"; 
	$dbname = "agora"; 
	$username_db = "root"; 
	$password_db = ""; 

    // connect to database
	try { 
		$db = new PDO( 
		"mysql:host=$host;dbname=$dbname", 
			$username_db, $password_db); 
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
		
		// add user to database
		$stmt = $db->prepare( 
		"INSERT INTO users (username,email,role,password) 
			VALUES (:username, :email, :role, :password)"); 
		$stmt->bindParam(":username", $username); 
		$stmt->bindParam(":email", $email); 
		$stmt->bindParam(":role", $role); 
		$stmt->bindParam(":password", $hashed_password); 
		$stmt->execute(); 

        // redirect user to login page
        header("Location: /agora/login/login.html");
	} 

    // handle errors
	catch(PDOException $e) { 
		echo "Connection failed: " . $e->getMessage(); 
	} 
} 
?>
