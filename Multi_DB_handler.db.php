<?php

  function connect_user_DB(){
    // sets up aribles to connect to the users database
    $users_servername = "localhost";
    $users_DB_Username = "root";
    $users_DB_Password = "zEJ5GWv1D08WfDQF";
    $users_DB_Name = "users";
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    //create connection and bind to the varible $users_conn
    $users_conn = mysqli_connect($users_servername, $users_DB_Username, $users_DB_Password, $users_DB_Name);
    //test connection
    if (!$users_conn) {
      // connection failed
      die("Connection Failed:".mysqi_connect_error());
    }
    else {
      // returns the database connectio so that it may be used
      return $users_conn;
    }
  }
  function connect_images_DB(){
    // sets up aribles to connect to the images database
    $imagers_servername = "localhost";;
    $images_DB_Username = "root";
    $images_DB_Password = "zEJ5GWv1D08WfDQF";
    $images_DB_Name = "images";

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    //create connection and bind to the varible $images_conn
    $images_conn = mysqli_connect($imagers_servername, $images_DB_Username, $images_DB_Password, $images_DB_Name);
    //test connection
    if (!$images_conn) {
      // connection failed
      die("Connection Failed:".mysqi_connect_error());
    }
    else {
      // returns the database connectio so that it may be used
      return $images_conn;
    }
  }

  $users_conn = connect_user_DB();
  $images_conn = connect_images_DB();

  // This is one of the must used function in my code, it allowed me to easy form a prepared statment to query the dataabse using just a function> It is design to query just 1 field and takes only other field as the condition
  //allthought there is a option to use a custom sql statment as a parameter in which case any sql statment may be formed. (Note if using a custom sql statment all other paramaters apart from the connection is set to false)
  function SELECT($field,$table,$condition,$equal_to,$equal_to_data_type,$conn,$custom_sql){
    if ($custom_sql) { //if custom sql is set ,the default sql statment is not needed
      $sql = $custom_sql;
    }
    else{//plugs the paramarters into the default sql prepared statment
      $sql = "SELECT $field FROM $table WHERE $condition = ?"; //prepared statment ( prevents SQL injection)
    }
    $result_array = [];
    $stmt = mysqli_stmt_init($conn); //connects the statment to DB
    if (!mysqli_stmt_prepare($stmt,$sql)) {//prepare statment with query
      // if the connection failed to prepare the sql statment in the database
      echo $sql;
      //header("Location: ../fatal_error.php?error=sqlerror&page=Multi_DB_Handler.db.php&line=59");
      exit();
    }
    // if a $equal_to_data_type is set (if not using a custom sql statment)
    elseif ($equal_to_data_type) {
      mysqli_stmt_bind_param($stmt,$equal_to_data_type,$equal_to); //binds parapaters to statment
    }
    //searches DB
    mysqli_stmt_execute($stmt); //executes the statment/query
    // stores the result
    $result = mysqli_stmt_get_result($stmt);
    // while there are still rows in the query result to fetch
    while ($row = mysqli_fetch_assoc($result)){
      // if $field is set (if a custom sql has been used)
      if ($field == null) {
        // added the entire row to the results array (this is because a custom sql statment may query 2 fields at the saem time, in which case $row is an array containing the 2 values of the 2 colums queried)
        array_push($result_array,$row);
      }
      else {
        // adds the dessired filed from row to the results_array (in theory $row will olny contain the field dessired)
        array_push($result_array,$row[$field]);
      }
    }
    // returns the result of the query
    return $result_array;
    // closes the connection to the database
    mysqli_stmt_close($stmt);
  }
  // This  function allows me to easy form a prepared statment to insert data into the dataabse using just a function> It is design to insert up to 10 columns at a time which is plenty for my project
  // there is no option to use a custom sql statmet for this function as it is not needed in my code, and to insert data into a table is rather simple
  function INSERT($table,$fields_array,$values_array,$values_data_type,$conn){
    //preparing to enter details into DB
    // sets $fields to a string containing the first column/field from $fields_array
    $fields = $fields_array[0];
    // sets $value_question_marks to "?"
    $value_question_marks = "?";
    // every column/field in $fields_array after the 1st item
    for ($field=1; $field < sizeof($fields_array); $field++) {
      // appends next column/field to the string $fields in the correct formated way for the sql prepared statment to undersatnd
      $fields .= ",".$fields_array[$field];
      // appends "?" to the string $value_question_marks, this ensures there are the same number of ?'s in $value_question_marks as there are columns/fields in $fields
      $value_question_marks .= ",?";
    }
    // add the formated data to the sql statment and stores it in the varible $sql
    $sql = "INSERT INTO $table($fields) VALUES($value_question_marks) ";
    //connects the statment to DB
    $stmt = mysqli_stmt_init($conn);
    //if sql stmt failed
    if (!mysqli_stmt_prepare($stmt,$sql)) {
      // if the connection failed to prepare the sql statment in the database
      header("Location: ../fatal_error.php?error=sqlerror&page=Multi_DB_Handler.db.php&line=84");
      exit();
    }
    else {
      // different code need to be ran depending of the amount to columns the want to be entered into the table
      // A switch is used to run the correct code for the size of $fields_array, this ensures all the paramaters of the prepared statment are bound correclty
      switch(sizeof($fields_array)){
        case 1:
          // binds the paramaters entered into the function to the sql statment
          mysqli_stmt_bind_param($stmt, $values_data_type, $values_array[0]);
          break;
        case 2:
          mysqli_stmt_bind_param($stmt, $values_data_type, $values_array[0], $values_array[1]);
          break;
        case 3:
          mysqli_stmt_bind_param($stmt, $values_data_type, $values_array[0], $values_array[1], $values_array[2]);
          break;
        case 4:
          mysqli_stmt_bind_param($stmt, $values_data_type, $values_array[0], $values_array[1], $values_array[2], $values_array[3]);
          break;
        case 5:
          mysqli_stmt_bind_param($stmt, $values_data_type, $values_array[0], $values_array[1], $values_array[2], $values_array[3], $values_array[4]);
          break;
        case 6:
          mysqli_stmt_bind_param($stmt, $values_data_type, $values_array[0], $values_array[1], $values_array[2], $values_array[3], $values_array[4], $values_array[5]);
          break;
        case 7:
          mysqli_stmt_bind_param($stmt, $values_data_type, $values_array[0], $values_array[1], $values_array[2], $values_array[3], $values_array[4], $values_array[5], $values_array[6]);
          break;
        case 8:
          mysqli_stmt_bind_param($stmt, $values_data_type, $values_array[0], $values_array[1], $values_array[2], $values_array[3], $values_array[4], $values_array[5], $values_array[6], $values_array[7]);
          break;
        case 9:
          mysqli_stmt_bind_param($stmt, $values_data_type, $values_array[0], $values_array[1], $values_array[2], $values_array[3], $values_array[4], $values_array[5], $values_array[6], $values_array[7], $values_array[8]);
          break;
        case 10:
          mysqli_stmt_bind_param($stmt, $values_data_type, $values_array[0], $values_array[1], $values_array[2], $values_array[3], $values_array[4], $values_array[5], $values_array[6], $values_array[7], $values_array[8], $values_array[9]);
          break;

      }
      // executes the query
      mysqli_stmt_execute($stmt);
      return true;
      // cloeses the connection
      mysqli_stmt_close($stmt);
    }
  }

  // This function in my code allows me to easy form a prepared statment to update the dataabse using just a function> It is design to update as many columns as needed.
  // There is also a option to use a custom sql statment as a parameter in which case any sql statment may be formed. (Note if using a custom sql statment all other paramaters apart from the connection is set to false)
  function UPDATE($table,$fields,$new_values,$condition,$equal_to,$equal_to_data_type,$conn,$custom_sql){ //note $new_values must be quated when inputed
    // is custom sql statment is set
    if ($custom_sql) {
      $sql = $custom_sql;
    }
    // else use the default statment
    else{
      // plugs the paramaters passed into the function into the sql statments
      $sql = "UPDATE $table SET $fields = $new_values  WHERE $condition = ?"; //prepared statment (prevents SQL injection)
    }
    // sets results array
    $result_array = [];
    $stmt = mysqli_stmt_init($conn); //connects the statment to DB
    if (!mysqli_stmt_prepare($stmt,$sql)) {//prepare statment with query
      // failed to prepare the statment in the databse
      header("Location: ../fatal_error.php?error=sqlerror&page=DB_image_handler.ex.php&line=160");
      exit();
    }
    else {
      // if custom sql is not being used
      if ($equal_to_data_type) {
        mysqli_stmt_bind_param($stmt,$equal_to_data_type,$equal_to); //binds parapaters to statment
      }
      // executes the query
      mysqli_stmt_execute($stmt);
      // closes the connection to the database
      mysqli_stmt_close($stmt);

    }
  }
