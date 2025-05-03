<?php
include('scripts/settings.php');
validate();
page_struct();
echo page_sidebar($links);
?>
	<div style="background-color: #ccc;">
	  <?php page_header();?>
	  <div class="counter px-2">
  <div class="col-12 col-sm-6 d-flex" style="width:375px !important;">
    
    <!-- Total Albums -->
    
    <div class="info-box my-3 mx-2 col-md-6">
    <a href="albums.php" style="text-decoration:none;">
      <span class="info-box-icon bg-info elevation-1"><i class="fas fa-images"></i></span>  </a>
      <div class="info-box-content">
        <span class="info-box-text">Total Albums</span>
        <span class="info-box-number">
        <?php 
          $sql = "SELECT COUNT(DISTINCT img_department) as unique_img_department_count FROM upload WHERE img_status = '0';";
          $result = mysqli_query($con, $sql);               
          if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo $row["unique_img_department_count"];
          } else {
            echo "0";
          }
        ?>
        </span>
      </div>
    </div>
    
    <!-- Total Images -->
    <div class="info-box my-3 mx-2 col-md-6">
    <a href="total.php?id=Images" style="text-decoration:none;">
      <span class="info-box-icon bg-info elevation-1"><i class="fa fa-image"></i></span> </a>
      <div class="info-box-content">
        <span class="info-box-text">Total Images</span>
        <span class="info-box-number">
        <?php 
          $sql = "SELECT COUNT(*) as total_rows FROM images WHERE img_status = '0' AND (file_path LIKE '%.jpg' OR file_path LIKE '%.jpeg' OR file_path LIKE '%.png' OR file_path LIKE '%.gif' OR file_path LIKE '%.jfif' OR file_path LIKE '%.heic');";
          $result = mysqli_query($con, $sql);               
          if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo $row["total_rows"];
          } else {
            echo "0";
          }
        ?>
        </span>
      </div>
    </div>
   
    <!-- Total PDF -->
    <div class="info-box my-3 mx-2 col-md-6">
    <a href="total.php?id=PDF" style="text-decoration:none;">
      <span class="info-box-icon bg-info elevation-1"><i class="fas fa-file-pdf"></i></span>  </a>
      <div class="info-box-content">
        <span class="info-box-text">Total PDF</span>
        <span class="info-box-number">
        <?php 
          $sql = "SELECT COUNT(*) as total_rows FROM images WHERE img_status = '0' AND file_path LIKE '%.pdf';";
          $result = mysqli_query($con, $sql);               
          if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo $row["total_rows"];
          } else {
            echo "0";
          }
        ?>
        </span>
      </div>
    </div>
    
    <!-- Total EXCEL -->
    
    <div class="info-box my-3 mx-2 col-md-6">
    <a href="total.php?id=Excel" style="text-decoration:none;">
      <span class="info-box-icon bg-info elevation-1"><i class="fas fa-file-excel"></i></span><a href="albums.php" style="text-decoration:none;"> </a>
      <div class="info-box-content">
        <span class="info-box-text">Total EXCEL</span>
        <span class="info-box-number">
        <?php 
          $sql = "SELECT COUNT(*) as total_rows FROM images WHERE img_status = '0' AND (file_path LIKE '%.xls' OR file_path LIKE '%.xlsx');";
          $result = mysqli_query($con, $sql);               
          if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo $row["total_rows"];
          } else {
            echo "0";
          }
        ?>
        </span>
      </div>
    </div>
 
    <!-- Total PPT -->
     
    <div class="info-box my-3 mx-2 col-md-6">
    <a href="total.php?id=PPT" style="text-decoration:none;">
      <span class="info-box-icon bg-info elevation-1"><i class="fas fa-file-powerpoint"></i></span> </a>
      <div class="info-box-content">
        <span class="info-box-text">Total PPT</span>
        <span class="info-box-number">
        <?php 
          $sql = "SELECT COUNT(*) as total_rows FROM images WHERE img_status = '0' AND (file_path LIKE '%.ppt' OR file_path LIKE '%.pptx');";
          $result = mysqli_query($con, $sql);               
          if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo $row["total_rows"];
          } else {
            echo "0";
          }
        ?>
        </span>
      </div>
    </div>
  
    <!-- Total DOCS -->
   
    <div class="info-box my-3 mx-2 col-md-6">
    <a href="total.php?id=DOC" style="text-decoration:none;">
      <span class="info-box-icon bg-info elevation-1"><i class="fa fa-file"></i></span> </a>
      <div class="info-box-content">
        <span class="info-box-text">Total DOCS</span>
        <span class="info-box-number">
        <?php 
          $sql = "SELECT COUNT(*) as total_rows FROM images WHERE img_status = '0' AND (file_path LIKE '%.doc' OR file_path LIKE '%.docx');";
          $result = mysqli_query($con, $sql);               
          if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo $row["total_rows"];
          } else {
            echo "0";
          }
        ?>
        </span>
      </div>
    </div>
  </div>
</div>
<!-- end --->
		
		<div class="counter" style="padding:20px;">
		<h6 class="center-btn mb-12">Featured Photos &nbsp;<i style="font-size: larger;" class="fa fa-hand-o-down"></i></h6>
		<div id="carouselExample" class="carousel slide" data-bs-ride="carousel" style="width:120% !important;">
  <div class="carousel-inner pb-2">

    <?php 
    $sql = "SELECT * FROM images WHERE like_status=1;";
    $result = mysqli_query($con, $sql);
    $count = 0; // Counter to track the number of images
    $active = true; // To set the first item as active

    while($row = mysqli_fetch_assoc($result)) {
      if ($count % 5 == 0) { // Start a new carousel item after every 5 images
        if ($count > 0) {
          // Close the previous carousel item div
          echo '</div></div>';
        }
        // Open a new carousel item div
        echo '<div class="carousel-item'.($active ? ' active' : '').'">';
        echo '<div style="overflow-x: hidden !important;" class="row">';
        $active = false; // Set active to false after the first item
      }
    ?>
      <div class="col-md-2">
        <img  class='clickable-image' src="upload-images/<?php echo $row['file_path']; ?>" class="d-block" alt="Image <?php echo $count + 1; ?>" height="170px" style="width:230px;">
      </div>
    <?php 
      $count++;
    }
    // Close the last carousel-item and row if they are not closed yet
    if ($count > 0) {
      echo '</div></div>';
    }
    ?>

  </div>

  <!-- Carousel controls -->
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next mx-5" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>

<div id="image-popup" class="popup" style="display: none;">
        <span class="close" onclick="closePopup()">&times;</span>
        <img class="popup-content" id="popup-image">
    </div>
		</div>

		<?php
 
page_footer();
?>
	</div>





