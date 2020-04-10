<head>
    <?php
    $title = 'Main page';
    include 'header.php';
    echo "<title>" . $title . "</title>";
    ?>
</head>
<body>

<h1 class=" text-center">
    Book sales
</h1>



<div class="row justify-content-md-center" id="content">
<form action="index.php" method="post"
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="inputEmail4">Email</label>
            <input type="email" class="form-control" id="inputEmail4" placeholder="Email">
        </div>
        <div class="form-group col-md-6">
            <label for="inputPassword4">Password</label>
            <input type="password" class="form-control" id="inputPassword4" placeholder="Password">
        </div>
    </div>
    <div class="form-group">
        <label for="inputAddress">Address</label>
        <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St">
    </div>
    <div class="form-group">
        <label for="inputAddress2">Address 2</label>
        <input type="text" class="form-control" id="inputAddress2" placeholder="Apartment, studio, or floor">
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="inputCity">City</label>
            <input type="text" class="form-control" id="inputCity">
        </div>
        <div class="form-group col-md-4">
            <label for="inputState">State</label>
            <select id="inputState" class="form-control">
                <option selected>Choose...</option>
                <option>...</option>
            </select>
        </div>
        <div class="form-group col-md-2">
            <label for="inputZip">Zip</label>
            <input type="text" class="form-control" id="inputZip">
        </div>
    </div>
    <div class="form-group">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="gridCheck">
            <label class="form-check-label" for="gridCheck">
                Check me out
            </label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Sign in</button>
</form>
</div>

<?php
require __DIR__ . "/vendor/autoload.php";
$con = mysqli_connect("127.0.0.1", "root", "", "books");

// Check connection
if ($con->connect_errno) {
//    echo "Failed to connect to MySQL: " . $con->connect_error;
    exit();
}

$string = "Hello world";
$integer = 50;
$arc = array("BmW", "Something", "Index");
?>
<div class="container">
    <div class="row">
<!--        <div class="col-12">-->
            <table class="table table-image">
                <thead>
                <tr>
                    <th scope="col">Обложка</th>
                    <th scope="col">Название</th>
                    <th scope="col">Краткое описание</th>
                    <th scope="col">Автор</th>
                    <th scope="col">Жанр</th>
                </tr>
                </thead>
                <tbody>

<?php
include('element.php');
include('Book.php');
$a = new Book;

$a->read();
$ref = "/something";
$iitem=0;
while ($a->flag > 0){
    $iitem++;
    $temple = $a->read_row();
    $name =  $a->title;
    $description =  $a->description;
    $img =  $a->cover_img_ref;

    // IN SEPARATE FUNCTION
    $raw_genres =  explode(",",$a->genre);
    echo "<br><br>";
    foreach($raw_genres as $unit){
        $genre[$unit] = "/2";
    }
    $raw_authors =  explode(",",$a->author);
    echo "<br><br>";
    foreach($raw_authors as $unit){
        $author[$unit] = "/2";
    }
    element($img, $name, $description, $author, $genre, $ref);
    unset($author);
    unset($genre);
    if ($iitem>50){break;}
}

?>
                </tbody>
            </table>
<!--        </div>-->
    </div>
</div>
</body>
