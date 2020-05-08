<?php declare(strict_types=1);


// GLOBAL VARIABLES
define("URL","https://www.litmir.me/bs");
// Operating mode selection flag:  0 for web scraping, 1 for html file parsing
define("FLAG", 0 );

// INITIALIZE
require __DIR__ . '/vendor/autoload.php';

// Prepare
$book_list = new \side\app\models\Book_managing($_dbname = "test_books");

// TRUNCATE the tables
$book_list->clear_all();
$files = glob('file_for/img_cop/*'); // get all file names

foreach($files as $file){ // iterate files
    if(is_file($file))
        unlink($file); // delete file
}

if (FLAG == 1){
    // Read from saved pages
    $html_scraping = file_get_contents(__DIR__ . "/file_for/LItMIr.htm");
    } else {
    // Read from website by the CURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, URL);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
    $html_scraping = curl_exec($curl);
}

// load picture
function load_pic($link){
    $url = sprintf('https://www.litmir.me%s', $link);
    $name = explode("/", $link);
    if (FLAG == 1){
        // for file from folder
        $name = end($name);
        copy(__DIR__ . '/file_for/' .$link, __DIR__ . '/file_for/img_cop/' .$name);
    } else {
        // for curl request
        $request_img = curl_init($url);
        if (end($name) == '0') {
            $name = array_slice($name, -2);
            $name = join("_", [$name[0], $name[1]]);
        } else {
            $name = array_slice($name, -2);
            $name = join("_", $name);
        }
        $img_file = fopen('/file_for/img_cop/'.$name, 'wb');
        curl_setopt($request_img, CURLOPT_FILE, $img_file);
        curl_setopt($request_img, CURLOPT_HEADER, 0);
        curl_exec($request_img);
        curl_close($request_img);
        fclose($img_file);
    }
    return $name;
}

$reg_exp_array = array(
    'author'=>'/<span itemprop="author".*?>.*?(<a.*?>[^<]+<\/a>)[^<]*?<\/span>/us',
    'genre'=>'/<span itemprop="genre".*?>.*?(<a.*?>[^<]+<\/a>)[^<]*?<\/span>/us',
    'img'=>'/<img class="lt32 lazy"[^<]+src="(.*?)".*?>/us',
    'name'=>'/<span itemprop="name">(.*?)<\/span>/us',
    'description'=>'/<div class="BBHtmlCodeInner"[^>]*?>(?!<span itemprop="reviewBody">)(?!<div style)(.*?)<\/div>/us',
    'a'=>'/<a [^>]+>(.+?)<\/a>/us',
    'table'=>'/<table class="island"(.*?)<\/table><div style="display:none;"/us'
);

function get_element($element_name, $reg_exp_array, $html, $add_reg_exp=''){
    preg_match_all($reg_exp_array[$element_name], $html, $element);
    $element = $element[1][0];
    if(!empty($add_reg_exp)) {
    preg_match_all($reg_exp_array[$add_reg_exp], $element,$element_array);
        $element = $element_array[1];
    }
    return $element;
}



preg_match_all($reg_exp_array['table'] ,$html_scraping ,$full_div);
$table_rows = $full_div[0];



foreach($table_rows as $html) {
    $authors=get_element('author', $reg_exp_array, $html, 'a');
    $genres = get_element('genre', $reg_exp_array, $html, 'a');

    $image = get_element('img', $reg_exp_array, $html);
    $img_ref = load_pic($image);

    $name = get_element('name', $reg_exp_array, $html);
    $description = get_element('description', $reg_exp_array, $html);

    // Record to the database
    $book_list->creation($name, $img_ref, $description, $genres, $authors);
}
echo "<h3>Finish</h3>";
?>
