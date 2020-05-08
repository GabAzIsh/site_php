<?php

namespace side\app\controllers;
class Main
{
    function __construct()
    {
        $book = new \side\app\models\Book_managing("test_books");
        $this->books = $book;
        $this->genres = $book->get_list('genre');
        $this->authors = $book->get_list('author');
        $this->options = array();
    }

// table row creation
    private function element($img, $name, $description, $author, $genre, $ref){
        // Prepare genres/authors block (html)
        $block1 = '';
        foreach ($author as $author_name => $author_ref) {
            $block1 .= '<p><a href="' . $author_ref . '">' . $author_name  . '</a></p>';
        }
        $block2 =  '';
        foreach ($genre as $genre_name => $genre_ref) {
            $block2 .= '<p><a href="' . $genre_ref . '">' . $genre_name . '</a></p>';
        }
        $author_genre = '<div><h6>Авторы</h6>'.$block1.'</div><div><h6>Жанры</h6>'.$block2.'</div>';


        $element =
            '<tr>
                <td class="w-25">
                    <a href="'.$ref.'">
                        <img class="lt32 lazy" alt="'. $name .'" src="./file_for/img_cop/'. $img .'" style="display: inline;" width="250" height="376">
                    </a>
                </td>
                <td class="name_gen_auth">
                    <a href="'.$ref.'">' .$name .'</a>
                    <div class="authors/genres">
                        <div class="button" id="button" style="color: #0056b3">authors/genres</div>
                        <div id="collapse_id" class="detail_click">'.$author_genre.'</div>
                    </div>
                </td>
                <td style="padding:10px;">
                    <p> ' . $description . '</p>
                </td>
                ';
        return $element.'</td></tr>';
    }
// table creation
    private function generate_table(array $options){
        $this->books->read($options);
        $table = '';
        while ($this->books->flag > 0){
            $temple = $this->books->read_row();
            $name =  $this->books->title;
            $description =  $this->books->description;
            $img =  $this->books->cover_img_ref;
            $ref = "/book/?name=".$name;
            // IN SEPARATE FUNCTION
            $raw_genres =  explode(",",$this->books->genre);
            foreach($raw_genres as $unit){
                $genre[$unit] = "/?genre=".$unit;
            }
            $raw_authors =  explode(",",$this->books->author);
            foreach($raw_authors as $unit){
                $author[$unit] = "/?author=".$unit;
            }
            $table .= $this->element($img, $name, $description, $author, $genre, $ref);
            unset($author);
            unset($genre);
            $count_row = $this->books->count_row;
        }
        $count_row = $count_row ?? 0; // If no matches match set 0
        return array($table,$count_row);
    }
// read/create COOKIES and concatenation with $_GET array
    private function cookie_management($number_per_page,$page_number)
    { // CHECK if POST request have 'flag' for detecting both JQuery and Fetch requests
        if ($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['flag'])) {
            return $this->options = array_merge($_COOKIE, array('paginator'=>$number_per_page,'offset'=>($page_number-1)*$number_per_page));
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->options = array_merge(array_diff($_POST, ['']), array('paginator' => $number_per_page, 'offset' => ($page_number - 1) * $number_per_page));
            // delete old cookies
            foreach ($_COOKIE as $key => $value) {
                setcookie($key, $value, time() - 3600, '/');
            }
            // set new cookies
            foreach ($_POST as $cookie_name => $cookie_value) {
                setcookie($cookie_name, $cookie_value, time() + 900, "/");
            }
            return $this->options;
        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            if (isset($_GET['genre']) OR isset($_GET['author'])) {
                return $this->options = array_merge($_GET, array('paginator'=>$number_per_page,'offset'=>($page_number-1)*$number_per_page));
            } else {
                return $this->options = array_merge($_COOKIE, array('paginator'=>$number_per_page,'offset'=>($page_number-1)*$number_per_page));
            }
                    }
    }
// creating a pagination array
    private function numeration($count_row, $number_per_page, $page_number) :array {
        $max = ceil($count_row/$number_per_page);
        $b = $max -$page_number - 3;
        // Create array with 7 or $max elements
        if ($max<=5) {
            $amount= range(1, $max);
        } else {
            $amount = range($page_number - 3, $page_number + 3);
            // Array correction
            if ($amount[0] <= 0) {
                $amount = range($page_number - 3 - $amount[0] + 1, $page_number + 3 - $amount[0] + 1);
            } elseif ($b < 0) {
                $amount = range($page_number - 3 + $b, $page_number + 3 + $b);
            }
        }
        // Delete current page
        unset($amount[array_search($page_number, $amount)]);
        return array($max, $amount);
    }

    public function actionIndex(){
        // create a stemmer (Rus)
        $stemmer = \Wamania\Snowball\StemmerFactory::create('ru');
        $number_per_page = 10;
        $page_number = $_GET['page'] ?? $_COOKIE['page'] ?? 1; // In Fetch/JQuery-AJAX case page number in cookie

        // For ajax request: CHECK 'CONTENT_TYPE' for fetch api, then we get POST array
        if (isset($_SERVER['CONTENT_TYPE']) AND  $_SERVER['CONTENT_TYPE'] == 'application/json') {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
        $checkbox_setup = $this->cookie_management($number_per_page, $page_number);
        list($table, $count_row) = $this->generate_table($this->options);
        list($max, $amount) = $this->numeration($count_row, $number_per_page, $page_number);

    // CHECK if POST request have 'flag' for detecting both JQuery and Fetch requests
        if (isset($_POST['flag'])){
            echo json_encode(array("html_from_view"=>$table, "amount"=>$amount, "maximum"=>$max, "page_number"=>$page_number ), JSON_UNESCAPED_UNICODE);
        } else {
            $genres = $this->genres;
            $authors = $this->authors;

            $checkbox_setup = array_diff($checkbox_setup, [0, '']);
            require_once 'app/views/main_view.php';
            return true;
        }
    }
}