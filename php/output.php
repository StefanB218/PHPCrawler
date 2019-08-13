<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phpcrawlerdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

function output($conn){
    $sql = "SELECT DISTINCT * from crawler where typ='email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // output data of each row
        echo "<table border='1'>";
        echo "<tr><th>#</th><th>Result</th><th>Hostname</th><th>Type</th></tr>";
        
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>". $row["id"]."</td>";
            echo "<td>". $row["result"]. "</td>";
            echo "<td>". $row["hostname"]."</td>";
            echo "<td>". $row["typ"]. "</td>";
            echo "</tr>";
        }
    } else {
        echo "0 results";
    }
    
    echo "</table>";
}

$conn->close();
?> 