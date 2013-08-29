<?php
/*
 * PHP Bruteforce ID downloader & web scraping script
 * @Author Luka Pušić luka@pusic.si
 * 
 * Don't forget to change the $url below!
 */

//REQUIRED
$start = 1;
$stop = 10000;
//OPTIONAL
$logfile = time() . '.txt';
$scrapedir = 'scrape';

//initiate logfile start, mk scrapedir
$log = fopen($logfile, 'w+') or die("can't open log");
fwrite($log, date('d.m.Y H:i') . "\tSTARTED at $start\n");
@mkdir($scrapedir);

for ($n = $start; $n < $stop; $n++) {

    //REQUIRED
    $url = "http://www.example.com/download.php?id=$n";

    //look for filename, if found, download
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');
    curl_setopt($ch, CURLOPT_HEADER, true); // header will be at output
    curl_setopt($ch, CURLOPT_NOBODY, true);
    #curl_setopt($ch, CURLOPT_PROXY, "localhost:9050");
    #curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    $header = curl_exec($ch);
    curl_close($ch);

    //validate and sanitize filename, if not found, continue loop
    if (preg_match('/content-disposition: .*filename=([^\n]+)/i', $header, $matches)) {
        //remove all non alnum chars
        $filename = trim(preg_replace("/[^a-zA-Z0-9.\-_]/", "", $matches[1]));
    } else {
        echo "$n\tskipping...\n";
        fwrite($log, date('d.m.Y H:i') . "\t$n\tskipping...\n");
        continue;
    }

    //initiate file download
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');
    curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    #curl_setopt($ch, CURLOPT_PROXY, "localhost:9050");
    #curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    $contents = curl_exec($ch);
    curl_close($ch);

    //stdout info
    echo "$n\t$filename\n";

    //write file contents
    $fh = fopen($scrapedir . '/' . $filename, 'w') or die("can't open file");
    fwrite($fh, $contents);
    fclose($fh);

    //write to log
    fwrite($log, date('d.m.Y H:i') . "\t$n\t$filename\n");
}
//write end time to log & close
fwrite($log, date('d.m.Y H:i') . "\tSTOPPED\n");
fclose($log);
?>
