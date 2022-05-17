<?php

namespace App;

class Validator
{
    const MIN_LENGTH = 4;
    const MAX_LENGTH = 16;

    public function validate(array $user)
    {
        $errors = [];
        
        $name = $user['name'];
        $length = strlen($name);
        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            $errors['name'] = 'Name must contains from 4 to 16 characters';
        }

        return $errors;
    }
}