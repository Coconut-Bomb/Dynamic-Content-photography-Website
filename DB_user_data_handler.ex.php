<?php
  // gets Multi_DB_handler.db.php and all of its functions
  require "Multi_DB_handler.db.php";

  // if data_request is set
  if (isset($_POST["data_request"])) {
    // if data_request is IMG CLICK. This code is designed to adjust the users intrests to increase the intrest rate for the tag of the image they clicked on
    // (ie if they clicked on a picture of a puppy, increase the intrest rate for puppies in the users intrests stored in the "users" table, althought the total amout of intrest rate
    // must remain at 1, therefore the intrest rate for evey intrest in the users intrests that is not puppies must be lowered)
    if ($_POST["data_request"]=="IMG CLICK") {

      if (!isset($_POST["UserID"]) || !isset($_POST["ImgID"])) {
        // no action request was mallformed.
      }else {
        // gets variables from POST
        $UserID = $_POST["UserID"];
        $ImgID = $_POST["ImgID"];

        // uses a custom sql statment to get the Tag of the image with the given ImgID, the image_data and tags tables are used here
        $custom_sql = "SELECT tags.Tag FROM `tags`, `image_data` WHERE image_data.ImgID = ".$ImgID." AND tags.TagID = image_data.TagID";
        $result = SELECT(false,false,false,false,false,$images_conn,$custom_sql);
        $tag = $result[0]["Tag"];

        // gets the interest array for the User ID send in data
        $result = SELECT("InterestArray","users","UserID",$UserID,"i",$users_conn,False);
        $user_interests = $result[0];
        // unserializes $user_interests to a php array
        $user_interests = unserialize($user_interests); // example [["cats", 0.25],["dogs",0.25],["cows",0.25],[intrest,intrest rate (0.25)]]
        // $user_interests is a 2D array, each array inside of $user_interests contains the intrest (as a string) and the corresponding intest rate (as float)
        // sets up variables ready to search $user_interests or the Tag

        $index = 0;
        $found = false;
        // for each array in users intrests
        for ($i=0; $i < sizeof($user_interests) ; $i++) {
          // if tag is in $user_interests[$i]
          if (in_array($tag,$user_interests[$i])) {
            //sets variables given the tag has been found in the users intrests
            $found = true;
            $index = $i;
          }
        }  // 1 click = 0.02 added intrest rate of the intrest that is equal to the tag of the image clicked on. This is taken evenly from allother intretss where possilbe, no intrest
        // rate can go below 0, if subtracting the nessasary amout takes the intrest rate below 0 it is removed from user_interests and what ever intrest rate it had is releasd
        // (added to $released_interests)
        //amount of intrest rate released from other intrests in $user_interests
        $released_interests = 0;
        $array_size = sizeof($user_interests);
        // if tag was found in $user_interests
        if ($found) {
          // for each array in $user_interests
          // note $array_size is used as the limit in the for loop as  sizeof($user_interests) will change its value after one of the arrays are unset
          for ($i=0; $i < $array_size ; $i++) {
            // only if this array in $user_interests is not the array that contains the tag
            if ($i != $index) {
              // checks if subtracting the necessary amount from this intrest rate takes it below 0
              if (($user_interests[$i][1] - (0.02/($array_size-1))) < 0) {
                // if so this intrest is removed from $user_interests and what ever intrest rate it had is then added to $released_interests (this keeps the total amout of intrest rate equal to 1 )
                $released_interests = $released_interests + $user_interests[$i][1];
                unset($user_interests[$i]);
              }
              else {
                // if not the the amout subracted from this intrest rate is added to $released_interests
                $released_interests = $released_interests + (0.02/($array_size-1));
                // and the the nessasary amout is then subrtacted from this intrest rate
                $user_interests[$i][1] = $user_interests[$i][1] - (0.02/($array_size-1));
              }
            }
          }
          // add the released amout of intrest rate to the intrest rate of the intrest that is equal to the tag of the image clicked on by the user
          $user_interests[$index][1] = $user_interests[$index][1] + $released_interests;
          // reindex $user_interests, unseting an array in $user_interests causes the index for that array to go missing, will casue issues in the future if not dealt with
          $user_interests = array_values($user_interests);
        }

        else {
          // tag is not found in user intrests and need to to added to it
          // for each array in $user_interests
          for ($i=0; $i <$array_size  ; $i++) {
            // checks if subtracting the nessasaty amout from this intrest rate takes it below 0
            if (($user_interests[$i][1] - (0.02/$array_size)) < 0) {
              // if so this intrest is removed from $user_interests and what ever intrest rate it had is then added to $released_interests (this keeps the total amout of intrest rate equal to 1 )
              $released_interests = $released_interests + $user_interests[$i][1];
              unset($user_interests[$i]);
            }
            else {
              // if not the the amount subracted from this intrest rate is added to $released_interests
              $released_interests = $released_interests + (0.02/sizeof($user_interests));
              // and the the nessasary amout is then subrtacted from this intrest rate
              $user_interests[$i][1] = $user_interests[$i][1] - (0.02/sizeof($user_interests));
            }
          }
          // add the new array to user intrests
          array_push($user_interests,[$tag,$released_interests]);
          // reindex $user_interests, unseting an array in $user_interests causes the index for that array to go missing, will casue issues in the future if not dealt with
          $user_interests = array_values($user_interests);
        }
        // reserializes $user_interests
        $user_interests = serialize($user_interests);
        // updates $user_interests in the users table tot eh new adjusted $user_interests
        UPDATE("users","InterestArray","'".$user_interests."'","UserID",$UserID,"i",$users_conn,false);
      }


    }

    // if data_request is ADD TO FAVS. This code is designed to add the ImgID of an image to the fav_imgs of a user
    elseif ($_POST["data_request"]=="ADD TO FAVS") {
      // gets data from POST
      $UserID = $_POST["UserID"];
      $ImgID = $_POST["ImgID"];
      // if User Id is set
      if ($UserID != "null") {
        // gets the users favorite images from users table
        $favs = SELECT("fav_imgs","users","UserID",$UserID,"i",$users_conn,false);
        // unserializes the result
        $favs = unserialize($favs[0]);
        // checks if the ImgID to add to $favs is allready in $favs
        if (!in_array($ImgID,$favs)) {
          // if not add the new ImgID to $favs
          array_push($favs,$ImgID);
          // reserialize $favs
          $favs = serialize($favs);
          // and update the users table with the new $favs array
          UPDATE("users","fav_imgs","'".$favs."'","UserID",$UserID,"i",$users_conn,false);
        }
        // echo a success message
        echo "success";
      }
    }

    // if data_request is GET PERSONAL INFO. This code is designed to return a users info for it to be displayed on the users profile
    elseif ($_POST["data_request"]=="GET PERSONAL INFO") {
      // gets data from POST
      $UserID = $_POST["UserID"];
      // gets Users Username from users table
      $UserUid  = SELECT("UserUid","users","UserID",$UserID,"i",$users_conn,false);
      // gets the users email form user table
      $email = SELECT("UserEmail","users","UserID",$UserID,"i",$users_conn,false);
      // forms a response
      $resp = [$UserUid[0],$email[0]];
      // encodes the respone is JSON
      $resp = json_encode($resp);
      // echos out the response
      echo $resp;
    }

    // if data_request is GET FAV INFO. This code is designed to return a users favorite images info for it to be displayed on the users profile
    elseif ($_POST["data_request"]=="GET FAV INFO") {
      // gets users ID from POSt
      $UserID = $_POST["UserID"];
      // gets users favorite images from users table
      $resp = SELECT("fav_imgs","users","UserID",$UserID,"i",$users_conn,false);
      // unserialize result to a php array
      $resp = unserialize($resp[0]);
      // encodes the php array to a JSON string
      $resp = json_encode($resp);
      // echos out the JSON string for reading
      echo $resp;
    }

    // if data_request is GET FRIENDS. This code is designed to return a list of the users friends  for it to be displayed on the users profile
    elseif ($_POST["data_request"]=="GET FRIENDS") {
      // get the users UserID from POST
      $UserID = $_POST["UserID"];
      // get the users friend list from the users table
      $friends = SELECT("friends","users","UserID",$UserID,"i",$users_conn,false);
      // unserialize the result  (string to php array)
      $friends = unserialize($friends[0]);
      // encode the friends array
      $resp = json_encode($friends);
      // echo out the JSON for reading
      echo $resp;
    }

    // if data_request is RESET PWD. This code is desgined to validete the request then update the users password in the users table
    elseif ($_POST["data_request"]=="RESET PWD") {

      // gets data from POST
      $UserID = $_POST["UserID"];
      // supposed current password
      $pwd1 = $_POST["pwd1"];
      // new password
      $pwd2 = $_POST["pwd2"];

      // checks if the new passwords meet the password requirements
      // checks if the pwd only contain allowed chars and contains at least 1 character from the sets {a-z} {A-Z} {0-9} {!£$%^&*_-=+} and is also at least 8 characters long
      if (preg_match("/^[\!\£\$\%\^\&\*_\-\=\+a-zA-Z0-9\s]+$/",$pwd2) && mb_strlen($pwd2) > 7 && preg_match("/[\!\£\$\%\^\&\*_\-\=\+]+/",$pwd2)  && preg_match("/[a-z]+/",$pwd2) && preg_match("/[A-Z]+/",$pwd2) && preg_match("/[0-9]+/",$pwd2)) {
        // if new passwrod is vailid
        // get the users current hashed password
        $result = SELECT("UserPwd","users","UserID",$UserID,"i",$users_conn,false);
        // password_verify hashes the inputed password using the salt from the allready hashed password and compares the 2 results, if they are identical the 2 passwords match
        if (password_verify($pwd1,$result[0])) {
          //Passwords match
          // hashes the user entered password using the Defulat hashing algorithm by php (bcrypt algorithm)
          $pwd2Hashed = password_hash($pwd2, PASSWORD_DEFAULT);
          // updates the users password to the newly enterd one (stores the hashed password)
          UPDATE("users","UserPwd","'".$pwd2Hashed."'","UserID",$UserID,"s",$users_conn,false);
          // return response
          echo "success";
        }
        else {
          // Passwords do not match
          // return response
          echo "no match";
        }

      }else {
        //new password does not meet requirements
        //return response
        echo "pwd2 invalid";
      }
    }

    // if data_request is ADD FRIEND.
    elseif ($_POST["data_request"]=="ADD FRIEND") {
      // gets data from POST
      $UserID0 = $_POST["UserID0"]; //This users ID
      $UserID1 = $_POST["UserID1"]; // Add this users ID

      // checks is the user is trying to add themselfs as a friend
      if ($UserID1 != $UserID0) {
        // checks if the UserID the user has entered exsists in the users table
        $result = SELECT("UserID","users","UserID",$UserID1,"s",$users_conn,False);
        // if a user exsists with a UserID what was sent to this page
        if (!empty($result[0])) {
          // get friends list for this User
          $result = SELECT("friends","users","UserID",$UserID0,"s",$users_conn,False);
          // convert serialized string into a php array
          $friends = unserialize($result[0]);
          // sets $add_friend to default value
          $add_friend = true;
          // for each friend in friends list
          for ($i=0; $i < sizeof($friends); $i++) {
            // checks if the user is allready friends with the user they entered
            if ($friends[$i][1] == $UserID1) { // stored as username and ID
              // if the two users are allready frinds, $add_friend is set to false
              $add_friend = false;
            }
          }
          // if $add_friend go head is true
          if ($add_friend) {
            // gets the Username of the frinds this user wasnts to add
            $result = SELECT("UserUid","users","UserID",$UserID1,"s",$users_conn,False);
            // adds the new friends username and UserID to this users friend list
            array_push($friends,[$result[0],$UserID1]);
            // reserializes friends list
            $friends = serialize($friends);
            // updates the users table to show the new frind for this user
            UPDATE("users","friends","'".$friends."'","UserID",$UserID0,"i",$users_conn,False);


            // repeats the process for the 2nd user, adds this user to their friends list
            $result = SELECT("friends","users","UserID",$UserID1,"s",$users_conn,False);
            $friends = unserialize($result[0]);
            $result = SELECT("UserUid","users","UserID",$UserID0,"s",$users_conn,False);
            array_push($friends,[$result[0],$UserID0]);
            $friends = serialize($friends);
            UPDATE("users","friends","'".$friends."'","UserID",$UserID1,"i",$users_conn,False);

            // After freind has been sussesfully added now We must start a convasation bewteen the to user so that they may message each other
            // inserts new row into messages table with the two users UserID's and an empty serialized array
            INSERT("messages",["UserID0","UserID1","messages"],[$UserID0,$UserID1,"a:0:{}"],"iis",$users_conn);
            // echo respone of success
            echo "success";
          }
          else {
            // echo error of already friends
            echo "already friends";
          }
        }
        else {  // echo error of user not found
          echo "user not found";
        }
      }
      else {  // echo error of user not found (User tryied to add themself as a friend)
        echo "you are this user";
      }
    }

    // if data request if to get the eusers timeline
    elseif ($_POST["data_request"]=="GET TIMELINE") {
      // gets varibles from POST
      $UserID = $_POST["UserID"];
      // sets up response and posts array
      $resp = [];
      $posts_array = [];
      // gets the date the account was created
      $result = SELECT("signup_date","users","UserID",$UserID,"i",$users_conn,false);
      //stored the account created date in the response array
      array_push($resp, "Account created: ".$result[0] );
      // queries users table to get the posts column
      $result = SELECT("posts","users","UserID",$UserID,"i",$users_conn,false);
      $posts = unserialize($result[0]);
      // for every post in the posts 2D array
      for ($i=0; $i < sizeof($posts) ; $i++) {
        // formats the posts data
        array_push($posts_array, "Post; ImgID: ".$posts[$i][0]." Date Uplaoded: ".$posts[$i][1]);
      }
      // adds the formated $posts_array to the response array
      array_push($resp, $posts_array);
      // encode the response array
      $json = json_encode($resp);
      // echo out the JSON for reading
      echo $json;
    }
  }

?>
