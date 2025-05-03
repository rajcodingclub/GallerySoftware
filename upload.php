<?php
include('scripts/settings.php');
validate();
page_struct();

 echo page_sidebar($links);
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<?php

$uploadDir = 'upload-images/';

// Create the directory if it does not exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function resizeImage($fileTmpName, $maxDim = 1500) {
    list($width, $height, $type) = getimagesize($fileTmpName);

    if ($width > $maxDim || $height > $maxDim) {
        $ratio = $width / $height;
        if ($ratio > 1) {
            $new_width = $maxDim;
            $new_height = $maxDim / $ratio;
        } else {
            $new_width = $maxDim * $ratio;
            $new_height = $maxDim;
        }

        $src = imagecreatefromstring(file_get_contents($fileTmpName));
        $dst = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($src);

        // Save the resized image
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($dst, $fileTmpName);
                break;
            case IMAGETYPE_PNG:
                imagepng($dst, $fileTmpName);
                break;
            case IMAGETYPE_GIF:
                imagegif($dst, $fileTmpName);
                break;
        }

        imagedestroy($dst);
    }
}

if (isset($_POST['submit'])) {
   
    $errors = [];
    $successCount = 0;
    $fileCount = isset($_FILES['images']['name']) ? count($_FILES['images']['name']) : 0;
    $allowedTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/heic',
        'image/tif',
        'video/mp4',                    // MP4
        'video/x-ms-wmv',               // WMV
        'video/x-flv',                  // FLV
        'video/avi',                    // AVI
        'video/mpeg',                   // MPEG
        'video/ogg',                    // OGG
        'video/webm',
        'application/pdf',              // PDF
        'application/msword',           // DOC
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
        'application/vnd.ms-excel',      // XLS
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',  // XLSX
        'application/vnd.ms-powerpoint', // PPT
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    if (empty($_POST['edit_sno'])) {
        // Insert New Data
        $img_title = test_input($_POST['title']);
        $img_type = test_input($_POST['type']);
        $img_department = test_input($_POST['department']);
        $upload_date = test_input($_POST['upload_date']);

        $query = "INSERT INTO upload (img_title, img_type, img_department, upload_date, img_status, `timestamp`)
                  VALUES ('$img_title', '$img_type', '$img_department', '$upload_date', '0', NOW())";
        $result = mysqli_query($con, $query);

        if ($result) {
            $id = mysqli_insert_id($con);

            for ($i = 0; $i < $fileCount; $i++) {
                $fileName = basename($_FILES['images']['name'][$i]);
                $fileTmpName = $_FILES['images']['tmp_name'][$i];
                $fileType = $_FILES['images']['type'][$i];
                $fileError = $_FILES['images']['error'][$i];

                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = "$fileName is not a valid image file.";
                    continue;
                }

                if ($fileError !== UPLOAD_ERR_OK) {
                    $errors[] = "Error uploading $fileName. Error code: $fileError.";
                    continue;
                }

                resizeImage($fileTmpName);

                $uploadFilePath = $uploadDir . $fileName;
                if (move_uploaded_file($fileTmpName, $uploadFilePath)) {
                    $sql = "INSERT INTO images (img_id, file_name, file_path, img_status, img_tag) 
        VALUES ('$id', '$fileName', '$uploadFilePath', 0, '" . $_POST['tag_1'] . "')";
                    if (!$con->query($sql)) {
                        $errors[] = "Error inserting file information for $fileName: " . $con->error;
                    } else {
                        $successCount++;
                    }
                } else {
                    $errors[] = "Failed to upload $fileName.";
                }
            }

            for ($i = 1; $i <= $_POST['add_rows_id1']; $i++) {
                $sql = 'INSERT INTO upload_trans (upload_id, tag) VALUES ("' . $id . '", "' . $_POST['tag_' . $i] . '")';
                mysqli_query($con, $sql);

                if (mysqli_error($con)) {
                    $errors[] = 'Error inserting tag: ' . mysqli_error($con);
                }
            }

            if ($successCount > 0) {
                echo '<script>swal("Submitted!", "Data Submitted Successfully", "success");</script>';
                $_POST = [];
            } else {
                echo '<script>swal("Oops", "No files were successfully uploaded!", "error");</script>';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo '<h3 style="margin-left:100px; color:red; background-color:yellow;">' . $error . '</h3> <br>';
                }
            }
        }
    } else {
        $sql = 'UPDATE upload SET
                img_title="' . $_POST['title'] . '",
                img_type="' . $_POST['type'] . '",
                img_department="' . $_POST['department'] . '",
                upload_date="' . $_POST['upload_date'] . '"
                WHERE sno=' . $_POST['edit_sno'];
        mysqli_query($con, $sql);

        if (mysqli_error($con)) {
            $errors[] = '<h6 class="alert alert-danger">Error: ' . mysqli_error($con) . ' >> ' . $sql . '</h6>';
        } else {
            if (isset($_FILES['images']['name']) && !empty(array_filter($_FILES['images']['name']))) {
                $fileCount = count($_FILES['images']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    $fileName = basename($_FILES['images']['name'][$i]);
                    $fileTmpName = $_FILES['images']['tmp_name'][$i];
                    $fileType = $_FILES['images']['type'][$i];
                    $fileError = $_FILES['images']['error'][$i];

                    if (!in_array($fileType, $allowedTypes)) {
                        $errors[] = "$fileName is not a valid image file.";
                        continue;
                    }

                    if ($fileError !== UPLOAD_ERR_OK) {
                        $errors[] = "Error uploading $fileName. Error code: $fileError.";
                        continue;
                    }

                    resizeImage($fileTmpName);

                    $uploadFilePath = $uploadDir . $fileName;
                    if (move_uploaded_file($fileTmpName, $uploadFilePath)) {
                        $sql = "INSERT INTO images (img_id, file_name, file_path, img_status)
                                VALUES ('" . $_POST['edit_sno'] . "', '$fileName', '$uploadFilePath', 0)";
                        if (!$con->query($sql)) {
                            $errors[] = "Error inserting file information for $fileName: " . $con->error;
                        } else {
                            $successCount++;
                        }
                    } else {
                        $errors[] = "Failed to upload $fileName.";
                    }
                }
            }

            $sql = "DELETE FROM upload_trans WHERE upload_id='" . $_POST['edit_sno'] . "'";
            mysqli_query($con, $sql);

            for ($i = 1; $i <= $_POST['add_rows_id1']; $i++) {
                $sql = 'INSERT INTO upload_trans (upload_id, tag) VALUES
                        ("' . $_POST['edit_sno'] . '", "' . $_POST['tag_' . $i] . '")';
                mysqli_query($con, $sql);

                if (mysqli_error($con)) {
                    $errors[] = 'Error: ' . mysqli_error($con) . ' >> ' . $sql;
                }
            }

            if (!empty($successCount)) {
                echo '<script>
                        swal("Updated!", "Data Updated Successfully", "success").then(() => {
                            window.location.href = window.location.href;
                        });
                      </script>';
                $_POST = [];
            } elseif (empty($successCount)) {
                echo '<script>swal("Updated!", "Data Updated Successfully. No new files uploaded.", "success").then(() => {
                                window.location.href = window.location.href;
                            });</script>';
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo '<h3 style="margin-left:100px; color:red; background-color:yellow;">' . $error . '</h3> <br>';
                }
            }
        }
    }
} else {
    $_POST['add_rows_id1'] = 1;
    $_POST['title'] = "";
    $_POST['type'] = "";
    $_POST['department'] = "";
    $_POST['tag'] = "";
    $_POST['image'] = "";
    $_POST['upload_date'] = "";
    $_POST['edit_sno'] = "";
    $_POST['add_rows_id_exist'] = "";
}


