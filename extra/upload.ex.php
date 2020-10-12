<?php
// gets Multi_DB_Handler.db.php allowing access to the functions in this file
require "DB_Handlers/Multi_DB_Handler.db.php";

// this function takes 1 paramater and returns the Findex (fingerprint index) for that input string
function findex_gen($file_name){
  $values = [];
  // merges visualy similar characters. Any character in the first
  // array will be transformed to the sinlge char is the second array
  // this is to prevent duplicate images being uploaded with just a small change
  // to the file name while keeping it readable
  $lookalikes = [
    [["O"],["0"]],
    [["L","I","|"],['1']],
    [["Z"],['2']],
    [["E"],['3]']],
    [["A","H"],['4']],
    [["S"],['5']],
    [["G"],['6']],
    [["T"],['7']],
    [["B"],['8']],
    [["Q"],['9']],
    [["@"],["A"]]
  ];

  $file_name = strtoupper($file_name);// converts all english character to uppercase
  for ($i=0; $i < strlen($file_name) ; $i++) { // for every char in file name
    $char = substr($file_name, $i, 1); // gets the idevidual char within the fime name
    for ($x=0; $x < sizeof($lookalikes) ; $x++) {// for size of the lookalikes array
      if(in_array($char,$lookalikes[$x][0])){ // if the char from the file name is in the first array with look alikes
        $char = $lookalikes[$x][1][0]; // it is then converted to the char is the seconds array
      }
    }
    // adds the ASCCI value of the char to the array values
    array_push($values, ord($char));
  }
  // finger_index of set to 1 by default
  $finger_index = 1;
  // i
  $i = 0;
  $s = 0;
  // failure keeps track of how many caculations have been performed on the finger_index
  // if the number of calculation exceedes a set rate it is assumed finger_index is not
  // increasing and is stuck in a loop,the while loop is broken and a call to fatal_error.php
  // is sent. In theoory this will never happen as finger_index will only ever be multiplied by
  // positive numbers above 1, meaning it will only ever increase and never decrease and the cal to fatal_error.php will never be sent
  $failure = 0;
  //finger_index is caculated using a somewhat recursive algorithm meaning the output of one algorithm run is fed into the input of the seccond algorithm run.
  while ($finger_index < 100000000000000) { // 100,000,000,000,000
    // if $failure exceeds  5000 calculations
    if ($failure > 5000) {
      // null is returned
      return null;
      // and the while loop is broken
      break;
    }
    // if $i is bigger or equal to the size of $values
    if ($i >= sizeof($values)) {
      //$i is reset to 0 to prevent a undefined index error
      $i = 0;
    }
    // is $s is bigger that 10
    if ($s > 10) {
      // $s is reset to 0
      $s=0;
    }
    // $finger_index is calculated by timesing $finger_index by the next item in $values, this is repeared for each item in $values starting at start of the array ending when fingure index is above
    //100000000000000, if the end of the array is reached and finger index has not reached the threshold the algorithm goes back to the stat of the values array and continues.
    // $s is added to the value in the array before it is timesed, this is because timesing the same numbers in different orders does not effect the output, without using the $s varible
    // the input "A gentleman" will give the same fingure index as "Elegant man", $s gives value to a chars position in the string instead of just is ASCCI value
    // since the ASCCI values for common english letters are arround 60, $finger_index may increase too rapidly hitting the 100000000000000 threshold before going
    // thorugh the entire vaules array at least once. This is bad as the input IMG_20192210_1645 will give the same ouput as IMG_20192210_1846 as the first 12 character are the same,
    // to solve this ($values[$i]) + $s is divided by 10 allowing the entire string input to be used when caculating the fingure index.
    $finger_index = $finger_index * (($values[$i] + $s)/10);
    $i++;
    $failure++;
  }
  // rounds $finger_index to nearest intager
  $finger_index = round($finger_index);
  // returns finger index
  return $finger_index;
}

// this function checks if a file with a similar file name has allready been uploaded by a user, it takes 2 parameter, the file name and the connection to the images database
function findex_check($file_name,$images_conn){
  // uses findex_gen function to get the fingure index for the file name
  $finger_index = findex_gen($file_name);
  // queryies the image_hash_table table in the images database too see if this fingure index allready exsists in the data base
  $resp = SELECT("ImgID","image_hash_table","Findex",$finger_index,"i",$images_conn,False);
  // if the ws a response there will be a collision
  if (!empty($resp)) {
    return True;
  }else {
    // no results found and thu no collision
    return False;
  }
}

