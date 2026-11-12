<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data  = $data;
        $this->rules = $rules;
    }

    public function validate(): array
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $errorCountBefore = count($this->errors[$field] ?? []);
                if ($this->applyRule($field, $value, $rule) === false) {
                    break;
                }
                if (count($this->errors[$field] ?? []) > $errorCountBefore) {
                    break;
                }
            }
        }
        return $this->errors;
    }

    private function applyRule(string $field, mixed $value, string $rule): bool
    {
        if (str_contains($rule, ':')) {
            [$ruleName, $param] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $param = null;
        }

        switch ($ruleName) {
            case 'nullable':
                if ($value === null || $value === '') {
                    return false;
                }
                break;
            case 'required':
                if ($value === null || $value === '') {
                    $this->errors[$field][] = "Trường {$field} là bắt buộc.";
                }
                break;
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "Email không hợp lệ.";
                }
                break;
            case 'min':
                if ($value !== null && $value !== '' && mb_strlen((string)$value) < (int)$param) {
                    $this->errors[$field][] = "Trường {$field} phải có ít nhất {$param} ký tự.";
                }
                break;
            case 'max':
                if ($value !== null && $value !== '' && mb_strlen((string)$value) > (int)$param) {
                    $this->errors[$field][] = "Trường {$field} không được vượt quá {$param} ký tự.";
                }
                break;
            case 'confirmed':
                $confirmed = $this->data[$field . '_confirmation'] ?? null;
                if ($value !== $confirmed) {
                    $this->errors[$field][] = "Xác nhận {$field} không khớp.";
                }
                break;
            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->errors[$field][] = "Trường {$field} phải là số.";
                }
                break;
            case 'in':
                $allowed = explode(',', $param ?? '');
                if ($value && !in_array($value, $allowed)) {
                    $this->errors[$field][] = "Giá trị {$field} không hợp lệ.";
                }
                break;
            case 'alpha_dash':
                if ($value !== null && $value !== '') {
                    if (!preg_match('/^[a-zA-Z0-9_-]+$/', (string)$value)) {
                        $this->errors[$field][] = "Trường {$field} chỉ được chứa chữ cái, chữ số, dấu gạch dưới và dấu gạch nối.";
                    }
                }
                break;
            case 'regex':
                if ($value !== null && $value !== '' && $param !== null) {
                    $pattern = $param;
                    if ($pattern !== '' && !in_array($pattern[0], ['/', '#', '~', '@'], true)) {
                        $pattern = '/' . str_replace('/', '\/', $pattern) . '/';
                    }
                    if (@preg_match($pattern, (string)$value) !== 1) {
                        $this->errors[$field][] = "Định dạng trường {$field} không hợp lệ.";
                    }
                }
                break;
            case 'tmu_student_id':
                if ($value !== null && $value !== '') {
                    if (!preg_match('/^\d{2}[dD]\d{6}$/', (string)$value)) {
                        $this->errors[$field][] = "Mã sinh viên TMU không hợp lệ (Ví dụ đúng: 21D123456).";
                    }
                }
                break;
            case 'tmu_student_email':
                if ($value !== null && $value !== '') {
                    if (!preg_match('/@mailsv\.tmu\.edu\.vn$/i', (string)$value)) {
                        $this->errors[$field][] = "Email phải có đuôi @mailsv.tmu.edu.vn";
                    }
                }
                break;
            case 'vietnamese_name':
                if ($value !== null && $value !== '') {
                    if (!preg_match('/^[\p{L}\s]+$/u', (string)$value)) {
                        $this->errors[$field][] = "Họ và tên chỉ được chứa chữ cái tiếng Việt";
                    }
                }
                break;
            case 'custom_username':
                if ($value !== null && $value !== '') {
                    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{2,49}$/', (string)$value)) {
                        $this->errors[$field][] = "Tên đăng nhập phải bắt đầu bằng chữ cái từ 3 ký tự và không chứa ký tự đặc biệt.";
                    }
                }
                break;
            case 'password_rules':
                if ($value !== null && $value !== '') {
                // Yêu cầu: Tối thiểu 8 ký tự, gồm ít nhất 1 chữ cái, 1 chữ số và 1 ký tự đặc biệt
                    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', (string)$value)) {
                        $this->errors[$field][] = "Mật khẩu phải có ít nhất 8 ký tự, bao gồm cả chữ cái, chữ số và ký tự đặc biệt.";
                    }
                }
                break;
        }

        return true;
    }
}

