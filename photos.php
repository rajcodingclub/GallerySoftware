<?php
include('scripts/settings.php');
validate();
page_struct();
echo page_sidebar($links);

if (isset($_POST['selected_files']) && !empty($_POST['selected_files'])) {
    // Array of selected file names
    $selected_files = $_POST['selected_files'];
    
    // Define the path to the upload directory
    $upload_dir = 'upload-images/';
    
    // Create a new ZIP archive
    $zip = new ZipArchive();
    
    // Create a temporary file to hold the ZIP archive
    $zip_file = tempnam(sys_get_temp_dir(), 'zip');
    
    if ($zip->open($zip_file, ZipArchive::CREATE) !== TRUE) {
        exit("Cannot open <$zip_file>\n");
    }

    // Loop through each selected file and add it to the ZIP archive
    foreach ($selected_files as $file) {
        $file_path = $upload_dir . basename($file); // Ensure the file path is correct and secure
        
        if (file_exists($file_path)) {
            $zip->addFile($file_path, basename($file)); // Add file to ZIP
        }
    }

    // Close the ZIP archive
    $zip->close();

    // Set headers to force download of the ZIP file
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=selected_files.zip');
    header('Content-Length: ' . filesize($zip_file));

    // Read the ZIP file and send it to the browser
    readfile($zip_file);

    // Delete the temporary ZIP file after download
    unlink($zip_file);

    exit();
} else {
    // No files were selected, show an error message or redirect
    
}

?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div style="background-color: #ccc;">
  <?php page_header();?>
  
  <div class="formdiv">
  <h6 style="width:220px;margin:0px auto 20px auto;" class="center-btn blink text-center">Filter Your Search &nbsp;<i style="font-size: larger;" class="fa fa-hand-o-down"></i></h6>
    <form class="row g-2" method="Post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>">
    <div class="row">
    <div class="col-md-4">
    <label for="select_date" class="form-label">Date Type</label>
    <select class="form-select form-select-sm" name="date_type" id="">
        <option value="">Select</option>
        <option value="timestamp">Upload Date</option>
        <option value="upload_date">Event Date</option>
    </select>
    <!-- <input type="date" class="form-control form-select-sm" name="select_date" id="select_date"
        max="<?php echo date('Y-m-d'); ?>"> -->
</div>
<div class="col-md-4">
        <label for="tag" class="form-label">Date from:</label> 
          <input class="form-control" type="date" name="date_from" id="" value="">
      </div>
      <div class="col-md-4">
        <label for="tag" class="form-label">Date To:</label>  
          <input class="form-control" type="date" name="date_to" id="" value="">
      </div>
</div>
<div class="row g-2">
      <div class="col-md-4">
        <label for="department" class="form-label">Album</label>
        <select class="form-select form-select-sm" aria-label="Small select example" name="department" id="department">
          <option value="">ALL</option>
          <?php
			$sql = 'select img_department from upload group by img_department';
			$result_department = mysqli_query($con,$sql);
			while($row= mysqli_fetch_assoc($result_department)){
				 echo "<option value='" . $row["img_department"] . "'>" . $row["img_department"] . "</option>";
			}									
			?>
        </select>
      </div>
      <div class="col-md-4">
        <label for="type" class="form-label">File Type</label>
        <select class="form-select form-select-sm" aria-label="Small select example" name="type" id="type">
          <option value="">ALL</option>
          <?php
			$sql = 'select img_type from upload group by img_type';
			$result_type = mysqli_query($con,$sql);
			while($row = mysqli_fetch_assoc($result_type)){
				echo "<option value='" . $row["img_type"] . "'>" . $row["img_type"] . "</option>";
			}									
			?>
        </select>

      </div>
      <div class="col-md-4">
        <label for="tag" class="form-label">Tags</label>
        <!-- <div class="tag-input-container">
          <input type="hidden" id="hidden-tag-input" name="tags">
          <input type="text" name="tag" id="tag-input" value="" autocomplete="off">
          <div id="suggestions"></div>
        </div> -->
        
        <select class="js-example-tags form-control form-select-sm" name="tags" id="tag-input"  onchange="toggleOtherTagInput(this, <?php echo $i;?>)">
            <option value="">Select Tag</option>
            <?php
                // Fetch options from the database
                $query = "SELECT DISTINCT upload_id, tag FROM upload_trans GROUP BY tag;";
                $result = mysqli_query($con, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = (isset($_POST['tag_'.$i]) && $_POST['tag_'.$i] == $row['upload_id']) ? 'selected' : '';
                        echo "<option value='" . $row['tag'] . "' $selected>" . $row['tag'] . "</option>";
                    }
                }
            ?>

        </select>
      </div>
      </div>
      <div class="col-md-4"></div>
      <div class="col-12 text-center">
        <button type="submit" name="submit" class="btn btn-primary text-center mt-2" 
          style="background-color: #FF0080;color:white;">SEARCH</button>
      </div>

    </form>
  </div>
  <div style="background:white;" class="output">
  <h4 class="center-btn">Gallery</h4>
  <?php

