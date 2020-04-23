<?php
require __DIR__ . "/vendor/autoload.php";

include('app/element.php');

$a = new \side\app\models\Book_managing("test_books");

$a->read();
$iitem=0;
while ($a->flag > 0){
    $iitem++;
    $temple = $a->read_row();
    $name =  $a->title;
    $description =  $a->description;
    $img =  $a->cover_img_ref;
    $ref = "/?full_name=".$name;
    // IN SEPARATE FUNCTION
    $raw_genres =  explode(",",$a->genre);
    echo "<br><br>";
    foreach($raw_genres as $unit){
        $genre[$unit] = "/?genre=".$unit;
    }
    $raw_authors =  explode(",",$a->author);
    echo "<br><br>";
    foreach($raw_authors as $unit){
        $author[$unit] = "/?author=".$unit;
    }
    element($img, $name, $description, $author, $genre, $ref);
    unset($author);
    unset($genre);
    if ($iitem>50){break;}
}

?>

