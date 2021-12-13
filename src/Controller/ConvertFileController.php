<?php

namespace App\Controller;

use App\Routing\Attribute\Route;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Service\ConvertCsvToExcelService;
use Service\DownloadFileService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ConvertFileController extends AbstractController{
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route(path: "/convert/csv", httpMethod: "GET", name: "showConvertCsv")]
    public function showConvert() {
        echo $this->twig->render('convert/form/formConvertCsv.html.twig');
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws Exception
     * @throws LoaderError
     */
    #[Route(path: "/convert/csv/create", httpMethod: "POST", name: "createConvertCsv")]
    public function createConvertCsv(ConvertCsvToExcelService $convertService, DownloadFileService $downloadFileService) {
        $messageErreur = $this->_uploadFile();
        if ($messageErreur === '') {
            $fileName = $_FILES["formFile"]["name"];
            $pathFile = $this->_getWdConvertDocument() . $fileName;
            $newPathFile = $convertService->convertCsvToExel($pathFile);
            $downloadFileService->downloadLocalFile($newPathFile);
        } else {
            echo $this->twig->render('convert/form/formConvertCsv.html.twig' , [
                'importError' => $messageErreur
            ]);
        }
    }

    private function _uploadFile(): string
    {
        $noError = '';
        if(isset($_FILES["formFile"]) && $_FILES["formFile"]["error"] == 0 && $_FILES["formFile"]["size"] > 0){
            $allowed = array("csv" => "application/vnd.ms-excel");
            $filename = $_FILES["formFile"]["name"];
            $filesize = $_FILES["formFile"]["size"];
            $filetype = $_FILES["formFile"]["type"];

            // Vérifie l'extension du fichier
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if(!array_key_exists($ext, $allowed)) {
                 return "Erreur : Veuillez sélectionner un format de fichier valide";
            }

            // Taille du fichier < 5MO
            $maxsize = 5 * 1024 * 1024;
            if($filesize > $maxsize) {
                return 'Erreur : Taille du fichier importer trop grande ' . $filesize/(1024 * 1024) . ' Mb';
            }
            // Vérifie le type du fichier
            if(in_array($filetype, $allowed)){
                $pathFile = $this->_getWdConvertDocument() . $filename;
                if(file_exists($pathFile)){
                    unlink($pathFile);
                }
                move_uploaded_file($_FILES["formFile"]["tmp_name"], $pathFile);
            } else {
                return 'Probleme dans l\'upload de fichier';
            }
        } else {
            return 'Erreur avec le fichier';
        }
        return $noError;
    }

    private function _getWdConvertDocument(): string {
        $projectDirectory = dirname(__DIR__ , 2);
        // Pour les machines tournant sous linux
        if (PHP_OS === 'Linux') {
            return $projectDirectory . '/public/convertFile/';
        } else {
            // Pour Windows
            return $projectDirectory . '\\public\\convertFile\\';
        }
    }
}