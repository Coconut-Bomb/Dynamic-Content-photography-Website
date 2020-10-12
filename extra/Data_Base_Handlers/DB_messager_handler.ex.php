<?php
// requires Multi_DB_handler.db.php along all of its function need to query the databases
require("Multi_DB_handler.db.php");
/*
//$_POST["request"] = "load_convo";
$_POST["request"] = "send_message";
$_POST["senderID"] = 1;
$_POST["message"] = "\\";  //  / \

$_POST["mess_type"] = "text";
$_POST["ChatID"] = 3; //*/

// if a request is set
if (isset($_POST["request"])) {

  // if data request id set to load_convo, with will return an entire conversation for a given ChatID
  if ($_POST["request"] == "load_convo") {
    // loads variables from POST
    $chat_id = $_POST["ChatID"];
    // gets the messages array for the convo with ChatID equal tot eh ChatID given
    $result = SELECT("messages","messages","ChatID",$chat_id,"i",$users_conn,False);
    // converts serialized string into a php array
    $messages = unserialize($result[0]);
    // ecoded the php array into a JSON string
    $json = json_encode($messages);
    // echos out the JSON string fdor reading
    echo $json;
  }
  // if data request id set to get_all_convo_headers, this will return all convosations a user is having
  elseif ($_POST["request"] == "get_all_convo_headers") {
    // sets response array
    $return = [];

    // due to the unique situation, the messages table has 2 columns for UserID's 1 for UserID0 and 1 for UserID1 there is not meaningfull difference between the two colmn other that to separeate the two UserID's
    // it is unkown whether a certain UserId will be stored in the UserID0 column or the UserID1 column, there is no determinant for this when entering a new row into messages. As such when seraching for a UserID in
    // messages both column need to be searched for the first UserID, and then both results need to be filtered for the 2nd UserID

    // checks if DATA UserID "UserID0" is in the COLUMN `UserID0`
    $column_user_id0 = $_POST["user_id0"]; //(This Users ID / the ID of the User that sent this request)
    // gets a array of the UserID's for every User THIS User has a conversation with, IF  searching the COLUMN `UserID0` for the GIVEN DATA "UserID0"
    $user_id1s = SELECT("UserID1","messages","UserID0",$column_user_id0,"i",$users_conn,False);
    // for every every user in $user_id1s
    for ($i=0; $i < sizeof($user_id1s); $i++) {
      // gets the Username of the user THIS user has a conversation with (the username of the opposite user)
      $user_name = SELECT("UserUid","users","UserID",$user_id1s[$i],"i",$users_conn,False);
      // forms a custom sql statment to get the last_mess from the messages table where (column) `UserID0` = this user's ID and (column) `UserID1` = a User's ID from $user_id1s array
      $custom_sql = "SELECT last_mess FROM `messages` WHERE UserID0 = $column_user_id0 AND UserID1 = $user_id1s[$i]";
      $last_mess = SELECT(False,False,False,False,False,$users_conn,$custom_sql);
      // forms a custom sql statment to get the ChatID from the messages table where (column) `UserID0` = this user's ID and (column) `UserID1` = a User's ID from $user_id1s array
      $custom_sql = "SELECT ChatID FROM `messages` WHERE UserID0 = $column_user_id0 AND UserID1 = $user_id1s[$i]";
      $chatID = SELECT(False,False,False,False,False,$users_conn,$custom_sql);
      // adds the username, last message sent, UserID and the ChatID to the response array
      array_push($return,[$user_name[0],$last_mess[0]["last_mess"],$user_id1s[$i],$chatID[0]["ChatID"]]);
    }

    // FOR ALL THOES WILL THIS ID IN USERID1
    // checks if DATA UserID "UserID0" is in the COLUMN `UserID1`
    $column_user_id1 = $_POST["user_id0"];//(This Users ID / the ID of the User that sent this request)
    // gets a array of the UserID's for every User THIS User has a conversation with, IF  searching the COLUMN `UserID1` for the POST DATA "UserID0"
    $user_id0s = SELECT("UserID0","messages","UserID1",$column_user_id1,"i",$users_conn,False);
    // for every every user in $user_id0s
    for ($i=0; $i < sizeof($user_id0s); $i++) {
      // gets the Username of the user THIS user has a conversation with (the username of the opposite user)
      $user_name = SELECT("UserUid","users","UserID",$user_id0s[$i],"i",$users_conn,False);
      // forms a custom sql statment to get the last_mess from the messages table where (column) `UserID1` = this user's ID and (column) `UserID0` = a User's ID from $user_id0s array
      $custom_sql = "SELECT last_mess FROM `messages` WHERE UserID0 = $user_id0s[$i] AND UserID1 = $column_user_id1";
      $last_mess = SELECT(False,False,False,False,False,$users_conn,$custom_sql);
      // forms a custom sql statment to get the ChatID from the messages table where (column) `UserID1` = this user's ID and (column) `UserID0` = a User's ID from $user_id0s array
      $custom_sql = "SELECT ChatID FROM `messages` WHERE UserID0 = $user_id0s[$i] AND UserID1 = $column_user_id1";
      $chatID = SELECT(False,False,False,False,False,$users_conn,$custom_sql);
      // adds the username, last message sent, UserID and the ChatID to the response array
      array_push($return,[$user_name[0],$last_mess[0]["last_mess"],$user_id0s[$i],$chatID[0]["ChatID"]]);
    }

   // an exsample of the php array generated [["James78","Nice one mate",78421],["User Name","Last messge",UsertID,ChatID]...]
   // encode the php array to a JSOn string
    $json = json_encode($return);
    // echos out the dtring for reading
    echo $json;
  }

  // if data request id set to send_message, this will validate the message contents and then add it to the messages table if it is valid data
  elseif ($_POST["request"] == "send_message") {

    // gets variables from POST
    $senderID = $_POST["senderID"];
    $ChatID = intval($_POST["ChatID"]);
    $message = $_POST["message"];
    $mess_type = $_POST["mess_type"];

    // if message type is text
    if ($mess_type == "text") {
      // get the messages 2D array for the given ChatID
      $result = SELECT("messages","messages","ChatID",$ChatID,"i",$users_conn,False);
      // unserialize the result of the query
      $messages = unserialize($result[0]);
      // add the array of senderID, Date and time, message type and the message content to the messages 2D array
      array_push($messages,[$senderID,date('M,d,Y h:i A'),$mess_type,$message]);
      // get the first 35 characters of the message just added to messages

      $trunc_message = substr($message, 0, 35);

      //var_dump(["messages","last_mess","'".$trunc_message."'","ChatID",$ChatID,"i",false]);
      // updat the last_messge column in the messages table to this new truncated message where ChatID is equal to the given ChatID
      UPDATE("messages","last_mess","'".$trunc_message."'","ChatID",$ChatID,"i",$users_conn,false);
      // reserialize the messages 2D array
      $messages = serialize($messages);
      // update the messges column in the messages table to the new 2D messages array
      UPDATE("messages","messages","'".$messages."'","ChatID",$ChatID,"i",$users_conn,false);
      // echo success message for reading
      echo "success";
    }
    // if message type is an image
    elseif ($mess_type == "img") {
      // check if the given ImgID exsists by qurying the image_data table for the given ImgID
      $img_url = SELECT("url","image_data","ImgID",intval($message),"i",$images_conn,false);
      // if result from query is not empty (ie the Img is vaild)
      if (!empty($img_url)) {
        // get the 2D array messages from messages where ChatID is equal to ChatID
        $result = SELECT("messages","messages","ChatID",$ChatID,"i",$users_conn,False);
        // unserialize result from qurey
        $messages = unserialize($result[0]);
        array_push($messages,[$senderID,date('M,d,Y h:i A'),$mess_type,$img_url[0]]);
        // reserialize the messages 2D array
        $messages = serialize($messages);
        // update the messges column in the messages table to the new 2D messages array
        UPDATE("messages","messages","'".$messages."'","ChatID",$ChatID,"i",$users_conn,false);
        // updates the last_messge column in the messages table to "< image >" where ChatID is equal to the given ChatID
        UPDATE("messages","last_mess","'< Image >'","ChatID",$ChatID,"i",$users_conn,false);
        // echo success message for reading
        echo "success";
      }else {
        // echo error messge for reading
        echo "imgID not found";
      }


    }


  }

  // if data request id set to load_comments, this will return the comments for a given ImgID
  elseif ($_POST["request"] == "load_comments") {
    // gets data from POST
    $ImgID = $_POST["ImgID"];
    $comments_array = [];

    //get commentID for row in image_data where ImgID is equal to the given ImgID
    $CommentsID = SELECT("CommentsID","image_data","ImgID",$ImgID,"i",$images_conn,false);
    // if no commentsID for this image, this is because no comments have ever been added to this Image
    if (!isset($CommentsID[0])) {
      //response will be empty, encode the empty php array to a empty serialized array
      $comments_array = json_encode($comments_array);
      // echos out response for reading. Althought response is empty it still needs to be send back to twhere the request came from
      echo $comments_array;
    }
    // There is a comment ID for this image meaning comments have been added to the past
    else{
      // gets the comments column from the comments table where CommentsID is equal to CommentsID
      $result = SELECT("comments","comments","CommentsID",$CommentsID[0],"i",$images_conn,false);
      // unserializes the result of the query
      $comments_array = unserialize($result[0]);
      // encode $comments_array to a JSON string
      $comments_array = json_encode($comments_array);
      // echos out $comments_array for reading
      echo $comments_array;
    }
  }

  // if data request id set to add_comment, this will attempt to add a comment to a post with a given ImgID
  elseif ($_POST["request"] == "add_comment") {
    // gets data from POST
    $UserID = $_POST["UserID"];
    $ImgID = $_POST["ImgID"];
    $comment = $_POST["comment"];
    // sets empty response
    $comments_array = [];
    //First gets the commentsID for the image with the given ImgID from the himage_data table
    $CommentsID = SELECT("CommentsID","image_data","ImgID",$ImgID,"i",$images_conn,false);

    // if a commetn ID has allready been set for this Image
    if (isset($CommentsID[0])) {
      // gets the comments column from the comments table where CommentsID is equal to CommentsID
      $result = SELECT("comments","comments","CommentsID",$CommentsID[0],"i",$images_conn,false);
      // unserializes the result of the query
      $comments_array = unserialize($result[0]);
      // append the array of date, UserID and comment to the 2D array $comments_array
      array_push($comments_array,[date("d,M,Y"),$UserID,$comment]);
      // reserializes $comments_array
      $comments_array = serialize($comments_array);
      // updates the comments column in the comments table where the column commentsID is equal to $CommentsID
      UPDATE("comments","comments","'".$comments_array."'","CommentsID",$CommentsID[0],"i",$images_conn,False);
      // echo out success message for reading
      echo "success";
    }
    //if no comment ID has been set for this Image, (this is the first comment to be added to this image)
    else {
      // appends the date, UserID and comment to the empty array $comments_array
      array_push($comments_array,[date("d,M,Y"),$UserID,$comment]);
      // serializes $comments_array
      $comments_array = serialize($comments_array);
      // inserts new row into the comments table containing $comments_array in the comments column
      INSERT("comments",["comments"],[$comments_array],"s",$images_conn,false);
      // used a custom aggregate sql query to find the CommentsID of the row just added to the table comments
      $custom_sql = "SELECT MAX(`CommentsID`) FROM `comments` ";
      $result = SELECT(False,False,False,False,False,$images_conn,$custom_sql);
      $CommetnsID = $result[0]["MAX(`CommentsID`)"];

      UPDATE("image_data","CommentsID",$CommetnsID,"ImgID",$ImgID,"i",$images_conn,False);
      echo "success";
    }
  }
}


?>
