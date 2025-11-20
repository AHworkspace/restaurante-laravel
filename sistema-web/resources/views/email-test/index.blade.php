@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>🧪 Panel de Pruebas - Sistema por Email</h4>
                </div>

                <div class="card-body">
                    <!-- Prueba de Conexión IMAP -->
                    <div class="mb-4">
                        <h5>1. Probar Conexión IMAP</h5>
                        <button id="testImapBtn" class="btn btn-primary">
                            Probar Conexión
                        </button>
                        <div id="imapResult" class="mt-2"></div>
                    </div>

                    <!-- Procesar Emails -->
                    <div class="mb-4">
                        <h5>2. Procesar Emails Pendientes</h5>
                        <button id="processEmailsBtn" class="btn btn-success">
                            Procesar Emails
                        </button>
                        <div id="processResult" class="mt-2"></div>
                    </div>

                    <!-- Prueba de Comandos -->
                    <div class="mb-4">
                        <h5>3. Probar Comando Manual</h5>
                        <form id="testCommandForm">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="email">Email de destino:</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="tu-email-prueba@gmail.com" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="command">Comando:</label>
                                <select class="form-control" id="command" name="command">
                                    <option value="AYUDA">AYUDA - Ver comandos disponibles</option>
                                    <option value="CONSULTAR INSUMOS">CONSULTAR INSUMOS</option>
                                    <option value="CONSULTAR STOCK harina">CONSULTAR STOCK harina</option>
                                    <option value="AGREGAR MOVIMIENTO entrada tomate 5 compra">AGREGAR MOVIMIENTO entrada tomate 5 compra</option>
                                    <option value="CONSULTAR MOVIMIENTOS">CONSULTAR MOVIMIENTOS</option>
                                    <option value="CONSULTAR RECETAS">CONSULTAR RECETAS</option>
                                    <option value="CONSULTAR VENTAS">CONSULTAR VENTAS</option>
                                    <option value="CONSULTAR REPORTE stock">CONSULTAR REPORTE stock</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                Probar Comando
                            </button>
                        </form>
                        <div id="commandResult" class="mt-2"></div>
                    </div>

                    <!-- Comandos Disponibles -->
                    <div class="mb-4">
                        <h5>4. Comandos Disponibles</h5>
                        <div class="alert alert-info">
                            <strong>Comandos que puedes usar por email:</strong><br>
                            • <code>CONSULTAR INSUMOS</code> - Lista todos los insumos<br>
                            • <code>CONSULTAR STOCK [nombre]</code> - Consulta stock de un insumo<br>
                            • <code>AGREGAR MOVIMIENTO [tipo] [insumo] [cantidad] [motivo]</code> - Registra movimiento<br>
                            • <code>CONSULTAR MOVIMIENTOS [fecha_inicio] [fecha_fin]</code> - Lista movimientos<br>
                            • <code>CONSULTAR RECETAS</code> - Lista recetas disponibles<br>
                            • <code>CONSULTAR VENTAS [fecha]</code> - Consulta ventas del día<br>
                            • <code>CONSULTAR REPORTE [tipo]</code> - Genera reporte<br>
                            • <code>AYUDA</code> - Muestra ayuda
                        </div>
                    </div>

                    <!-- Instrucciones -->
                    <div class="mb-4">
                        <h5>5. Instrucciones para Pruebas</h5>
                        <div class="alert alert-warning">
                            <strong>Para probar el sistema:</strong><br>
                            1. Configura tu email en el archivo .env<br>
                            2. Usa "Contraseñas de aplicación" de Gmail<br>
                            3. Envía emails a tu dirección configurada<br>
                            4. Ejecuta el comando: <code>php artisan email:process</code><br>
                            5. O usa el botón "Procesar Emails" arriba
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Probar conexión IMAP
    document.getElementById('testImapBtn').addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('imapResult');

        btn.disabled = true;
        btn.textContent = 'Probando...';

        fetch('/email-test/imap', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        ✅ ${data.message}<br>
                        Emails no leídos: ${data.unread_emails}
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        ❌ ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    ❌ Error: ${error.message}
                </div>
            `;
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Probar Conexión';
        });
    });

    // Procesar emails
    document.getElementById('processEmailsBtn').addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('processResult');

        btn.disabled = true;
        btn.textContent = 'Procesando...';

        fetch('/email-test/process', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        ✅ Emails procesados exitosamente<br>
                        <pre style="background: #f8f9fa; padding: 10px; margin-top: 10px;">${data.output}</pre>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        ❌ Error: ${data.error}
                    </div>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    ❌ Error: ${error.message}
                </div>
            `;
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Procesar Emails';
        });
    });

    // Probar comando manual
    document.getElementById('testCommandForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = this.querySelector('button[type="submit"]');
        const resultDiv = document.getElementById('commandResult');

        btn.disabled = true;
        btn.textContent = 'Probando...';

        const formData = new FormData(this);

        fetch('/email-test/command', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        ✅ Comando procesado exitosamente<br>
                        <pre style="background: #f8f9fa; padding: 10px; margin-top: 10px;">${data.response}</pre>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        ❌ Error: ${data.error}
                    </div>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    ❌ Error: ${error.message}
                </div>
            `;
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Probar Comando';
        });
    });
});
</script>
@endsection

