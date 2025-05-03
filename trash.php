<?php
include('scripts/settings.php');
validate();
page_struct();
echo page_sidebar($links);
if(isset($_POST['delete'])){
        $sql = "delete from upload where img_status='1'";
        $data = mysqli_query($con,$sql);
        $sql = "delete from images where img_status='1'";
        $data = mysqli_query($con,$sql);
        $msg.= '<script>swal ( "Deleted!" ,  "All Images Have been Permanently Deleted!" ,  "error" )</script>';    
}
if(isset($_GET['restore'])){

    $sql= "select * from images where sno=".$_GET['restore'];
    $data = mysqli_query($con,$sql);
    $row= mysqli_fetch_array($data);

    $sql = "UPDATE upload SET img_status = '0' WHERE upload.sno =".$row['img_id'];
    $data = mysqli_query($con,$sql);
    $sql = "UPDATE images SET img_status = '0' WHERE sno =".$_GET['restore'];
    $data = mysqli_query($con,$sql);
    $msg.= '<script>swal ( "Restored!" ,  "Image Has been Restored successfully!" ,  "success" )</script>';
    
  }
?>
<div style="background-color: #ccc;">
<?php page_header(); 
     echo $msg;
?>
<div class="output">
<div class="w-100 d-flex justify-content-between border-bottom py-2">
	<h3>Images</h3>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
	<button class="btn btn-flat btn-danger" type="submit" id="permanently_delete" name="delete" onclick="return confirm('Are you sure ?\nOnce deleted, you will not be able to recover this imaginary file!')" ><i class="fa fa-trash-alt"></i> Permanently Delete All</button>
    </form>
</div>
<div class="row row-cols-4 row-cols-md-3 row-cols-sm-1 row-cols-lg-4 py-2"></div>
<table> 
<?php
    echo "<table><tr>";
    $sql = 'SELECT upload.sno as id,upload.img_title,images.file_path,images.sno as sno FROM images join upload on upload.sno=images.img_id  where images.img_status=1;';
    $result_data = mysqli_query($con,$sql); 
    $counter = 0;
    
    while($row = mysqli_fetch_array($result_data)){
        $inputString = $row['file_path'];
        $imagesArray = explode(', ', $inputString);
        $imagesArray = array_map('trim', $imagesArray);

        foreach ($imagesArray as $image) {
            // Extract file extension
            $fileExtension = strtolower(pathinfo($image, PATHINFO_EXTENSION));

            if ($counter < 5) {
                echo "<td>";
                // Check if it's an image file
                if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'tiff', 'jfif'])) {
                    echo '<img style="width:200px;height:150px;margin:20px;border:2px solid rgb(9, 9, 103);border-radius:5px;" src="upload-images/' . $image . '">';
                } else if(in_array($fileExtension, ['pdf'])){
                    echo'<img src="images/pdf.png" width="170px" height="120px">';
                    echo '<a class="mt-2 mx-4" href="upload-images/' . $image . '" target="_blank">View ' . strtoupper($fileExtension) . ' File</a>';
                } else if(in_array($fileExtension, ['doc','docs','docx'])){
                    echo'<img src="images/doc.png" width="170px" height="120px">';
                    echo '<a class="mt-2 mx-4" href="upload-images/' . $image . '" target="_blank">View ' . strtoupper($fileExtension) . ' File</a>';
                } else if(in_array($fileExtension, ['xls','xlsx'])){
                    echo'<img src="images/xls.png" width="170px" height="120px">';
                    echo '<a class="mt-2 mx-4" href="upload-images/' . $image . '" target="_blank">View ' . strtoupper($fileExtension) . ' File</a>';
                } else if(in_array($fileExtension, ['ppt','pptx'])){
                    echo'<img src="images/ppt.png" width="170px" height="120px">';
                    echo '<a class="mt-2 mx-4" href="upload-images/' . $image . '" target="_blank">View ' . strtoupper($fileExtension) . ' File</a>';
                } 
                ?>
                <div class="text-center">
                    <div class="dropleft" style="display:flex; justify-content:space-around;">
                        <?php echo "<b>".$row['img_title']."</b>"; ?>
                        <a href="#" id="menus_<?php echo $row['id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="text-dark"><i class="fa fa-ellipsis-v"></i></a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item retrieve_image" data-album-id="<?php echo $row['album_id'] ?>" href="<?php echo $_SERVER['PHP_SELF'].'?restore='.$row['sno'];?>"><i class="fa fa-undo-alt text-dark"></i> Retrieve Image</a>
                        </div>
                    </div>
                </div>
                <?php
                echo "</td>";
            } else {
                echo "</tr><tr style='margin-top:100px !important;'><td>";
                if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic'])) {
                    echo '<img style="width:200px;height:150px;margin:20px;border:2px solid rgb(9, 9, 103);border-radius:5px;" src="upload-images/' . $image . '">';
                } else {
                    echo '<a href="upload-images/' . $image . '" target="_blank">View ' . strtoupper($fileExtension) . ' File</a>';
                }
                ?>
                <div class="text-center">
                    <div class="dropleft" style="display:flex; justify-content:space-around;">
                        <?php echo "<b>".$row['img_title']."</b>"; ?>
                        <a href="#" id="menus_<?php echo $row['id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="text-dark dropdown-toggle"><i class="fa fa-ellipsis-v"></i></a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item retrieve_image" data-id="<?php echo $row['id'] ?>" data-album-id="<?php echo $row['album_id'] ?>" href="<?php echo $_SERVER['PHP_SELF'].'?restore='.$row['sno'];?>"><i class="fa fa-undo-alt text-dark"></i> Retrieve File</a>
                        </div>
                    </div>
                </div>
                <?php
                echo "</td>";
                $counter = 0; // Reset the counter for the next row
            }
            $counter++;
        }
    }
    echo "</tr></table>";
?>

</div>
<?php page_footer(); ?>
</div>
 <script>
        $(document).ready(function(){
            $('[id^="menus_"]').on('click', function(e){
                e.preventDefault();
                $(this).next('.dropdown-menu').toggleClass('show');
            });

            // Close the dropdown if clicked outside
            $(document).on('click', function(e){
                if (!$(e.target).closest('.dropleft').length) {
                    $('.dropdown-menu').removeClass('show');
                }
            });
        });
    </script>