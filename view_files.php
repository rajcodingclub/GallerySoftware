<?php
include('scripts/settings.php');
validate();
page_struct();
echo page_sidebar($links);

if(isset($_GET['del1'])){
   echo $sql = "UPDATE images SET img_status = '1' WHERE sno = " . $_GET['del1'];
    $data = mysqli_query($con, $sql);
    if($data){
    $msg = '<script>swal("Deleted!", "Image Has been Deleted!", "error")</script>';
    }
    // Redirect to the same page without the 'del1' parameter to refresh
    // header("Location: " . $_SERVER["PHP_SELF"] . "?view=" . $_GET['view']);
    // exit();
}

if(isset($_GET['view'])){
    $query = "SELECT upload_id, GROUP_CONCAT(DISTINCT tag SEPARATOR ',') AS tags 
          FROM upload_trans 
          WHERE upload_id = '" . intval($_GET['view']) . "'";
$result_tag = mysqli_query($con, $query);
$row_tag = mysqli_fetch_assoc($result_tag);
    
    $photos = '<div style="display: grid; grid-template-columns: auto auto auto auto auto; row-gap: 2;">';
    $sql = "SELECT DISTINCT images.sno, images.*, upload.*, images.file_path 
    FROM images 
    JOIN upload ON images.img_id = upload.sno  
    WHERE images.img_id = '" . intval($_GET['view']) . "' AND images.img_status = 0";
$result_trans = mysqli_query($con, $sql);

    
    while ($row_trans = mysqli_fetch_assoc($result_trans)) {
        $file_extension = pathinfo($row_trans["file_path"], PATHINFO_EXTENSION);

        if (in_array($file_extension, ['jpg','JPG', 'jpeg', 'png', 'gif', 'webp', 'heic', 'tif'])) {
            $photos .= '<div style="background-color:transparent; margin:10px 20px;">
            <img src="upload-images/' . htmlspecialchars($row_trans["file_path"]) . '" alt="image" width="200px" height="150px">
            <div style="font-size:14px;">
                <b>Title:</b> ' . htmlspecialchars($row_trans['img_title']) . '<br>
                <b>Album:</b> ' . htmlspecialchars($row_trans['img_department']) . '<br>
                <b>Tags:</b> ' . htmlspecialchars($row_tag['tags']) . '<br>
                <a href="upload-images/' . htmlspecialchars($row_trans["file_path"]) . '" download="' . htmlspecialchars($row_trans["file_path"]) . '" style="text-decoration:none;">
                    <i style="font-size:20px; margin-top:5px; color:blue; cursor:pointer;" class="fa fa-download" aria-hidden="true"></i>
                </a>
               <span style="margin-left: 20px; margin-top: 5px;">
                    <a href="view_files.php?view=' . urlencode($_GET['view']) . '&&del1=' . urlencode($row_trans['sno']). '"
                       onclick="return confirm(\'Are you sure you want to delete this item?\');" 
                       style="color: #f00;" 
                       alt="Delete">
                        <i class="far fa-trash-alt" aria-hidden="true" data-toggle="tooltip" title="Delete"></i>
                    </a>
                </span>
            </div>
        </div>';
        
        } else if ($file_extension === 'pdf') {
            $photos .= '<div style="background-color:transparent; margin:10px 20px;">
                <img src="images/pdf.png" width="120px" height="120px">
                <div style="font-size:14px;">
                    <b>Title:</b> ' . $row_trans['img_title'] . '<br>
                    <b>Album:</b> ' . $row_trans['img_department'] . '<br>
                    <b>Tags:</b> ' . $row_tag['tags'] . '<br>
                    <a href="' . $row_trans["file_path"] . '" target="_blank" class="btn btn-link">View File</a>
                    <a href="upload-images/' . $row_trans["file_path"] . '" download="' . $row_trans["file_path"] . '" style="text-decoration:none;">
                        <i style="font-size:20px;margin-top:5px;color:blue;cursor:pointer" class="fa fa-download" aria-hidden="true"></i>
                    </a>
                </div>
            </div>';
        } else if (in_array($file_extension, ['doc', 'docx'])) {
            $photos .= '<div style="background-color:transparent; margin:10px 20px;">
                <img src="images/doc.png" width="120px" height="120px">
                <div style="font-size:14px;">
                    <b>Title:</b> ' . $row_trans['img_title'] . '<br>
                    <b>Album:</b> ' . $row_trans['img_department'] . '<br>
                    <b>Tags:</b> ' . $row_tag['tags'] . '<br>
                    <a href="' . $row_trans["file_path"] . '" target="_blank" class="btn btn-link">View File</a>
                    <a href="upload-images/' . $row_trans["file_path"] . '" download="' . $row_trans["file_path"] . '" style="text-decoration:none;">
                        <i style="font-size:20px;margin-top:5px;color:blue;cursor:pointer" class="fa fa-download" aria-hidden="true"></i>
                    </a>
                </div>
            </div>';
        } else if (in_array($file_extension, ['xls', 'xlsx'])) {
            $photos .= '<div style="background-color:transparent; margin:10px 20px;">
                <img src="images/xls.png" width="120px" height="120px">
                <div style="font-size:14px;">
                    <b>Title:</b> ' . $row_trans['img_title'] . '<br>
                    <b>Album:</b> ' . $row_trans['img_department'] . '<br>
                    <b>Tags:</b> ' . $row_tag['tags'] . '<br>
                    <a href="' . $row_trans["file_path"] . '" target="_blank" class="btn btn-link">View File</a>
                    <a href="upload-images/' . $row_trans["file_path"] . '" download="' . $row_trans["file_path"] . '" style="text-decoration:none;">
                        <i style="font-size:20px;margin-top:5px;color:blue;cursor:pointer" class="fa fa-download" aria-hidden="true"></i>
                    </a>
                </div>
            </div>';
        } else if (in_array($file_extension, ['ppt', 'pptx'])) {
            $photos .= '<div style="background-color:transparent; margin:10px 20px;">
                <img src="images/ppt.png" width="120px" height="120px">
                <div style="font-size:14px;">
                    <b>Title:</b> ' . $row_trans['img_title'] . '<br>
                    <b>Album:</b> ' . $row_trans['img_department'] . '<br>
                    <b>Tags:</b> ' . $row_tag['tags'] . '<br>
                    <a href="' . $row_trans["file_path"] . '" target="_blank" class="btn btn-link">View File</a>
                    <a href="upload-images/' . $row_trans["file_path"] . '" download="' . $row_trans["file_path"] . '" style="text-decoration:none;">
                        <i style="font-size:20px;margin-top:5px;color:blue;cursor:pointer" class="fa fa-download" aria-hidden="true"></i>
                    </a>
                </div>
            </div>';
        } else if (in_array($file_extension, ['mp4', 'avi', 'mpeg', 'ogg', 'webm'])) {
            $photos .= '<div style="background-color:transparent; margin:10px 20px;">
                <img src="images/video.png" width="120px" height="120px">
                <div style="font-size:14px;">
                    <b>Title:</b> ' . $row_trans['img_title'] . '<br>
                    <b>Album:</b> ' . $row_trans['img_department'] . '<br>
                    <b>Tags:</b> ' . $row_tag['tags'] . '<br>
                    <a href="' . $row_trans["file_path"] . '" target="_blank" class="btn btn-link">View Video</a>
                    <a href="upload-images/' . $row_trans["file_path"] . '" download="' . $row_trans["file_path"] . '" style="text-decoration:none;">
                        <i style="font-size:20px;margin-top:5px;color:blue;cursor:pointer" class="fa fa-download" aria-hidden="true"></i>
                    </a>
                </div>
            </div>';
        }
    }
    $photos .= '</div>';
}
?>

<div style="background-color: #ccc;">
    <?php 
        page_header(); 
        echo $msg;
    ?>
    <div class="output">
        <?php echo $photos; ?>
    </div>
    <?php page_footer(); ?>
</div>
