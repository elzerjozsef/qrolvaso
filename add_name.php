<?php
require 'vendor/autoload.php';  // PhpSpreadsheet autoloader

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $filePath = 'uploads/nevek.xlsx';  // Az Excel fájl helye
    $name = $_POST['name'];  // A hozzáadni kívánt név

    try {
        // Excel fájl betöltése
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Keresés az első üres cellában (pl. A oszlopban)
        $lastRow = $sheet->getHighestRow() + 1;  // Következő üres sor

        // Név hozzáadása
        $sheet->setCellValue('A' . $lastRow, $name);

        // Mentés
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        // Válasz visszaadása
        echo json_encode(['status' => 'success', 'message' => 'Név sikeresen hozzáadva.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Hiba történt: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Nincs név a kérésben.']);
}
?>