if (isset($_GET['id'])) {
    // Fetch the main upload data
    $sql = 'SELECT * FROM upload WHERE sno=' . $_GET['id'];
    $data = mysqli_fetch_assoc(mysqli_query($con, $sql));
    $_POST['title'] = $data['img_title'];
    $_POST['type'] = $data['img_type'];
    $_POST['department'] = $data['img_department'];
    $_POST['upload_date'] = $data['upload_date'];
    $_POST['edit_sno'] = $data['sno'];

    // Fetch related upload_trans data
    $sql = 'SELECT * FROM upload_trans WHERE upload_id=' . $_GET['id'];
    $res = mysqli_query($con, $sql);

    $_POST['add_rows_id1'] = mysqli_num_rows($res);
    $_POST['add_rows_id_exist'] = mysqli_num_rows($res);
    $i = 1;
    while ($dat_trans = mysqli_fetch_assoc($res)) {
        $_POST['tag_' . $i] = $dat_trans['tag'];
        $i++;
    }
}




if(isset($_GET['del'])){
  $sql = "UPDATE upload set img_status='1' where upload.sno =".$_GET['del'];
  $data = mysqli_query($con,$sql);
  $sql = "UPDATE images set img_status='1' where img_id =".$_GET['del'];
  $data = mysqli_query($con,$sql);

  $msg.= '<script>swal("Deleted!", "Image Has been Deleted!", "error")</script>';
}
?>
<div style="background-color: #ccc;">
	<?php page_header(); ?>
	<div class="formdiv">
		<div class="card-header">
			<?php echo $msg; ?>
		</div>
		<form class="" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
			<div class="row">
				<div class="col-md-3" style="margin-bottom: 50px;">
					<label for="title" class="form-label">Image Title</label>
					<input type="text" class="form-control form-select-sm" name="title" id="title"
						value="<?php echo $_POST['title']; ?>" required>
				</div>
				<div class="col-md-3">
					<label for="department" class="form-label">Album</label>
                    
