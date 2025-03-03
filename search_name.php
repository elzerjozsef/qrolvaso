<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$searchTerm = $_POST['term'] ?? '';
$filePath = 'uploads/nevek.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

$result = '';

foreach ($data as $row) {
    if (stripos($row[0], $searchTerm) !== false) {
        $result .= '<li onclick="selectName(\'' . addslashes($row[0]) . '\')">' . htmlspecialchars($row[0]) . '</li>';
    }
}

echo $result ?: '<li>Nincs találat</li>';
