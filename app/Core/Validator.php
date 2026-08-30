<?php

namespace App\Core;

class Validator {
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self {
        $validator = new self($data, $rules);
        $validator->validate();
        return $validator;
    }

    public function validate(): bool {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $rulesList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value = $this->data[$field] ?? null;

            foreach ($rulesList as $rule) {
                $ruleParams = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramStr] = explode(':', $rule, 2);
                    $ruleParams = explode(',', $paramStr);
                } else {
                    $ruleName = $rule;
                }

                $this->applyRule($field, $value, $ruleName, $ruleParams);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void {
        $formattedField = ucwords(str_replace(['_', '-'], ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->addError($field, "{$formattedField} is required.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "{$formattedField} must be a valid email address.");
                }
                break;

            case 'min':
                $min = (int) ($params[0] ?? 0);
                if (is_string($value) && mb_strlen($value) < $min) {
                    $this->addError($field, "{$formattedField} must be at least {$min} characters.");
                } elseif (is_numeric($value) && $value < $min) {
                    $this->addError($field, "{$formattedField} must be at least {$min}.");
                }
                break;

            case 'max':
                $max = (int) ($params[0] ?? 255);
                if (is_string($value) && mb_strlen($value) > $max) {
                    $this->addError($field, "{$formattedField} must not exceed {$max} characters.");
                } elseif (is_numeric($value) && $value > $max) {
                    $this->addError($field, "{$formattedField} must not exceed {$max}.");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "{$formattedField} must be a number.");
                }
                break;

            case 'confirmed':
                $confirmationKey = $field . '_confirmation';
                $confirmationValue = $this->data[$confirmationKey] ?? null;
                if ($value !== $confirmationValue) {
                    $this->addError($field, "{$formattedField} confirmation does not match.");
                }
                break;

            case 'in':
                if (!empty($value) && !in_array((string)$value, $params, true)) {
                    $this->addError($field, "The selected {$formattedField} is invalid.");
                }
                break;

            case 'unique':
                $table = $params[0] ?? '';
                $column = $params[1] ?? $field;
                $ignoreId = $params[2] ?? null;
                if (!empty($value) && !empty($table)) {
                    $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val";
                    $queryParams = ['val' => $value];
                    if ($ignoreId !== null && $ignoreId !== '') {
                        $sql .= " AND id != :ignore_id";
                        $queryParams['ignore_id'] = $ignoreId;
                    }
                    $count = (int) Database::fetchValue($sql, $queryParams);
                    if ($count > 0) {
                        $this->addError($field, "This {$formattedField} is already taken.");
                    }
                }
                break;
        }
    }

    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function passes(): bool {
        return empty($this->errors);
    }

    public function fails(): bool {
        return !$this->passes();
    }

    public function errors(): array {
        return $this->errors;
    }

    public function first(string $field): ?string {
        return $this->errors[$field][0] ?? null;
    }
}
