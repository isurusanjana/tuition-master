<?php
/**
 * Validator - simple rule-based validation.
 * Usage: $v = new Validator($data); $v->required('name')->email('email')->min('password',6);
 * if ($v->fails()) { $errors = $v->errors(); }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label = null): self
    {
        $label = $label ?? ucwords(str_replace('_', ' ', $field));
        if (!isset($this->data[$field]) || trim((string) $this->data[$field]) === '') {
            $this->errors[$field][] = "$label is required.";
        }
        return $this;
    }

    public function email(string $field): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "Please enter a valid email address.";
        }
        return $this;
    }

    public function min(string $field, int $len): self
    {
        if (!empty($this->data[$field]) && strlen((string) $this->data[$field]) < $len) {
            $this->errors[$field][] = ucfirst($field) . " must be at least $len characters.";
        }
        return $this;
    }

    public function numeric(string $field): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = ucfirst($field) . " must be a number.";
        }
        return $this;
    }

    public function unique(string $field, string $table, string $column = null, int $ignoreId = null): self
    {
        $column = $column ?? $field;
        if (empty($this->data[$field])) return $this;
        $sql = "SELECT id FROM $table WHERE $column = :val";
        $params = ['val' => $this->data[$field]];
        if ($ignoreId) {
            $sql .= " AND id != :id";
            $params['id'] = $ignoreId;
        }
        $exists = Database::fetchOne($sql, $params);
        if ($exists) {
            $this->errors[$field][] = ucfirst($field) . " is already taken.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }
        return null;
    }
}
