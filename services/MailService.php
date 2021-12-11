<?php

namespace Service;

class MailService {

    public function sendMail(
        string $fromMail,
        string $fromName,
        string $to,
        string $subject,
        string $message,
        string $replyToMail = '',
        array $cc = [],
        bool $customMail = false,
        string $fileTemplateName = ''
    ) {
        // Headers
        $headers = $this->_getMailHeaders($fromName, $fromMail,$replyToMail, $cc);
        $headers .='Content-Type:text/html; charset="uft-8"'."\n";
        $headers .='Content-Transfer-Encoding: 8bit' ."\r\n";

        if ($customMail) {
            $message = file_get_contents($this->_getWdMailTemplate($fileTemplateName)) ."\r\n";
        }

        mail($to, $subject , $message , $headers);
    }

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
        string $fileTemplateName = ''
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
        if ($customMail) {
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
    }

    private function _getMailHeaders(string $fromName, string $fromMail, string $replyToMail , array $cc) {
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

    private function _getWdAttachmentDocument($fileName): string {
        $projectDirectory = dirname(__DIR__);
        // Pour les machines tournant sous linux
        if (PHP_OS === 'Linux') {
            return $projectDirectory . '/public/mailAttachment/' . $fileName;
        } else {
            // Pour Windows
            return $projectDirectory . '\\public\\mailAttachment\\' . $fileName;
        }
    }

    private function _getWdMailTemplate($fileTemplateName): string {
        $projectDirectory = dirname(__DIR__);
        // Pour les machines tournant sous linux
        if (PHP_OS === 'Linux') {
            return $projectDirectory . '/templates/mail/' . $fileTemplateName;
        } else {
            // Pour Windows
            return $projectDirectory . '\\templates\\mail\\' . $fileTemplateName;
        }
    }
}