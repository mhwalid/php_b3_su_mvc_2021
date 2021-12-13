<?php

namespace App\Controller;

use App\Entity\Mail;
use App\Routing\Attribute\Route;
use App\Utils\FormError;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Service\MailService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MailController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: "/mail", httpMethod: "GET", name: "showMail")]
    public function showMail() {
        echo $this->twig->render('mail/form/createMail.html.twig');
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: "/mails", httpMethod: "GET", name: "showMails")]
    public function showMails(EntityManager $em) {
        $mails = $em->getRepository(Mail::class)->findAll();
        echo $this->twig->render('mail/showMails.html.twig' , [
            'mails' => $mails
        ]);
    }

    /**
     * @throws OptimisticLockException
     * @throws SyntaxError
     * @throws ORMException
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: "/mail/create", httpMethod: "POST", name: "createMail")]
    public function createMail(MailService $mail , FormError $error)
    {
        if (!empty($_POST)) {
                // Verification des champs obligatoires
                $tabObligatoire = array($_POST['senderName'], $_POST['senderMail'] , $_POST['receiverMail'] , $_POST['subject'], $_POST['message']);
                $errorEmpty = $error->validateEmpty($tabObligatoire);
                $errorMessageUploadOrSuccess = true;

                // Verification si une PJ est importer et upload de celle ci dans le dossier attachment dans public/
                if (!empty($_FILES["formFile"]['size'])) {
                    $errorMessageUploadOrSuccess = $this->_uploadFile($_FILES["formFile"]['name']);
                }

                // Si il n'y a pas d'erreur dans l'upload et que les champs obligatoire ne sont pas vide
                if ($errorEmpty === true && $errorMessageUploadOrSuccess === true) {
                    // Recuperation de chaque addresse mail cc transformer en array
                    $arrayMailCc = $this->_fillArrayCC();
                    // On regarde les erreurs dans le formulaire
                    $isNotError = $this->_validateFormError($error, $arrayMailCc);

                    if ($isNotError){
                        if ($_FILES["formFile"]['size'] > 0) {
                            $mail->sendMailWithAttach($_POST['senderMail'], $_POST['senderName'], $_POST['receiverMail'], $_POST['subject'], $_POST['message'], $_FILES["formFile"]["name"], $_POST['replyMail'], $arrayMailCc);
                            $title = 'Envoie mail PJ';
                            $this->_renderSuccessMail($title , $_POST['receiverMail'], $_POST['senderMail'], $_POST['subject'] , $_FILES["formFile"]["name"]);
                        } else {
                            $mail->sendMail($_POST['senderMail'], $_POST['senderName'], $_POST['receiverMail'], $_POST['subject'], $_POST['message'], $_POST['replyMail'], $arrayMailCc);
                            $title = 'Envoie mail';
                            $this->_renderSuccessMail($title , $_POST['receiverMail'],  $_POST['senderMail'], $_POST['subject']);
                        }
                    }
                } else {
                    // On renvoie les erreurs et les informations saisis par l'utilisateur
                    echo $this->twig->render('mail/form/createMail.html.twig' , [
                        'errorEmpty' => $errorEmpty,
                        'errorUploadFile' => $errorMessageUploadOrSuccess,
                        'values' => $_POST
                    ]);
                }
        }
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    private function _renderSuccessMail($title , $to, $fromName, $subject, $filesName = '') {
        echo $this->twig->render('mail/successSendMail.html.twig' , [
            'titre' => $title,
            'to' => $to,
            'fromName' => $fromName,
            'subject' => $subject,
            'fileName' => $filesName
        ]);
    }

    private function _fillArrayCC(): array
    {
        $arrayMailCc = [];
        if (!empty($_POST['ccMail'])) {
            $arrayMailCc = explode(",", $_POST['ccMail']);
        }
        return $arrayMailCc;
    }

    private function _validateFormError(FormError $error , array $cc): bool
    {
        foreach ($_POST as $key => $post) {
            $errorLenghtMessage = $error->validateLength($post , $key);
            if ($key === 'senderMail' || $key === 'receiverMail' || ($key === 'replyMail'&& !empty($post))) {
                $errorMailMessage = $error->validateMail($post, $key);
            } else {
                $errorMailMessage = true;
            }
            // On valide les addresses cc
            if (!empty($cc) && $key === 'ccMail') {
                foreach($cc as $c) {
                    $errorMailMessage = $error->validateMail($c, $key);
                }
            }
            // Si il y a des erreurs on les renvoies au formulaire
            if ($errorLenghtMessage !== true || $errorMailMessage !== true) {
                echo $this->twig->render('mail/form/createMail.html.twig' , [
                    'errorLength' => $errorLenghtMessage,
                    'errorMail' => $errorMailMessage,
                    'values' => $_POST
                ]);
                return false;
            }
        }
        return true;
    }

    private function _uploadFile(string $fileName): bool|string
    {
        if(isset($_FILES["formFile"]) && $_FILES["formFile"]["error"] == 0){
            $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"  , "pdf" => "application/pdf" );
            $filesize = $_FILES["formFile"]["size"];
            $filetype = $_FILES["formFile"]["type"];
            // Taille du fichier < 5MO
            $maxsize = 5 * 1024 * 1024;
            if($filesize > $maxsize) {
                return 'Taille du fichier importer trop grande ' . $filesize/(1024 * 1024) . ' Mb';
            }
            // Vérifie le type du fichier
            if(in_array($filetype, $allowed)){
                $uploadDirFile = $this->_getWdAttachmentDocument() . $fileName;
                //var_dump($pathFile);
                if(file_exists($uploadDirFile)){
                    unlink($uploadDirFile);
                }
                // Creation et moove du fichier importer dans le repertoire
                move_uploaded_file($_FILES["formFile"]["tmp_name"], $uploadDirFile);
            } else {
                return 'Probleme dans l\'upload de fichier verifier le format de la PJ. Format accepté : .jpg / .png / .gif / .jpeg / .pdf';
            }
        }
        return true;
    }


    /**
     * @return string
     */
    private function _getWdAttachmentDocument(): string
    {
        // On monte de deux niveau pour atteindre la racine du projet
        $projectDirectory = dirname(__DIR__ , 2);
        // Pour les machines sous linux
        if (PHP_OS === 'Linux') {
            return $projectDirectory . '/public/mailAttachment/';
        } else {
            // Pour les machines sous Windows
            return $projectDirectory . '\\public\\mailAttachment\\';
        }
    }
}