$conditions = [];
$sql = 'SELECT 
          upload.*, 
          GROUP_CONCAT(DISTINCT upload_trans.tag SEPARATOR ", ") AS tags,
          GROUP_CONCAT(DISTINCT images.file_path SEPARATOR ", ") AS file_path,
          GROUP_CONCAT(DISTINCT images.sno SEPARATOR ", ") AS img_sno,
          GROUP_CONCAT(DISTINCT images.like_status SEPARATOR ", ") AS like_status,
          GROUP_CONCAT(DISTINCT images.img_tag SEPARATOR ", ") AS img_tags
      FROM 
          upload
      LEFT JOIN 
          upload_trans ON upload.sno = upload_trans.upload_id
      LEFT JOIN 
          images ON upload.sno = images.img_id
      WHERE 1=1 AND upload.img_status=0';

// Date range filtering
if (!empty($_POST['date_type'])) {
    $conditions[] = "'" . mysqli_real_escape_string($con, $_POST['date_from']) . "' <= DATE(" . mysqli_real_escape_string($con, $_POST['date_type']) . ") 
        AND '" . mysqli_real_escape_string($con, $_POST['date_to']) . "' >= DATE(" . mysqli_real_escape_string($con, $_POST['date_type']) . ")";
}

// Filter by type
if (!empty($_POST['type'])) {
    $conditions[] = 'img_type="' . mysqli_real_escape_string($con, $_POST['type']) . '"';
}

// Filter by department
if (!empty($_POST['department'])) {
    $conditions[] = 'img_department="' . mysqli_real_escape_string($con, $_POST['department']) . '"';
}

// Filter by tags (including img_tag in images table)
if (!empty($_POST['tags'])) {
    $tags = str_replace('"', '', $_POST['tags']); // Remove extra quotes
    $tags = array_map('trim', explode(',', $tags)); // Split by comma and trim spaces
    $escaped_tags = array_map(function ($tag) use ($con) {
        return '"' . mysqli_real_escape_string($con, $tag) . '"';
    }, $tags);

    $conditions[] = '(
        upload_trans.tag IN (' . implode(',', $escaped_tags) . ')
        OR images.img_tag IN (' . implode(',', $escaped_tags) . ')
    )';
}

