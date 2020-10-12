<?php
  // add header.php to top of page
  require "header.php"
?>
    <main>
      <script type="text/javascript">
      </script>
      <!wrapper for all content below header>
      <div class="wrapper-main">
        <!the default section for content>
        <section class="section-default">
          <!Title for the page>
          <h1>Upload Image</h1>
          <!the form that the user fills out with the information needed to sign up along with the page location where this data is processed>
          <form class="form-t1" action="extra/upload.ex.php" method="POST" enctype="multipart/form-data"> <!>
            <!passes UserID trough form>
            <input id="UserID" type="text" name="UserID" style="display:none;" value="<?php if (isset($_SESSION["UserID"])) {echo $_SESSION["UserID"];}
            else {echo "null";}?>">
            <!passes Uername through form>
            <input id="UserUid" type="text" name="UserUid" style="display:none;" value="<?php if (isset($_SESSION["UserUid"])) {echo $_SESSION["UserUid"];}
            else {echo "null";}?>">
            <!sign up success message normally hidden>
            <div id="success-alert"> SUCCESS </div>
            <!file upload input>
            <input id="fileToUpload" class="upload-top-inputs" type="file" name="fileToUpload" placeholder="">
            <!reset file currently uploaded by calling file_reset() func>
            <input id="upload-reset" class="upload-top-inputs" type="reset" onclick="file_reset()" >
            <p>Please enter a Tag as a single word that best describes the image you have uploaded</p>
            <p>(Accepted file formates are: png, jpg and jpeg)</p>
            <!user entered form data>
            <input id="tag" type="text" name="tag" placeholder="Tag" value="">
            <!user entered form data>
            <input id="width" type="number" name="width" placeholder="width" value="">
            <!user entered form data>
            <input id="height" type="number" name="height" placeholder="height" value="">
            <!error display>
            <p class="form-error" id="general-error"></p>
            <p>Select image to upload</p>
            <!submit button>
            <button id="upload-submit" type="submit" name="submit" >Upload</button> <!onclick="send_upload()">
          </form>
        </section>

      </div>

      <script type="text/javascript">
        // when file is uploaded
        $('#fileToUpload').change(function() {
          // change colour of file upload input to green
          $('#fileToUpload').css("background-color", "green");
        });


        // resets the all form data
        function file_reset(){
          $("#fileToUpload").val("");
          $("#tag").val("");
          $("#width").val("");
          $("#height").val("");
          $("#upload-submit").css("display","block");
          $('#fileToUpload').css("background-color", "#e0e0e0");
        }


        <?php
        // if there was an error with the file upload
        if (isset($_GET["error"])) {
          // display the error message in the general-error <p>
          echo '$("#general-error").html("'.$_GET["error"].'")';
        }elseif (isset($_GET["upload_file"])){
          if ($_GET["upload_file"] == "success") { // if there is a success message
            // show the success alert on the form
            echo '$("#success-alert").css("display", "block")';
          }
        }


        ?>
      </script>

    </main>

<?php
  // add footer.php to top of page
  require "footer.php"
?>
