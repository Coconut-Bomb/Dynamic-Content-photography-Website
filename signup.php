<?php
// add header.php to top of page
  require "header.php"
?>

    <main>
      <script type="text/javascript">
      //updates signup form to show user the error
        function emptyfields() {
          $("#emptyfields-error").html("Please enter all the fields")
        };
        //updates signup form to show user the error
        function invaildmail() {
          $("#email-error").html("Invailid email address")
        };
        //updates signup form to show user the error
        function invailduid() {
          $("#uid-error").html("Invalid username please use _:()a-zA-Z0-9 and spaces only")
        };
        //updates signup form to show user the error
        function invalidpwd() {
          $("#pwd-error").html("Invalid password, password must only contain and at least 1 character from the sets a-z, A-Z, 0-9 and !£$%^&*_-=+. Password must be 8 characters or more")
        };
        //updates signup form to show user the error
        function pwdmissmatch() {
          $("#pwd-error").html("Passwords dont match")
        };
        //updates signup form to show user the error
        function uidtaken() {
          $("#uid-error").html("Username taken")
        };
        //updates signup form to show user the error
        function sqlerror() {
          $("#emptyfields-error").html("SQL Error Good Luck")
        };
        //updates signup form to show user the error
        function emailtaken() {
          $("#email-error").html("Email Taken")
        };
        //updates signup form to keep previous entered data
        function set_uid(uid) {
          $("#signup-form-uid").val(uid);
        };
        //updates signup form to keep previous entered data
        function set_email(email) {
          $("#signup-form-email").val(email);
        };
        //displays a success box to show
        function display_success(){
          $("#success-alert").css("display", "block");
          setTimeout(redirect,1500);
        };
        //Send the user back to the home page after the signup process is complete
        function redirect(){
          window.location.replace("index.php")
        }

        </script>
      </script>
      <!wrapper for all content below header>
      <div class="wrapper-main">
        <!the default section for content>
        <section class="section-default">
          <!Title for the page>
          <h1>Sign Up</h1>
          <!the form that the user fills out with the information needed to sign up along with the page location where this data is processed>
          <form class="form-t1" action="extra/signup.ex.php" method="POST">
            <!sign up success message normally hidden>
            <div id="success-alert"> SUCCESS </div  >
            <!username field>
            <input id="signup-form-uid" type="text" name="UserUid" placeholder="Username" value="">
            <!username field error message>
            <p class="form-error" id="uid-error"></p>
            <!email field>
            <input id="signup-form-email" type="text" name="Email" placeholder="Email" value="">
            <!email field error message>
            <p class="form-error" id="email-error"></p>
            <!password field>
            <input type="password" name="Pwd" placeholder="Password">
            <!second password field>
            <input type="password" name="Pwd-repeat" placeholder="Re-enter Password">
            <!password field error message>
            <p class="form-error" id="pwd-error"></p>
            <!form fields error message>
            <p class="form-error" id="emptyfields-error"></p>
            <!submit sing up button>
            <button type="submit" name="signup-submit">Sign Up</button>
          </form>
        </section>

      </div>

    </main>

<?php
// checks if signup was a success
  if (isset($_GET["signup"])) {
    if ($_GET["signup"] == "success"){

      //sets session variables
      $_SESSION["UserID"] = $_GET["UserID"];
      $_SESSION["UserUid"] = $_GET["UserUid"];

      //triggers display_success function
      echo '<script type="text/javascript">',
      'display_success();',
      '</script>',
      "\n";
    }
  }

  //if there was an error with the sign up
  if (isset($_GET["error"])){
    //checks error message and trigger the appropriate function to display the appropriate message to the user
    if ($_GET["error"] == "emptyfields"){
      echo '<script type="text/javascript">',
      'emptyfields();',
      '</script>',
      "\n";
    }
    //checks error message and trigger the appropriate function to display the appropriate message to the user
    elseif ($_GET["error"] == "invaildmailuid") {
      echo "<script type='text/javascript'>",
      "invaildmail();",
      "invailduid();",
      "</script>",
      "\n";
    }
    //checks error message and trigger the appropriate function to display the appropriate message to the user
    elseif ($_GET["error"] == "invailduid") {
      echo "<script type='text/javascript'>",
      "invailduid();",
      "</script>",
      "\n";
    }
    //checks error message and trigger the appropriate function to display the appropriate message to the user
    elseif ($_GET["error"] == "invalidpwd") {
      echo "<script type='text/javascript'>",
      "invalidpwd();",
      "</script>",
      "\n";
    }
    //checks error message and trigger the appropriate function to display the appropriate message to the user
    elseif ($_GET["error"] == "invaildmail") {
      echo "<script type='text/javascript'>",
      "invaildmail();",
      "</script>",
      "\n";
    }
    //checks error message and trigger the appropriate function to display the appropriate message to the user
    elseif ($_GET["error"] == "pwdmissmatch") {
      echo "<script type='text/javascript'>",
      "pwdmissmatch();",
      "</script>",
      "\n";
    }
    //checks error message and trigger the appropriate function to display the appropriate message to the user
    elseif ($_GET["error"] == "sqlerror") {
      echo "<script type='text/javascript'>",
      "sqlerror();",
      "</script>",
      "\n";
    }
    //checks error message and trigger the appropriate function to display the appropriate message to the user
    elseif ($_GET["error"] == "uidtaken"){
      echo "<script type='text/javascript'>",
      "uidtaken();",
      "</script>",
      "\n";
    }
    //checks error message and trigger the appropriate function to display the appropriate message to the user
    else{
      echo "<script type='text/javascript'>",
      "emailtaken();",
      "</script>",
      "\n";
    }
  }
  //checks if form field allreday have a value and then calles the appropriate function to automaticaly fill in the appropriate field in the form
  if (isset($_GET["uid"])) {
    echo "<script type='text/javascript'>",
    "set_uid('".$_GET['uid']."');",
    "</script>",
    "\n";

  }
  //checks if form field allreday have a value and then calles the appropriate function to automaticaly fill in the appropriate field in the form
  if (isset($_GET["email"])) {
    echo "<script type='text/javascript'>",
    "set_email('".$_GET['email']."');",
    "</script>",
    "\n";
  }
  // add footer.php to top of page
  require "footer.php"
?>
