<?php


class Book
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

    function __construct($_servername = "127.0.0.1", $_username = "root", $_password = "", $_dbname = "books")
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
        $conn = new mysqli($this->_servername, $this->_username, $this->_password, $this->_dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
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

    function read($options=[]) {
        $string = array("title"=>"book.title = ", "genre"=>"book_genre.book_genre_name = ", "author"=>"book_author.book_author_name =  ");
        foreach ($string as $filter_name=>$value){
            $string[$filter_name] = (!empty($options[$filter_name])) ? $string[$filter_name].$options[$filter_name] : "";
        }
        $string = array_filter($string);
        $string = (!empty($string)) ? " WHERE ".join(" AND ",$string) : "";

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
        $this->read_generator =$this->results();
    }

    function read_row(){
        if ($this->flag == -1){
            $this->read();
            $this->read_generator =$this->results();
        }
        $this->read_generator->next();
        return array("title"=>$this->title, "description"=>$this->description, "cover_img_ref"=>$this->cover_img_ref, "genre"=>$this->genre, "author"=>$this->author);

    }
}

?>
<?php
/* Example:
  $a = new Book;

$a->read();
while ($a->flag > 0){
    $a->read_row();
    echo $a->title."<br><br>";
    echo $a->description."<br><br>";
    echo $a->cover_img_ref."<br><br>";
} */
//    $destruct = 0;
//    // breaker 10
//    if ($destruct == 10){break;}
//    $destruct++;

?>
