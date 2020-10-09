<?php require_once('../vendor/autoload.php');

// Connect to DB
$con = mysqli_connect("localhost", "root", "", "ndp_guestbook");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// This stops SQL Injection in POST vars 
foreach ($_POST as $key => $value) {
    $_POST[$key] = mysqli_real_escape_string($con, $value);
}

// This stops SQL Injection in GET vars 
foreach ($_GET as $key => $value) {
    $_GET[$key] = mysqli_real_escape_string($con, $value);
}

// This is where I define the name for the app
define("APP_NAME", "Modern Guestbook");

// assign the current date
$current_date = date("Y-m-d");

if (isset($_POST['submit'])) {
    // DECLARE VARIABLES
    $valid = 1;
    $msgPreError = "<div class=\"alert alert-danger\" role=\"alert\">";
    $msgPreSuccess = "<div class=\"alert alert-success\" role=\"alert\">";
    $msgPost = "</div>";
    $add_name = trim($_POST['addname']);
    $add_email = trim($_POST['addemail']);
    $add_comment = trim($_POST['addcomment']);
    // echo "$title, $message";

    // VALIDATION
    // check name
    if ((strlen($add_name) < 3) || (strlen($add_name) > 20)) {
        $valid = 0;
        $valNameMsg = "Please enter a name 3 and 20 characters.";
    }

    // check email format
    if ($add_email != "") {
        $valid = 0;
        $add_email = filter_var($add_email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($add_email, FILTER_VALIDATE_EMAIL)) {
            $valEmailMsg .= "\n<br />Please enter a correct email.";
        } else {
            $valid = 1;
        }
    } else {
        $valid = 0;
        $valEmailMsg .= "\n<br />Please enter an email address.";
    }

    // check comment
    if ((strlen($add_comment) < 3) || (strlen($add_comment) > 250)) {
        $valid = 0;
        $valCommentMsg = "Please enter a comment between 3 and 250 characters.";
    }

    // SUCCESS. 
    // If our boolean is still 1 then user form data is good.
    if ($valid == 1) {
        $msgSuccess = "Success! The form data has been stored.";
        mysqli_query(
            $con,
            "INSERT INTO message(
				name,
                date,
                email, 
				comment) 
			VALUES('$add_name',
                '$current_date',
                '$add_email', 
				'$add_comment')"
        ) or die(mysqli_error($con));
        // RESET
        $add_name = "";
        $add_email = "";
        $add_comment = "";
    }
}

// This creates an array of all lines in the specified table; using the $con variable from earlier, I can view the tables' data
$result = mysqli_query($con, "SELECT * FROM message ORDER BY mid DESC $limstring");

?>

<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <title><?= APP_NAME ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>

<style>
    body {
        background: #d4f6ff;
        max-width:1200px;
    }

    .book-post {
        background-color: #fcd2cd;
        width: calc(100vw * 0.85) !important;
        max-width:920px;
        margin: 0 auto;
        margin-left:  calc(-100vw * 0.032) !important;
        padding: 0;
        border: 1px solid black;
        box-shadow: 0 0 5px 0 gray;
        border-radius: 5px;
        margin-bottom: 1rem;
        padding-top: .9rem;
        padding-right: 2rem;
        padding-left: 2rem;
    }
</style>

<body class="d-flex flex-column h-100">
    <main role="main" class="flex-shrink-0">
        <div class="container">
            <a style="float:right" href="/mysql" class="btn btn-primary" target="_blank">MySQL Admin</a>
            <h1 class="mt-5"><?= APP_NAME ?></h1>
            <p>A modern Geocities guestbook...</p>


            <!-- Display all messages in a list -->
            <div class="">
                <ul class="" style="list-style-type: none;">
                    <?php while ($row = mysqli_fetch_array($result)) : ?>
                        <?php
                        // format the date for future use with style
                        $myDay = strtotime($row['date']);
                        $day = date('d', $myDay);
                        $myYear = strtotime($row['date']);
                        $year = date('Y', $myYear);
                        $myMonth = strtotime($row['date']);
                        $month = date('M', $myMonth);

                        /* echo to test */
                        // echo "$day, $month, $year";

                        // assign each row value to a variable
                        $mid = $row['mid'];
                        $date = $row['date'];
                        $name = $row['name'];
                        $email = $row['email'];
                        $comment = $row['comment'];
                        ?>

                        <li class="book-post">
                            <div class="row">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="row d-flex justify-content-between">
                                                <div>
                                                    <h5 style="margin-bottom:-0.2rem;"><?php echo $name ?></h5>
                                                    <p><?php echo $date ?></p>
                                                </div>
                                                <div>
                                                    <p class="pr-3"><a href = "mailto: <?php echo $email ?>">Email</a></p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <p><?php echo $row['comment']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                    <?php endwhile; ?>
                </ul>
            </div>

            <!-- Sign the guestbook / Add a message to the database -->
            <h2>Leave a Comment</h2>


            <form class="pb-5" id="myform" name="myform" method="post">
                <div class="form-group">
                    <label for="ct">Current Date:</label>
                    <span class="form-control" readonly><?php echo $current_date ?></span>
                </div>
                <div class="form-group">
                    <label for="addname">Name:</label>
                    <input type="text" name="addname" class="form-control" value="<?php if ($add_name) : ?><?php echo $add_name; ?><?php endif; ?>">
                    <?php if ($valNameMsg) : ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $valNameMsg; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="addemail">Email:</label>
                    <input type="text" name="addemail" class="form-control" value="<?php if ($add_email) : ?><?php echo $add_email; ?><?php endif; ?>">
                    <?php if ($valEmailMsg) : ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $valEmailMsg; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="addcomment">Comment:</label>
                    <textarea name="addcomment" class="form-control"><?php if ($add_comment) : ?><?php echo $add_comment; ?><?php endif; ?></textarea>
                    <?php if ($valCommentMsg) : ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $valCommentMsg; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group mb-5">
                    <br />
                    <label for="submit">&nbsp;</label>
                    <input type="submit" name="submit" class="btn btn-success" value="Sign">
                </div>
            </form>

            <!-- <a href="/" target="_blank">Open New Window</a> -->
        </div>
    </main>
</body>

</html>