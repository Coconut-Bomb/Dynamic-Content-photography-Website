<?php
  // add header.php to top of page
  require "header.php"
?>

    <main>
      <script type="text/javascript">

      </script>
      <!main wrapper>
      <div class="wrapper-main">
        <!default section for content>
        <section class="section-default">
          <! wrapper div for all the profile content>
          <div class="wrapper-profile"></div>

        </section>
      </div>

    </main>

    <script type="text/javascript">
    // login prompt function, triggered if user is not logged in
    function login_prompt(){
      // appends a div to the wrapper-profile telling the user to log in
      $(".wrapper-profile").append('<div class="login_prompt" >Please Login to view your Profile</div>');

    }

    // reset password function, triggered on button click
    function reset_pwd_redirect(){
      UserID = <?php if (isset($_SESSION["UserID"])) {echo $_SESSION["UserID"];}else {echo "null";}?>; // sets UserID to this UsersID
      window.location.replace("reset_pwd.php?UserID="+UserID+""); // redirects the user to reset_pwd.php with the UserID in the url
    }
    // gets all profile info ready for it to be displayed, triggered if the user is logged in
    function get_profile(UserID){
      // create the frame work for the profile, so that info can be added into the correct element. Adds a personal-info, fav-img and friends list div
      // adds personal-info, fav-img, friends-list and time-line divs to the user-info div in the wrapper-profile div
      $(".wrapper-profile" ).append( "<h2>Profile:</h2><div class='user-info'><div class='personal-info'></div><div class='fav-img'></div><div class='friends-list'></div><div class='time-line'></div></div>" );

      var data = { // prepares data to form a request to DB_user_data_handler.ex.php to get the users profile infomation
        data_request: "GET PERSONAL INFO", // data request type
        UserID: UserID // Users ID
      }
      //sends a HTTP request to the server to retive the users data to form a profile
      $.post("extra/DB_Handlers/DB_user_data_handler.ex.php", data , function (resp) { //on response from server
        var data_array = JSON.parse(resp); // the servers response is parsed from JSON to a javascript array   [UserUid,Email]
          // appends a list of all important user details such as User ID, User Name and Email
          $(".personal-info" ).append( "<ul class='ul-no-style'><li>User ID "+UserID+"</li><li>User name: "+data_array[0]+"</li><li>Email: "+data_array[1]+"</li></ul>" );
          // add a button that can be used for the user to reset their password
          $(".personal-info" ).append( "<span class='button' style='margin-top: 5px;' onclick='reset_pwd_redirect()'>Reset Password</span>" );
      });

      // sets up data to get the info about the users fav images
      var data = {
        data_request: "GET FAV INFO", // request type
        UserID: UserID // Users ID
      }
      // sends a HTTP POST request to the server for DB_user_data_handler.ex.php
      $.post("extra/DB_Handlers/DB_user_data_handler.ex.php", data , function (resp) { //on response
        var data_array = JSON.parse(resp); // data is parsed from JSON
        // adds fav images title and a empty list to the profile
        $(".fav-img" ).append( "<h2>Fav Images</h2><ul id='fav-img-ul-element' class='ul-no-style' ></ul>" );
        for (var i = 0; i < data_array.length; i++) { // for the length of the respose
          //add a list item to the empty list (fav-img-ul-element) displaying the ImgID of the fav image
          $("#fav-img-ul-element" ).append( "<li>ImageID: "+data_array[i]+"</li>" );
        }
      });

      //prepares a data request from the server
      var data = {
        data_request: "GET FRIENDS", // request type
        UserID: UserID // User's ID
      }
      //sends a HTTP POST request for DB_user_data_handler.ex.php to the server
      $.post("extra/DB_Handlers/DB_user_data_handler.ex.php", data , function (resp) { //on server response
        var data_array = JSON.parse(resp); // parse the JSON response
        // adds friends list title and an empty list to the friends-list div
        $(".friends-list" ).append( "<h2>Friend List</h2><ul id='friend-list-ul-element' class='ul-no-style' ></ul>" );
        for (var i = 0; i < data_array.length; i++) {// for array in response (each friend in response)
          // adds a list item to the empty list (friend-list-ul-element) containing the ID of the friends as well as their username
          $("#friend-list-ul-element" ).append( "<li>ID: "+data_array[i][1]+"   Username: "+data_array[i][0]+"</li>" );
        }
      });
      // prepares a data reques from the server
      var data = {
        data_request: "GET TIMELINE",
        UserID: UserID
      }
      // sends a HTTP POST request to  DB_user_data_handler.ex.php to get the Users Time Line info
      $.post("extra/DB_Handlers/DB_user_data_handler.ex.php", data , function (resp) { //on response
        // parses the response into a javascript array
        var data_array = JSON.parse(resp);
        // add the Time Line header to the proflie
        $(".time-line" ).append( "<h2>Time line</h2><ul id='time-line-list-ul-element' class='ul-no-style' ></ul>" );
        // adds the account creation data to the profile
        $("#time-line-list-ul-element" ).append( "<li>"+data_array[0]+"</li>" );
        // if the user has no uploaded images
        if ( data_array[1].length == 0) {
          // adds a messae to the profile infomint the user that they have no user uploaded images
          $("#time-line-list-ul-element" ).append( "<li> You Currently Have No Uploaded Images. </li>" );
        }
        else {
          // for every post if the response
          for (var i = 0; i < data_array[1].length; i++) {
            // adds each item in the response arry to the profile page (Note response is allready formated with text and data)
            $("#time-line-list-ul-element" ).append( "<li>"+data_array[1][i]+"</li>" );
          }
        }
      });
    }

    <?php if (isset($_SESSION["UserID"])) {
      echo "get_profile(".$_SESSION["UserID"].")";
    }else {
      echo "login_prompt()";
    }?>

    </script>

<?php
// add footer.php to top of page
  require "footer.php"
?>
