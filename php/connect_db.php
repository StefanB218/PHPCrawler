<?php
function db_open($server_name, $user_name, $pwd, $db_name){       

    $conn = new mysqli($server_name, $user_name, $pwd, $db_name);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    //echo "<pre>Connected to DB</pre>";

    return $conn;
}

function db_close($conn)
{
    $conn->close();
    //echo "<pre>Closed DB-Connection</pre>";
}

function db_insert($conn,$value1,$value2,$value3){

    $sql_query = "INSERT INTO crawler (result,hostname,typ) VALUES ('$value1','$value2','$value3');";

    if($conn->query($sql_query) === true){
       // echo "<pre>New record created successfully</pre>";
    } else {
        echo "Error: " .$conn->error;
    }
}
