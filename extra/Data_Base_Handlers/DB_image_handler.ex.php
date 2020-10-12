<?php
  // inherits multi_DB_handler.db.php and all of its functions and varibles
  require "Multi_DB_handler.db.php";
  // allows access to the functions in HTTP.ex.php
  require "../HTTP.ex.php";
  // gets varibles from HTTP.ex.php
  require("../tag_wild_card.ex.php");

  define("APIID", 1);

  // compare_interest_tags is a function that takes the Users ID and interests_from_session along with $current_tags, $Wild_Card and users connection as paramaters
  // and uses this data to produce a  $target_tag for a this user given the current state of the images being displayed
  function compare_interest_tags($user_id,$user_interests_from_session,$current_tags,$Wild_Card,$users_conn){//returns $target_tag for a image
    // Note $user_interests_from_session is a 2D array with each array containing a intrest and the intrest rate for that interest

    // This varible is used to count how many images are currently being displayed that have a tag that matches any intrest in $user_interests_from_session
    // it is set to 0 by defualt
    $total_interest_img = 0;
    // for each intrest in $user_interests_from_session
    for ($pointer=0; $pointer < sizeof($user_interests_from_session); $pointer++) {
      // if this intrest from $user_interests_from_session is in the assortative array $current_tags
      if (array_key_exists($user_interests_from_session[$pointer][0], $current_tags)) {
        // increases $total_interest_img by the value in the assortative array $current_tags with the key $user_interests_from_session[$pointer][0]
        $total_interest_img = $total_interest_img + $current_tags[$user_interests_from_session[$pointer][0]];
      }
    }

    //first checks to see if there are any interests from $user_interests_from_session that are NOT currently being displayed
    // for each intrest in $user_interests_from_session
    for ($pointer=0; $pointer < sizeof($user_interests_from_session) ; $pointer++) {
      // sets the interst currently beening checked to a varible for simplisity
      $interest_tag = $user_interests_from_session[$pointer][0];
      // sets foun varible to fasle by defualt
      $interest_in_current_tags = False;
      // cheack is the intrest is null (an error that ocurs as the result of the user clicking on an undefined image, althought this is extremely rare, its is corrected here)
      if ($interest_tag != null) {
        // if the interest tag is in the array of tags currently being displayed ($current_tags)
        if ( array_key_exists($interest_tag,$current_tags)){
          // sets found to true
          $interest_in_current_tags = True;
        }
        // a user interest that is not currently bing displayed has been found
        if (!$interest_in_current_tags) {
          // returns that user interest
          return $user_interests_from_session[$pointer][0];
        }
      }
      else {
        // "null" tag error caught, removes the null interest and redistrubures its interest rate
        // stores the interest rate of the null interest
        $redistribute_interest = $user_interests_from_session[$pointer][1];
        // null interest is unset from the array
        unset($user_interests_from_session[$pointer]);
        // $user_interests_from_session 2D array is reindexed
        array_values($user_interests_from_session);
        // for each interest in $user_interests_from_session
        for ($x=0; $x <sizeof($user_interests_from_session) ; $x++) {
          // redistribute the released interest rate back to the remaining interests evenly
          $user_interests_from_session[$x][1] = $user_interests_from_session[$x][1] + ($redistribute_interest/sizeof($user_interests_from_session));
        }
        // $pointer is set back 1 as an array frothe 2D array $user_interests_from_session has been removed and reindexed, if this wasnt done a array
        // would be skiped by the for loop pointer
        $pointer = $pointer - 1;
        // the null interest is now corrected in $user_interests_from_session but still exsists in the data base

        // corrects the null interst in the database
        $temp = SELECT("InterestArray","users","UsserID",$user_id,"i",$users_conn,false);
        $temp = unserialize($temp[0]);
        for ($t=0; $t < sizeof($temp); $t++) {
          if ($temp[$t][0] == null) {
            unset($temp[$t]);
            array_values($temp);
          }
        }
        $temp = serialize($temp);
        UPDATE("users","InterestArray","'".$temp."'","UserID",$user_id,"i",$users_conn,false);
      }

    }// cheacks that there is atleast 1 img of each interest present

    // every intrest in $user_interests_from_session has at least 1 image displayed for it, intrest rates comaprison now must be used to determin what interest
    // will be returned as the $target_tag

    // random number is gerated, it is used wo decide whether a wild card tag will be used instead of one from $user_interests_from_session
    $rand_int = rand(1,4);
    // if $rand_int happened to be 1 or if there are no interests pressent in $user_interests_from_session, use a wild card
    if ($rand_int == 1 || sizeof($user_interests_from_session) == 0  ) {
    // generates a random number repersenting a tag from the $Wild_Card array
     $rand_index = rand( 0 , (sizeof($Wild_Card)-1)  );
    // returns the random tag from $Wild_Card
     return $Wild_Card[$rand_index];
    }
    // a Wild card was nor selected nor was $user_interests_from_session empty
    else {
      // Normal tag
      // for each interest in $user_interests_from_session it takes the interest rate for the intrest and compares it to the rate it appears in current tags
      // determining if this interest / tag is underrepresented or not
      for ($pointer=0; $pointer < sizeof($user_interests_from_session) ; $pointer++) {
        // gets the intrest rate for this interest in $user_interests_from_session
        $interest_rate = $user_interests_from_session[$pointer][1];
        // gets the interest currently being examined
        $possible_new_img_tag = $user_interests_from_session[$pointer][0];
        //comapre ratios of tags in cuurent tags to the interest rate
        // note ($current_tags is a associative array with the tag as the key and the number of times that tag is displayed in index.php as the value)
        // if this tag / interest is under represent (or equaly represent) in current tags compared to what it should be in $user_interests_from_session
        if ($interest_rate >= ($current_tags[$possible_new_img_tag]/$total_interest_img) ) {
          // return this tag / interest to be the $target_tag
          return $possible_new_img_tag;
        }
      }
      // redirects user to fatal_error.php, in theory this will never happened as at least 1 interest from $user_interests_from_session
      // will be under represented or equaly represented, they cant all be over repeated thats not how percentages work.
      header("Location: ../../fatal_error.php?error=failed_to_aquire_new_image_tag");
   }

  }
  //  this function is called with  paramaters, the target tag, current id's and tags along with both the users and images database connections
  // Returns a list of vaild ImgID's for tag or nothing if tag needs to be droped
  function get_target_tag_ids($target_tag,$current_ids,$current_tags,$images_conn,$users_conn){
    // sets varibles used in loops and constants
    $x = 0;

    //$APIID = 1;
    // sets defualt value for the function response
    $API_func_resp = "Blank";
    // while loop is used to allow for corrections and adjustment od varibles of 1st and 2nd pass allowing 3nd pass to allways succeed
    while ($x < 3) {
      // $x is incromented
      $x++;
      // gets the TagID for the given $target_tag
      $TagID_target_tag = SELECT("TagID","tags","Tag",$target_tag,"s",$images_conn,False);
      // if query result is empty, this tag has not been entered in to thge tags table yet
      if (empty($TagID_target_tag)) {//tag has not been set in tags
        // creates new row in tags table for the new tag
        INSERT("tags",["Tag"],[$target_tag],"s",$images_conn);
        // get the TagID of the new row
        $TagID_target_tag = SELECT("TagID","tags","Tag",$target_tag,"s",$images_conn,False);
      }

      // prioritises any user image with this tag over a API image with this tag
      // $user_img_avalible set to true by defualt
      $user_img_avalible = true;
      //checks if there are user uplaoded images for this tag
      $custom_sql = "SELECT `ImgID` FROM `image_data` WHERE `TagID` =  ".$TagID_target_tag[0]." AND `ImgSrcID` = 1 ";
      $user_imgIDs_with_targetted_tag = SELECT(False,False,False,False,False,$images_conn,$custom_sql);

      // if the qurey result was empty there are no user images to display
      if (empty($user_imgIDs_with_targetted_tag)) {
        //$user_img_avalible set to false
        $user_img_avalible = false;
      }
      // user img with this tag are pressent in DB
      else {

        // filter currently displayed images from ones in the database
        $filtered_user_imgIDs_with_targetted_tag = array();
        // for each ImgID in $user_imgIDs_with_targetted_tag
        for ($i=0; $i < sizeof($user_imgIDs_with_targetted_tag); $i++) {
          // if this ImgID is not in $current_ids, ie not currently beeing disayed
          if (!in_array($user_imgIDs_with_targetted_tag[$i]["ImgID"],$current_ids)) {
            // adds this ImgID to the tha array $filtered_user_imgIDs_with_targetted_tag
            array_push($filtered_user_imgIDs_with_targetted_tag , $user_imgIDs_with_targetted_tag[$i]["ImgID"]);
          }
        }
        //if all user img with target tag are allready being displayed
        if (empty($filtered_user_imgIDs_with_targetted_tag)) {
          // sets $user_img_avalible to false
          $user_img_avalible = false;
        }
        else {
          // return the list of ImgID's by users that have the given tag
          return $filtered_user_imgIDs_with_targetted_tag;
        }
      }

      // if user images are not suitable for whatever reason, API images are needed
      if (!$user_img_avalible) {
        //gets an array of all img id with target tag
        $imgIDs_with_targetted_tag = SELECT("ImgID","image_data","TagID",$TagID_target_tag[0],"i",$images_conn,False); // queryed like a million times
        //if no image of target tag in DB
        if ( empty($imgIDs_with_targetted_tag) ){
          // checks if the $target tag is marked as exhausted in the api_data table
          $exhausted =  SELECT(false,false,false,false,false,$images_conn,"SELECT `Exhausted` FROM api_data WHERE `APIID` = 1 AND `TagID` = $TagID_target_tag[0]");
          // if this tag hasnt been searched for before,
          if (empty($exhausted)) {
            // creats a new row in the api_data table
            INSERT("api_data",array("TagID","APIID","Page","Exhausted"),array($TagID_target_tag[0] , APIID , 0 ,0),"iiii",$images_conn);
          }
          elseif ($exhausted[0]["Exhausted"] == 1) {
            //tag marked as exhausted no more image of this tag can get got from API / Tag is not droped from session tags
            return NULL;
          }
          else {
            // tag is not marked as exhausted, API is called to get more images with this tag
            $API_func_resp = API_request_images(APIID,$target_tag,$TagID_target_tag[0],$images_conn,$users_conn);//ADD API TOTAL RESULTS
          }
        }
        else {
          //else filter out imgs with ids that are allready being displayed
          $imgIDs_with_targetted_tag_which_are_not_in_Current_ids = array();
          // for each ImgID in $imgIDs_with_targetted_tag
          for ($i=0; $i < sizeof($imgIDs_with_targetted_tag); $i++) {
            // if this imgID from $imgIDs_with_targetted_tag is not in $current_ids
            if (!in_array($imgIDs_with_targetted_tag[$i],$current_ids)) {
              // add this imgID from $imgIDs_with_targetted_tag to $imgIDs_with_targetted_tag_which_are_not_in_Current_ids
              array_push($imgIDs_with_targetted_tag_which_are_not_in_Current_ids , $imgIDs_with_targetted_tag[$i]);
            }
          }
          //if all img with target tag are allready being displayed, API is needed to get more images of this tag
          if (empty($imgIDs_with_targetted_tag_which_are_not_in_Current_ids)) {
            // checks if this tag has allready been marked ass exhausted
            $exhausted =  SELECT(false,false,false,false,false,$images_conn,"SELECT `Exhausted` FROM api_data WHERE `APIID` = 1 AND `TagID` = $TagID_target_tag[0]");
            // if thsi tag hasnt been searched for yet
            if (empty($exhausted)) {
              // create new row in the api_data table for this TagID
              INSERT("api_data",array("TagID","APIID","Page","Exhausted"),array($TagID_target_tag , 1 , 0 ,0),"iiii",$images_conn);
            }
            // else check if this tagID has been marked as exhausted
            elseif ($exhausted[0]["Exhausted"] == 1) {
              //tag marked as exhausted no more image of this tag can get got from API / Tag is not droped from session tags
              return NULL;
            }
            // else tagID is not exhausted, API request is vaild to go head
            else {
              $API_func_resp = API_request_images(APIID,$target_tag,$TagID_target_tag[0],$images_conn,$users_conn);
            }
          }
          else{
            //return filtered img ids which all have target tag
            return $imgIDs_with_targetted_tag_which_are_not_in_Current_ids;
          }
        }
      }

      //if API has no results for a given tag
      if ($API_func_resp == "ERROR" || $API_func_resp == "Tag Exhausted") {
        //interst from this session will be droped
        return NULL;
      }
      //APi was called but all img it responded with were allready in DB, page incromented need second loop with this tag
    }
    header("Location: ../../fatal_error.php?error=failed_to_aquire_new_image_IDs");
  }

  // this function takes an array of ImgID's and the images databse connection as paramaters, it is designed to find the ImgID with the lowwest DisplayCount from the image_data table
  function get_lowest_display_count($vailid_new_image_ids,$images_conn){
    // $sql_string contains all of the array items of $vailid_new_image_ids but is a sql friendly format
    $sql_string = $vailid_new_image_ids[0];
    // for every image in $vailid_new_image_ids (excluding the first image)
    for ($image=1; $image < sizeof($vailid_new_image_ids); $image++) {
      // appends this ImgID to $sql_string with the correct formatting
      $sql_string .= ",".$vailid_new_image_ids[$image];
    }

    //custom sql string to order results to get the lowest displaycount
    $custom_sql = "SELECT ImgID, DisplayCount FROM `image_data` WHERE ImgID IN ($sql_string) ORDER BY DisplayCount ASC LIMIT 1";
    // calls SELECT function to query the database
    $result = SELECT(False,False,False,False,False,$images_conn,$custom_sql);
    // result is stored
    $ImgID = $result[0]["ImgID"];
    // gets
    //$result = SELECT("DisplayCount","image_data","ImgID",$ImgID,"i",$images_conn,False);
    $current_display_count = $result[0]["DisplayCount"];
    $return = array($ImgID,$current_display_count);
    // returns the array containing the ImgID and the Displaycount
    return $return;
  }

  // this function is used to generate a row of 4 images it takes 5 paramaters, the $Wild_Card array (from tag_wild_card.ex.php),
  // The div ID of the row to be generated, the Users ID and the users and images database connections.
  function generate_row($Wild_Card,$div_id,$user_id,$users_conn,$images_conn){
    // if this is the first set of images request on the index.php page
    if ($div_id == 0) {
      // reset session_img_ids in the users table for the new index.php page
      UPDATE("users","session_img_ids","'a:0:{}'","UserID",$user_id,"i",$users_conn,false);
      // reset session_img_ids in the users table for the new index.php page
      UPDATE("users","session_img_tags","'a:0:{}'","UserID",$user_id,"i",$users_conn,false);
      // sets current variibles to empty arrays
      $current_tags = [];
      $current_ids = [];
    }
    // if this isnt the first request for images on the index.php page
    else {
      // gets session tags from DB (this is an associative array containing all the tags presant on the index.php since page was loaded for this User, along with the amount of
      // images that have that tag)
      $result = SELECT("session_img_tags","users","UserID",$user_id,"i",$users_conn,False);
      // unserialize the result form the query
      $current_tags = unserialize($result[0]);
      //gets session ids from DB (This is an array containing all of the ID's of every image on index.php since the page was loaded for this User)
      $result = SELECT("session_img_ids","users","UserID",$user_id,"i",$users_conn,False);
      // unserialize the result form the query
      $current_ids = unserialize($result[0]);
    }

    //gets the Users interest array from the users table (the interest array is a 2D array that contains a intrest and the given intrest rate for that interest)
    $result_array = SELECT("InterestArray","users","UserID",$user_id,"i",$users_conn,False);
    $user_interests_from_session = $result_array[0];
    // unserialize the result from the query into a php array
    $user_interests_from_session = unserialize($user_interests_from_session);
    // set up the response array which will be returned at the end of the func
    $resp = array();
    // repeats code 4 times once for each image in the row
    for ($position=0; $position < 4; $position++) {
      // adds to the log of the respone array (useful for debugging)
      $resp["log"][$position] = "--LOG--";
      // initializee $a varible allowing while loop to run
      $a = 0;
      // code can be repeated twice this allows for a intersts from $user_interests_from_session to be droped if nessacary and then loop
      // back back to the start of the while loop. If a tag is not droped it will not loop back
      // (A intrest from $user_interests_from_session is only droped when no more images with that tag can be send back to the client and
      //  therefore it is removed from $user_interests_from_session)
      while ($a < 3) {
        $a++;// $a is incomented here and if a final img id is found (later on)
        // calls compare_interest_tags to get the target tag, the Users ID and interests_from_session along with $current_tags, $Wild_Card and  users connection
        // are passd in to the function
        $target_tag = compare_interest_tags($user_id,$user_interests_from_session,$current_tags,$Wild_Card,$users_conn);
        // adds $target_tag to log
        $resp["log"][$position].=$target_tag."--";
        // calls get_target_tag_ids with the paramaters $target_tag, $current_ids, $current_tags. This function is designed to produce a list of ImgID's
        // that have the given tag and are not in $current_ids (ie nor allready beeing displayed)
        $vailid_new_image_ids = get_target_tag_ids($target_tag,$current_ids,$current_tags,$images_conn,$users_conn);//get images that ares valid ids
        // if $vailid_new_image_ids is empty this meanis that the API is has been exsausted for this tag and it is released from the $user_interests_from_session
        // and the interest rates are redistubrited
        if (empty($vailid_new_image_ids)) {
          // add to log that $vailid_new_image_ids was empty
          $resp["log"][$position].="--ID's GOT EMPTY--";
          //tag is then released from session user intersts
          //checks to make sure all tags arent depleded
          if (sizeof($user_interests_from_session) > 1) {
            // $user_interests_from_session will not be empty after an interest is removed
            // sets $found varible
            $found = false;
            // sets the size of $user_interests_from_session to a varible as it will change and therefor not sutable to use in the for loop
            $size_of_user_interests_from_session = sizeof($user_interests_from_session);
            // searches the $user_interests_from_session for the tag to be released
            for ($x=0; $x < $size_of_user_interests_from_session ; $x++) {
              if ($user_interests_from_session[$x][0] == $target_tag) {
                // tag has been found, the pointer to theis tag is stored in $interest_pointer
                $interest_pointer = $x;
                // x is set to stop the for loop
                $x = $size_of_user_interests_from_session;
                // found is set to true
                $found = true;
              }
            }
            if ($found) {
              // adds to log
              $resp["log"][$position].="--found and redistrubured--";
              // $interest_from_rleased_tag stores the intrest rate for the intrest that is about to be removed
              $interest_from_rleased_tag = $user_interests_from_session[$interest_pointer][1];
              //Removes exsausted tag from session interests
              unset($user_interests_from_session[$interest_pointer]);
              // since unset() also removes the index for the romved value, the entire array must be reindexed to prevent errors in the future
              $user_interests_from_session = array_values($user_interests_from_session);
              // the amout of interest rate from the removed interest is them split evenly by the new size of $user_interests_from_session
              $redistribute_interest_amount = $interest_from_rleased_tag / sizeof($user_interests_from_session) ;
              //redistubruites interst in exsausted tag evenly to the remaining tags using the just calculated $redistribute_interest_amount
              for ($i=0; $i < sizeof($user_interests_from_session); $i++) {
                // for every intrest in $user_interests_from_session, increase its intrest rate by $redistribute_interest_amount
                $user_interests_from_session[$i][1] = $user_interests_from_session[$i][1] + $redistribute_interest_amount;
              }
            }else {
              // if the tag to be released is not found in $user_interests_from_session a big error has occured and it is added to the log
              // for debugging. In theory this will never ocurr since the target tag is based of the intrest in $user_interests_from_session
              // and there is no reason as to why it would suddenly disappear
              $resp["log"][$position].="--SPECIAL TAGID IS ".$target_tag."--";
            }

          }
          //no more tags exist in session tags, all data is returned
          else {
            // if this tag is released, $user_interests_from_session will be empty,
            $user_interests_from_session = [];
            // if any requests come in for images an error will be thrown therefore an error field is set in the response array stopping any requests for images,
            $resp["error"] = "stop img requests";
            // the varibles $a and $position are allso set that the loops will not run again and the response array is immediately sent back to the client
            $a = 2;
            $position = 4;
          }
        }
        //if there are valid ids to pick from
        else{
          // adds to log that there are vaild IDs to pick from
          $resp["log"][$position].="--ID's GOT ".sizeof($vailid_new_image_ids)."--";
          // get_lowest_display_count func is called with the paramaters of $vailid_new_image_ids and the images database connection
          // its purpose is to find the ImgID with the lowwest display count that is in $vailid_new_image_ids
          // it returns an array with the ImgID and its display count
          $result = get_lowest_display_count($vailid_new_image_ids,$images_conn);
          // sets $final_img_id to the result of the get_lowest_display_count func
          $final_img_id = $result[0];
          // addes to the lof that the final ImgID has been found
          $resp["log"][$position].=$final_img_id;
          // incroments the display count by 1
          $current_display_count = $result[1];
          //final img found no need for 2nd loop a is incromented (stopping the loop)
          $a++;

          //updates $current_tags (2D array) to add the new img tag.
          // checks if the tag of the $final_img_id is allready in $current_tags
          if (array_key_exists($target_tag,$current_tags)){
            // if so, the amount of images with this tag is then increased by 1
            $current_tags[$target_tag] = $current_tags[$target_tag] + 1;
          }
          else {
            // else the new tag and is set in $current_tags with a value of 1
            $current_tags += array($target_tag => 1);
          }

          // updates display count in image_Data table for the final ImgID
          UPDATE("image_data","DisplayCount",$current_display_count+1 ,"ImgID",$final_img_id,"i",$images_conn,false);
          // appends the final Img ID to $current_ids
          array_push($current_ids,$final_img_id);
          //updates current img ids in users table for this User
          UPDATE("users","session_img_ids","'".serialize($current_ids)."'","UserID",$user_id,"i",$users_conn,false);
          //updates current img ids in users table for this User
          UPDATE("users","session_img_tags","'".serialize($current_tags)."'","UserID",$user_id,"i",$users_conn,false);

          // Adds data to the response file

          // gets the image link from the image_data table for the given ImgID of $final_img_id
          $result = SELECT("portrait","image_data","ImgID",$final_img_id,"i",$images_conn,False);
          // if the quety result is empty the Image has no link and therefore is a user uplaoded image and has a file path instead
          if ($result[0] != NULL) {
            //
            $resp["src"][$position] = $result[0];
            $resp["src_type"][$position] = "url";

            $result = SELECT("url","image_data","ImgID",$final_img_id,"i",$images_conn,False);
            $resp["org_src"][$position] = $result[0];
            //var_dump($resp);
          }
          else {
            $result = SELECT("file","image_data","ImgID",$final_img_id,"i",$images_conn,False);
            $resp["src"][$position] = $result[0];
            $resp["org_src"][$position] = $result[0];
            $resp["src_type"][$position] = "file";
          }
          $resp["div_id"][$position] = $div_id;
          $resp["img_id"][$position] = $final_img_id;
          $resp["tag"][$position] = $target_tag;
        }
      }
    }// returns the response array
    return $resp;
  }

    // this function is used in the "GET IMAGE DATA" data_request type, it is designed to reeturn all the relevant infomation about a given ImgID
    // it takes 2 paramaters the ImgID and the images database connection

  function get_img_data($ImgID,$images_conn){
    // gets the width for the given ImgID
    $width = SELECT("width","image_data","ImgID",$ImgID,"i",$images_conn,false);
    // gets the height for the given ImgID
    $height = SELECT("height","image_data","ImgID",$ImgID,"i",$images_conn,false);
    // gets the authors ID for the given ImgID
    $authorID = SELECT("AuthorID","image_data","ImgID",$ImgID,"i",$images_conn,false);
    // gets the authors username for the given ImgID
    $author_username = SELECT("AuthorUid","authors","AuthorID",$authorID[0],"i",$images_conn,false);
    // gets the authors link for the given ImgID
    $author_link = SELECT("AuthorUrl","authors","AuthorID",$authorID[0],"i",$images_conn,false);
    // gets the TagID for the given ImgID
    $ImgTagID = SELECT("TagID","image_data","ImgID",$ImgID,"i",$images_conn,false);
    // gets the Tag for the given ImgID
    $ImgTag = SELECT("Tag","tags","TagID",$ImgTagID[0],"i",$images_conn,false);
    // returns an array with all the collected data in it
    return [$width[0],$height[0],$author_username[0],$author_link[0],$ImgTag[0]];
  }

  // checks if data_request is set
  if (isset($_POST["data_request"])) {

    // checks if the data_request is to RECEIVE BLOCK, This code is desinged to generate and  return 5 groups of 4 images along with all the
    //meta data needed to add and display the images on index.php
    if ($_POST["data_request"]=="RECEIVE BLOCK") {
      // gets the div ID offest and the Users ID from the POST varible
      $start_div_id = $_POST["div_id"];
      $UserID = $_POST["user_id"];
      // sets up the empty response
      $resp = [];
      // repeats this code 4 times, once for each row of images, each run produces a row of images
      for ($i=0; $i < 5; $i++) {
        // $i is used to incroment the div Id passed into the generate_row func
        $div = $start_div_id + $i;
        // calls the func generate_row, with 5 paramaters, the $Wild_Card array (from tag_wild_card.ex.php), The div ID of the row to be
        // generated, the Users ID and the users and images database connections. The ruturned value from the func is then appended to
        // the resp array
        array_push($resp , generate_row($Wild_Card, $div , $UserID ,$users_conn,$images_conn));
      }
      //turns $resp array into a JSON string
      $json = json_encode($resp);
      //echos out the JSON for reading
      echo $json;
    }

    // checks if the data_request is to RECEIVE ROW, This code is desinged to generate and  return 1 groups of 4 images along with all the
    //meta data needed to add and display the images on index.php
    if ($_POST["data_request"]=="RECEIVE ROW") {//if the request is to recive data from DB
      $resp = generate_row($_POST["div_id"],$_POST["user_id"] ,$users_conn,$images_conn);
      //turns data array into a JSON obj
      //var_dump($resp);
      $json = json_encode($resp);
      //echos out JSON for reading
      echo $json;
    }

    // checks if the data_request is to RECEIVE SINGLE, This code is desinged to return 1 image with a given ImgID along with all the
    // meta data needed to add and display the image
    elseif ($_POST["data_request"]=="RECEIVE SINGLE"){
      // set up response array
      $resp = array();
      // gets ImgID from POST varible
      $imgID = $_POST["img_id"];
      // gets the url of the given ImgID
      $result = SELECT("original_url","image_data","ImgID",$imgID,"i",$images_conn,False);
      if (!empty($result)) {
        return [$result[0],"API"];
      }
      else {
        $result = SELECT("file","image_data","ImgID",$imgID,"i",$images_conn,False);
        return [$result[0],"USER"];
      }
    }

    // checks if the data_request is to GET IMG DATA, This code is desinged to return all relevant image data for a given ImgID
    // so that info abount the image may be displayed, this is mainly used on index.php
    elseif ($_POST["data_request"]=="GET IMG DATA") {
      // gets ImgID from POST varible
      $ImgID = $_POST["ImgID"];
      // calls the get_img_data function with the given ImgID
      $resp = get_img_data($ImgID,$images_conn);
      // encode the response to JSON from a php array
      $resp = json_encode($resp);
      // echos out the response for reading
      echo $resp;
    }
  }
?>
