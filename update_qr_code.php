<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$name = $_POST['name'] ?? '';
$qrCode = $_POST['qr_code'] ?? '';
$filePath = 'uploads/nevek.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

foreach ($data as $index => $row) {
    if ($row[0] === $name) {
        $rowIndex = $index + 1;
        if ($row[1] === $qrCode) {
            $sheet->setCellValue('B' . $rowIndex, '');
            echo "QR-kód törölve: $qrCode";
        } else {
            $sheet->setCellValue('B' . $rowIndex, $qrCode);
            echo "QR-kód hozzárendelve: $qrCode a $name névhez";
        }
        break;
    }
}

$writer = new Xlsx($spreadsheet);
$writer->save($filePath);
