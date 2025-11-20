<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailServiceMock;
use App\Services\EmailCommandParser;
use Illuminate\Support\Facades\Log;

class ProcessEmailsMockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {--interactive : Modo interactivo para probar comandos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar sistema de email SIN credenciales reales (modo prueba)';

    private $emailService;
    private $commandParser;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->emailService = new EmailServiceMock();
        $this->commandParser = new EmailCommandParser($this->emailService);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 MODO PRUEBA - Sistema de Email SIN credenciales reales');
        $this->line('');

        if ($this->option('interactive')) {
            $this->interactiveMode();
        } else {
            $this->automaticMode();
        }
    }

    private function interactiveMode()
    {
        $this->info('🎮 Modo Interactivo - Prueba comandos manualmente');
        $this->line('');

        while (true) {
            $command = $this->ask('Ingresa un comando (o "salir" para terminar)');

            if (strtolower($command) === 'salir') {
                break;
            }

            if (empty($command)) {
                continue;
            }

            $this->line('');
            $this->info("Procesando: $command");
            $this->line('');

            try {
                $response = $this->commandParser->processCommand(
                    $command,
                    'prueba@example.com',
                    null
                );

                $this->line("📧 Respuesta:");
                $this->line($response);

            } catch (\Exception $e) {
                $this->error("❌ Error: " . $e->getMessage());
            }

            $this->line('');
        }

        $this->info('👋 ¡Pruebas completadas!');
    }

    private function automaticMode()
    {
        $this->info('🔄 Procesando emails de prueba...');

        try {
            // Conectar (modo prueba)
            if (!$this->emailService->connect()) {
                $this->error('❌ Error conectando en modo prueba');
                return 1;
            }

            $this->info('✅ Conectado en modo PRUEBA');

            // Obtener emails simulados
            $emails = $this->emailService->getUnreadEmails();
            $this->info("📧 Emails de prueba encontrados: " . count($emails));

            if (empty($emails)) {
                $this->info('ℹ️ No hay emails de prueba para procesar');
                $this->emailService->disconnect();
                return 0;
            }

            $processed = 0;

            foreach ($emails as $email) {
                $this->line("\n--- Procesando email de: {$email['from']} ---");
                $this->line("Asunto: {$email['subject']}");
                $this->line("Comando: {$email['body']}");

                try {
                    // Procesar comando
                    $response = $this->commandParser->processCommand(
                        $email['body'],
                        $email['from'],
                        $email['message']
                    );

                    $this->line("✅ Respuesta simulada enviada");
                    $processed++;

                } catch (\Exception $e) {
                    $this->error("❌ Error procesando comando: " . $e->getMessage());
                }

                // Marcar como leído
                $this->emailService->markAsRead($email['message']);
            }

            $this->emailService->disconnect();

            $this->line("\n=== RESUMEN ===");
            $this->info("Emails procesados: {$processed}");
            $this->info("Total emails: " . count($emails));

            if ($processed > 0) {
                $this->info("🎉 Pruebas completadas exitosamente");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error general: " . $e->getMessage());
            Log::error('Error en comando email:test: ' . $e->getMessage());
            return 1;
        }
    }
}

