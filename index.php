

<?php
include('scripts/settings.php');
page_struct();
$msg="";

if(isset($_POST['login'])){
    $user_id = test_input($_POST['user_id']);
    $password = test_input($_POST['password']);
    $query = "select * from register where email_id = '$user_id' and confirm_password = '$password'";
  $result = mysqli_query($con,$query);
	$row = mysqli_fetch_array($result);
	$count = mysqli_num_rows($result);
	if($count==1){
		$_SESSION['userid']=$user_id;
		header("Location: admin.php");
	}else{
		$msg.= '<script>swal ( "Oops" ,  "Userid or Password is Wrong!" ,  "error" )</script>';
   // header("Location: index.php");		
	}
}

?>
<style>
  .video-background {
  position: relative;
  width: 100%;
  height: 100vh;
  overflow: hidden;
}

.video-background video {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  min-width: 100%;
  min-height: 100%;
  z-index: -1; /* Send the video to the background */
}

.content {
  position: relative;
  z-index: 1; /* Ensure content is above the video */
 
}

</style>

<div class="video-background">
  <video autoplay muted loop>
    <source src="images/bgvideo.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>
  <div class="content">
    <!-- Your content here -->

<div class="full_container">
  <div class="form_title">
    <div class="card-header">
      <?php echo $msg; ?>
    </div>
    <h2>Gallery Login &nbsp;<i class="fa fa-user"></i></h2>
  </div>
  <div class="login">
    <form style="margin:80px 20px 0px 20px;" method="POST" action="<?php echo htmlspecialchars($_SERVER[" PHP_SELF"]);?>
      ">
      <h4>Login via User Details</h4><br>
      <div class="mb-3">
        <label style="font-weight:500;" for="exampleInputEmail1" class="form-label">USER ID</label>
        <input type="text" class="form-control" name="user_id" id="exampleInputEmail1" aria-describedby="emailHelp"
          required>
      </div>
      <div class="mb-3">
        <label style="font-weight:500;" for="exampleInputPassword1" class="form-label">PASSWORD</label>
        <input type="password" class="form-control" name="password" id="exampleInputPassword1" required>
      </div>

      <button style="background: #FF0080;" name="login" type="submit" class="btn w-100 mt-4 btn-primary text-center">Login</button>
    </form>
  </div>
</div>
</div>
</div>