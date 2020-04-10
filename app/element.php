<?php

function element($img, $name, $description, $author, $genre, $ref){

echo '<tr><td class="w-25"><a href="'.$ref.'"><img class="lt32 lazy" alt="Гарри Поттер и Кубок огня" src="./img/' .
    $img .
    '.jpg" style="display: inline;" width="250" height="376"></td><td><a href="'.$ref.'">' .
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


function registration_parser(){

}



?>


