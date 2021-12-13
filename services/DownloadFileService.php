<?php

namespace Service;

class DownloadFileService {

    /**
     * Force le telechargement d'un fichier qui est disponible en local
     * @param string $filePath
     */
    public function downloadLocalFile(string $filePath) {
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
        }
    }

    /**
     * Generer un fichier via une url
     * @param string $url
     * @return bool|string
     */
    public function downloadExterneFile(string $url): bool|string
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $error = 'Url Invalid';
            return $error;
        }
        $fileName = basename($url);
        $filePath = $this->_getWdDownloadDocument() . $fileName;

        $sessionCurl = curl_init();
        if(curl_setopt($sessionCurl, CURLOPT_URL, $url) ===  false) {
            return 'Erreur dans la récupération du fichier à télécharger';
        }

        $fp = fopen($filePath, 'w');
        if ($fp === false) {
            return 'Erreur dans la sauvegarde du fichier';
        }

        if(curl_setopt($sessionCurl, CURLOPT_FILE, $fp) === false) {
            return 'Erreur dans le téléchargement du fichier';
        }

        if (curl_exec ($sessionCurl) === false) {
            return 'Erreur dans la session curl';
        }
        curl_close ($sessionCurl);
        fclose($fp);

        $this->downloadLocalFile($filePath);

        return true;
    }

    /**
     * @return string
     */
    private function _getWdDownloadDocument(): string {
        $projectDirectory = dirname(__DIR__ , 1);
        // Pour les machines sous linux
        if (PHP_OS === 'Linux') {
            return $projectDirectory . '/public/download/';
        } else {
            // Pour les machines sous Windows
            return $projectDirectory . '\\public\\download\\';
        }
    }
}