<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Services\EmailCommandParser;
use Illuminate\Support\Facades\Log;

class ProcessEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:process {--test : Solo mostrar qué emails se procesarían}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesar emails recibidos y ejecutar comandos del sistema';

    private $emailService;
    private $commandParser;

    /**
     * Create a new command instance.
     */
    public function __construct(EmailService $emailService, EmailCommandParser $commandParser)
    {
        parent::__construct();
        $this->emailService = $emailService;
        $this->commandParser = $commandParser;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando procesamiento de emails...');

        try {
            // Conectar al servidor IMAP
            if (!$this->emailService->connect()) {
                $this->error('❌ No se pudo conectar al servidor IMAP');
                return 1;
            }

            $this->info('✅ Conectado al servidor IMAP');

            // Obtener emails no leídos
            $emails = $this->emailService->getUnreadEmails();
            $this->info("📧 Emails encontrados: " . count($emails));

            if (empty($emails)) {
                $this->info('ℹ️ No hay emails nuevos para procesar');
                $this->emailService->disconnect();
                return 0;
            }

            $processed = 0;
            $errors = 0;

            foreach ($emails as $email) {
                $this->line("\n--- Procesando email de: {$email['from']} ---");
                $this->line("Asunto: {$email['subject']}");

                // Extraer comando del cuerpo del email
                $command = $this->extractCommandFromEmail($email['body']);

                if ($command) {
                    $this->line("Comando detectado: {$command}");

                    if ($this->option('test')) {
                        $this->line("🔍 [MODO TEST] Se procesaría este comando");
                        continue;
                    }

                    try {
                        // Procesar comando
                        $response = $this->commandParser->processCommand(
                            $command,
                            $email['from'],
                            $email['message']
                        );

                        $this->line("✅ Respuesta enviada");
                        $processed++;

                    } catch (\Exception $e) {
                        $this->error("❌ Error procesando comando: " . $e->getMessage());
                        $errors++;
                    }
                } else {
                    $this->warn("⚠️ No se detectó comando válido");
                }

                // Marcar como leído
                $this->emailService->markAsRead($email['message']);
            }

            $this->emailService->disconnect();

            $this->line("\n=== RESUMEN ===");
            $this->info("Emails procesados: {$processed}");
            $this->info("Errores: {$errors}");
            $this->info("Total emails: " . count($emails));

            if ($processed > 0) {
                $this->info("🎉 Procesamiento completado exitosamente");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error general: " . $e->getMessage());
            Log::error('Error en comando email:process: ' . $e->getMessage());
            return 1;
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
