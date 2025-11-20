<?php

namespace App\Services;

interface EmailServiceInterface
{
    public function connect(): bool;
    public function getUnreadEmails(): array;
    public function markAsRead($email): bool;
    public function sendResponse(string $to, string $subject, string $body, $originalMessage = null): bool;
    public function disconnect(): void;
}

