<!DOCTYPE html>
<html lang="en">
<head>
    <?php     include 'header.php';    ?>
    <title>Main</title>
</head>
<body>
    <div class="row" style="height: 80px">
        <?php  include 'navibar.php'; ?>
    </div>
    <div class="container">

        <?php  echo isset($alert) ? '
        <div class="alert alert-success" role="alert">
            <strong> '.$alert.' </strong>
        </div>': '' ?>

        <h2>Stacked form</h2>
        <form action="/create" method="POST">
            <div class="form-group">
                <label for="address">Input the address:</label>
                <input type="text" class="form-control"  id="address" placeholder="Leave blank (https://www.litmir.me/bs) " name="url_name">
            </div>
            <div class="form-group row">
                <label for="example-number-input" class="col-2 col-form-label">Number [25-100]</label>
                <div class="col-10">
                    <input class="form-control" style="align-self: center" type="number" value="25" name="quantity" min="20" max="100" id="example-number-input">
                </div>
            </div>
            <div class="form-group form-check">
                <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" name="FLAG"> Read from file
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Parse</button>
        </form>
    </div>
</body>
</html>