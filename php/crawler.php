<?php
include 'connect_db.php';
$glo_conn = db_open("127.0.0.1", "root", "", "phpcrawlerdb");

if (isset($_POST['submit'])) {
	$url = $_POST['url_field'];
	parseResponse(downLoad($url));	
}elseif (isset($_POST['show'])) {
	output($glo_conn);
}elseif(isset($_POST['delete'])){
	db_delete($glo_conn);
}

/*	$test1 = "https://www.bluechip.de/";	
	//$test2 = "https://de.wikipedia.org/wiki/Wikipedia:Impressum";
	//$test3="https://www.heise.de/impressum.html";
	//$test4="https://www.deepl.com/publisher.html";
	parseResponse(downLoad($test1));*/

function downLoad($downLoad_URL){
		$cURL_connection = curl_init();
		
		$cURL_options = array(CURLOPT_URL=>$downLoad_URL,CURLOPT_HTTPGET=>true,CURLINFO_HEADER_OUT=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_RETURNTRANSFER=>true,
			CURLOPT_USERAGENT=>"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:64.0) Gecko/20100101 Firefox/64.0"); // set cURL Options
		curl_setopt_array($cURL_connection, $cURL_options);

		$download_cURL = curl_exec($cURL_connection);
		preg_match("/(?<=Host: )(\S+)/" //extract host from header
		,curl_getinfo($cURL_connection, CURLINFO_HEADER_OUT),$host);
		curl_close($cURL_connection);    
		
		$document = array(0 => $host[0], 1=>$download_cURL);
	return $document;
}	

function parseResponse($downLoad_response){

	$conn = $GLOBALS['glo_conn'];
	$hostname = $downLoad_response[0];
	$html = $downLoad_response[1];
	
	/*create and load dom to select all anchor <a> tags*/
	libxml_use_internal_errors(true);
	$dom = new DOMDocument();
	$dom->loadHTML($downLoad_response[1]);
	$anchor = $dom->getElementsByTagName('a');

	$isAbsoluteURL = "/((http|https):\/\/?)[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/?))/"; //regex check if valid url //(http or https)+[not whitespace or ()<>](all words & digits)or[not punctuation char]
	$isRelativeURL = "/^(\/)[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/?))/";//same as above without protocol

	$isEmail = "/([\w\.\-_]+)?\w+\@[\w\-\_]+(\.\w+){1,}/";	//name@domain.com //(any word/.-_)@(any word).
	$isEmail_escaped = "/([\w\.\-_]+)?\w+\(at\)[\w\-\_]+(\(dot\)\w+){1,}/"; //name(at)domain(dot)com
	$isEmail_escaped_at = "/([\w\.\-_]+)?\w+\(at\)[\w\-\_]+(\.\w+){1,}/"; //name(at)domain.com

	$isPhone = "/(([\+][\d]{1,6})(.[0-9\s\/\(\)\-]{8,17}\d))/"; //phone nr. with country code
	
	foreach ($anchor as $href) {
		//select attribute href from anchor to scrape all links
		$link = $href->getAttribute('href');

		if (preg_match($isAbsoluteURL, $link)) {
			db_insert($conn, $link,  $hostname, 'url');			
		} elseif (preg_match($isRelativeURL, $link)) {
			db_insert($conn, $hostname.$link,  $hostname, 'url');	
		}
	}

	if(!empty(preg_match_all($isEmail, $html, $mails))){
		fillMailandPhone($conn, $mails,$hostname,'email');
	}	
	elseif(!empty(preg_match_all($isEmail_escaped, $html, $escaped_mail))){
		fillMailandPhone($conn, $escaped_mail,$hostname,'email');
	}
	elseif(!empty(preg_match_all($isEmail_escaped_at,$html, $escaped_mail_at))){
		fillMailandPhone($conn, $escaped_mail_at,$hostname,'email');
	}	
	if(!empty(preg_match_all($isPhone, $html, $tel_match))){
		fillMailandPhone($conn, $tel_match, $hostname,'phone');
	}
	output($conn);
	db_close($conn);
	
}

function fillMailandPhone($conn,$results,$hostname,$typ){
		$rm_dupl = array_unique($results[0]);
		foreach ($rm_dupl as $key) {
			db_insert($conn, $key,$hostname, $typ);		
		}
}

function output($conn){
	echo "<table border='1'><tr><th>Result</th><th>Hostname</th><th>Typ</th></tr>"; 
	$sql = "SELECT DISTINCT * FROM crawler ORDER BY typ, hostname";
	$result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>". $row["result"]. "</td>";
            echo "<td>". $row["hostname"]."</td>";
            echo "<td>". $row["typ"]. "</td>";
            echo "</tr>";
        }
    } else {
        echo "Datenbank ist leer";
    }    
    echo "</table>";
}
