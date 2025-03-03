<?php
require 'vendor/autoload.php';  // PhpSpreadsheet autoloader

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_code'])) {
    // Excel fájl helye
    $filePath = 'uploads/nevek.xlsx';  // Ha a fájl a weboldal gyökérmappájában található
    $qrCode = trim(strtolower($_POST['qr_code']));  // A törölni kívánt QR-kód, kisbetűsre konvertálva és szóközök eltávolítva
    $response = ['status' => 'info', 'message' => 'QR-kód törlés elindult...'];

    // Logolás kezdete
    $logMessage = "QR-kód törlés elindult: " . $qrCode;
    error_log($logMessage, 3, "uploads/error_log.log");  // A QR-kódot és az indítást rögzítjük

    try {
        // Excel fájl betöltése
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // B oszlop keresése
        $highestRow = $sheet->getHighestRow(); // A legutolsó sor számának lekérése
        $response['message'] .= " Excel fájl betöltve, sorok: " . $highestRow;

        $deletedQR = null;  // A törölt QR-kód eltárolása

        // Keresés a B oszlopban
        for ($row = 1; $row <= $highestRow; $row++) {
            // Az adott sor B oszlopának lekérése
            $cellValue = trim(strtolower($sheet->getCell('B' . $row)->getValue())); // Kisbetűsre konvertálva és szóközök eltávolítva
            if ($cellValue == $qrCode) {
                // Csak a QR-kód cellát töröljük, ne az egész sort
                $sheet->setCellValue('B' . $row, '');  // QR-kód cellájának törlése
                $deletedQR = $qrCode;  // Megjegyezzük a törölt QR-kódot
                $response['message'] .= " QR kód találva és törölve a sorban: " . $row;
                $logMessage = "QR-kód törölve a sorban: $row";
                error_log($logMessage, 3, "uploads/error_log.log");  // Logoljuk a sikeres törlést
                break; // Kiszállunk a ciklusból, ha megtaláltuk és töröltük
            }
        }

        // Ha nincs törlendő QR-kód, akkor értesítjük a felhasználót
        if ($deletedQR === null) {
            $response['status'] = 'success';  // Törlés után is sikeres válasz
            $response['message'] = 'Törölve';  // Csak törlés üzenet
            $logMessage = "QR-kód nem található, törlés nem történt.";
            error_log($logMessage, 3, "uploads/error_log.log");  // Logoljuk, ha nem találunk törlendő QR-kódot
            echo json_encode($response);  // Egy válasz, nem több
            exit(); // Leállítjuk a scriptet, hogy ne folytassuk a törlés után
        }

        // Mentés az Excel fájlba
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        // JSON válasz visszaküldése
        $response['status'] = 'success';
        $response['message'] .= ' QR-kód törlésre került minden előfordulásban.';
        $response['deletedQR'] = $deletedQR;  // Hozzáadjuk a törölt QR-kódot a válaszhoz
        $logMessage = "QR-kód sikeresen törölve: " . $deletedQR;
        error_log($logMessage, 3, "uploads/error_log.log");  // Logoljuk a törlés sikerét
        echo json_encode($response);
        exit();  // Fontos: itt megállítjuk a scriptet, hogy ne folytassuk a törlés után
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Hiba történt: ' . $e->getMessage();
        $logMessage = "Hiba történt: " . $e->getMessage();
        error_log($logMessage, 3, "uploads/error_log.log");  // Logoljuk a hibát
        echo json_encode($response);
        exit();
    }
} else {
    // Ha nincs QR-kód a POST-ban
    $response = ['status' => 'error', 'message' => 'Nincs QR-kód a kérésben.'];
    $logMessage = "Nincs QR-kód a kérésben";
    error_log($logMessage, 3, "uploads/error_log.log");  // Logoljuk, ha nincs QR-kód
    echo json_encode($response);
    exit();
}
?>
