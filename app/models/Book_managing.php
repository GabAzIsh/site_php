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

    function get_list($element_name){
        $sql =  "SELECT ".$element_name."_name FROM ".$element_name."s;";
        $result = $this->conn->query($sql);
        return $result->fetch_all();

    }

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
                $this->flag--;
            }
        } else {
            return "0 results";
        }
        $this->conn->close();
        $this->flag=-1;
    }

    function read(array $options=[]) {
        $string = array("author"=>"authors.author_name = '", "genre"=>"genres.genre_name = '", "name"=>"MATCH(name, description) AGAINST( '"); // the order in the string is the order of the filters in the SQL query
        foreach ($string as $filter_name=>$value){
            $string[$filter_name] = (!empty($options[$filter_name])) ? $string[$filter_name].$options[$filter_name].'\'' : ""; // Building a full filter (with concatenation with the end of expressions [sql string + ' ] for all existing filters)
        }
        $string = array_filter($string);
        $string = (!empty($string)) ? " WHERE ".join(" AND ",$string) : "";
        if (isset($options['name'])){ $string .= ') ';} // Extra substring for cases when there is a (name, description) search
        $sql =
            "SELECT books.name AS title, 
            books.description AS description, 
            books.img AS cover_img_ref, 
            GROUP_CONCAT(DISTINCT genres.genre_name) AS genre, 
            GROUP_CONCAT(DISTINCT authors.author_name) AS author
            FROM books 
            JOIN genre_book ON books.book_id = genre_book.book_id 
            JOIN genres ON genres.genre_id = genre_book.genre_id 
            JOIN author_book ON books.book_id = author_book.book_id 
            JOIN authors ON authors.author_id = author_book.author_id 
            ".$string."
            GROUP BY books.book_id;";
        $result = $this->conn->query($sql);
        $this->flag = $result->num_rows;
        $this->result = $result;
        $this->read_generator = $this->results();
    }

    function read_row(){
        if ($this->flag == -1){
            $this->read();
            $this->read_generator =$this->results();
        }
        $this->read_generator->next();
        return array("title"=>$this->title, "description"=>$this->description, "cover_img_ref"=>$this->cover_img_ref, "genre"=>$this->genre, "author"=>$this->author);

    }
    function clear_all(){
        $sql_list = array('DELETE FROM books;',' DELETE FROM authors;',' DELETE FROM genres;');
        foreach ($sql_list as $sql){
            $this->conn->query($sql);
        }
    }

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

