<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $uploadFileDir = './uploads/';
    $dest_path = $uploadFileDir . $fileName;

    // Ellenőrizd, hogy létezik-e az uploads mappa
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
    }

    // Fájl mentése az uploads mappába
    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        echo "Fájl sikeresen feltöltve: " . $fileName . "<br>";

        // Excel fájl beolvasása és QR kód hozzárendelése
        $spreadsheet = IOFactory::load($dest_path);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        // Nevek beolvasása az első oszlopból és QR kód hozzárendelése
        $namesWithQR = [];
        foreach ($sheetData as $row) {
            $name = $row[0]; // Az első oszlopból olvassa ki a nevet
            if ($name) {
                $qrCodeValue = generateQRCode($name); // QR-kód generálása a névhez
                $namesWithQR[] = ['name' => $name, 'qrCode' => $qrCodeValue];
            }
        }

        echo "<h2>Nevek és QR-kódok:</h2>";
        foreach ($namesWithQR as $entry) {
            echo "Név: {$entry['name']} - QR kód érték: {$entry['qrCode']}<br>";
        }

    } else {
        echo "Hiba történt a fájl feltöltése során!";
    }
}

// QR-kód generálása (a név alapján)
function generateQRCode($name) {
    // Használhatunk egy egyszerű QR kód könyvtárat vagy API-t a QR kód generálására
    return md5($name); // Itt egy egyszerű hash generálás QR-kód értékként (tesztelési célra)
}