<select name="department" class="js-example-tags form-control">
    <?php 
       $query = "SELECT img_department FROM upload GROUP BY img_department";
       $result = mysqli_query($con, $query);
       while ($row = mysqli_fetch_assoc($result)) {
           $selected = (isset($_POST['department']) && $_POST['department'] == $row['img_department']) ? 'selected' : '';
           echo '<option value="' . $row['img_department'] . '" ' . $selected . '>' . $row['img_department'] . '</option>';
       }
    ?>
</select>

					
				</div>
				<div class="col-md-3">
    <label for="type" class="form-label">File Type</label>
    <div class="custom-dropdown" id="fileType">
        <div class="custom-select" id="selected-option">
            <span style="font-size:14px;">Select File Type</span>
            <i class="arrow"></i>
        </div>
        <div class="dropdown-options" id="dropdown-options">
            <div class="dropdown-item" data-value="image">
                <img src="images/photo.png" alt="image-icon" class="icon">
                <span>image</span>
            </div>
            <div class="dropdown-item" data-value="video">
                <img src="images/video.png" alt="image-icon" class="icon">
                <span>video</span>
            </div>
            <div class="dropdown-item" data-value="pdf">
                <img src="images/pdf.png" alt="pdf-icon" class="icon">
                <span>pdf</span>
            </div>
            <div class="dropdown-item" data-value="doc">
                <img src="images/doc.png" alt="doc-icon" class="icon">
                <span>doc</span>
            </div>
            <div class="dropdown-item" data-value="ppt">
                <img src="images/ppt.png" alt="ppt-icon" class="icon">
                <span>ppt</span>
            </div>
            <div class="dropdown-item" data-value="excel">
                <img src="images/xls.png" alt="excel-icon" class="icon">
                <span>excel</span>
            </div>
        </div>
        <input type="hidden" name="type" id="file_type"  required>
    </div>
</div>


				<div class="col-md-3">
    			<label for="upload_date" class="form-label">Event Date</label>
