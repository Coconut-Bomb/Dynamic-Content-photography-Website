<?php
// if the submit button was pressed
$_POST["submit"] = "ff";
if (isset($_POST["submit"])){
  //$intrest_elem is set to default value of 0
  $intrest_elem=0;
  // sets up variables ready to recive data from the user
  $interests = [];
  $interests_names = [];
  //gets all the names of the intrests the user has selected in intrests.php
  while ($intrest_elem < 8) { //8 is intrest avalibe to pick from in intrest.php
    // if this intrest was send over from intrest.php (ie if the user has picked this intrest)
    if (isset($_POST["interests".$intrest_elem])) {
      // add the intrest name to the array $interests_names
      array_push($interests_names,$_POST["interests".$intrest_elem]);
    }
    // increment $intrest_elem to check the next possilbe intrest
    $intrest_elem++;
  }

  // for each intrest the user has selected
  for ($pointer=0; $pointer < sizeof($interests_names); $pointer++) {
    // add an array containing the intrest name along with the intrest rate to the 2D array $interests
    // the total amout of intrest rate for any user will allways be equal to 1, at first it is spread evenly between all selected intrests
    array_push($interests,[$interests_names[$pointer],(1/sizeof($interests_names))]);
  }

  // prepares data to be entered into the databse
  $InterestArray = serialize($interests);
  $UserUid = $_POST["UserUid"];
  $UserEmail = $_POST["Email"];
  $HashedPwd = $_POST["HashedPwd"];

  // gets all the functions and variables from Multi_DB_Handler.db.php, needed for database querying
  require "DB_Handlers/Multi_DB_Handler.db.php";
  // creates the new users account, and adds all the data to the new account
  $sql = "INSERT INTO users(UserUid,UserEmail,UserPwd,InterestArray,signup_date) VALUES(?,?,?,?,?) ";
  $stmt = mysqli_stmt_init($users_conn);
  //if sql stmt failed
  if (!mysqli_stmt_prepare($stmt,$sql)) {
    header("Location: ../fatal_error.php?error=sqlerror");
    exit();
  }
  // enter details into DB
  else {
    mysqli_stmt_bind_param($stmt,"sssss",$UserUid, $UserEmail,$HashedPwd,$InterestArray,date('M,d,Y h:i A'));
    mysqli_stmt_execute($stmt);

    $result = SELECT("UserID","users","UserUid",$UserUid,"s",$users_conn,False);
    // redirects the user back to signup.php with a success message anf the new Users username and UserId in the url
    header("Location: ../signup.php?signup=success&UserUid=".$UserUid."&UserID=".$result[0]);
    exit();
  }

  mysqli_stmt_close($stmt);
  mysqli_stmt_close($users_conn);
  
}
?>
