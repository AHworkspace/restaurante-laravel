<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\MovimientoInventario;
use App\Models\Receta;
use App\Models\Venta;
use App\Models\Categoria;
use App\Models\User;
use App\Models\PlanProduccion;
use App\Models\ReportePersonalizado;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class EmailCommandParser
{
    private $emailService;

    public function __construct(EmailServiceInterface $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Procesar comando recibido por email
     */
    public function processCommand($command, $fromEmail, $originalMessage = null)
    {
        $command = strtoupper(trim($command));
        $response = '';

        try {
            switch (true) {
                // ========================================
                // CU1 - GESTIÓN DE USUARIOS
                // ========================================
                case strpos($command, 'CREAR USUARIO') === 0:
                    $response = $this->crearUsuario($command);
                    break;

                case strpos($command, 'CONSULTAR USUARIOS') === 0:
                    $response = $this->consultarUsuarios();
                    break;

                case strpos($command, 'ACTUALIZAR USUARIO') === 0:
                    $response = $this->actualizarUsuario($command);
                    break;

                case strpos($command, 'ELIMINAR USUARIO') === 0:
                    $response = $this->eliminarUsuario($command);
                    break;

                case strpos($command, 'ASIGNAR ROL') === 0:
                    $response = $this->asignarRol($command);
                    break;

                // ========================================
                // CU2 - GESTIÓN DE INSUMOS
                // ========================================
                case strpos($command, 'CONSULTAR INSUMOS') === 0:
                    $filtros = trim(str_replace('CONSULTAR INSUMOS', '', $command));
                    $response = $this->consultarInsumosConFiltros($filtros);
                    break;

                case strpos($command, 'CONSULTAR STOCK DISPONIBLE') === 0:
                    $response = $this->consultarStockDisponible($command);
                    break;

                case strpos($command, 'ACTUALIZAR INSUMO') === 0:
                case strpos($command, 'EDITAR INSUMO') === 0:
                    $response = $this->actualizarInsumo($command);
                    break;

                case strpos($command, 'ELIMINAR INSUMO') === 0:
                    $response = $this->eliminarInsumo($command);
                    break;

                case strpos($command, 'CREAR INSUMO') === 0:
                    $response = $this->crearInsumo($command);
                    break;

                // ========================================
                // CU4 - GESTIÓN DE INVENTARIOS
                // ========================================
                case strpos($command, 'CREAR MOVIMIENTO') === 0:
                    $response = $this->crearMovimiento($command);
                    break;

                case strpos($command, 'CONSULTAR MOVIMIENTOS') === 0:
                    $filtros = trim(str_replace('CONSULTAR MOVIMIENTOS', '', $command));
                    $response = $this->consultarMovimientos($filtros);
                    break;

                case strpos($command, 'ACTUALIZAR MOVIMIENTO') === 0:
                    $response = $this->actualizarMovimiento($command);
                    break;

                case strpos($command, 'ELIMINAR MOVIMIENTO') === 0:
                    $response = $this->eliminarMovimiento($command);
                    break;

                // ========================================
                // CU3 - GESTIÓN DE RECETAS
                // ========================================
                case strpos($command, 'CONSULTAR LISTA RECETAS') === 0:
                    $response = $this->consultarListaRecetas();
                    break;

                case strpos($command, 'CONSULTAR RECETA') === 0:
                    $response = $this->consultarReceta($command);
                    break;

                case strpos($command, 'CREAR RECETA') === 0:
                    $response = $this->crearReceta($command);
                    break;

                case strpos($command, 'AÑADIR INDICACIONES') === 0:
                    $response = $this->añadirIndicaciones($command);
                    break;

                case strpos($command, 'AGREGAR INGREDIENTES') === 0:
                    $response = $this->agregarIngredientes($command);
                    break;

                case strpos($command, 'QUITAR INGREDIENTE') === 0:
                    $response = $this->quitarIngrediente($command);
                    break;

                case strpos($command, 'ACTUALIZAR RECETA') === 0:
                case strpos($command, 'EDITAR RECETA') === 0:
                    $response = $this->actualizarReceta($command);
                    break;

                case strpos($command, 'ELIMINAR RECETA') === 0:
                    $response = $this->eliminarReceta($command);
                    break;

                // ========================================
                // CU4 - GESTIÓN DE INVENTARIOS (Movimientos)
                // ========================================
                case strpos($command, 'CREAR MOVIMIENTO') === 0:
                    $response = $this->crearMovimiento($command);
                    break;

                case strpos($command, 'CONSULTAR MOVIMIENTOS') === 0:
                    $filtros = trim(str_replace('CONSULTAR MOVIMIENTOS', '', $command));
                    $response = $this->consultarMovimientos($filtros);
                    break;

                case strpos($command, 'ACTUALIZAR MOVIMIENTO') === 0:
                    $response = $this->actualizarMovimiento($command);
                    break;

                case strpos($command, 'ELIMINAR MOVIMIENTO') === 0:
                    $response = $this->eliminarMovimiento($command);
                    break;

                // ========================================
                // CU5 - GESTIÓN DE PRODUCCIÓN (Planes)
                // ========================================
                case strpos($command, 'CALCULAR PRODUCCION') === 0:
                    $response = $this->calcularProduccion($command);
                    break;

                case strpos($command, 'CREAR PLAN PRODUCCION') === 0:
                    $response = $this->crearPlanProduccion($command);
                    break;

                case strpos($command, 'CONSULTAR PLANES PRODUCCION') === 0:
                    $filtros = trim(str_replace('CONSULTAR PLANES PRODUCCION', '', $command));
                    $response = $this->consultarPlanesProduccion($filtros);
                    break;

                case strpos($command, 'CONSULTAR PLAN PRODUCCION') === 0:
                    $response = $this->consultarPlanProduccion($command);
                    break;

                case strpos($command, 'ACTUALIZAR PLAN PRODUCCION') === 0:
                    $response = $this->actualizarPlanProduccion($command);
                    break;

                case strpos($command, 'ELIMINAR PLAN PRODUCCION') === 0:
                    $response = $this->eliminarPlanProduccion($command);
                    break;

                // ========================================
                // CU6 - GESTIÓN DE VENTAS
                // ========================================
                case strpos($command, 'CREAR VENTA') === 0:
                    $response = $this->crearVenta($command);
                    break;

                case strpos($command, 'CONSULTAR VENTAS') === 0:
                    $filtros = trim(str_replace('CONSULTAR VENTAS', '', $command));
                    $response = $this->consultarVentas($filtros);
                    break;

                case strpos($command, 'ACTUALIZAR VENTA') === 0:
                    $response = $this->actualizarVenta($command);
                    break;

                case strpos($command, 'ELIMINAR VENTA') === 0:
                    $response = $this->eliminarVenta($command);
                    break;

                // ========================================
                // CU7 - COMPRAS/PAGOS
                // ========================================
                case strpos($command, 'CREAR COMPRA') === 0:
                    $response = $this->crearCompra($command);
                    break;

                case strpos($command, 'CONSULTAR COMPRAS') === 0:
                    $filtros = trim(str_replace('CONSULTAR COMPRAS', '', $command));
                    $response = $this->consultarCompras($filtros);
                    break;

                case strpos($command, 'ACTUALIZAR COMPRA') === 0:
                    $response = $this->actualizarCompra($command);
                    break;

                case strpos($command, 'ELIMINAR COMPRA') === 0:
                    $response = $this->eliminarCompra($command);
                    break;

                // ========================================
                // CU8 - REPORTES PERSONALIZADOS
                // ========================================
                case strpos($command, 'CREAR REPORTE') === 0:
                    $response = $this->crearReporte($command);
                    break;

                case strpos($command, 'CONSULTAR REPORTE') === 0:
                    // Si no tiene parámetros, lista todos; si tiene, muestra detalle específico
                    $rest = trim(str_replace('CONSULTAR REPORTE', '', $command));
                    if (empty($rest)) {
                        $response = $this->consultarReportes();
                    } else {
                        $response = $this->consultarReporte($command);
                    }
                    break;

                case strpos($command, 'GENERAR REPORTE') === 0:
                    $response = $this->generarReporte($command);
                    break;

                case strpos($command, 'ACTUALIZAR REPORTE') === 0:
                    $response = $this->actualizarReporte($command);
                    break;

                case strpos($command, 'ELIMINAR REPORTE') === 0:
                    $response = $this->eliminarReporte($command);
                    break;

                case strpos($command, 'CONSULTAR PREDICCIONES') === 0:
                    $response = $this->consultarPredicciones();
                    break;

                // ========================================
                // AYUDA
                // ========================================
                case strpos($command, 'AYUDA') === 0:
                    $response = $this->mostrarAyuda();
                    break;

                default:
                    $response = "Comando no reconocido. Escriba 'AYUDA' para ver los comandos disponibles.";
            }

            // Enviar respuesta
            $this->emailService->sendResponse(
                $fromEmail,
                'Respuesta del Sistema de Inventarios',
                $response,
                $originalMessage
            );

            return $response;

        } catch (\Exception $e) {
            Log::error('Error procesando comando: ' . $e->getMessage());
            $errorResponse = "Error procesando su solicitud: " . $e->getMessage();
            $this->emailService->sendResponse(
                $fromEmail,
                'Error - Sistema de Inventarios',
                $errorResponse,
                $originalMessage
            );
            return $errorResponse;
        }
    }

    // ========================================
    // CU1 - GESTIÓN DE USUARIOS - MÉTODOS
    // ========================================

    /**
     * Crear un nuevo usuario
     * Formato: CREAR USUARIO nombre apellidoP apellidoM email password
     */
    private function crearUsuario($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) < 7) {
            return "Formato incorrecto.\nUse: CREAR USUARIO [nombre] [apellido_paterno] [apellido_materno] [email] [password]\n\nEjemplo: CREAR USUARIO Juan Perez Lopez juan@email.com Pass1234";
        }

        $nombre = $parts[2];
        $apellidoPaterno = $parts[3];
        $apellidoMaterno = $parts[4];
        $email = $parts[5];
        $password = $parts[6];

        // Validar email único
        if (User::where('email', $email)->exists()) {
            return "❌ ERROR: El email {$email} ya está registrado.";
        }

        // Validar longitud de contraseña
        if (strlen($password) < 8) {
            return "❌ ERROR: La contraseña debe tener al menos 8 caracteres.";
        }

        try {
            $user = User::create([
                'nombre' => $nombre,
                'apellido_paterno' => $apellidoPaterno,
                'apellido_materno' => $apellidoMaterno,
                'email' => $email,
                'password' => bcrypt($password),
            ]);

            \App\Helpers\HistorialHelper::registrar(
                'Creó usuario vía email',
                'Usuario: ' . $user->email,
                'Usuarios'
            );

            $response = "✅ USUARIO CREADO EXITOSAMENTE\n\n";
            $response .= "ID: {$user->id}\n";
            $response .= "Nombre: {$user->nombre} {$user->apellido_paterno} {$user->apellido_materno}\n";
            $response .= "Email: {$user->email}\n";
            $response .= "Estado: Activo\n\n";
            $response .= "⚠️ IMPORTANTE: Asigne un rol al usuario usando:\n";
            $response .= "ASIGNAR ROL {$user->id} [admin|director|cocinero|cajero|ayudante_cocina|mesero]";

            return $response;

        } catch (\Exception $e) {
            return "❌ ERROR al crear usuario: " . $e->getMessage();
        }
    }

    /**
     * Consultar todos los usuarios
     */
    private function consultarUsuarios()
    {
        $users = User::with('roles')->get();

        $response = "========================================\n";
        $response .= "    LISTADO DE USUARIOS DEL SISTEMA\n";
        $response .= "========================================\n\n";

        if ($users->isEmpty()) {
            return $response . "No hay usuarios registrados.\n";
        }

        foreach ($users as $user) {
            $rol = $user->roles->first()->name ?? 'Sin rol';
            $response .= "📋 ID: {$user->id}\n";
            $response .= "   Nombre: {$user->nombre} {$user->apellido_paterno} {$user->apellido_materno}\n";
            $response .= "   Email: {$user->email}\n";
            $response .= "   Rol: " . ucfirst($rol) . "\n";
            $response .= "   ─────────────────────────────────────\n\n";
        }

        $response .= "Total de usuarios: " . $users->count() . "\n";

        return $response;
    }

    /**
     * Actualizar datos de un usuario
     * Formato: ACTUALIZAR USUARIO id campo valor
     */
    private function actualizarUsuario($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) < 5) {
            return "Formato incorrecto.\nUse: ACTUALIZAR USUARIO [id] [campo] [valor]\n\nCampos disponibles: nombre, apellido_paterno, apellido_materno, email, password\n\nEjemplos:\n• ACTUALIZAR USUARIO 5 nombre Carlos\n• ACTUALIZAR USUARIO 5 email nuevo@email.com\n• ACTUALIZAR USUARIO 5 password NuevaPass123";
        }

        $userId = intval($parts[2]);
        $campo = strtolower($parts[3]);
        $valor = implode(' ', array_slice($parts, 4));

        $user = User::find($userId);
        if (!$user) {
            return "❌ ERROR: Usuario con ID {$userId} no encontrado.";
        }

        $camposPermitidos = ['nombre', 'apellido_paterno', 'apellido_materno', 'email', 'password'];
        if (!in_array($campo, $camposPermitidos)) {
            return "❌ ERROR: Campo '{$campo}' no válido.\nCampos disponibles: " . implode(', ', $camposPermitidos);
        }

        try {
            if ($campo === 'email') {
                // Validar que el email no esté en uso por otro usuario
                if (User::where('email', $valor)->where('id', '!=', $userId)->exists()) {
                    return "❌ ERROR: El email {$valor} ya está en uso por otro usuario.";
                }
            }

            if ($campo === 'password') {
                if (strlen($valor) < 8) {
                    return "❌ ERROR: La contraseña debe tener al menos 8 caracteres.";
                }
                $user->password = bcrypt($valor);
                $campo = 'contraseña';
            } else {
                $user->$campo = $valor;
            }

            $user->save();

            \App\Helpers\HistorialHelper::registrar(
                'Actualizó usuario vía email',
                "Usuario ID: {$userId}, Campo: {$campo}",
                'Usuarios'
            );

            $response = "✅ USUARIO ACTUALIZADO EXITOSAMENTE\n\n";
            $response .= "ID: {$user->id}\n";
            $response .= "Nombre: {$user->nombre} {$user->apellido_paterno} {$user->apellido_materno}\n";
            $response .= "Email: {$user->email}\n";
            $response .= "Campo actualizado: {$campo}\n";

            return $response;

        } catch (\Exception $e) {
            return "❌ ERROR al actualizar usuario: " . $e->getMessage();
        }
    }

    /**
     * Eliminar un usuario
     * Formato: ELIMINAR USUARIO id
     */
    private function eliminarUsuario($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) < 3) {
            return "Formato incorrecto.\nUse: ELIMINAR USUARIO [id]\n\nEjemplo: ELIMINAR USUARIO 5";
        }

        $userId = intval($parts[2]);

        $user = User::find($userId);
        if (!$user) {
            return "❌ ERROR: Usuario con ID {$userId} no encontrado.";
        }

        $email = $user->email;
        $nombreCompleto = "{$user->nombre} {$user->apellido_paterno} {$user->apellido_materno}";

        try {
            $user->delete();

            \App\Helpers\HistorialHelper::registrar(
                'Eliminó usuario vía email',
                'Usuario: ' . $email,
                'Usuarios'
            );

            $response = "✅ USUARIO ELIMINADO EXITOSAMENTE\n\n";
            $response .= "ID eliminado: {$userId}\n";
            $response .= "Nombre: {$nombreCompleto}\n";
            $response .= "Email: {$email}\n";
            $response .= "\n⚠️ Esta acción no se puede deshacer.";

            return $response;

        } catch (\Exception $e) {
            return "❌ ERROR al eliminar usuario: " . $e->getMessage();
        }
    }

    /**
     * Asignar rol a un usuario
     * Formato: ASIGNAR ROL id rol
     */
    private function asignarRol($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) < 4) {
            return "Formato incorrecto.\nUse: ASIGNAR ROL [id] [rol]\n\nRoles disponibles: admin, director, cocinero, cajero, ayudante_cocina, mesero\n\nEjemplo: ASIGNAR ROL 5 cocinero";
        }

        $userId = intval($parts[2]);
        $rolName = strtolower($parts[3]);

        $user = User::find($userId);
        if (!$user) {
            return "❌ ERROR: Usuario con ID {$userId} no encontrado.";
        }

        $rolesDisponibles = ['admin', 'director', 'cocinero', 'cajero', 'ayudante_cocina', 'mesero'];
        if (!in_array($rolName, $rolesDisponibles)) {
            return "❌ ERROR: Rol '{$rolName}' no válido.\nRoles disponibles: " . implode(', ', $rolesDisponibles);
        }

        try {
            $user->syncRoles([$rolName]);

            \App\Helpers\HistorialHelper::registrar(
                'Asignó rol vía email',
                "Usuario: {$user->email}, Rol: {$rolName}",
                'Usuarios'
            );

            $response = "✅ ROL ASIGNADO EXITOSAMENTE\n\n";
            $response .= "Usuario: {$user->nombre} {$user->apellido_paterno} {$user->apellido_materno}\n";
            $response .= "Email: {$user->email}\n";
            $response .= "Nuevo rol: " . ucfirst($rolName) . "\n";

            return $response;

        } catch (\Exception $e) {
            return "❌ ERROR al asignar rol: " . $e->getMessage();
        }
    }

    // ========================================
    // CU2 - GESTIÓN DE INSUMOS - MÉTODOS
    // ========================================

    /**
     * Consultar insumos con filtros opcionales
     * Formato: CONSULTAR INSUMOS [filtro] [valor], [filtro] [valor]...
     */
    private function consultarInsumosConFiltros($filtros)
    {
        $query = Insumo::with(['categoria', 'unidad_medida']);

        $filtrosAplicados = [];

        // Si hay filtros, procesarlos
        if (!empty($filtros)) {
            // Separar por comas
            $filtrosArray = explode(',', $filtros);

            foreach ($filtrosArray as $filtro) {
                $filtro = trim($filtro);
                $partes = explode(' ', $filtro, 2);

                if (count($partes) >= 2) {
                    $tipoFiltro = strtolower($partes[0]);
                    $valorFiltro = trim($partes[1]);

                    if ($tipoFiltro === 'insumo') {
                        $query->where('nombre', 'like', "%{$valorFiltro}%");
                        $filtrosAplicados[] = "Nombre contiene: {$valorFiltro}";
                    } elseif ($tipoFiltro === 'categoria') {
                        $query->whereHas('categoria', function($q) use ($valorFiltro) {
                            $q->where('nombre', 'like', "%{$valorFiltro}%");
                        });
                        $filtrosAplicados[] = "Categoría: {$valorFiltro}";
                    } elseif ($tipoFiltro === 'stock_minimo') {
                        $query->where('stock_minimo', floatval($valorFiltro));
                        $filtrosAplicados[] = "Stock mínimo: {$valorFiltro}";
                    }
                }
            }
        }

        $insumos = $query->get();

        // Construir respuesta
        if (empty($filtrosAplicados)) {
            $response = "=== LISTADO COMPLETO DE INSUMOS ===\n\n";
        } else {
            $response = "=== INSUMOS FILTRADOS ===\n\n";
            $response .= "Filtros aplicados:\n";
            foreach ($filtrosAplicados as $filtro) {
                $response .= "• {$filtro}\n";
            }
            $response .= "\n";
        }

        if ($insumos->isEmpty()) {
            $response .= "No se encontraron insumos con los filtros especificados.\n";
            return $response;
        }

        foreach ($insumos as $insumo) {
            $stockActual = $insumo->getCantidadTotal();
            $response .= "• {$insumo->nombre}";
            if ($insumo->descripcion) {
                $response .= " ({$insumo->descripcion})";
            }
            $response .= "\n";
            $response .= "  Categoría: {$insumo->categoria->nombre}\n";
            $response .= "  Stock actual: {$stockActual} {$insumo->unidad_medida->nombre}\n";
            $response .= "  Stock mínimo: {$insumo->stock_minimo} {$insumo->unidad_medida->nombre}\n";
            $response .= "  Estado: " . ($stockActual <= $insumo->stock_minimo ? "⚠️ STOCK BAJO" : "✅ OK") . "\n\n";
        }

        $response .= "Total de insumos encontrados: " . $insumos->count();

        return $response;
    }

    /**
     * Consultar todos los insumos (método antiguo para compatibilidad)
     */
    private function consultarInsumos()
    {
        return $this->consultarInsumosConFiltros('');
    }

    /**
     * Consultar stock disponible de un insumo (simplificado)
     */
    private function consultarStockDisponible($command)
    {
        $nombreInsumo = trim(str_replace('CONSULTAR STOCK DISPONIBLE', '', $command));

        if (empty($nombreInsumo)) {
            return "Formato incorrecto. Use: CONSULTAR STOCK DISPONIBLE [nombre]\nEjemplo: CONSULTAR STOCK DISPONIBLE harina";
        }

        $insumo = Insumo::where('nombre', 'like', "%{$nombreInsumo}%")->first();

        if (!$insumo) {
            return "❌ No se encontró el insumo: {$nombreInsumo}";
        }

        $stockActual = $insumo->getCantidadTotal();

        return "Insumo: {$insumo->nombre}\n" .
               "Stock actual: {$stockActual} {$insumo->unidad_medida->nombre}\n" .
               "Stock mínimo: {$insumo->stock_minimo} {$insumo->unidad_medida->nombre}";
    }

    /**
     * Actualizar insumo
     * Formato: ACTUALIZAR INSUMO [nombre_insumo] [campo] [valor], [campo] [valor]...
     */
    private function actualizarInsumo($command)
    {
        // Extraer nombre del insumo y campos
        $rest = trim(str_replace(['ACTUALIZAR INSUMO', 'EDITAR INSUMO'], '', $command));

        if (empty($rest)) {
            return "Formato incorrecto. Use: ACTUALIZAR INSUMO [nombre_insumo] [campo] [valor], [campo] [valor]...\n" .
                   "(Campos: nombre, descripcion, stock_minimo, categoria, unidad)\n" .
                   "Ejemplo: ACTUALIZAR INSUMO tomate stock_minimo 15, descripcion tomate rojo fresco";
        }

        // Separar nombre del insumo del resto
        $parts = explode(' ', $rest, 2);
        if (count($parts) < 2) {
            return "Faltan parámetros. Use: ACTUALIZAR INSUMO [nombre_insumo] [campo] [valor], [campo] [valor]...\n" .
                   "(Campos: nombre, descripcion, stock_minimo, categoria, unidad)";
        }

        $nombreInsumo = $parts[0];
        $camposStr = $parts[1];

        // Buscar el insumo
        $insumo = Insumo::where('nombre', 'like', "%{$nombreInsumo}%")->first();

        if (!$insumo) {
            return "❌ Error: No se encontró el insumo '{$nombreInsumo}'.\n" .
                   "Use 'CONSULTAR INSUMOS' para ver insumos disponibles.";
        }

        // Procesar campos separados por comas
        $camposArray = explode(',', $camposStr);
        $camposActualizados = [];
        $updates = [];

        foreach ($camposArray as $campoStr) {
            $campoStr = trim($campoStr);
            $campoParts = explode(' ', $campoStr, 2);

            if (count($campoParts) < 2) {
                continue;
            }

            $campo = strtolower($campoParts[0]);
            $valor = trim($campoParts[1]);

            // Validar campo
            if (!in_array($campo, ['nombre', 'descripcion', 'stock_minimo', 'categoria', 'unidad'])) {
                return "❌ Error: Campo '{$campo}' no válido.\n" .
                       "(Campos: nombre, descripcion, stock_minimo, categoria, unidad)";
            }

            // Procesar cada tipo de campo
            if ($campo === 'nombre') {
                // Verificar nombre único
                $existe = Insumo::where('nombre', $valor)->where('id', '!=', $insumo->id)->exists();
                if ($existe) {
                    return "❌ Error: Ya existe otro insumo con el nombre '{$valor}'.";
                }
                $insumo->nombre = $valor;
                $camposActualizados[] = "nombre: {$valor}";

            } elseif ($campo === 'descripcion') {
                $insumo->descripcion = $valor;
                $camposActualizados[] = "descripcion: {$valor}";

            } elseif ($campo === 'stock_minimo') {
                $stockMinimo = floatval($valor);
                if ($stockMinimo < 0) {
                    return "❌ Error: El stock_minimo debe ser un número válido mayor o igual a 0.";
                }
                $insumo->stock_minimo = $stockMinimo;
                $camposActualizados[] = "stock_minimo: {$valor}";

            } elseif ($campo === 'categoria') {
                $categoria = Categoria::where('nombre', 'like', "%{$valor}%")->first();
                if (!$categoria) {
                    return "❌ Error: La categoría '{$valor}' no existe.";
                }
                $insumo->categoria_id = $categoria->id;
                $camposActualizados[] = "categoria: {$valor}";

            } elseif ($campo === 'unidad') {
                $unidad = \App\Models\UnidadMedida::where('nombre', 'like', "%{$valor}%")->first();
                if (!$unidad) {
                    return "❌ Error: La unidad '{$valor}' no existe.";
                }
                $insumo->unidad_medida_id = $unidad->id;
                $camposActualizados[] = "unidad: {$valor}";
            }
        }

        if (empty($camposActualizados)) {
            return "❌ Error: No se especificaron campos válidos para actualizar.\n" .
                   "(Campos: nombre, descripcion, stock_minimo, categoria, unidad)";
        }

        // Guardar cambios
        try {
            $insumo->save();

            \App\Helpers\HistorialHelper::registrar(
                'Actualizó insumo vía email',
                "Insumo: {$insumo->nombre}",
                'Insumos'
            );

            $response = "✅ Insumo actualizado exitosamente:\n";
            $response .= "• Nombre: {$insumo->nombre}\n";
            $response .= "• Campos actualizados:\n";
            foreach ($camposActualizados as $campo) {
                $response .= "  - {$campo}\n";
            }

            return $response;

        } catch (\Exception $e) {
            return "❌ Error al actualizar insumo: " . $e->getMessage();
        }
    }

    /**
     * Eliminar insumo
     * Formato: ELIMINAR INSUMO [nombre]
     */
    private function eliminarInsumo($command)
    {
        $nombreInsumo = trim(str_replace('ELIMINAR INSUMO', '', $command));

        if (empty($nombreInsumo)) {
            return "Formato incorrecto. Use: ELIMINAR INSUMO [nombre]\nEjemplo: ELIMINAR INSUMO harina";
        }

        $insumo = Insumo::where('nombre', 'like', "%{$nombreInsumo}%")->first();

        if (!$insumo) {
            return "❌ No se encontró el insumo: {$nombreInsumo}";
        }

        $nombre = $insumo->nombre;

        try {
            // Eliminar movimientos relacionados
            foreach ($insumo->movimiento_inventarios as $movimiento) {
                $movimiento->delete();
            }
            // Eliminar relaciones en recetas
            $insumo->recetas()->detach();

            $insumo->delete();

            \App\Helpers\HistorialHelper::registrar(
                'Eliminó insumo vía email',
                "Insumo: {$nombre}",
                'Insumos'
            );

            return "✅ Insumo '{$nombre}' eliminado exitosamente.";
        } catch (\Exception $e) {
            return "❌ Error al eliminar insumo: " . $e->getMessage();
        }
    }

    /**
     * Crear insumo
     * Formato: CREAR INSUMO [nombre] [descripcion] [stock_min] [categoria] [unidad]
     */
    private function crearInsumo($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 6) {
            return "Formato incorrecto. Use: CREAR INSUMO [nombre] [descripcion] [stock_min] [categoria] [unidad]\n" .
                   "Ejemplo: CREAR INSUMO tomate fresco 10 verduras kg";
        }

        $nombre = $parts[2];
        $descripcion = $parts[3];
        $stockMin = floatval($parts[4]);
        $categoriaNombre = $parts[5];
        $unidadNombre = $parts[6];

        // Validar stock mínimo
        if ($stockMin < 0) {
            return "❌ Error: El stock mínimo debe ser mayor o igual a 0.";
        }

        // Buscar categoría
        $categoria = Categoria::where('nombre', 'like', "%{$categoriaNombre}%")->first();
        if (!$categoria) {
            return "❌ Error: La categoría '{$categoriaNombre}' no existe.";
        }

        // Buscar unidad
        $unidad = \App\Models\UnidadMedida::where('nombre', 'like', "%{$unidadNombre}%")->first();
        if (!$unidad) {
            return "❌ Error: La unidad '{$unidadNombre}' no existe.";
        }

        // Verificar si el insumo ya existe
        if (Insumo::where('nombre', $nombre)->exists()) {
            return "❌ Error: Ya existe un insumo con el nombre '{$nombre}'.";
        }

        try {
            $insumo = Insumo::create([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'stock_minimo' => $stockMin,
                'categoria_id' => $categoria->id,
                'unidad_medida_id' => $unidad->id,
                'restaurante_id' => 1, // Asumiendo restaurante por defecto
            ]);

            \App\Helpers\HistorialHelper::registrar(
                'Creó insumo vía email',
                "Insumo: {$insumo->nombre}",
                'Insumos'
            );

            return "✅ Insumo creado exitosamente:\n" .
                   "• Nombre: {$insumo->nombre}\n" .
                   "• Descripción: {$insumo->descripcion}\n" .
                   "• Stock mínimo: {$insumo->stock_minimo} {$unidad->nombre}\n" .
                   "• Categoría: {$categoria->nombre}\n" .
                   "• Unidad: {$unidad->nombre}";

        } catch (\Exception $e) {
            return "❌ Error al crear insumo: " . $e->getMessage();
        }
    }

    /**
     * Consultar stock de un insumo específico (método antiguo con más detalles)
     */
    private function consultarStock($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) < 3) {
            return "Formato incorrecto. Use: CONSULTAR STOCK [nombre_insumo]";
        }

        $nombreInsumo = implode(' ', array_slice($parts, 2));
        $insumo = Insumo::where('nombre', 'like', "%{$nombreInsumo}%")->first();

        if (!$insumo) {
            return "No se encontró el insumo: {$nombreInsumo}";
        }

        $stockActual = $insumo->getCantidadTotal();

        $response = "=== STOCK DE {$insumo->nombre} ===\n\n";
        $response .= "Stock actual: {$stockActual} {$insumo->unidad_medida->nombre}\n";
        $response .= "Stock mínimo: {$insumo->stock_minimo} {$insumo->unidad_medida->nombre}\n";
        $response .= "Estado: " . ($stockActual <= $insumo->stock_minimo ? "⚠️ STOCK BAJO" : "✅ OK") . "\n";

        return $response;
    }

    // ========================================
    // CU4 - GESTIÓN DE INVENTARIOS - MÉTODOS
    // ========================================

    /**
     * Crear movimiento de inventario
     * Formato: CREAR MOVIMIENTO [insumo] [tipo] [cantidad] [motivo]
     */
    private function crearMovimiento($command)
    {
        $parts = explode(' ', $command);
        if (count($parts) < 6) {
            return "Formato incorrecto.\nUse: CREAR MOVIMIENTO [insumo] [tipo] [cantidad] [motivo]\n\n" .
                   "Tipos: entrada, salida\n" .
                   "Ejemplo: CREAR MOVIMIENTO tomate entrada 5 compra";
        }

        $nombreInsumo = $parts[2];
        $tipo = strtolower($parts[3]);
        $cantidad = floatval($parts[4]);
        $motivo = implode(' ', array_slice($parts, 5));

        if (!in_array($tipo, ['entrada', 'salida'])) {
            return "Tipo debe ser 'entrada' o 'salida'";
        }

        $insumo = Insumo::where('nombre', 'like', "%{$nombreInsumo}%")->first();
        if (!$insumo) {
            return "No se encontró el insumo: {$nombreInsumo}";
        }

        // Validar stock para salidas
        if ($tipo === 'salida') {
            $stockDisponible = $insumo->getCantidadTotal();
            if ($cantidad > $stockDisponible) {
                return "No hay suficiente stock. Disponible: {$stockDisponible} {$insumo->unidad_medida->nombre}";
            }
        }

        // Crear movimiento
        $movimiento = MovimientoInventario::create([
            'cantidad' => $cantidad,
            'tipo' => $tipo,
            'motivo' => $motivo,
            'insumo_id' => $insumo->id,
        ]);

        // Registrar en historial
        \App\Helpers\HistorialHelper::registrar(
            'Creó movimiento vía email',
            "Insumo: {$insumo->nombre}, Tipo: {$tipo}, Cantidad: {$cantidad}, Motivo: {$motivo}",
            'Movimientos'
        );

        $stockActual = $insumo->getCantidadTotal();
        $simbolo = $tipo === 'entrada' ? '+' : '-';

        $response = "✅ MOVIMIENTO REGISTRADO EXITOSAMENTE\n\n";
        $response .= "ID: {$movimiento->id}\n";
        $response .= "Insumo: {$insumo->nombre}\n";
        $response .= "Tipo: " . strtoupper($tipo) . "\n";
        $response .= "Cantidad: {$simbolo}{$cantidad} {$insumo->unidad_medida->nombre}\n";
        $response .= "Motivo: {$motivo}\n";
        $response .= "Stock actual: {$stockActual} {$insumo->unidad_medida->nombre}\n";

        if ($stockActual <= $insumo->stock_minimo) {
            $response .= "\n⚠️ ALERTA: Stock por debajo del mínimo ({$insumo->stock_minimo})";
        }

        return $response;
    }

    /**
     * Consultar movimientos con filtros opcionales
     * Formato: CONSULTAR MOVIMIENTOS [filtro] [valor], [filtro] [valor]...
     * Filtros: tipo, insumo, fecha, fecha_desde, fecha_hasta, motivo
     */
    private function consultarMovimientos($filtros)
    {
        $query = MovimientoInventario::with(['insumo.unidad_medida']);

        // Variables para mostrar filtros aplicados
        $filtrosAplicados = [];

        // Si hay filtros, procesarlos
        if (!empty($filtros)) {
            // Separar por comas
            $filtrosArray = explode(',', $filtros);

            foreach ($filtrosArray as $filtro) {
                $filtro = trim($filtro);
                $partes = explode(' ', $filtro, 2);

                if (count($partes) >= 2) {
                    $tipoFiltro = strtolower(trim($partes[0]));
                    $valorFiltro = trim($partes[1]);

                    switch ($tipoFiltro) {
                        case 'tipo':
                            $valorFiltro = strtolower($valorFiltro);
                            if (in_array($valorFiltro, ['entrada', 'salida'])) {
                                $query->where('tipo', $valorFiltro);
                                $filtrosAplicados[] = "Tipo: " . strtoupper($valorFiltro);
                            }
                            break;

                        case 'insumo':
                            $query->whereHas('insumo', function($q) use ($valorFiltro) {
                                $q->where('nombre', 'like', "%{$valorFiltro}%");
                            });
                            $filtrosAplicados[] = "Insumo: {$valorFiltro}";
                            break;

                        case 'fecha':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', $fechaNormalizada);
                                $filtrosAplicados[] = "Fecha: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida silenciosamente
                            }
                            break;

                        case 'fecha_desde':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', '>=', $fechaNormalizada);
                                $filtrosAplicados[] = "Desde: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida silenciosamente
                            }
                            break;

                        case 'fecha_hasta':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', '<=', $fechaNormalizada);
                                $filtrosAplicados[] = "Hasta: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida silenciosamente
                            }
                            break;

                        case 'motivo':
                            $query->where('motivo', 'like', "%{$valorFiltro}%");
                            $filtrosAplicados[] = "Motivo: {$valorFiltro}";
                            break;
                    }
                }
            }
        }

        // Ejecutar consulta
        $movimientos = $query->orderBy('created_at', 'desc')->get();

        if ($movimientos->isEmpty()) {
            return "No se encontraron movimientos" . (empty($filtrosAplicados) ? "." : " con los filtros especificados.");
        }

        // Título según si hay filtros o no
        if (empty($filtrosAplicados)) {
            $response = "=== LISTADO COMPLETO DE MOVIMIENTOS ===\n\n";
        } else {
            $response = "=== MOVIMIENTOS FILTRADOS ===\n\n";
            $response .= "Filtros aplicados:\n";
            foreach ($filtrosAplicados as $filtro) {
                $response .= "• {$filtro}\n";
            }
            $response .= "\n";
        }

        $count = 0;
        foreach ($movimientos as $movimiento) {
            $count++;
            $simbolo = $movimiento->tipo === 'entrada' ? '+' : '-';
            $response .= "{$count}. [ID:{$movimiento->id}] {$movimiento->insumo->nombre}: {$simbolo}{$movimiento->cantidad} {$movimiento->insumo->unidad_medida->nombre}\n";
            $response .= "   Tipo: " . strtoupper($movimiento->tipo) . " | Motivo: {$movimiento->motivo}\n";
            $response .= "   Fecha: {$movimiento->created_at->format('d/m/Y H:i')}\n\n";
        }

        $response .= "Total movimientos encontrados: {$count}";

        return $response;
    }

    /**
     * Actualizar movimiento de inventario
     * Formato: ACTUALIZAR MOVIMIENTO [nombre_insumo] [id] [campo] [valor], [campo] [valor]...
     */
    private function actualizarMovimiento($command)
    {
        // Extraer nombre de insumo, ID y campos a actualizar
        $parts = explode(' ', $command, 5);

        if (count($parts) < 5) {
            return "Formato incorrecto.\nUse: ACTUALIZAR MOVIMIENTO [nombre_insumo] [id] [campo] [valor], [campo] [valor]...\n\n" .
                   "Campos disponibles: tipo, cantidad, motivo, fecha\n" .
                   "Ejemplo: ACTUALIZAR MOVIMIENTO tomate 10 cantidad 25, motivo compra urgente";
        }

        $nombreInsumo = $parts[2];
        $movimientoId = intval($parts[3]);
        $cambiosTexto = $parts[4];

        // Buscar el insumo
        $insumo = Insumo::where('nombre', 'like', "%{$nombreInsumo}%")->first();

        if (!$insumo) {
            return "❌ ERROR: No se encontró el insumo: {$nombreInsumo}";
        }

        // Buscar el movimiento específico y verificar que pertenezca al insumo
        $movimiento = MovimientoInventario::with(['insumo.unidad_medida'])
            ->where('id', $movimientoId)
            ->where('insumo_id', $insumo->id)
            ->first();

        if (!$movimiento) {
            return "❌ ERROR: No se encontró el movimiento ID {$movimientoId} para el insumo {$nombreInsumo}.\n" .
                   "Use: CONSULTAR MOVIMIENTOS insumo {$nombreInsumo}\npara ver los IDs disponibles.";
        }

        // Procesar campos a actualizar (formato: campo valor, campo valor, ...)
        $cambiosProcesados = [];
        $actualizaciones = [];

        // Separar por comas
        $pares = explode(',', $cambiosTexto);

        foreach ($pares as $par) {
            $par = trim($par);
            $partes = explode(' ', $par, 2);

            if (count($partes) >= 2) {
                $campo = strtolower(trim($partes[0]));
                $valor = trim($partes[1]);

                switch ($campo) {
                    case 'tipo':
                        $valor = strtolower($valor);
                        if (!in_array($valor, ['entrada', 'salida'])) {
                            return "❌ ERROR: Tipo inválido. Use: entrada o salida";
                        }
                        $actualizaciones['tipo'] = $valor;
                        $cambiosProcesados[] = "tipo: {$valor}";
                        break;

                    case 'cantidad':
                        $cantidad = floatval($valor);
                        if ($cantidad <= 0) {
                            return "❌ ERROR: La cantidad debe ser mayor a 0";
                        }
                        $actualizaciones['cantidad'] = $cantidad;
                        $cambiosProcesados[] = "cantidad: {$cantidad}";
                        break;

                    case 'motivo':
                        $actualizaciones['motivo'] = $valor;
                        $cambiosProcesados[] = "motivo: {$valor}";
                        break;

                    case 'fecha':
                        try {
                            $fechaParsed = Carbon::parse($valor);
                            $actualizaciones['created_at'] = $fechaParsed;
                            $cambiosProcesados[] = "fecha: {$fechaParsed->format('Y-m-d H:i')}";
                        } catch (\Exception $e) {
                            return "❌ ERROR: Formato de fecha inválido. Use: YYYY-MM-DD";
                        }
                        break;

                    default:
                        return "❌ ERROR: Campo '{$campo}' no válido.\nCampos disponibles: tipo, cantidad, motivo, fecha";
                }
            }
        }

        if (empty($actualizaciones)) {
            return "❌ ERROR: No se especificaron campos válidos para actualizar.";
        }

        // Actualizar el movimiento
        $movimiento->update($actualizaciones);

        // Registrar en historial
        \App\Helpers\HistorialHelper::registrar(
            'Actualizó movimiento vía email',
            "ID: {$movimientoId}, Cambios: " . implode(', ', $cambiosProcesados),
            'Movimientos'
        );

        $stockActual = $movimiento->insumo->getCantidadTotal();
        $simbolo = $movimiento->tipo === 'entrada' ? '+' : '-';

        $response = "✅ MOVIMIENTO ACTUALIZADO EXITOSAMENTE\n\n";
        $response .= "ID: {$movimiento->id}\n";
        $response .= "Insumo: {$movimiento->insumo->nombre}\n";
        $response .= "Tipo: " . strtoupper($movimiento->tipo) . "\n";
        $response .= "Cantidad: {$simbolo}{$movimiento->cantidad} {$movimiento->insumo->unidad_medida->nombre}\n";
        $response .= "Motivo: {$movimiento->motivo}\n";
        $response .= "Fecha: {$movimiento->created_at->format('d/m/Y H:i')}\n";
        $response .= "Stock actual del insumo: {$stockActual} {$movimiento->insumo->unidad_medida->nombre}\n\n";
        $response .= "Campos actualizados: " . implode(', ', $cambiosProcesados);

        return $response;
    }

    /**
     * Eliminar movimiento de inventario
     * Formato: ELIMINAR MOVIMIENTO [nombre_insumo] [id]
     */
    private function eliminarMovimiento($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 4) {
            return "Formato incorrecto.\nUse: ELIMINAR MOVIMIENTO [nombre_insumo] [id]\n\n" .
                   "Ejemplo: ELIMINAR MOVIMIENTO tomate 10";
        }

        $nombreInsumo = $parts[2];
        $movimientoId = intval($parts[3]);

        // Buscar el insumo
        $insumo = Insumo::where('nombre', 'like', "%{$nombreInsumo}%")->first();

        if (!$insumo) {
            return "❌ ERROR: No se encontró el insumo: {$nombreInsumo}";
        }

        // Buscar el movimiento específico y verificar que pertenezca al insumo
        $movimiento = MovimientoInventario::with(['insumo.unidad_medida'])
            ->where('id', $movimientoId)
            ->where('insumo_id', $insumo->id)
            ->first();

        if (!$movimiento) {
            return "❌ ERROR: No se encontró el movimiento ID {$movimientoId} para el insumo {$nombreInsumo}.\n" .
                   "Use: CONSULTAR MOVIMIENTOS insumo {$nombreInsumo}\npara ver los IDs disponibles.";
        }

        // Guardar datos para el mensaje de confirmación
        $insumoNombre = $movimiento->insumo->nombre;
        $tipo = $movimiento->tipo;
        $cantidad = $movimiento->cantidad;
        $motivo = $movimiento->motivo;
        $fecha = $movimiento->created_at->format('d/m/Y H:i');

        // Eliminar el movimiento
        $movimiento->delete();

        // Registrar en historial
        \App\Helpers\HistorialHelper::registrar(
            'Eliminó movimiento vía email',
            "ID: {$movimiento->id}, Insumo: {$insumoNombre}, Tipo: {$tipo}, Cantidad: {$cantidad}",
            'Movimientos'
        );

        $response = "✅ MOVIMIENTO ELIMINADO EXITOSAMENTE\n\n";
        $response .= "ID eliminado: {$movimiento->id}\n";
        $response .= "Insumo: {$insumoNombre}\n";
        $response .= "Tipo: " . strtoupper($tipo) . "\n";
        $response .= "Cantidad: {$cantidad}\n";
        $response .= "Motivo: {$motivo}\n";
        $response .= "Fecha: {$fecha}\n\n";
        $response .= "⚠️ El stock del insumo ha sido recalculado automáticamente.";

        return $response;
    }

    /**
     * Normaliza una fecha al formato SQL estándar (Y-m-d)
     * Acepta formatos: 2025-6-1, 2025-06-01, etc.
     */
    private function normalizarFecha($fecha)
    {
        // Eliminar espacios
        $fecha = trim($fecha);

        // Separar por guiones
        $partes = explode('-', $fecha);
        if (count($partes) !== 3) {
            throw new \Exception("Formato de fecha inválido");
        }

        $año = intval($partes[0]);
        $mes = intval($partes[1]);
        $dia = intval($partes[2]);

        // Validar rangos
        if ($año < 1900 || $año > 2100) {
            throw new \Exception("Año fuera de rango");
        }
        if ($mes < 1 || $mes > 12) {
            throw new \Exception("Mes fuera de rango");
        }
        if ($dia < 1 || $dia > 31) {
            throw new \Exception("Día fuera de rango");
        }

        // Formatear con ceros a la izquierda
        return sprintf("%04d-%02d-%02d", $año, $mes, $dia);
    }

    // ========================================
    // CU3 - GESTIÓN DE RECETAS - MÉTODOS
    // ========================================

    /**
     * Consultar lista de recetas
     * Formato: CONSULTAR LISTA RECETAS
     */
    private function consultarListaRecetas()
    {
        $recetas = Receta::orderBy('nombre')->get();

        $response = "=== LISTADO COMPLETO DE RECETAS ===\n\n";

        $count = 0;
        foreach ($recetas as $receta) {
            $count++;
            $response .= "{$count}. {$receta->nombre}\n";
            $response .= "   • Precio: Bs. {$receta->precio}\n";
            $response .= "   • Tiempo: {$receta->tiempo_preparacion} minutos\n\n";
        }

        if ($count == 0) {
            $response .= "No hay recetas registradas.\n";
        } else {
            $response .= "Total de recetas: {$count}";
        }

        return $response;
    }

    /**
     * Consultar detalle de una receta
     * Formato: CONSULTAR RECETA [nombre]
     */
    private function consultarReceta($command)
    {
        $nombreReceta = trim(str_replace('CONSULTAR RECETA', '', $command));

        if (empty($nombreReceta)) {
            return "Formato incorrecto. Use: CONSULTAR RECETA [nombre]\nEjemplo: CONSULTAR RECETA Pizza";
        }

        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ No se encontró la receta: {$nombreReceta}\n" .
                   "Verifique el nombre o use 'CONSULTAR LISTA RECETAS' para ver la lista completa.";
        }

        $response = "=== RECETA: " . strtoupper($receta->nombre) . " ===\n\n";
        $response .= "📝 Información:\n";
        $response .= "• Precio: Bs. {$receta->precio}\n";
        $response .= "• Tiempo de preparación: {$receta->tiempo_preparacion} minutos\n\n";

        if ($receta->indicaciones) {
            $response .= "📜 Indicaciones:\n";
            $response .= "{$receta->indicaciones}\n\n";
        }

        $response .= "🥘 Ingredientes (para 1 porción):\n";
        $ingredientes = $receta->insumos;

        if ($ingredientes->isEmpty()) {
            $response .= "No hay ingredientes registrados para esta receta.\n";
        } else {
            $count = 0;
            foreach ($ingredientes as $insumo) {
                $count++;
                $cantidad = $insumo->pivot->cantidad;
                $unidad = $insumo->unidad_medida->nombre;
                $response .= "{$count}. {$insumo->nombre}: {$cantidad} {$unidad}\n";
            }
            $response .= "\nTotal ingredientes: {$count}";
        }

        return $response;
    }

    /**
     * Crear receta
     * Formato: CREAR RECETA [nombre] [precio] [tiempo]
     */
    private function crearReceta($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 5) {
            return "Formato incorrecto. Use: CREAR RECETA [nombre] [precio] [tiempo_minutos]\n" .
                   "Ejemplo: CREAR RECETA Pizza 35 30 (30 minutos)";
        }

        $nombre = $parts[2];
        $precio = floatval($parts[3]);
        $tiempo = intval($parts[4]);

        // Validar datos
        if ($precio <= 0 || $tiempo <= 0) {
            return "❌ Error: El precio y tiempo deben ser números mayores a 0.";
        }

        // Verificar si la receta ya existe
        if (Receta::where('nombre', $nombre)->exists()) {
            return "❌ Error: Ya existe una receta con el nombre '{$nombre}'.";
        }

        try {
            $receta = Receta::create([
                'nombre' => $nombre,
                'precio' => $precio,
                'tiempo_preparacion' => $tiempo,
            ]);

            \App\Helpers\HistorialHelper::registrar(
                'Creó receta vía email',
                "Receta: {$receta->nombre}",
                'Recetas'
            );

            return "✅ Receta creada exitosamente:\n" .
                   "• Nombre: {$receta->nombre}\n" .
                   "• Precio: Bs. {$receta->precio}\n" .
                   "• Tiempo de preparación: {$receta->tiempo_preparacion} minutos\n" .
                   "• Ingredientes: 0\n\n" .
                   "⚠️ IMPORTANTE:\n" .
                   "- Agregue indicaciones con: AÑADIR INDICACIONES {$receta->nombre} [texto]\n" .
                   "- Agregue ingredientes con: AGREGAR INGREDIENTES {$receta->nombre} [insumo] [cantidad]";

        } catch (\Exception $e) {
            return "❌ Error al crear receta: " . $e->getMessage();
        }
    }

    /**
     * Añadir indicaciones a una receta
     * Formato: AÑADIR INDICACIONES [receta] [texto]
     */
    private function añadirIndicaciones($command)
    {
        $rest = trim(str_replace('AÑADIR INDICACIONES', '', $command));
        $parts = explode(' ', $rest, 2);

        if (count($parts) < 2) {
            return "Faltan parámetros. Use: AÑADIR INDICACIONES [receta] [texto de las indicaciones]\n" .
                   "Ejemplo: AÑADIR INDICACIONES Pizza Extender la masa y hornear";
        }

        $nombreReceta = $parts[0];
        $indicaciones = $parts[1];

        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ Error: No se encontró la receta '{$nombreReceta}'.\n" .
                   "Use 'CONSULTAR LISTA RECETAS' para ver recetas disponibles.";
        }

        try {
            $receta->indicaciones = $indicaciones;
            $receta->save();

            \App\Helpers\HistorialHelper::registrar(
                'Añadió indicaciones a receta vía email',
                "Receta: {$receta->nombre}",
                'Recetas'
            );

            return "✅ Indicaciones agregadas exitosamente a la receta: {$receta->nombre}\n\n" .
                   "ℹ️  Para ver las indicaciones completas use:\n" .
                   "CONSULTAR RECETA {$receta->nombre}";

        } catch (\Exception $e) {
            return "❌ Error al añadir indicaciones: " . $e->getMessage();
        }
    }

    /**
     * Agregar ingredientes a una receta
     * Formato: AGREGAR INGREDIENTES [receta] [insumo] [cantidad], [insumo] [cantidad]...
     */
    private function agregarIngredientes($command)
    {
        $rest = trim(str_replace('AGREGAR INGREDIENTES', '', $command));
        $parts = explode(' ', $rest, 2);

        if (count($parts) < 2) {
            return "Faltan parámetros. Use: AGREGAR INGREDIENTES [receta] [insumo] [cantidad], [insumo] [cantidad]...\n" .
                   "Ejemplo: AGREGAR INGREDIENTES Pizza harina 200, tomate 3";
        }

        $nombreReceta = $parts[0];
        $ingredientesStr = $parts[1];

        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ Error: No se encontró la receta '{$nombreReceta}'.\n" .
                   "Use 'CONSULTAR LISTA RECETAS' para ver recetas disponibles.";
        }

        // Procesar ingredientes separados por comas
        $ingredientesArray = explode(',', $ingredientesStr);
        $ingredientesAgregados = [];

        foreach ($ingredientesArray as $ingredienteStr) {
            $ingredienteStr = trim($ingredienteStr);
            $ingParts = explode(' ', $ingredienteStr, 2);

            if (count($ingParts) < 2) {
                continue;
            }

            $nombreInsumo = $ingParts[0];
            $cantidad = floatval($ingParts[1]);

            if ($cantidad <= 0) {
                return "❌ Error: La cantidad debe ser mayor a 0 en '{$ingredienteStr}'";
            }

            $insumo = Insumo::where('nombre', 'like', "%{$nombreInsumo}%")->first();

            if (!$insumo) {
                return "❌ Error: No se encontró el insumo '{$nombreInsumo}'";
            }

            // Agregar o actualizar ingrediente
            $receta->insumos()->syncWithoutDetaching([
                $insumo->id => ['cantidad' => $cantidad]
            ]);

            $ingredientesAgregados[] = "{$insumo->nombre}: {$cantidad}";
        }

        if (empty($ingredientesAgregados)) {
            return "❌ Error: No se especificaron ingredientes válidos.";
        }

        try {
            \App\Helpers\HistorialHelper::registrar(
                'Agregó ingredientes a receta vía email',
                "Receta: {$receta->nombre}",
                'Recetas'
            );

            $response = "✅ Ingredientes agregados a la receta:\n";
            $response .= "• Receta: {$receta->nombre}\n";
            $response .= "• Ingredientes agregados:\n";
            foreach ($ingredientesAgregados as $ing) {
                $response .= "  - {$ing}\n";
            }

            return $response;

        } catch (\Exception $e) {
            return "❌ Error al agregar ingredientes: " . $e->getMessage();
        }
    }

    /**
     * Quitar ingrediente de una receta
     * Formato: QUITAR INGREDIENTE [receta] [insumo]
     */
    private function quitarIngrediente($command)
    {
        $params = explode(' ', trim(str_replace('QUITAR INGREDIENTE', '', $command)), 2);

        if (count($params) < 2) {
            return "Faltan parámetros. Use: QUITAR INGREDIENTE [receta] [insumo]\n" .
                   "Ejemplo: QUITAR INGREDIENTE Pizza tomate";
        }

        $nombreReceta = $params[0];
        $nombreInsumo = $params[1];

        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ Error: No se encontró la receta '{$nombreReceta}'";
        }

        $insumo = Insumo::where('nombre', 'like', "%{$nombreInsumo}%")->first();

        if (!$insumo) {
            return "❌ Error: No se encontró el insumo '{$nombreInsumo}'";
        }

        // Verificar si el ingrediente está en la receta
        if (!$receta->insumos->contains($insumo->id)) {
            return "❌ Error: El ingrediente '{$insumo->nombre}' no estaba en la receta '{$receta->nombre}'.";
        }

        try {
            $receta->insumos()->detach($insumo->id);

            \App\Helpers\HistorialHelper::registrar(
                'Quitó ingrediente de receta vía email',
                "Receta: {$receta->nombre}, Ingrediente: {$insumo->nombre}",
                'Recetas'
            );

            $remaining = $receta->insumos()->count();

            return "✅ Ingrediente quitado de la receta:\n" .
                   "• Receta: {$receta->nombre}\n" .
                   "• Ingrediente eliminado: {$insumo->nombre}\n" .
                   "• Ingredientes restantes: {$remaining}";

        } catch (\Exception $e) {
            return "❌ Error al quitar ingrediente: " . $e->getMessage();
        }
    }

    /**
     * Actualizar receta
     * Formato: ACTUALIZAR RECETA [nombre_receta] [campo] [valor], [campo] [valor]...
     */
    private function actualizarReceta($command)
    {
        $rest = trim(str_replace(['ACTUALIZAR RECETA', 'EDITAR RECETA'], '', $command));

        if (empty($rest)) {
            return "Formato incorrecto. Use: ACTUALIZAR RECETA [nombre_receta] [campo] [valor], [campo] [valor]...\n" .
                   "(Campos: nombre, precio, tiempo_preparacion, indicaciones)\n" .
                   "Ejemplo: ACTUALIZAR RECETA Pizza precio 40, tiempo_preparacion 35";
        }

        $parts = explode(' ', $rest, 2);
        if (count($parts) < 2) {
            return "Faltan parámetros. Use: ACTUALIZAR RECETA [nombre_receta] [campo] [valor], [campo] [valor]...\n" .
                   "(Campos: nombre, precio, tiempo_preparacion, indicaciones)";
        }

        $nombreReceta = $parts[0];
        $camposStr = $parts[1];

        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ Error: No se encontró la receta '{$nombreReceta}'.\n" .
                   "Use 'CONSULTAR LISTA RECETAS' para ver recetas disponibles.";
        }

        // Procesar campos separados por comas
        $camposArray = explode(',', $camposStr);
        $camposActualizados = [];

        foreach ($camposArray as $campoStr) {
            $campoStr = trim($campoStr);
            $campoParts = explode(' ', $campoStr, 2);

            if (count($campoParts) < 2) {
                continue;
            }

            $campo = strtolower($campoParts[0]);
            $valor = trim($campoParts[1]);

            // Validar campo
            if (!in_array($campo, ['nombre', 'precio', 'tiempo_preparacion', 'indicaciones'])) {
                return "❌ Error: Campo '{$campo}' no válido.\n" .
                       "(Campos: nombre, precio, tiempo_preparacion, indicaciones)";
            }

            // Procesar cada tipo de campo
            if ($campo === 'nombre') {
                $existe = Receta::where('nombre', $valor)->where('id', '!=', $receta->id)->exists();
                if ($existe) {
                    return "❌ Error: Ya existe otra receta con el nombre '{$valor}'.";
                }
                $receta->nombre = $valor;
                $camposActualizados[] = "nombre: {$valor}";

            } elseif ($campo === 'precio') {
                $precio = floatval($valor);
                if ($precio <= 0) {
                    return "❌ Error: El precio debe ser mayor a 0.";
                }
                $receta->precio = $precio;
                $camposActualizados[] = "precio: {$valor}";

            } elseif ($campo === 'tiempo_preparacion') {
                $tiempo = intval($valor);
                if ($tiempo <= 0) {
                    return "❌ Error: El tiempo_preparacion debe ser mayor a 0.";
                }
                $receta->tiempo_preparacion = $tiempo;
                $camposActualizados[] = "tiempo_preparacion: {$valor}";

            } elseif ($campo === 'indicaciones') {
                $receta->indicaciones = $valor;
                $camposActualizados[] = "indicaciones: {$valor}";
            }
        }

        if (empty($camposActualizados)) {
            return "❌ Error: No se especificaron campos válidos para actualizar.\n" .
                   "(Campos: nombre, precio, tiempo_preparacion, indicaciones)";
        }

        try {
            $receta->save();

            \App\Helpers\HistorialHelper::registrar(
                'Actualizó receta vía email',
                "Receta: {$receta->nombre}",
                'Recetas'
            );

            $response = "✅ Receta actualizada exitosamente:\n";
            $response .= "• Nombre: {$receta->nombre}\n";
            $response .= "• Campos actualizados:\n";
            foreach ($camposActualizados as $campo) {
                $response .= "  - {$campo}\n";
            }

            return $response;

        } catch (\Exception $e) {
            return "❌ Error al actualizar receta: " . $e->getMessage();
        }
    }

    /**
     * Eliminar receta
     * Formato: ELIMINAR RECETA [nombre]
     */
    private function eliminarReceta($command)
    {
        $nombreReceta = trim(str_replace('ELIMINAR RECETA', '', $command));

        if (empty($nombreReceta)) {
            return "Formato incorrecto. Use: ELIMINAR RECETA [nombre]\nEjemplo: ELIMINAR RECETA Pizza";
        }

        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ Error: No se encontró la receta '{$nombreReceta}'.\n" .
                   "Use 'CONSULTAR LISTA RECETAS' para ver recetas disponibles.";
        }

        $nombre = $receta->nombre;
        $cantidadIngredientes = $receta->insumos()->count();

        try {
            // Eliminar relaciones en la tabla pivote
            $receta->insumos()->detach();

            // Eliminar la receta
            $receta->delete();

            \App\Helpers\HistorialHelper::registrar(
                'Eliminó receta vía email',
                "Receta: {$nombre}",
                'Recetas'
            );

            return "✅ Receta '{$nombre}' eliminada exitosamente.\n" .
                   "• Ingredientes eliminados: {$cantidadIngredientes}\n" .
                   "• La receta ya no estará disponible en el sistema.";

        } catch (\Exception $e) {
            return "❌ Error al eliminar receta: " . $e->getMessage();
        }
    }

    /**
     * Consultar ventas
     */
    /**
     * Crear venta
     * Formato: CREAR VENTA [receta] [cantidad]
     */
    private function crearVenta($command)
    {
        $parts = explode(' ', $command, 4);

        if (count($parts) < 4) {
            return "Formato incorrecto.\nUse: CREAR VENTA [receta] [cantidad]\n\nEjemplo: CREAR VENTA Pizza 5";
        }

        $nombreReceta = $parts[2];
        $cantidad = floatval($parts[3]);

        if ($cantidad <= 0) {
            return "❌ ERROR: La cantidad debe ser mayor a 0";
        }

        // Buscar receta
        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ ERROR: No se encontró la receta: {$nombreReceta}";
        }

        // Obtener precio de la receta
        $precio = $receta->precio;

        if (!$precio || $precio <= 0) {
            return "❌ ERROR: La receta {$receta->nombre} no tiene un precio válido configurado";
        }

        // Calcular total
        $total = $cantidad * $precio;

        // Crear venta
        $venta = Venta::create([
            'receta_id' => $receta->id,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'total' => $total,
        ]);

        // Registrar en historial
        \App\Helpers\HistorialHelper::registrar(
            'Creó venta vía email',
            "Receta: {$receta->nombre}, Cantidad: {$cantidad}, Total: {$total}",
            'Ventas'
        );

        $response = "✅ VENTA REGISTRADA EXITOSAMENTE\n\n";
        $response .= "ID: {$venta->id}\n";
        $response .= "Receta: {$receta->nombre}\n";
        $response .= "Cantidad: {$cantidad} porciones\n";
        $response .= "Precio unitario: Bs. {$precio}\n";
        $response .= "Total: Bs. {$total}\n";
        $response .= "Fecha: {$venta->created_at->format('d/m/Y H:i')}\n\n";
        $response .= "✅ Insumos descontados automáticamente del inventario";

        return $response;
    }

    /**
     * Consultar ventas con filtros
     * Formato: CONSULTAR VENTAS [filtro] [valor], [filtro] [valor]...
     */
    private function consultarVentas($filtros = '')
    {
        $query = Venta::with(['receta'])->orderBy('created_at', 'desc');

        $filtrosAplicados = [];

        if (!empty($filtros)) {
            $filtrosPares = array_map('trim', explode(',', $filtros));

            foreach ($filtrosPares as $par) {
                $partes = preg_split('/\s+/', trim($par), 2);

                if (count($partes) >= 2) {
                    $tipoFiltro = strtolower($partes[0]);
                    $valorFiltro = trim($partes[1]);

                    switch ($tipoFiltro) {
                        case 'receta':
                            $receta = Receta::where('nombre', 'like', "%{$valorFiltro}%")->first();
                            if ($receta) {
                                $query->where('receta_id', $receta->id);
                                $filtrosAplicados[] = "Receta: {$receta->nombre}";
                            }
                            break;

                        case 'cantidad':
                            $query->where('cantidad', $valorFiltro);
                            $filtrosAplicados[] = "Cantidad: {$valorFiltro}";
                            break;

                        case 'precio':
                            $query->where('precio', $valorFiltro);
                            $filtrosAplicados[] = "Precio: {$valorFiltro}";
                            break;

                        case 'total':
                            $query->where('total', $valorFiltro);
                            $filtrosAplicados[] = "Total: {$valorFiltro}";
                            break;

                        case 'fecha':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', $fechaNormalizada);
                                $filtrosAplicados[] = "Fecha: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida
                            }
                            break;

                        case 'fecha_desde':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', '>=', $fechaNormalizada);
                                $filtrosAplicados[] = "Desde: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida
                            }
                            break;

                        case 'fecha_hasta':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', '<=', $fechaNormalizada);
                                $filtrosAplicados[] = "Hasta: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida
                            }
                            break;
                    }
                }
            }
        }

        $ventas = $query->get();

        if ($ventas->isEmpty()) {
            return "No se encontraron ventas" . (empty($filtrosAplicados) ? "." : " con los filtros especificados.");
        }

        $response = "=== VENTAS ===\n\n";

        if (!empty($filtrosAplicados)) {
            $response .= "Filtros aplicados:\n";
            foreach ($filtrosAplicados as $filtro) {
                $response .= "• {$filtro}\n";
            }
            $response .= "\n";
        }

        $count = 0;
        $totalGeneral = 0;
        foreach ($ventas as $venta) {
            $count++;
            $response .= "{$count}. [ID:{$venta->id}] {$venta->receta->nombre}\n";
            $response .= "   Cantidad: {$venta->cantidad} porciones | Precio: Bs. {$venta->precio}\n";
            $response .= "   Total: Bs. {$venta->total} | Fecha: {$venta->created_at->format('d/m/Y H:i')}\n\n";
            $totalGeneral += $venta->total;
        }

        $response .= "Total ventas: {$count}\n";
        $response .= "Monto total: Bs. " . number_format($totalGeneral, 2);

        return $response;
    }


    /**
     * Actualizar venta
     * Formato: ACTUALIZAR VENTA [receta] [id] [campo] [valor]
     */
    private function actualizarVenta($command)
    {
        $parts = explode(' ', $command, 6);

        if (count($parts) < 6) {
            return "Formato incorrecto.\nUse: ACTUALIZAR VENTA [receta] [id] [campo] [valor]\n\n" .
                   "Campos disponibles: cantidad, precio, receta\n" .
                   "Ejemplo: ACTUALIZAR VENTA Pizza 10 cantidad 8";
        }

        $nombreReceta = $parts[2];
        $ventaId = intval($parts[3]);
        $campo = strtolower($parts[4]);
        $valor = $parts[5];

        // Buscar receta
        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ ERROR: No se encontró la receta: {$nombreReceta}";
        }

        // Buscar venta específica de esa receta
        $venta = Venta::with(['receta'])
            ->where('id', $ventaId)
            ->where('receta_id', $receta->id)
            ->first();

        if (!$venta) {
            return "❌ ERROR: No se encontró la venta ID {$ventaId} de la receta {$nombreReceta}.\n" .
                   "Use: CONSULTAR VENTAS receta {$nombreReceta}\npara ver los IDs disponibles.";
        }

        $valorAnterior = '';

        switch ($campo) {
            case 'cantidad':
                $cantidad = floatval($valor);
                if ($cantidad <= 0) {
                    return "❌ ERROR: La cantidad debe ser mayor a 0";
                }
                $valorAnterior = $venta->cantidad;
                $venta->cantidad = $cantidad;
                $venta->total = $cantidad * $venta->precio;
                break;

            case 'precio':
                $precio = floatval($valor);
                if ($precio <= 0) {
                    return "❌ ERROR: El precio debe ser mayor a 0";
                }
                $valorAnterior = $venta->precio;
                $venta->precio = $precio;
                $venta->total = $venta->cantidad * $precio;
                break;

            case 'receta':
                $receta = Receta::where('nombre', 'like', "%{$valor}%")->first();
                if (!$receta) {
                    return "❌ ERROR: No se encontró la receta: {$valor}";
                }
                $valorAnterior = $venta->receta->nombre;
                $venta->receta_id = $receta->id;
                break;

            default:
                return "❌ ERROR: Campo '{$campo}' no válido.\nCampos disponibles: cantidad, precio, receta";
        }

        $venta->save();
        $venta->refresh();

        // Registrar en historial
        \App\Helpers\HistorialHelper::registrar(
            'Actualizó venta vía email',
            "ID: {$venta->id}, Campo: {$campo}, Valor anterior: {$valorAnterior}, Nuevo valor: {$valor}",
            'Ventas'
        );

        $response = "✅ VENTA ACTUALIZADA EXITOSAMENTE\n\n";
        $response .= "ID: {$venta->id}\n";
        $response .= "Receta: {$venta->receta->nombre}\n";
        $response .= "Cantidad: {$venta->cantidad} porciones\n";
        $response .= "Precio unitario: Bs. {$venta->precio}\n";
        $response .= "Total: Bs. {$venta->total}\n";
        $response .= "Fecha: {$venta->created_at->format('d/m/Y H:i')}\n\n";
        $response .= "Campo actualizado: {$campo}";

        return $response;
    }

    /**
     * Eliminar venta
     * Formato: ELIMINAR VENTA [receta] [id]
     */
    private function eliminarVenta($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 4) {
            return "Formato incorrecto.\nUse: ELIMINAR VENTA [receta] [id]\n\nEjemplo: ELIMINAR VENTA Pizza 10";
        }

        $nombreReceta = $parts[2];
        $ventaId = intval($parts[3]);

        // Buscar receta
        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ ERROR: No se encontró la receta: {$nombreReceta}";
        }

        // Buscar venta específica de esa receta
        $venta = Venta::with(['receta', 'movimientos_inventario'])
            ->where('id', $ventaId)
            ->where('receta_id', $receta->id)
            ->first();

        if (!$venta) {
            return "❌ ERROR: No se encontró la venta ID {$ventaId} de la receta {$nombreReceta}.\n" .
                   "Use: CONSULTAR VENTAS receta {$nombreReceta}\npara ver los IDs disponibles.";
        }

        $recetaNombre = $venta->receta->nombre;
        $cantidad = $venta->cantidad;
        $total = $venta->total;
        $fecha = $venta->created_at->format('d/m/Y H:i');

        // Eliminar movimientos relacionados
        foreach ($venta->movimientos_inventario as $movimiento) {
            $movimiento->delete();
        }

        $venta->delete();

        // Registrar en historial
        \App\Helpers\HistorialHelper::registrar(
            'Eliminó venta vía email',
            "ID: {$ventaId}, Receta: {$recetaNombre}, Cantidad: {$cantidad}, Total: {$total}",
            'Ventas'
        );

        $response = "✅ VENTA ELIMINADA EXITOSAMENTE\n\n";
        $response .= "ID: {$ventaId}\n";
        $response .= "Receta: {$recetaNombre}\n";
        $response .= "Cantidad: {$cantidad} porciones\n";
        $response .= "Total: Bs. {$total}\n";
        $response .= "Fecha: {$fecha}\n\n";
        $response .= "⚠️ Los movimientos de inventario asociados fueron eliminados.";

        return $response;
    }


    /**
     * Consultar predicciones
     */
    private function consultarPredicciones()
    {
        // Aquí integrarías con tu servicio de IA
        $response = "=== PREDICCIONES DE CONSUMO ===\n\n";
        $response .= "Las predicciones están disponibles en el sistema web.\n";
        $response .= "Para obtener predicciones específicas, contacte al administrador.\n";

        return $response;
    }

    /**
     * Mostrar ayuda con comandos disponibles organizados por los 8 CU
     */
    private function mostrarAyuda()
    {
        $response = "╔════════════════════════════════════════════════════════════╗\n";
        $response .= "║   SISTEMA DE GESTIÓN DE RESTAURANTE - COMANDOS EMAIL      ║\n";
        $response .= "╚════════════════════════════════════════════════════════════╝\n\n";

        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "📋 CU1 - GESTIÓN DE USUARIOS\n";
        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "• CREAR USUARIO [nombre] [apellidoP] [apellidoM] [email] [pass]\n";
        $response .= "• CONSULTAR USUARIOS\n";
        $response .= "• ACTUALIZAR USUARIO [id] [campo] [valor]\n";
        $response .= "  Campos: nombre, apellido_paterno, apellido_materno, email, password\n";
        $response .= "• ELIMINAR USUARIO [id]\n";
        $response .= "• ASIGNAR ROL [id] [rol]\n";
        $response .= "  Roles: admin, director, cocinero, cajero, ayudante_cocina, mesero\n\n";

        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "🥕 CU2 - GESTIÓN DE INSUMOS\n";
        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "• CREAR INSUMO [nombre] [descripcion] [stock_min] [categoria] [unidad]\n";
        $response .= "• CONSULTAR INSUMOS [filtro] [valor], [filtro] [valor]...\n";
        $response .= "  Filtros: insumo, categoria, stock_minimo\n";
        $response .= "• CONSULTAR STOCK DISPONIBLE [nombre_insumo]\n";
        $response .= "• ACTUALIZAR INSUMO [nombre_insumo] [campo] [valor], [campo] [valor]...\n";
        $response .= "  (Campos: nombre, descripcion, stock_minimo, categoria, unidad)\n";
        $response .= "• ELIMINAR INSUMO [nombre]\n\n";

        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "🍳 CU3 - GESTIÓN DE RECETAS\n";
        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "• CREAR RECETA [nombre] [precio] [tiempo_minutos]\n";
        $response .= "• AÑADIR INDICACIONES [receta] [texto]\n";
        $response .= "• AGREGAR INGREDIENTES [receta] [insumo] [cantidad], [insumo] [cantidad]...\n";
        $response .= "• QUITAR INGREDIENTE [receta] [insumo]\n";
        $response .= "• CONSULTAR LISTA RECETAS\n";
        $response .= "• CONSULTAR RECETA [nombre]\n";
        $response .= "• ACTUALIZAR RECETA [nombre] [campo] [valor], [campo] [valor]...\n";
        $response .= "  (Campos: nombre, precio, tiempo_preparacion, indicaciones)\n";
        $response .= "• ELIMINAR RECETA [nombre]\n\n";

        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "📦 CU4 - GESTIÓN DE INVENTARIOS\n";
        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "• AGREGAR MOVIMIENTO [entrada/salida] [insumo] [cantidad] [motivo]\n";
        $response .= "• CONSULTAR MOVIMIENTOS [fecha_inicio] [fecha_fin]\n\n";

        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "🏭 CU5 - GESTIÓN DE PRODUCCIÓN\n";
        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "⚠️  Funcionalidad en desarrollo\n\n";

        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "💰 CU6 - GESTIÓN DE VENTAS\n";
        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "• CONSULTAR VENTAS [fecha]\n\n";

        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "💳 CU7 - GESTIÓN DE PAGOS\n";
        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "⚠️  Funcionalidad en desarrollo\n\n";

        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "📊 CU8 - REPORTES Y ESTADÍSTICAS\n";
        $response .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $response .= "• CONSULTAR REPORTE [tipo]\n";
        $response .= "  Tipos: stock, ventas, general\n";
        $response .= "• CONSULTAR PREDICCIONES\n\n";

        $response .= "════════════════════════════════════════════════════════════\n";
        $response .= "📖 EJEMPLOS DE USO:\n";
        $response .= "════════════════════════════════════════════════════════════\n";
        $response .= "CU1: CREAR USUARIO Juan Perez Lopez juan@mail.com Pass1234\n";
        $response .= "CU1: ASIGNAR ROL 5 cocinero\n";
        $response .= "CU2: CREAR INSUMO tomate fresco 10 verduras kg\n";
        $response .= "CU2: CONSULTAR INSUMOS\n";
        $response .= "CU2: CONSULTAR INSUMOS insumo harina\n";
        $response .= "CU2: CONSULTAR INSUMOS categoria verduras, stock_minimo 10\n";
        $response .= "CU2: CONSULTAR STOCK DISPONIBLE tomate\n";
        $response .= "CU2: ELIMINAR INSUMO harina\n";
        $response .= "CU4: AGREGAR MOVIMIENTO entrada tomate 5 compra\n\n";

        $response .= "ℹ️  Para más información sobre un comando específico,\n";
        $response .= "   ejecute el comando sin parámetros.\n";

        return $response;
    }

    // ========================================
    // CU5 - GESTIÓN DE PRODUCCIÓN
    // ========================================

    /**
     * Calcular producción (solo consulta, no guarda)
     * Formato: CALCULAR PRODUCCION [nombre_receta] [cantidad]
     */
    private function calcularProduccion($command)
    {
        $parts = explode(' ', $command, 4);

        if (count($parts) < 4) {
            return "Formato incorrecto.\nUse: CALCULAR PRODUCCION [nombre_receta] [cantidad]\n\nEjemplo: CALCULAR PRODUCCION Pizza 20";
        }

        $nombreReceta = $parts[2];
        $cantidad = intval($parts[3]);

        if ($cantidad <= 0) {
            return "❌ ERROR: La cantidad debe ser mayor a 0";
        }

        // Buscar receta
        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ ERROR: No se encontró la receta: {$nombreReceta}";
        }

        // Calcular insumos (sin guardar)
        $insumos = $receta->insumos->map(function ($insumo) use ($cantidad) {
            return [
                'nombre' => $insumo->nombre,
                'cantidad' => $insumo->pivot->cantidad * $cantidad,
                'unidad_medida' => $insumo->unidad_medida->abreviatura,
                'stock_actual' => $insumo->getCantidadTotal(),
                'stock_suficiente' => $insumo->getCantidadTotal() >= ($insumo->pivot->cantidad * $cantidad),
            ];
        });

        $response = "📊 CÁLCULO DE PRODUCCIÓN (No guardado)\n\n";
        $response .= "Receta: {$receta->nombre}\n";
        $response .= "Cantidad: {$cantidad} unidades\n";
        $response .= "Precio unitario: \${$receta->precio}\n";
        $response .= "Total estimado: \$" . ($receta->precio * $cantidad) . "\n\n";
        $response .= "INSUMOS NECESARIOS:\n";

        $todoDisponible = true;
        foreach ($insumos as $insumo) {
            $simbolo = $insumo['stock_suficiente'] ? '✅' : '❌';
            $response .= "• {$insumo['nombre']}: {$insumo['cantidad']} {$insumo['unidad_medida']}\n";
            $response .= "  Stock actual: {$insumo['stock_actual']} {$insumo['unidad_medida']} {$simbolo}\n";

            if (!$insumo['stock_suficiente']) {
                $todoDisponible = false;
            }
        }

        $response .= "\n";
        $response .= $todoDisponible ? "✅ Todos los insumos disponibles" : "⚠️ Insumos insuficientes";
        $response .= "\n\n💡 Para guardar este plan use: CREAR PLAN PRODUCCION {$nombreReceta} {$cantidad}";

        return $response;
    }

    /**
     * Crear plan de producción (calcula Y guarda)
     * Formato: CREAR PLAN PRODUCCION [nombre_receta] [cantidad]
     */
    private function crearPlanProduccion($command)
    {
        $parts = explode(' ', $command, 5);

        if (count($parts) < 5) {
            return "Formato incorrecto.\nUse: CREAR PLAN PRODUCCION [nombre_receta] [cantidad]\n\nEjemplo: CREAR PLAN PRODUCCION Pizza 20";
        }

        $nombreReceta = $parts[3];
        $cantidad = intval($parts[4]);

        if ($cantidad <= 0) {
            return "❌ ERROR: La cantidad debe ser mayor a 0";
        }

        // Buscar receta
        $receta = Receta::where('nombre', 'like', "%{$nombreReceta}%")->first();

        if (!$receta) {
            return "❌ ERROR: No se encontró la receta: {$nombreReceta}";
        }

        // Crear plan
        $plan = PlanProduccion::create([
            'nombre' => $nombrePlan,
            'receta_id' => $receta->id,
            'cantidad' => $cantidad,
        ]);

        // Calcular insumos
        $insumos = $plan->calcularInsumos();

        // Registrar en historial
        \App\Helpers\HistorialHelper::registrar(
            'Creó plan de producción vía email',
            "Receta: {$receta->nombre}, Cantidad: {$cantidad}",
            'Planes Producción'
        );

        $response = "✅ PLAN DE PRODUCCIÓN CREADO\n\n";
        $response .= "ID: {$plan->id}\n";
        $response .= "Nombre: {$plan->nombre}\n";
        $response .= "Receta: {$receta->nombre}\n";
        $response .= "Cantidad a producir: {$cantidad} unidades\n";
        $response .= "Fecha: {$plan->created_at->format('d/m/Y H:i')}\n\n";
        $response .= "INSUMOS CALCULADOS:\n";

        foreach ($insumos as $insumo) {
            $simbolo = $insumo['stock_suficiente'] ? '✅' : '❌';
            $response .= "• {$insumo['nombre']}: {$insumo['cantidad']} {$insumo['unidad_medida']} {$simbolo}\n";
        }

        return $response;
    }

    /**
     * Consultar planes de producción
     * Formato: CONSULTAR PLANES PRODUCCION [filtros opcionales]
     */
    private function consultarPlanesProduccion($filtros = '')
    {
        $query = PlanProduccion::with(['receta'])->orderBy('created_at', 'desc');

        $filtrosAplicados = [];

        if (!empty($filtros)) {
            $filtrosPares = array_map('trim', explode(',', $filtros));

            foreach ($filtrosPares as $par) {
                $partes = preg_split('/\s+/', trim($par), 2);

                if (count($partes) >= 2) {
                    $tipoFiltro = strtolower($partes[0]);
                    $valorFiltro = trim($partes[1]);

                    switch ($tipoFiltro) {
                        case 'nombre':
                            $query->where('nombre', 'like', "%{$valorFiltro}%");
                            $filtrosAplicados[] = "Nombre: {$valorFiltro}";
                            break;

                        case 'receta':
                            $receta = Receta::where('nombre', 'like', "%{$valorFiltro}%")->first();
                            if ($receta) {
                                $query->where('receta_id', $receta->id);
                                $filtrosAplicados[] = "Receta: {$receta->nombre}";
                            }
                            break;

                        case 'cantidad':
                            $query->where('cantidad', $valorFiltro);
                            $filtrosAplicados[] = "Cantidad: {$valorFiltro}";
                            break;

                        case 'fecha':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', $fechaNormalizada);
                                $filtrosAplicados[] = "Fecha: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida
                            }
                            break;

                        case 'fecha_desde':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', '>=', $fechaNormalizada);
                                $filtrosAplicados[] = "Desde: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida
                            }
                            break;

                        case 'fecha_hasta':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', '<=', $fechaNormalizada);
                                $filtrosAplicados[] = "Hasta: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida
                            }
                            break;
                    }
                }
            }
        }

        $planes = $query->get();

        if ($planes->isEmpty()) {
            return "No se encontraron planes de producción" . (empty($filtrosAplicados) ? "." : " con los filtros especificados.");
        }

        $response = "=== PLANES DE PRODUCCIÓN ===\n\n";

        if (!empty($filtrosAplicados)) {
            $response .= "Filtros aplicados:\n";
            foreach ($filtrosAplicados as $filtro) {
                $response .= "• {$filtro}\n";
            }
            $response .= "\n";
        }

        $count = 0;
        foreach ($planes as $plan) {
            $count++;
            $response .= "{$count}. [ID:{$plan->id}] {$plan->nombre}\n";
            $response .= "   Receta: {$plan->receta->nombre} - {$plan->cantidad} unidades\n";
            $response .= "   Fecha: {$plan->created_at->format('d/m/Y H:i')}\n\n";
        }

        $response .= "Total planes: {$count}";

        return $response;
    }

    /**
     * Consultar detalle de un plan de producción
     * Formato: CONSULTAR PLAN PRODUCCION [nombre_plan] [id]
     */
    private function consultarPlanProduccion($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 5) {
            return "Formato incorrecto.\nUse: CONSULTAR PLAN PRODUCCION [nombre_plan] [id]\n\nEjemplo: CONSULTAR PLAN PRODUCCION MenuDiario 10";
        }

        $nombrePlan = $parts[3];
        $planId = intval($parts[4]);

        $plan = PlanProduccion::with(['receta'])
            ->where('id', $planId)
            ->where('nombre', 'like', "%{$nombrePlan}%")
            ->first();

        if (!$plan) {
            return "❌ ERROR: No se encontró el plan con nombre '{$nombrePlan}' e ID: {$planId}.\n" .
                   "Use: CONSULTAR PLANES PRODUCCION\npara ver los planes disponibles.";
        }

        $insumos = $plan->calcularInsumos();

        $response = "=== PLAN DE PRODUCCIÓN #{$plan->id} ===\n\n";
        $response .= "Nombre: {$plan->nombre}\n";
        $response .= "Receta: {$plan->receta->nombre}\n";
        $response .= "Cantidad: {$plan->cantidad} unidades\n";
        $response .= "Creado: {$plan->created_at->format('d/m/Y H:i')}\n\n";
        $response .= "INSUMOS NECESARIOS:\n";

        $todoDisponible = true;
        foreach ($insumos as $insumo) {
            $simbolo = $insumo['stock_suficiente'] ? '✅' : '❌';
            $response .= "• {$insumo['nombre']}: {$insumo['cantidad']} {$insumo['unidad_medida']}\n";
            $response .= "  Stock actual: {$insumo['stock_actual']} {$insumo['unidad_medida']} {$simbolo}\n";

            if (!$insumo['stock_suficiente']) {
                $todoDisponible = false;
            }
        }

        $response .= "\n";
        $response .= $todoDisponible ? "✅ Todos los insumos disponibles" : "⚠️ Insumos insuficientes";

        return $response;
    }

    /**
     * Actualizar plan de producción
     * Formato: ACTUALIZAR PLAN PRODUCCION [nombre_plan] [id] [campo] [valor]
     */
    private function actualizarPlanProduccion($command)
    {
        $parts = explode(' ', $command, 7);

        if (count($parts) < 7) {
            return "Formato incorrecto.\nUse: ACTUALIZAR PLAN PRODUCCION [nombre_plan] [id] [campo] [valor]\n\n" .
                   "Campos disponibles: nombre, receta, cantidad\n" .
                   "Ejemplo: ACTUALIZAR PLAN PRODUCCION MenuDiario 10 cantidad 25";
        }

        $nombrePlan = $parts[3];
        $planId = intval($parts[4]);
        $campo = strtolower($parts[5]);
        $valor = $parts[6];

        $plan = PlanProduccion::with(['receta'])
            ->where('id', $planId)
            ->where('nombre', 'like', "%{$nombrePlan}%")
            ->first();

        if (!$plan) {
            return "❌ ERROR: No se encontró el plan con nombre '{$nombrePlan}' e ID: {$planId}.\n" .
                   "Use: CONSULTAR PLANES PRODUCCION\npara ver los planes disponibles.";
        }

        $valorAnterior = '';

        switch ($campo) {
            case 'nombre':
                $valorAnterior = $plan->nombre;
                $plan->nombre = $valor;
                break;

            case 'receta':
                $receta = Receta::where('nombre', 'like', "%{$valor}%")->first();
                if (!$receta) {
                    return "❌ ERROR: No se encontró la receta: {$valor}";
                }
                $valorAnterior = $plan->receta->nombre;
                $plan->receta_id = $receta->id;
                break;

            case 'cantidad':
                $cantidad = intval($valor);
                if ($cantidad <= 0) {
                    return "❌ ERROR: La cantidad debe ser mayor a 0";
                }
                $valorAnterior = $plan->cantidad;
                $plan->cantidad = $cantidad;
                break;

            default:
                return "❌ ERROR: Campo '{$campo}' no válido.\nCampos disponibles: nombre, receta, cantidad";
        }

        $plan->save();
        $plan->refresh();

        // Registrar en historial
        \App\Helpers\HistorialHelper::registrar(
            'Actualizó plan de producción vía email',
            "ID: {$plan->id}, Campo: {$campo}, Valor anterior: {$valorAnterior}, Nuevo valor: {$valor}",
            'Planes Producción'
        );

        $insumos = $plan->calcularInsumos();

        $response = "✅ PLAN DE PRODUCCIÓN ACTUALIZADO\n\n";
        $response .= "ID: {$plan->id}\n";
        $response .= "Receta: {$plan->receta->nombre}\n";
        $response .= "Cantidad: {$plan->cantidad} unidades\n\n";

        if ($campo === 'cantidad') {
            $response .= "INSUMOS RECALCULADOS:\n";
            foreach ($insumos as $insumo) {
                $response .= "• {$insumo['nombre']}: {$insumo['cantidad']} {$insumo['unidad_medida']}\n";
            }
        }

        return $response;
    }

    /**
     * Eliminar plan de producción
     * Formato: ELIMINAR PLAN PRODUCCION [nombre_plan] [id]
     */
    private function eliminarPlanProduccion($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 5) {
            return "Formato incorrecto.\nUse: ELIMINAR PLAN PRODUCCION [nombre_plan] [id]\n\nEjemplo: ELIMINAR PLAN PRODUCCION MenuDiario 10";
        }

        $nombrePlan = $parts[3];
        $planId = intval($parts[4]);

        $plan = PlanProduccion::with(['receta'])
            ->where('id', $planId)
            ->where('nombre', 'like', "%{$nombrePlan}%")
            ->first();

        if (!$plan) {
            return "❌ ERROR: No se encontró el plan con nombre '{$nombrePlan}' e ID: {$planId}.\n" .
                   "Use: CONSULTAR PLANES PRODUCCION\npara ver los planes disponibles.";
        }

        $recetaNombre = $plan->receta->nombre;
        $cantidad = $plan->cantidad;
        $fecha = $plan->created_at->format('d/m/Y H:i');

        $plan->delete();

        // Registrar en historial
        \App\Helpers\HistorialHelper::registrar(
            'Eliminó plan de producción vía email',
            "ID: {$planId}, Receta: {$recetaNombre}, Cantidad: {$cantidad}",
            'Planes Producción'
        );

        $response = "✅ PLAN DE PRODUCCIÓN ELIMINADO\n\n";
        $response .= "ID: {$planId}\n";
        $response .= "Receta: {$recetaNombre}\n";
        $response .= "Cantidad: {$cantidad} unidades\n";
        $response .= "Fecha: {$fecha}";

        return $response;
    }

    // ========== MÉTODOS DE COMPRAS/PAGOS (CU7) ==========

    /**
     * Crear compra
     * Formato: CREAR COMPRA [proveedor] [monto] [descripcion]
     */
    private function crearCompra($command)
    {
        $parts = explode(' ', $command, 5);

        if (count($parts) < 5) {
            return "Formato incorrecto.\nUse: CREAR COMPRA [proveedor] [monto] [descripcion]\n\nEjemplo: CREAR COMPRA DistribuidoraXYZ 500 Compra semanal de insumos";
        }

        $proveedor = $parts[2];
        $monto = floatval($parts[3]);
        $descripcion = $parts[4];

        if ($monto <= 0) {
            return "❌ ERROR: El monto debe ser mayor a 0";
        }

        $compra = Compra::create([
            'costo_total' => $monto,
            'proveedor' => $proveedor,
            'descripcion' => $descripcion,
        ]);

        \App\Helpers\HistorialHelper::registrar(
            'Creó compra vía email',
            "Proveedor: {$proveedor}, Monto: Bs. {$monto}",
            'Compras'
        );

        return "✅ COMPRA REGISTRADA EXITOSAMENTE\n\n" .
               "ID: {$compra->id}\n" .
               "Proveedor: {$proveedor}\n" .
               "Monto: Bs. " . number_format($monto, 2) . "\n" .
               "Descripción: {$descripcion}";
    }

    /**
     * Consultar compras con filtros
     */
    private function consultarCompras($filtros = '')
    {
        $query = Compra::query();

        $filtrosAplicados = [];

        if (!empty($filtros)) {
            $filtrosPares = explode(',', $filtros);

            foreach ($filtrosPares as $par) {
                $partes = preg_split('/\s+/', trim($par), 2);

                if (count($partes) >= 2) {
                    $tipoFiltro = strtolower($partes[0]);
                    $valorFiltro = trim($partes[1]);

                    switch ($tipoFiltro) {
                        case 'proveedor':
                            $query->where('proveedor', 'like', "%{$valorFiltro}%");
                            $filtrosAplicados[] = "Proveedor: {$valorFiltro}";
                            break;

                        case 'monto_min':
                            $query->where('costo_total', '>=', $valorFiltro);
                            $filtrosAplicados[] = "Monto mínimo: {$valorFiltro}";
                            break;

                        case 'monto_max':
                            $query->where('costo_total', '<=', $valorFiltro);
                            $filtrosAplicados[] = "Monto máximo: {$valorFiltro}";
                            break;

                        case 'fecha':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', $fechaNormalizada);
                                $filtrosAplicados[] = "Fecha: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida
                            }
                            break;

                        case 'fecha_desde':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', '>=', $fechaNormalizada);
                                $filtrosAplicados[] = "Desde: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida
                            }
                            break;

                        case 'fecha_hasta':
                            try {
                                $fechaNormalizada = $this->normalizarFecha($valorFiltro);
                                $query->whereDate('created_at', '<=', $fechaNormalizada);
                                $filtrosAplicados[] = "Hasta: {$fechaNormalizada}";
                            } catch (\Exception $e) {
                                // Ignorar fecha inválida
                            }
                            break;
                    }
                }
            }
        }

        $compras = $query->orderBy('created_at', 'desc')->get();

        $response = "=== COMPRAS ===\n\n";

        if (!empty($filtrosAplicados)) {
            $response .= "Filtros aplicados: " . implode(', ', $filtrosAplicados) . "\n\n";
        }

        if ($compras->isEmpty()) {
            $response .= "No se encontraron compras";
            if (!empty($filtrosAplicados)) {
                $response .= " con los filtros especificados";
            }
            $response .= ".\n";
        } else {
            $totalGeneral = 0;
            $count = 0;

            foreach ($compras as $compra) {
                $count++;
                $totalGeneral += $compra->costo_total;

                $response .= "{$count}. [ID:{$compra->id}] " . ($compra->proveedor ?? 'N/A') . "\n";
                $response .= "   Monto: Bs. " . number_format($compra->costo_total, 2) . "\n";
                $response .= "   Descripción: " . ($compra->descripcion ?? 'N/A') . "\n";
                $response .= "   Fecha: {$compra->created_at->format('d/m/Y H:i')}\n\n";
            }

            $response .= "═══════════════════════\n";
            $response .= "Total compras: {$count}\n";
            $response .= "Monto total: Bs. " . number_format($totalGeneral, 2);
        }

        \App\Helpers\HistorialHelper::registrar(
            'Consultó compras vía email',
            'Filtros: ' . ($filtros ?: 'ninguno'),
            'Compras'
        );

        return $response;
    }

    /**
     * Actualizar compra
     * Formato: ACTUALIZAR COMPRA [id] [campo] [valor]
     */
    private function actualizarCompra($command)
    {
        $parts = explode(' ', $command, 5);

        if (count($parts) < 5) {
            return "Formato incorrecto.\nUse: ACTUALIZAR COMPRA [id] [campo] [valor]\n\n" .
                   "Campos disponibles: monto, proveedor, descripcion\n" .
                   "Ejemplo: ACTUALIZAR COMPRA 10 monto 550";
        }

        $compraId = intval($parts[2]);
        $campo = strtolower($parts[3]);
        $valor = $parts[4];

        $compra = Compra::find($compraId);

        if (!$compra) {
            return "❌ ERROR: No se encontró la compra con ID: {$compraId}";
        }

        switch ($campo) {
            case 'monto':
                $montoNuevo = floatval($valor);
                if ($montoNuevo <= 0) {
                    return "❌ ERROR: El monto debe ser mayor a 0";
                }
                $compra->costo_total = $montoNuevo;
                break;

            case 'proveedor':
                $compra->proveedor = $valor;
                break;

            case 'descripcion':
                $compra->descripcion = $valor;
                break;

            default:
                return "❌ ERROR: Campo '{$campo}' no válido.\nCampos disponibles: monto, proveedor, descripcion";
        }

        $compra->save();

        \App\Helpers\HistorialHelper::registrar(
            'Actualizó compra vía email',
            "ID: {$compraId}, Campo: {$campo}",
            'Compras'
        );

        return "✅ COMPRA ACTUALIZADA EXITOSAMENTE\n\n" .
               "ID: {$compraId}\n" .
               "Campo actualizado: {$campo}\n" .
               "Nuevo valor: {$valor}";
    }

    /**
     * Eliminar compra
     * Formato: ELIMINAR COMPRA [id]
     */
    private function eliminarCompra($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 3) {
            return "Formato incorrecto.\nUse: ELIMINAR COMPRA [id]\n\nEjemplo: ELIMINAR COMPRA 10";
        }

        $compraId = intval($parts[2]);

        $compra = Compra::find($compraId);

        if (!$compra) {
            return "❌ ERROR: No se encontró la compra con ID: {$compraId}";
        }

        $proveedor = $compra->proveedor;
        $monto = $compra->costo_total;
        $descripcion = $compra->descripcion;
        $fecha = $compra->created_at->format('d/m/Y H:i');

        $compra->delete();

        \App\Helpers\HistorialHelper::registrar(
            'Eliminó compra vía email',
            "ID: {$compraId}, Proveedor: {$proveedor}, Monto: Bs. {$monto}",
            'Compras'
        );

        return "✅ COMPRA ELIMINADA EXITOSAMENTE\n\n" .
               "ID: {$compraId}\n" .
               "Proveedor: " . ($proveedor ?? 'N/A') . "\n" .
               "Monto: Bs. " . number_format($monto, 2) . "\n" .
               "Descripción: " . ($descripcion ?? 'N/A') . "\n" .
               "Fecha: {$fecha}";
    }

    // ========== MÉTODOS AUXILIARES DE REPORTES ==========

    /**
     * Generar reporte de ventas por fechas
     */
    private function generarReporteVentasPorFechas($fechaDesde, $fechaHasta)
    {
        $ventas = Venta::whereBetween(\DB::raw('DATE(created_at)'), [$fechaDesde, $fechaHasta])->get();

        $totalVentas = $ventas->count();
        $ingresosTotales = $ventas->sum('total');
        $promedioVenta = $totalVentas > 0 ? $ingresosTotales / $totalVentas : 0;
        $platosVendidos = $ventas->sum('cantidad');

        $response = "📊 REPORTE DE VENTAS\n";
        $response .= "══════════════════════════════════════\n";
        $response .= "Período: {$fechaDesde} a {$fechaHasta}\n\n";
        $response .= "📈 RESUMEN GENERAL:\n";
        $response .= "• Total ventas: {$totalVentas}\n";
        $response .= "• Ingresos totales: Bs. " . number_format($ingresosTotales, 2) . "\n";
        $response .= "• Promedio por venta: Bs. " . number_format($promedioVenta, 2) . "\n";
        $response .= "• Platos vendidos: {$platosVendidos}\n\n";

        // Top 5 productos más vendidos
        $top5 = Venta::select('receta_id',
                \DB::raw('SUM(cantidad) as total_vendido'),
                \DB::raw('SUM(total) as ingresos'),
                \DB::raw('COUNT(id) as num_ventas'),
                \DB::raw('AVG(precio) as precio_promedio'))
            ->whereBetween(\DB::raw('DATE(created_at)'), [$fechaDesde, $fechaHasta])
            ->groupBy('receta_id')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->with('receta')
            ->get();

        $response .= "🏆 TOP 5 PRODUCTOS MÁS VENDIDOS:\n";
        if ($top5->isEmpty()) {
            $response .= "No hay ventas en este período.\n";
        } else {
            $posicion = 1;
            foreach ($top5 as $item) {
                $response .= "{$posicion}. {$item->receta->nombre}\n";
                $response .= "   • Unidades: {$item->total_vendido} | Ingresos: Bs. " . number_format($item->ingresos, 2) . "\n";
                $response .= "   • Ventas: {$item->num_ventas} | Precio prom: Bs. " . number_format($item->precio_promedio, 2) . "\n\n";
                $posicion++;
            }
        }

        return $response;
    }

    /**
     * Generar reporte de ventas por período (auxiliar - legacy)
     */
    private function generarReporteVentasPorPeriodo($periodo = 'semanal')
    {
        $periodo = strtolower($periodo);

        switch ($periodo) {
            case 'semanal':
                $fechaInicio = now()->subDays(7);
                $titulo = 'ÚLTIMOS 7 DÍAS';
                break;
            case 'mensual':
                $fechaInicio = now()->subDays(30);
                $titulo = 'ÚLTIMOS 30 DÍAS';
                break;
            case 'anual':
                $fechaInicio = now()->subDays(365);
                $titulo = 'ÚLTIMO AÑO';
                break;
            default:
                return "❌ ERROR: Período inválido.\nUse: semanal, mensual o anual";
        }

        $ventas = Venta::where('created_at', '>=', $fechaInicio)->get();

        $totalVentas = $ventas->count();
        $ingresosTotales = $ventas->sum('total');
        $promedioVenta = $totalVentas > 0 ? $ingresosTotales / $totalVentas : 0;
        $platosVendidos = $ventas->sum('cantidad');

        $response = "📊 REPORTE DE VENTAS - {$titulo}\n";
        $response .= "══════════════════════════════════════\n\n";
        $response .= "📈 RESUMEN GENERAL:\n";
        $response .= "• Total ventas: {$totalVentas}\n";
        $response .= "• Ingresos totales: Bs. " . number_format($ingresosTotales, 2) . "\n";
        $response .= "• Promedio por venta: Bs. " . number_format($promedioVenta, 2) . "\n";
        $response .= "• Platos vendidos: {$platosVendidos}\n\n";

        // Top 3 productos más vendidos
        $top3 = Venta::select('receta_id', \DB::raw('SUM(cantidad) as total_vendido'), \DB::raw('SUM(total) as ingresos'))
            ->where('created_at', '>=', $fechaInicio)
            ->groupBy('receta_id')
            ->orderBy('total_vendido', 'desc')
            ->limit(3)
            ->with('receta')
            ->get();

        $response .= "🏆 TOP 3 PRODUCTOS MÁS VENDIDOS:\n";
        $posicion = 1;
        foreach ($top3 as $item) {
            $response .= "{$posicion}. {$item->receta->nombre} - {$item->total_vendido} unidades (Bs. " . number_format($item->ingresos, 2) . ")\n";
            $posicion++;
        }

        \App\Helpers\HistorialHelper::registrar(
            'Consultó reporte de ventas vía email',
            "Período: {$periodo}",
            'Reportes'
        );

        return $response;
    }

    /**
     * Generar reporte de inventario (auxiliar)
     */
    private function generarReporteInventario()
    {
        $insumos = Insumo::with(['unidad_medida'])->get();

        $response = "📦 REPORTE DE INVENTARIO\n";
        $response .= "══════════════════════════════════════\n\n";
        $response .= "📋 ESTADO DE INSUMOS:\n\n";

        $total = 0;
        $stockBajo = 0;
        $stockOk = 0;

        foreach ($insumos as $insumo) {
            $total++;
            $stockActual = $insumo->getCantidadTotal();
            $estado = $stockActual >= $insumo->stock_minimo ? '✅' : '⚠️';

            if ($stockActual < $insumo->stock_minimo) {
                $stockBajo++;
            } else {
                $stockOk++;
            }

            $response .= "{$estado} {$insumo->nombre}: " . number_format($stockActual, 2) .
                        "/" . number_format($insumo->stock_minimo, 2) .
                        " {$insumo->unidad_medida->abreviatura}\n";
        }

        $response .= "\n📊 RESUMEN:\n";
        $response .= "• Total insumos: {$total}\n";
        $response .= "• Stock OK: {$stockOk} (✅)\n";
        $response .= "• Stock bajo: {$stockBajo} (⚠️)\n";

        \App\Helpers\HistorialHelper::registrar(
            'Consultó reporte de inventario vía email',
            'Reporte completo de todos los insumos',
            'Reportes'
        );

        return $response;
    }

    /**
     * Generar reporte de productos más vendidos (auxiliar)
     */
    private function generarReporteProductosMasVendidos()
    {
        $productos = Venta::select('receta_id',
                \DB::raw('SUM(cantidad) as total_vendido'),
                \DB::raw('SUM(total) as ingresos_generados'),
                \DB::raw('COUNT(id) as numero_ventas'),
                \DB::raw('AVG(precio) as precio_promedio'))
            ->groupBy('receta_id')
            ->orderBy('total_vendido', 'desc')
            ->limit(10)
            ->with('receta')
            ->get();

        $response = "🏆 REPORTE - PRODUCTOS MÁS VENDIDOS\n";
        $response .= "══════════════════════════════════════\n\n";

        if ($productos->isEmpty()) {
            $response .= "No hay ventas registradas aún.\n";
        } else {
            $posicion = 1;
            foreach ($productos as $producto) {
                $response .= "{$posicion}. {$producto->receta->nombre}\n";
                $response .= "   • Unidades vendidas: {$producto->total_vendido}\n";
                $response .= "   • Ingresos: Bs. " . number_format($producto->ingresos_generados, 2) . "\n";
                $response .= "   • Número de ventas: {$producto->numero_ventas}\n";
                $response .= "   • Precio promedio: Bs. " . number_format($producto->precio_promedio, 2) . "\n\n";
                $posicion++;
            }
        }

        \App\Helpers\HistorialHelper::registrar(
            'Consultó reporte de productos más vendidos vía email',
            'Top 10 productos',
            'Reportes'
        );

        return $response;
    }

    /**
     * Generar reporte de insumos críticos (auxiliar)
     */
    private function generarReporteInsumosCriticos()
    {
        $insumosCriticos = Insumo::with(['unidad_medida', 'categoria'])->get()->filter(function ($insumo) {
            return $insumo->getCantidadTotal() < $insumo->stock_minimo;
        })->sortBy(function ($insumo) {
            return $insumo->getCantidadTotal() / $insumo->stock_minimo;
        });

        $response = "⚠️ REPORTE - INSUMOS CRÍTICOS\n";
        $response .= "══════════════════════════════════════\n\n";

        if ($insumosCriticos->isEmpty()) {
            $response .= "✅ No hay insumos críticos.\n";
            $response .= "Todos los insumos están por encima del stock mínimo.\n";
        } else {
            foreach ($insumosCriticos as $insumo) {
                $stockActual = $insumo->getCantidadTotal();
                $porcentaje = ($stockActual / $insumo->stock_minimo) * 100;

                $nivel = $porcentaje < 25 ? '🔴 CRÍTICO' :
                        ($porcentaje < 50 ? '🟠 BAJO' : '🟡 ALERTA');

                $response .= "{$nivel} {$insumo->nombre}\n";
                $response .= "   • Stock: " . number_format($stockActual, 2) . "/" .
                            number_format($insumo->stock_minimo, 2) .
                            " {$insumo->unidad_medida->abreviatura}\n";
                $response .= "   • Categoría: " . ($insumo->categoria->nombre ?? 'N/A') . "\n";
                $response .= "   • Nivel: " . number_format($porcentaje, 1) . "%\n\n";
            }

            $response .= "📊 RESUMEN:\n";
            $response .= "Total insumos críticos: " . $insumosCriticos->count() . "\n";
            $response .= "\n⚠️ Se recomienda reabastecer estos insumos pronto.\n";
        }

        \App\Helpers\HistorialHelper::registrar(
            'Consultó reporte de insumos críticos vía email',
            'Insumos bajo stock mínimo',
            'Reportes'
        );

        return $response;
    }

    // ========== MÉTODOS CRUD DE REPORTES PERSONALIZADOS (CU8) ==========

    /**
     * Crear reporte de ventas
     * Formato: CREAR REPORTE [nombre] [fecha_desde] [fecha_hasta] [guardar]
     */
    private function crearReporte($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 5) {
            return "Formato incorrecto.\nUse: CREAR REPORTE [nombre] [fecha_desde] [fecha_hasta] [guardar]\n\n" .
                   "Ejemplo: CREAR REPORTE VentasNoviembre 2025-11-1 2025-11-30\n" .
                   "Con guardar: CREAR REPORTE VentasNoviembre 2025-11-1 2025-11-30 guardar";
        }

        $nombre = $parts[2];
        $fechaDesde = $parts[3];
        $fechaHasta = $parts[4];
        $guardar = isset($parts[5]) && strtolower($parts[5]) === 'guardar';

        // Normalizar fechas
        try {
            $fechaDesde = $this->normalizarFecha($fechaDesde);
            $fechaHasta = $this->normalizarFecha($fechaHasta);
        } catch (\Exception $e) {
            return "❌ ERROR: Formato de fecha inválido.\nUse: YYYY-MM-DD o YYYY-M-D\nEjemplo: 2025-11-1 o 2025-11-01";
        }

        // Generar reporte
        $reporteResultado = $this->generarReporteVentasPorFechas($fechaDesde, $fechaHasta);

        // Si se pidió guardar
        if ($guardar) {
            $reporte = ReportePersonalizado::create([
                'nombre' => $nombre,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ]);

            \App\Helpers\HistorialHelper::registrar(
                'Creó y guardó reporte personalizado vía email',
                "ID: {$reporte->id}, Nombre: {$nombre}, Período: {$fechaDesde} a {$fechaHasta}",
                'Reportes'
            );

            return $reporteResultado . "\n\n" .
                   "✅ REPORTE GUARDADO EXITOSAMENTE\n" .
                   "ID: {$reporte->id} | Nombre: {$nombre}\n" .
                   "Puedes regenerarlo con: GENERAR REPORTE {$nombre} {$reporte->id}";
        }

        \App\Helpers\HistorialHelper::registrar(
            'Generó reporte temporal vía email',
            "Nombre: {$nombre}, Período: {$fechaDesde} a {$fechaHasta}",
            'Reportes'
        );

        return $reporteResultado;
    }

    /**
     * Consultar lista de reportes guardados
     */
    private function consultarReportes()
    {
        $reportes = ReportePersonalizado::orderBy('created_at', 'desc')->get();

        $response = "=== MIS REPORTES GUARDADOS ===\n\n";

        if ($reportes->isEmpty()) {
            $response .= "No tienes reportes guardados aún.\n\n";
            $response .= "Crea y guarda uno con:\n";
            $response .= "CREAR REPORTE [nombre] [fecha_desde] [fecha_hasta] guardar";
        } else {
            $count = 1;
            foreach ($reportes as $reporte) {
                $response .= "{$count}. [ID:{$reporte->id}] {$reporte->nombre}\n";
                $response .= "   Período: {$reporte->fecha_desde} a {$reporte->fecha_hasta}\n";
                $response .= "   Creado: " . $reporte->created_at->format('d/m/Y') . "\n\n";
                $count++;
            }

            $response .= "Total reportes: " . $reportes->count();
        }

        \App\Helpers\HistorialHelper::registrar(
            'Consultó lista de reportes guardados vía email',
            'Total: ' . $reportes->count(),
            'Reportes'
        );

        return $response;
    }

    /**
     * Consultar detalle de un reporte
     * Formato: CONSULTAR REPORTE [nombre] [id]
     */
    private function consultarReporte($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 4) {
            return "Formato incorrecto.\nUse: CONSULTAR REPORTE [nombre] [id]\n\nEjemplo: CONSULTAR REPORTE MisVentas 5";
        }

        $nombre = $parts[2];
        $reporteId = intval($parts[3]);

        $reporte = ReportePersonalizado::where('id', $reporteId)
            ->where('nombre', 'like', "%{$nombre}%")
            ->first();

        if (!$reporte) {
            return "❌ ERROR: No se encontró el reporte con nombre '{$nombre}' e ID: {$reporteId}";
        }

        $response = "=== REPORTE GUARDADO ===\n\n";
        $response .= "ID: {$reporte->id}\n";
        $response .= "Nombre: {$reporte->nombre}\n";
        $response .= "Período: {$reporte->fecha_desde} a {$reporte->fecha_hasta}\n";
        $response .= "Descripción: " . ($reporte->descripcion ?? 'N/A') . "\n";
        $response .= "Creado: " . $reporte->created_at->format('d/m/Y H:i') . "\n";
        $response .= "Última modificación: " . $reporte->updated_at->format('d/m/Y H:i') . "\n\n";
        $response .= "Para generar este reporte:\n";
        $response .= "GENERAR REPORTE {$reporte->nombre} {$reporte->id}";

        \App\Helpers\HistorialHelper::registrar(
            'Consultó detalle de reporte personalizado vía email',
            "ID: {$reporte->id}, Nombre: {$reporte->nombre}",
            'Reportes'
        );

        return $response;
    }

    /**
     * Generar/ejecutar un reporte personalizado
     * Formato: GENERAR REPORTE [nombre] [id]
     */
    private function generarReporte($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 4) {
            return "Formato incorrecto.\nUse: GENERAR REPORTE [nombre] [id]\n\nEjemplo: GENERAR REPORTE MisVentas 5";
        }

        $nombre = $parts[2];
        $reporteId = intval($parts[3]);

        $reporte = ReportePersonalizado::where('id', $reporteId)
            ->where('nombre', 'like', "%{$nombre}%")
            ->first();

        if (!$reporte) {
            return "❌ ERROR: No se encontró el reporte con nombre '{$nombre}' e ID: {$reporteId}";
        }

        // Generar PDF del reporte
        $pdf = $this->generarPDFReporte($reporte);
        $pdfPath = storage_path("app/reportes/reporte_{$reporte->id}.pdf");

        // Crear directorio si no existe
        if (!file_exists(storage_path('app/reportes'))) {
            mkdir(storage_path('app/reportes'), 0755, true);
        }

        $pdf->save($pdfPath);

        // Enviar email con el PDF adjunto
        $emailDestino = auth()->user()->email ?? 'scgrupo012tecnoweb@gmail.com';
        $asunto = "Reporte: {$reporte->nombre}";
        $mensaje = "Se adjunta el reporte '{$reporte->nombre}' generado para el período {$reporte->fecha_desde} a {$reporte->fecha_hasta}.";

        \App\Helpers\HistorialHelper::registrar(
            'Generó PDF de reporte vía email',
            "ID: {$reporte->id}, Nombre: {$reporte->nombre}, PDF enviado",
            'Reportes'
        );

        // Nota: El PDF se guarda y se envía por separado
        return "✅ REPORTE GENERADO Y ENVIADO\n\n" .
               "ID: {$reporte->id}\n" .
               "Nombre: {$reporte->nombre}\n" .
               "Período: {$reporte->fecha_desde} a {$reporte->fecha_hasta}\n\n" .
               "📧 El PDF del reporte ha sido generado y enviado a tu email.\n" .
               "📎 Nombre archivo: reporte_{$reporte->id}.pdf";
    }

    /**
     * Generar PDF del reporte
     */
    private function generarPDFReporte($reporte)
    {
        $ventas = Venta::with('receta')
            ->whereBetween(\DB::raw('DATE(created_at)'), [$reporte->fecha_desde, $reporte->fecha_hasta])
            ->get();

        $totalVentas = $ventas->count();
        $ingresosTotales = $ventas->sum('total');
        $promedioVenta = $totalVentas > 0 ? $ingresosTotales / $totalVentas : 0;
        $platosVendidos = $ventas->sum('cantidad');

        $top5 = Venta::select('receta_id',
                \DB::raw('SUM(cantidad) as total_vendido'),
                \DB::raw('SUM(total) as ingresos'))
            ->whereBetween(\DB::raw('DATE(created_at)'), [$reporte->fecha_desde, $reporte->fecha_hasta])
            ->groupBy('receta_id')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->with('receta')
            ->get();

        $data = [
            'reporte' => $reporte,
            'totalVentas' => $totalVentas,
            'ingresosTotales' => $ingresosTotales,
            'promedioVenta' => $promedioVenta,
            'platosVendidos' => $platosVendidos,
            'top5' => $top5,
            'ventas' => $ventas,
        ];

        return \PDF::loadView('reportes.pdf_ventas', $data);
    }

    /**
     * Actualizar reporte personalizado
     * Formato: ACTUALIZAR REPORTE [nombre] [id] [campo] [valor]
     */
    private function actualizarReporte($command)
    {
        $parts = explode(' ', $command, 6);

        if (count($parts) < 6) {
            return "Formato incorrecto.\nUse: ACTUALIZAR REPORTE [nombre] [id] [campo] [valor]\n\n" .
                   "Campos: nombre, tipo, periodo, descripcion\n" .
                   "Ejemplo: ACTUALIZAR REPORTE MisVentas 5 periodo mensual";
        }

        $nombre = $parts[2];
        $reporteId = intval($parts[3]);
        $campo = strtolower($parts[4]);
        $valor = $parts[5];

        $reporte = ReportePersonalizado::where('id', $reporteId)
            ->where('nombre', 'like', "%{$nombre}%")
            ->first();

        if (!$reporte) {
            return "❌ ERROR: No se encontró el reporte con nombre '{$nombre}' e ID: {$reporteId}.\n" .
                   "Use: CONSULTAR REPORTE\npara ver los reportes disponibles.";
        }

        $valorAnterior = '';

        switch ($campo) {
            case 'nombre':
                $valorAnterior = $reporte->nombre;
                $reporte->nombre = $valor;
                break;

            case 'fecha_desde':
                try {
                    $valor = $this->normalizarFecha($valor);
                } catch (\Exception $e) {
                    return "❌ ERROR: Formato de fecha inválido.\nUse: YYYY-MM-DD o YYYY-M-D";
                }
                $valorAnterior = $reporte->fecha_desde;
                $reporte->fecha_desde = $valor;
                break;

            case 'fecha_hasta':
                try {
                    $valor = $this->normalizarFecha($valor);
                } catch (\Exception $e) {
                    return "❌ ERROR: Formato de fecha inválido.\nUse: YYYY-MM-DD o YYYY-M-D";
                }
                $valorAnterior = $reporte->fecha_hasta;
                $reporte->fecha_hasta = $valor;
                break;

            case 'descripcion':
                $valorAnterior = $reporte->descripcion ?? 'N/A';
                $reporte->descripcion = $valor;
                break;

            default:
                return "❌ ERROR: Campo '{$campo}' no válido.\nCampos disponibles: nombre, fecha_desde, fecha_hasta, descripcion";
        }

        $reporte->save();

        \App\Helpers\HistorialHelper::registrar(
            'Actualizó reporte personalizado vía email',
            "ID: {$reporte->id}, Campo: {$campo}, Valor nuevo: {$valor}",
            'Reportes'
        );

        $response = "✅ REPORTE ACTUALIZADO\n\n";
        $response .= "ID: {$reporte->id}\n";
        $response .= "Nombre: {$reporte->nombre}\n\n";
        $response .= "Campo actualizado: {$campo}\n";
        $response .= "Valor anterior: {$valorAnterior}\n";
        $response .= "Valor nuevo: {$valor}";

        return $response;
    }

    /**
     * Eliminar reporte personalizado
     * Formato: ELIMINAR REPORTE [nombre] [id]
     */
    private function eliminarReporte($command)
    {
        $parts = explode(' ', $command);

        if (count($parts) < 4) {
            return "Formato incorrecto.\nUse: ELIMINAR REPORTE [nombre] [id]\n\nEjemplo: ELIMINAR REPORTE MisVentas 5";
        }

        $nombre = $parts[2];
        $reporteId = intval($parts[3]);

        $reporte = ReportePersonalizado::where('id', $reporteId)
            ->where('nombre', 'like', "%{$nombre}%")
            ->first();

        if (!$reporte) {
            return "❌ ERROR: No se encontró el reporte con nombre '{$nombre}' e ID: {$reporteId}.\n" .
                   "Use: CONSULTAR REPORTE\npara ver los reportes disponibles.";
        }

        $nombreReporte = $reporte->nombre;
        $tipo = $reporte->tipo;
        $periodo = $reporte->periodo;

        $reporte->delete();

        \App\Helpers\HistorialHelper::registrar(
            'Eliminó reporte personalizado vía email',
            "ID: {$reporteId}, Nombre: {$nombreReporte}",
            'Reportes'
        );

        $response = "✅ REPORTE ELIMINADO\n\n";
        $response .= "ID: {$reporteId}\n";
        $response .= "Nombre: {$nombreReporte}\n";
        $response .= "Tipo: {$tipo}\n";
        $response .= "Período: {$periodo}\n\n";
        $response .= "El reporte ha sido eliminado permanentemente.";

        return $response;
    }
}