<input type="date" class="form-control form-select-sm" name="upload_date" id="upload_date"
    value="<?php echo isset($_POST['upload_date']) ? $_POST['upload_date'] : date('Y-m-d'); ?>"
    max="<?php echo date('Y-m-d'); ?>" required>

    			</div>
                </div>
                <?php
    for ($i = 1; $i <= $_POST['add_rows_id1']; $i++) {
?>
<div class="row" id="row_<?php echo $i; ?>" style="margin:5px 0px !important;">
    <!-- Tag Section -->
    <div class="col-md-4" style="margin-top:20px;">
        <div class="form-group">
            <?php echo $i; ?>. <label for="tag" class="form-label">Tag</label>
            <select class="js-example-tags form-control form-select-sm" 
                    name="tag_<?php echo $i; ?>" 
                    id="tag_<?php echo $i; ?>" 
                    required 
                    onchange="toggleOtherTagInput(this, <?php echo $i; ?>)">
                <option value="">Select Tag</option>
                <?php
                    // Fetch tags from database
                    $query = "SELECT upload_id, tag FROM upload_trans GROUP BY tag;";
                    $result = mysqli_query($con, $query);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Compare current row value with fetched tag value
                            $selected = (isset($_POST['tag_' . $i]) && $_POST['tag_' . $i] == $row['tag']) ? 'selected' : '';
                            echo "<option value='" . $row['tag'] . "' $selected>" . $row['tag'] . "</option>";
                        }
                    }
                ?>
            </select>

            <!-- Manual Tag Input -->
            <input type="text" class="form-control form-select-sm" 
                   name="other_tag_<?php echo $i; ?>" 
                   id="other_tag_<?php echo $i; ?>" 
                   style="display:none; margin-top:10px;" 
                   placeholder="Enter your own tag" 
                   onblur="addNewOption(this, <?php echo $i; ?>)">
        </div>
    </div>

    <!-- Add Button -->
    <div class="col-md-1 d-flex justify-content-center align-items-center">
        <button style="background-color: rgb(9, 9, 103); color:white;" 
                type="button" 
                id="add_button1" 
                class="btn btn-info pull-right" 
                onClick="add_rows1()">Add</button>
    </div>

    <!-- Remove Button -->
    <div class="col-md-1 d-flex justify-content-center align-items-center">
        <button style="background-color: rgb(202, 19, 19); color:white;" 
                type="button" 
                id="remove_button_<?php echo $i; ?>" 
                class="btn btn-danger pull-right" 
                onClick="remove_row(<?php echo $i; ?>)">Remove</button>
    </div>

    <!-- File Upload Section (outside the loop) -->
    <?php if ($i == 1) { ?>
        <div class="col-md-6" style="margin-top: 20px;">
    <label for="file" class="form-label">Upload Images</label>
    <input type="file" class="form-control form-select-sm" name="images[]" id="input_file" multiple>
    <a href="#" style="z-index: 999px;" id="viewImageLink" class="btn btn-link mt-2">Image Preview</a>
    <div id="imagePreview" class="mt-3"></div>

    <!-- Existing files section -->
     <?php if (!empty($_POST['edit_sno'])) { ?>
    <h6>Previously Uploaded Images</h6>
    <div id="existingImages" style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 10px;">
    <?php
    
        $editId = $_POST['edit_sno'];
        $result = mysqli_query($con, "SELECT * FROM images WHERE img_id = '$editId'");

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<div style="text-align: center; ">';  // Add text-align center to center the content
            echo '<img src="upload-images/' . htmlspecialchars($row['file_path']) . '" width="50" height="50" alt="' . $row['file_name'] . '" />';  // Adjust size of images
            echo '<br>';
            echo '<button type="button" class="btn btn-danger btn-sm delete-btn" style="margin-top: 5px;width: 20px;height:20px; padding:0;"  data-file-id="' . $row['sno'] . '">&#10006</button>';
            echo '</div>';
        
            }
        }
        ?>
    </div>

    <?php } ?>
</div>
<?php } ?>

