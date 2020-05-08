<?php
namespace side\app\models;

class Book_managing
{
    protected $_servername = "127.0.0.1";
    protected $_username = "root";
    protected $_password = "";
    protected $_dbname = "books";
    public $title;
    public $description;
    public $cover_img_ref;
    public $flag;
    public $genre;
    public $author;
    public $count_row;

// Common functions
    function __construct($_dbname = "books", $_servername = "127.0.0.1", $_username = "root", $_password = "")
    {
        $this->_servername = $_servername;
        $this->_username = $_username;
        $this->_password = $_password;
        $this->_dbname = $_dbname;
        $this->conn = $this->init();
        $this->flag = -1;// initial flag for reading
    }

    private function init()
    {
        // Create connection
        $conn = new \mysqli($this->_servername, $this->_username, $this->_password, $this->_dbname);
        // Check connection
        if ($conn->connect_error) {
            return "Connection failed: " . $conn->connect_error;
        } else {
            return $conn;
        }

    }

// Internal functions
    // functions for reading from sql (READ)
    private function results(){
        if ($this->flag > 0) {
            // output data of each row
            while ($row = $this->result->fetch_assoc()) {
                yield "1";
                $this->title = $row["title"];
                $this->description = $row["description"];
                $this->cover_img_ref = $row["cover_img_ref"];
                $this->genre = $row["genre"];
                $this->author = $row["author"];
                $this->count_row = $row["num_rows"];
                $this->flag--;
            }
        } else {
            $this->conn->close();
            $this->flag = -1;
            return "0 results";
        }
        $this->conn->close();
        $this->flag = -1;
    }
    // functions for additional request setting (READ)
    private function name_prepare(string $name) :string {
        // Create a stemmer (Rus)
        $stemmer = \Wamania\Snowball\StemmerFactory::create('ru');
        // Preparation of name
        $replacing = array("'"=>"`", "'\\n'"=>"\n", ','=>'', '.'=>'', ':'=>'', ';'=>'', '='=>'', '*'=>'' );
        $name_mod = strtr($name, $replacing);
        $name_array = explode(' ', $name_mod);
        // Replace an inflectional  by '*' by using lookahead case insensitive (  '/^.+(?=name)/i' ) and lookbehind case insensitive  '/(?<=name).+/i
        $set_name = function($value) use ($stemmer) {
            return preg_replace(['/^.+(?=' . $stemmer->stem($value) . ')/iu', '/(?<=' . $stemmer->stem($value) . ').+/iu'], ['*', '*'], $value);
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

    private function construct_filter(array $options){
        $string = array("author"=>"authors.author_name = '", "genre"=>"genres.genre_name = '", "name"=>"MATCH(name, description) AGAINST( '"); // the order in the string is the order of the filters in the SQL query

        if (isset($options['name'])){
            $options['name'] =$this->name_prepare($options['name']);
        }

        foreach ($string as $filter_name=>$value){
            $array_of_element = array_filter($options,function($var) use($filter_name) {return(explode('_', $var)[0]==$filter_name);}, ARRAY_FILTER_USE_KEY);
            $value_of_element = join('\' OR '.$value, $array_of_element);
            $string[$filter_name] = (!empty($value_of_element)) ? $string[$filter_name].$value_of_element.'\'' : ""; // Building a full filter (with concatenation with the end of expressions [sql string + ' ] for all existing filters)
        }
        $string = array_filter($string);
        $string = (!empty($string)) ? " WHERE ".join(" OR ",$string) : "";
        if (isset($options['name'])){ $string .= ' IN BOOLEAN MODE) ';} // Extra substring for cases when there is a (name, description) search
        return $string;
    }

    private function construct_sql(array $options){
        $string = $this->construct_filter($options);
        $sql_part1 = "SELECT books.name AS title, 
            books.description AS description, 
            books.img AS cover_img_ref, 
            GROUP_CONCAT(DISTINCT genres.genre_name) AS genre, 
            GROUP_CONCAT(DISTINCT authors.author_name) AS author
            ".(isset($options['name'])? ', MATCH(name, description) AGAINST( \''.$options['name'].' \' IN BOOLEAN MODE) AS rel ' :'');

        $sql_part3 = "FROM books 
            JOIN genre_book ON books.book_id = genre_book.book_id 
            JOIN genres ON genres.genre_id = genre_book.genre_id 
            JOIN author_book ON books.book_id = author_book.book_id 
            JOIN authors ON authors.author_id = author_book.author_id 
            ".$string;

        $sql_part2 = ", ( SELECT COUNT(DISTINCT(books.name)) AS cnt ".$sql_part3." ) AS num_rows "; // Add row counts to results

        $last_part = " GROUP BY books.book_id 
            ".(isset($options['name'])? " ORDER BY rel DESC":'')."
            LIMIT ".($options['paginator']?? 10).(" OFFSET ".$options['offset'].';'?? '0;');
        $sql = $sql_part1.$sql_part2.$sql_part3.$last_part;

        return $sql;
}
    // functions for inserting data to database (CREATE)
    private function unpacking($elements, string $element_type, string $name){
        # SQL query for including the element to table if it is not exists
        $sql_string_p1 = 'INSERT IGNORE INTO %1$ss(%1$s_name) VALUES (\'%2$s\');';

        # SQL query for creating of relation between element and the book
        $sql_string_p2 = 'INSERT INTO %1$s_book( book_id, %1$s_id) VALUES ( ( SELECT book_id FROM books WHERE name=\''.$name.'\'), (SELECT %1$s_id FROM %1$ss WHERE %1$s_name = \'%2$s\') ); ';

        $serial_sql_p1 = array();
        $serial_sql_p2 = array();
        # creation SQL strings, that will be include in the main SQL code
        foreach ($elements as $element){
            array_push($serial_sql_p1, sprintf($sql_string_p1, $element_type, $element));
            array_push($serial_sql_p2, sprintf($sql_string_p2, $element_type, $element));
        }
        return array($serial_sql_p1, $serial_sql_p2);
    }

// Public functions
    // function for genre/author filter (controller: MAIN)
    function get_list($element_name){
        $sql =  "SELECT ".$element_name."_name FROM ".$element_name."s;";
        $result = $this->conn->query($sql);
        return $result->fetch_all();
    }
    // read preparation function  (controllers: MAIN/BOOK) (READ)
    function read(array $options) {
        $sql = $this->construct_sql($options);
//        echo $sql;
        $result = $this->conn->query($sql);
        $this->flag = $result->num_rows?? 0;
        $this->result = $result;
        $this->read_generator = $this->results();
    }
    // Additional function for reading (controller: MAIN) (READ)
    function read_row(){
        if ($this->flag == -1){
            $this->read();
            $this->read_generator =$this->results();
        }
        $this->read_generator->next();
        return array("title"=>$this->title, "description"=>$this->description, "cover_img_ref"=>$this->cover_img_ref, "genre"=>$this->genre, "author"=>$this->author);

    }
    // function for TRUNCATE all tables (controller: CREATE) (DELETE)
    function clear_all(){
        $sql_list = array('DELETE FROM books;',' DELETE FROM authors;',' DELETE FROM genres;');
        foreach ($sql_list as $sql){
            $this->conn->query($sql);
        }
    }
    // function for INSERT information from web page/file to the database (controller: CREATE)
    function creation(string $name, string $img, string $description, array $genres, array $authors){
        //  clear data
        $replacing = array("'"=>"`", "'\\n'"=>"\n");
        $name = strtr($name, $replacing);
        $description = strtr($description, $replacing);

        list($genre_serialize_p1, $genre_serialize_p2) = $this->unpacking($genres, 'genre', $name); //# Genres
        list($author_serialize_p1, $author_serialize_p2) = $this->unpacking($authors, 'author', $name); //# Authors
        $main_sql = array("INSERT IGNORE INTO books(name ,img, description) VALUES ('".$name."', '".$img."', '".$description."');");
        $sql_com = array_merge($genre_serialize_p1, $author_serialize_p1, $main_sql, $genre_serialize_p2, $author_serialize_p2); // SQL string execute
        try{
            $this->conn->begin_transaction();
            foreach ($sql_com as $sql){
                $this->conn->query($sql);
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
        }
    }
}

?>