<!-- Edit profile info and picture -->
<?php
include 'connection.php';
include 'navbar.php';

$user_id = $_SESSION['user_id'];

// this because initially the user_profiles is null and inner join is not applicable
$selectUser = "SELECT * FROM USERS WHERE user_id = $user_id";
$selectUserResult = mysqli_query($conn, $selectUser);

$user = mysqli_fetch_assoc($selectUserResult);

$selectUserInfo = "SELECT username, bio, profile_picture, gender, date_of_birth, location FROM users u INNER JOIN user_profiles p ON u.user_id = p.user_id WHERE u.user_id = $user_id";
$selectUserInfoResult = mysqli_query($conn, $selectUserInfo);

$userInfo = mysqli_fetch_assoc($selectUserInfoResult);

$isValid = true;
$errors = [];

if (isset($_POST['update'])) {
    if (isset($_POST['username']) && !empty($_POST['username']) && trim($_POST['username']) != '') {
        $username = $_POST['username'];
    } else {
        $errors['username'] = "Username is required! <br>";
        $isValid = false;
    }

    if (isset($_POST['bio']) && !empty($_POST['bio']) && trim($_POST['bio']) != '') {
        $bio = $_POST['bio'];
    } else {
        $errors['bio'] = "Bio is required! <br>";
        $isValid = false;
    }

    if (isset($_POST['gender']) && !empty($_POST['gender']) && trim($_POST['gender']) != '') {
        $gender = $_POST['gender'];
    } else {
        $errors['gender'] = "Gender is required! <br>";
        $isValid = false;
    }

    if (isset($_POST['dob']) && !empty($_POST['dob']) && trim($_POST['dob']) != '') {
        $dob = $_POST['dob'];
    } else {
        $errors['dob'] = "DOB is required! <br>";
        $isValid = false;
    }

    if (isset($_POST['address']) && !empty($_POST['address']) && trim($_POST['address']) != '') {
        $address = $_POST['address'];
    } else {
        $errors['address'] = "Address is required! <br>";
        $isValid = false;
    }



// if all validation passed, update the username and profile details
    if ($isValid) {
        $updateUsername = "UPDATE users SET username = '$username' WHERE user_id = $user_id";
        $updateUsernameResult = mysqli_query($conn, $updateUsername);

        $selectUserDetail = "SELECT * FROM user_profiles WHERE user_id = $user_id";
        $selectUserDetailResult = mysqli_query($conn, $selectUserDetail);

        $userData = mysqli_num_rows($selectUserDetailResult);

        if ($userData === 1) {
            $updateProfileDetails = "UPDATE user_profiles SET bio = '$bio', gender = '$gender' , location = '$address' WHERE user_id = $user_id";

            $updateProfileDetailResult = mysqli_query($conn, $updateProfileDetails);
        } else {
            $insertProfileDetails = "INSERT INTO user_profiles (user_id, bio, gender, date_of_birth, location) VALUES ( $user_id, '$bio', '$gender', '$dob', '$address')";

            $insertProfileDetailsResult = mysqli_query($conn, $insertProfileDetails);
        }
    }
}

// profile picture upload and compression
if (isset($_POST['addprofile'])) {

    $image = $_FILES['profile'];
    $title = $image['name'];
    $image_size = $image['size'];

    if (!empty($title)) {
        $ext = strtolower(pathinfo($title, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
            if ($image_size > 0 && $image_size < 2097152) { // < 2MB
                // Rename image to a standard compressed name
                $image_final_name = "profile-" . time() . ".jpg";
                $uploadDir = "uploads/";

                // GD Compression function
                function compressImage($source, $destination, $quality = 75) {
                    $info = getimagesize($source);
                    if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($source);
                    elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($source);
                    elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($source);
                    else return false;

                    imagejpeg($image, $destination, $quality);
                    imagedestroy($image);
                    return $destination;
                }

                $compressedPath = compressImage($image['tmp_name'], $uploadDir . $image_final_name, 75);

                if ($compressedPath) {
                   
                    $update = "UPDATE user_profiles SET profile_picture='$image_final_name' WHERE user_id=$user_id";
                    mysqli_query($conn, $update);

                    // Success message + refresh page
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                            Profile image uploaded successfully!
                          </div>
                          <meta http-equiv='refresh' content='2'>";
                } else {
                    echo "<div class='alert alert-danger'>Image compression failed!</div>";
                }

            } else {
                echo "<div class='alert alert-danger'>File too large. Max 2MB.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Invalid file type! Only jpg, png, gif allowed.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No file selected!</div>";
    }
}


?>

<!-- Edit profile Form -->
<div class="row container-lg d-flex justify-content-center">
    <div class="col-lg-5 col-md-4">

        <img src="uploads/<?php echo !empty($userInfo['profile_picture']) ? $userInfo['profile_picture'] : 'default.png'; ?>"
            alt="profile-image" class='rounded w-50'>

        <form action="#" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label fw-bold my-2" for="profile">Change Profile Picture</label>
                <input type="file" class="form-control" id="profile" name="profile">
            </div>
            <input type="submit" name="addprofile" value="Add"
                class="bg-primary border-0 text-white px-2 py-1 my-1 rounded">
        </form>
    </div>

    <div class="col-lg-7">
        <form action="#" method="post">
            <div class="form-group">
                <label for="username" class="form-label h4">Username</label>
                <input type="text" name="username" id="username" class="form-control"
                    value="<?php echo $user['username'] ?>">
                <span class="error"><?php echo isset($errors['username']) ? $errors['username'] : ''; ?></span>
            </div>
            <div class="form-group">
                <label for="" class="form-label h4">Bio</label>
                <textarea id="" name="bio" class="form-control" rows="10"><?php if (!empty($userInfo))
                    echo $userInfo['bio']; ?></textarea>
                <span class="error"><?php echo isset($errors['bio']) ? $errors['bio'] : ''; ?></span>
            </div>
            <div class="form-group">
                <label for="gender" class="h4 w-100">Gender</label>
                <input type="radio" id="male" value="Male" name="gender" class="" <?php if (!empty($userInfo)) if ($userInfo['gender'] == "m")
                    echo "checked" ?>>
                        <label for="male">Male</label>
                        <input type="radio" id="female" value="Female" name="gender" class="" <?php if (!empty($userInfo)) if ($userInfo['gender'] == "f")
                    echo "checked" ?>>
                        <label for="female">Female</label>
                        <input type="radio" id="other" value="Other" name="gender" class="" <?php if (!empty($userInfo)) if ($userInfo['gender'] == "o")
                    echo "checked" ?>>
                        <label for="other">Other</label><br>
                        <span class="error"><?php echo isset($errors['gender']) ? $errors['gender'] : ''; ?></span>
            </div>
            <div class="form-group">
                <label for="dob" class="h4 w-100">DOB</label>
                <input type="date" name="dob" id="dob" value="<?php echo $userInfo['date_of_birth']; ?>"><br>
                <span class="error"><?php echo isset($errors['dob']) ? $errors['dob'] : ''; ?></span>
            </div>
            <div class="form-group">
                <label for="" class="form-label h4">Address</label>
                <input type="text" class="form-control" name="address" value="<?php if (!empty($userInfo))
                    echo $userInfo['location']; ?>">
                <span class="error"><?php echo isset($errors['address']) ? $errors['address'] : ''; ?></span>
            </div>
            <div class="my-2">
                <input type="submit" name="update" value="Save"
                    class="bg-primary border-0 text-white px-2 py-1 rounded">
            </div>
        </form>
    </div>
</div>