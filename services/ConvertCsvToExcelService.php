<?php

namespace Service;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ConvertCsvToExcelService {
    /**
     * Convertis un fichier csv en exel grace à PhpSpreadsheet
     * @param string $fileName
     * @return string
     * @throws Exception
     */
    public function convertCsvToExel(string $fileName): string
    {
        $spreadsheet = new Spreadsheet();
        $reader = new Csv();

        // On met les parametres pour lire le csv
        $reader->setDelimiter(';');
        $reader->setEnclosure('"');
        $reader->setSheetIndex(0);

        // Charger le fichier csv et generer le fichier xls
        $spreadsheet = $reader->load($fileName);
        $fileNameWithoutExtension = explode('.' , $fileName)[0];
        $writer = new Xlsx($spreadsheet);
        $writer->save($fileNameWithoutExtension.'.xlsx');

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $fileNameWithoutExtension.'.xlsx';
    }
}