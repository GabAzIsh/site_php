<?php


namespace side\app\controllers;


class Book
{
    function __construct()
    {
        $book = new \side\app\models\Book_managing("test_books");
        $this->books = $book;
    }

    public function actionIndex(){
        // GET name of book and prepare $options array
        $options = array_merge($_GET, array('paginator'=>'1','offset'=>'0'));

        // Get data from DataBase
        $this->books->read($options);
        $temple = $this->books->read_row();

        // Create variables: $title $genre $author $descriptions  $cover_img_ref
        foreach($temple as $index=>$value){
            ${$index} = $value;
        }

        // Prepare genres/authors block (html)
        $genre = explode(',', $genre);
        $author = explode(',', $author);
        $block1 = '';
        foreach ($author as $author_ref) {
            $block1 .= '<p><a href="../?author=' . $author_ref . '">' . $author_ref  . '</a></p>';
        }
        $block2 =  '';
        foreach ($genre as $genre_ref) {
            $block2 .= '<p><a href="../?genre=' . $genre_ref . '">' . $genre_ref . '</a></p>';
        }
        $author_genre = '<div><h6>Авторы</h6>'.$block1.'</div><div><h6>Жанры</h6>'.$block2.'</div>';

        // Execute view with inserted prepared variables
        require_once 'app/views/book_view.php';
        return true;
    }
}