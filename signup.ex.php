<?php
//if users gained access to page thtough submit button
if (isset($_POST["signup-submit"])) {
  // requires DB_Handlers/Multi_DB_Handler.db.php allowing access to all the functions inside
  require "DB_Handlers/Multi_DB_Handler.db.php";
  // Grabing data from URL Through POST
  $uid = $_POST["UserUid"];
  $email = $_POST["Email"];
  $pwd = $_POST["Pwd"];
  $pwdRepeat = $_POST["Pwd-repeat"];

  //if field have not been filled in
  if (empty($uid) || empty($email) || empty($pwd) || empty($pwdRepeat)) {
    // redirect the user back to signup.php with a error message
    header("Location: ../signup.php?error=emptyfields&uid=".$uid."&email=".$email);
    exit();
  }
  //checks for a vailid EMAIL and uid
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !preg_match("/^[_:()a-zA-Z0-9\s]*$/",$uid) ) {
    // redirect the user back to signup.php with a error message
    header("Location: ../signup.php?error=invaildmailuid");
    exit();
  }
  //checks for a vailid EMAIL
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // redirect the user back to signup.php with a error message
    header("Location: ../signup.php?error=invaildmail&uid=".$uid);
    exit();
  }
  //checks if the uid only contain allowed chars
  elseif (!preg_match("/^[_\-:()a-zA-Z0-9\s]*$/",$uid)) {
    // redirect the user back to signup.php with a error message
    header("Location: ../signup.php?error=invailduid&email=".$email."&uid=".$uid);
    exit();
  }
  //cheaks if passwords match
  elseif ($pwd != $pwdRepeat) {
    // redirect the user back to signup.php with a error message
    header("Location: ../signup.php?error=pwdmissmatch&uid=".$uid."&email=".$email);
    exit();
  }
  //checks if the pwd only contain allowed chars and contains at least 1 character from the sets {a-z} {A-Z} {0-9} {!£$%^&*_-=+} and is also at least 8 characters long
  elseif (preg_match("/^[\!\£\$\%\^\&\*_\-\=\+a-zA-Z0-9\s]+$/",$pwd) && mb_strlen($pwd) > 7 && preg_match("/[\!\£\$\%\^\&\*_\-\=\+]+/",$pwd) && preg_match("/[a-z]+/",$pwd) && preg_match("/[A-Z]+/",$pwd) && preg_match("/[0-9]+/",$pwd)) {

    // cheaks if uid or email allready exsists in DB and if not enters details into DB
    $sql = "SELECT UserUid FROM users WHERE UserUid = ?"; //prepared statment (SQL injection)
    $stmt = mysqli_stmt_init($users_conn);
    //if sql stmt failed
    if (!mysqli_stmt_prepare($stmt,$sql)) {
      header("Location: ../fatal_error.php?error=sqlerror");
      exit();
    }
    //searches DB if uid is taken
    else {
      mysqli_stmt_bind_param($stmt,"s",$uid);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_store_result($stmt);
      $resultCheck = mysqli_stmt_num_rows($stmt);
      //if uid is taken
      if ($resultCheck > 0) {
        header("Location: ../signup.php?error=uidtaken&email=".$email);
        exit();
      }
      // uid is free and email is about to be cheaked if free
      else {
        $sql = "SELECT UserEmail FROM users WHERE UserEmail = ?"; //prepared statment (SQL injection)
        $stmt = mysqli_stmt_init($users_conn);
        //if sql stmt failed
        if (!mysqli_stmt_prepare($stmt,$sql)) {
          header("Location: ../fatal_error.php?error=sqlerror");
          exit();
        }
        //searching data base if email taken
        else {
          mysqli_stmt_bind_param($stmt,"s",$email);
          mysqli_stmt_execute($stmt);
          mysqli_stmt_store_result($stmt);
          $resultCheck = mysqli_stmt_num_rows($stmt);
          //if email allready taken
          if ($resultCheck > 0) {
            header("Location: ../signup.php?error=emailtaken&uid=".$uid);
            exit();
          }
          //preparing to enter details into DB
          else {
            // hashes the user entered password using the Defulat hashing algorithm by php (bcrypt algorithm)
            $HashedPwd =  password_hash($pwd, PASSWORD_DEFAULT);
            // creats a form and auto fills the users username, email and hashed password, and then automaticaly submits the form to interests.php
            echo '<!DOCTYPE html>',
                    '<html>',
                      '<form id="myForm" action="../interests.php" method="post">',
                        '<input type="hidden" name="UserUid" value="'.$uid.'">',
                        '<input type="hidden" name="UserEmail" value="'.$email.'">',
                        '<input type="hidden" name="HashedPwd" value="'.$HashedPwd.'">',
                      '</form>',
                      '<script type="text/javascript">',
                      'document.getElementById(\'myForm\').submit();',
                      '</script>',
                    '</html>';
            exit();
          }
        }
      }
    }
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($users_conn);

  }
  else {
    // redirect the user back to signup.php with a error message
    header("Location: ../signup.php?error=invalidpwd&uid=".$uid."&email=".$email);
    exit();
  }



}

?>
