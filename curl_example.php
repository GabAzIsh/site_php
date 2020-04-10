<?php
//header("Location: curia.html?dsuccess");
// INITIALIZE
$curl = curl_init();

// GLOBAL VARIABLES
$url = "https://www.litmir.me/bs";

include 'element.php';


curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($curl);
echo gettype($html);
echo "<br>";
echo strlen($html);


?>
<?php
$regexep='/<table class="island"(.*)<\/table>/Us';
//preg_match_all($regexep ,$html ,$gora, PREG_OFFSET_CAPTURE);
preg_match_all($regexep ,$html ,$gora);
print_r($gora[0][1]);
echo count($gora[0]);
echo '<br><br><br><br><br>';
if (empty(array_filter($gora))){
    echo "yes";
}
$html = $gora[0];
echo count($html);
?>
<?php

// RegExpression For authors '/<span [/w/s]+? itemprop="author"(.*)>(.*)(<a[^>]+>)*<\/span>/us'
$regexp='/<span itemprop="author".*?>.*?(<a.*?>[^<]+<\/a>)<\/span>/us';
preg_match_all($regexp ,$html ,$authors);
print_r($authors[1]);
// RegExpression For genres '/<span itemprop="genre"(.*)>(.*)(<a[^>]+>)*<\/span>/us'
$regexp='/<span itemprop="genre".*?>.*?(<a.*?>[^<]+<\/a>)<\/span>/us';
preg_match_all($regexp ,$html ,$genres);
print_r($genres[1]);
/* RegExpression  For IMG  '/<img class="lt32 lazy"[^<]+src="(.*?)".*?>/us' */
$regexp='/<img class="lt32 lazy"[^<]+src="(.*?)".*?>/us';
preg_match_all($regexp ,$html ,$image);
$image=$image[1][0];
var_dump($image);

// RegExpression For Name(title) '/<span itemprop="name">(.*?)<\/span>/us'
$regexp='/<span itemprop="name">(.*?)<\/span>/us';
preg_match_all($regexp ,$html ,$name);
$name=$name[1][0];
var_dump($name);

/* RegExpression For Description '/<div class="BBHtmlCodeInner"[^>]+?>(?!<span itemprop="reviewBody">)(?!<div style="text-align)(.*?)<\/div>/us' */
$regexp='/<div class="BBHtmlCodeInner"[^>]*?>(?!<span itemprop="reviewBody">)(?!<div style)(.*?)<\/div>/us';
preg_match_all($regexp ,$html ,$description);
$description=$description[1][0];
var_dump($description);




//while($node > 0){
//    preg_match_all($regexep ,$html ,$gora, PREG_OFFSET_CAPTURE);
//
//    if (!empty(array_filter($gora))) {
//        $main_arra=$gora;
//        $gora = $gora[0][0][1];
//        $residue = substr_count($gora, '</table>');
//        $node = substr_count($gora, '<div')-$residue;
//        $prepared = str_replace("/","\/",$gora);
//        $regexep = '/'.$prepared.str_repeat('(.*)<\/div>', $node).'/Us';
//
//    } else {
//        $node = 0;
//    }
//
//}

//echo  'THis strange, but: '.$result;
//
//preg_match_all( "/<([^>]+)>([^<]+)<\/([a-z]+)>/U",$result ,$something);
//
//print_r($something);



// Declare a string


// Use preg_match_all() function to perform
// a global regular expression match



?>
