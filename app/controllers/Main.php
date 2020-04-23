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
//        $this->books->read();
    }

    private function element($img, $name, $description, $author, $genre, $ref){

        echo '<tr><td class="w-25"><a href="'.$ref.'"><img class="lt32 lazy" alt="'.$name.'" src="./file_for/img_cop/' . //    img/
            $img .
            '" style="display: inline;" width="250" height="376"></td><td><a href="'.$ref.'">' .                                     //    .jpg
            $name .
            '</a></td><td style="padding:10px;"><p> ' .
            $description .
            '</p></td><td>';
        foreach ($author as $author_name => $author_ref) {
            echo '<a href="' . $author_ref . '">' . $author_name . '</a><hr>';
        }
        echo '</td><td>';

        foreach ($genre as $genre_name => $genre_ref) {
            echo '<a href="' . $genre_ref . '">' . $genre_name . '</a><hr>';

        }
        echo '</td></tr>';
    }

    public function actionIndex(){
        $iitem=0;
        $genres = $this->genres;
        $authors = $this->authors;
        require_once 'app/views/main.php';
        return true;

    }
}