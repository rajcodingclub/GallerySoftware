<?php
include('scripts/settings.php');
validate();
page_struct();
echo page_sidebar($links);
if (isset($_GET['id'])) {
    if ($_GET['id'] == 'Images') {
        $sql = "SELECT DISTINCT upload.sno as sno,images.sno as img_sno, images.img_tag, upload.img_title, upload.img_department, images.file_path, upload_trans.tag 
                FROM upload 
                JOIN images ON upload.sno = images.img_id 
                JOIN upload_trans ON upload.sno = upload_trans.upload_id 
                WHERE upload.img_type='image' AND images.img_status=0;";
        $result = mysqli_query($con, $sql);
    } elseif ($_GET['id'] == 'PDF') {
        $sql = "SELECT DISTINCT upload.sno, upload.img_title, upload.img_department, images.file_path, upload_trans.tag 
                FROM upload 
                JOIN images ON upload.sno = images.img_id 
                JOIN upload_trans ON upload.sno = upload_trans.upload_id 
                WHERE upload.img_type='pdf' AND images.img_status=0;";
        $result = mysqli_query($con, $sql);
    } elseif ($_GET['id'] == 'Excel') {
        $sql = "SELECT DISTINCT upload.sno, upload.img_title, upload.img_department, images.file_path, upload_trans.tag 
                FROM upload 
                JOIN images ON upload.sno = images.img_id 
                JOIN upload_trans ON upload.sno = upload_trans.upload_id 
                WHERE upload.img_type='excel' AND images.img_status=0;";
        $result = mysqli_query($con, $sql);
    } elseif ($_GET['id'] == 'PPT') {
        $sql = "SELECT DISTINCT upload.sno, upload.img_title, upload.img_department, images.file_path, upload_trans.tag 
                FROM upload 
                JOIN images ON upload.sno = images.img_id 
                JOIN upload_trans ON upload.sno = upload_trans.upload_id 
                WHERE upload.img_type='ppt' AND images.img_status=0;";
        $result = mysqli_query($con, $sql);
    } elseif ($_GET['id'] == 'DOC') {
        $sql = "SELECT DISTINCT upload.sno, upload.img_title, upload.img_department, images.file_path, upload_trans.tag 
                FROM upload 
                JOIN images ON upload.sno = images.img_id 
                JOIN upload_trans ON upload.sno = upload_trans.upload_id 
                WHERE upload.img_type='doc' AND images.img_status=0;";
        $result = mysqli_query($con, $sql);
    }
} else {
    $sql = "SELECT DISTINCT upload.sno, upload.img_title, upload.img_department, images.file_path, upload_trans.tag 
            FROM upload 
            JOIN images ON upload.sno = images.img_id 
            JOIN upload_trans ON upload.sno = upload_trans.upload_id 
            WHERE upload.img_type='image' AND images.img_status=0;";
    $result = mysqli_query($con, $sql);
}

?>
<div style="background-color: #ccc;">
    <?php page_header(); ?>
    <div class="output" style="max-width: 100%; padding: 20px 80px 50px 80px;">
        <h4 class="center-btn" style="margin-bottom: 30px;">Total <?php echo $_GET['id']; ?></h4>
        <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; margin-top: 50px;">
            <?php 
            $counter = 0;
            while($row = mysqli_fetch_array($result)) {
                $image = $row['file_path'];

                echo "<div style='width: 210px; margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1), 0 6px 20px rgba(0, 0, 0, 0.1); border-radius: 20px; padding: 10px; background: white;'>";
                $file_extension = pathinfo($image, PATHINFO_EXTENSION); // Get file extension

                if (in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'tiff', 'jfif'])) {
                    // Show image
                    echo "<div class='image-container'>
                            <img src='upload-images/$image' class='clickable-image' style='width: 170px; height: 120px; object-fit: cover; border: 2px solid rgb(9, 9, 103); border-radius: 5px;' onclick='openPopup($counter)'>
                          </div>";
                } else if (in_array($file_extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
                    // Document or other file preview
                    $icon = match ($file_extension) {
                        'pdf' => 'pdf.png',
                        'doc', 'docx' => 'doc.png',
                        'xls', 'xlsx' => 'xls.png',
                        'ppt', 'pptx' => 'ppt.png',
                    };
                    echo "<div class='doc-container text-center'>
                            <a href='upload-images/$image' target='_blank' style='text-decoration:none;'>
                                <img src='images/$icon' width='170px' height='120px'>
                            </a>
                          </div>";
                }

                echo "<div class='text-center' style='font-size:14px;'>";
                echo "<b>Title:</b> " . $row['img_title'] . "<br>";
                echo "<b>Album:</b> " . $row['img_department'] . "<br>";
                echo "<b>Tags:</b> <span contenteditable='true' onblur='saveTag(this, " . $row['img_sno'] . ")' style='color: blue; cursor: text;'>" . htmlspecialchars($row['img_tag']) . "</span><br>";

                echo "<a href='upload-images/$image' download='$image' style='text-decoration:none;'>
                        <i style='font-size:20px;margin-top:5px;color:blue;cursor:pointer' class='fa fa-download' aria-hidden='true'></i>
                      </a>";
                
                echo "</div>"; // End of text-center
                echo "</div>"; // End of individual container

                $counter++;
            }
            ?>
        </div>
    </div>
</div>




</div>


    <!-- Popup Modal Structure -->
    <div id="image-popup" class="popup" style="display: none;">
        <span class="close" onclick="closePopup()">&times;</span>
        <img class="popup-content" id="popup-image">
        <div id="prev" class="popup-prev" onclick="changeImage(-1)">&#10094;</div>
        <div id="next" class="popup-next" onclick="changeImage(1)">&#10095;</div>
    </div>

    <?php page_footer(); ?>
</div>

<script>
    // Save the tag to the server when editing is done
    function saveTag(element, sno) {
        const tag = element.innerText.trim(); // Get the updated tag
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "update_tag.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (!response.success) {
                    alert("Failed to update tag: " + response.error);
                }
            }
        };
        xhr.send("sno=" + sno + "&tag=" + encodeURIComponent(tag));
    }
</script>

<script>
   // Initialize current index for tracking the displayed image
let currentIndex = -1;

// Open the popup and show the clicked image
function openPopup(index) {
    const imageElements = document.querySelectorAll('.clickable-image');
    currentIndex = index;
    const popupImage = document.getElementById("popup-image");
    popupImage.src = imageElements[currentIndex].src; // Get the source from the image element
    document.getElementById("image-popup").style.display = "block";
}

// Close the popup
function closePopup() {
    document.getElementById("image-popup").style.display = "none";
}

// Change to next/previous image based on direction
function changeImage(direction) {
    const imageElements = document.querySelectorAll('.clickable-image');
    currentIndex += direction;

    // Wrap around indices if they go out of bounds
    if (currentIndex < 0) {
        currentIndex = imageElements.length - 1;
    }
    if (currentIndex >= imageElements.length) {
        currentIndex = 0;
    }

    // Update the popup image source
    const popupImage = document.getElementById("popup-image");
    popupImage.src = imageElements[currentIndex].src;
}

// Listen to keyboard arrow events for navigation
document.addEventListener('keydown', function (event) {
    const popup = document.getElementById("image-popup");
    if (popup.style.display === "block") { // Only process if the popup is visible
        if (event.key === "ArrowRight") {
            changeImage(1); // Go to next image
        } else if (event.key === "ArrowLeft") {
            changeImage(-1); // Go to previous image
        }
    }
});

</script>

