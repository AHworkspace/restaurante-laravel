<?php

namespace App\Services;

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService implements EmailServiceInterface
{
    private $client;
    private $systemEmail;

    public function __construct()
    {
        $this->systemEmail = config('mail.from.address');
        // No inicializar automáticamente para evitar errores al cargar comandos
    }

    private function initializeImapClient()
    {
        $cm = new ClientManager();

        $this->client = $cm->make([
            'host' => config('mail.imap.host', 'imap.gmail.com'),
            'port' => config('mail.imap.port', 993),
            'encryption' => config('mail.imap.encryption', 'ssl'),
            'validate_cert' => true,
            'username' => config('mail.imap.username'),
            'password' => config('mail.imap.password'),
            'protocol' => 'imap'
        ]);
    }

    /**
     * Conectar al servidor IMAP
     */
    public function connect(): bool
    {
        try {
            // Inicializar cliente si no existe
            if (!$this->client) {
                $this->initializeImapClient();
            }

            $this->client->connect();
            Log::info('Conectado exitosamente al servidor IMAP');
            return true;
        } catch (\Exception $e) {
            Log::error('Error conectando a IMAP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener emails no leídos
     */
    public function getUnreadEmails(): array
    {
        try {
            // Inicializar cliente si no existe
            if (!$this->client) {
                $this->initializeImapClient();
            }

            $folder = $this->client->getFolder('INBOX');
            $messages = $folder->messages()->unseen()->get();

            $emails = [];
            foreach ($messages as $message) {
                $emails[] = [
                    'id' => $message->getUid(),
                    'from' => $message->getFrom()[0]->mail ?? '',
                    'subject' => $message->getSubject(),
                    'body' => $message->getTextBody(),
                    'date' => $message->getDate(),
                    'message' => $message
                ];
            }

            return $emails;
        } catch (\Exception $e) {
            Log::error('Error obteniendo emails: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Marcar email como leído
     */
    public function markAsRead($email): bool
    {
        try {
            $message = is_array($email) ? $email['message'] : $email;
            $message->setFlag('Seen');
            return true;
        } catch (\Exception $e) {
            Log::error('Error marcando email como leído: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar respuesta por email
     */
    public function sendResponse(string $to, string $subject, string $body, $originalMessage = null): bool
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject, $originalMessage) {
                $message->to($to)
                        ->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));

                if ($originalMessage) {
                    $message->replyTo($originalMessage->getFrom()[0]->mail ?? '');
                }
            });

            Log::info("Respuesta enviada a: $to");
            return true;
        } catch (\Exception $e) {
            Log::error('Error enviando respuesta: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Desconectar del servidor IMAP
     */
    public function disconnect(): void
    {
        try {
            $this->client->disconnect();
            Log::info('Desconectado del servidor IMAP');
        } catch (\Exception $e) {
            Log::error('Error desconectando de IMAP: ' . $e->getMessage());
        }
    }
}
