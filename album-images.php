<?php
include('scripts/settings.php');
validate();
page_struct();
echo page_sidebar($links);

if(isset($_GET['del1'])){
   $sql_del = "UPDATE images SET img_status = '1' WHERE sno = " . $_GET['del1'];
    $data_del = mysqli_query($con, $sql_del);
    echo '<script>
        swal("Deleted!", "Image Has been Deleted!", "error");
    </script>';
  
}
// Fetch department from query parameter
$department = isset($_GET['department']) ? htmlspecialchars($_GET['department']) : '';

if ($department) {
    
    // Build the SQL query to fetch images for the specific department
   $sql = "
    SELECT 
        upload.sno AS id, 
        upload.img_title, 
        upload.img_department, 
        GROUP_CONCAT(DISTINCT upload_trans.tag SEPARATOR ', ') AS tags, 
        images.file_path, 
        images.sno AS sno
    FROM 
        images
    JOIN 
        upload ON upload.sno = images.img_id
    JOIN 
        upload_trans ON upload.sno = upload_trans.upload_id
    WHERE 
        images.img_status = 0 AND upload.img_department = '$department'
    GROUP BY 
        images.sno, 
        upload.sno, 
        upload.img_title, 
        upload.img_department, 
        images.file_path";

   // echo $sql;
    $result = $con->query($sql);

    // Check for query errors
    if ($result === FALSE) {
        die("Error: " . $con->error);
    }

    // Check if any images are found for the department
    if ($result->num_rows > 0) {
        // Fetch images
        $images = [];
        $title = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row['file_path'];
            $title [] = $row['img_title'];
            $album_name [] = $row['img_department'];
            $tag[] = $row['tags'];
            $snos[] = $row['sno'];
        }
    } else {
        echo "No images found for this album.";
        exit;
    }
} else {
    echo "Album not found.";
    exit;
}


?>

<div style="background-color: #ccc;">
    <?php page_header(); ?>
    <div class="output" style="max-width: 100%; padding: 20px 80px 50px 80px;">
    <a href="albums.php" class="btn back-btn" style="height:30px;padding:2px 10px;background-color: #FF0080; color: white;position:absolute;margin:-15px 0px 0px -70px;">Back</a>
    <h4 class="center-btn" style="margin-bottom: 30px;">Files in <?= htmlspecialchars($department) ?></h4>
    <div style="display: flex; flex-wrap: wrap; gap: 20px 50px;">
        <?php 
        $counter = 0;
        foreach ($images as $index => $image): 
            $sno = $snos[$index];
            $counter++;
        ?>
            <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between;">
    <?php 
   
        // Fixed container width to match image width
        echo "<div style='width: 210px; margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1), 0 6px 20px rgba(0, 0, 0, 0.1); border-radius: 20px; padding: 10px; background: white;'>";

        $file_extension = pathinfo($image, PATHINFO_EXTENSION); // Get file extension

        if (in_array($file_extension, ['jpg','JPG', 'jpeg', 'png', 'gif', 'webp', 'heic','tiff', 'jfif'])) {
            // Show image
            echo "<div class='image-container'>
                    <img src='upload-images/$image' class='clickable-image' style='width: 170px; height: 120px; object-fit: cover; border: 2px solid rgb(9, 9, 103); border-radius: 5px;' onclick='openPopup(<?= $index ?>)'>
                  </div>";
        } else if (in_array($file_extension, ['pdf'])) {
            // Show 'View' link for document files
            echo "<div class='doc-container text-center'>
                    <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                        <img src='images/pdf.png' width='170px' height='120px'>
                    </a>
                  </div>";
        } else if (in_array($file_extension, ['doc', 'docs', 'docx'])) {
            // Show 'View' link for document files
            echo "<div class='doc-container text-center'>
                    <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                        <img src='images/doc.png' width='170px' height='120px'>
                    </a>
                  </div>";
        } else if (in_array($file_extension, ['xls', 'xlsx'])) {
            // Show 'View' link for document files
            echo "<div class='doc-container text-center'>
                    <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                        <img src='images/xls.png' width='170px' height='120px'>
                    </a>
                  </div>";
        } else if (in_array($file_extension, ['ppt', 'pptx'])) {
            // Show 'View' link for document files
            echo "<div class='doc-container text-center'>
                    <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                        <img src='images/ppt.png' width='170px' height='120px'>
                    </a>
                  </div>";
        }
        else if(in_array($file_extension, [ 'mp4','x-ms-wmv','x-flv','avi','mpeg','ogg','webm'])) {
            // Show 'View' link for document files
            echo "<div class='doc-container text-center'>
                    <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                        <img src='images/video.png' width='170px' height='120px'>
                    </a>
                  </div>";
        }

        echo "<div class='text-center' style='font-size:14px;'>";
        echo "<b>Title:</b> " . $title[$index] . "<br>";
        echo "<b>Album:</b> " .$album_name[$index] . "<br>";
        echo "<b>Tags:</b> " . $tag[$index] . "<br>";

        // Include the sno in the heart and download link section
        echo "<a href='upload-images/$image' download='$image' style='text-decoration:none;'>
                <i style='font-size:20px;margin-top:5px;color:blue;cursor:pointer' class='fa fa-download' aria-hidden='true'></i>
              </a>";

              echo '<span>
              <a href="album-images.php?department='.urlencode($department).'&&del1=' . $sno . '" 
                 onclick="return confirm(\'Are you sure you want to delete this image?\');" 
                 style="color:#f00; text-decoration: none;" 
                 alt="Delete">
                 <i class="far fa-trash-alt" style="font-size:20px; margin-left:20px;" data-toggle="tooltip" title="Delete"></i>
              </a>
            </span>';
      
        echo "</div>"; // End of text-center
        echo "</div>"; // End of image/doc div

        $counter++;
    
    ?>
