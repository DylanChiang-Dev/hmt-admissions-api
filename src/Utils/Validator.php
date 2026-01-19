<?php

namespace App\Utils;

use App\Exceptions\ValidationException;

class Validator
{
    public static function validate(array $data, array $rules): void
    {
        foreach ($rules as $field => $ruleString) {
            $ruleParts = explode('|', $ruleString);

            foreach ($ruleParts as $rule) {
                if ($rule === 'required') {
                    if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                        throw new ValidationException("Field '$field' is required.", ['field' => $field, 'rule' => 'required']);
                    }
                }

                if (str_starts_with($rule, 'in:')) {
                    $options = explode(',', substr($rule, 3));
                    if (isset($data[$field]) && !in_array($data[$field], $options)) {
                        throw new ValidationException("Field '$field' must be one of: " . implode(', ', $options), ['field' => $field, 'rule' => 'in', 'options' => $options]);
                    }
                }
            }
        }
    }
}
