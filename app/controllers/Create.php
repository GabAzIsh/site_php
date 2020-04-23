<?php


namespace side\app\controllers;


class Create
{
    protected $FLAG;
    protected $URL;

    private function load_pic($link){
        $url = 'https://www.litmir.me'.$link;
        $name = explode("/", $link);
        if ($this->FLAG == 1){
            // for file from folder
            $name = end($name);
            copy('C:/OpenServer/OSPanel/domains/site_one/file_for/'.$link, 'C:/OpenServer/OSPanel/domains/site_one/file_for/img_cop/'.$name);
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
            $img_file = fopen('C:/OpenServer/OSPanel/domains/site_one/file_for/img_cop/'.$name, 'wb');
            curl_setopt($request_img, CURLOPT_FILE, $img_file);
            curl_setopt($request_img, CURLOPT_HEADER, 0);
            curl_exec($request_img);
            curl_close($request_img);
            fclose($img_file);
        }
        return $name;
    }

    private function read_html(){
        if ($this->FLAG == 1){
            // Read from saved pages
            $html_scraping = file_get_contents("C:/OpenServer/OSPanel/domains/site_one/file_for/LItMIr.htm");
        } else {
            // Read from website by the CURL
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->URL);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
            $html_scraping = curl_exec($curl);
//            echo $html_scraping;
        }
        return $html_scraping;
    }

    private function get_element($element_name, $reg_exp_array, $html, $add_reg_exp=''){
        preg_match_all($reg_exp_array[$element_name], $html, $element);
        $element = $element[1][0];

        if(!empty($add_reg_exp)) {
            preg_match_all($reg_exp_array[$add_reg_exp], $element,$element_array);
            $element = $element_array[1];
        }
        return $element;
    }

    private function truncate($book_list){
        // TRUNCATE the tables
        $book_list->clear_all();
        $files = glob('file_for/img_cop/*'); // get all img file names
        // CLEAR all img files
        foreach($files as $file){ // iterate files
            if(is_file($file))
                unlink($file); // delete file
        }
        return true;
    }


    public function actionIndex(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Prepare
            $this->FLAG = isset($_POST['FLAG']) ? 1:0; // SET 1 to read from the file "/file_for/LItMIr.htm", ELSE the URL web page will be read
            $this->URL = !empty($_POST['url_name']) ? $_POST['url_name']: 'https://www.litmir.me/bs';
            $book_list = new \side\app\models\Book_managing($_dbname = "test_books");
            $this->truncate($book_list);
            $reg_exp_array = array(
                'author'     =>'/<span itemprop="author".*?>.*?(<a.*?>[^<]+<\/a>)[^<]*?<\/span>/us',
                'genre'      =>'/<span itemprop="genre".*?>.*?(<a.*?>[^<]+<\/a>)[^<]*?<\/span>/us',
                'img'        =>'/<img class="lt32 lazy"[^<]+src="(.*?)".*?>/us',
                'name'       =>'/<span itemprop="name">(.*?)<\/span>/us',
                'description'=>'/<div class="BBHtmlCodeInner"[^>]*?>(?!<span itemprop="reviewBody">)(?!<div style)(.*?)<\/div>/us',
                'a'          =>'/<a [^>]+>(.+?)<\/a>/us',
                'table'      =>'/<table class="island"(.*?)<\/table><div style="display:none;"/us'
            );
            $html_scraping = $this->read_html();

            // READ and WRITE to DB "test_books"
           preg_match_all($reg_exp_array['table'] ,$html_scraping ,$full_div);
            $table_rows = $full_div[0];

            foreach($table_rows as $html) {
                $authors = $this->get_element('author', $reg_exp_array, $html, 'a');
                $genres = $this->get_element('genre', $reg_exp_array, $html, 'a');

                $image = $this->get_element('img', $reg_exp_array, $html);
                $img_ref = $this->load_pic($image);

                $name = $this->get_element('name', $reg_exp_array, $html);
                $description = $this->get_element('description', $reg_exp_array, $html);
//                echo $name;
//                echo $description;
                // Record to the database
                $book_list->creation($name, $img_ref, $description, $genres, $authors);
            }
            $alert = "<h3>Finish</h3>";
        }  else {
            $this->FLAG = isset($_GET['FLAG']) ? 1:0;
        }
        require_once 'app/views/create_html.php';
        return true;
    }
}