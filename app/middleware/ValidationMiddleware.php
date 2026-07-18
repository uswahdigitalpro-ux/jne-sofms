<?php

namespace App\Middleware;

class ValidationMiddleware
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $this->validateField($field, $value, $rule);
        }

        return empty($this->errors);
    }

    private function validateField(string $field, mixed $value, string $rules): void
    {
        $ruleArray = explode('|', $rules);

        foreach ($ruleArray as $rule) {
            if (strpos($rule, ':') !== false) {
                [$ruleName, $ruleValue] = explode(':', $rule, 2);
            } else {
                $ruleName = $rule;
                $ruleValue = null;
            }

            $this->applyRule($field, $value, $ruleName, $ruleValue);
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, ?string $ruleValue): void
    {
        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' wajib diisi';
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = ucfirst($field) . ' harus berupa email yang valid';
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < (int)$ruleValue) {
                    $this->errors[$field][] = ucfirst($field) . ' minimal ' . $ruleValue . ' karakter';
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > (int)$ruleValue) {
                    $this->errors[$field][] = ucfirst($field) . ' maksimal ' . $ruleValue . ' karakter';
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' harus berupa angka';
                }
                break;

            case 'unique':
                // Implement database check if needed
                break;

            case 'confirmed':
                if ($value !== ($_POST[$field . '_confirmation'] ?? null)) {
                    $this->errors[$field][] = ucfirst($field) . ' tidak cocok';
                }
                break;
        }
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }
}
