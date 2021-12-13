<?php

namespace Service;

use App\Entity\Mail;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;

class MailService {

    private EntityManager $_em;
    public function __construct(EntityManager $em)
    {
        $this->_em = $em;
    }

    /**
     * Envoie d'un mail simple, sans piece jointe
     * @param string $fromMail
     * @param string $fromName
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param string $replyToMail
     * @param array $cc
     * @param bool $customMail True = utilisation d'un template twig par defaut false
     * @param string $fileTemplateName Le template twig
     * @param bool $storeInBdd True = Stocke le mail en BDD, par defaut true
     */
    public function sendMail(
        string $fromMail,
        string $fromName,
        string $to,
        string $subject,
        string $message,
        string $replyToMail = '',
        array $cc = [],
        bool $customMail = false,
        string $fileTemplateName = '',
        bool $storeInBdd = true
    ) {
        // Headers
        $headers = $this->_getMailHeaders($fromName, $fromMail,$replyToMail, $cc);
        $headers .='Content-Type:text/html; charset="uft-8"'."\n";
        $headers .='Content-Transfer-Encoding: 8bit' ."\r\n";

        if ($customMail && $fileTemplateName !== '') {
            $message = file_get_contents($this->_getWdMailTemplate($fileTemplateName)) ."\r\n";
        }

        mail($to, $subject , $message , $headers);

        if ($storeInBdd){
            try {
                $this->_createMailInBDD($fromMail, $fromName, $to, $subject, $message, '', $replyToMail, $cc);
            } catch (OptimisticLockException | ORMException $e) {
                echo 'Exception reçue : ',  $e->getMessage(), "\n";
            }
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function sendMailWithAttach(
        string $fromMail,
        string $fromName,
        string $to,
        string $subject,
        string $message,
        string $fileName,
        string $replyToMail = '',
        array $cc = [],
        bool $customMail = false,
        string $fileTemplateName = '',
        bool $storeInBdd = true
    ) {
        // Clé aléatoire de limite pour definir un separateur
        $boundary = md5(uniqid(microtime(), TRUE));

        // Headers
        $headers = $this->_getMailHeaders($fromName, $fromMail, $replyToMail, $cc);
        $headers .= 'Content-Type: multipart/mixed;boundary='.$boundary."\r\n";
        $headers .='Content-Transfer-Encoding: 8bit' ."\r\n";

        // Texte Message
        $Allmessage = '--'.$boundary."\r\n";
        $Allmessage .= 'Content-Type:text/html; charset="uft-8"'."\n";
        if ($customMail && $fileTemplateName !== '') {
            $Allmessage .= file_get_contents($this->_getWdMailTemplate($fileTemplateName)) ."\r\n" ;
        } else {
            $Allmessage .= $message ."\r\n";
        }

        // Pièce jointe
        $fullFileName = $this->_getWdAttachmentDocument($fileName);
        if (file_exists($fullFileName))
        {
            $fileType = filetype($fullFileName);
            $fileSize = filesize($fullFileName);

            $handle = fopen($fullFileName, 'r');
            if (!$handle) {
                die('File '.$fileName.'can t be open');
            }
            $content = fread($handle, $fileSize);
            $content = chunk_split(base64_encode($content));
            $f = fclose($handle);
            if (!$f) {
                die('File '.$fileName.'can t be close');
            }

            $Allmessage .= '--'.$boundary."\r\n";
            $Allmessage .= 'Content-type:'.$fileType.'/html;name='.$fileName."\r\n";
            $Allmessage .= 'Content-transfer-encoding:base64'."\r\n";
            $Allmessage .= $content."\r\n";
        }
        $Allmessage .= '--'.$boundary."\r\n";

        mail($to, $subject , $Allmessage , $headers);

        // On stocke le mail en BDD pour pouvoir avoir un suivis des mails
        if ($storeInBdd){
            $this->_createMailInBDD($fromMail, $fromName, $to, $subject, $message, $fileName , $replyToMail , $cc);
        }
    }

    /**
     * Une partie du Header du mail
     * @param string $fromName
     * @param string $fromMail
     * @param string $replyToMail
     * @param array $cc On peut avoir plusieurs mail de cc
     * @return string
     */
    private function _getMailHeaders(string $fromName, string $fromMail, string $replyToMail , array $cc): string
    {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .='From:'.$fromName.'<'.$fromMail.'>'."\n";
        $headers .='Reply-To: '.$replyToMail."\n";
        if (!empty($cc)) {
            foreach ($cc as $c) {
                $headers .= 'Cc: '. $c . "\r\n";
            }
        }
        return $headers;
    }

    /**
     * @param $fileName
     * @return string
     */
    private function _getWdAttachmentDocument($fileName): string {
        $projectDirectory = dirname(__DIR__);
        // Pour les machines sous linux
        if (PHP_OS === 'Linux') {
            return $projectDirectory . '/public/mailAttachment/' . $fileName;
        } else {
            // Pour Windows
            return $projectDirectory . '\\public\\mailAttachment\\' . $fileName;
        }
    }

    /**
     * @param $fileTemplateName
     * @return string
     */
    private function _getWdMailTemplate($fileTemplateName): string {
        $projectDirectory = dirname(__DIR__);
        // Pour les machines sous linux
        if (PHP_OS === 'Linux') {
            return $projectDirectory . '/templates/mail/custom/' . $fileTemplateName;
        } else {
            // Pour Windows
            return $projectDirectory . '\\templates\\mail\\custom\\' . $fileTemplateName;
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException|\Doctrine\ORM\ORMException
     */
    private function _createMailInBDD(
        string $fromMail,
        string $fromName,
        string $to,
        string $subject,
        string $message,
        string $fileName = '',
        string $replyToMail = '',
        array $cc = [],
    ) {
        $mail = new Mail();
        $mail->setFromMail($fromMail);
        $mail->setFromName($fromName);
        $mail->setToMail($to);
        $mail->setSubject($subject);
        $mail->setMessage($message);
        $mail->setFileName($fileName);
        $mail->setReplyToMail($replyToMail);
        $mail->setCc($cc);
        $mail->setDateSend(new DateTime());
        $this->_em->persist($mail);
        $this->_em->flush();
    }
}