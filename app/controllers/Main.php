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

    private function element($img, $name, $description, $author, $genre, $ref){

        $block1 =  '<tr><td class="w-25"><a href="'.$ref.'"><img class="lt32 lazy" alt="'.$name.'" src="./file_for/img_cop/' . //    img/
            $img .
            '" style="display: inline;" width="250" height="376"></td><td><a href="'.$ref.'">' .                                     //    .jpg
            $name .
            '</a></td><td style="padding:10px;"><p> ' .
            $description .
            '</p></td><td>';
        $block2 = '';
        foreach ($author as $author_name => $author_ref) {
            $block2 .= '<a href="' . $author_ref . '">' . $author_name . '</a><hr>';
        }
        $block3 =  '</td><td>';
        foreach ($genre as $genre_name => $genre_ref) {
            $block3 .= '<a href="' . $genre_ref . '">' . $genre_name . '</a><hr>';
        }
        return $block1.$block2.$block3.'</td></tr>';
    }

    private function generate_table(array $options){
        $this->books->read($options);
        $iitem=0;
        $table = '';
        while ($this->books->flag > 0){
            $iitem++;
            $temple = $this->books->read_row();
            $name =  $this->books->title;
            $description =  $this->books->description;
            $img =  $this->books->cover_img_ref;
            $ref = "/?full_name=".$name;
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
            if ($iitem>50){break;}
        }
        return $table;
    }

    private function name_prepare(string $name) :string {
        // Create a stemmer (Rus)
        $stemmer = \Wamania\Snowball\StemmerFactory::create('ru');
        // Preparation of name
        $replacing = array("'"=>"`", "'\\n'"=>"\n", ','=>'', '.'=>'', ':'=>'', ';'=>'', '='=>'', '*'=>'' );
        $name_mod = strtr($name, $replacing);
        $name_array = explode(' ', $name_mod);

        // Replace an inflectional  by '*' by using lookahead case insensitive (  '/^.+(?=name)/i' ) and lookbehind case insensitive  '/(?<=name).+/i
        $set_name = function($value) use ($stemmer) {
                    return preg_replace(['/^.+(?=' . $stemmer->stem($value) . ')/i', '/(?<=' . $stemmer->stem($value) . ').+/i'], ['*', '*'], $value);
                };
        // Apply the function to array and concatenate to string
        $name_array_modified = array_map($set_name, $name_array);
        $saved_name = $name_array_modified[0];
        array_walk($name_array_modified, function(&$item) {  $item = '+'.$item; });
        $name_mod = join(' ', $name_array_modified);
        if (count($name_array_modified) == 1){
            $final_name = $saved_name;
        } else {
            $final_name = '>"'.$name.'" <('.$name_mod.')';
        }
        return $final_name;
    }

    private function cookie_management($number_per_page,$page_number){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $this->options = array_merge(array_diff($_POST,['']), array('paginator'=>$number_per_page,'offset'=>($page_number-1)*$number_per_page));
            // delete old cookies
            foreach ( $_COOKIE as $key => $value )
            { setcookie( $key, $value, time() - 3600, '/' ); }
            // set new cookies
            foreach($_POST as $cookie_name=>$cookie_value){
                setcookie($cookie_name, $cookie_value, time() + 300, "/");
            }
            if (isset($this->options['name'])){
                $this->options['name'] =$this->name_prepare($this->options['name']);
            }
            return $this->options;
        } else {
            return $this->options = array_merge($_COOKIE, array('paginator'=>$number_per_page,'offset'=>($page_number-1)*$number_per_page));
        }
    }

    public function actionIndex(){
        // create a stemmer (Rus)
        $stemmer = \Wamania\Snowball\StemmerFactory::create('ru');
        $number_per_page = 10;
        $page_number = 1;
        $checkbox_setup = $this->cookie_management($number_per_page, $page_number);
        $genres = $this->genres;
        $authors = $this->authors;
//        print_r($this->options);
        $table = $this->generate_table($this->options);
        $checkbox_setup = array_diff($checkbox_setup, [0, '']);
        require_once 'app/views/main.php';
        return true;

    }
}