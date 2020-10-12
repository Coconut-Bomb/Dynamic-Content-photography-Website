<?php
  require("header.php");
?>
    <main>
      <!wrapper for the modal>
      <div id="modal-display" class="modal-wrapper">
        <!modal div>
        <div class="modal">
        </div>
      </div>

      <! main wrapper>
      <div class="wrapper-main">
        <!defulat section for content>
        <section class="section-default" style="background-color: rgba(1,1,1,0); border: none;">
          <!images wrapper>
          <div class="wrapper-images"></div>
        </section>
      </div>
    </main>
    <!Load more content in when bottom of page reached>

    <script type="text/javascript">
      // sets div_id to 5 as 1-5 are loaded automaticaly on page load
      var div_id = 5;
      // stops the request of imags in chase of error and acts as time buffer new content can only be requested after old content is succesfuly loaded
      var request_img_ready = false;

      //triggers when suer scrolls of the page
      $(window).scroll(function (event_obj) {
        //checks if the user is within 20 px of the bottom of that page and if  request_img_ready == true
        if (   ( $(document).height()<=($(window).scrollTop()+$(window).height()+20) )  && request_img_ready == true ) {
          //  request_img_ready is set to false to prevent another request being sent before this one is loaded
          request_img_ready = false;
          // Get_Img_Data is called with the div_id
          Get_Img_Data(div_id);
          // div_id is then incromented by 5  (this is because Get_Img_Data returns 5 divs at once)
          div_id = div_id + 5;
        }
      });

      // this function is called when the user is not logged if
      function login_prompt(){
        //prevents a img request from being send when user reaches botom of page
        request_img_ready = false;
        // add a message to prompt the user to log in
        $(".wrapper-images").append('<div class="login_prompt">Please log in above.</div>');
      }

      // this function is used to add a ImgID to the users favorites
      function add_to_favs(ImgID){
        // prepares data for a request to the server
        var data = {
          data_request: "ADD TO FAVS",// request type
          UserID: <?php if (isset($_SESSION["UserID"])) {echo $_SESSION["UserID"];}else {echo "null";}?>, // this Users ID
          ImgID: ImgID // ImgID of the image to be add to the Users favorites
        }
        // sends a HTTP POST request to DB_user_data_handler.ex.php to add this ImgID to the Users favorites
        $.post("extra/DB_Handlers/DB_user_data_handler.ex.php", data , function (resp1) { //on response
          // Note since the server does not response with any large data the response is in plain text rather than JSON
          // if request was successful
          if (resp1== "success") {
            // change colour of the "Add to Favs" button to green to indicate to the user the request was successful
            $("#add_to_favs_button").css("background-color","green");
          }
        });
      }

      // this function is used when a user attempts to add a comment to an image, the ImgID is passed into the func
      function add_comment(ImgID){
        // prepares data for a request to the server
        var data = {
          request: "add_comment", // request type
          UserID: <?php if (isset($_SESSION["UserID"])) {echo $_SESSION["UserID"];}else {echo "null";}?>, // Users ID
          ImgID: ImgID, // ImgID that the comment is to be add to
          comment: document.getElementById("add-comment-input").value // gets comment from the value of the div with the id of "add-comment-input"
        }
        console.log(data);
        // sends a HTTP POST request to the server to add the comment to the image
        $.post("extra/DB_Handlers/DB_messager_handler.ex.php", data , function (resp2) { //on response
          console.log(resp2);
          //reload commetns
          // prepares to request the comments for a image
          var data = {
            request: "load_comments", // request type
            ImgID: ImgID // Image Id of the Image to get the commets for
          }
          console.log(data);
          // sends a HTTP POST request to the server (DB_messager_handler.ex.php) for the comments array
          $.post("extra/DB_Handlers/DB_messager_handler.ex.php", data , function (resp3) { //on response
            console.log(resp3);
            // parse response from JSON to a php array
            var data_array = JSON.parse(resp3);
            //clear previous comments
            $(".display-comments").empty();
            // clears the comment input feild
            $("#add-comment-input").val("")
            // if there are comments for this ImgID
            if (data_array.length > 0 ) {
              // make the display-comments div visible to the user
              $(".display-comments").css("display","block");
              // for array in response
              for (var i = 0; i < data_array.length; i++) {
                // gets variables from the array
                var UserID = data_array[i][1];
                var comment = data_array[i][2];
                var timeStamp = data_array[i][0];
                // adds a single comment using the timeStamp, UserID and comment from the array
                $(".display-comments").append("<div>"+timeStamp+"   UserID:"+UserID+"   "+comment+"</div>");
              }
            }
          });
        });
      }

      // this function is triggered when the user clicks on any image, org_src id the link to the orignal image in full resolution
      //ImgID id the Id of the image and UserID is the Id of the User
      function on_click_image(org_src,ImgID,UserID){
        //remove previoues modal content
        $(".modal").empty();
        //add new modal content
        //add exit button to the modal, which on click hiddes the modal-wrapper
        $(".modal").append("<span onclick='$(\".modal-wrapper\").css(\"display\",\"none\")' class='button display-topright'>&times;</span>");

        // prepares data to be send off to the server
        var data = {
          data_request: "IMG CLICK", // request type
          UserID: <?php if (isset($_SESSION["UserID"])) {echo $_SESSION["UserID"];}else {echo "null";}?>, // Users Id
          ImgID: ImgID // ID of this Image
        }
        // sends a HTTP POST request to DB_user_data_handler.ex.php telling the server detial about the image the user has clicked on
        $.post("extra/DB_Handlers/DB_user_data_handler.ex.php", data , function (resp4) {
        });

        // prepares data to form a data request to the server
        var data = {
          data_request: "GET IMG DATA", // request type
          ImgID: ImgID // Id of the image
        }
        // sends a HTTP POST to DB_image_handler.ex.php to request data from the server
        $.post("extra/DB_Handlers/DB_image_handler.ex.php", data , function (resp5) { //on response
          // parse the response from JSON to a php array
          var data_array = JSON.parse(resp5);
          // [width,height,author_username,author_link,tag]
          // sets variables fromt eh response
          var width = data_array[0];
          var height = data_array[1];
          var Author = data_array[2];
          var Author_Url = data_array[3];
          var Tag = data_array[4];

          //adds downloadable link on click user downloads the image in the modal, the name of the downloaded image is its tag and its ImgID
          $(".modal").append("<a id='download-image' href='"+org_src+"' target='_blank' download='"+Tag+'_'+ImgID+"'><img src='"+org_src+"' alt='"+Tag+"'></a>");
          // sets default vale for Author_page, if the image is user uploaded there will be no link avalible
          Author_page = "";
          // is Author_Url is not null (if the image is not user uplaoded and does have a author link)
          if (Author_Url != null) {
            // sets Author_page to a list item containing a link to the authors page
            Author_page = "<li><a href='"+Author_Url+"' target='_blank'> Author's page: </a></li>";
          }
          // adds Image info to the modal in the form of a list, this list includes ImgID, width, height, Author and Authors page (if applicable). It also adds the "Add to Favs" button to the modal
          $(".modal").append("<div class='modal-img-info'><ul class='ul-no-style' style='margin-bottom: 10px;' ><li style='margin-bottom:5px'>Please click on the image if you wish to downlaod it.</li><li>Image ID: "+ImgID+"</li><li>Image Tag: "+Tag+"</li><li>Width: "+width+"px</li><li>Height: "+height+"px</li><li>Author: "+Author+"</li>"+Author_page+"</ul><span id='add_to_favs_button' class='button' onclick='add_to_favs("+ImgID+")' >Add to Favs</span></div>");

          // prepares to send a request to the server for all the comments for a image
          var data = {
            request: "load_comments", // request type
            ImgID: ImgID // ImgID
          }
          // sends a HTTP POST request to DB_messager_handler.ex.php to request comments for a Image
          $.post("extra/DB_Handlers/DB_messager_handler.ex.php", data , function (resp6) { //on response
            // parse the JSON response to a php array
            var data_array = JSON.parse(resp6);
            // add the comments-wrapper and display-comments divs to the modal-img-info div
            $(".modal-img-info").append("<div class='comments-wrapper'><p>Comments:</p><div class='display-comments'></div></div>");
            // if the response has comments to add
            if (data_array.length > 0 ) {
              // for every comment is response
              for (var i = 0; i < data_array.length; i++) {
                // gets variables from the response
                var UserID = data_array[i][1];
                var comment = data_array[i][2];
                var timeStamp = data_array[i][0];
                // adds the comment to the display-comments div, each comment has the UserId of the sender and the comment itself
                $(".display-comments").append("<div>"+timeStamp+"   UserID:"+UserID+"   "+comment+"</div>");
              }
            }
            else {
              //hide comment display
              $(".display-comments").css("display","none");
            }
            // adds the add-comment-form div to the modal-img-info div, this add-comment-form div contains an input for a comment and a submit button whuch will triggeer add_comment on click
            $(".modal-img-info").append("<div class='search-form' id='add-comment-form'><input id='add-comment-input' type='text' name='message' placeholder='Add a Comment'><button type='text' value='submit' name='submit' onclick='add_comment("+ImgID+")'>Add Comment</button></div>");
          });
        });
        //makes the whole modal visable
        $(".modal-wrapper").css("display","block");
      }

      // this function is designed to get the data required to adds 5 rows of images to index.php, it has 1 paramater which is the stating div id of 
      //  the new row of images it returns the boolean varible request_img_ready to show if the code is ready to request another block of images
      function Get_Img_Data(div_id){
        // prepares data for a request to the server for image data
        var data = {
          data_request: "RECEIVE BLOCK", // request type
          div_id: div_id, // starting div
          user_id: <?php if (isset($_SESSION["UserID"])) {echo $_SESSION["UserID"];}else {echo "null";}?> // users ID
        }
        // sends a HTTP POST request to DB_image_handler.ex.php to get image data
        $.post("extra/DB_Handlers/DB_image_handler.ex.php", data , function (resp7) { //on response
          // response is parsed from JSON to a php array
          var data_array = JSON.parse(resp7);
          // for each array in response try
          for (var i = 0; i < data_array.length; i++) {
            try {
              //load all the varablies from the response ready to pass to load_img_data function
              var img_id = data_array[i]["img_id"];
              var src = data_array[i]["src"];
              var org_src = data_array[i]["org_src"]
              var tag = data_array[i]["tag"];
              var div_id = data_array[i]["div_id"][0];
              var error = data_array[i]["error"];
              var row = [img_id,src,tag,div_id,error]
              var UserID = <?php if ( isset($_SESSION["UserID"]) ){ echo $_SESSION["UserID"]; }else{ echo "null"; }?>;
            } catch (e) {
              var error = data_array[i]["error"];
            }
            // if no error was reported my the server
            if (error != "stop img requests") {
              // a total of 20 images are added to the wrapper-images div, images are split up into groups of 4 in a row-images div.
              // each Image elemt has its id set to the ImgID, its alt set to the image tag, its class set to hover-opacity, its src set to the image source (Portait for API images and orginal to fit for user uplaoded images)
              // each image elemnt also has a onclick function which is on_click_image(), the orignal source, ImgID and UserID are passed intot his func
              $(".wrapper-images").append('<div id="'+div_id+'"class="row-images"><img class="hover-opacity" id="'+img_id[0]+'" onclick="on_click_image(\''+org_src[0]+'\','+img_id[0]+','+UserID+')" src="'+src[0]+'" alt="'+tag[0]+'" ><img class="hover-opacity" id="'+img_id[1]+'" onclick="on_click_image(\''+org_src[1]+'\','+img_id[1]+','+UserID+')" src="'+src[1]+'" alt="'+tag[1]+'" ><img class="hover-opacity" id="'+img_id[2]+'" onclick="on_click_image(\''+org_src[2]+'\','+img_id[2]+','+UserID+')" src="'+src[2]+'" alt="'+tag[2]+'" ><img class="hover-opacity" id="'+img_id[3]+'" onclick="on_click_image(\''+org_src[3]+'\','+img_id[3]+','+UserID+')" src="'+src[3]+'" alt="'+tag[3]+'" ></div>');
              // images are successfully added to the wrapper-images div
              // request_img_ready is set to true, ready to make another request for more images
              request_img_ready = true;
            }
            else {
              // an caught error has ocured server side and request_img_ready is set to false to prevent any more request going to the server for more images,
              // this is like to happen if the user only sets 1 intrest. The supply of images with that tag is quickly exhausted and the Database nor the API
              // has any more images to give the user. This error can be fixed by a page refresh
              request_img_ready = false;
            }
          }
        });
      }

      <?php
      // checks if user is logged in
        if (isset($_SESSION["UserID"])) {
          // kick starts the page by immediately calling the function Get_Img_Data with the start div_id of 0
          echo "Get_Img_Data(0);";
        }
        else {
          // calls the function login_prompt to prompt the user to log in
          echo "login_prompt();";
        }
      ?>
    </script>
  </body>
</html>
