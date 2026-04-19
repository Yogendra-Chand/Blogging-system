<?php

include 'connection.php';

$errors = [];
$username = $email = $password = $cPassword = "";
$emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

if (isset($_POST['submit'])) {

   
    if (!empty($_POST['username'])) {

        $username = trim($_POST['username']);

        // Check if username already exists
        $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
        if (mysqli_num_rows($checkUser) > 0) {
            $errors['username'] = "Username already exists!";
        }

    } else {
        $errors['username'] = "Username is required!";
    }

  //email check
    if (!empty($_POST['email'])) {

        $email = trim($_POST['email']);

        // Check format
        if (!preg_match($emailPattern, $email)) {
            $errors['email'] = "Enter a valid email!";
        }

        // Check if email exists
        $checkEmail = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors['email'] = "Email already exists!";
        }

    } else {
        $errors['email'] = "Email is required!";
    }

   //password check
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        $errors['password'] = "Password is required!";
    }

  //confirm password 
    if (!empty($_POST['c-password'])) {

        $cPassword = $_POST['c-password'];

        if ($password !== $cPassword) {
            $errors['cPassword'] = "Both passwords must match!";
        }

    } else {
        $errors['cPassword'] = "Confirm your password!";
    }
//no error save user
    if (empty($errors)) {

        $hashedPassword = md5($password); 

        $sql = "INSERT INTO users (username, email, password) 
                VALUES ('$username', '$email', '$hashedPassword')";

        if (mysqli_query($conn, $sql)) {
            header("Location: login.php");
            exit;
        } else {
            $errors['database'] = "Registration failed! Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Daily Blog - Register</title>

    <link rel="stylesheet" href="bootstrap.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #74ebd5, #9face6);
            min-height: 100vh;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .form-control {
            border-radius: 10px;
            padding: 10px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .error-text {
            font-size: 0.85rem;
            color: red;
        }

        .btn {
            border-radius: 10px;
            padding: 10px 20px;
        }

        .login-link {
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>

<body>

<div class="container d-flex justify-content-center align-items-center">
    <div class="col-md-5 col-lg-4">

        <div class="card p-4 my-5">
            <h2 class="text-center text-primary mb-4">Create Account</h2>

            <?php if(isset($errors['database'])) { ?>
                <div class="alert alert-danger text-center">
                    <?php echo $errors['database']; ?>
                </div>
            <?php } ?>

            <form method="POST">

                <!-- Username -->
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control"
                           value="<?php echo htmlspecialchars($username); ?>">
                    <div class="error-text"><?php echo $errors['username'] ?? '' ?></div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?php echo htmlspecialchars($email); ?>">
                    <div class="error-text"><?php echo $errors['email'] ?? '' ?></div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control">
                    <div class="error-text"><?php echo $errors['password'] ?? '' ?></div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="c-password" class="form-control">
                    <div class="error-text"><?php echo $errors['cPassword'] ?? '' ?></div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="submit" class="btn btn-primary">
                        Register
                    </button>
                    <button type="reset" class="btn btn-outline-danger">
                        Reset
                    </button>
                </div>

                <p class="text-center mt-3">
                    Already have an account?
                    <a href="login.php" class="login-link text-primary">Login</a>
                </p>

            </form>
        </div>

    </div>
</div>

</body>
</html>
