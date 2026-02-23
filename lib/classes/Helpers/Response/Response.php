<?php

namespace Demo\Api\Classes\Helpers\Response;

class Response
{
    private string $status;
    private string $text;
    private string $errorCode;

    public function __construct(string $status = 'ok', string $text = '', string $errorCode = '')
    {
        $this->setStatus($status);
        $this->text = $text;
        $this->errorCode = $errorCode;
    }

    public function setStatus(string $status): void
    {
        $allowed = ['success', 'error', 'warning'];

        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException("Недопустимый статус: {$status}");
        }

        $this->status = $status;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function setErrorCode(string $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    public static function success(string $text = '', string $errorCode = ''): self
    {
        $response = new self('success', $text, $errorCode);
        return $response;
    }

    public static function error(string $text = '', string $errorCode = ''): self
    {
        return new self('error', $text, $errorCode);
    }

    public static function warning(string $text = '', string $errorCode = ''): self
    {
        return new self('warning', $text, $errorCode);
    }

    public function toArray(): array
    {
        return [
            'error_code' => $this->errorCode,
            'status' => $this->status,
            'text' => $this->text,
        ];
    }

    public function toJson(): string
    {
        return json_encode([
            'error_code' => $this->errorCode,
            'status' => $this->status,
            'text' => $this->text,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
