<?php

namespace App\Utils;

class FormError {
    private $errorsMessage = [
        'empty' => 'Le champ renseigné est vide ',
        'maxLenght' => 'Le champs renseigné est trop long ',
        'mailError' => 'L\'adresse mail renseigner n\'est pas valide ',
    ];

    public function validateLength(string $value, string $key): bool|string {
        if (strlen($value) < 100) {
            return true;
        } else {
            return $this->errorsMessage['maxLenght'] . $key;
        }
    }

    public function validateEmpty(string $value, string $key): bool|string {
        if(isset($value)) {
            return true;
        } else {
            return $this->errorsMessage['empty'] . $value. ' ' . $key;
        }
    }

    public function validateMail(string $mail, string $key): bool|string
    {
        if(filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return $this->errorsMessage['mailError'] . $mail . ' ' . $key;
        }
    }
}
