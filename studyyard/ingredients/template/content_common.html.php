

<div id="content_actually">
  <?php
   //$content_mode_templatefile = "";
   echo $content_mode;
   switch ($content_mode) {
     case "index":
       $content_mode_templatefile = "content_index.html.php";
       break;
     case "listing":
       $content_mode_templatefile = "content_listing_content.html.php";
       break;
     default:
       $content_mode_templatefile = "content_404.html.php";
       break;
   };

   eval_template($content_mode_templatefile);

  ?>
</div>
