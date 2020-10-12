<?php
  // add header.php to top of page
  require "header.php";
?>

    <main>
      <script type="text/javascript">
      //fuction that removes the returned message from requesting from addfriend.ex.php from the display
        function remove_add_friend_result(){
          $("#add-friend-result").remove();
        }
        // fuction that adds a message to inform the user to login
        function login_prompt(){
          $(".section-default").append('<div class="login_prompt">Please log in to use messages.</div>');
        }
        //function that loads a convosation between two users, ChatId is passed into this func
        function load_convo(ChatID) {
          //removes the send message search bar ready for a new convasation to be loaded
          $(".send-message").remove();
          //removes the mesasge display div ready for a new convasation to be loaded
          $(".messages-display").remove();
          //adds back the empty messages-display and send-message div
          $(".messages-convo-wrapper").append("<div class='messages-display'></div> <div class='send-message'></div> ");

          //sets all the data needed to load a new covo in a array to be send of tp the DB_messager_handler.ex.php file
          var data = {
            request: "load_convo",//request type
            ChatID: ChatID //ID of the chat to be loaded
          }
          //sends a POST HTTP request to DB_messager_handler.ex.php file with the data array
          $.post("extra/DB_Handlers/DB_messager_handler.ex.php", data , function (resp) { //on response
            var mess = JSON.parse(resp); // parse the JSON response into a php array
            var UserID = <?php if (isset($_SESSION["UserID"])) {echo $_SESSION["UserID"];}else {echo "null";}?>; // Gets this Users ID

            // for every message in the convo loaded
            for (var i = 0; i < mess.length; i++) {
              //if the message is type text the message content in a certain format
              if (mess[i][2] == "text") {
                // message info ()
                var message_display_content = '<span>'+mess[i][1]+' '+mess[i][3]+'</span>';
              }
              //if the message is type img the message content in a different format allowing the message to act as a link when clicked on displaying the image
              else if (mess[i][2] == "img") {
                var message_display_content = '<a href="'+mess[i][3]+'" target="_blank" >'+mess[i][1]+' < Image >'+'</span>';
              }
              //if message is from this user is is the message content is displayed with a certain class that will change how the message is displayed indercating who the message is from
              if (mess[i][0] == UserID) {
                $(".messages-display").append('<div class="sent-message"> '+message_display_content+' </div>');
              }
              else { // mess is from the other user the message content is displayed with a different class that will change how the message is displayed indercating who the message is from
                $(".messages-display").append('<div class="received-message">'+message_display_content+'</div>');
              }
            }
            //after all messages from the convo have been added, the send "form" is add that allows the user to input text and either send a text message or a img message using 2 different butoms to specify which message type they want to send, the "form" also has a chat-id-passthrough inut that is not accessable to the user but is used to keep track of the Chat ID. Both buttons send a call to the send_message function with the messs type as a paramater
            //the input field for the message to be sent has the input type of ext meaning only text can be entered and the inputed data is treated as a string
            $(".send-message").append(" <div class='search-form' id='send-message-form'> <input id='send-message-input' type='text' name='message' placeholder='Send a message or Image ID'> <input style='display:none'id='chat-id-passthrough' value='"+ChatID+"'> <button type='text' value='submit' name='submit' onclick='send_message(\"text\")' >Send Message</button> <button type='text' value='submit' name='submit' onclick='send_message(\"img\")' >Send Image</button> </div> "),"\n";
          });
        }
        // This function takes 1 parameter, a 2D array that contains the username,
        //last message and userID of all other users this user has ever sent or received messages from.
        //This function take this 2D array and for every array in the 2D array,
        //it add a conversation header to  the .messages-side-bar div
        function add_all_convo_headers(convos_array){
          //[name,lastmessage,userid] /*[["James78","Nice one mate",78421],["Jenny_moo","U like cows?",287613]...]
          //if the convos_array has  a length of 0 the user has no friends and a message is added to the webpage prompting them to add friends
          if (convos_array.length == 0) {
            //  add a message informing the use that they have no friends and prompts them to add some
            $(".wrapper-messages").append('<div class="" style="margin-left:5px"<p>Looks Like you\'ve got no friends 😐 Search their ID to add them.</p></div>');
            // messages-side-bar is hidden as there are no convo headers to display
            $(".messages-side-bar").css('display','none');
            //messages-convo-wrapper is hidden as there are no messages to display
            $(".messages-convo-wrapper").css('display','none');
          }
          else {// if the user has had at least 1 covno
            // for each array in convos_array+
            for (var i = 0; i < convos_array.length; i++) {
              //default font size for the username of the convo header
              var font_size = 16;
              // if the usernme is over length 13, the font size is decreased so it fitts better
              if (convos_array[i][0].length > 13) {
                font_size = 12;
              }
              // each array in convos_array is then add the to .messages-side-bar as a div displaying their username and the last maessage send between the two users
              // the div is clickable and calls the function load_convo with User ID of the 2nd user
              // all div element appended have has a element id containing the index of the convos_array the data came from, this is to separtat all the added elements so that they can be identified individually
              $(".messages-side-bar").append('<div class="messages-side-bar-convo" id="messages-side-bar-convo'+i+'" onclick="load_convo('+convos_array[i][3]+')" </div>');
              // adds the anme of the user this User is chatting to, to the messages-side-bar-convo+i div
              $("#messages-side-bar-convo"+i+"").append('<div class="messages-side-bar-name"><p style="font-size:'+font_size+'px">'+convos_array[i][0]+'</p></div>');
              // add the last messaged between the two users to the messages-side-bar-convo+i div
              $("#messages-side-bar-convo"+i+"").append('<div class="messages-side-bar-last-message"><p>'+convos_array[i][1]+'</p></div></div>');
            }
          }
        }
        //This fuction sends a request to DB_user_data_handler.ex.php to add a friend with a specified ID, This Users ID is passed into this func
        function addfriend(UserID0){
          //The UserID varible is got from the value of the input with the id of search-friend-input
          UserID1 = document.getElementById("search-friend-input").value;
          // creats the array containing all the data that wil be sen to DB_user_data_handler.ex.php
          var data = {
            data_request: "ADD FRIEND", // This is the request type
            UserID0: parseFloat(UserID0), //This users ID
            UserID1: parseFloat(UserID1) //This user by ID to be added as a friend
          }

          // A POST HTTP request is sent to DB_user_data_handler.ex.php and the response is loaded
          $.post("extra/DB_Handlers/DB_user_data_handler.ex.php", data , function (resp) {
            // (Note no large amounts of data is backed back by the server and thus the data is not in JSON)
            // on successful response this code is executed
            if (resp == "success") { // if the request to add friend was successful
              // a success message is displayed to the user informing them the add friend attempt was susseccful
              $( "#friend-search-form" ).append( "<p style='color: green' id='add-friend-result'>Success</p>" );
              //after 1500ms the function remove_add_friend_result is called to remove the success message
              setTimeout(remove_add_friend_result,1500)
            } //if the response is "already friends")
            else if (resp == "already friends") {
              //a message is displayed to the user informing them the add friend attempt was unsusseccful because they are allready friends with the ID they entered
              $( "#friend-search-form" ).append( "<p style='color: red' id='add-friend-result'>already friends</p>" );
              setTimeout(remove_add_friend_result,1500) //after 1500ms the function remove_add_friend_result is called to remove the  message
            }
            else if (resp == "user not found") {
              //a message is displayed to the user informing them the add friend attempt was unsusseccful because the UserID they entered was not found in the Database
              $( "#friend-search-form" ).append( "<p style='color: red' id='add-friend-result'>user not found</p>" );
              setTimeout(remove_add_friend_result,1500)//after 1500ms the function remove_add_friend_result is called to remove the  message
            }
            else if (resp == "you are this user") {
              //a message is displayed to the user informing them the add friend attempt was unsusseccful because the UserID they entered was not found in the Database
              $( "#friend-search-form" ).append( "<p style='color: red' id='add-friend-result'>You are This User</p>" );
              setTimeout(remove_add_friend_result,1500)//after 1500ms the function remove_add_friend_result is called to remove the  message
            }

          });
        }
        //this function if called when a the user attempt to send a message, the inputed data by the user is first validated before the message is sent
        // the message type is passed into the func
        function send_message(mess_type){
          var go_ahead = false; // This varible determins wether the data is vaild to be send off, by default it is set to false
          var message = document.getElementById("send-message-input").value; // This gets the user inputed text from the input element with the id of send-message-input
          var ChatID = document.getElementById("chat-id-passthrough").value;// This gets the ChatID from the hidden input element with the id of chat-id-passthrough
          // if the message has a length of 0 the message is considered invalid and is not sent
          if ($.trim(message).length != 0) {
            if (mess_type == "text") { // if the message has a type of text
            // checks if the message contains a \ or a ' character
              if (message.includes('\\') || message.includes('\'')){
                $('#send-message-input').val('');// the value currently in the input element with id of send-message-input is cleared, thus displayign the place holder in its place
                //the place holder of the input element with id of send-message-input is then changed to show the user that they must input a positive intager in order to send a image message
                $('#send-message-input').attr("placeholder", "--------- The \\ and ' characters are not allowed  -------");
                setTimeout(function(){$("#send-message-input").attr("placeholder", "Send message or Imge ID");},2000); // after 2000ms the placeholder is set back to what it was before
              }
              // if a \ or ' is not found in the message it is valid
              else{
                go_ahead = true;
              }
            }
            // if the message type is img and the message is successfuly transformed to a intager which is positive it is then considered to be valid
            else if (mess_type == "img" && Number.isInteger(parseFloat(message)) && message >= 0) {
              go_ahead = true;
            }
            else { // else the inputed message must have failed to be transformed to a intager or is negative in which case
              $('#send-message-input').val('');// the value currently in the input element with id of send-message-input is cleared, thus displayign the place holder in its place
              //the place holder of the input element with id of send-message-input is then changed to show the user that they must input a positive intager in order to send a image message
              $('#send-message-input').attr("placeholder", "---------- Must enter a Positive Number  ----------");
              setTimeout(function(){$("#send-message-input").attr("placeholder", "Send message or Imge ID");},2000); // after 2000ms the placeholder is set back to what it was before
            }
          }

          if (go_ahead == true) {  //if the message inputed by the user is deemed vaild for the type of message they want to send
            // prepares data to be sent off to the server
            var data = {
              request: "send_message", // request type
              ChatID: parseFloat(ChatID), // ID of the convo between the two users
              message: message, // message content
              senderID: <?php if (isset($_SESSION["UserID"])) {echo $_SESSION["UserID"];}else {echo "null";}?>, // UserID of the sender
              mess_type: mess_type // type od message ("text" or "img")
            }
            // sends a POST HTTP request to DB_messager_handler.ex.php to add validate and  the message request server side
            $.post("extra/DB_Handlers/DB_messager_handler.ex.php", data , function (resp) { // on sucess
              // (Note no large amounts of data is backed back by the server and thus the data is not in JSON)
              if (resp == "imgID not found") {
                $('#send-message-input').val('');// the value currently in the input element with id of send-message-input is cleared, thus displayign the place holder in its place
                //the place holder of the input element with id of send-message-input is then changed to tellt eh user that the ImgID they entered doesnt exsist in the Database
                $('#send-message-input').attr("placeholder", "---------- Image ID does not exsist ----------");
                setTimeout(function(){$("#send-message-input").attr("placeholder", "Send message or Imge ID");},2000);// after 2000ms the placeholder is set back to what it was before

              }
              else if (resp == "success") {
                // request is sussessful and the convosation is reloaded to show the newly added message after 500ms (gives time for the server to update everything server side)
                setTimeout(function(){load_convo(ChatID)},500);
              }


            });
          }
        }

      </script>
      <!main wrapper div>
      <div class="wrapper-main" style="height:500px">
        <!default section for content>
        <section class="section-default" style="height:460px">
          <!messages wrapper>
          <div class="wrapper-messages" style="display:none"> <!felx row> </div>

        </section>
      </div>

    </main>

