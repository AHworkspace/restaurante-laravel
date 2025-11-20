<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NativeEmailService implements EmailServiceInterface
{
    private $smtpService;
    private $imapService;
    private $systemEmail;

    public function __construct()
    {
        $this->smtpService = new NativeSMTPService();
        $this->imapService = new NativeIMAPService();
        $this->systemEmail = config('mail.from.address');
    }

    /**
     * Conectar a ambos servicios (SMTP e IMAP)
     */
    public function connect(): bool
    {
        try {
            // Conectar SMTP para enviar emails
            if (!$this->smtpService->connect()) {
                throw new \Exception('Error conectando SMTP');
            }

            // Conectar IMAP para recibir emails
            if (!$this->imapService->connect()) {
                throw new \Exception('Error conectando IMAP');
            }

            // Seleccionar buzón INBOX
            if (!$this->imapService->selectMailbox()) {
                throw new \Exception('Error seleccionando buzón');
            }

            Log::info('✅ Conectado exitosamente a SMTP e IMAP');
            return true;

        } catch (\Exception $e) {
            Log::error('Error conectando servicios de email: ' . $e->getMessage());
            $this->disconnect();
            return false;
        }
    }

    /**
     * Obtener emails no leídos usando IMAP nativo
     */
    public function getUnreadEmails(): array
    {
        try {
            $uids = $this->imapService->searchUnreadEmails();
            $emails = [];

            foreach ($uids as $uid) {
                $email = $this->imapService->fetchEmail($uid);
                if ($email) {
                    $emails[] = [
                        'id' => $uid,
                        'from' => $email['from'],
                        'subject' => $email['subject'],
                        'body' => $email['body'],
                        'date' => $email['date'],
                        'uid' => $uid
                    ];
                }
            }

            Log::info('Emails no leídos encontrados: ' . count($emails));
            return $emails;

        } catch (\Exception $e) {
            Log::error('Error obteniendo emails: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Marcar email como leído usando IMAP nativo
     */
    public function markAsRead($email): bool
    {
        try {
            $uid = is_array($email) ? $email['uid'] : $email;
            return $this->imapService->markAsRead($uid);
        } catch (\Exception $e) {
            Log::error('Error marcando email como leído: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar respuesta usando SMTP nativo
     */
    public function sendResponse(string $to, string $subject, string $body, $originalMessage = null): bool
    {
        try {
            // Construir respuesta
            $responseSubject = 'Re: ' . $subject;
            $responseBody = $this->formatResponseBody($body, $originalMessage);

            // Enviar usando SMTP nativo
            $success = $this->smtpService->sendEmail($to, $responseSubject, $responseBody);

            if ($success) {
                Log::info("Respuesta enviada exitosamente a: $to");
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('Error enviando respuesta: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Formatear cuerpo de respuesta
     */
    private function formatResponseBody($body, $originalMessage = null)
    {
        $response = "=== RESPUESTA DEL SISTEMA DE INVENTARIOS ===\n\n";
        $response .= $body;

        if ($originalMessage) {
            $response .= "\n\n--- Mensaje original ---\n";
            $response .= "De: " . ($originalMessage['from'] ?? 'Desconocido') . "\n";
            $response .= "Asunto: " . ($originalMessage['subject'] ?? 'Sin asunto') . "\n";
            $response .= "Fecha: " . ($originalMessage['date'] ?? 'Desconocida') . "\n";
        }

        $response .= "\n\n--- Sistema de Gestión de Inventarios ---";
        $response .= "\nRespuesta generada automáticamente el " . date('d/m/Y H:i:s');

        return $response;
    }

    /**
     * Desconectar de ambos servicios
     */
    public function disconnect(): void
    {
        try {
            $this->smtpService->disconnect();
            $this->imapService->disconnect();
            Log::info('Desconectado de todos los servicios de email');
        } catch (\Exception $e) {
            Log::error('Error desconectando: ' . $e->getMessage());
        }
    }

    /**
     * Probar conexión TCP/UDP usando streams
     */
    public function testConnection()
    {
        $results = [];

        // Probar conexión SMTP usando stream
        try {
            $context = stream_context_create([
                'socket' => [
                    'tcp_nodelay' => true,
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);

            $smtpSocket = @stream_socket_client(
                "tcp://smtp.gmail.com:587",
                $errno,
                $errstr,
                5,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if ($smtpSocket) {
                fclose($smtpSocket);
                $results['smtp'] = 'OK';
            } else {
                $results['smtp'] = "ERROR: $errstr ($errno)";
            }
        } catch (\Exception $e) {
            $results['smtp'] = 'ERROR: ' . $e->getMessage();
        }

        // Probar conexión IMAP usando SSL
        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            $imapSocket = @stream_socket_client(
                "ssl://imap.gmail.com:993",
                $errno,
                $errstr,
                5,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if ($imapSocket) {
                fclose($imapSocket);
                $results['imap'] = 'OK';
            } else {
                $results['imap'] = "ERROR: $errstr ($errno)";
            }
        } catch (\Exception $e) {
            $results['imap'] = 'ERROR: ' . $e->getMessage();
        }

        return $results;
    }
}
