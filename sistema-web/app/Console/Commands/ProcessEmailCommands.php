<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Services\EmailCommandParser;
use Illuminate\Support\Facades\Log;

class ProcessEmailCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa comandos recibidos por email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando procesamiento de emails...');

        $emailService = new EmailService();
        $commandParser = new EmailCommandParser($emailService);

        // Conectar al servidor IMAP
        if (!$emailService->connect()) {
            $this->error('No se pudo conectar al servidor IMAP');
            return 1;
        }

        // Obtener emails no leídos
        $emails = $emailService->getUnreadEmails();

        if (empty($emails)) {
            $this->info('No hay emails nuevos para procesar');
            $emailService->disconnect();
            return 0;
        }

        $this->info('Procesando ' . count($emails) . ' emails...');

        foreach ($emails as $email) {
            $this->info("Procesando email de: {$email['from']}");
            $this->info("Asunto: {$email['subject']}");

            // Extraer comando del cuerpo del email
            $command = $this->extractCommand($email['body']);

            if (empty($command)) {
                $this->warn('No se encontró comando válido en el email');
                continue;
            }

            $this->info("Comando detectado: {$command}");

            // Procesar comando
            $response = $commandParser->processCommand(
                $command,
                $email['from'],
                $email['message']
            );

            // Marcar como leído
            $emailService->markAsRead($email['message']);

            $this->info('Email procesado exitosamente');
        }

        $emailService->disconnect();
        $this->info('Procesamiento completado');

        return 0;
    }

    /**
     * Extraer comando del cuerpo del email
     */
    private function extractCommand($body)
    {
        // Limpiar el cuerpo del email
        $body = strip_tags($body);
        $body = preg_replace('/\s+/', ' ', $body);
        $body = trim($body);

        // Buscar comandos en líneas separadas
        $lines = explode("\n", $body);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Buscar patrones de comandos
            $commandPatterns = [
                '/^CONSULTAR\s+INSUMOS/i',
                '/^CONSULTAR\s+STOCK/i',
                '/^AGREGAR\s+MOVIMIENTO/i',
                '/^CONSULTAR\s+MOVIMIENTOS/i',
                '/^CONSULTAR\s+RECETAS/i',
                '/^CONSULTAR\s+VENTAS/i',
                '/^CONSULTAR\s+REPORTE/i',
                '/^CONSULTAR\s+PREDICCIONES/i',
                '/^AYUDA/i'
            ];

            foreach ($commandPatterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    return $line;
                }
            }
        }

        return '';
    }
}

