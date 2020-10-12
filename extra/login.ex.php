<?php
// if login button has been pressed
if (isset($_GET["login-submit"])) {
  // requires DB_Handlers/Multi_DB_Handler.db.php and all of the functions inside of it
  require "DB_Handlers/Multi_DB_Handler.db.php";
  // gets the users inputed username and password
  $UserUid = $_GET["UserUid"];
  $Pwd = $_GET["Pwd"];
  //inputs not set
  if (empty($UserUid) || empty($Pwd) ){
    // redirect the user back to index.php with an error message
    header("Location: ../index.php?login-error=empty-fields");
    exit();
  }

  else{
    // queries the database if a User exsists with the username this user has entered
    $sql = "SELECT * FROM users WHERE UserUid = ?"; //prepared statment (SQL injection)
    $stmt = mysqli_stmt_init($users_conn);
    //if sql stmt failed
    if (!mysqli_stmt_prepare($stmt,$sql)) {
      header("Location: ../fatal_error.php?error=sql-error");
      exit();
    }
    //searches DB if uid is exsists
    else {
      mysqli_stmt_bind_param($stmt,"s",$UserUid);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      //if data is returned
      if ($row = mysqli_fetch_assoc($result)) {
        // password_verify hashes the inputed password using the salt from the allready hashed password and compares the 2 results, if they are identical the 2 passwords match
        if (password_verify($Pwd, $row["UserPwd"])) {
          // the passwrod the user entered was correct
          // starts a new session
          session_start();
          // sets session data to the data retrived from the data base
          $_SESSION["UserID"] = $row["UserID"];
          $_SESSION["UserUid"] = $row["UserUid"];
          // redirects user to index.php with a success message
          header("Location: ../index.php?login=success");
        }
        else  {
          // redirects user to index.php with a error message
          header("Location: ../index.php?login-error=invalid-pwd");
          exit();
        }
      }
      else{
        // redirects user to index.php with a error message
        header("Location: ../index.php?login-error=username-notfound");
        exit();
      }
    }

  }
  mysqli_stmt_close($stmt);
  mysqli_stmt_close($conn);
}
//login submit not set
else {
  // redirects user to index.php
  header("Location: ../index.php");
}


?>
