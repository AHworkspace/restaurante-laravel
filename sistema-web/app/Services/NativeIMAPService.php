<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NativeIMAPService
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $socket;
    private $tagCounter = 1;

    public function __construct($host = 'imap.gmail.com', $port = 993)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = config('mail.imap.username');
        $this->password = config('mail.imap.password');
    }

    /**
     * Conectar al servidor IMAP usando socket SSL
     */
    public function connect()
    {
        try {
            // Crear contexto SSL
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            // Conectar usando SSL
            $this->socket = stream_socket_client(
                "ssl://{$this->host}:{$this->port}",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$this->socket) {
                throw new \Exception("Error conectando IMAP: $errstr ($errno)");
            }

            // Leer respuesta inicial
            $response = $this->readResponse();
            Log::info("IMAP Conectado: $response");

            // Autenticación LOGIN
            $this->sendCommand("LOGIN \"{$this->username}\" \"{$this->password}\"");

            Log::info('✅ Conectado exitosamente a IMAP');
            return true;

        } catch (\Exception $e) {
            Log::error('Error conectando IMAP: ' . $e->getMessage());
            $this->disconnect();
            return false;
        }
    }

    /**
     * Seleccionar buzón (INBOX)
     */
    public function selectMailbox($mailbox = 'INBOX')
    {
        $response = $this->sendCommand("SELECT \"$mailbox\"");
        return strpos($response, 'OK') !== false;
    }

    /**
     * Buscar emails no leídos usando comandos IMAP nativos
     */
    public function searchUnreadEmails()
    {
        try {
            // Buscar emails no leídos
            $response = $this->sendCommand('SEARCH UNSEEN');

            // Extraer UIDs de la respuesta
            if (preg_match('/\* SEARCH (.+)/', $response, $matches)) {
                $uids = trim($matches[1]);
                if (empty($uids)) {
                    return [];
                }

                return explode(' ', $uids);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Error buscando emails: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener email por UID usando FETCH
     */
    public function fetchEmail($uid)
    {
        try {
            // Obtener headers y cuerpo del email
            $response = $this->sendCommand("FETCH $uid (ENVELOPE BODY[TEXT])");

            // Parsear respuesta IMAP
            $email = $this->parseEmailResponse($response);
            $email['uid'] = $uid;

            return $email;

        } catch (\Exception $e) {
            Log::error("Error obteniendo email $uid: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Marcar email como leído usando STORE
     */
    public function markAsRead($uid)
    {
        try {
            $response = $this->sendCommand("STORE $uid +FLAGS (\\Seen)");
            return strpos($response, 'OK') !== false;
        } catch (\Exception $e) {
            Log::error("Error marcando email $uid como leído: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Parsear respuesta IMAP compleja
     */
    private function parseEmailResponse($response)
    {
        $email = [
            'from' => '',
            'subject' => '',
            'body' => '',
            'date' => ''
        ];

        // Extraer FROM del ENVELOPE
        if (preg_match('/ENVELOPE \("([^"]+)" "([^"]+)" "([^"]+)" "([^"]+)" "([^"]+)" "([^"]+)" "([^"]+)" "([^"]+)" "([^"]+)" "([^"]+)"\)/', $response, $matches)) {
            $email['from'] = $matches[2] . '@' . $matches[3];
            $email['subject'] = $matches[6];
            $email['date'] = $matches[8];
        }

        // Extraer BODY del email
        if (preg_match('/BODY\[TEXT\] \{(\d+)\}/', $response, $matches)) {
            $bodyLength = (int)$matches[1];
            // Leer el cuerpo del email
            $body = fread($this->socket, $bodyLength);
            $email['body'] = trim($body);
        }

        return $email;
    }

    /**
     * Enviar comando IMAP con tag único
     */
    private function sendCommand($command)
    {
        $tag = 'A' . $this->tagCounter++;
        $fullCommand = "$tag $command\r\n";

        fwrite($this->socket, $fullCommand);
        return $this->readResponse();
    }

    /**
     * Leer respuesta del servidor IMAP
     */
    private function readResponse()
    {
        $response = '';

        while (true) {
            $line = fgets($this->socket, 1024);
            if ($line === false) break;

            $response .= $line;

            // IMAP termina con línea que contiene el tag
            if (preg_match('/^[A-Z]\d+ (OK|NO|BAD|BYE)/', $line)) {
                break;
            }
        }

        return trim($response);
    }

    /**
     * Desconectar del servidor IMAP
     */
    public function disconnect()
    {
        if ($this->socket) {
            $this->sendCommand('LOGOUT');
            fclose($this->socket);
            $this->socket = null;
            Log::info('Desconectado de IMAP');
        }
    }
}
