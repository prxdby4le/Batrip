<?php
/**
 * Validator Helper
 * 
 * Sistema de validação de dados
 * 
 * @category Helpers
 * @package  Batrip
 */

namespace App\Helpers;

class Validator
{
    /**
     * Erros de validação
     *
     * @var array
     */
    private array $errors = [];

    /**
     * Dados a validar
     *
     * @var array
     */
    private array $data = [];

    /**
     * Construtor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Valida campo obrigatório
     *
     * @param  string $field
     * @param  string $message
     * @return self
     */
    public function required(string $field, string $message = null): self
    {
        if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
            $this->errors[$field][] = $message ?? "O campo {$field} é obrigatório";
        }
        return $this;
    }

    /**
     * Valida email
     *
     * @param  string $field
     * @param  string $message
     * @return self
     */
    public function email(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field][] = $message ?? "Email inválido";
            }
        }
        return $this;
    }

    /**
     * Valida tamanho mínimo
     *
     * @param  string $field
     * @param  int    $min
     * @param  string $message
     * @return self
     */
    public function min(string $field, int $min, string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (strlen($this->data[$field]) < $min) {
                $this->errors[$field][] = $message ?? "O campo {$field} deve ter no mínimo {$min} caracteres";
            }
        }
        return $this;
    }

    /**
     * Valida tamanho máximo
     *
     * @param  string $field
     * @param  int    $max
     * @param  string $message
     * @return self
     */
    public function max(string $field, int $max, string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (strlen($this->data[$field]) > $max) {
                $this->errors[$field][] = $message ?? "O campo {$field} deve ter no máximo {$max} caracteres";
            }
        }
        return $this;
    }

    /**
     * Valida valor numérico
     *
     * @param  string $field
     * @param  string $message
     * @return self
     */
    public function numeric(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!is_numeric($this->data[$field])) {
                $this->errors[$field][] = $message ?? "O campo {$field} deve ser numérico";
            }
        }
        return $this;
    }

    /**
     * Valida valor inteiro
     *
     * @param  string $field
     * @param  string $message
     * @return self
     */
    public function integer(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
                $this->errors[$field][] = $message ?? "O campo {$field} deve ser um número inteiro";
            }
        }
        return $this;
    }

    /**
     * Valida CPF
     *
     * @param  string $field
     * @param  string $message
     * @return self
     */
    public function cpf(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $cpf = preg_replace('/[^0-9]/', '', $this->data[$field]);
            
            if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
                $this->errors[$field][] = $message ?? "CPF inválido";
                return $this;
            }

            // Valida dígitos verificadores
            for ($t = 9; $t < 11; $t++) {
                $d = 0;
                for ($c = 0; $c < $t; $c++) {
                    $d += $cpf[$c] * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf[$c] != $d) {
                    $this->errors[$field][] = $message ?? "CPF inválido";
                    return $this;
                }
            }
        }
        return $this;
    }

    /**
     * Valida CEP
     *
     * @param  string $field
     * @param  string $message
     * @return self
     */
    public function cep(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $cep = preg_replace('/[^0-9]/', '', $this->data[$field]);
            if (strlen($cep) != 8) {
                $this->errors[$field][] = $message ?? "CEP inválido";
            }
        }
        return $this;
    }

    /**
     * Valida telefone
     *
     * @param  string $field
     * @param  string $message
     * @return self
     */
    public function phone(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $phone = preg_replace('/[^0-9]/', '', $this->data[$field]);
            $length = strlen($phone);
            if ($length < 10 || $length > 11) {
                $this->errors[$field][] = $message ?? "Telefone inválido";
            }
        }
        return $this;
    }

    /**
     * Valida URL
     *
     * @param  string $field
     * @param  string $message
     * @return self
     */
    public function url(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
                $this->errors[$field][] = $message ?? "URL inválida";
            }
        }
        return $this;
    }

    /**
     * Valida se campos são iguais
     *
     * @param  string $field
     * @param  string $match
     * @param  string $message
     * @return self
     */
    public function match(string $field, string $match, string $message = null): self
    {
        if (isset($this->data[$field], $this->data[$match])) {
            if ($this->data[$field] !== $this->data[$match]) {
                $this->errors[$field][] = $message ?? "Os campos não coincidem";
            }
        }
        return $this;
    }

    /**
     * Valida valor mínimo (numérico)
     *
     * @param  string $field
     * @param  float  $min
     * @param  string $message
     * @return self
     */
    public function minValue(string $field, float $min, string $message = null): self
    {
        if (isset($this->data[$field]) && is_numeric($this->data[$field])) {
            if ($this->data[$field] < $min) {
                $this->errors[$field][] = $message ?? "O valor deve ser no mínimo {$min}";
            }
        }
        return $this;
    }

    /**
     * Valida valor máximo (numérico)
     *
     * @param  string $field
     * @param  float  $max
     * @param  string $message
     * @return self
     */
    public function maxValue(string $field, float $max, string $message = null): self
    {
        if (isset($this->data[$field]) && is_numeric($this->data[$field])) {
            if ($this->data[$field] > $max) {
                $this->errors[$field][] = $message ?? "O valor deve ser no máximo {$max}";
            }
        }
        return $this;
    }

    /**
     * Valida se valor está em lista
     *
     * @param  string $field
     * @param  array  $values
     * @param  string $message
     * @return self
     */
    public function in(string $field, array $values, string $message = null): self
    {
        if (isset($this->data[$field])) {
            if (!in_array($this->data[$field], $values)) {
                $this->errors[$field][] = $message ?? "Valor inválido";
            }
        }
        return $this;
    }

    /**
     * Verifica se passou na validação
     *
     * @return bool
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Verifica se falhou na validação
     *
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Retorna erros
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Retorna primeiro erro de um campo
     *
     * @param  string $field
     * @return string|null
     */
    public function error(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Retorna todos os erros formatados
     *
     * @return string
     */
    public function errorString(): string
    {
        $messages = [];
        foreach ($this->errors as $fieldErrors) {
            $messages = array_merge($messages, $fieldErrors);
        }
        return implode(', ', $messages);
    }
}