</div>
        <?php endforeach; ?>
    </div>
</div>


    <!-- Popup Modal Structure -->
    <div id="image-popup" class="popup" style="display: none;">
    <span class="close" onclick="closePopup()">&times;</span>
     <!-- Download & Delete Buttons -->
     <div class="popup-actions" style="text-align:center; margin-top:20px;">
        <a id="popup-download" href="#" download class="popup-btn" style="text-decoration:none; margin-right: 20px;">
            <i class="fa fa-download" style="font-size:20px; color:white;"></i>
        </a>
        <a id="popup-delete" href="#" class="popup-btn" style="text-decoration:none; color:white;"
           onclick="return confirm('Are you sure you want to delete this image?');">
            <i class="far fa-trash-alt" style="font-size:20px;"></i>
        </a>
    </div>
    <img class="popup-content" id="popup-image">
    
   

    <div id="prev" class="popup-prev" onclick="changeImage(-1)">&#10094;</div>
    <div id="next" class="popup-next" onclick="changeImage(1)">&#10095;</div>
</div>

    <?php page_footer(); ?>
</div>

<script>
  var images = <?= json_encode($images) ?>; // Array of image paths
var snos = <?= json_encode($snos) ?>; // Array of image IDs (for deletion)
var department = <?= json_encode($department) ?>; // Department for deletion URL

var currentIndex = -1;

// Open popup for image
function openPopup(index) {
    currentIndex = index; 
    updatePopup();
    document.getElementById("image-popup").style.display = "block";
}

// Close popup
function closePopup() {
    document.getElementById("image-popup").style.display = "none";
}

// Change image with Next/Prev
function changeImage(direction) {
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = images.length - 1; // Loop to last
    if (currentIndex >= images.length) currentIndex = 0; // Loop to first
    updatePopup();
}

// Update popup content (Image, Download, Delete)
function updatePopup() {
    var imagePath = "upload-images/" + images[currentIndex];
    var sno = snos[currentIndex];

    // Set image
    document.getElementById("popup-image").src = imagePath;

    // Set download button
    document.getElementById("popup-download").href = imagePath;

    // Set delete button link
    document.getElementById("popup-delete").href = "album-images.php?department=" + encodeURIComponent(department) + "&del1=" + sno;
}

// Keyboard navigation for Next/Prev
document.addEventListener('keydown', function(event) {
    if (event.key === "ArrowRight") changeImage(1);
    else if (event.key === "ArrowLeft") changeImage(-1);
});

</script>
