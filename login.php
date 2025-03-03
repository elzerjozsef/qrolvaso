<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Admin ellenőrzés
    if ($username === 'Elvision' && $password === 'Elvision8923!') {
        $_SESSION['username'] = $username;
        header('Location: dashboard.php');
        exit();
    }

    // Felhasználók tárolása (később adatbázissal helyettesíthető)
    $usersFile = 'users.json';
    $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

    if (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
        $_SESSION['username'] = $username;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Hibás felhasználónév vagy jelszó!';
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="center-container">
        <h1>Bejelentkezés</h1>
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
            <button type="submit" class="btn">Bejelentkezés</button>
        </form>
        <p><a href="register.php" class="link">Regisztráció</a></p>
    </div>
</body>
</html>