<?php
      //checks is the User is loged in
      if (!isset($_SESSION["UserID"])) {
        // if user is not logged in login_prompt() is called
        echo '<script type="text/javascript"> login_prompt();',
        '</script>';
      }
      else {
        // if user is logged in the frame work for the page is added
        // Add the add-friend-search-bar and friend-search-form to the top of the section-default
        echo '<script type="text/javascript"> ',
          ' $("<div class=\'add-friend-search-bar\'> ',
            ' <div class=\'search-form\' id=\'friend-search-form\' > ',
              ' <span>Enter User ID to add a Friend</span> ',
              ' <input id=\'search-friend-input\' type=\'number\' name=\'UserID0\' placeholder=\'Friend ID\'> ',
              ' <button type=\'text\' value=\'submit\' name=\'submit\' onclick=\'addfriend(\\"'.$_SESSION["UserID"].'\\")\' >Add Friend</button> ',
              ' </div> ',
          ' </div> ',
          ' ").insertBefore(".wrapper-messages");   ',
          "\n";

        // makes wrapper-messages visable and adds messages-side-bar to it
        echo '$(".wrapper-messages").css("display","flex");',"\n",
          '$(".wrapper-messages").append("<div class=\'messages-side-bar\'>',
          '</div>");',"\n",
          // adds messages-convo-wrapper to wrapper-messages, and adds messages-display and end-message divs to wrapper-messages
          '$(".wrapper-messages").append("<div class=\'messages-convo-wrapper\'>',
            '<div class=\'messages-display\'>',
             '<p style=\"font-size: 24px;text-align: center;\"> Please sellect a conversation to start messaging</p>',
            '</div> ',
            '<div class=\'send-message\'>',
            '</div>',
          '</div>");',"\n";

        // afther the frame work of the page is all layed out the page automaticly sends a data request to  DB_messager_handler.ex.php
        // to get all the convo header for this user. It then calls the func add_all_convo_headers() with the array response of the server
        // Prepares data for the request, including the request type and this Users ID
        echo 'var data = {request: "get_all_convo_headers",user_id0: '.$_SESSION["UserID"]. '} ',//
            "\n",
            // sends a HTTP POST request to the server with the data array just created
          '$.post("extra/DB_Handlers/DB_messager_handler.ex.php", data , function (resp) {',

            'var data_array = JSON.parse(resp);', // respose is parsed from JSON into a php array
            'console.log(resp);'.
            'add_all_convo_headers(data_array);', // this array is then passed into the func add_all_convo_headers()
          '});',
          '</script>';
      }
      // add footler.php to top of page
      require "footer.php";
?>
