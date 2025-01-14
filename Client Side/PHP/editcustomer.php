<!DOCTYPE html>
<html>
<head>
	<title>Edit Customer Information</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../css/layout.css"/>
    <!-- <script type="text/javascript" src="../script/validation.js"></script>-->
</head>
<header>
    <div class="flex-container">    
       <?php session_start(); include "navbar.php";?>
    </div>
</header>
<body>
	<h1>Edit Customer Information</h1>
	<div>
		<div class="columnleft">
			<form method="post" action="edituser.php" id="loginForm">
                    <p>
                        <label for="username">Username: </label>
                        <input type="text" id="username" name="username" required>
                    </p>
                    <p>
                        <label for="email">Email: </label>
                        <input type="email" id="email" name="email" required>
                    </p>
                    <p>
                        <label for="updates">Email Updates: </label>
                        <input type="checkbox" id="updates" name="updates" value=1>
                    </p>
                    <button type="submit">Submit</button>
			</form>
            <button onclick="location.href = 'customer.php';">Back</button> 
            <?php if(isset($_SESSION["edituser_msg"])) {
                 echo "<h3>".$_SESSION["edituser_msg"]."</h3>";
                 unset($_SESSION["edituser_msg"]);
            }?>
             <!--<button onclick="location.href = 'changepassword.php';">Change Password</button>-->

		</div>
		
        </div>
        </body>
        <footer>
            <p>
                <a href="home.php">Home</a> |
                <a href="browse.php">Browse</a>
            </p>
            <p>
                <small><i>Copyright &copy; 2023 COSC 360 Project XTREME GPT</i></small>
            </p>
        </footer>
        </html>