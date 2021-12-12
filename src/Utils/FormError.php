<?php

namespace App\Utils;

class FormError {
    private $_errorsMessage = array(
        'empty' => 'Le(s) champ(s) obligatoire(s) est(sont) vide(s) ',
        'maxLenght' => 'Le champs renseigné est trop long ',
        'mailError' => 'L\'adresse mail renseigner n\'est pas valide ',
    );

    public function validateLength(string $value, string $key): bool|string {
        if (strlen($value) < 100) {
            return true;
        } else {
            return $this->_errorsMessage['maxLenght'] . $key;
        }
    }

    public function validateEmpty(array $array): bool|string {
        foreach ($array as $arr) {
            if (empty($arr)) {
                return $this->_errorsMessage['empty'] . $arr;
            }
        }
        return true;
    }

    public function validateMail(string $mail, string $key): bool|string
    {
        if(filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return $this->_errorsMessage['mailError'] . $mail . ' ' . $key;
        }
    }
}
