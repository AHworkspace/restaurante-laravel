<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NativeSMTPService
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $socket;

    public function __construct($host = 'smtp.gmail.com', $port = 587)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = config('mail.imap.username');
        $this->password = config('mail.imap.password');
    }

    /**
     * Conectar al servidor SMTP usando socket TCP
     */
    public function connect()
    {
        try {
            // Crear socket TCP
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if (!$this->socket) {
                throw new \Exception('No se pudo crear el socket');
            }

            // Conectar al servidor SMTP
            $result = socket_connect($this->socket, $this->host, $this->port);
            if (!$result) {
                throw new \Exception('No se pudo conectar al servidor SMTP');
            }

            // Leer respuesta inicial del servidor
            $response = $this->readResponse();
            Log::info("SMTP Conectado: $response");

            // Iniciar conversación SMTP
            $this->sendCommand('EHLO ' . gethostname());
            $this->sendCommand('STARTTLS');

            // Habilitar cifrado TLS
            if (!socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \Exception('No se pudo habilitar TLS');
            }

            // Re-enviar EHLO después de TLS
            $this->sendCommand('EHLO ' . gethostname());

            // Autenticación
            $this->authenticate();

            Log::info('✅ Conectado exitosamente a SMTP');
            return true;

        } catch (\Exception $e) {
            Log::error('Error conectando SMTP: ' . $e->getMessage());
            $this->disconnect();
            return false;
        }
    }

    /**
     * Autenticación SMTP usando AUTH LOGIN
     */
    private function authenticate()
    {
        $this->sendCommand('AUTH LOGIN');
        $this->sendCommand(base64_encode($this->username));
        $this->sendCommand(base64_encode($this->password));
    }

    /**
     * Enviar email usando protocolo SMTP nativo
     */
    public function sendEmail($to, $subject, $body, $from = null)
    {
        try {
            if (!$this->socket) {
                throw new \Exception('No hay conexión SMTP activa');
            }

            $from = $from ?: $this->username;

            // Comenzar transacción de email
            $this->sendCommand("MAIL FROM:<$from>");
            $this->sendCommand("RCPT TO:<$to>");
            $this->sendCommand('DATA');

            // Construir headers del email
            $headers = $this->buildHeaders($from, $to, $subject);
            $emailContent = $headers . "\r\n" . $body . "\r\n.\r\n";

            // Enviar contenido del email
            socket_write($this->socket, $emailContent, strlen($emailContent));

            // Leer confirmación
            $response = $this->readResponse();

            if (strpos($response, '250') === 0) {
                Log::info("Email enviado exitosamente a: $to");
                return true;
            } else {
                throw new \Exception("Error enviando email: $response");
            }

        } catch (\Exception $e) {
            Log::error('Error enviando email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Construir headers del email según RFC 2822
     */
    private function buildHeaders($from, $to, $subject)
    {
        $headers = [];
        $headers[] = "From: $from";
        $headers[] = "To: $to";
        $headers[] = "Subject: $subject";
        $headers[] = "Date: " . date('r');
        $headers[] = "Message-ID: <" . uniqid() . "@" . gethostname() . ">";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/plain; charset=UTF-8";

        return implode("\r\n", $headers);
    }

    /**
     * Enviar comando SMTP
     */
    private function sendCommand($command)
    {
        $command .= "\r\n";
        socket_write($this->socket, $command, strlen($command));
        return $this->readResponse();
    }

    /**
     * Leer respuesta del servidor SMTP
     */
    private function readResponse()
    {
        $response = '';
        while (true) {
            $data = socket_read($this->socket, 1024);
            if ($data === false) break;

            $response .= $data;

            // SMTP termina respuestas con código seguido de espacio
            if (preg_match('/^\d{3} /', $response)) {
                break;
            }
        }

        return trim($response);
    }

    /**
     * Desconectar del servidor SMTP
     */
    public function disconnect()
    {
        if ($this->socket) {
            $this->sendCommand('QUIT');
            socket_close($this->socket);
            $this->socket = null;
            Log::info('Desconectado de SMTP');
        }
    }
}