// Append conditions to SQL query
if (!empty($conditions)) {
    $sql .= ' AND ' . implode(' AND ', $conditions);

echo $sql .= ' GROUP BY upload.sno';
$result_data = mysqli_query($con, $sql);
    $counter = 0;
    echo "<form id='downloadForm' action='photos.php' method='POST'>";
    echo "<button class='btn btn-primary' type='button' id='selectAllBtn'>Select All</button>&nbsp;&nbsp;";
    echo "<button class='btn btn-success' type='submit' id='downloadSelectedBtn'>Download Selected</button>";
    echo "<div style='display: flex; flex-wrap: wrap; gap:30px; margin-bottom: 20px;'>"; 

    while ($row = mysqli_fetch_array($result_data)) {
        $inputString = $row['file_path'];
        $imagesArray = explode(', ', $inputString);
        $imagesArray = array_map('trim', $imagesArray);

        $inputStrin = $row['img_sno'];
        $imagesArra = explode(', ', $inputStrin);
        $imagesArra = array_map('trim', $imagesArra);

        $inputStri = $row['like_status'];
        $likeArray = explode(', ', $inputStri);
        $likeArray = array_map('trim', $likeArray);

        $imagesWithSno = []; // 2D array to hold images and their corresponding sno

        // Use a for loop to iterate over both arrays
        for ($i = 0; $i < count($imagesArray); $i++) {
            $imagesWithSno[] = [
                'image' => $imagesArray[$i],
                'sno' => $imagesArra[$i],
                'like_status'=> $likeArray[$i] // Associate the 'sno' with each image
            ];
        }

        foreach ($imagesWithSno as $entry) {
            $image = $entry['image'];
            $sno = $entry['sno'];
            $likeStatus = $entry['like_status']; // Get the current like status from the database

            // Define the heart color based on like_status
            $heartColor = $likeStatus == 1 ? 'red-heart' : 'grey-heart'; 

            // Start of image/document div with checkbox for multi-select
            echo "<div style='width: 18%; margin: 20px 0px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1), 0 6px 20px rgba(0, 0, 0, 0.1); border-radius: 20px; padding: 10px; background: white;'>";
            echo "<input type='checkbox' name='selected_files[]' value='$image' class='file-checkbox'>"; // Checkbox for selection

            $file_extension = pathinfo($image, PATHINFO_EXTENSION); // Get file extension

            if (in_array($file_extension, ['jpg','JPG', 'jpeg', 'png', 'gif', 'webp', 'heic','tiff', 'jfif'])) {
                // Show image
                echo "<div class='image-container'>
                        <img style='width: 170px; height: 120px; object-fit: cover; border: 2px solid rgb(9, 9, 103); border-radius: 5px;' src='upload-images/$image' class='clickable-image'>
                      </div>";
            } else if(in_array($file_extension, ['pdf'])) {
                // Show 'View' link for document files
                echo "<div class='doc-container text-center'>
                        <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                         <img src='images/pdf.png' width='170px' height='120px'>
                        </a>
                      </div>";
            } else if(in_array($file_extension, ['doc','docs','docx'])) {
                // Show 'View' link for document files
                echo "<div class='doc-container text-center'>
                        <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                         <img src='images/doc.png' width='170px' height='120px'>
                        </a>
                      </div>";
            }
            else if(in_array($file_extension, ['xls','xlsx'])) {
                // Show 'View' link for document files
                echo "<div class='doc-container text-center'>
                        <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                         <img src='images/xls.png' width='170px' height='120px'>
                        </a>
                      </div>";
            }
            else if(in_array($file_extension, ['ppt','pptx'])) {
                // Show 'View' link for document files
                echo "<div class='doc-container text-center'>
                        <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                         <img src='images/ppt.png' width='170px' height='120px'>
                        </a>
                      </div>";
            }
            else if(in_array($file_extension, [ 'mp4','x-ms-wmv','x-flv','avi','mpeg','ogg','webm'])){
                // Show 'View' link for document files
                echo "<div class='doc-container text-center'>
                        <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                         <img src='images/video.png' width='170px' height='120px'>
                        </a>
                      </div>";
            }

            echo "<div class='text-center' style='font-size:14px;'>";
            echo "<b>Title:</b> " . $row['img_title'] . "<br>";
            echo "<b>Album:</b> " . $row['img_department'] . "<br>";
            echo "<b>Tags:</b> " . $row['img_tags'] . "<br>";

            // Include the sno in the heart and download link section
            echo "<a href='upload-images/$image' download='$image' style='text-decoration:none;'>
                    <i style='font-size:20px;margin-top:5px;color:blue;cursor:pointer' class='fa fa-download' aria-hidden='true'></i>
                  </a><span style='margin-left:20px;margin-top:5px;'>
                  <i id='heart-$sno' class='fas fa-heart heart $heartColor' data-row-value='$sno' style='cursor: pointer;'></i></span>";

            echo "</div>"; // End of text-center

            echo "</div>"; // End of image/doc div

            $counter++;
            if ($counter % 5 == 0) {
                echo "</div><div style='display: flex; flex-wrap: wrap; justify-content: space-between; margin-top: 20px;'>"; // Start a new row after 5 items
            }
        }
    }
    echo "</form>";
}
echo "</div>"; // End of the last row container
?>

</div>

<?php
 
page_footer();
?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    // Attach click event handler to the heart icons
    $('.heart').on('click', function() {
        var sno = $(this).data('row-value'); // Get the 'sno' value from the data attribute
        var $heartIcon = $(this);
        var isLiked = $heartIcon.hasClass('red-heart'); // Check if it's already liked (red)

        // Determine the new like status
        var newLikeStatus = isLiked ? 0 : 1;

        // Toggle the heart color
        $heartIcon.toggleClass('red-heart grey-heart');

        // Send AJAX request to update like_status in the database
        $.ajax({
            url: 'scripts/ajax2.php',
            method: 'POST',
            data: { sno: sno, like_status: newLikeStatus }, // Send new status (1 or 0)
            success: function(response) {
                console.log(response); // For debugging purposes
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });
});
</script>


<style>
    .red-heart {
        color: red;
        font-size: 30px; /* Heart turns red when liked */
    }
    .grey-heart {
        color: grey;
        font-size: 25px; /* Heart is grey by default */
    }
</style>

<script>
    // Handle 'Select All' functionality
    let isAllSelected = false; // Track the selection state

document.getElementById('selectAllBtn').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('.file-checkbox');
    
    // Toggle checkboxes based on the current state
    checkboxes.forEach(checkbox => checkbox.checked = !isAllSelected);
    
    // Update button text based on the new state
    this.textContent = isAllSelected ? 'Select All' : 'Deselect All';
    
    // Toggle the selection state for the next click
    isAllSelected = !isAllSelected;
});

    // Handle 'Download Selected' button
    document.getElementById('downloadSelectedBtn').addEventListener('click', function() {
        const selectedFiles = [];
        const checkboxes = document.querySelectorAll('.file-checkbox:checked');
        checkboxes.forEach(checkbox => selectedFiles.push(checkbox.value));

        if (selectedFiles.length === 0) {
            alert('Please select at least one file.');
            return;
        }

        // Submit the form to download the selected files
        document.getElementById('downloadForm').submit();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(".js-example-tags").select2({
  tags: true
});
</script>

