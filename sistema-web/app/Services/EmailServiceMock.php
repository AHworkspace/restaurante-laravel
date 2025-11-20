<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailServiceMock extends EmailService
{
    private $mockEmails = [];

    public function __construct()
    {
        $this->systemEmail = config('mail.from.address', 'test@example.com');
        // No inicializar cliente IMAP real
    }

    /**
     * Conectar al servidor IMAP (MODO PRUEBA)
     */
    public function connect(): bool
    {
        Log::info('Conectado en modo PRUEBA - Sin IMAP real');
        return true;
    }

    /**
     * Obtener emails no leídos (MODO PRUEBA)
     */
    public function getUnreadEmails(): array
    {
        // Emails de prueba simulados
        $this->mockEmails = [
            [
                'id' => 'test-1',
                'from' => 'docente-prueba@gmail.com',
                'subject' => 'Prueba de comando',
                'body' => 'AYUDA',
                'date' => now(),
                'message' => (object)['getFrom' => function() { return [(object)['mail' => 'docente-prueba@gmail.com']]; }]
            ],
            [
                'id' => 'test-2',
                'from' => 'docente-prueba@gmail.com',
                'subject' => 'Consultar stock',
                'body' => 'CONSULTAR STOCK harina',
                'date' => now(),
                'message' => (object)['getFrom' => function() { return [(object)['mail' => 'docente-prueba@gmail.com']]; }]
            ]
        ];

        Log::info('Emails de prueba simulados: ' . count($this->mockEmails));
        return $this->mockEmails;
    }

    /**
     * Marcar email como leído (MODO PRUEBA)
     */
    public function markAsRead($email): bool
    {
        Log::info('Email marcado como leído en modo PRUEBA');
        return true;
    }

    /**
     * Enviar respuesta por email (MODO PRUEBA)
     */
    public function sendResponse(string $to, string $subject, string $body, $originalMessage = null): bool
    {
        Log::info("Respuesta simulada enviada a: $to");
        Log::info("Asunto: $subject");
        Log::info("Contenido: " . substr($body, 0, 100) . "...");

        // En modo prueba, solo logueamos, no enviamos email real
        return true;
    }

    /**
     * Desconectar del servidor IMAP (MODO PRUEBA)
     */
    public function disconnect(): void
    {
        Log::info('Desconectado en modo PRUEBA');
    }
}
