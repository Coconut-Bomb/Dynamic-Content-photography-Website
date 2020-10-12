<?php
  // note this file does not require Multi_DB_Handler as it is required by the parent file that also requres this file
  // set time out limit higher that default to allow the API time to respond
  set_time_limit(50);
  // this function is called to reuest images from the API, it is called with 3 main paramaters and 2 databse connections
  // the main parapaters are the APIID, the trarget tag and the target tag ID
  function API_request_images($APIID,$target_tag,$TagID_target_tag,$images_conn,$users_conn){
    //The API will firstly check if the tag its requesting is marked as EXAHUSTED in the DB
    //The API will only add a response from Pexels if the photo isnt allready in the DB, if no imgs are added to the DB as a result of a API request
    //the page number is auto incromented
    //the API will allways return the TOTAL number of img belonging to the tag queryed
    //this is used to set the EXAHUSTED in DB out side of Func (get img ids)

    // gets if the tag has been marked as Exhausted by the server
    //$exhausted = SELECT(False,False,False,False,False,$images_conn,"SELECT `Exhausted` FROM api_data WHERE `TagID` = $TagID_target_tag AND `APIID` = $APIID ");
    // gets the last page queryed by the API
    $page_pickup = SELECT(False,False,False,False,False,$images_conn,"SELECT `Page` FROM api_data WHERE `TagID` = $TagID_target_tag AND `APIID` = $APIID ");
    $page = $page_pickup[0]["Page"];

    // base url for the API request
    $url = "https://api.pexels.com/v1/search?query=".$target_tag."&page=".$page; //https://api.pexels.com/v1/search?query=example+query&per_page=15&page=1
    // creats a cURL handle
    $ch = curl_init();
    // adds opptions to the cURl handle
    curl_setopt($ch, CURLOPT_URL, $url);//
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    // add the API authorisation key to the header of the request
    $headers = array();
    $headers[] = 'Authorization: 563492ad6f917000010000010d40f897bc0545e0af96c113b4c2d019';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // executes the API request
    $result = curl_exec($ch);
    // if there was a cURL error
    if (curl_errno($ch)) {
      // return error
      return "ERROR";
      // closes the cURL handle
      curl_close($ch);
    }
    else{// API RESPONDED
      // decode the response from JSOn to a php array
      $response = json_decode($result, true);

      if (isset($response["error"])) {
        // API responded with an error most likely API Rate limit was exceeded and no more API requests may be formed
        header("../fatal_error.php?error=API responded with an error, likely exceeded hourly limit. ERROR: ".$response["error"]);

      }
      // no error was returned
      else {
        // if the API responded with a total amout of image of 0
        if ($response["total_results"] == 0) {
          // set tag as exhausted to stop any future requests for this tag
          $custom_sql = "UPDATE `api_data` SET `Exhausted` = 1  WHERE `TagID` = ".$TagID_target_tag." AND `APIID` = 1 ";
          UPDATE(false,false,false,false,false,false,$images_conn,$custom_sql);
          // returns TAG_EXAHUSTED to show that tag show be released from session intrests
          return "Tag Exhausted";
        }
        elseif (sizeof($response["photos"]) != 0) {
          // API responded wioth images for this page
          // keeps track of how many image were actually added to the database from this APi request
          $img_added = 0;
          // for each photo in response
          for ($photo=0; $photo < sizeof($response["photos"]); $photo++) {
            // checks if This API image is allready in DB, by checking ImgSrcID
            $result = SELECT("ID_from_src","image_data","ID_from_src",$response["photos"][$photo]["id"],"i",$images_conn,False);
            if (!empty($result)) {
              // This photo is allready in the Db and is passed over
            }
            // photo from API Is New, now preping to add to DB
            else {
              // increments the $img_added counter
              $img_added = $img_added + 1;
              //GETS NEXT AVALIBE IMD ID (for tables wher imd ID is not the primary key (synces the different tables))
              $custom_sql = "SELECT MAX(ImgID) FROM `image_data`";
              $result = SELECT(False,False,False,False,False,$images_conn,$custom_sql);
              $Next_ImgID = $result[0]["MAX(ImgID)"] + 1;

              //checks if author is allready in the DB
              $result = SELECT("AuthorID","authors","PexelsAID",$response["photos"][$photo]["photographer_id"],"i",$images_conn,False);
              if (empty($result)) {
                //if author doesnt exist in DB Then add author to database
                INSERT("authors",array("PexelsAID","AuthorUid","AuthorUrl"),array($response["photos"][$photo]["photographer_id"],$response["photos"][$photo]["photographer"],$response["photos"][$photo]["photographer_url"]),"iss",$images_conn);
                // gets the AuthorID of the author just added
                $result = SELECT("AuthorID","authors","AuthorUid",$response["photos"][$photo]["photographer"],"s",$images_conn,False);
                $AuthorID = $result[0];
              }
              else {///if author does exist in DB
                $AuthorID = $result[0];
              }

              //insert new API img into image_data
              INSERT("image_data",array("ImgSrcID","AuthorID","TagID","ID_from_Src","DisplayCount","width","height","url","portrait"),array( 2 , $AuthorID , $TagID_target_tag , $response["photos"][$photo]["id"] , 0 , $response["photos"][$photo]["width"] , $response["photos"][$photo]["height"] , $response["photos"][$photo]["src"]["original"], $response["photos"][$photo]["src"]["portrait"]  ),"iiiiiiiss",$images_conn);
            }
          }
          // if no images were added from this page response from APi
          if ($img_added == 0) {
            //all image API responded with are allreading in the DB, page number is incromented
            $new_page = $page + 1;
            // updates the page number in api_data
            $custom_sql = "UPDATE `api_data` SET `page` = ".$new_page."  WHERE `TagID` = ".$TagID_target_tag." AND `APIID` = 1 ";
            $resp  = UPDATE(false,false,false,false,false,false,$images_conn,$custom_sql);
          }
        }
        else {
          // API responded with no imges for this page, this means all images of this tag are allready in the Databse and thus no future APi calls are needed
          //update DB to prevent futhur API calls for this tag, set Exhausted to 1
          $custom_sql = "UPDATE `api_data` SET `Exhausted` = 1  WHERE `TagID` = ".$TagID_target_tag." AND `APIID` = 1 ";
          UPDATE(false,false,false,false,false,false,$images_conn,$custom_sql);
          // returns TAG_EXAHUSTED to show that tag show be released from session intrests
          return "Tag Exhausted";
        }
        return $response["total_results"];
      }

    }
  }
?>
