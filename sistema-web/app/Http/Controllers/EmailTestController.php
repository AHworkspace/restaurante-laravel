<?php

namespace App\Http\Controllers;

use App\Services\EmailService;
use App\Services\EmailCommandParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailTestController extends Controller
{
    private $emailService;
    private $commandParser;

    public function __construct(EmailService $emailService, EmailCommandParser $commandParser)
    {
        $this->emailService = $emailService;
        $this->commandParser = $commandParser;
    }

    /**
     * Mostrar la interfaz de pruebas
     */
    public function index()
    {
        return view('email-test.index');
    }

    /**
     * Probar conexión IMAP
     */
    public function testImapConnection(Request $request)
    {
        try {
            $connected = $this->emailService->connect();

            if ($connected) {
                $emails = $this->emailService->getUnreadEmails();
                $this->emailService->disconnect();

                return response()->json([
                    'success' => true,
                    'message' => 'Conexión IMAP exitosa',
                    'unread_emails' => count($emails)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo conectar al servidor IMAP. Verifique las credenciales.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error en test IMAP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar emails pendientes
     */
    public function processEmails(Request $request)
    {
        try {
            $output = [];
            $output[] = "=== PROCESANDO EMAILS ===";

            // Conectar al servidor
            if (!$this->emailService->connect()) {
                throw new \Exception('No se pudo conectar al servidor IMAP');
            }

            $output[] = "✅ Conectado al servidor IMAP";

            // Obtener emails no leídos
            $emails = $this->emailService->getUnreadEmails();
            $output[] = "📧 Emails encontrados: " . count($emails);

            $processed = 0;
            foreach ($emails as $email) {
                $output[] = "\n--- Procesando email de: {$email['from']} ---";
                $output[] = "Asunto: {$email['subject']}";

                // Extraer comando del cuerpo del email
                $command = $this->extractCommandFromEmail($email['body']);
                if ($command) {
                    $output[] = "Comando detectado: {$command}";

                    // Procesar comando
                    $response = $this->commandParser->processCommand(
                        $command,
                        $email['from'],
                        $email['message']
                    );

                    $output[] = "✅ Respuesta enviada";
                    $processed++;
                } else {
                    $output[] = "⚠️ No se detectó comando válido";
                }

                // Marcar como leído
                $this->emailService->markAsRead($email['message']);
            }

            $this->emailService->disconnect();

            $output[] = "\n=== RESUMEN ===";
            $output[] = "Emails procesados: {$processed}";
            $output[] = "Total emails: " . count($emails);

            return response()->json([
                'success' => true,
                'output' => implode("\n", $output)
            ]);

        } catch (\Exception $e) {
            Log::error('Error procesando emails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Probar comando manual
     */
    public function processTestCommand(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'command' => 'required|string'
            ]);

            $email = $request->email;
            $command = $request->command;

            // Procesar comando sin enviar email real
            $response = $this->commandParser->processCommand($command, $email, null);

            return response()->json([
                'success' => true,
                'response' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Error procesando comando de prueba: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Extraer comando del cuerpo del email
     */
    private function extractCommandFromEmail($body)
    {
        // Limpiar el cuerpo del email
        $body = strip_tags($body);
        $body = preg_replace('/\s+/', ' ', $body);
        $body = trim($body);

        // Buscar comandos conocidos
        $commands = [
            'CONSULTAR INSUMOS',
            'CONSULTAR STOCK',
            'AGREGAR MOVIMIENTO',
            'CONSULTAR MOVIMIENTOS',
            'CONSULTAR RECETAS',
            'CONSULTAR VENTAS',
            'CONSULTAR REPORTE',
            'CONSULTAR PREDICCIONES',
            'AYUDA'
        ];

        foreach ($commands as $command) {
            if (stripos($body, $command) !== false) {
                // Extraer la línea completa que contiene el comando
                $lines = explode("\n", $body);
                foreach ($lines as $line) {
                    if (stripos($line, $command) !== false) {
                        return trim($line);
                    }
                }
            }
        }

        return null;
    }
}
