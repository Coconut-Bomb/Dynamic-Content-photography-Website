<?php
// adds header.php to the topo f the page
  require "header.php"
?>

    <main>
      <script type="text/javascript">
      function checkform(){
        // sets up variables ready to recive data from the user
        var interests = 0;
        //gets all the names of the intrests the user has selected in intrests.php
        for (var i = 0; i < 8; i++) {
          //alert(document.getElementsByName("interests"+i).checked);
          if (document.getElementById(i).checked) {
            // add the intrest name to the array $interests_names
            interests = interests + 1;
          }
        }
        if (interests < 4) {
          $("#slackin-error").html("Please enter atleast 4 interests")
          return false;
        }
        else {
          return true;
        }
      }

      </script>
      <!main wrapper>
      <div class="wrapper-main">
        <!defual section for content>
        <section class="section-default">
          <!interests wrapper>
          <div class="wrapper-interests">
            <!interest title>
            <h2 class="interests-header">Add any interests you have to your profile</h2>
            <!form to submiut what intrests the user has selected>
            <form  action="extra/interests.ex.php" method="post" onSubmit="return checkform()">
              <!a row of interests>
              <div class="row-interests">
                <! a div with calass of "intrest" hold info about a single intrest >
                <div class="interest">
                  <!img of the intrest from file system>
                  <img src="img/interests/football.jpg" alt="football" >
                  <!chech box with the intrest name, the user can tick the tick box if they like>
                  <p><input id="0" type="checkbox" name="interests0" value="football"> Sports</p>
                </div>
                <div class="interest">
                  <img src="img/interests/puppies.jpeg" alt="">
                  <p><input id="1" type="checkbox" name="interests1" value="puppies"> Puppies</p>
                </div>
                <div class="interest">
                  <img src="img/interests/lambo.jpg" alt="lambo" >
                  <p><input id="2" type="checkbox" name="interests2" value="lamborghini"> Sports Cars</p>
                </div>
                <div class="interest">
                  <img src="img/interests/space.jpeg" alt="space" >
                  <p><input id="3" type="checkbox" name="interests3" value="space"> space</p>
                </div>

              </div>
              <!a row of interests>
              <div class="row-interests">

                <div class="interest">
                  <img src="img/interests/sailing.jpg" alt="Sailing" >
                  <p><input id="4" type="checkbox" name="interests4" value="sailing"> sailing</p>
                </div>
                <div class="interest">
                  <img src="img/interests/cooking.jpg" alt="Cooking" >
                  <p><input id="5" type="checkbox" name="interests5" value="cooking"> cooking</p>
                </div>
                <div class="interest">
                  <img src="img/interests/sunsets.jpg" alt="Sunset" >
                  <p><input id="6" type="checkbox" name="interests6" value="sunset"> Sunsets</p>
                </div>
                <div class="interest">
                  <img src="img/interests/spider.jpg" alt="Spider" >
                  <p><input id="7" type="checkbox" name="interests7" value="spiders"> spiders</p>
                </div>

              </div>
              <p class="form-error" style="margin-bottom:5px;" id="slackin-error"></p>
              <!div that holds the subit buttom along with the varible pass through inputs>
              <div class="interests-button-div">
                <! This input is invisable to the user and serves the purpose of adding data to the form that was passed into intrests.php>
                <!passes the Users Username to the form data>
                <input class="invisible" id="uid-passthrough" type="text" name="UserUid" value="ERROR">
                <!passes the Users Email to the form data>
                <input class="invisible" id="email-passthrough" type="text" name="Email" value="ERROR">
                <!passes the users Hashed password to the form data>
                <input class="invisible" id="pwd-passthrough" type="text" name="HashedPwd" value="ERROR">
                <!submit buttom which passes all form data to intrestes.ex.php through a POST meathod>
                <button type="submit" value="submit" name="submit"><p>SUBMIT</p></button>
              </div>

            </form>
          </div>

          </form>
        </section>
      </div>
      <script type="text/javascript">
      // this is the pass-through function, it sets the values of the pass-through inputs in the form to the data passed to intrests.php
      // it takes 3 paramaters UserUid, Email and HashedPwd, all passed into the page when it was loaded
        function var_passthrough(UserUid,Email,HashedPwd){
          // sets the value of the input with the id of uid-passthrough to the value passed into the func
          $('#uid-passthrough').val(""+UserUid)+"";
          // sets the value of the input with the id of email-passthrough to the value passed into the func
          $('#email-passthrough').val(Email);
          // sets the value of the input with the id of pwd-passthrough to the value passed into the func
          $('#pwd-passthrough').val(HashedPwd);
        };
      </script>
    </main>

<?php
// calls the var_passthrough func with the paramaters passed into intrests.php via the POST meathod
// also checks if these varblies have a value, if not user is redirected to home page
if (isset($_POST["UserUid"])) {
  echo "<script type='text/javascript'>",
  "var_passthrough('".$_POST["UserUid"]."','".$_POST["UserEmail"]."','".$_POST["HashedPwd"]."');",
  "</script>",
  "\n";
}
else {
  // user didnt fill out the form on signup and has accessed the page via direct url, user is sent back to haome page
  echo "<script type='text/javascript'>",
  "window.location.replace('index.php');",
  "</script>",
  "\n";
}
    // adds footer.php to the bottom of the page
      require "footer.php"
?>
