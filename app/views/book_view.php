<!DOCTYPE html>
<html lang="en">
<head>
    <?php     include 'header.php';     ?>
    <title>Book</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@600&display=swap" rel="stylesheet">
    <link href="/app/static/css/book.css" rel="stylesheet" media="screen">
</head>
<body>
<?php   include 'navibar.php';
        echo
            '<div class="title">'.$title.'</div> 
<div class="wrapper">
    <div class="box img">
        <img alt="'
            .$title.
            '" src="/file_for/img_cop/'.
            $cover_img_ref .
            '" width="250" height="376">
    <div>'.$description.'
        <div class="authors/genres">
            <div class="button" id="button">authors/genres</div>
            <div id="collapse_id" class="detail_click">'.$author_genre.'</div>
        </div>
    </div>'
    ;  ?>



</div>


</div>

</body>

<script type="text/javascript" src="/app/static/js/book.js"></script>
</html>