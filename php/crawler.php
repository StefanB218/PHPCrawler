<?php
include 'download.php';
include 'connect_db.php';
$connect = db_open("127.0.0.1", "root", "", "phpcrawlerdb");

if (isset($_POST['submit'])) {
	$url = $_POST['url_field'];
	parseResponse(downLoad($url));
}elseif (isset($_POST['show'])) {
	output($connect);
}

/*	$test1 = "https://www.bluechip.de/";	
	//$test2 = "https://de.wikipedia.org/wiki/Wikipedia:Impressum";
	//$test3="https://www.heise.de/impressum.html";
	parseResponse(downLoad($test1));*/

function parseResponse($downLoad_response)
{

	$conn = $GLOBALS["connect"];
	$hostname = $downLoad_response[0];
	$html = $downLoad_response[1];

	libxml_use_internal_errors(true);
	$dom = new DOMDocument();
	$dom->loadHTML($downLoad_response[1]);
	$anchor = $dom->getElementsByTagName('a');

	$isAbsoluteURL = "/((http|https):\/\/?)[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/?))/"; //regex check if valid url
	$isRelativeURL = "/^(\/)[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/?))/";
	$isEmail = "/([\w\.\-_]+)?\w+\@[\w\-\_]+(\.\w+){1,}/";
	$isPhone = "/(([\+][\d]{1,6})(.[0-9\s\/\(\)\-]{8,17}\d))/"; //phone nr. with country code

	foreach ($anchor as $href) {

		$link = $href->getAttribute('href');

		if (preg_match($isAbsoluteURL, $link)) {
			db_insert($conn, $link,  $hostname, 'url');			
		} elseif (preg_match($isRelativeURL, $link)) {
			db_insert($conn, $hostname.$link,  $hostname, 'url');	
		} elseif (preg_match($isEmail, $link, $mail)) {
			db_insert($conn, $mail[0],$hostname, 'email');		
		}
	}

	$tel = preg_match_all($isPhone, $html, $tel_match);
	foreach ($tel_match[0] as $telnum) {
		db_insert($conn, $telnum,$hostname, 'phone');		
	}
	db_close($conn);
}

function output($conn){
    $sql = "SELECT DISTINCT * from crawler";
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