<input type="hidden" name="add_rows_id1" id="add_rows_id1" value="<?php echo $_POST['add_rows_id1']; ?>">
<input type="hidden" name="add_rows_id_exist" id="add_rows_id_exist" value="<?php echo $_POST['add_rows_id_exist']; ?>">
<div class="row" id="test1"></div>


			<div class="col-12">
			<button style="background: #FF0080;color:white;margin-top:27px" class="btn btn-primary" type="submit" name="submit" >Submit</button>
				<input type="hidden" name="edit_sno" id="edit_sno" value="<?php echo $_POST['edit_sno']; ?>">
			</div>

		</form>
	</div>
    <?php if (empty($_POST['edit_sno'])) { ?>
	<div class="output">
    <h4 class="center-btn">Gallery</h4>
    <div class="container_display">
        <?php
        // Pagination logic
        $limit = 10; // Number of entries to show per page
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        } else {
            $page = 1;
        }
        $start_from = ($page - 1) * $limit;

        // Get the total number of records for pagination
        $sql_count = "SELECT COUNT(DISTINCT upload.sno) as total FROM upload
                      LEFT JOIN upload_trans ON upload.sno = upload_trans.upload_id
                      WHERE upload.img_status = 0";
        $result_count = mysqli_query($con, $sql_count);
        $row_count = mysqli_fetch_assoc($result_count);
        $total_records = $row_count['total'];

        // Calculate total pages
        $total_pages = ceil($total_records / $limit);

        // SQL query to fetch data for the current page
        $sql = "SELECT upload.*,
        GROUP_CONCAT(DISTINCT upload_trans.tag SEPARATOR ', ') AS tags
 FROM upload
 LEFT JOIN upload_trans ON upload.sno = upload_trans.upload_id
 WHERE upload.img_status = 0
 GROUP BY upload.sno
 ORDER BY upload.timestamp DESC
 LIMIT $start_from, $limit";

        $result = mysqli_query($con, $sql);
        $i = $start_from + 1; // Adjust serial number to reflect the current page
        ?>

   <table>
            <tr class="text-center" style="background-color: #7928CA;color:white;">
                <th>Sr.No</th>
                <th>Image Title</th>
                <th>Image Tag</th>
                <th>Photo Type</th>
                <th>Albums</th>
                <th>Images</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>

            <?php
            while ($row = mysqli_fetch_array($result)) {

            ?>

            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $row['img_title']; ?></td>
                <td><?php echo $row['tags']; ?></td>
                <td><?php echo $row['img_type']; ?></td>
                <td><?php echo $row['img_department']; ?></td>
                <?php 
                $query="SELECT COUNT(*) AS total_count FROM images where img_id='".$row['sno']."' and img_status=0";
                //echo $query;
                $result_count = mysqli_query($con,$query);
                $count_row=mysqli_fetch_assoc($result_count);
                 ?>  
                <td><?php echo '<a href="view_files.php?view=' . $row['sno']. '" class="btn btn-link">View File&nbsp; ('.$count_row['total_count'].')</a>';?></td>
                <td class="text-center"><a href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $row['sno']; ?>" alt="Edit"
                        data-toggle="tooltip" title="Edit"><span class="far fa-edit" aria-hidden="true"></span></a></td>
                <td class="text-center"><a href="<?php echo $_SERVER['PHP_SELF'] . '?del=' . $row['sno']; ?>"
                        onclick="return confirm('Are you sure?');" style="color:#f00" alt="Delete"><span
                            class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip"
                            title="Delete"></span></a></td>
            </tr>

            <?php } ?>
        </table>

        <!-- Pagination Links -->
        <div class="pagination">
        <p style="margin:10px 50px;">Total Records: <?php echo $total_records; ?> | Current Page: <?php echo $page; ?> of <?php echo $total_pages; ?></p>
            <!-- Previous Button -->
            <?php if ($page > 1) { ?>
                <a href="?page=<?php echo ($page - 1); ?>" class="btn btn-link">Previous</a>
            <?php } else { ?>
                <span class="btn btn-link disabled">Previous</span>
            <?php } ?>

            <!-- Page Numbers -->
            <?php
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                    echo "<span class='btn btn-link active'>$i</span> "; // Highlight current page
                } else {
                    echo "<a href='?page=$i' class='btn btn-link'>$i</a> ";
                }
            }
            ?>

            <!-- Next Button -->
            <?php if ($page < $total_pages) { ?>
                <a href="?page=<?php echo ($page + 1); ?>" class="btn btn-link">Next</a>
            <?php } else { ?>
                <span class="btn btn-link disabled">Next</span>
            <?php } ?>
        </div>
    </div>
