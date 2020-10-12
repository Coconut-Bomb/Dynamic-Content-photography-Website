<?php
// stars or resumes a session
session_start();
?>

<!DOCTYPE html>
<html>
  <head>
    <!meta data for the site>
    <meta charset="utf-8">
    <meta name=viewport content="width=device-width, initial-scale=1">
    <!Websire title>
    <title>Delmar's Image Site</title>
    <!links the style sheet>
    <link href="style.css" rel="stylesheet" type="text/css">
    <!links the favicon>
    <link rel="shortcut icon" type="image/icon" href="favicon.ico"  />
    <!links the jQuery libary>
    <script src="extra\resources\jquery-3.4.1.js"></script>



  </head>
  <body>

    <header>
      <! nav bar tag>
      <nav class="nav-header-main">
        <!logo with link to home page>
        <a class="header-logo"herf="index.php">
          <!logo>
          <img src="img\logo\logo-800x800.png" alt="logo">
        </a>
        <!nav bar list>
        <ul>
          <!Home page>
          <li><a href="index.php">Home</a></li> <!class="active">
          <!profile page>
          <li><a href="profile.php">Profile</a></li>
          <!About page>
          <li><a href="about.php">About</a></li>
          <!messages page>
          <li><a href="messages.php">Messages</a></li>
        </ul>
        <!upload-img div >
        <div id="upload-img" class="header-right">
          <!form to upload image button>
          <form id="upload-img-form" action="upload.php" method="POST" enctype="multipart/form-data">
            <!upload image button, onclick redirects user to upload.php>
            <button type="submit" value="upload-submit" name="submit">Upload Image</button>
          </form>
        </div>
        <!login div>
        <div id="header-login"class="header-right">
          <! login form>
          <form id="login-form" action="extra/login.ex.php" meathod="post">
            <!User name input>
            <input type="text" name="UserUid" placeholder="Username"/>
            <!Password input >
            <input type="password" name="Pwd" placeholder="password"/>
            <!login form submit button>
            <button type="submit" name="login-submit" value="submit">Log in</button>
          </form>
          <! sign up button, onclick redirects user to signup.php>
          <a id="signup-link"href="signup.php">Sign Up</a>

          <!log out form>
          <form action="extra/logout.ex.php" meathod="GET">
            <!logout buton>
            <button type="submit" name="logout-submit" value="submit">Log out</button>
          </form>

        </div>


      </nav>
      <script type="text/javascript">
        // show_login_error is a function that is called when there is an unsuccessfull login attempt
        function show_login_error(){
          // add a error message in the header-login div telling the user what the error was
          $('<p id="header-login-error-mess">'+'<?php  if(isset( $_GET["login-error"])){echo $_GET["login-error"];} ?>'+'</p>').insertBefore("#login-form");
        }
        // remove_login is a function that is called when the user is loggin
        function remove_login(){
          // hides the login form
          $("#login-form").css("display", "none");
          // hides the sign up form
          $("#signup-link").css("display", "none");
        }
        // add_upload is a function that is called when the user is logged in
        function add_upload(){
          // shows the upload-img-form form
          $("#upload-img-form ").css("display", "flex");
          // moves header-login div out of the way as it is no longer beening displayed
          $("#header-login").css("margin-left", "0px");
        }



      </script>

      <?php
      // checks i the user is login or not
      if (isset($_SESSION["UserID"])) {
        // calls the functions remove_login and add_upload
        echo '<script type="text/javascript">',
        'remove_login();',
        'add_upload();',
        '</script>',
        "\n";
      }
      // checks is there is a login error in url
      if (isset($_GET["login-error"])) {
        // calls function show_login_error if an error is presant
        echo '<script type="text/javascript">',
        'show_login_error();',
        '</script>',
        "\n";
      }
      ?>

    </header>
