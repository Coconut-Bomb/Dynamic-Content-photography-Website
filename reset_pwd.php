<?php
// add header.php to top of page
  require "header.php"
?>

    <main>
      <script type="text/javascript">
        // reset password function triggered on submit button
        function reset_pwd(){
          $("#pwd-error").empty(); //resets all display messages ready for the next attempt
          $("#pwd-missmatch").empty();
          $("#success-alert").css("display","none");

          pwd1 = document.getElementById("old-pwd").value;// gets values of the user input fields
          pwd2 = document.getElementById("new-pwd1").value;
          pwd3 = document.getElementById("new-pwd2").value;

          if (pwd1 == "" || pwd2 == "" || pwd3 == "") { // if any of the fields have been left empty
            $("#pwd-missmatch").html("Empty Fields"); // display an error message to the user
            document.getElementById("old-pwd").value = "";// user input fields are rest to empty, and the request is not sent
            document.getElementById("new-pwd1").value = "";
            document.getElementById("new-pwd2").value = "";
          }
          else if (pwd2 != pwd3) { // if the new entered passwords do not match
            $("#pwd-missmatch").html("Passwords Do Not Match"); // display an error message to the user
            document.getElementById("new-pwd1").value = ""; // restest the user input fields ready for a new attempt
            document.getElementById("new-pwd2").value = "";
          }
          else {
            // prepares the data for a request to reset the users password
            var data = {
              data_request: "RESET PWD", // data request type
              UserID: <?php if (isset($_SESSION["UserID"])) {echo $_SESSION["UserID"];}else {echo "null";}?>, // UserID
              pwd1: pwd1,// current password entered my user
              pwd2: pwd2 // new password entered by user
            }
            console.log("Send reset request");
            console.log(data);

            $.post("extra/DB_Handlers/DB_user_data_handler.ex.php", data , function (resp) { //resp = response
              console.log(resp);
              if (resp == "success") { // is the server responded with success
                $("#success-alert").css("display","block");// displays a success message to the user
              }else if (resp == "no match") { // if a password no mathch error message
                $("#pwd-error").html("Password is Incorrect"); // displayed error message to the user
                document.getElementById("old-pwd").value = ""; // empties the input field of the password
              }else if (resp == "pwd2 invalid") {// if new password is invalid
                $("#pwd-missmatch").html("New Password doesn't meet requirements"); // displays error message to user
              }

            });
          }
        }

      </script>
      <!wrapper for all content below header>
      <div class="wrapper-main">
        <!the default section for content>
        <section class="section-default">
          <!Title for the page>
          <h1>Reset Password</h1>
          <!the form that the user fills out with the information needed to sign up along with the page location where this data is processed>
          <div class="form-t1" >
            <!sign up success message normally hidden>
            <div id="success-alert"> SUCCESS </div  >
            <!current password input>
            <input id="old-pwd" type="password" name="Pwd1" placeholder="Current Password" value="">
            <!password error display>
            <p class="form-error" id="pwd-error"></p>
            <!new password input>
            <input id="new-pwd1" type="password" name="Pwd2" placeholder="New Password" value="">
            <!new password input>
            <input id="new-pwd2" type="password" name="Pwd3" placeholder="Re-enter New Password">
            <!new password mismatch error display>
            <p class="form-error" id="pwd-missmatch"></p>
            <!submit button, triggers reset_pwd() >
            <button type="submit" onclick="reset_pwd()" >Reset</button>
          </div>
        </section>

      </div>

    </main>

<?php
// add footer.php to top of page
  require "footer.php"
?>