</div>
</div>

	<?php
    }
page_footer();


?>


<script>
document.getElementById('viewImageLink').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent the link from navigating away

    const fileInput = document.getElementById('input_file');
    const imagePreview = document.getElementById('imagePreview');

    // Toggle the visibility of the image preview
    if (imagePreview.style.display === 'none' || imagePreview.style.display === '') {
        // Show the preview
        imagePreview.style.display = 'block';
        imagePreview.innerHTML = ''; // Clear previous content if any

        // Check if any files are selected
        if (fileInput.files.length > 0) {
            // Loop through each selected file
            for (let i = 0; i < fileInput.files.length; i++) {
                const file = fileInput.files[i];

                // Ensure that it's an image file
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();

                    // Once the file is read, display it
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100px';  // Set a fixed width for the preview
                        img.style.marginRight = '10px'; // Add some spacing between images
                        img.classList.add('img-thumbnail'); // Optional: Bootstrap's thumbnail class for better styling

                        // Append the image to the preview container
                        imagePreview.appendChild(img);
                    }

                    // Read the image file as a data URL
                    reader.readAsDataURL(file);
                }
            }
        } else {
            alert('Please select images to preview.');
        }

        // Change the link text to "Close Image"
        this.textContent = 'Close Image';
    } else {
        // Hide the preview
        imagePreview.style.display = 'none';
        imagePreview.innerHTML = ''; // Clear the content

        // Change the link text back to "View Image"
        this.textContent = 'View Image';
    }
});
</script>

<script>
document.getElementById('selected-option').addEventListener('click', function() {
    const dropdown = document.getElementById('dropdown-options');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
});

document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function() {
        const value_dropdown = this.getAttribute('data-value');
        const text = this.querySelector('span').innerText;
        document.getElementById('selected-option').querySelector('span').innerText = text;
        document.getElementById('file_type').value = value_dropdown;
        document.getElementById('dropdown-options').style.display = 'none';
    });
});
</script>

<script>
// Show the input field when 'Other' is selected
function toggleOtherTagInput(selectElement, index) {
    var otherInput = document.getElementById('other_tag_' + index);

    if (selectElement.value === 'other') {
        otherInput.style.display = 'block';
        otherInput.focus(); // Focus on input when visible
    } else {
        otherInput.style.display = 'none';
    }
}

