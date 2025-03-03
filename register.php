<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);

    if ($password !== $confirmPassword) {
        $error = 'A jelszavak nem egyeznek!';
    } else {
        $usersFile = 'users.json';
        $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

        if (isset($users[$username])) {
            $error = 'A felhasználónév már létezik!';
        } else {
            $users[$username] = [
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'email' => $email,
                'folder' => date('Y-m-d')
            ];
            file_put_contents($usersFile, json_encode($users));
            mkdir('uploads/' . $users[$username]['folder'], 0777, true);
            header('Location: login.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="center-container">
        <h1>Regisztráció</h1>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">Felhasználónév:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Jelszó:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Jelszó megerősítése:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <button type="submit" class="btn">Regisztráció</button>
        </form>
        <p><a href="login.php" class="link">Bejelentkezés</a></p>
    </div>
</body>
</html>
