<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['login_error'] = "You do not have this permission, please sign in";
	header('Location: login.php'); 
	exit;
}
include "connectDB.php"; 

$stmt = $con->prepare("SELECT p.id AS product_id, pc.name AS product_category, p.imgsrc AS product_img, pp.price AS product_price, p.description AS product_desc
        FROM product p
        JOIN (
            SELECT product_id, chain_location_id, MAX(created_at) AS latest_date
            FROM product_price
            JOIN chain_location ON product_price.chain_location_id = chain_location.id
            JOIN chain ON chain_location.chain_id = chain.id
            WHERE chain.name = ? AND chain_location.name = ?
            GROUP BY product_id, chain_location_id
        ) latest_prices ON p.id = latest_prices.product_id 
        JOIN product_price pp ON pp.product_id = p.id AND pp.chain_location_id = latest_prices.chain_location_id AND pp.created_at = latest_prices.latest_date
        JOIN chain_location cl ON pp.chain_location_id = cl.id
        JOIN chain c ON c.id = cl.chain_id 
        JOIN product_category pc ON pc.id = p.category_id
        WHERE p.name = ?");

$search = $_GET["search"];
$product_name = $_GET["product_name"]; 
$chain = $_GET["chain"]; 
$chain_location = $_GET["chain_location"];
$product_category = $_GET["product_category"];
$product_name_url = rawurlencode($product_name);
$search_url = rawurlencode($search);
$product_url_query = "?product_name=$product_name_url&chain=$chain&chain_location=$chain_location&product_category=$product_category&search=$search_url";

$stmt->bind_param('sss', $chain, $chain_location, $product_name);

$stmt->execute();

$stmt->bind_result($product_id, $product_exact_category, $product_img, $product_latest_price, $product_desc);

$stmt->fetch();

$stmt->close();


$stmt2 = $con->prepare("SELECT c.id as commentID, a.username AS username, c.comment AS comment_desc, c.rating AS comment_rating
    FROM account a
    JOIN comments c ON c.account_id = a.id
    JOIN product p ON p.id = c.product_id
    WHERE p.name = ?");
// In this case we can use the search to get the comment info.
$stmt2->bind_param('s', $product_name);
$stmt2->execute();
$result2 = $stmt2->get_result();

$stmt2->close();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Product</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../css/layout.css"/>
    <script></script>
</head>
<header>
        <?php include "navbar.php"; ?>
</header>
<body>
    <div id="prodinfo">
        <p id="prodname">Product Name: <?=$product_name?></p>
        <p id="prodname">Product Category: <?=$product_exact_category?></p>
        <p id="prodname">Sourced From: <?=$chain?> in <?=$chain_location?></p>
        <p id="description">Description: <?=$product_desc?></p>
        <p id="price">Latest Price: $<?=$product_latest_price?></p>
    </div>
    <figure class="productfig">
        <div class="card">
            <p><img class="card-img" src=<?=$product_img?>></p>
        </div>
        <p><!-- soon to be implemented  <form method="post" action="product.php">
            <label for="pricealert">set alert price: </label>
            <input type="number" min="0" step="0.01" id="pricealert" name="pricealert">
            <button type="submit">Submit</button>
        </form>--></p>
        <p> 
        <!-- <button onclick="location.href = 'browse.php';">add to basket</button>-->
        <button onclick="location.href = 'browse.php?search=<?=$search?>&chain=<?=$chain?>&chain_location=<?=$chain_location?>&product_category=<?=$product_category?>';">back to browse</button>
        </p>
    </figure>
    <div> 
        <h4>Add a Comment:</h4>
        <?php       
            if (isset($_SESSION['loggedin'])){
                    echo '<form method="post" action="enterComment.php?product_id='.$product_id.'&account_id='.$_SESSION["id"].'">
                        <p>
                            <input type="hidden" id="productURL" name="productURL" value="'.$product_url_query.'">
                            <label for="rating">Rating /5: </label>
                            <select name="rating" id="rating" required>
                                <option value=1>1 star</option>
                                <option value=2>2 star</option>
                                <option value=3>3 star</option>
                                <option value=4>4 star</option>
                                <option value=5>5 star</option>
                            </select>
                        </p>
                        <p>
                            <label for="comment">Comment: </label>
                            <textarea id="comment" name="comment" rows="5" cols="40" required></textarea>
                        </p>
                        <button type="submit">Post</button> | 
                        <button type="reset">Clear</button>
                    </form>';
                }?>
    </div>
    <div class = "comments">
        <h4>Comments:</h4>
        <table>
            <tr>
                <th>Username</th>
                <th>Rating</th>
                <th>Comments</th>
            </tr>
            <?php
            //$_SESSION["is_admin"] = true; // added until admin functionality is implemented
   if (isset($_SESSION["id"]) && isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true){
    while($row = $result2->fetch_assoc()) {
        echo '<tr>
                <td>'.$row["username"].'</td>
                <td>'.$row["comment_rating"].'</td>
                <td>'.$row["comment_desc"].'</td>
                <td><form method="post" action="deleteComment.php"> 
                <input type="hidden" id="commentID" name="commentID" value="'.$row["commentID"].'"> 
                <input type="hidden" id="productURL" name="productURL" value="'.$product_url_query.'">
                <button type="submit">Delete Comment</button>
                </form></td></tr>';}
    }
    else{
        while($row = $result2->fetch_assoc()) {
            echo '<tr>
                    <td>'.$row["username"].'</td>
                    <td>'.$row["comment_rating"].'</td>
                    <td>'.$row["comment_desc"].'</td>
                    ';if($_SESSION["username"] === $row["username"]) echo '<td><form method="post" action="deleteComment.php"> 
                    <input type="hidden" id="commentID" name="commentID" value="'.$row["commentID"].'"> 
                    <input type="hidden" id="productURL" name="productURL" value="'.$product_url_query.'">
                    <button type="submit">Delete Comment</button>
                    </form></td></tr>';
                    else echo'
                    </tr>';}
    }
    ?>
    </div>
</body>