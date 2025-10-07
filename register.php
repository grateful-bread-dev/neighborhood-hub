<?php
session_start();
require_once "config.php";

// if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $display_name = trim($_POST["display_name"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    // added location fields for registration
    $city = trim($_POST["city"] ?? "");
    $state = trim($_POST["state"] ?? "");
    $country = trim($_POST["country"] ?? "");

    $errors = [];

    // validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($display_name) < 3) {
        $errors[] = "Display name must be at least 3 characters.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    // require location fields
    if ($city === "" || $state === "" || $country === "") {
        $errors[] = "City, State/Province, and Country are required.";
    }

    // insert into DB if no errors
    if (empty($errors)) {
        try {
            // check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // insert with location
                $stmt = $pdo->prepare(
                    "INSERT INTO users (email, password_hash, display_name, city, state, country, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, NOW())"
                );
                $stmt->execute([$email, $hash, $display_name, $city, $state, $country]);

                // log user in immediately and keep location in session
                $_SESSION["user_id"] = (int)$pdo->lastInsertId();
                $_SESSION["display_name"] = $display_name;
                $_SESSION["user_city"] = $city;
                $_SESSION["user_state"] = $state;
                $_SESSION["user_country"] = $country;

                header("Location: feed.php");
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Neighborhood Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Create Your Account</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post" class="auth-form">
            <h2>Create Your Account</h2>

            <label for="display_name">Display Name</label>
            <input type="text" name="display_name" id="display_name" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <label for="city">City/Town</label>
            <input type="text" name="city" id="city" required>

            <label for="state">State/Province</label>
            <input type="text" name="state" id="state" required>

            <label for="country">Country</label>
            <input type="text" name="country" id="country" required>

            <button type="submit" class="btn btn-primary">Register</button>
            </form>

            <p class="register-link">
            Already have an account? <a href="index.php">Log in here</a>
            </p>

    </div>
</body>
</html>
