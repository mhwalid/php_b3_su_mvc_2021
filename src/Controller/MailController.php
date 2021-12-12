<?php

namespace App\Controller;

use App\Controller\AbstractController;
use App\Entity\Mail;
use App\Routing\Attribute\Route;
use App\Utils\FormError;
use Doctrine\ORM\EntityManager;
use Service\MailService;

class MailController extends AbstractController
{
    #[Route(path: "/mail", name: "showMail", httpMethod: "GET")]
    public function showMail() {
        echo $this->twig->render('mail/form/createMail.html.twig');
    }

    #[Route(path: "/show/mails", name: "showMails", httpMethod: "GET")]
    public function showMails(EntityManager $em) {
        $mails = $em->getRepository(Mail::class)->findAll();
        echo $this->twig->render('mail/showMails.html.twig' , [
            'mails' => $mails
        ]);
    }

    #[Route(path: "/createMail", name: "createMail", httpMethod: "POST")]
    public function createMail(MailService $mail , FormError $error)
    {
        if (!empty($_POST)) {
                // Verification des champs obligatoires
                $tabObligatoire = [$_POST['senderName'], $_POST['senderMail'] , $_POST['receiverMail'] , $_POST['subject'], $_POST['message']];
                $errorEmpty = $error->validateEmpty($tabObligatoire);

                // Verification si une PJ est importer et upload de celle ci dans le dossier attachment dans public/
                if (!empty($_FILES['size'])) {
                    $errorMessageUpload = $this->_uploadFile();
                } else {
                    $errorMessageUpload = true;
                }

                // Si il n'y a pas d'erreur dans l'upload et que les champs obligatoire ne sont pas vide
                if ($errorEmpty === true && $errorMessageUpload === true) {
                    // Recuperation de chaque addresse mail cc transformer en array
                    $arrayMailCc = $this->_fillArrayCC();
                    // On regarde les erreurs dans le formulaire
                    $noError = $this->_validateFormError($error, $arrayMailCc);

                    if ($noError){
                        if ($_FILES["formFile"]['size'] > 0) {
                            $this->_mailAttach($mail, $_POST['senderMail'], $_POST['senderName'],$_POST['receiverMail'], $_POST['subject'], $_POST['message'] , $_FILES["formFile"]["name"] , $_POST['replyMail'] , $arrayMailCc);
                        } else {
                            $this->_mail($mail, $_POST['senderMail'], $_POST['senderName'],$_POST['receiverMail'], $_POST['subject'], $_POST['message'], $_POST['replyMail'] , $arrayMailCc);
                        }
                    }
                } else {
                    // On renvoie les erreurs et les informations saisis par l'utilisateur
                    echo $this->twig->render('mail/form/createMail.html.twig' , [
                        'errorEmpty' => $errorEmpty,
                        'errorUploadFile' => $errorMessageUpload,
                        'values' => $_POST
                    ]);
                }
        }
    }

    private function _mailAttach($mail , $from, $fromName , $to , $subject, $message, $filesName = '' , $replyTo = '' , $cc = [], $customMail = false , $fileTemplateName = '') {
        $mail->sendMailWithAttach(
            $from,
            $fromName,
            $to,
            $subject,
            $message,
            $filesName,
            $replyTo,
            $cc,
            $customMail,
            $fileTemplateName
        );
        $title = 'Envoie mail PJ';
        $this->_renderSuccessMail($title , $to, $from, $subject , true, $filesName);
    }

    private function _mail($mail , $from, $fromName , $to , $subject, $message, $replyTo = '' , $cc = [], $customMail = false , $fileTemplateName = '') {
        $mail->sendMail(
            $from,
            $fromName,
            $to,
            $subject,
            $message,
            $replyTo,
            $cc,
            $customMail,
            $fileTemplateName
        );
        $title = 'Envoie mail';
        $this->_renderSuccessMail($title , $to, $fromName, $subject);
    }

    private function _renderSuccessMail($title , $to, $fromName, $subject, $isPj = false, $filesName = '') {
        echo $this->twig->render('mail/successSendMail.html.twig' , [
            'titre' => $title,
            'to' => $to,
            'fromName' => $fromName,
            'subject' => $subject,
            'isPj' => $isPj,
            'fileName' => $filesName
        ]);
    }

    private function _fillArrayCC(){
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

    private function _uploadFile(): bool|string
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
                $fileName = $this->_getWdAttachmentDocument() . $_FILES["formFile"]["name"];
                if(file_exists($fileName)){
                    unlink($fileName);
                }
                move_uploaded_file($_FILES["formFile"]["tmp_name"], $fileName);
            } else {
                return 'Probleme dans l\'upload de fichier';
            }
        }
        return true;
    }


    private function _getWdAttachmentDocument(): string {
        $projectDirectory = dirname(__DIR__);
        // Pour les machines tournant sous linux
        if (PHP_OS === 'Linux') {
            return $projectDirectory . '/public/mailAttachment/';
        } else {
            // Pour Windows
            return $projectDirectory . '\\public\\mailAttachment\\';
        }
    }
}