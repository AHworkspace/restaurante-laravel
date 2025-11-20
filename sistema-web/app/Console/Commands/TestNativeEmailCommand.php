<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NativeEmailService;
use App\Services\EmailCommandParser;
use Illuminate\Support\Facades\Log;

class TestNativeEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:native-test {--test-connection : Solo probar conexiones TCP/UDP}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar sistema de email usando protocolos nativos (SMTP/IMAP/Sockets)';

    private $emailService;
    private $commandParser;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->emailService = new NativeEmailService();
        $this->commandParser = new EmailCommandParser($this->emailService);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔌 PROTOCOLOS NATIVOS - SMTP/IMAP/Sockets');
        $this->line('==========================================');
        $this->line('');

        if ($this->option('test-connection')) {
            $this->testConnections();
        } else {
            $this->fullTest();
        }
    }

    private function testConnections()
    {
        $this->info('🌐 Probando conexiones TCP/UDP...');
        $this->line('');

        // Probar conexiones básicas
        $results = $this->emailService->testConnection();

        $this->line('📧 SMTP (Puerto 587): ' . $results['smtp']);
        $this->line('📥 IMAP (Puerto 993): ' . $results['imap']);
        $this->line('');

        // Probar con telnet simulado
        $this->info('🔍 Probando protocolos...');

        $this->testSMTPProtocol();
        $this->testIMAPProtocol();
    }

    private function testSMTPProtocol()
    {
        $this->line('');
        $this->info('📤 Probando protocolo SMTP...');

        try {
            $context = stream_context_create([
                'socket' => [
                    'tcp_nodelay' => true,
                ]
            ]);

            $socket = @stream_socket_client(
                "tcp://smtp.gmail.com:587",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$socket) {
                $this->error("❌ No se pudo conectar a SMTP: $errstr ($errno)");
                return;
            }

            // Leer respuesta inicial
            $response = fgets($socket);
            $this->line('✅ SMTP Response: ' . trim($response));

            // Enviar EHLO
            fwrite($socket, "EHLO test\r\n");
            $response = fgets($socket);
            $this->line('✅ EHLO Response: ' . trim($response));

            fclose($socket);
            $this->info('✅ Protocolo SMTP funcionando correctamente');

        } catch (\Exception $e) {
            $this->error('❌ Error probando SMTP: ' . $e->getMessage());
        }
    }

    private function testIMAPProtocol()
    {
        $this->line('');
        $this->info('📥 Probando protocolo IMAP...');

        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            $socket = stream_socket_client(
                "ssl://imap.gmail.com:993",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$socket) {
                $this->error("❌ No se pudo conectar a IMAP: $errstr ($errno)");
                return;
            }

            // Leer respuesta inicial
            $response = fgets($socket);
            $this->line('✅ IMAP Response: ' . trim($response));

            fclose($socket);
            $this->info('✅ Protocolo IMAP funcionando correctamente');

        } catch (\Exception $e) {
            $this->error('❌ Error probando IMAP: ' . $e->getMessage());
        }
    }

    private function fullTest()
    {
        $this->info('🚀 Prueba completa con protocolos nativos...');
        $this->line('');

        // Probar conexiones
        $this->testConnections();

        $this->line('');
        $this->info('🔌 Conectando con protocolos nativos...');

        try {
            // Conectar usando protocolos nativos
            if (!$this->emailService->connect()) {
                $this->error('❌ Error conectando con protocolos nativos');
                return 1;
            }

            $this->info('✅ Conectado exitosamente usando SMTP/IMAP nativos');

            // Obtener emails
            $emails = $this->emailService->getUnreadEmails();
            $this->info("📧 Emails encontrados: " . count($emails));

            // Procesar emails
            $processed = 0;
            foreach ($emails as $email) {
                $this->line("\n--- Procesando email de: {$email['from']} ---");
                $this->line("Asunto: {$email['subject']}");

                // Extraer comando
                $command = $this->extractCommand($email['body']);
                if ($command) {
                    $this->line("Comando: $command");

                    // Procesar comando
                    $response = $this->commandParser->processCommand(
                        $command,
                        $email['from'],
                        $email
                    );

                    $this->line("✅ Respuesta enviada usando SMTP nativo");
                    $processed++;
                }

                // Marcar como leído usando IMAP nativo
                $this->emailService->markAsRead($email);
            }

            $this->emailService->disconnect();

            $this->line("\n=== RESUMEN ===");
            $this->info("Emails procesados: $processed");
            $this->info("Protocolos utilizados: SMTP nativo + IMAP nativo + Sockets TCP/SSL");

            if ($processed > 0) {
                $this->info("🎉 Sistema funcionando con protocolos nativos!");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error('Error en prueba nativa: ' . $e->getMessage());
            return 1;
        }
    }

    private function extractCommand($body)
    {
        $body = strip_tags($body);
        $body = preg_replace('/\s+/', ' ', $body);
        $body = trim($body);

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
