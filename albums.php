<?php
include('scripts/settings.php');
validate();
page_struct();
echo page_sidebar($links);
 $sqla = "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
 mysqli_query($con, $sqla);
$sql = "
SELECT 
    COUNT(DISTINCT images.sno) AS img_count,
    upload.img_title,
    upload.img_department,
    images.file_path,
    images.sno
FROM 
    images
JOIN 
    upload ON upload.sno = images.img_id
JOIN 
    upload_trans ON upload_trans.upload_id = upload.sno
WHERE 
    images.img_status = 0
GROUP BY 
    upload.img_department";
$result = $con->query($sql);

if ($result === FALSE) {
    die("Error: " . $con->error);
}

// Group data by img_department
$albums = [];
while ($row = $result->fetch_assoc()) {
    $img_department = $row['img_department'];
    $file_name = $row['file_path'];
    $count = $row['img_count'];

    // Create a new album array if it doesn't exist
    if (!isset($albums[$img_department])) {
        $albums[$img_department] = [
            'images' => [],
            'count' => $count // Store the image count
        ];
    }

    // Add the image to the album
    $albums[$img_department]['images'][] = $file_name;
}

?>
<div style="background-color: #ccc;">
    <?php page_header(); ?>
    <div class="output">
        <h4 class="center-btn" style="margin-bottom: 30px;">Albums</h4>

        <div class="album-container" style="display: flex; flex-wrap: wrap; gap: 10px;">
            <?php foreach ($albums as $department => $album): ?>
                <div class="album" style="flex: 1 1 calc(20% - 10px); text-align: center;">
                    <a href="album-images.php?department=<?= urlencode($department) ?>">
                        <div class="album-icon" id="icon-<?= htmlspecialchars($department) ?>" style="cursor: pointer;">
                            <!-- Display the first image of the album as the album icon, with a default fallback image -->
                            <img src="upload-images/<?= htmlspecialchars($album['images'][0]) ?>" 
                                 alt="<?= htmlspecialchars($department) ?>" 
                                 style="width: 70%; height: 100px; max-height: 150px; object-fit: cover;border-radius:10px;" 
                                 onerror="this.onerror=null;this.src='images/gallery.png';" />
                        </div>
                    </a>
                    <h6><?= htmlspecialchars($department) ?></h6>
                    <h6 style="color:grey;"><?php echo htmlspecialchars($album['count']); ?></h6> <!-- Display the image count here -->
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php page_footer(); ?>
</div>