// this function is used to add images to the database, its takes data about the image such a tag, size and location aswell as info about the user such as UserID
//and UserUid as paramaters
function add_img_to_DB($file_name,$width,$height,$tag,$UserID,$UserUid,$location,$images_conn,$users_conn){
  //checks if author is allready in the DB
  $result = SELECT("AuthorID","authors","UserAID",$UserID,"i",$images_conn,False);
  // result was empty
  if (empty($result)) {
    echo "-- CREATING AUTHOR ID --";
    //author doesnt exist in DB, adds author to database
    INSERT("authors",array("UserAID","AuthorUid"),array($UserID,$UserUid),"is",$images_conn);
    // gets the AuthorID just entered for this Author/User
    $result = SELECT("AuthorID","authors","UserAID",$UserID,"i",$images_conn,False);
    $AuthorID = $result[0];
    echo "-- AUTHOR ID GOT --";
  }
  else {///if author does exist in DB
    echo "-- AUTHOR ID GOT --";
    $AuthorID = $result[0];
  }
  // checks if the user etered tag exsists in the tags table
  $result = SELECT("TagID","tags","Tag",$tag,"s",$images_conn,False);
  // if result was empty
  if (empty($result)) {
    echo "-- CREATING TAG ID --";
    // add the user inputed tag for the uploaded image in to the tags table
    INSERT("tags",array("Tag"),array($tag),"s",$images_conn);
    // gets the TagID of the tage just enterd in to the tag table
    $result = SELECT("TagID","tags","Tag",$tag,"s",$images_conn,False);
    $TagID = $result[0];
    echo "-- AUTHOR ID GOT --";
  }
  else {
    echo "-- TAG ID GOT --";
    // if tag allready exsists in the tag table, gets its TagID
    $TagID = $result[0];
  }
  //insert new user uploaded iMG to image_data table
  INSERT("image_data",array("ImgSrcID","AuthorID","TagID","DisplayCount","width","height","file"),array( 1 , $AuthorID , $TagID , 0 , $width , $height , $location ),"iiiiiis",$images_conn);
  // gets the ImgID of the user uploaed image just added to the image_data table
  $result = SELECT("ImgID","image_data","file",$location,"s",$images_conn,false);
  $ImgID = $result[0];
  // adds the finger_index and ImgID to the image_hash_table table
  INSERT("image_hash_table",array("Findex","ImgID"),array(findex_gen($file_name),$ImgID),"ii",$images_conn);

  // Updates posts for this user to show the uploaded images details
  echo "-- GETTING POSTS --";
  $result = SELECT("posts","users","UserID",$UserID,"i",$users_conn,false);
  $posts = unserialize($result[0]);
  // adds the Image  ID and current date and time to the $posts 2D array
  array_push($posts, [$ImgID,date('d,M,Y h:i A')]);
  // reserializes the $posts 2D array
  $posts = serialize($posts);
  // Updates the posts column in the users table to show the new pots array
  UPDATE("users","posts","'".$posts."'","UserID",$UserID,"i",$users_conn,false);
  echo "-- POSTS UPDATED --";

}

// checks if submit is pressant
if (isset($_POST["submit"])) {

  // sets variables
  // the file itself
  $file = $_FILES["fileToUpload"];
  // the file name
  $file_name = $_FILES["fileToUpload"]["name"];
  // the temporty location of the file
  $file_TmpName = $_FILES["fileToUpload"]["tmp_name"];
  // file size in bytes
  $file_size = $_FILES["fileToUpload"]["size"];
  // any file errors
  $file_error = $_FILES["fileToUpload"]["error"];
  // file type
  $file_type = $_FILES["fileToUpload"]["type"];

  // users set width
  $width = $_POST["width"]; // string
  // user set height
  $height = $_POST["height"]; // string
  // user set tag of image
  $tag = strtolower($_POST["tag"]);

  // gets the Uers ID
  $UserID = $_POST["UserID"];
  // gets the users Username
  $UserUid = $_POST["UserUid"];
  // if any of the user inputs are blank or just white space
  if ($width == "" || $height == "" || $tag == "" || ctype_space($width) || ctype_space($height) || ctype_space($tag)) {
    // redirect the user back to upload.php with an error message in the URl
    header("Location: ../upload.php?error=Please Ensure All Fields Are Filled Out");
  }
  else {
    // formats the user inputed tag to make it more compatible with an API
    // removes whitspace before and after the tag
    $tag = trim($tag);
    // replaces any spaces in the tag with a +
    $tag = str_replace(' ', '+', $tag);

    // if there wsa no error while uplaoding the file to the server
    if ($file_error == 0) {
      // split the file at "."
      $fileExplode = explode(".",$file_name);
      // gets the lowwercase file extension of the file
      $fileExt = strtolower(end($fileExplode));
      // sets the allowed file extensions
      $valid_ext = ["jpg","jpeg","png"];
      // checks if the uploaed image is of the allowed file types
      if (in_array($fileExt,$valid_ext)) {
        // checks the files is excessively large (above 60MB)
        if ($file_size < 60000000) {
          // cecks if there will be no collison in the image_hash_table table if the imge is add to the table, uses findex_check function to do this
          if(!findex_check($fileExplode[0],$images_conn)){
            // generates a new file name uising uniqid func with the prefix img_, and adds the mounth, day, year, hour, minuite, second and ms  to the end of the file name
            $file_New_Name = uniqid("img_",true).date('M,d,Y_h-i-s-v A').".".$fileExt;
            // sets the path, from this file, for the server to move the file into from its temport location
            $file_store = "../user_images/".$file_New_Name;
            // sets the path in relation to the folder user_images
            $location = "user_images/".$file_New_Name;
            // trigger the add_img_to_DB function to add all the necessary data to the data base
            add_img_to_DB($fileExplode[0],$width,$height,$tag,$UserID,$UserUid,$location,$images_conn,$users_conn);
            // moves the file from its tempory location the user images folder, also changes the name id the file
            move_uploaded_file($file_TmpName,$file_store);
            // redirect the user back to upload.php with a success message
            header("Location: ../upload.php?upload_file=success");
          }
          else {
            // redirect the user back to upload.php
            header("Location: ../upload.php?error=Collision, similar file has allready been uploaded");
          }
        }
        else {
          // redirect the user back to upload.php with a error message
          header("Location: ../upload.php?error=File is too Big");
        }
      }
      else {
        // redirect the user back to upload.php with a error message
        header("Location: ../upload.php?error=File Type Not accepted");
      }
    }
    else {
      // redirect the user back to upload.php with a error message
      header("Location: ../upload.php?error=File Failed To Upload Please Try Again");
    }
  }
}

?>
