<?php
include('dbconnection.php');
session_start();
error_reporting(E_ALL);
error_reporting(0);
$msg="";
function test_input($data){                                                              
    $data= trim($data);
    $data= stripslashes($data);
    $data= htmlspecialchars($data);
    return $data;
}
function page_struct(){
   echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <link rel='stylesheet' href='css/style.css'>
        <link rel='stylesheet' href='css/googlefont.css'>
        <link rel='stylesheet' href='css/bootstrap.css'>
        <link rel='stylesheet' href='css/fontawesome.css'>
        <title>Gallery</title>
    </head>
    <body> 
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js' integrity='sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz' crossorigin='anonymous'></script> 
    <script src='https://kit.fontawesome.com/edbe7d44c5.js' crossorigin='anonymous'></script>
    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
    <script src='https://code.jquery.com/ui/1.12.1/jquery-ui.min.js'></script>
      <script src='https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js'></script>
    <script src='js/script.js'></script>
    <script src='js/bootstrap.js'></script>
    <script src='js/fontawesome.js'></script>
    <script src='js/jquery.js'></script>
    <script src='js/jqueryui.js'></script>
    <script src='js/popper.js'></script>
    <script src='js/sweet_alert.js'></script>
    </body>
    </html>";
 }
 function page_header(){
   echo "<div class='panel'>
   <h3 style='margin-left:100px;FONT-WEIGHT:600;'>Gallery</h3>
   <div class='d-flex mx-5'>
    <button type='submit' onclick='confirm('Do you want to Logout?')' class='btn ' style='background-color: #7928CA;color:white;' name='logout'><i class='fa-solid fa-user'></i>&nbsp;"; echo $_SESSION['userid']; echo"</button>&nbsp;|&nbsp;"; 
     echo "
    <form method='post'>
    <button type='submit' onclick='confirm('Do you want to Logout?')' class='btn ' style='background-color: #FF0080;color:white;' name='logout'><i class='fa-solid fa-power-off'></i>&nbsp; Log Out</button>
    </form>
    </div>
  </div>";
 }

 function validate(){
  if(!isset($_SESSION['userid'])){
    header('location: index.php');
}
if(isset($_POST['logout'])){
    session_destroy();
    header('location: index.php');
  }
 }
function getSidebarLinks($con) {
    $sql = "SELECT url, icon, name FROM navigation";
    $result = mysqli_query($con,$sql);

    $links = [];
    while ($row = $result->fetch_assoc()) {
        $links[] = $row;
    }

    return $links;
}
$links = getSidebarLinks($con);
function page_sidebar($links) {
  // Sidebar container
  $sidebar = '<div id="myDiv" onmouseover="expandDiv()" onmouseout="shrinkDiv()" 
  class="vertical-sidebar" style="background-image: linear-gradient(310deg, #7928CA 0%, #FF0080 100%);
  width:65px; height:100%; position:fixed; z-index: 2; transition: width 0.3s ease;
  box-shadow: 2px 0 5px rgba(0,0,0,0.1); overflow:hidden;">';

  // Sidebar content container
  $sidebar .= '<div id="sidebar-content" class="sidebar-content" style="padding-top:20px; color:white;">';

  // Sidebar header with icon
  $sidebar .= '<h3>';
  
  // Add icon on the left side of the welcome text
  $sidebar .= '<i class="fas fa-praying-hands" style="font-size:1.6rem; margin:0px 20px 0px 15px;"></i>'; // You can change "fa-home" to the desired icon class
  $sidebar .= 'Welcome</h3><hr style="border-color:white; width:80%; margin:auto;">';

  // Iterate through links
  foreach ($links as $link) {
      $url = htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8');
      $icon = htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8');
      $name = htmlspecialchars($link['name'], ENT_QUOTES, 'UTF-8');

      // Sidebar item wrapper
      $sidebar .= '<div style="display: flex; align-items: center; padding: 5px 10px; gap: 10px;">';

      // Separate icon container
      $sidebar .= '<div style="width: 30px; text-align:center;">
                    <i class="' . $icon . '" style="font-size:1.7rem;"></i>
                  </div>';

      // Anchor link next to the icon
      $sidebar .= '<a href="' . $url . '" style="color:white; text-decoration:none; 
      transition: all 0.2s ease; font-size: 1rem; white-space: nowrap; overflow: hidden; 
      text-overflow: ellipsis;">' . $name . '</a>';

      // Close sidebar item wrapper
      $sidebar .= '</div>';
  }

  // Close sidebar content
  $sidebar .= '</div>';

  // Close sidebar container
  $sidebar .= '</div>';

  return $sidebar;
}

function page_footer(){
   echo "<footer class='panel' style='height:50px; margin-top:30px;'>
        <strong style='margin-left:120px;'>Copyright © <?php echo date('Y') ?>.All rights reserved.
        </strong>
        <div class='mx-3 d-none d-sm-inline-block'>
          <b class='d-flex'>Gallery (by: <a href='https://weknowtech.in' target='_blank'>WeKnow Technologies</a> )v1.0</b> 
        </div>
      </footer>";
}

function move_rename_photos(){
	global $con;
	$sql = 'SELECT * FROM `images` order by sno desc ';
	$result = mysqli_query($con, $sql);
	echo '<table border="1">';
	$i=1;
	while($row = mysqli_fetch_assoc($result)){
		$imageFileType = strtolower(pathinfo($row['file_name'],PATHINFO_EXTENSION));
		$file_name = $row['sno'].'.'.$imageFileType;
		$photo = ceil((float)$row['sno']/1000).'/'.$row['sno'].'.'.$imageFileType;
		$target_dir = 'upload-images/'.ceil((float)$row['sno']/1000);
		
		if (!file_exists($target_dir)) {
			mkdir($target_dir, 0777, true);
		}
		copy($row['file_path'], 'upload-images/'.$photo);
		
		$sql = 'update images set file_name="'.$file_name.'", file_path="'.$photo.'" where sno="'.$row['sno'].'"';
		mysqli_query($con, $sql);
		
		// echo '<tr><td>'.$i++.'</td>
		// <td>'.$row['sno'].'</td>
		// <td>'.$row['file_name'].'</td>
		// <td>'.$row['file_path'].'</td>
		// <td>'.$photo.'</td>
		// <td>'.$sql.'</td>
		// </tr>';
	}
}


?>