// Add the input value as a new option in the select dropdown
function addNewOption(inputElement, index) {
    var inputValue = inputElement.value.trim();
    var selectElement = document.getElementById('tag_' + index);

    if (inputValue !== "") {
        // Create a new option
        var newOption = document.createElement("option");
        newOption.value = inputValue; // Set the input value as the option's value
        newOption.text = inputValue;  // Set the input value as the option's display text
        newOption.selected = true;    // Mark it as selected

        // Insert the new option before the "Other" option
        var otherOption = selectElement.querySelector("option[value='other']");
        selectElement.insertBefore(newOption, otherOption);

        // Reset and hide the input field
        inputElement.value = '';
        inputElement.style.display = 'none';
    }
}
</script>
<script>
    const allowedExtensions = {
        'image': ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp', 'svg', 'ico', 'heic'],
        'pdf': ['pdf'],
        'doc': ['doc', 'docx'],
        'xlsx': ['xls', 'xlsx']
    };

    // Get references to form elements
    const fileInput = document.getElementById('input_file');
    const fileTypeSelect = document.getElementById('file_type');
    const errorMessage = document.getElementById('errorMessage');
    // console.log('fileTypeSelect)';
    // Listen for changes in the file type dropdown
    fileTypeSelect.addEventListener('change', function() {
        const fileType = fileTypeSelect.value;

        // Set the accept attribute based on the selected file type
        if (fileType === 'image') {
            fileInput.setAttribute('accept', 'image/*'); // Accept all image types
        } else if (fileType === 'pdf') {
            fileInput.setAttribute('accept', 'application/pdf');
        } else if (fileType === 'doc') {
            fileInput.setAttribute('accept', '.doc, .docx');
        } else if (fileType === 'excel') {
            fileInput.setAttribute('accept', '.xls, .xlsx');
        } else {
            fileInput.removeAttribute('accept'); // Remove accept attribute if no valid type is selected
        }

        // Reset file input when file type changes
        fileInput.value = ''; // Clear the file input to prevent invalid file selections
        errorMessage.textContent = ''; // Clear any previous error message
    });

    // Listen for changes in the file input field
    fileInput.addEventListener('change', function() {
        const fileType = fileTypeSelect.value; // Get selected file type from dropdown
        const file = fileInput.files[0]; // Get the selected file

        errorMessage.textContent = ''; // Clear any previous error message

        if (!file) {
            errorMessage.textContent = 'Please choose a file.';
            return;
        }

        // Get the file extension
        const fileName = file.name;
        const fileExtension = fileName.split('.').pop().toLowerCase();

        // Check if the file extension matches the selected file type
        if (allowedExtensions[fileType] && allowedExtensions[fileType].includes(fileExtension)) {
            errorMessage.textContent = 'File is valid!';
        } else {
            errorMessage.textContent = 'Invalid file type. Please select the correct file based on your selection.';
            fileInput.value = ''; // Reset the file input to force the user to select a valid file
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(".js-example-tags").select2({
  tags: true
});
</script>

<script>
// Set the file type based on the fetched value
document.addEventListener("DOMContentLoaded", function () {
    const fileTypeDropdown = document.querySelector("#dropdown-options");
    const selectedOptionDiv = document.querySelector("#selected-option");
    const hiddenInput = document.querySelector("#file_type");

    // Assuming the value is already fetched in PHP ($_POST['type'])
    const fetchedFileType = "<?php echo isset($_POST['type']) ? $_POST['type'] : ''; ?>";

    if (fetchedFileType) {
        // Highlight the matching file type
        fileTypeDropdown.querySelectorAll(".dropdown-item").forEach((item) => {
            if (item.getAttribute("data-value") === fetchedFileType) {
                selectedOptionDiv.innerHTML = item.innerHTML; // Set display value
                hiddenInput.value = fetchedFileType; // Set hidden input value
            }
        });
    }

    // Event handling for selection change
    fileTypeDropdown.querySelectorAll(".dropdown-item").forEach(item => {
        item.addEventListener("click", function () {
            selectedOptionDiv.innerHTML = this.innerHTML; // Update displayed option
            hiddenInput.value = this.getAttribute("data-value"); // Update hidden input
        });
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const existingImages = document.getElementById('existingImages');
    
    // Event delegation for Delete Buttons
    existingImages.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-btn')) {
            const fileId = e.target.getAttribute('data-file-id');
            if (confirm("Are you sure you want to delete this file?")) {
                // AJAX request to delete the file
                fetch('delete_file.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `file_id=${fileId}`
                })
                .then(response => response.text())
                .then(data => {
                    if (data === 'success') {
                        e.target.parentElement.remove(); // Remove the file preview
                        alert("File deleted successfully!");
                    } else {
                        e.target.parentElement.remove(); // Remove the file preview
                        alert("File deleted successfully!");
                    }
                });
            }
        }
    });
});
</script>
<?php move_rename_photos();?>