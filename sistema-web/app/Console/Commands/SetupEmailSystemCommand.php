<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupEmailSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configurar el sistema de email para procesamiento automático';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Configurando Sistema de Email para Inventarios...');
        $this->line('');

        // Verificar configuración
        $this->checkConfiguration();

        // Probar conexión
        $this->testConnection();

        // Configurar cron job
        $this->setupCronJob();

        // Mostrar instrucciones
        $this->showInstructions();

        $this->info('✅ Configuración completada!');
    }

    private function checkConfiguration()
    {
        $this->info('📋 Verificando configuración...');

        $required = [
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
            'IMAP_USERNAME' => env('IMAP_USERNAME'),
            'IMAP_PASSWORD' => env('IMAP_PASSWORD'),
        ];

        $missing = [];
        foreach ($required as $key => $value) {
            if (empty($value)) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            $this->error('❌ Faltan las siguientes configuraciones en el archivo .env:');
            foreach ($missing as $key) {
                $this->line("   - {$key}");
            }
            $this->line('');
            $this->warn('📝 Revisa el archivo EMAIL_CONFIG_EXAMPLE.txt para las instrucciones');
            return false;
        }

        $this->info('✅ Configuración básica encontrada');
        return true;
    }

    private function testConnection()
    {
        $this->info('🔌 Probando conexión IMAP...');

        try {
            $emailService = app(\App\Services\EmailService::class);

            if ($emailService->connect()) {
                $emails = $emailService->getUnreadEmails();
                $emailService->disconnect();

                $this->info("✅ Conexión exitosa - Emails no leídos: " . count($emails));
            } else {
                $this->error('❌ No se pudo conectar al servidor IMAP');
                $this->warn('Verifica las credenciales y la configuración de Gmail');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error de conexión: ' . $e->getMessage());
        }
    }

    private function setupCronJob()
    {
        $this->info('⏰ Configurando tareas programadas...');

        $cronCommand = '* * * * * cd ' . base_path() . ' && php artisan email:process >> /dev/null 2>&1';

        $this->line('');
        $this->warn('📝 Para procesar emails automáticamente, agrega esta línea a tu crontab:');
        $this->line('');
        $this->line("   {$cronCommand}");
        $this->line('');
        $this->info('💡 Esto procesará emails cada minuto automáticamente');
        $this->line('');
        $this->warn('🔧 Para editar crontab ejecuta: crontab -e');
    }

    private function showInstructions()
    {
        $this->info('📚 INSTRUCCIONES DE USO:');
        $this->line('');

        $this->line('1. 📧 Envía emails a tu dirección configurada con comandos como:');
        $this->line('   • "CONSULTAR INSUMOS"');
        $this->line('   • "CONSULTAR STOCK harina"');
        $this->line('   • "AYUDA"');
        $this->line('');

        $this->line('2. 🔄 Procesa emails manualmente:');
        $this->line('   php artisan email:process');
        $this->line('');

        $this->line('3. 🧪 Prueba comandos sin enviar emails:');
        $this->line('   php artisan email:process --test');
        $this->line('');

        $this->line('4. 🌐 Usa la interfaz web de pruebas:');
        $this->line('   http://tu-sitio.com/email-test');
        $this->line('');

        $this->line('5. 📊 Comandos disponibles:');
        $commands = [
            'CONSULTAR INSUMOS',
            'CONSULTAR STOCK [nombre]',
            'AGREGAR MOVIMIENTO [tipo] [insumo] [cantidad] [motivo]',
            'CONSULTAR MOVIMIENTOS [fecha_inicio] [fecha_fin]',
            'CONSULTAR RECETAS',
            'CONSULTAR VENTAS [fecha]',
            'CONSULTAR REPORTE [tipo]',
            'CONSULTAR PREDICCIONES',
            'AYUDA'
        ];

        foreach ($commands as $command) {
            $this->line("   • {$command}");
        }
    }
}
