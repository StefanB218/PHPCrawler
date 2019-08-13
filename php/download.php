<?php
    function downLoad($downLoad_URL){
            $cURL_connection = curl_init();
            $cURL_options = array(
                CURLOPT_URL=>$downLoad_URL,
                CURLOPT_HTTPGET=>true,
                CURLINFO_HEADER_OUT=>true,
                CURLOPT_FOLLOWLOCATION=>true,
                CURLOPT_MAXREDIRS=>4,
                CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_USERAGENT=>"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:64.0) Gecko/20100101 Firefox/64.0");

            curl_setopt_array($cURL_connection, $cURL_options);

            $download_cURL = curl_exec($cURL_connection);
            preg_match("/(?<=Host: )(\S+)/",curl_getinfo($cURL_connection, CURLINFO_HEADER_OUT),$host);
            curl_close($cURL_connection);    
            
            $document = array(0 => $host[0], 1=>$download_cURL);
        return $document;
    }
?>