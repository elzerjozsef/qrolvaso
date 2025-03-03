<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$users = json_decode(file_get_contents('users.json'), true);
$userFolder = 'uploads/' . $users[$username]['folder'];

// Fájlok listázása
$files = glob("$userFolder/*.xlsx");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $fileToDelete = $_POST['delete'];
        if (file_exists($fileToDelete)) unlink($fileToDelete);
    } elseif (isset($_FILES['file'])) {
        move_uploaded_file($_FILES['file']['tmp_name'], "$userFolder/" . $_FILES['file']['name']);
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css"> <!-- Külső CSS fájl -->
</head>
<body>
    <div class="container">
        <h1>Felhasználói Dashboard</h1>
        <p>Üdvözöljük, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>

        <h2>Feltöltés és fájlok kezelése</h2>

        <!-- Fájl feltöltés -->
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="file-upload">Új XLSX fájl feltöltése:</label>
            <input type="file" id="file-upload" name="uploaded_file" accept=".xlsx">
            <button type="submit">Feltöltés</button>
        </form>

        <h3>Feltöltött fájlok</h3>
        <table>
            <thead>
                <tr>
                    <th>Fájl neve</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $userFolder = 'uploads/' . $_SESSION['username'];
                if (is_dir($userFolder)) {
                    $files = scandir($userFolder);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..') {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($file) . "</td>";
                            echo "<td>
                                    <a href='select_file.php?file=" . urlencode($file) . "' class='action-button'>Kiválaszt</a>
                                    <a href='delete_file.php?file=" . urlencode($file) . "' class='action-button delete'>Törlés</a>
                                  </td>";
                            echo "</tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='2'>Nincs feltöltött fájl.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <a href="logout.php" class="logout-button">Kijelentkezés</a>
    </div>
</body>
</html>
