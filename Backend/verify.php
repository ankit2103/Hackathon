<HTML>
    <head>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

</head>
<body>
<?php
if (isset($_GET["p"]) && !empty($_GET["p"])) {
    $token  = $_GET["p"];
   verifyemail($token);
} else {
    echo '<script> swal("Oh noes!", "Something Went Wrong!", "error");</script>';
}

function verifyemail($token)
{

    require('include/database.php');

    $query = mysqli_query($connect, "SELECT * FROM Users WHERE Email_Token = '$token'");

    $check = mysqli_num_rows($query);

    if ($check == 1) {

        $update  =  mysqli_query($connect, "UPDATE Users SET Email_Token = NULL ,VerifyEmailStatus = 1 WHERE Email_Token = '$token' ");

        $row = mysqli_affected_rows($connect);

        if ($row == 1) {
            
           echo '<script> swal("Verify Successfully", "Your email verified successfully !", "success");</script>';
        }
    } else {
        echo '<script> swal("Oh noes!", "Something Went Wrong!", "error");</script>';
    }
}
?>
</body>
</html>