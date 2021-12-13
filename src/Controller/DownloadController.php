<?php

namespace App\Controller;

use App\Routing\Attribute\Route;
use Service\DownloadFileService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DownloadController extends AbstractController{
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route(path: "/download", httpMethod: "GET", name: "showDownload")]
    public function showFormDownload() {
        echo $this->twig->render('download/form/formDownload.html.twig');
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: "/download/create", httpMethod: "POST", name: "createDownload")]
    public function createDownloadFile(DownloadFileService $downloadFileService) {
        if (!empty($_POST['fileurl'])) {
            $erreurMessage = $downloadFileService->downloadExterneFile($_POST['fileurl']);
            if ($erreurMessage !== true) {
                echo $this->twig->render('download/form/formDownload.html.twig' , [
                    'errorMessage' => $erreurMessage
                ]);
            }
        } else {
            echo $this->twig->render('download/form/formDownload.html.twig' , [
                'errorMessage' => 'Pas de Valeur donnée'
            ]);
        }
    }
}