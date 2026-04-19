<?php
include 'connection.php';
session_start();

// If user is already logged in, redirect to homepage
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];
$email = "";
$password = "";

// Email pattern to validate email format
$emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

if (isset($_POST['login'])) {

   //email validation
    if (!empty($_POST['email'])) {
        $email = trim($_POST['email']);

        if (!preg_match($emailPattern, $email)) {
            $errors['email'] = "Invalid email format!";
        }
    } else {
        $errors['email'] = "Email is required!";
    }

    //password validation
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        $errors['password'] = "Password is required!";
    }

   //login check
    if (empty($errors)) {

        // Encrypt password using md5
        $hashed_password = md5($password);

        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$hashed_password'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            // Admin redirect
            if ($user['role'] === "admin") {
                $_SESSION['user_id'] = $user['user_name'];
                header('Location: admin/dashboard');
                exit;
            }

            // Normal user redirect
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            header('Location: index.php');
            exit;

        } else {
            $errors['login_failed'] = "Incorrect email or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Daily Blog</title>

    <link rel="stylesheet" href="bootstrap.min.css">
    <script src="bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/7419fa8a42.js" crossorigin="anonymous"></script>

    <style>
        span {
            color: red;
        }
    </style>
</head>

<body style="background-image:url('login.jpg'); background-size: cover;">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div style="width: 100%; max-width: 400px;">

            <h3 class="text-center mb-4 fw-bolder fs-1 text-white">Login</h3>

            <?php if (isset($errors['login_failed'])) { ?>
                <div class="alert alert-danger text-center">
                    <?php echo $errors['login_failed']; ?>
                </div>
            <?php } ?>

            <form method="POST">

                <!-- Email -->
                <div class="form-group mb-3">
                    <div class="d-flex gap-2 bg-white align-items-center rounded px-2">
                        <i class="fa-regular fa-user text-dark"></i>
                        <input type="email" name="email" class="form-control border-0"
                            placeholder="Enter your email">
                    </div>
                    <span><?php echo $errors['email'] ?? ''; ?></span>
                </div>

                <!-- Password -->
                <div class="form-group mb-3">
                    <div class="d-flex gap-2 bg-white align-items-center rounded px-2">
                        <i class="fa-solid fa-lock text-dark"></i>
                        <input type="password" name="password" class="form-control border-0"
                            placeholder="Enter your password">
                    </div>
                    <span><?php echo $errors['password'] ?? ''; ?></span>
                </div>

               <!-- login button -->
                <button type="submit" name="login" class="btn btn-primary w-100">Login</button>

                <div class="d-flex justify-content-between mt-3">
                    <a class="text-white fw-bold" href="./register.php">Not yet registered?</a>
                    <a class="text-white fw-bold" href="./password-reset/index.php">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
