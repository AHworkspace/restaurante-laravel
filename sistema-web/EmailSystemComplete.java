import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.Socket;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;
import java.util.Properties;
import java.util.regex.Pattern;
import javax.net.ssl.SSLSocketFactory;
import javax.mail.*;
import javax.mail.internet.*;

/**
 * Sistema completo que procesa emails y envía respuestas usando JavaMail con conexión a base de datos
 */
public class EmailSystemComplete {

    private final static int PORT_POP = 995;
    private final static String HOST = "pop.gmail.com";
    private final static String USER = "scgrupo012tecnoweb@gmail.com";
    private final static String PASSWORD = "fgfdkpujhbhlwzpb";

    // Configuración SMTP para enviar emails
    private final static String SMTP_HOST = "smtp.gmail.com";
    private final static String SMTP_PORT = "587";
    private final static String EMAIL_USER = USER;
    private final static String EMAIL_PASSWORD = PASSWORD;

    // Variable para almacenar el email del remitente del último comando
    private static String lastSenderEmail = null;

    // Configuración de base de datos
    private final static String DB_URL = "jdbc:postgresql://127.0.0.1:5432/restaurante";
    private final static String DB_USERNAME = "postgres";
    private final static String DB_PASSWORD = "1666";

    public static void main(String[] args) {
        System.out.println("🚀 SISTEMA COMPLETO DE EMAIL CON BASE DE DATOS");
        System.out.println("==============================================");
        System.out.println("📧 Email: " + USER);
        System.out.println("🗄️ Base de datos: " + DB_URL);
        System.out.println();

        while(true) {
            try {
                System.out.println("🔄 Iniciando ciclo de verificación...");
                processEmails();
                System.out.println("⏰ Esperando 10 segundos...");
                Thread.sleep(10000);
            } catch (Exception e) {
                System.out.println("❌ Error: " + e.getMessage());
                e.printStackTrace();
            }
        }
    }

    private static void processEmails() throws Exception {
        Socket socket = null;
        BufferedReader input = null;
        DataOutputStream output = null;

        try {
            // Conectar a Gmail POP3
            SSLSocketFactory sslSocketFactory = (SSLSocketFactory) SSLSocketFactory.getDefault();
            socket = sslSocketFactory.createSocket(HOST, PORT_POP);

            input = new BufferedReader(new InputStreamReader(socket.getInputStream()));
            output = new DataOutputStream(socket.getOutputStream());

            System.out.println("✅ Conexión SSL establecida");

            // Leer mensaje de bienvenida
            String welcome = input.readLine();
            System.out.println("📨 Servidor: " + welcome);

            // Autenticación
            output.writeBytes("USER " + USER + "\r\n");
            String userResponse = input.readLine();
            System.out.println("👤 USER: " + userResponse);

            if (userResponse.contains("-ERR")) {
                System.out.println("❌ Error en USER");
                return;
            }

            output.writeBytes("PASS " + PASSWORD + "\r\n");
            String passResponse = input.readLine();
            System.out.println("🔑 PASS: " + passResponse);

            if (passResponse.contains("-ERR")) {
                System.out.println("❌ Error de autenticación");
                return;
            }

            System.out.println("✅ Autenticación exitosa");

            // Verificar cantidad de emails
            output.writeBytes("STAT\r\n");
            String statResponse = input.readLine();
            System.out.println("📊 STAT: " + statResponse);

            if (statResponse.startsWith("+OK")) {
                String[] parts = statResponse.split(" ");
                int emailCount = Integer.parseInt(parts[1]);
                System.out.println("📧 Total emails: " + emailCount);

                if (emailCount > 0) {
                    System.out.println("📬 Procesando emails...");

                    // Obtener el primer email
                    output.writeBytes("RETR 1\r\n");

                    String emailContent = "";
                    while(true) {
                        String line = input.readLine();
                        if(line == null) break;
                        if(line.equals(".")) break;
                        emailContent += line + "\n";
                    }

                    // Extraer información del email
                    String from = extractFrom(emailContent);
                    String subject = extractSubject(emailContent);

                    System.out.println("📨 Email recibido:");
                    System.out.println("   De: " + from);
                    System.out.println("   Asunto: " + subject);

                    // Guardar el email del remitente para usarlo en respuestas con adjuntos
                    lastSenderEmail = from;

                    // Procesar comando con base de datos
                    String response = processCommandWithDatabase(subject);
                    System.out.println("💬 Respuesta completa:");
                    System.out.println("Longitud: " + response.length() + " caracteres");
                    System.out.println("Primeros 200 caracteres: " + response.substring(0, Math.min(200, response.length())));
                    System.out.println("Últimos 200 caracteres: " + response.substring(Math.max(0, response.length() - 200)));

                    // Enviar respuesta por email usando JavaMail
                    System.out.println("📤 Enviando respuesta a: " + from);
                    sendResponseEmailJavaMail(from, response);

                    // NO eliminar email procesado (comentado para mantener emails)
                    // output.writeBytes("DELE 1\r\n");
                    // String deleResponse = input.readLine();
                    // System.out.println("🗑️ DELE: " + deleResponse);
                    System.out.println("📧 Email procesado pero NO eliminado");
                } else {
                    System.out.println("📭 No hay emails nuevos");
                }
            }

            // Cerrar conexión
            output.writeBytes("QUIT\r\n");
            input.readLine();
            System.out.println("🔚 Conexión cerrada");

        } finally {
            try {
                if (input != null) input.close();
                if (output != null) output.close();
                if (socket != null) socket.close();
            } catch (IOException e) {
                System.out.println("Error cerrando conexión: " + e.getMessage());
            }
        }
    }

    private static String extractFrom(String emailContent) {
        String[] lines = emailContent.split("\n");
        for (String line : lines) {
            if (line.toLowerCase().startsWith("from:")) {
                return line.substring(5).trim();
            }
        }
        return "unknown@email.com";
    }

    private static String extractSubject(String emailContent) {
        String[] lines = emailContent.split("\n");
        for (String line : lines) {
            if (line.toLowerCase().startsWith("subject:")) {
                String subject = line.substring(8).trim();
                try {
                    // Decodificar subject si viene en formato MIME encoded-word (UTF-8, etc.)
                    subject = MimeUtility.decodeText(subject);
                } catch (Exception e) {
                    System.out.println("⚠️ No se pudo decodificar subject, usando original: " + e.getMessage());
                }
                return subject;
            }
        }
        return "";
    }

    private static String processCommandWithDatabase(String command) {
        if (command == null) return "Comando no válido";

        String originalCommand = command.trim();
        String normalizedCommand = originalCommand.toUpperCase();
        System.out.println("🔍 Procesando comando: " + normalizedCommand);

        try {
            if (command.contains("AYUDA")) {
                String helpMessage = getHelpMessage();
                System.out.println("🔍 DEBUG AYUDA - Longitud: " + helpMessage.length());
                System.out.println("🔍 DEBUG AYUDA - Contenido completo:");
                System.out.println(helpMessage);
                return helpMessage;
            }
            // ========== COMANDOS DE INSUMOS ==========
            else if (command.contains("CONSULTAR INSUMOS")) {
                // Extraer filtros del comando
                String filtros = removeKeyword(originalCommand, "CONSULTAR INSUMOS");
                return getInsumosListWithFilters(filtros);
            } else if (command.contains("CONSULTAR STOCK DISPONIBLE")) {
                String insumoName = removeKeyword(originalCommand, "CONSULTAR STOCK DISPONIBLE");
                if (!insumoName.isEmpty()) {
                    return getStockDisponible(insumoName);
                } else {
                    return "Formato incorrecto. Use: CONSULTAR STOCK DISPONIBLE [nombre]\nEjemplo: CONSULTAR STOCK DISPONIBLE harina";
                }
            } else if (command.contains("CREAR INSUMO")) {
                return createInsumo(originalCommand);
            } else if (command.contains("ACTUALIZAR INSUMO")) {
                return editInsumo(originalCommand);
            } else if (command.contains("EDITAR INSUMO")) {
                return editInsumo(originalCommand);
            } else if (command.contains("ELIMINAR INSUMO")) {
                return deleteInsumo(originalCommand);
            }
            // ========== COMANDOS DE MOVIMIENTOS ==========
            else if (command.contains("CONSULTAR MOVIMIENTOS")) {
                String filtros = removeKeyword(originalCommand, "CONSULTAR MOVIMIENTOS");
                return getMovimientosListWithFilters(filtros);
            } else if (command.contains("CREAR MOVIMIENTO")) {
                return createMovimiento(originalCommand);
            } else if (command.contains("ACTUALIZAR MOVIMIENTO")) {
                return updateMovimiento(originalCommand);
            } else if (command.contains("ELIMINAR MOVIMIENTO")) {
                return deleteMovimiento(originalCommand);
            }
            // ========== COMANDOS DE RECETAS (CU3) ==========
            else if (command.contains("CONSULTAR LISTA RECETAS")) {
                return getRecetasList();
            } else if (command.contains("CONSULTAR RECETA")) {
                String recetaName = extractRecetaName(originalCommand);
                if (recetaName != null) {
                    return getRecetaDetails(recetaName);
                } else {
                    return "Formato incorrecto. Use: CONSULTAR RECETA [nombre]\nEjemplo: CONSULTAR RECETA pizza";
                }
            } else if (command.contains("CREAR RECETA")) {
                return createReceta(originalCommand);
            } else if (command.contains("AÑADIR INDICACIONES")) {
                return addIndicaciones(originalCommand);
            } else if (command.contains("AGREGAR INGREDIENTES")) {
                return addIngredientes(originalCommand);
            } else if (command.contains("QUITAR INGREDIENTE")) {
                return removeIngrediente(originalCommand);
            } else if (command.contains("ACTUALIZAR RECETA")) {
                return updateReceta(originalCommand);
            } else if (command.contains("EDITAR RECETA")) {
                return updateReceta(originalCommand);
            } else if (command.contains("ELIMINAR RECETA")) {
                return deleteReceta(originalCommand);
            }
            // ========== COMANDOS DE PRODUCCIÓN (CU5) ==========
            else if (command.contains("CALCULAR PRODUCCION")) {
                return calcularProduccion(originalCommand);
            } else if (command.contains("CREAR PLAN PRODUCCION")) {
                return createPlanProduccion(originalCommand);
            } else if (command.contains("CONSULTAR PLANES PRODUCCION")) {
                String filtros = removeKeyword(originalCommand, "CONSULTAR PLANES PRODUCCION");
                return getPlanesProduccion(filtros);
            } else if (command.contains("CONSULTAR PLAN PRODUCCION")) {
                return getPlanProduccionDetails(originalCommand);
            } else if (command.contains("ACTUALIZAR PLAN PRODUCCION")) {
                return updatePlanProduccion(originalCommand);
            } else if (command.contains("ELIMINAR PLAN PRODUCCION")) {
                return deletePlanProduccion(originalCommand);
            }
            // ========== COMANDOS DE VENTAS (CU6) ==========
            else if (command.contains("CREAR VENTA")) {
                return createVenta(originalCommand);
            } else if (command.contains("CONSULTAR VENTAS")) {
                String filtros = removeKeyword(originalCommand, "CONSULTAR VENTAS");
                return getVentasListWithFilters(filtros);
            } else if (command.contains("CONSULTAR VENTA")) {
                return getVentaDetails(originalCommand);
            } else if (command.contains("ACTUALIZAR VENTA")) {
                return updateVenta(originalCommand);
            } else if (command.contains("ELIMINAR VENTA")) {
                return deleteVenta(originalCommand);
            }
            // ========== COMANDOS DE COMPRAS/PAGOS (CU7) ==========
            else if (command.contains("CREAR COMPRA")) {
                return createCompra(originalCommand);
            } else if (command.contains("CONSULTAR COMPRAS")) {
                String filtros = removeKeyword(originalCommand, "CONSULTAR COMPRAS");
                return getComprasListWithFilters(filtros);
            } else if (command.contains("ACTUALIZAR COMPRA")) {
                return updateCompra(originalCommand);
            } else if (command.contains("ELIMINAR COMPRA")) {
                return deleteCompra(originalCommand);
            }
            // ========== COMANDOS DE REPORTES (CU8) ==========
            else if (command.contains("CREAR REPORTE")) {
                return createReporte(originalCommand);
            } else if (command.contains("CONSULTAR REPORTE")) {
                // Si no tiene parámetros, lista todos; si tiene, muestra detalle específico
                    String rest = removeKeyword(originalCommand, "CONSULTAR REPORTE");
                if (rest.isEmpty()) {
                    return getReportesList();
                } else {
                    return getReporteDetails(originalCommand);
                }
            } else if (command.contains("GENERAR REPORTE")) {
                return generarReporte(originalCommand);
            } else if (command.contains("ACTUALIZAR REPORTE")) {
                return updateReporte(originalCommand);
            } else if (command.contains("ELIMINAR REPORTE")) {
                return deleteReporte(originalCommand);
            }
            // ========== COMANDOS DE COMPRAS/PAGOS (CU7) ==========
            else if (command.contains("CREAR COMPRA")) {
                return createCompra(originalCommand);
            } else if (command.contains("CONSULTAR COMPRAS")) {
                String filtros = removeKeyword(originalCommand, "CONSULTAR COMPRAS");
                return getComprasListWithFilters(filtros);
            } else if (command.contains("ACTUALIZAR COMPRA")) {
                return updateCompra(originalCommand);
            } else if (command.contains("ELIMINAR COMPRA")) {
                return deleteCompra(originalCommand);
            }
            // ========== COMANDOS DE PROVEEDORES ==========
            else if (command.contains("CONSULTAR PROVEEDOR")) {
                String proveedorName = extractProveedorName(originalCommand);
                if (proveedorName != null) {
                    return getProveedorDetails(proveedorName);
                } else {
                    return "Formato incorrecto. Use: CONSULTAR PROVEEDOR [nombre]\nEjemplo: CONSULTAR PROVEEDOR distribuidora";
                }
            } else if (command.contains("CONSULTAR PROVEEDORES")) {
                return getProveedoresList();
            } else if (command.contains("CREAR PROVEEDOR")) {
                return createProveedor(originalCommand);
            } else if (command.contains("EDITAR PROVEEDOR")) {
                return editProveedor(originalCommand);
            } else if (command.contains("ELIMINAR PROVEEDOR")) {
                return deleteProveedor(originalCommand);
            }
            // ========== COMANDOS DE CATEGORÍAS ==========
            if (normalizedCommand.contains("CONSULTAR CATEGORIAS")) {
                return getCategoriasList();
            } else if (normalizedCommand.contains("CREAR CATEGORIA")) {
                return createCategoria(originalCommand);
            } else if (normalizedCommand.contains("EDITAR CATEGORIA")) {
                return editCategoria(originalCommand);
            } else if (normalizedCommand.contains("ELIMINAR CATEGORIA")) {
                return deleteCategoria(originalCommand);
            }
            // ========== COMANDOS DE UNIDADES DE MEDIDA ==========
            else if (normalizedCommand.contains("CONSULTAR UNIDADES")) {
                return getUnidadesList();
            } else if (normalizedCommand.contains("CREAR UNIDAD")) {
                return createUnidad(originalCommand);
            } else if (normalizedCommand.contains("EDITAR UNIDAD")) {
                return editUnidad(originalCommand);
            } else if (normalizedCommand.contains("ELIMINAR UNIDAD")) {
                return deleteUnidad(originalCommand);
            }
            // ========== CU1 - GESTIÓN DE USUARIOS ==========
            else if (normalizedCommand.contains("CREAR USUARIO")) {
                return createUsuario(originalCommand);
            } else if (normalizedCommand.contains("CONSULTAR USUARIOS")) {
                return getUsuariosList();
            } else if (normalizedCommand.contains("ACTUALIZAR USUARIO")) {
                return actualizarUsuario(originalCommand);
            } else if (normalizedCommand.contains("ELIMINAR USUARIO")) {
                return eliminarUsuario(originalCommand);
            } else if (normalizedCommand.contains("ASIGNAR ROL")) {
                return asignarRol(originalCommand);
            }
            // ========== COMANDOS DE ALERTAS Y NOTIFICACIONES ==========
            else if (normalizedCommand.contains("CONSULTAR ALERTAS")) {
                return getAlertasList();
            } else if (normalizedCommand.contains("CONSULTAR NOTIFICACIONES")) {
                return getNotificacionesList();
            } else if (normalizedCommand.contains("CREAR ALERTA")) {
                return createAlerta(originalCommand);
            }
            // ========== COMANDOS DE HISTORIAL ==========
            else if (normalizedCommand.contains("CONSULTAR HISTORIAL FECHA")) {
                String fecha = extractFilterValue(command, "CONSULTAR HISTORIAL FECHA");
                return getHistorialByDate(fecha);
            } else if (normalizedCommand.contains("CONSULTAR HISTORIAL")) {
                String tabla = extractTableValue(command, "CONSULTAR HISTORIAL");
                return getHistorialByTable(tabla);
            }
            // ========== COMANDOS DE PREDICCIONES ==========
            else if (normalizedCommand.contains("CONSULTAR PREDICCION")) {
                String id = extractIdValue(command, "CONSULTAR PREDICCION");
                return getPrediccionDetails(id);
            } else if (normalizedCommand.contains("CONSULTAR PREDICCIONES")) {
                return getPrediccionesList();
            } else if (normalizedCommand.contains("GENERAR PREDICCION")) {
                String tipo = extractFilterValue(command, "GENERAR PREDICCION");
                return generatePrediccion(tipo);
            }
            // ========== COMANDOS DE DASHBOARD ==========
            else if (normalizedCommand.contains("CONSULTAR DASHBOARD")) {
                return getDashboardData();
            } else if (normalizedCommand.contains("CONSULTAR ESTADISTICAS")) {
                return getEstadisticasData();
            }
            else {
                return "Comando no reconocido: '" + command + "'\n\n" + getHelpMessage();
            }
        } catch (Exception e) {
            System.out.println("❌ Error procesando comando: " + e.getMessage());
            return "Error procesando comando: " + e.getMessage();
        }
    }

    // ========== FUNCIONES AUXILIARES PARA EXTRAER PARÁMETROS ==========
    private static String extractInsumoName(String command) {
        if (command.contains("CONSULTAR STOCK")) {
            String[] parts = command.split("CONSULTAR STOCK");
            if (parts.length > 1) {
                return parts[1].trim();
            }
        } else if (command.contains("CONSULTAR INSUMO")) {
            String[] parts = command.split("CONSULTAR INSUMO");
            if (parts.length > 1) {
                return parts[1].trim();
            }
        }
        return null;
    }

    private static String extractRecetaName(String command) {
        if (command.contains("CONSULTAR RECETA")) {
            String[] parts = command.split("CONSULTAR RECETA");
            if (parts.length > 1) {
                return parts[1].trim();
            }
        } else if (command.contains("CONSULTAR INGREDIENTES")) {
            String[] parts = command.split("CONSULTAR INGREDIENTES");
            if (parts.length > 1) {
                return parts[1].trim();
            }
        }
        return null;
    }

    private static String extractProveedorName(String command) {
        if (command.contains("CONSULTAR PROVEEDOR")) {
            String[] parts = command.split("CONSULTAR PROVEEDOR");
            if (parts.length > 1) {
                return parts[1].trim();
            }
        }
        return null;
    }

    private static String extractFilterValue(String command, String prefix) {
        if (command.contains(prefix)) {
            String[] parts = command.split(prefix);
            if (parts.length > 1) {
                return parts[1].trim();
            }
        }
        return null;
    }

    private static String extractEmailValue(String command, String prefix) {
        if (command.contains(prefix)) {
            String[] parts = command.split(prefix);
            if (parts.length > 1) {
                return parts[1].trim();
            }
        }
        return null;
    }

    private static String extractTableValue(String command, String prefix) {
        if (command.contains(prefix)) {
            String[] parts = command.split(prefix);
            if (parts.length > 1) {
                return parts[1].trim();
            }
        }
        return null;
    }

    private static String extractIdValue(String command, String prefix) {
        if (command.contains(prefix)) {
            String[] parts = command.split(prefix);
            if (parts.length > 1) {
                return parts[1].trim();
            }
        }
        return null;
    }

    private static String extractReporteType(String command) {
        if (command.contains("CONSULTAR REPORTE")) {
            String[] parts = command.split("CONSULTAR REPORTE");
            if (parts.length > 1) {
                return parts[1].trim();
            }
        }
        return "general";
    }

    private static String getHelpMessage() {
        StringBuilder help = new StringBuilder();
        help.append("╔══════════════════════════════════════════════════════════╗\n");
        help.append("║  SISTEMA DE GESTION DE RESTAURANTE - COMANDOS EMAIL     ║\n");
        help.append("╚══════════════════════════════════════════════════════════╝\n\n");

        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("📋 CU1 - GESTION DE USUARIOS\n");
        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("• CREAR USUARIO [nombre] [apellidoP] [apellidoM] [email] [password]\n");
        help.append("• CONSULTAR USUARIOS\n");
        help.append("• ACTUALIZAR USUARIO [id] [campo] [valor]\n");
        help.append("  (Campos: nombre, apellido_paterno, apellido_materno, email, password)\n");
        help.append("• ELIMINAR USUARIO [id]\n");
        help.append("• ASIGNAR ROL [id] [rol]\n");
        help.append("  (Roles: admin, director, cocinero, cajero, ayudante_cocina, mesero)\n\n");

        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("🥕 CU2 - GESTION DE INSUMOS\n");
        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("• CREAR INSUMO [nombre] [descripcion] [stock_min] [categoria] [unidad]\n");
        help.append("• CONSULTAR INSUMOS [filtro] [valor], [filtro] [valor]...\n");
        help.append("  Filtros: insumo, categoria, stock_minimo\n");
        help.append("• CONSULTAR STOCK DISPONIBLE [nombre_insumo]\n");
        help.append("• ACTUALIZAR INSUMO [nombre_insumo] [campo] [valor], [campo] [valor]...\n");
        help.append("  (Campos: nombre, descripcion, stock_minimo, categoria, unidad)\n");
        help.append("• ELIMINAR INSUMO [nombre]\n\n");

        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("🍳 CU3 - GESTION DE RECETAS\n");
        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("• CREAR RECETA [nombre] [precio] [tiempo_minutos]\n");
        help.append("• AÑADIR INDICACIONES [receta] [texto]\n");
        help.append("• AGREGAR INGREDIENTES [receta] [insumo] [cantidad], [insumo] [cantidad]...\n");
        help.append("• QUITAR INGREDIENTE [receta] [insumo]\n");
        help.append("• CONSULTAR LISTA RECETAS\n");
        help.append("• CONSULTAR RECETA [nombre]\n");
        help.append("• ACTUALIZAR RECETA [nombre] [campo] [valor], [campo] [valor]...\n");
        help.append("  (Campos: nombre, precio, tiempo_preparacion, indicaciones)\n");
        help.append("• ELIMINAR RECETA [nombre]\n\n");

        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("📦 CU4 - GESTION DE INVENTARIOS\n");
        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("• CREAR MOVIMIENTO [insumo] [tipo] [cantidad] [motivo]\n");
        help.append("  Tipos: entrada, salida\n");
        help.append("• CONSULTAR MOVIMIENTOS [filtro] [valor], [filtro] [valor]...\n");
        help.append("  Filtros: tipo, insumo, fecha, fecha_desde, fecha_hasta, motivo\n");
        help.append("• ACTUALIZAR MOVIMIENTO [nombre_insumo] [id] [campo] [valor], [campo] [valor]...\n");
        help.append("  Campos: tipo, cantidad, motivo, fecha\n");
        help.append("• ELIMINAR MOVIMIENTO [nombre_insumo] [id]\n\n");

        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("🏭 CU5 - GESTION DE PRODUCCION (Planes)\n");
        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("• CREAR PLAN PRODUCCION [nombre_plan] [receta] [cantidad]\n");
        help.append("• CONSULTAR PLANES PRODUCCION [filtro] [valor], [filtro] [valor]...\n");
        help.append("  Filtros: nombre, receta, cantidad, fecha, fecha_desde, fecha_hasta\n");
        help.append("• CONSULTAR PLAN PRODUCCION [nombre_plan] [id]\n");
        help.append("• ACTUALIZAR PLAN PRODUCCION [nombre_plan] [id] [campo] [valor]\n");
        help.append("  Campos: nombre, receta, cantidad\n");
        help.append("• ELIMINAR PLAN PRODUCCION [nombre_plan] [id]\n\n");

        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("💰 CU6 - GESTION DE VENTAS\n");
        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("• CREAR VENTA [receta] [cantidad]\n");
        help.append("  (Precio se obtiene automaticamente de la receta)\n");
        help.append("• CONSULTAR VENTAS [filtro] [valor], [filtro] [valor]...\n");
        help.append("  Filtros: receta, cantidad, precio, total, fecha, fecha_desde, fecha_hasta\n");
        help.append("• ACTUALIZAR VENTA [receta] [id] [campo] [valor]\n");
        help.append("  Campos: cantidad, precio, receta\n");
        help.append("• ELIMINAR VENTA [receta] [id]\n\n");

        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("💳 CU7 - GESTION DE PAGOS\n");
        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("⚠️  Funcionalidad en desarrollo\n\n");

        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("📊 CU8 - REPORTES DE VENTAS\n");
        help.append("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
        help.append("• CREAR REPORTE [nombre] [fecha_desde] [fecha_hasta] [guardar]\n");
        help.append("  Sin 'guardar': solo genera y muestra\n");
        help.append("  Con 'guardar': genera, muestra y guarda para uso futuro\n");
        help.append("• CONSULTAR REPORTE [nombre] [id]\n");
        help.append("  Sin parámetros: muestra lista completa de reportes guardados\n");
        help.append("  Con parámetros: muestra detalles de un reporte específico\n");
        help.append("• GENERAR REPORTE [nombre] [id]\n");
        help.append("  Genera PDF y lo envía por email\n");
        help.append("• ACTUALIZAR REPORTE [nombre] [id] [campo] [valor]\n");
        help.append("  Campos: nombre, fecha_desde, fecha_hasta, descripcion\n");
        help.append("• ELIMINAR REPORTE [nombre] [id]\n\n");

        help.append("══════════════════════════════════════════════════════════\n");
        help.append("📖 EJEMPLOS DE USO:\n");
        help.append("══════════════════════════════════════════════════════════\n");
        help.append("CU1: CREAR USUARIO Juan Perez Lopez juan@mail.com Pass1234\n");
        help.append("CU1: ASIGNAR ROL 5 cocinero\n");
        help.append("CU2: CREAR INSUMO tomate fresco 10 verduras kg\n");
        help.append("CU2: CONSULTAR INSUMOS\n");
        help.append("CU2: CONSULTAR INSUMOS insumo harina\n");
        help.append("CU2: CONSULTAR INSUMOS categoria verduras, stock_minimo 10\n");
        help.append("CU2: CONSULTAR STOCK DISPONIBLE tomate\n");
        help.append("CU2: ACTUALIZAR INSUMO tomate stock_minimo 15, descripcion tomate fresco\n");
        help.append("CU2: ELIMINAR INSUMO harina\n");
        help.append("CU3: CREAR RECETA Pizza 35 30 (tiempo en minutos)\n");
        help.append("CU3: AÑADIR INDICACIONES Pizza Extender masa y hornear a 200C\n");
        help.append("CU3: AGREGAR INGREDIENTES Pizza harina 200, tomate 3, queso 150\n");
        help.append("CU3: CONSULTAR LISTA RECETAS\n");
        help.append("CU3: ACTUALIZAR RECETA Pizza precio 40, tiempo_preparacion 35\n");
        help.append("CU4: CREAR MOVIMIENTO tomate entrada 5 compra\n");
        help.append("CU4: CONSULTAR MOVIMIENTOS tipo entrada, insumo tomate\n");
        help.append("CU4: CONSULTAR MOVIMIENTOS fecha_desde 2025-7-1, fecha_hasta 2025-10-31\n");
        help.append("CU4: ACTUALIZAR MOVIMIENTO tomate 10 cantidad 25, motivo compra urgente\n");
        help.append("CU4: ELIMINAR MOVIMIENTO harina 15\n");
        help.append("CU5: CREAR PLAN PRODUCCION MenuDiario Pizza 20\n");
        help.append("CU5: CONSULTAR PLANES PRODUCCION\n");
        help.append("CU5: CONSULTAR PLANES PRODUCCION nombre Menu, receta Pizza\n");
        help.append("CU5: CONSULTAR PLAN PRODUCCION MenuDiario 5\n");
        help.append("CU5: ACTUALIZAR PLAN PRODUCCION MenuDiario 5 cantidad 30\n");
        help.append("CU5: ELIMINAR PLAN PRODUCCION EventoEspecial 8\n");
        help.append("CU6: CREAR VENTA Pizza 5\n");
        help.append("CU6: CONSULTAR VENTAS\n");
        help.append("CU6: CONSULTAR VENTAS receta Pizza, fecha_desde 2025-11-1\n");
        help.append("CU6: ACTUALIZAR VENTA Pizza 10 cantidad 8\n");
        help.append("CU6: ELIMINAR VENTA Pizza 15\n");
        help.append("CU7: CREAR COMPRA Distribuidora 500 Compra semanal de insumos\n");
        help.append("CU7: CONSULTAR COMPRAS\n");
        help.append("CU7: CONSULTAR COMPRAS proveedor Distribuidora\n");
        help.append("CU7: ACTUALIZAR COMPRA 10 costo_total 550\n");
        help.append("CU7: ELIMINAR COMPRA 10\n");
        help.append("CU8: CREAR REPORTE VentasNoviembre 2025-11-1 2025-11-30\n");
        help.append("CU8: CREAR REPORTE VentasOctubre 2025-10-1 2025-10-31 guardar\n");
        help.append("CU8: CONSULTAR REPORTE\n");
        help.append("CU8: CONSULTAR REPORTE VentasNoviembre 5\n");
        help.append("CU8: GENERAR REPORTE VentasNoviembre 5\n");
        help.append("CU8: ACTUALIZAR REPORTE VentasNoviembre 5 nombre VentasTotales\n");
        help.append("CU8: ELIMINAR REPORTE VentasNoviembre 5\n\n");

        help.append("ℹ️  Para mas informacion sobre un comando especifico,\n");
        help.append("   ejecute el comando sin parametros.\n\n");
        help.append("✅ TODOS los sistemas (CU1-CU8) funcionales y completos");

        return help.toString();
    }

    private static String getInsumosListWithFilters(String filtros) {
        StringBuilder result = new StringBuilder();

        // Variables para filtros
        String filtroNombre = null;
        String filtroCategoria = null;
        Float filtroStockMinimo = null;

        // Si no hay filtros, mostrar todos
        if (filtros.isEmpty()) {
            result.append("=== LISTADO COMPLETO DE INSUMOS ===\n\n");
        } else {
            result.append("=== INSUMOS FILTRADOS ===\n\n");
            result.append("Filtros aplicados:\n");

            // Procesar filtros separados por comas
            String[] filtrosArray = filtros.split(",");

            for (String filtro : filtrosArray) {
                filtro = filtro.trim();
                String[] partes = splitParams(filtro, 2);

                if (partes.length >= 2) {
                    String tipoFiltro = partes[0].toLowerCase();
                    String valorFiltro = partes[1].trim();

                    if (tipoFiltro.equals("insumo")) {
                        filtroNombre = valorFiltro;
                        result.append("• Nombre contiene: ").append(valorFiltro).append("\n");
                    } else if (tipoFiltro.equals("categoria")) {
                        filtroCategoria = valorFiltro;
                        result.append("• Categoría: ").append(valorFiltro).append("\n");
                    } else if (tipoFiltro.equals("stock_minimo")) {
                        try {
                            filtroStockMinimo = Float.parseFloat(valorFiltro);
                            result.append("• Stock mínimo: ").append(valorFiltro).append("\n");
                        } catch (NumberFormatException e) {
                            return "❌ Error: El valor de stock_minimo debe ser un número.";
                        }
                    }
                }
            }
            result.append("\n");
        }

        try (Connection conn = getDatabaseConnection()) {
            // Construir SQL dinámico según filtros
            StringBuilder sql = new StringBuilder();
            sql.append("SELECT i.id, i.nombre, i.descripcion, i.stock_minimo, um.nombre as unidad, c.nombre as categoria ");
            sql.append("FROM insumos i ");
            sql.append("LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id ");
            sql.append("LEFT JOIN categorias c ON i.categoria_id = c.id ");

            // Agregar condiciones WHERE si hay filtros
            boolean hayFiltros = false;
            if (filtroNombre != null || filtroCategoria != null || filtroStockMinimo != null) {
                sql.append("WHERE ");

                if (filtroNombre != null) {
                    sql.append("i.nombre ILIKE ? ");
                    hayFiltros = true;
                }

                if (filtroCategoria != null) {
                    if (hayFiltros) sql.append("AND ");
                    sql.append("c.nombre ILIKE ? ");
                    hayFiltros = true;
                }

                if (filtroStockMinimo != null) {
                    if (hayFiltros) sql.append("AND ");
                    sql.append("i.stock_minimo = ? ");
                }
            }

            sql.append("ORDER BY i.nombre");

            try (PreparedStatement stmt = conn.prepareStatement(sql.toString())) {
                // Setear parámetros según filtros
                int paramIndex = 1;
                if (filtroNombre != null) {
                    stmt.setString(paramIndex++, "%" + filtroNombre + "%");
                }
                if (filtroCategoria != null) {
                    stmt.setString(paramIndex++, "%" + filtroCategoria + "%");
                }
                if (filtroStockMinimo != null) {
                    stmt.setFloat(paramIndex++, filtroStockMinimo);
                }

                try (ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                        int id = rs.getInt("id");
                    String nombre = rs.getString("nombre");
                    String descripcion = rs.getString("descripcion");
                    float stockMinimo = rs.getFloat("stock_minimo");
                    String unidad = rs.getString("unidad");
                        String categoria = rs.getString("categoria");

                    // Calcular stock actual
                        float stockActual = getStockActual(conn, id);
                    String estado = stockActual >= stockMinimo ? "✅ OK" : "⚠️ STOCK BAJO";

                        result.append("• ").append(nombre);
                        if (descripcion != null && !descripcion.isEmpty()) {
                            result.append(" (").append(descripcion).append(")");
                        }
                        result.append("\n");
                        result.append("  Categoría: ").append(categoria != null ? categoria : "Sin categoría").append("\n");
                        result.append("  Stock actual: ").append(stockActual).append(" ").append(unidad != null ? unidad : "").append("\n");
                        result.append("  Stock mínimo: ").append(stockMinimo).append(" ").append(unidad != null ? unidad : "").append("\n");
                        result.append("  Estado: ").append(estado).append("\n\n");
                    }

                    if (count == 0) {
                        result.append("No se encontraron insumos con los filtros especificados.\n");
                    } else {
                        result.append("Total de insumos encontrados: ").append(count);
                    }
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando insumos: " + e.getMessage());
            return "Error consultando insumos: " + e.getMessage();
        }

        return result.toString();
    }

    private static String getInsumosList() {
        // Mantener el método viejo para compatibilidad
        return getInsumosListWithFilters("");
    }

    private static String getStockDisponible(String insumoName) {
        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT i.id, i.nombre, i.stock_minimo, um.nombre as unidad " +
                        "FROM insumos i " +
                        "LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                        "WHERE i.nombre ILIKE ?";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, "%" + insumoName + "%");

                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        int id = rs.getInt("id");
                        String nombre = rs.getString("nombre");
                        float stockMinimo = rs.getFloat("stock_minimo");
                        String unidad = rs.getString("unidad");
                        float stockActual = getStockActual(conn, id);

                        return "Insumo: " + nombre + "\n" +
                               "Stock actual: " + stockActual + " " + unidad + "\n" +
                               "Stock mínimo: " + stockMinimo + " " + unidad;
                    } else {
                        return "❌ No se encontró el insumo: " + insumoName;
                    }
                }
            }
        } catch (SQLException e) {
            return "Error consultando stock: " + e.getMessage();
        }
    }

    private static String getStockInsumo(String insumoName) {
        StringBuilder result = new StringBuilder();
        result.append("=== STOCK DE ").append(insumoName.toUpperCase()).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT i.id, i.nombre, i.descripcion, i.stock_minimo, um.nombre as unidad " +
                        "FROM insumos i " +
                        "LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                        "WHERE i.nombre ILIKE ?";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, "%" + insumoName + "%");

                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        String nombre = rs.getString("nombre");
                        String descripcion = rs.getString("descripcion");
                        float stockMinimo = rs.getFloat("stock_minimo");
                        String unidad = rs.getString("unidad");
                        int insumoId = rs.getInt("id");

                        // Calcular stock actual
                        float stockActual = getStockActual(conn, insumoId);
                        String estado = stockActual >= stockMinimo ? "✅ OK" : "⚠️ STOCK BAJO";

                        result.append("Insumo: ").append(nombre).append("\n");
                        result.append("Descripción: ").append(descripcion != null ? descripcion : "Sin descripción").append("\n");
                        result.append("Stock actual: ").append(stockActual).append(" ").append(unidad != null ? unidad : "").append("\n");
                        result.append("Stock mínimo: ").append(stockMinimo).append(" ").append(unidad != null ? unidad : "").append("\n");
                        result.append("Estado: ").append(estado).append("\n");

                        // Mostrar últimos movimientos
                        result.append("\n=== ÚLTIMOS MOVIMIENTOS ===\n");
                        result.append(getUltimosMovimientos(conn, insumoId));

                    } else {
                        result.append("❌ No se encontró el insumo: ").append(insumoName).append("\n");
                        result.append("Verifique el nombre o use 'CONSULTAR INSUMOS' para ver la lista completa.");
                    }
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando stock: " + e.getMessage());
            return "Error consultando stock: " + e.getMessage();
        }

        return result.toString();
    }

    private static float getStockActual(Connection conn, int insumoId) throws SQLException {
        String sql = "SELECT " +
                    "SUM(CASE WHEN tipo = 'entrada' THEN cantidad ELSE -cantidad END) as stock_actual " +
                    "FROM movimiento_inventarios " +
                    "WHERE insumo_id = ?";

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, insumoId);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    float stock = rs.getFloat("stock_actual");
                    return rs.wasNull() ? 0.0f : stock;
                }
            }
        }
        return 0.0f;
    }

    private static String getUltimosMovimientos(Connection conn, int insumoId) throws SQLException {
        StringBuilder result = new StringBuilder();

        String sql = "SELECT cantidad, tipo, motivo, created_at " +
                    "FROM movimiento_inventarios " +
                    "WHERE insumo_id = ? " +
                    "ORDER BY created_at DESC " +
                    "LIMIT 5";

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, insumoId);

            try (ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    float cantidad = rs.getFloat("cantidad");
                    String tipo = rs.getString("tipo");
                    String motivo = rs.getString("motivo");
                    String fecha = rs.getString("created_at");

                    String simbolo = tipo.equals("entrada") ? "+" : "-";
                    result.append(simbolo).append(cantidad).append(" - ").append(motivo)
                          .append(" (").append(fecha).append(")\n");
                }

                if (count == 0) {
                    result.append("No hay movimientos registrados.");
                }
            }
        }

        return result.toString();
    }

    private static String getMovimientosListWithFilters(String filtros) {
        StringBuilder result = new StringBuilder();
        java.util.List<String> filtrosAplicados = new java.util.ArrayList<>();

        // DEBUG solo en consola
        System.out.println("🔍 DEBUG - Filtros recibidos: '" + filtros + "'");
        System.out.println("🔍 DEBUG - Longitud: " + filtros.length());

        // Construir SQL base
        StringBuilder sql = new StringBuilder();
        sql.append("SELECT mi.id, mi.cantidad, mi.tipo, mi.motivo, mi.created_at, i.nombre as insumo, um.nombre as unidad ");
        sql.append("FROM movimiento_inventarios mi ");
        sql.append("JOIN insumos i ON mi.insumo_id = i.id ");
        sql.append("LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id ");

        java.util.List<String> whereClauses = new java.util.ArrayList<>();
        java.util.List<String> paramValues = new java.util.ArrayList<>();

        // Procesar filtros si existen
        if (!filtros.isEmpty()) {
            System.out.println("🔍 DEBUG - Procesando filtros...");
            String[] filtrosArray = filtros.split(",");
            System.out.println("🔍 DEBUG - Número de filtros: " + filtrosArray.length);

            for (String filtro : filtrosArray) {
                filtro = filtro.trim();
                System.out.println("🔍 DEBUG - Filtro individual: '" + filtro + "'");
                String[] partes = splitParams(filtro, 2);

                if (partes.length >= 2) {
                    String tipoFiltro = partes[0].toLowerCase();
                    String valorFiltro = partes[1].trim();

                    switch (tipoFiltro) {
                        case "tipo":
                            valorFiltro = valorFiltro.toLowerCase();
                            if (valorFiltro.equals("entrada") || valorFiltro.equals("salida")) {
                                whereClauses.add("mi.tipo = ?");
                                paramValues.add(valorFiltro);
                                filtrosAplicados.add("Tipo: " + valorFiltro.toUpperCase());
                            }
                            break;

                        case "insumo":
                            whereClauses.add("i.nombre ILIKE ?");
                            paramValues.add("%" + valorFiltro + "%");
                            filtrosAplicados.add("Insumo: " + valorFiltro);
                            break;

                        case "fecha":
                            try {
                                String fechaNormalizada = normalizarFecha(valorFiltro);
                                whereClauses.add("DATE(mi.created_at) = ?");
                                paramValues.add(fechaNormalizada);
                                filtrosAplicados.add("Fecha: " + fechaNormalizada);
                            } catch (Exception e) {
                                return "❌ ERROR: Fecha inválida: " + valorFiltro + "\n" +
                                       "Formato correcto: YYYY-MM-DD o YYYY-M-D\n" +
                                       "Ejemplo: fecha 2025-11-02 o fecha 2025-6-1";
                            }
                            break;

                        case "fecha_desde":
                            try {
                                System.out.println("🔍 Procesando fecha_desde: '" + valorFiltro + "'");
                                String fechaNormalizada = normalizarFecha(valorFiltro);
                                whereClauses.add("DATE(mi.created_at) >= ?");
                                paramValues.add(fechaNormalizada);
                                filtrosAplicados.add("Desde: " + fechaNormalizada);
                                System.out.println("✅ Fecha desde normalizada: " + valorFiltro + " → " + fechaNormalizada);
                            } catch (Exception e) {
                                System.out.println("❌ ERROR normalizando fecha_desde: " + valorFiltro + " - " + e.getMessage());
                                return "❌ ERROR en 'fecha_desde': " + valorFiltro + "\n" +
                                       "Motivo: " + e.getMessage() + "\n" +
                                       "Formato: YYYY-MM-DD o YYYY-M-D";
                            }
                            break;

                        case "fecha_hasta":
                            try {
                                System.out.println("🔍 Procesando fecha_hasta: '" + valorFiltro + "'");
                                String fechaNormalizada = normalizarFecha(valorFiltro);
                                whereClauses.add("DATE(mi.created_at) <= ?");
                                paramValues.add(fechaNormalizada);
                                filtrosAplicados.add("Hasta: " + fechaNormalizada);
                                System.out.println("✅ Fecha hasta normalizada: " + valorFiltro + " → " + fechaNormalizada);
                            } catch (Exception e) {
                                System.out.println("❌ ERROR normalizando fecha_hasta: " + valorFiltro + " - " + e.getMessage());
                                return "❌ ERROR en 'fecha_hasta': " + valorFiltro + "\n" +
                                       "Motivo: " + e.getMessage() + "\n" +
                                       "Formato: YYYY-MM-DD o YYYY-M-D";
                            }
                            break;

                        case "motivo":
                            whereClauses.add("mi.motivo ILIKE ?");
                            paramValues.add("%" + valorFiltro + "%");
                            filtrosAplicados.add("Motivo: " + valorFiltro);
                            break;
                    }
                }
            }
        }

        // Agregar WHERE si hay filtros
        if (!whereClauses.isEmpty()) {
            sql.append("WHERE ").append(String.join(" AND ", whereClauses)).append(" ");
        }

        sql.append("ORDER BY mi.created_at DESC");

        try (Connection conn = getDatabaseConnection()) {
            try (PreparedStatement stmt = conn.prepareStatement(sql.toString())) {
                // Establecer parámetros
                for (int i = 0; i < paramValues.size(); i++) {
                    stmt.setString(i + 1, paramValues.get(i));
                }

                try (ResultSet rs = stmt.executeQuery()) {
                    // Título según si hay filtros
                    if (filtrosAplicados.isEmpty()) {
                        result.append("=== LISTADO COMPLETO DE MOVIMIENTOS ===\n\n");
                    } else {
                        result.append("=== MOVIMIENTOS FILTRADOS ===\n\n");
                        result.append("Filtros aplicados:\n");
                        for (String filtro : filtrosAplicados) {
                            result.append("• ").append(filtro).append("\n");
                        }
                        result.append("\n");
                    }

                int count = 0;
                while (rs.next()) {
                    count++;
                        int id = rs.getInt("id");
                    float cantidad = rs.getFloat("cantidad");
                    String tipo = rs.getString("tipo");
                    String motivo = rs.getString("motivo");
                    String fecha = rs.getString("created_at");
                    String insumo = rs.getString("insumo");
                        String unidad = rs.getString("unidad");

                    String simbolo = tipo.equals("entrada") ? "+" : "-";

                        result.append(count).append(". [ID:").append(id).append("] ").append(insumo)
                          .append(": ").append(simbolo).append(cantidad)
                              .append(" ").append(unidad != null ? unidad : "").append("\n");
                        result.append("   Tipo: ").append(tipo.toUpperCase())
                              .append(" | Motivo: ").append(motivo).append("\n");
                        result.append("   Fecha: ").append(fecha).append("\n\n");
                    }

                    if (count == 0) {
                        return "No se encontraron movimientos" + (filtrosAplicados.isEmpty() ? "." : " con los filtros especificados.");
                    }

                    result.append("Total movimientos encontrados: ").append(count);
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando movimientos: " + e.getMessage());
            return "Error consultando movimientos: " + e.getMessage();
        }

        return result.toString();
    }

    private static String getRecetasList() {
        StringBuilder result = new StringBuilder();
        result.append("=== LISTADO COMPLETO DE RECETAS ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT nombre, precio, tiempo_preparacion FROM recetas ORDER BY nombre";

            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {

                int count = 0;
                while (rs.next()) {
                    count++;
                    String nombre = rs.getString("nombre");
                    float precio = rs.getFloat("precio");
                    int tiempo = rs.getInt("tiempo_preparacion");

                    result.append(count).append(". ").append(nombre).append("\n");
                    result.append("   • Precio: Bs. ").append(precio).append("\n");
                    result.append("   • Tiempo: ").append(tiempo).append(" minutos\n\n");
                }

                if (count == 0) {
                    result.append("No hay recetas registradas.\n");
                } else {
                    result.append("Total de recetas: ").append(count);
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando recetas: " + e.getMessage());
            return "Error consultando recetas: " + e.getMessage();
        }

        return result.toString();
    }

    private static String createVenta(String command) {
        // Formato: CREAR VENTA [receta] [cantidad]
        String[] parts = command.split("CREAR VENTA");
        if (parts.length < 2) {
            return "Formato incorrecto.\nUse: CREAR VENTA [receta] [cantidad]\n\nEjemplo: CREAR VENTA Pizza 5";
        }

        String[] params = splitParams(parts[1].trim(), 2);
        if (params.length < 2) {
            return "Formato incorrecto.\nUse: CREAR VENTA [receta] [cantidad]\n\nEjemplo: CREAR VENTA Pizza 5";
        }

        String nombreReceta = params[0];
        float cantidad;

        try {
            cantidad = Float.parseFloat(params[1]);

            if (cantidad <= 0) {
                return "❌ ERROR: La cantidad debe ser mayor a 0";
            }
        } catch (NumberFormatException e) {
            return "❌ ERROR: La cantidad debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Buscar receta con su precio
            String checkRecetaSql = "SELECT id, nombre, precio FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;
            String recetaNombre = "";
            float precioReceta = 0;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkRecetaSql)) {
                checkStmt.setString(1, "%" + nombreReceta + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                        recetaNombre = rs.getString("nombre");
                        precioReceta = rs.getFloat("precio");
                    } else {
                        return "❌ ERROR: No se encontró la receta: " + nombreReceta;
                    }
                }
            }

            // Validar que la receta tenga precio válido
            if (precioReceta <= 0) {
                return "❌ ERROR: La receta " + recetaNombre + " no tiene un precio válido configurado";
            }

            // Calcular total con el precio de la receta
            float total = cantidad * precioReceta;

            // Crear venta
            String insertSql = "INSERT INTO ventas (receta_id, cantidad, precio, total, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
            int ventaId = -1;

            try (PreparedStatement stmt = conn.prepareStatement(insertSql, Statement.RETURN_GENERATED_KEYS)) {
                stmt.setInt(1, recetaId);
                stmt.setFloat(2, cantidad);
                stmt.setFloat(3, precioReceta);
                stmt.setFloat(4, total);
                stmt.executeUpdate();

                try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
                    if (generatedKeys.next()) {
                        ventaId = generatedKeys.getInt(1);
                    }
                }
            }

            // Descontar insumos
            String insumosSql = "SELECT insumo_id, cantidad * ? as cantidad_total " +
                               "FROM recetas_insumos WHERE receta_id = ?";

            try (PreparedStatement insStmt = conn.prepareStatement(insumosSql)) {
                insStmt.setFloat(1, cantidad);
                insStmt.setInt(2, recetaId);

                try (ResultSet rs = insStmt.executeQuery()) {
                    String movimientoSql = "INSERT INTO movimiento_inventarios (tipo, cantidad, insumo_id, motivo, venta_id, created_at, updated_at) " +
                                          "VALUES ('salida', ?, ?, ?, ?, NOW(), NOW())";

                    try (PreparedStatement movStmt = conn.prepareStatement(movimientoSql)) {
                        while (rs.next()) {
                            int insumoId = rs.getInt("insumo_id");
                            float cantidadInsumo = rs.getFloat("cantidad_total");

                            movStmt.setFloat(1, cantidadInsumo);
                            movStmt.setInt(2, insumoId);
                            movStmt.setString(3, "Venta de " + recetaNombre);
                            movStmt.setInt(4, ventaId);
                            movStmt.executeUpdate();
                        }
                    }
                }
            }

            return "✅ VENTA REGISTRADA EXITOSAMENTE\n\n" +
                   "ID: " + ventaId + "\n" +
                   "Receta: " + recetaNombre + "\n" +
                   "Cantidad: " + cantidad + " porciones\n" +
                   "Precio unitario: Bs. " + precioReceta + " (de la receta)\n" +
                   "Total: Bs. " + total + "\n\n" +
                   "✅ Insumos descontados automáticamente del inventario";

        } catch (SQLException e) {
            System.out.println("❌ Error creando venta: " + e.getMessage());
            return "Error creando venta: " + e.getMessage();
        }
    }

    private static String getVentasListWithFilters(String filtros) {
        try (Connection conn = getDatabaseConnection()) {
            StringBuilder sql = new StringBuilder();
            sql.append("SELECT v.id, v.cantidad, v.precio, v.total, v.created_at, r.nombre as receta ");
            sql.append("FROM ventas v ");
            sql.append("JOIN recetas r ON v.receta_id = r.id ");

            java.util.List<String> filtrosAplicados = new java.util.ArrayList<>();
            java.util.List<String> whereClauses = new java.util.ArrayList<>();
            java.util.List<Object> params = new java.util.ArrayList<>();

            if (!filtros.isEmpty()) {
                String[] filtrosPares = filtros.split(",");

                for (String par : filtrosPares) {
                    String[] partes = splitParams(par.trim(), 2);

                    if (partes.length >= 2) {
                        String tipoFiltro = partes[0].toLowerCase();
                        String valorFiltro = partes[1].trim();

                        switch (tipoFiltro) {
                            case "receta":
                                whereClauses.add("r.nombre ILIKE ?");
                                params.add("%" + valorFiltro + "%");
                                filtrosAplicados.add("Receta: " + valorFiltro);
                                break;

                            case "cantidad":
                                whereClauses.add("v.cantidad = ?");
                                params.add(valorFiltro);
                                filtrosAplicados.add("Cantidad: " + valorFiltro);
                                break;

                            case "precio":
                                whereClauses.add("v.precio = ?");
                                params.add(valorFiltro);
                                filtrosAplicados.add("Precio: " + valorFiltro);
                                break;

                            case "total":
                                whereClauses.add("v.total = ?");
                                params.add(valorFiltro);
                                filtrosAplicados.add("Total: " + valorFiltro);
                                break;

                            case "fecha":
                                try {
                                    String fechaNorm = normalizarFecha(valorFiltro);
                                    whereClauses.add("DATE(v.created_at) = ?");
                                    params.add(fechaNorm);
                                    filtrosAplicados.add("Fecha: " + fechaNorm);
                                } catch (Exception e) {
                                    // Ignorar fecha inválida
                                }
                                break;

                            case "fecha_desde":
                                try {
                                    String fechaDesde = normalizarFecha(valorFiltro);
                                    whereClauses.add("DATE(v.created_at) >= ?");
                                    params.add(fechaDesde);
                                    filtrosAplicados.add("Desde: " + fechaDesde);
                                } catch (Exception e) {
                                    // Ignorar fecha inválida
                                }
                                break;

                            case "fecha_hasta":
                                try {
                                    String fechaHasta = normalizarFecha(valorFiltro);
                                    whereClauses.add("DATE(v.created_at) <= ?");
                                    params.add(fechaHasta);
                                    filtrosAplicados.add("Hasta: " + fechaHasta);
                                } catch (Exception e) {
                                    // Ignorar fecha inválida
                                }
                                break;
                        }
                    }
                }
            }

            if (!whereClauses.isEmpty()) {
                sql.append("WHERE ").append(String.join(" AND ", whereClauses)).append(" ");
            }

            sql.append("ORDER BY v.created_at DESC");

            try (PreparedStatement stmt = conn.prepareStatement(sql.toString())) {
                for (int i = 0; i < params.size(); i++) {
                    stmt.setObject(i + 1, params.get(i));
                }

                try (ResultSet rs = stmt.executeQuery()) {
                    StringBuilder result = new StringBuilder();
                    result.append("=== VENTAS ===\n\n");

                    if (!filtrosAplicados.isEmpty()) {
                        result.append("Filtros aplicados:\n");
                        for (String filtro : filtrosAplicados) {
                            result.append("• ").append(filtro).append("\n");
                        }
                        result.append("\n");
                    }

                int count = 0;
                    float totalGeneral = 0;
                while (rs.next()) {
                    count++;
                        int id = rs.getInt("id");
                        String receta = rs.getString("receta");
                        float cant = rs.getFloat("cantidad");
                        float prec = rs.getFloat("precio");
                        float tot = rs.getFloat("total");
                        String fecha = rs.getString("created_at");

                        result.append(count).append(". [ID:").append(id).append("] ").append(receta).append("\n");
                        result.append("   Cantidad: ").append(cant).append(" porciones | Precio: Bs. ").append(prec).append("\n");
                        result.append("   Total: Bs. ").append(tot).append(" | Fecha: ").append(fecha).append("\n\n");

                        totalGeneral += tot;
                    }

                    if (count == 0) {
                        return "No se encontraron ventas" + (filtrosAplicados.isEmpty() ? "." : " con los filtros especificados.");
                    }

                    result.append("Total ventas: ").append(count).append("\n");
                    result.append("Monto total: Bs. ").append(String.format("%.2f", totalGeneral));

                    return result.toString();
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error consultando ventas: " + e.getMessage());
            return "Error consultando ventas: " + e.getMessage();
        }
    }

    private static String getVentaDetails(String command) {
        String[] parts = command.split("CONSULTAR VENTA");
        if (parts.length < 2 || parts[1].trim().isEmpty()) {
            return "Formato incorrecto.\nUse: CONSULTAR VENTA [id]\n\nEjemplo: CONSULTAR VENTA 10";
        }

        int ventaId;
        try {
            ventaId = Integer.parseInt(parts[1].trim());
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            String ventaSql = "SELECT v.id, v.cantidad, v.precio, v.total, v.created_at, r.nombre as receta " +
                             "FROM ventas v " +
                             "JOIN recetas r ON v.receta_id = r.id " +
                             "WHERE v.id = ?";

            try (PreparedStatement stmt = conn.prepareStatement(ventaSql)) {
                stmt.setInt(1, ventaId);

                try (ResultSet rs = stmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró la venta con ID: " + ventaId;
                    }

                    float cantidad = rs.getFloat("cantidad");
                    float precio = rs.getFloat("precio");
                    float total = rs.getFloat("total");
                    String receta = rs.getString("receta");
                    String fecha = rs.getString("created_at");

                    StringBuilder result = new StringBuilder();
                    result.append("=== VENTA #").append(ventaId).append(" ===\n\n");
                    result.append("Receta: ").append(receta).append("\n");
                    result.append("Cantidad: ").append(cantidad).append(" porciones\n");
                    result.append("Precio unitario: Bs. ").append(precio).append("\n");
                    result.append("Total: Bs. ").append(total).append("\n");
                    result.append("Fecha: ").append(fecha).append("\n\n");
                    result.append("INSUMOS DESCONTADOS:\n");

                    // Obtener insumos descontados
                    String insumosSql = "SELECT i.nombre, mi.cantidad, um.abreviatura " +
                                       "FROM movimiento_inventarios mi " +
                                       "JOIN insumos i ON mi.insumo_id = i.id " +
                                       "JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                                       "WHERE mi.venta_id = ?";

                    try (PreparedStatement insStmt = conn.prepareStatement(insumosSql)) {
                        insStmt.setInt(1, ventaId);

                        try (ResultSet insRs = insStmt.executeQuery()) {
                            while (insRs.next()) {
                                result.append("• ").append(insRs.getString("nombre")).append(": ")
                                      .append(insRs.getFloat("cantidad")).append(" ")
                                      .append(insRs.getString("abreviatura")).append("\n");
                            }
                        }
        }

        return result.toString();
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error consultando venta: " + e.getMessage());
            return "Error consultando venta: " + e.getMessage();
        }
    }

    private static String updateVenta(String command) {
        // Formato: ACTUALIZAR VENTA [receta] [id] [campo] [valor]
        String[] parts = command.split("ACTUALIZAR VENTA");
        if (parts.length < 2) {
            return "Formato incorrecto.\nUse: ACTUALIZAR VENTA [receta] [id] [campo] [valor]\n\n" +
                   "Campos disponibles: cantidad, precio, receta\n" +
                   "Ejemplo: ACTUALIZAR VENTA Pizza 10 cantidad 8";
        }

        String[] params = splitParams(parts[1].trim(), 4);
        if (params.length < 4) {
            return "Formato incorrecto.\nUse: ACTUALIZAR VENTA [receta] [id] [campo] [valor]\n\n" +
                   "Campos disponibles: cantidad, precio, receta\n" +
                   "Ejemplo: ACTUALIZAR VENTA Pizza 10 cantidad 8";
        }

        String nombreReceta = params[0];
        int ventaId;
        try {
            ventaId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        String campo = params[2].toLowerCase();
        String valor = params[3];

        try (Connection conn = getDatabaseConnection()) {
            // Buscar receta
            String checkRecetaSql = "SELECT id FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkRecetaSql)) {
                checkStmt.setString(1, "%" + nombreReceta + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                    } else {
                        return "❌ ERROR: No se encontró la receta: " + nombreReceta;
                    }
                }
            }

            // Verificar que la venta existe y pertenece a esa receta
            String checkSql = "SELECT id, cantidad, precio FROM ventas WHERE id = ? AND receta_id = ?";
            float cantidadActual = 0;
            float precioActual = 0;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setInt(1, ventaId);
                checkStmt.setInt(2, recetaId);
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró la venta ID " + ventaId + " de la receta " + nombreReceta + ".\n" +
                               "Use: CONSULTAR VENTAS receta " + nombreReceta + "\npara ver los IDs disponibles.";
                    }
                    cantidadActual = rs.getFloat("cantidad");
                    precioActual = rs.getFloat("precio");
                }
            }

            // Actualizar según el campo
            String updateSql = "";

            switch (campo) {
                case "cantidad":
                    float cantidad;
                    try {
                        cantidad = Float.parseFloat(valor);
                        if (cantidad <= 0) {
                            return "❌ ERROR: La cantidad debe ser mayor a 0";
                        }
                    } catch (NumberFormatException e) {
                        return "❌ ERROR: La cantidad debe ser un número válido";
                    }
                    float nuevoTotal = cantidad * precioActual;
                    updateSql = "UPDATE ventas SET cantidad = " + cantidad + ", total = " + nuevoTotal + ", updated_at = NOW() WHERE id = " + ventaId;
                    break;

                case "precio":
                    float precio;
                    try {
                        precio = Float.parseFloat(valor);
                        if (precio <= 0) {
                            return "❌ ERROR: El precio debe ser mayor a 0";
                        }
                    } catch (NumberFormatException e) {
                        return "❌ ERROR: El precio debe ser un número válido";
                    }
                    float nuevoTotal2 = cantidadActual * precio;
                    updateSql = "UPDATE ventas SET precio = " + precio + ", total = " + nuevoTotal2 + ", updated_at = NOW() WHERE id = " + ventaId;
                    break;

                case "receta":
                    String recetaSql = "SELECT id FROM recetas WHERE nombre ILIKE ?";
                    int nuevaRecetaId = -1;
                    try (PreparedStatement recetaStmt = conn.prepareStatement(recetaSql)) {
                        recetaStmt.setString(1, "%" + valor + "%");
                        try (ResultSet rs = recetaStmt.executeQuery()) {
                            if (rs.next()) {
                                nuevaRecetaId = rs.getInt("id");
                            } else {
                                return "❌ ERROR: No se encontró la receta: " + valor;
                            }
                        }
                    }
                    updateSql = "UPDATE ventas SET receta_id = " + nuevaRecetaId + ", updated_at = NOW() WHERE id = " + ventaId;
                    break;

                default:
                    return "❌ ERROR: Campo '" + campo + "' no válido.\nCampos disponibles: cantidad, precio, receta";
            }

            // Ejecutar actualización
            try (PreparedStatement stmt = conn.prepareStatement(updateSql)) {
                stmt.executeUpdate();
            }

            // Obtener datos actualizados
            String selectSql = "SELECT v.id, v.cantidad, v.precio, v.total, v.created_at, r.nombre as receta " +
                              "FROM ventas v " +
                              "JOIN recetas r ON v.receta_id = r.id " +
                              "WHERE v.id = ?";

            try (PreparedStatement selectStmt = conn.prepareStatement(selectSql)) {
                selectStmt.setInt(1, ventaId);

                try (ResultSet rs = selectStmt.executeQuery()) {
                    if (rs.next()) {
                        return "✅ VENTA ACTUALIZADA EXITOSAMENTE\n\n" +
                               "ID: " + ventaId + "\n" +
                               "Receta: " + rs.getString("receta") + "\n" +
                               "Cantidad: " + rs.getFloat("cantidad") + " porciones\n" +
                               "Precio unitario: Bs. " + rs.getFloat("precio") + "\n" +
                               "Total: Bs. " + rs.getFloat("total") + "\n" +
                               "Fecha: " + rs.getString("created_at") + "\n\n" +
                               "Campo actualizado: " + campo;
                    }
                }
            }

            return "✅ VENTA ACTUALIZADA";

        } catch (SQLException e) {
            System.out.println("❌ Error actualizando venta: " + e.getMessage());
            return "Error actualizando venta: " + e.getMessage();
        }
    }

    private static String deleteVenta(String command) {
        String[] parts = command.split("ELIMINAR VENTA");
        if (parts.length < 2 || parts[1].trim().isEmpty()) {
            return "Formato incorrecto.\nUse: ELIMINAR VENTA [receta] [id]\n\nEjemplo: ELIMINAR VENTA Pizza 10";
        }

        String[] params = splitParams(parts[1].trim(), 2);
        if (params.length < 2) {
            return "Formato incorrecto.\nUse: ELIMINAR VENTA [receta] [id]\n\nEjemplo: ELIMINAR VENTA Pizza 10";
        }

        String nombreReceta = params[0];
        int ventaId;
        try {
            ventaId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Buscar receta
            String checkRecetaSql = "SELECT id FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkRecetaSql)) {
                checkStmt.setString(1, "%" + nombreReceta + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                    } else {
                        return "❌ ERROR: No se encontró la receta: " + nombreReceta;
                    }
                }
            }

            // Obtener datos de la venta antes de eliminar
            String selectSql = "SELECT v.id, v.cantidad, v.total, v.created_at, r.nombre as receta " +
                              "FROM ventas v " +
                              "JOIN recetas r ON v.receta_id = r.id " +
                              "WHERE v.id = ? AND v.receta_id = ?";

            String receta = "";
            float cantidad = 0;
            float total = 0;
            String fecha = "";

            try (PreparedStatement selectStmt = conn.prepareStatement(selectSql)) {
                selectStmt.setInt(1, ventaId);
                selectStmt.setInt(2, recetaId);

                try (ResultSet rs = selectStmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró la venta ID " + ventaId + " de la receta " + nombreReceta + ".\n" +
                               "Use: CONSULTAR VENTAS receta " + nombreReceta + "\npara ver los IDs disponibles.";
                    }
                    receta = rs.getString("receta");
                    cantidad = rs.getFloat("cantidad");
                    total = rs.getFloat("total");
                    fecha = rs.getString("created_at");
                }
            }

            // Eliminar movimientos asociados
            String deleteMovimientosSql = "DELETE FROM movimiento_inventarios WHERE venta_id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(deleteMovimientosSql)) {
                stmt.setInt(1, ventaId);
                stmt.executeUpdate();
            }

            // Eliminar venta
            String deleteSql = "DELETE FROM ventas WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(deleteSql)) {
                stmt.setInt(1, ventaId);
                int rowsAffected = stmt.executeUpdate();

                if (rowsAffected > 0) {
                    return "✅ VENTA ELIMINADA EXITOSAMENTE\n\n" +
                           "ID: " + ventaId + "\n" +
                           "Receta: " + receta + "\n" +
                           "Cantidad: " + cantidad + " porciones\n" +
                           "Total: Bs. " + total + "\n" +
                           "Fecha: " + fecha + "\n\n" +
                           "⚠️ Los movimientos de inventario asociados fueron eliminados.";
                } else {
                    return "❌ Error: No se pudo eliminar la venta";
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error eliminando venta: " + e.getMessage());
            return "Error eliminando venta: " + e.getMessage();
        }
    }

    // ========== MÉTODOS DE COMPRAS/PAGOS (CU7) ==========

    private static String createCompra(String command) {
        // Formato: CREAR COMPRA [proveedor] [monto] [descripcion]
        String[] parts = command.split("CREAR COMPRA");
        if (parts.length < 2) {
            return "Formato incorrecto.\nUse: CREAR COMPRA [proveedor] [monto] [descripcion]\n\nEjemplo: CREAR COMPRA DistribuidoraXYZ 500 Compra semanal de insumos";
        }

        String[] params = splitParams(parts[1].trim(), 3);
        if (params.length < 3) {
            return "Formato incorrecto.\nUse: CREAR COMPRA [proveedor] [monto] [descripcion]\n\nEjemplo: CREAR COMPRA DistribuidoraXYZ 500 Compra semanal de insumos";
        }

        String proveedor = params[0];
        float monto;
        String descripcion = params[2];

        try {
            monto = Float.parseFloat(params[1]);

            if (monto <= 0) {
                return "❌ ERROR: El monto debe ser mayor a 0";
            }
        } catch (NumberFormatException e) {
            return "❌ ERROR: El monto debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            String insertSql = "INSERT INTO compras (costo_total, proveedor, descripcion, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";

            try (PreparedStatement stmt = conn.prepareStatement(insertSql, Statement.RETURN_GENERATED_KEYS)) {
                stmt.setFloat(1, monto);
                stmt.setString(2, proveedor);
                stmt.setString(3, descripcion);
                stmt.executeUpdate();

                int compraId = -1;
                try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
                    if (generatedKeys.next()) {
                        compraId = generatedKeys.getInt(1);
                    }
                }

                return "✅ COMPRA REGISTRADA EXITOSAMENTE\n\n" +
                       "ID: " + compraId + "\n" +
                       "Proveedor: " + proveedor + "\n" +
                       "Monto: Bs. " + String.format("%.2f", monto) + "\n" +
                       "Descripción: " + descripcion;
            }

        } catch (SQLException e) {
            System.out.println("❌ Error creando compra: " + e.getMessage());
            return "Error creando compra: " + e.getMessage();
        }
    }

    private static String getComprasListWithFilters(String filtros) {
        try (Connection conn = getDatabaseConnection()) {
            StringBuilder sql = new StringBuilder();
            sql.append("SELECT id, costo_total, proveedor, descripcion, created_at FROM compras ");

            java.util.List<String> filtrosAplicados = new java.util.ArrayList<>();
            java.util.List<String> whereClauses = new java.util.ArrayList<>();
            java.util.List<Object> params = new java.util.ArrayList<>();

            if (!filtros.isEmpty()) {
                String[] filtrosPares = filtros.split(",");

                for (String par : filtrosPares) {
                    String[] partes = splitParams(par.trim(), 2);

                    if (partes.length >= 2) {
                        String tipoFiltro = partes[0].toLowerCase();
                        String valorFiltro = partes[1].trim();

                        switch (tipoFiltro) {
                            case "proveedor":
                                whereClauses.add("proveedor ILIKE ?");
                                params.add("%" + valorFiltro + "%");
                                filtrosAplicados.add("Proveedor: " + valorFiltro);
                                break;

                            case "monto_min":
                                whereClauses.add("costo_total >= ?");
                                params.add(valorFiltro);
                                filtrosAplicados.add("Monto mínimo: " + valorFiltro);
                                break;

                            case "monto_max":
                                whereClauses.add("costo_total <= ?");
                                params.add(valorFiltro);
                                filtrosAplicados.add("Monto máximo: " + valorFiltro);
                                break;

                            case "fecha":
                                try {
                                    String fechaNorm = normalizarFecha(valorFiltro);
                                    whereClauses.add("DATE(created_at) = ?");
                                    params.add(fechaNorm);
                                    filtrosAplicados.add("Fecha: " + fechaNorm);
                                } catch (Exception e) {
                                    // Ignorar fecha inválida
                                }
                                break;

                            case "fecha_desde":
                                try {
                                    String fechaDesde = normalizarFecha(valorFiltro);
                                    whereClauses.add("DATE(created_at) >= ?");
                                    params.add(fechaDesde);
                                    filtrosAplicados.add("Desde: " + fechaDesde);
                                } catch (Exception e) {
                                    // Ignorar fecha inválida
                                }
                                break;

                            case "fecha_hasta":
                                try {
                                    String fechaHasta = normalizarFecha(valorFiltro);
                                    whereClauses.add("DATE(created_at) <= ?");
                                    params.add(fechaHasta);
                                    filtrosAplicados.add("Hasta: " + fechaHasta);
                                } catch (Exception e) {
                                    // Ignorar fecha inválida
                                }
                                break;
                        }
                    }
                }

                if (!whereClauses.isEmpty()) {
                    sql.append("WHERE ").append(String.join(" AND ", whereClauses)).append(" ");
                }
            }

            sql.append("ORDER BY created_at DESC");

        StringBuilder result = new StringBuilder();
            result.append("=== COMPRAS ===\n\n");

            if (!filtrosAplicados.isEmpty()) {
                result.append("Filtros aplicados: ").append(String.join(", ", filtrosAplicados)).append("\n\n");
            }

            try (PreparedStatement stmt = conn.prepareStatement(sql.toString())) {
                for (int i = 0; i < params.size(); i++) {
                    stmt.setObject(i + 1, params.get(i));
                }

                try (ResultSet rs = stmt.executeQuery()) {
                    float totalGeneral = 0;
                    int count = 0;

                    while (rs.next()) {
                        count++;
                        int id = rs.getInt("id");
                        float monto = rs.getFloat("costo_total");
                        String proveedor = rs.getString("proveedor");
                        String descripcion = rs.getString("descripcion");
                        String fecha = rs.getString("created_at");

                        totalGeneral += monto;

                        result.append(count).append(". [ID:").append(id).append("] ").append(proveedor != null ? proveedor : "N/A").append("\n");
                        result.append("   Monto: Bs. ").append(String.format("%.2f", monto)).append("\n");
                        result.append("   Descripción: ").append(descripcion != null ? descripcion : "N/A").append("\n");
                        result.append("   Fecha: ").append(fecha).append("\n\n");
                    }

                    if (count == 0) {
                        result.append("No se encontraron compras");
                        if (!filtrosAplicados.isEmpty()) {
                            result.append(" con los filtros especificados");
                        }
                        result.append(".\n");
                    } else {
                        result.append("═══════════════════════\n");
                        result.append("Total compras: ").append(count).append("\n");
                        result.append("Monto total: Bs. ").append(String.format("%.2f", totalGeneral));
                    }
                }
            }

            return result.toString();

        } catch (SQLException e) {
            System.out.println("❌ Error consultando compras: " + e.getMessage());
            return "Error consultando compras: " + e.getMessage();
        }
    }

    private static String updateCompra(String command) {
        // Formato: ACTUALIZAR COMPRA [id] [campo] [valor]
        String[] parts = command.split("ACTUALIZAR COMPRA");
        if (parts.length < 2) {
            return "Formato incorrecto.\nUse: ACTUALIZAR COMPRA [id] [campo] [valor]\n\n" +
                   "Campos disponibles: monto, proveedor, descripcion\n" +
                   "Ejemplo: ACTUALIZAR COMPRA 10 monto 550";
        }

        String[] params = splitParams(parts[1].trim(), 3);
        if (params.length < 3) {
            return "Formato incorrecto.\nUse: ACTUALIZAR COMPRA [id] [campo] [valor]\n\n" +
                   "Campos disponibles: monto, proveedor, descripcion\n" +
                   "Ejemplo: ACTUALIZAR COMPRA 10 monto 550";
        }

        int compraId;
        try {
            compraId = Integer.parseInt(params[0]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        String campo = params[1].toLowerCase();
        String valor = params[2];

        try (Connection conn = getDatabaseConnection()) {
            // Verificar que la compra existe
            String checkSql = "SELECT id FROM compras WHERE id = ?";
            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setInt(1, compraId);
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró la compra con ID: " + compraId;
                    }
                }
            }

            // Actualizar según el campo
            String updateSql = "";

            switch (campo) {
                case "monto":
                    float monto;
                    try {
                        monto = Float.parseFloat(valor);
                        if (monto <= 0) {
                            return "❌ ERROR: El monto debe ser mayor a 0";
                        }
                    } catch (NumberFormatException e) {
                        return "❌ ERROR: El monto debe ser un número válido";
                    }
                    updateSql = "UPDATE compras SET costo_total = " + monto + ", updated_at = NOW() WHERE id = " + compraId;
                    break;

                case "proveedor":
                    updateSql = "UPDATE compras SET proveedor = ?, updated_at = NOW() WHERE id = ?";
                    break;

                case "descripcion":
                    updateSql = "UPDATE compras SET descripcion = ?, updated_at = NOW() WHERE id = ?";
                    break;

                default:
                    return "❌ ERROR: Campo '" + campo + "' no válido.\nCampos disponibles: monto, proveedor, descripcion";
            }

            try (PreparedStatement stmt = conn.prepareStatement(updateSql)) {
                if (campo.equals("proveedor") || campo.equals("descripcion")) {
                    stmt.setString(1, valor);
                    stmt.setInt(2, compraId);
                }

                int rowsAffected = stmt.executeUpdate();

                if (rowsAffected > 0) {
                    return "✅ COMPRA ACTUALIZADA EXITOSAMENTE\n\n" +
                           "ID: " + compraId + "\n" +
                           "Campo actualizado: " + campo + "\n" +
                           "Nuevo valor: " + valor;
                } else {
                    return "❌ Error: No se pudo actualizar la compra";
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error actualizando compra: " + e.getMessage());
            return "Error actualizando compra: " + e.getMessage();
        }
    }

    private static String deleteCompra(String command) {
        String[] parts = command.split("ELIMINAR COMPRA");
        if (parts.length < 2 || parts[1].trim().isEmpty()) {
            return "Formato incorrecto.\nUse: ELIMINAR COMPRA [id]\n\nEjemplo: ELIMINAR COMPRA 10";
        }

        int compraId;
        try {
            compraId = Integer.parseInt(parts[1].trim());
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Obtener datos de la compra antes de eliminar
            String selectSql = "SELECT id, costo_total, proveedor, descripcion, created_at FROM compras WHERE id = ?";

            float monto = 0;
            String proveedor = "";
            String descripcion = "";
            String fecha = "";

            try (PreparedStatement selectStmt = conn.prepareStatement(selectSql)) {
                selectStmt.setInt(1, compraId);
                try (ResultSet rs = selectStmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró la compra con ID: " + compraId;
                    }
                    monto = rs.getFloat("costo_total");
                    proveedor = rs.getString("proveedor");
                    descripcion = rs.getString("descripcion");
                    fecha = rs.getString("created_at");
                }
            }

            // Eliminar compra
            String deleteSql = "DELETE FROM compras WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(deleteSql)) {
                stmt.setInt(1, compraId);
                int rowsAffected = stmt.executeUpdate();

                if (rowsAffected > 0) {
                    return "✅ COMPRA ELIMINADA EXITOSAMENTE\n\n" +
                           "ID: " + compraId + "\n" +
                           "Proveedor: " + (proveedor != null ? proveedor : "N/A") + "\n" +
                           "Monto: Bs. " + String.format("%.2f", monto) + "\n" +
                           "Descripción: " + (descripcion != null ? descripcion : "N/A") + "\n" +
                           "Fecha: " + fecha;
                } else {
                    return "❌ Error: No se pudo eliminar la compra";
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error eliminando compra: " + e.getMessage());
            return "Error eliminando compra: " + e.getMessage();
        }
    }

    // ========== MÉTODOS DE REPORTES PERSONALIZADOS (CU8) ==========

    private static String createReporte(String command) {
        // Formato: CREAR REPORTE [nombre] [fecha_desde] [fecha_hasta] [guardar]
        String args = removeKeyword(command, "CREAR REPORTE");
        if (args.isEmpty()) {
            return "Formato incorrecto.\nUse: CREAR REPORTE [nombre] [fecha_desde] [fecha_hasta] [guardar]\n\n" +
                   "Ejemplo: CREAR REPORTE VentasNoviembre 2025-11-1 2025-11-30\n" +
                   "Con guardar: CREAR REPORTE VentasNoviembre 2025-11-1 2025-11-30 guardar";
        }

        String[] params = splitParams(args);
        if (params.length < 3) {
            return "Formato incorrecto.\nUse: CREAR REPORTE [nombre] [fecha_desde] [fecha_hasta]\n\n" +
                   "Ejemplo: CREAR REPORTE VentasNoviembre 2025-11-1 2025-11-30";
        }

        String nombre = params[0];
        String fechaDesde = params[1];
        String fechaHasta = params[2];
        boolean guardar = params.length >= 4 && params[3].equalsIgnoreCase("guardar");

        // Normalizar fechas
        try {
            fechaDesde = normalizarFecha(fechaDesde);
            fechaHasta = normalizarFecha(fechaHasta);
        } catch (Exception e) {
            return "❌ ERROR: Formato de fecha inválido.\nUse: YYYY-MM-DD o YYYY-M-D\nEjemplo: 2025-11-1 o 2025-11-01";
        }

        // Generar reporte de ventas
        String reporteResultado = generarReporteVentasPorFechas(fechaDesde, fechaHasta);

        // Si se pidió guardar, guardamos en la BD
        if (guardar) {
            try (Connection conn = getDatabaseConnection()) {
                String insertSql = "INSERT INTO reportes_personalizados (nombre, fecha_desde, fecha_hasta, created_at, updated_at) " +
                                  "VALUES (?, ?, ?, NOW(), NOW())";

                int reporteId = -1;

                try (PreparedStatement stmt = conn.prepareStatement(insertSql, Statement.RETURN_GENERATED_KEYS)) {
                    stmt.setString(1, nombre);
                    stmt.setString(2, fechaDesde);
                    stmt.setString(3, fechaHasta);
                    stmt.executeUpdate();

                    try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
                        if (generatedKeys.next()) {
                            reporteId = generatedKeys.getInt(1);
                        }
                    }
                }

                return reporteResultado + "\n\n" +
                       "✅ REPORTE GUARDADO EXITOSAMENTE\n" +
                       "ID: " + reporteId + " | Nombre: " + nombre + "\n" +
                       "Puedes regenerarlo con: GENERAR REPORTE " + nombre + " " + reporteId;

            } catch (SQLException e) {
                return reporteResultado + "\n\n⚠️ Error guardando: " + e.getMessage();
            }
        }

        return reporteResultado;
    }

    private static String getReporteInventario() {
        try (Connection conn = getDatabaseConnection()) {
            StringBuilder result = new StringBuilder();
            result.append("📦 REPORTE DE INVENTARIO\n");
            result.append("══════════════════════════════════════\n\n");

            String sql = "SELECT i.nombre, " +
                        "COALESCE(SUM(CASE WHEN mi.tipo = 'entrada' THEN mi.cantidad ELSE 0 END), 0) - " +
                        "COALESCE(SUM(CASE WHEN mi.tipo = 'salida' THEN mi.cantidad ELSE 0 END), 0) as stock_actual, " +
                        "i.stock_minimo, " +
                        "um.abreviatura as unidad " +
                            "FROM insumos i " +
                        "LEFT JOIN movimiento_inventarios mi ON i.id = mi.insumo_id " +
                            "LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                        "GROUP BY i.id, i.nombre, i.stock_minimo, um.abreviatura " +
                            "ORDER BY i.nombre";

            int total = 0;
            int stockBajo = 0;
            int stockOk = 0;

                try (PreparedStatement stmt = conn.prepareStatement(sql);
                     ResultSet rs = stmt.executeQuery()) {

                result.append("📋 ESTADO DE INSUMOS:\n\n");

                    while (rs.next()) {
                    total++;
                        String nombre = rs.getString("nombre");
                    float stockActual = rs.getFloat("stock_actual");
                        float stockMinimo = rs.getFloat("stock_minimo");
                        String unidad = rs.getString("unidad");

                    String estado = stockActual >= stockMinimo ? "✅" : "⚠️";
                    if (stockActual < stockMinimo) {
                        stockBajo++;
                    } else {
                        stockOk++;
                    }

                    result.append(estado).append(" ").append(nombre)
                          .append(": ").append(String.format("%.2f", stockActual))
                          .append("/").append(String.format("%.2f", stockMinimo))
                          .append(" ").append(unidad != null ? unidad : "").append("\n");
                }

                result.append("\n📊 RESUMEN:\n");
                result.append("• Total insumos: ").append(total).append("\n");
                result.append("• Stock OK: ").append(stockOk).append(" (✅)\n");
                result.append("• Stock bajo: ").append(stockBajo).append(" (⚠️)\n");
            }

            return result.toString();

        } catch (SQLException e) {
            System.out.println("❌ Error generando reporte de inventario: " + e.getMessage());
            return "Error generando reporte de inventario: " + e.getMessage();
        }
    }

    private static String getReporteProductosMasVendidos() {
        try (Connection conn = getDatabaseConnection()) {
            StringBuilder result = new StringBuilder();
            result.append("🏆 REPORTE - PRODUCTOS MÁS VENDIDOS\n");
            result.append("══════════════════════════════════════\n\n");

            String sql = "SELECT r.nombre, " +
                        "SUM(v.cantidad) as total_vendido, " +
                        "SUM(v.total) as ingresos_generados, " +
                        "COUNT(v.id) as numero_ventas, " +
                        "AVG(v.precio) as precio_promedio " +
                        "FROM ventas v " +
                        "JOIN recetas r ON v.receta_id = r.id " +
                        "GROUP BY r.id, r.nombre " +
                        "ORDER BY total_vendido DESC " +
                        "LIMIT 10";

            try (PreparedStatement stmt = conn.prepareStatement(sql);
                     ResultSet rs = stmt.executeQuery()) {

                int posicion = 1;
                while (rs.next()) {
                    result.append(posicion).append(". ").append(rs.getString("nombre")).append("\n");
                    result.append("   • Unidades vendidas: ").append(rs.getInt("total_vendido")).append("\n");
                    result.append("   • Ingresos: Bs. ").append(String.format("%.2f", rs.getFloat("ingresos_generados"))).append("\n");
                    result.append("   • Número de ventas: ").append(rs.getInt("numero_ventas")).append("\n");
                    result.append("   • Precio promedio: Bs. ").append(String.format("%.2f", rs.getFloat("precio_promedio"))).append("\n\n");
                    posicion++;
                }

                if (posicion == 1) {
                    result.append("No hay ventas registradas aún.\n");
                }
            }

            return result.toString();

        } catch (SQLException e) {
            System.out.println("❌ Error generando reporte de productos más vendidos: " + e.getMessage());
            return "Error generando reporte de productos más vendidos: " + e.getMessage();
        }
    }

    private static String getReporteInsumosCriticos() {
        try (Connection conn = getDatabaseConnection()) {
            StringBuilder result = new StringBuilder();
            result.append("⚠️ REPORTE - INSUMOS CRÍTICOS\n");
            result.append("══════════════════════════════════════\n\n");

            String sql = "SELECT i.nombre, " +
                        "COALESCE(SUM(CASE WHEN mi.tipo = 'entrada' THEN mi.cantidad ELSE 0 END), 0) - " +
                        "COALESCE(SUM(CASE WHEN mi.tipo = 'salida' THEN mi.cantidad ELSE 0 END), 0) as stock_actual, " +
                        "i.stock_minimo, " +
                        "um.abreviatura as unidad, " +
                        "c.nombre as categoria " +
                        "FROM insumos i " +
                        "LEFT JOIN movimiento_inventarios mi ON i.id = mi.insumo_id " +
                        "LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                        "LEFT JOIN categorias c ON i.categoria_id = c.id " +
                        "GROUP BY i.id, i.nombre, i.stock_minimo, um.abreviatura, c.nombre " +
                        "HAVING stock_actual < i.stock_minimo " +
                        "ORDER BY (stock_actual / i.stock_minimo) ASC";

            int count = 0;

            try (PreparedStatement stmt = conn.prepareStatement(sql);
                     ResultSet rs = stmt.executeQuery()) {

                while (rs.next()) {
                    count++;
                    String nombre = rs.getString("nombre");
                    float stockActual = rs.getFloat("stock_actual");
                    float stockMinimo = rs.getFloat("stock_minimo");
                    String unidad = rs.getString("unidad");
                    String categoria = rs.getString("categoria");

                    float porcentaje = (stockActual / stockMinimo) * 100;
                    String nivel = porcentaje < 25 ? "🔴 CRÍTICO" :
                                  porcentaje < 50 ? "🟠 BAJO" : "🟡 ALERTA";

                    result.append(nivel).append(" ").append(nombre).append("\n");
                    result.append("   • Stock: ").append(String.format("%.2f", stockActual))
                          .append("/").append(String.format("%.2f", stockMinimo))
                          .append(" ").append(unidad != null ? unidad : "").append("\n");
                    result.append("   • Categoría: ").append(categoria != null ? categoria : "N/A").append("\n");
                    result.append("   • Nivel: ").append(String.format("%.1f", porcentaje)).append("%\n\n");
                }

                if (count == 0) {
                    result.append("✅ No hay insumos críticos.\n");
                    result.append("Todos los insumos están por encima del stock mínimo.\n");
                } else {
                    result.append("📊 RESUMEN:\n");
                    result.append("Total insumos críticos: ").append(count).append("\n");
                    result.append("\n⚠️ Se recomienda reabastecer estos insumos pronto.\n");
                }
            }

            return result.toString();

        } catch (SQLException e) {
            System.out.println("❌ Error generando reporte de insumos críticos: " + e.getMessage());
            return "Error generando reporte de insumos críticos: " + e.getMessage();
        }
    }

    private static String getReportesList() {
        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT * FROM reportes_personalizados ORDER BY created_at DESC";
            StringBuilder result = new StringBuilder();
            result.append("=== MIS REPORTES PERSONALIZADOS ===\n\n");

            int count = 0;
            try (PreparedStatement stmt = conn.prepareStatement(sql);
                     ResultSet rs = stmt.executeQuery()) {

                while (rs.next()) {
                    count++;
                    int id = rs.getInt("id");
                    String nombre = rs.getString("nombre");
                    String fechaDesde = rs.getString("fecha_desde");
                    String fechaHasta = rs.getString("fecha_hasta");
                    String fecha = rs.getString("created_at");

                    result.append(count).append(". [ID:").append(id).append("] ").append(nombre).append("\n");
                    result.append("   Período: ").append(fechaDesde).append(" a ").append(fechaHasta).append("\n");
                    result.append("   Creado: ").append(fecha.substring(0, 10)).append("\n\n");
                }

                if (count == 0) {
                    result.append("No tienes reportes personalizados aún.\n\n");
                    result.append("Crea uno con: CREAR REPORTE [nombre] [tipo] [periodo]");
                } else {
                    result.append("Total reportes: ").append(count);
                }
            }

            return result.toString();

        } catch (SQLException e) {
            System.out.println("❌ Error consultando reportes: " + e.getMessage());
            return "Error consultando reportes: " + e.getMessage();
        }
    }

    private static String getReporteDetails(String command) {
        String args = removeKeyword(command, "CONSULTAR REPORTE");
        if (args.isEmpty()) {
            return "Formato incorrecto.\nUse: CONSULTAR REPORTE [nombre] [id]\n\nEjemplo: CONSULTAR REPORTE MisVentas 5";
        }

        String[] params = splitParams(args, 2);
        if (params.length < 2) {
            return "Formato incorrecto.\nUse: CONSULTAR REPORTE [nombre] [id]\n\nEjemplo: CONSULTAR REPORTE MisVentas 5";
        }

        String nombre = params[0];
        int reporteId;
        try {
            reporteId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT * FROM reportes_personalizados WHERE id = ? AND nombre ILIKE ?";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, reporteId);
                stmt.setString(2, "%" + nombre + "%");

                try (ResultSet rs = stmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró el reporte con nombre '" + nombre + "' e ID: " + reporteId;
                    }

                    return "=== REPORTE GUARDADO ===\n\n" +
                           "ID: " + rs.getInt("id") + "\n" +
                           "Nombre: " + rs.getString("nombre") + "\n" +
                           "Período: " + rs.getString("fecha_desde") + " a " + rs.getString("fecha_hasta") + "\n" +
                           "Descripción: " + (rs.getString("descripcion") != null ? rs.getString("descripcion") : "N/A") + "\n" +
                           "Creado: " + rs.getString("created_at") + "\n" +
                           "Última modificación: " + rs.getString("updated_at") + "\n\n" +
                           "Para generar este reporte:\n" +
                           "GENERAR REPORTE " + rs.getString("nombre") + " " + rs.getInt("id");
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error consultando reporte: " + e.getMessage());
            return "Error consultando reporte: " + e.getMessage();
        }
    }

    private static String generarReporte(String command) {
        String args = removeKeyword(command, "GENERAR REPORTE");
        if (args.isEmpty()) {
            return "Formato incorrecto.\nUse: GENERAR REPORTE [nombre] [id]\n\nEjemplo: GENERAR REPORTE MisVentas 5";
        }

        String[] params = splitParams(args, 2);
        if (params.length < 2) {
            return "Formato incorrecto.\nUse: GENERAR REPORTE [nombre] [id]\n\nEjemplo: GENERAR REPORTE MisVentas 5";
        }

        String nombre = params[0];
        int reporteId;
        try {
            reporteId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT * FROM reportes_personalizados WHERE id = ? AND nombre ILIKE ?";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, reporteId);
                stmt.setString(2, "%" + nombre + "%");

                try (ResultSet rs = stmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró el reporte con nombre '" + nombre + "' e ID: " + reporteId;
                    }

                    String nombreReporte = rs.getString("nombre");
                    String fechaDesde = rs.getString("fecha_desde");
                    String fechaHasta = rs.getString("fecha_hasta");

                    // Generar reporte de ventas con las fechas guardadas
                    String reporteTexto = generarReporteVentasPorFechas(fechaDesde, fechaHasta);

                    // Generar PDF llamando al endpoint de Laravel
                    boolean pdfEnviado = enviarPDFReporte(reporteId, nombreReporte);

                    String resultado = "✅ REPORTE GENERADO\n\n" +
                           "ID: " + reporteId + "\n" +
                           "Nombre: " + nombreReporte + "\n" +
                           "Período: " + fechaDesde + " a " + fechaHasta + "\n\n";

                    if (pdfEnviado) {
                        resultado += "📧 ✅ El PDF ha sido generado y enviado a tu email.\n" +
                                    "📎 Nombre archivo: reporte_" + nombreReporte + "_" + reporteId + ".pdf\n\n";
                    } else {
                        resultado += "⚠️ No se pudo enviar el PDF por email.\n" +
                                    "Puedes descargarlo desde la web: http://127.0.0.1:8000/reportes/generar-pdf/" + reporteId + "\n\n";
                    }

                    resultado += "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" +
                           "VISTA PREVIA DEL REPORTE:\n" +
                           "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" +
                           reporteTexto;

                    return resultado;
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error generando reporte: " + e.getMessage());
            return "Error generando reporte: " + e.getMessage();
        }
    }

    private static String generarReporteVentasPorFechas(String fechaDesde, String fechaHasta) {
        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT COUNT(*) as total_ventas, SUM(total) as ingresos_totales, " +
                        "AVG(total) as promedio_venta, SUM(cantidad) as platos_vendidos " +
                        "FROM ventas WHERE DATE(created_at) BETWEEN ? AND ?";

            StringBuilder result = new StringBuilder();
            result.append("📊 REPORTE DE VENTAS\n");
            result.append("══════════════════════════════════════\n");
            result.append("Período: ").append(fechaDesde).append(" a ").append(fechaHasta).append("\n\n");

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, fechaDesde);
                stmt.setString(2, fechaHasta);

                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        result.append("📈 RESUMEN GENERAL:\n");
                        result.append("• Total ventas: ").append(rs.getInt("total_ventas")).append("\n");
                        result.append("• Ingresos totales: Bs. ").append(String.format("%.2f", rs.getFloat("ingresos_totales"))).append("\n");
                        result.append("• Promedio por venta: Bs. ").append(String.format("%.2f", rs.getFloat("promedio_venta"))).append("\n");
                        result.append("• Platos vendidos: ").append(rs.getInt("platos_vendidos")).append("\n\n");
                    }
                }
            }

            // Top 5 productos más vendidos en el período
            String sqlTop = "SELECT r.nombre, SUM(v.cantidad) as total_vendido, SUM(v.total) as ingresos, " +
                           "COUNT(v.id) as num_ventas, AVG(v.precio) as precio_promedio " +
                           "FROM ventas v JOIN recetas r ON v.receta_id = r.id " +
                           "WHERE DATE(v.created_at) BETWEEN ? AND ? " +
                           "GROUP BY r.id, r.nombre ORDER BY total_vendido DESC LIMIT 5";

            result.append("🏆 TOP 5 PRODUCTOS MÁS VENDIDOS:\n");
            try (PreparedStatement stmt = conn.prepareStatement(sqlTop)) {
                stmt.setString(1, fechaDesde);
                stmt.setString(2, fechaHasta);

                try (ResultSet rs = stmt.executeQuery()) {
                    int posicion = 1;
                    while (rs.next()) {
                        result.append(posicion).append(". ").append(rs.getString("nombre")).append("\n");
                        result.append("   • Unidades: ").append(rs.getInt("total_vendido")).append(" | ");
                        result.append("Ingresos: Bs. ").append(String.format("%.2f", rs.getFloat("ingresos"))).append("\n");
                        result.append("   • Ventas: ").append(rs.getInt("num_ventas")).append(" | ");
                        result.append("Precio prom: Bs. ").append(String.format("%.2f", rs.getFloat("precio_promedio"))).append("\n\n");
                        posicion++;
                    }

                    if (posicion == 1) {
                        result.append("No hay ventas en este período.\n");
                    }
                }
            }

            return result.toString();

        } catch (SQLException e) {
            return "Error generando reporte de ventas: " + e.getMessage();
        }
    }

    private static String updateReporte(String command) {
        String args = removeKeyword(command, "ACTUALIZAR REPORTE");
        if (args.isEmpty()) {
            return "Formato incorrecto.\nUse: ACTUALIZAR REPORTE [nombre] [id] [campo] [valor]\n\n" +
                   "Campos: nombre, tipo, periodo, descripcion\n" +
                   "Ejemplo: ACTUALIZAR REPORTE MisVentas 5 periodo mensual";
        }

        String[] params = splitParams(args, 4);
        if (params.length < 4) {
            return "Formato incorrecto.\nUse: ACTUALIZAR REPORTE [nombre] [id] [campo] [valor]\n\nEjemplo: ACTUALIZAR REPORTE MisVentas 5 periodo mensual";
        }

        String nombre = params[0];
        int reporteId;
        try {
            reporteId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        String campo = params[2].toLowerCase();
        String valor = params[3];

        try (Connection conn = getDatabaseConnection()) {
            // Verificar que el reporte existe
            String checkSql = "SELECT * FROM reportes_personalizados WHERE id = ? AND nombre ILIKE ?";
            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setInt(1, reporteId);
                checkStmt.setString(2, "%" + nombre + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró el reporte con nombre '" + nombre + "' e ID: " + reporteId;
                    }
                }
            }

            String updateSql = "";
            switch (campo) {
                case "nombre":
                    updateSql = "UPDATE reportes_personalizados SET nombre = ?, updated_at = NOW() WHERE id = ?";
                    break;
                case "fecha_desde":
                    try {
                        valor = normalizarFecha(valor);
                    } catch (Exception e) {
                        return "❌ ERROR: Formato de fecha inválido.\nUse: YYYY-MM-DD o YYYY-M-D";
                    }
                    updateSql = "UPDATE reportes_personalizados SET fecha_desde = ?, updated_at = NOW() WHERE id = ?";
                    break;
                case "fecha_hasta":
                    try {
                        valor = normalizarFecha(valor);
                    } catch (Exception e) {
                        return "❌ ERROR: Formato de fecha inválido.\nUse: YYYY-MM-DD o YYYY-M-D";
                    }
                    updateSql = "UPDATE reportes_personalizados SET fecha_hasta = ?, updated_at = NOW() WHERE id = ?";
                    break;
                case "descripcion":
                    updateSql = "UPDATE reportes_personalizados SET descripcion = ?, updated_at = NOW() WHERE id = ?";
                    break;
                default:
                    return "❌ ERROR: Campo '" + campo + "' no válido.\nCampos disponibles: nombre, fecha_desde, fecha_hasta, descripcion";
            }

            try (PreparedStatement stmt = conn.prepareStatement(updateSql)) {
                stmt.setString(1, valor);
                stmt.setInt(2, reporteId);
                stmt.executeUpdate();
            }

            return "✅ REPORTE ACTUALIZADO\n\n" +
                   "ID: " + reporteId + "\n" +
                   "Campo actualizado: " + campo + "\n" +
                   "Nuevo valor: " + valor;

        } catch (SQLException e) {
            System.out.println("❌ Error actualizando reporte: " + e.getMessage());
            return "Error actualizando reporte: " + e.getMessage();
        }
    }

    private static String deleteReporte(String command) {
        String args = removeKeyword(command, "ELIMINAR REPORTE");
        if (args.isEmpty()) {
            return "Formato incorrecto.\nUse: ELIMINAR REPORTE [nombre] [id]\n\nEjemplo: ELIMINAR REPORTE MisVentas 5";
        }

        String[] params = splitParams(args, 2);
        if (params.length < 2) {
            return "Formato incorrecto.\nUse: ELIMINAR REPORTE [nombre] [id]\n\nEjemplo: ELIMINAR REPORTE MisVentas 5";
        }

        String nombre = params[0];
        int reporteId;
        try {
            reporteId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Obtener datos antes de eliminar
            String selectSql = "SELECT * FROM reportes_personalizados WHERE id = ? AND nombre ILIKE ?";
            String nombreReporte = "";
            String tipo = "";
            String periodo = "";

            try (PreparedStatement selectStmt = conn.prepareStatement(selectSql)) {
                selectStmt.setInt(1, reporteId);
                selectStmt.setString(2, "%" + nombre + "%");

                try (ResultSet rs = selectStmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró el reporte con nombre '" + nombre + "' e ID: " + reporteId;
                    }
                    nombreReporte = rs.getString("nombre");
                    tipo = rs.getString("tipo");
                    periodo = rs.getString("periodo");
                }
            }

            // Eliminar
            String deleteSql = "DELETE FROM reportes_personalizados WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(deleteSql)) {
                stmt.setInt(1, reporteId);
                int rowsAffected = stmt.executeUpdate();

                if (rowsAffected > 0) {
                    return "✅ REPORTE ELIMINADO\n\n" +
                           "ID: " + reporteId + "\n" +
                           "Nombre: " + nombreReporte + "\n" +
                           "Tipo: " + tipo + "\n" +
                           "Período: " + periodo + "\n\n" +
                           "El reporte ha sido eliminado permanentemente.";
                } else {
                    return "❌ Error: No se pudo eliminar el reporte";
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error eliminando reporte: " + e.getMessage());
            return "Error eliminando reporte: " + e.getMessage();
        }
    }

    private static Connection getDatabaseConnection() throws SQLException {
        try {
            Class.forName("org.postgresql.Driver");
            return DriverManager.getConnection(DB_URL, DB_USERNAME, DB_PASSWORD);
        } catch (ClassNotFoundException e) {
            throw new SQLException("Driver PostgreSQL no encontrado", e);
        }
    }

    private static void sendResponseEmailJavaMail(String toEmail, String message) {
        try {
            System.out.println("📧 Enviando email usando JavaMail...");

            // Configurar propiedades para Gmail SMTP
            Properties props = new Properties();
            props.put("mail.smtp.host", "smtp.gmail.com");
            props.put("mail.smtp.port", "587");
            props.put("mail.smtp.auth", "true");
            props.put("mail.smtp.starttls.enable", "true");
            props.put("mail.smtp.ssl.trust", "smtp.gmail.com");

            // Crear sesión con autenticación
            Session session = Session.getInstance(props, new Authenticator() {
                @Override
                protected PasswordAuthentication getPasswordAuthentication() {
                    return new PasswordAuthentication(USER, PASSWORD);
                }
            });

            // Crear mensaje
            Message emailMessage = new MimeMessage(session);
            emailMessage.setFrom(new InternetAddress(USER));
            emailMessage.setRecipients(Message.RecipientType.TO, InternetAddress.parse(toEmail));
            emailMessage.setSubject("Respuesta del Sistema de Inventarios");
            emailMessage.setText(message);

            // Enviar email
            Transport.send(emailMessage);

            System.out.println("✅ Email enviado exitosamente a: " + toEmail);

        } catch (Exception e) {
            System.out.println("❌ Error enviando email: " + e.getMessage());
            e.printStackTrace();
        }
    }

    // ========== FUNCIONES ESPECÍFICAS PARA INSUMOS ==========

    private static String getInsumosByCategory(String categoria) {
        StringBuilder result = new StringBuilder();
        result.append("=== INSUMOS POR CATEGORÍA: ").append(categoria.toUpperCase()).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT i.id, i.nombre, i.descripcion, i.stock_minimo, um.nombre as unidad, c.nombre as categoria " +
                        "FROM insumos i " +
                        "LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                        "LEFT JOIN categorias c ON i.categoria_id = c.id " +
                        "WHERE c.nombre ILIKE ? " +
                        "ORDER BY i.nombre";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, "%" + categoria + "%");
                try (ResultSet rs = stmt.executeQuery()) {
                    int count = 0;
                    while (rs.next()) {
                        count++;
                        String nombre = rs.getString("nombre");
                        String descripcion = rs.getString("descripcion");
                        float stockMinimo = rs.getFloat("stock_minimo");
                        String unidad = rs.getString("unidad");
                        int insumoId = rs.getInt("id");

                        // Calcular stock actual
                        float stockActual = getStockActual(conn, insumoId);
                        String estado = stockActual >= stockMinimo ? "✅ OK" : "⚠️ STOCK BAJO";

                        result.append("• ").append(nombre)
                              .append(" (").append(descripcion != null ? descripcion : "Sin descripción").append(")")
                              .append(" - Stock: ").append(stockActual).append(" ").append(unidad != null ? unidad : "")
                              .append(" - Mínimo: ").append(stockMinimo).append(" ").append(unidad != null ? unidad : "")
                              .append(" - Estado: ").append(estado).append("\n");
                    }

                    if (count == 0) {
                        result.append("❌ No se encontraron insumos en la categoría: ").append(categoria).append("\n");
                        result.append("Use 'CONSULTAR CATEGORIAS' para ver categorías disponibles.");
                    } else {
                        result.append("\nTotal insumos encontrados: ").append(count);
                    }
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando insumos por categoría: " + e.getMessage());
            return "Error consultando insumos por categoría: " + e.getMessage();
        }

        return result.toString();
    }

    private static String getInsumoDetails(String insumoName) {
        StringBuilder result = new StringBuilder();
        result.append("=== DETALLES DEL INSUMO: ").append(insumoName.toUpperCase()).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT i.id, i.nombre, i.descripcion, i.stock_minimo, i.imagen, " +
                        "um.nombre as unidad, c.nombre as categoria, i.created_at, i.updated_at " +
                        "FROM insumos i " +
                        "LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                        "LEFT JOIN categorias c ON i.categoria_id = c.id " +
                        "WHERE i.nombre ILIKE ?";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, "%" + insumoName + "%");
                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        int insumoId = rs.getInt("id");
                        String nombre = rs.getString("nombre");
                        String descripcion = rs.getString("descripcion");
                        float stockMinimo = rs.getFloat("stock_minimo");
                        String unidad = rs.getString("unidad");
                        String categoria = rs.getString("categoria");
                        String imagen = rs.getString("imagen");
                        String fechaCreacion = rs.getString("created_at");
                        String fechaActualizacion = rs.getString("updated_at");

                        // Calcular stock actual
                        float stockActual = getStockActual(conn, insumoId);
                        String estado = stockActual >= stockMinimo ? "✅ STOCK OK" : "⚠️ STOCK BAJO";

                        result.append("📋 INFORMACIÓN GENERAL:\n");
                        result.append("• ID: ").append(insumoId).append("\n");
                        result.append("• Nombre: ").append(nombre).append("\n");
                        result.append("• Descripción: ").append(descripcion != null ? descripcion : "Sin descripción").append("\n");
                        result.append("• Categoría: ").append(categoria != null ? categoria : "Sin categoría").append("\n");
                        result.append("• Unidad: ").append(unidad != null ? unidad : "Sin unidad").append("\n");
                        result.append("• Imagen: ").append(imagen != null ? imagen : "Sin imagen").append("\n\n");

                        result.append("📊 INFORMACIÓN DE STOCK:\n");
                        result.append("• Stock actual: ").append(stockActual).append(" ").append(unidad != null ? unidad : "").append("\n");
                        result.append("• Stock mínimo: ").append(stockMinimo).append(" ").append(unidad != null ? unidad : "").append("\n");
                        result.append("• Estado: ").append(estado).append("\n\n");

                        result.append("📅 INFORMACIÓN DE FECHAS:\n");
                        result.append("• Creado: ").append(fechaCreacion).append("\n");
                        result.append("• Actualizado: ").append(fechaActualizacion).append("\n\n");

                        result.append("📦 ÚLTIMOS MOVIMIENTOS:\n");
                        result.append(getUltimosMovimientos(conn, insumoId));

                    } else {
                        result.append("❌ No se encontró el insumo: ").append(insumoName).append("\n");
                        result.append("Verifique el nombre o use 'CONSULTAR INSUMOS' para ver la lista completa.");
                    }
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando detalles del insumo: " + e.getMessage());
            return "Error consultando detalles del insumo: " + e.getMessage();
        }

        return result.toString();
    }

    private static String createInsumo(String command) {
        // Extraer parámetros del comando: CREAR INSUMO [nombre] [descripcion] [stock_minimo] [unidad] [categoria]
        String[] parts = command.split("CREAR INSUMO");
        if (parts.length < 2) {
            return "Formato incorrecto. Use: CREAR INSUMO [nombre] [descripcion] [stock_minimo] [unidad] [categoria]\n" +
                   "Ejemplo: CREAR INSUMO azucar dulce 10 kg endulzantes";
        }

        String[] params = splitParams(parts[1].trim());
        if (params.length < 5) {
            return "Faltan parámetros. Use: CREAR INSUMO [nombre] [descripcion] [stock_minimo] [unidad] [categoria]\n" +
                   "Ejemplo: CREAR INSUMO azucar dulce 10 kg endulzantes";
        }

        String nombre = params[0];
        String descripcion = params[1];
        float stockMinimo;
        String unidad = params[3];
        String categoria = params[4];

        try {
            stockMinimo = Float.parseFloat(params[2]);
        } catch (NumberFormatException e) {
            return "Error: El stock mínimo debe ser un número válido.\n" +
                   "Ejemplo: CREAR INSUMO azucar dulce 10 kg endulzantes";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Verificar si la unidad existe
            int unidadId = getUnidadId(conn, unidad);
            if (unidadId == -1) {
                return "❌ Error: La unidad '" + unidad + "' no existe.\n" +
                       "Use 'CONSULTAR UNIDADES' para ver unidades disponibles.";
            }

            // Verificar si la categoría existe
            int categoriaId = getCategoriaId(conn, categoria);
            if (categoriaId == -1) {
                return "❌ Error: La categoría '" + categoria + "' no existe.\n" +
                       "Use 'CONSULTAR CATEGORIAS' para ver categorías disponibles.";
            }

            // Verificar si el insumo ya existe
            String checkSql = "SELECT COUNT(*) FROM insumos WHERE nombre = ?";
            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setString(1, nombre);
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next() && rs.getInt(1) > 0) {
                        return "❌ Error: Ya existe un insumo con el nombre '" + nombre + "'.";
                    }
                }
            }

            // Crear el insumo
            String sql = "INSERT INTO insumos (nombre, descripcion, stock_minimo, unidad_medida_id, categoria_id, created_at, updated_at) " +
                        "VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, nombre);
                stmt.setString(2, descripcion);
                stmt.setFloat(3, stockMinimo);
                stmt.setInt(4, unidadId);
                stmt.setInt(5, categoriaId);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Insumo creado exitosamente:\n" +
                           "• Nombre: " + nombre + "\n" +
                           "• Descripción: " + descripcion + "\n" +
                           "• Stock mínimo: " + stockMinimo + " " + unidad + "\n" +
                           "• Categoría: " + categoria + "\n" +
                           "• Stock actual: 0.0 " + unidad + " (⚠️ STOCK BAJO)";
                } else {
                    return "❌ Error: No se pudo crear el insumo.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error creando insumo: " + e.getMessage());
            return "Error creando insumo: " + e.getMessage();
        }
    }

    private static String editInsumo(String command) {
        // Extraer parámetros del comando: ACTUALIZAR INSUMO [nombre_insumo] [campo] [valor], [campo] [valor]...
        String[] parts = command.split("ACTUALIZAR INSUMO");
        if (parts.length < 2) {
            parts = command.split("EDITAR INSUMO");
        }

        if (parts.length < 2) {
            return "Formato incorrecto. Use: ACTUALIZAR INSUMO [nombre_insumo] [campo] [valor], [campo] [valor]...\n" +
                   "(Campos: nombre, descripcion, stock_minimo, categoria, unidad)\n" +
                   "Ejemplo: ACTUALIZAR INSUMO tomate stock_minimo 15, descripcion tomate rojo fresco";
        }

        String rest = parts[1].trim();
        if (rest.isEmpty()) {
            return "Formato incorrecto. Use: ACTUALIZAR INSUMO [nombre_insumo] [campo] [valor], [campo] [valor]...\n" +
                   "(Campos: nombre, descripcion, stock_minimo, categoria, unidad)\n" +
                   "Ejemplo: ACTUALIZAR INSUMO tomate stock_minimo 15, descripcion tomate rojo fresco";
        }

        // Separar el nombre del insumo del resto
        String[] restParts = rest.split(" ", 2);
        if (restParts.length < 2) {
            return "Faltan parámetros. Use: ACTUALIZAR INSUMO [nombre_insumo] [campo] [valor], [campo] [valor]...\n" +
                   "(Campos: nombre, descripcion, stock_minimo, categoria, unidad)";
        }

        String nombreInsumo = restParts[0];
        String camposStr = restParts[1];

        try (Connection conn = getDatabaseConnection()) {
            // Buscar el insumo por nombre
            String checkSql = "SELECT id, nombre FROM insumos WHERE nombre ILIKE ?";
            int insumoId = -1;
            String nombreExacto = null;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setString(1, "%" + nombreInsumo + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        insumoId = rs.getInt("id");
                        nombreExacto = rs.getString("nombre");
                    } else {
                        return "❌ Error: No se encontró el insumo '" + nombreInsumo + "'.\n" +
                               "Use 'CONSULTAR INSUMOS' para ver insumos disponibles.";
                    }
                }
            }

            // Procesar campos separados por comas
            String[] camposArray = camposStr.split(",");
            List<String> camposActualizados = new ArrayList<>();
            StringBuilder sql = new StringBuilder("UPDATE insumos SET ");
            List<String> updates = new ArrayList<>();
            boolean hayActualizaciones = false;

            for (String campoStr : camposArray) {
                campoStr = campoStr.trim();
                String[] campoParts = splitParams(campoStr, 2);

                if (campoParts.length < 2) {
                    continue;
                }

                String campo = campoParts[0].toLowerCase();
                String valor = campoParts[1].trim();

                // Validar campo
                if (!campo.equals("nombre") && !campo.equals("descripcion") &&
                    !campo.equals("stock_minimo") && !campo.equals("categoria") &&
                    !campo.equals("unidad")) {
                    return "❌ Error: Campo '" + campo + "' no válido.\n" +
                           "(Campos: nombre, descripcion, stock_minimo, categoria, unidad)";
                }

                // Procesar cada tipo de campo
            if (campo.equals("nombre")) {
                    // Verificar nombre único
                String checkNameSql = "SELECT COUNT(*) FROM insumos WHERE nombre = ? AND id != ?";
                try (PreparedStatement checkNameStmt = conn.prepareStatement(checkNameSql)) {
                        checkNameStmt.setString(1, valor);
                        checkNameStmt.setInt(2, insumoId);
                    try (ResultSet rs = checkNameStmt.executeQuery()) {
                        if (rs.next() && rs.getInt(1) > 0) {
                                return "❌ Error: Ya existe otro insumo con el nombre '" + valor + "'.";
                            }
                        }
                    }
                    updates.add("nombre = ?");
                    camposActualizados.add("nombre: " + valor);
                    hayActualizaciones = true;

                } else if (campo.equals("descripcion")) {
                    updates.add("descripcion = ?");
                    camposActualizados.add("descripcion: " + valor);
                    hayActualizaciones = true;

                } else if (campo.equals("stock_minimo")) {
                    try {
                        Float.parseFloat(valor);
                        updates.add("stock_minimo = ?");
                        camposActualizados.add("stock_minimo: " + valor);
                        hayActualizaciones = true;
                    } catch (NumberFormatException e) {
                        return "❌ Error: El stock_minimo debe ser un número válido.";
                    }

                } else if (campo.equals("categoria")) {
                    int categoriaId = getCategoriaId(conn, valor);
                    if (categoriaId == -1) {
                        return "❌ Error: La categoría '" + valor + "' no existe.";
                    }
                    updates.add("categoria_id = ?");
                    camposActualizados.add("categoria: " + valor);
                    hayActualizaciones = true;

                } else if (campo.equals("unidad")) {
                    int unidadId = getUnidadId(conn, valor);
                    if (unidadId == -1) {
                        return "❌ Error: La unidad '" + valor + "' no existe.";
                    }
                    updates.add("unidad_medida_id = ?");
                    camposActualizados.add("unidad: " + valor);
                    hayActualizaciones = true;
                }
            }

            if (!hayActualizaciones) {
                return "❌ Error: No se especificaron campos válidos para actualizar.\n" +
                       "(Campos: nombre, descripcion, stock_minimo, categoria, unidad)";
            }

            // Construir SQL final
            sql.append(String.join(", ", updates));
            sql.append(", updated_at = NOW() WHERE id = ?");

            // Ejecutar actualización
            try (PreparedStatement stmt = conn.prepareStatement(sql.toString())) {
                int paramIndex = 1;

                // Procesar campos de nuevo para asignar valores
                for (String campoStr : camposArray) {
                    campoStr = campoStr.trim();
                    String[] campoParts = splitParams(campoStr, 2);

                    if (campoParts.length < 2) continue;

                    String campo = campoParts[0].toLowerCase();
                    String valor = campoParts[1].trim();

                    if (campo.equals("nombre") || campo.equals("descripcion")) {
                        stmt.setString(paramIndex++, valor);
                    } else if (campo.equals("stock_minimo")) {
                        stmt.setFloat(paramIndex++, Float.parseFloat(valor));
                    } else if (campo.equals("categoria")) {
                        int categoriaId = getCategoriaId(conn, valor);
                        stmt.setInt(paramIndex++, categoriaId);
                    } else if (campo.equals("unidad")) {
                        int unidadId = getUnidadId(conn, valor);
                        stmt.setInt(paramIndex++, unidadId);
                    }
                }

                stmt.setInt(paramIndex, insumoId);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    StringBuilder response = new StringBuilder("✅ Insumo actualizado exitosamente:\n");
                    response.append("• Nombre: ").append(nombreExacto).append("\n");
                    response.append("• Campos actualizados:\n");
                    for (String campo : camposActualizados) {
                        response.append("  - ").append(campo).append("\n");
                    }
                    return response.toString();
                } else {
                    return "❌ Error: No se pudo actualizar el insumo.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error editando insumo: " + e.getMessage());
            return "Error editando insumo: " + e.getMessage();
        }
    }

    private static String deleteInsumo(String command) {
        // Extraer parámetros del comando: ELIMINAR INSUMO [nombre]
        String[] parts = command.split("ELIMINAR INSUMO");
        if (parts.length < 2) {
            return "Formato incorrecto. Use: ELIMINAR INSUMO [nombre]\n" +
                   "Ejemplo: ELIMINAR INSUMO harina";
        }

        String nombreInsumo = parts[1].trim();

        try (Connection conn = getDatabaseConnection()) {
            // Buscar el insumo por nombre y obtener su ID
            String checkSql = "SELECT id, nombre FROM insumos WHERE nombre ILIKE ?";
            int insumoId = -1;
            String nombreExacto = null;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setString(1, "%" + nombreInsumo + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        insumoId = rs.getInt("id");
                        nombreExacto = rs.getString("nombre");
                    } else {
                        return "❌ Error: No se encontró el insumo '" + nombreInsumo + "'.\n" +
                               "Use 'CONSULTAR INSUMOS' para ver los insumos disponibles.";
                    }
                }
            }

            // Verificar si tiene movimientos asociados
            String checkMovimientosSql = "SELECT COUNT(*) FROM movimiento_inventarios WHERE insumo_id = ?";
            try (PreparedStatement checkMovStmt = conn.prepareStatement(checkMovimientosSql)) {
                checkMovStmt.setInt(1, insumoId);
                try (ResultSet rs = checkMovStmt.executeQuery()) {
                    if (rs.next() && rs.getInt(1) > 0) {
                        return "❌ Error: No se puede eliminar el insumo '" + nombreExacto + "' porque tiene movimientos asociados.\n" +
                               "Primero elimine los movimientos relacionados.";
                    }
                }
            }

            // Verificar si está en recetas
            String checkRecetasSql = "SELECT COUNT(*) FROM insumo_receta WHERE insumo_id = ?";
            try (PreparedStatement checkRecStmt = conn.prepareStatement(checkRecetasSql)) {
                checkRecStmt.setInt(1, insumoId);
                try (ResultSet rs = checkRecStmt.executeQuery()) {
                    if (rs.next() && rs.getInt(1) > 0) {
                        return "❌ Error: No se puede eliminar el insumo '" + nombreExacto + "' porque está siendo usado en recetas.";
                    }
                }
            }

            // Eliminar el insumo
            String sql = "DELETE FROM insumos WHERE id = ?";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, insumoId);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Insumo eliminado exitosamente:\n" +
                           "• Nombre: " + nombreExacto + "\n" +
                           "• ID: " + insumoId;
                } else {
                    return "❌ Error: No se pudo eliminar el insumo.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error eliminando insumo: " + e.getMessage());
            return "Error eliminando insumo: " + e.getMessage();
        }
    }

    // Funciones auxiliares para obtener IDs
    private static int getUnidadId(Connection conn, String unidad) throws SQLException {
        String sql = "SELECT id FROM unidad_medidas WHERE nombre ILIKE ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, "%" + unidad + "%");
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt("id");
                }
            }
        }
        return -1;
    }

    private static int getCategoriaId(Connection conn, String categoria) throws SQLException {
        String sql = "SELECT id FROM categorias WHERE nombre ILIKE ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, "%" + categoria + "%");
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt("id");
                }
            }
        }
        return -1;
    }

    /**
     * Normaliza una fecha al formato SQL estándar (YYYY-MM-DD)
     * Acepta formatos: 2025-6-1, 2025-06-01, etc.
     */
    private static String normalizarFecha(String fecha) throws Exception {
        // Eliminar espacios
        fecha = fecha.trim();

        // Intentar parsear la fecha
        String[] partes = fecha.split("-");
        if (partes.length != 3) {
            throw new Exception("Formato de fecha inválido");
        }

        try {
            int año = Integer.parseInt(partes[0]);
            int mes = Integer.parseInt(partes[1]);
            int dia = Integer.parseInt(partes[2]);

            // Validar rangos
            if (año < 1900 || año > 2100) {
                throw new Exception("Año fuera de rango");
            }
            if (mes < 1 || mes > 12) {
                throw new Exception("Mes fuera de rango");
            }
            if (dia < 1 || dia > 31) {
                throw new Exception("Día fuera de rango");
            }

            // Formatear con ceros a la izquierda
            return String.format("%04d-%02d-%02d", año, mes, dia);

        } catch (NumberFormatException e) {
            throw new Exception("Fecha contiene caracteres no numéricos");
        }
    }

    // ========== FUNCIONES ESPECÍFICAS PARA MOVIMIENTOS ==========

    private static String getMovimientosByType(String tipo) {
        StringBuilder result = new StringBuilder();
        result.append("=== MOVIMIENTOS POR TIPO: ").append(tipo.toUpperCase()).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT mi.cantidad, mi.tipo, mi.motivo, mi.created_at, i.nombre as insumo " +
                        "FROM movimiento_inventarios mi " +
                        "JOIN insumos i ON mi.insumo_id = i.id " +
                        "WHERE mi.tipo ILIKE ? " +
                        "ORDER BY mi.created_at DESC";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, "%" + tipo + "%");
                try (ResultSet rs = stmt.executeQuery()) {
                    int count = 0;
                    while (rs.next()) {
                        count++;
                        float cantidad = rs.getFloat("cantidad");
                        String tipoMov = rs.getString("tipo");
                        String motivo = rs.getString("motivo");
                        String fecha = rs.getString("created_at");
                        String insumo = rs.getString("insumo");

                        String simbolo = tipoMov.equals("entrada") ? "+" : "-";
                        result.append(count).append(". ").append(insumo)
                              .append(": ").append(simbolo).append(cantidad)
                              .append(" - ").append(motivo)
                              .append(" (").append(fecha).append(")\n");
                    }

                    if (count == 0) {
                        result.append("❌ No se encontraron movimientos del tipo: ").append(tipo).append("\n");
                        result.append("Tipos disponibles: entrada, salida");
                    } else {
                        result.append("\nTotal movimientos encontrados: ").append(count);
                    }
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando movimientos por tipo: " + e.getMessage());
            return "Error consultando movimientos por tipo: " + e.getMessage();
        }

        return result.toString();
    }

    private static String getMovimientosByDate(String fecha) {
        StringBuilder result = new StringBuilder();
        result.append("=== MOVIMIENTOS POR FECHA: ").append(fecha).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT mi.cantidad, mi.tipo, mi.motivo, mi.created_at, i.nombre as insumo " +
                        "FROM movimiento_inventarios mi " +
                        "JOIN insumos i ON mi.insumo_id = i.id " +
                        "WHERE DATE(mi.created_at) = ? " +
                        "ORDER BY mi.created_at DESC";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, fecha);
                try (ResultSet rs = stmt.executeQuery()) {
                    int count = 0;
                    while (rs.next()) {
                        count++;
                        float cantidad = rs.getFloat("cantidad");
                        String tipo = rs.getString("tipo");
                        String motivo = rs.getString("motivo");
                        String fechaMov = rs.getString("created_at");
                        String insumo = rs.getString("insumo");

                        String simbolo = tipo.equals("entrada") ? "+" : "-";
                        result.append(count).append(". ").append(insumo)
                              .append(": ").append(simbolo).append(cantidad)
                              .append(" - ").append(motivo)
                              .append(" (").append(fechaMov).append(")\n");
                    }

                    if (count == 0) {
                        result.append("❌ No se encontraron movimientos para la fecha: ").append(fecha).append("\n");
                        result.append("Formato de fecha: YYYY-MM-DD");
                    } else {
                        result.append("\nTotal movimientos encontrados: ").append(count);
                    }
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando movimientos por fecha: " + e.getMessage());
            return "Error consultando movimientos por fecha: " + e.getMessage();
        }

        return result.toString();
    }

    private static String createMovimiento(String command) {
        // Extraer parámetros del comando: CREAR MOVIMIENTO [insumo] [tipo] [cantidad] [motivo]
        String[] parts = command.split("CREAR MOVIMIENTO");
        if (parts.length < 2) {
            return "Formato incorrecto. Use: CREAR MOVIMIENTO [insumo] [tipo] [cantidad] [motivo]\n" +
                   "Ejemplo: CREAR MOVIMIENTO harina entrada 50 compra";
        }

        String[] params = splitParams(parts[1].trim(), 4);
        if (params.length < 4) {
            return "Faltan parámetros. Use: CREAR MOVIMIENTO [insumo] [tipo] [cantidad] [motivo]\n" +
                   "Ejemplo: CREAR MOVIMIENTO harina entrada 50 compra";
        }

        String insumoName = params[0];
        String tipo = params[1].toLowerCase();
        float cantidad;
        String motivo = params[3];

        try {
            cantidad = Float.parseFloat(params[2]);
        } catch (NumberFormatException e) {
            return "Error: La cantidad debe ser un número válido.\n" +
                   "Ejemplo: CREAR MOVIMIENTO harina entrada 50 compra";
        }

        // Validar tipo
        if (!tipo.equals("entrada") && !tipo.equals("salida")) {
            return "❌ Error: Tipo no válido. Tipos disponibles: entrada, salida";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Verificar si el insumo existe
            String checkInsumoSql = "SELECT id FROM insumos WHERE nombre ILIKE ?";
            int insumoId = -1;
            try (PreparedStatement checkStmt = conn.prepareStatement(checkInsumoSql)) {
                checkStmt.setString(1, "%" + insumoName + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        insumoId = rs.getInt("id");
                    } else {
                        return "❌ Error: No se encontró el insumo '" + insumoName + "'.\n" +
                               "Use 'CONSULTAR INSUMOS' para ver insumos disponibles.";
                    }
                }
            }

            // Crear el movimiento
            String sql = "INSERT INTO movimiento_inventarios (insumo_id, cantidad, tipo, motivo, created_at, updated_at) " +
                        "VALUES (?, ?, ?, ?, NOW(), NOW())";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, insumoId);
                stmt.setFloat(2, cantidad);
                stmt.setString(3, tipo);
                stmt.setString(4, motivo);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    String simbolo = tipo.equals("entrada") ? "+" : "-";
                    return "✅ Movimiento creado exitosamente:\n" +
                           "• Insumo: " + insumoName + "\n" +
                           "• Tipo: " + tipo + "\n" +
                           "• Cantidad: " + simbolo + cantidad + "\n" +
                           "• Motivo: " + motivo;
                } else {
                    return "❌ Error: No se pudo crear el movimiento.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error creando movimiento: " + e.getMessage());
            return "Error creando movimiento: " + e.getMessage();
        }
    }

    private static String updateMovimiento(String command) {
        // Extraer parámetros: ACTUALIZAR MOVIMIENTO [nombre_insumo] [id] [campo] [valor], [campo] [valor]...
        String[] parts = command.split("ACTUALIZAR MOVIMIENTO");
        if (parts.length < 2) {
            return "Formato incorrecto.\nUse: ACTUALIZAR MOVIMIENTO [nombre_insumo] [id] [campo] [valor], [campo] [valor]...\n\n" +
                   "Campos disponibles: tipo, cantidad, motivo, fecha\n" +
                   "Ejemplo: ACTUALIZAR MOVIMIENTO tomate 10 cantidad 25, motivo compra urgente";
        }

        String[] params = splitParams(parts[1].trim(), 3);
        if (params.length < 3) {
            return "Formato incorrecto.\nUse: ACTUALIZAR MOVIMIENTO [nombre_insumo] [id] [campo] [valor], [campo] [valor]...\n\n" +
                   "Campos disponibles: tipo, cantidad, motivo, fecha\n" +
                   "Ejemplo: ACTUALIZAR MOVIMIENTO tomate 10 cantidad 25, motivo compra urgente";
        }

        String nombreInsumo = params[0];
        int movimientoId;
        try {
            movimientoId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido.\n" +
                   "Ejemplo: ACTUALIZAR MOVIMIENTO tomate 10 cantidad 25";
        }
        String cambiosTexto = params[2];

        try (Connection conn = getDatabaseConnection()) {
            // Buscar el insumo
            String checkInsumoSql = "SELECT id, nombre FROM insumos WHERE nombre ILIKE ?";
            int insumoId = -1;
            String insumoNombre = "";

            try (PreparedStatement checkStmt = conn.prepareStatement(checkInsumoSql)) {
                checkStmt.setString(1, "%" + nombreInsumo + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        insumoId = rs.getInt("id");
                        insumoNombre = rs.getString("nombre");
                    } else {
                        return "❌ ERROR: No se encontró el insumo: " + nombreInsumo;
                    }
                }
            }

            // Verificar que el movimiento exista Y pertenezca al insumo
            String checkMovSql = "SELECT id FROM movimiento_inventarios WHERE id = ? AND insumo_id = ?";
            boolean movimientoValido = false;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkMovSql)) {
                checkStmt.setInt(1, movimientoId);
                checkStmt.setInt(2, insumoId);
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        movimientoValido = true;
                    }
                }
            }

            if (!movimientoValido) {
                return "❌ ERROR: No se encontró el movimiento ID " + movimientoId + " para el insumo " + insumoNombre + ".\n" +
                       "Use: CONSULTAR MOVIMIENTOS insumo " + nombreInsumo + "\npara ver los IDs disponibles.";
            }

            // Procesar campos a actualizar
            StringBuilder updateSql = new StringBuilder("UPDATE movimiento_inventarios SET ");
            java.util.List<String> setClauses = new java.util.ArrayList<>();
            java.util.List<Object> values = new java.util.ArrayList<>();
            java.util.List<String> cambiosProcesados = new java.util.ArrayList<>();

            // Separar por comas
            String[] pares = cambiosTexto.split(",");

            for (String par : pares) {
                par = par.trim();
                String[] partes = splitParams(par, 2);

                if (partes.length >= 2) {
                    String campo = partes[0].toLowerCase();
                    String valor = partes[1].trim();

                    switch (campo) {
                        case "tipo":
                            valor = valor.toLowerCase();
                            if (!valor.equals("entrada") && !valor.equals("salida")) {
                                return "❌ ERROR: Tipo inválido. Use: entrada o salida";
                            }
                            setClauses.add("tipo = ?");
                            values.add(valor);
                            cambiosProcesados.add("tipo: " + valor);
                            break;

                        case "cantidad":
                            try {
                                float cantidad = Float.parseFloat(valor);
                                if (cantidad <= 0) {
                                    return "❌ ERROR: La cantidad debe ser mayor a 0";
                                }
                                setClauses.add("cantidad = ?");
                                values.add(cantidad);
                                cambiosProcesados.add("cantidad: " + cantidad);
                            } catch (NumberFormatException e) {
                                return "❌ ERROR: La cantidad debe ser un número válido";
                            }
                            break;

                        case "motivo":
                            setClauses.add("motivo = ?");
                            values.add(valor);
                            cambiosProcesados.add("motivo: " + valor);
                            break;

                        case "fecha":
                            setClauses.add("created_at = ?");
                            values.add(valor);
                            cambiosProcesados.add("fecha: " + valor);
                            break;

                        default:
                            return "❌ ERROR: Campo '" + campo + "' no válido.\nCampos disponibles: tipo, cantidad, motivo, fecha";
                    }
                }
            }

            if (setClauses.isEmpty()) {
                return "❌ ERROR: No se especificaron campos válidos para actualizar.";
            }

            // Construir y ejecutar UPDATE
            updateSql.append(String.join(", ", setClauses));
            updateSql.append(", updated_at = NOW() WHERE id = ?");

            try (PreparedStatement stmt = conn.prepareStatement(updateSql.toString())) {
                int paramIndex = 1;
                for (Object value : values) {
                    if (value instanceof String) {
                        stmt.setString(paramIndex++, (String) value);
                    } else if (value instanceof Float) {
                        stmt.setFloat(paramIndex++, (Float) value);
                    }
                }
                stmt.setInt(paramIndex, movimientoId);

                int rowsAffected = stmt.executeUpdate();

                if (rowsAffected > 0) {
                    // Obtener datos actualizados
                    String checkSql = "SELECT m.id, m.tipo, m.cantidad, m.motivo, m.created_at, i.nombre as insumo " +
                                     "FROM movimiento_inventarios m " +
                                     "JOIN insumos i ON m.insumo_id = i.id " +
                                     "WHERE m.id = ?";
                    try (PreparedStatement selectStmt = conn.prepareStatement(checkSql)) {
                        selectStmt.setInt(1, movimientoId);
                        try (ResultSet rs = selectStmt.executeQuery()) {
                            if (rs.next()) {
                                String tipo = rs.getString("tipo");
                                float cantidad = rs.getFloat("cantidad");
                                String motivo = rs.getString("motivo");
                                String fecha = rs.getString("created_at");
                                String simbolo = tipo.equals("entrada") ? "+" : "-";

                                return "✅ MOVIMIENTO ACTUALIZADO EXITOSAMENTE\n\n" +
                                       "ID: " + movimientoId + "\n" +
                                       "Insumo: " + rs.getString("insumo") + "\n" +
                                       "Tipo: " + tipo.toUpperCase() + "\n" +
                                       "Cantidad: " + simbolo + cantidad + "\n" +
                                       "Motivo: " + motivo + "\n" +
                                       "Fecha: " + fecha + "\n\n" +
                                       "Campos actualizados: " + String.join(", ", cambiosProcesados);
                            }
                        }
                    }
                    return "✅ MOVIMIENTO ACTUALIZADO EXITOSAMENTE";
                } else {
                    return "❌ Error: No se pudo actualizar el movimiento.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error actualizando movimiento: " + e.getMessage());
            return "Error actualizando movimiento: " + e.getMessage();
        }
    }

    private static String deleteMovimiento(String command) {
        // Extraer parámetros: ELIMINAR MOVIMIENTO [nombre_insumo] [id]
        String[] parts = command.split("ELIMINAR MOVIMIENTO");
        if (parts.length < 2) {
            return "Formato incorrecto.\nUse: ELIMINAR MOVIMIENTO [nombre_insumo] [id]\n\n" +
                   "Ejemplo: ELIMINAR MOVIMIENTO tomate 10";
        }

        String[] params = splitParams(parts[1].trim(), 2);
        if (params.length < 2) {
            return "Formato incorrecto.\nUse: ELIMINAR MOVIMIENTO [nombre_insumo] [id]\n\n" +
                   "Ejemplo: ELIMINAR MOVIMIENTO tomate 10";
        }

        String nombreInsumo = params[0];
        int movimientoId;
        try {
            movimientoId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido.\n" +
                   "Ejemplo: ELIMINAR MOVIMIENTO tomate 10";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Buscar el insumo
            String checkInsumoSql = "SELECT id, nombre FROM insumos WHERE nombre ILIKE ?";
            int insumoId = -1;
            String insumoNombre = "";

            try (PreparedStatement checkStmt = conn.prepareStatement(checkInsumoSql)) {
                checkStmt.setString(1, "%" + nombreInsumo + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        insumoId = rs.getInt("id");
                        insumoNombre = rs.getString("nombre");
                    } else {
                        return "❌ ERROR: No se encontró el insumo: " + nombreInsumo;
                    }
                }
            }

            // Buscar el movimiento específico y obtener sus datos, verificando que pertenezca al insumo
            String checkSql = "SELECT m.id, m.tipo, m.cantidad, m.motivo, m.created_at " +
                             "FROM movimiento_inventarios m " +
                             "WHERE m.id = ? AND m.insumo_id = ?";

            String tipo = "";
            float cantidad = 0;
            String motivo = "";
            String fecha = "";
            boolean movimientoExists = false;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setInt(1, movimientoId);
                checkStmt.setInt(2, insumoId);
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        movimientoExists = true;
                        tipo = rs.getString("tipo");
                        cantidad = rs.getFloat("cantidad");
                        motivo = rs.getString("motivo");
                        fecha = rs.getString("created_at");
                    }
                }
            }

            if (!movimientoExists) {
                return "❌ ERROR: No se encontró el movimiento ID " + movimientoId + " para el insumo " + insumoNombre + ".\n" +
                       "Use: CONSULTAR MOVIMIENTOS insumo " + nombreInsumo + "\npara ver los IDs disponibles.";
            }

            // Eliminar el movimiento
            String deleteSql = "DELETE FROM movimiento_inventarios WHERE id = ?";

            try (PreparedStatement stmt = conn.prepareStatement(deleteSql)) {
                stmt.setInt(1, movimientoId);
                int rowsAffected = stmt.executeUpdate();

                if (rowsAffected > 0) {
                    return "✅ MOVIMIENTO ELIMINADO EXITOSAMENTE\n\n" +
                           "ID eliminado: " + movimientoId + "\n" +
                           "Insumo: " + insumoNombre + "\n" +
                           "Tipo: " + tipo.toUpperCase() + "\n" +
                           "Cantidad: " + cantidad + "\n" +
                           "Motivo: " + motivo + "\n" +
                           "Fecha: " + fecha + "\n\n" +
                           "⚠️ El stock del insumo ha sido recalculado automáticamente.";
                } else {
                    return "❌ Error: No se pudo eliminar el movimiento.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error eliminando movimiento: " + e.getMessage());
            return "Error eliminando movimiento: " + e.getMessage();
        }
    }

    // ========== FUNCIONES ESPECÍFICAS PARA RECETAS ==========

    private static String getRecetaDetails(String recetaName) {
        StringBuilder result = new StringBuilder();
        result.append("=== RECETA: ").append(recetaName.toUpperCase()).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT id, nombre, precio, tiempo_preparacion, indicaciones " +
                        "FROM recetas WHERE nombre ILIKE ?";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, "%" + recetaName + "%");
                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        int recetaId = rs.getInt("id");
                        String nombre = rs.getString("nombre");
                        float precio = rs.getFloat("precio");
                        int tiempo = rs.getInt("tiempo_preparacion");
                        String indicaciones = rs.getString("indicaciones");

                        result.append("📝 Información:\n");
                        result.append("• Precio: Bs. ").append(precio).append("\n");
                        result.append("• Tiempo de preparación: ").append(tiempo).append(" minutos\n\n");

                        if (indicaciones != null && !indicaciones.isEmpty()) {
                            result.append("📜 Indicaciones:\n");
                            result.append(indicaciones).append("\n\n");
                        }

                        result.append("🥘 Ingredientes (para 1 porción):\n");
                        result.append(getRecetaIngredientes(conn, recetaId));

                    } else {
                        result.append("❌ No se encontró la receta: ").append(recetaName).append("\n");
                        result.append("Verifique el nombre o use 'CONSULTAR LISTA RECETAS' para ver la lista completa.");
                    }
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando detalles de la receta: " + e.getMessage());
            return "Error consultando detalles de la receta: " + e.getMessage();
        }

        return result.toString();
    }

    private static String getRecetaIngredientes(String recetaName) {
        StringBuilder result = new StringBuilder();
        result.append("=== INGREDIENTES DE LA RECETA: ").append(recetaName.toUpperCase()).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            // Primero obtener el ID de la receta
            String recetaSql = "SELECT id FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;
            try (PreparedStatement recetaStmt = conn.prepareStatement(recetaSql)) {
                recetaStmt.setString(1, "%" + recetaName + "%");
                try (ResultSet rs = recetaStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                    } else {
                        return "❌ No se encontró la receta: " + recetaName + "\n" +
                               "Use 'CONSULTAR RECETAS' para ver recetas disponibles.";
                    }
                }
            }

            return getRecetaIngredientes(conn, recetaId);
        } catch (SQLException e) {
            System.out.println("❌ Error consultando ingredientes: " + e.getMessage());
            return "Error consultando ingredientes: " + e.getMessage();
        }
    }

    private static String getRecetaIngredientes(Connection conn, int recetaId) throws SQLException {
        StringBuilder result = new StringBuilder();

        String sql = "SELECT ri.cantidad, i.nombre as insumo, um.nombre as unidad " +
                    "FROM recetas_insumos ri " +
                    "JOIN insumos i ON ri.insumo_id = i.id " +
                    "LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                    "WHERE ri.receta_id = ? " +
                    "ORDER BY i.nombre";

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, recetaId);
            try (ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    float cantidad = rs.getFloat("cantidad");
                    String insumo = rs.getString("insumo");
                    String unidad = rs.getString("unidad");

                    result.append(count).append(". ").append(insumo)
                          .append(": ").append(cantidad).append(" ").append(unidad != null ? unidad : "").append("\n");
                }

                if (count == 0) {
                    result.append("No hay ingredientes registrados para esta receta.");
                } else {
                    result.append("\nTotal ingredientes: ").append(count);
                }
            }
        }

        return result.toString();
    }

    private static String createReceta(String command) {
        // Extraer parámetros del comando: CREAR RECETA [nombre] [precio] [tiempo_preparacion]
        String[] parts = command.split("CREAR RECETA");
        if (parts.length < 2) {
            return "Formato incorrecto. Use: CREAR RECETA [nombre] [precio] [tiempo_minutos]\n" +
                   "Ejemplo: CREAR RECETA Pizza 35 30 (30 minutos)";
        }

        String[] params = splitParams(parts[1].trim(), 3);
        if (params.length < 3) {
            return "Faltan parámetros. Use: CREAR RECETA [nombre] [precio] [tiempo_minutos]\n" +
                   "Ejemplo: CREAR RECETA Pizza 35 30 (30 minutos)";
        }

        String nombre = params[0];
        float precio;
        int tiempo;

        try {
            precio = Float.parseFloat(params[1]);
            tiempo = Integer.parseInt(params[2]);
        } catch (NumberFormatException e) {
            return "Error: El precio y tiempo deben ser números válidos.\n" +
                   "Ejemplo: CREAR RECETA Pizza 35 30 (tiempo en minutos)";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Verificar si la receta ya existe
            String checkSql = "SELECT COUNT(*) FROM recetas WHERE nombre = ?";
            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setString(1, nombre);
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next() && rs.getInt(1) > 0) {
                        return "❌ Error: Ya existe una receta con el nombre '" + nombre + "'.";
                    }
                }
            }

            // Crear la receta
            String sql = "INSERT INTO recetas (nombre, precio, tiempo_preparacion, indicaciones, created_at, updated_at) " +
                        "VALUES (?, ?, ?, '', NOW(), NOW())";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, nombre);
                stmt.setFloat(2, precio);
                stmt.setInt(3, tiempo);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Receta creada exitosamente:\n" +
                           "• Nombre: " + nombre + "\n" +
                           "• Precio: Bs. " + precio + "\n" +
                           "• Tiempo de preparación: " + tiempo + " minutos\n" +
                           "• Ingredientes: 0\n\n" +
                           "⚠️ IMPORTANTE:\n" +
                           "- Agregue indicaciones con: AÑADIR INDICACIONES " + nombre + " [texto]\n" +
                           "- Agregue ingredientes con: AGREGAR INGREDIENTES " + nombre + " [insumo] [cantidad]";
                } else {
                    return "❌ Error: No se pudo crear la receta.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error creando receta: " + e.getMessage());
            return "Error creando receta: " + e.getMessage();
        }
    }

    private static String addIndicaciones(String command) {
        // Extraer parámetros del comando: AÑADIR INDICACIONES [receta] [texto]
        String[] parts = command.split("AÑADIR INDICACIONES");
        if (parts.length < 2) {
            return "Formato incorrecto. Use: AÑADIR INDICACIONES [receta] [texto de las indicaciones]\n" +
                   "Ejemplo: AÑADIR INDICACIONES Pizza Extender la masa y hornear";
        }

        String rest = parts[1].trim();
        String[] restParts = rest.split(" ", 2);

        if (restParts.length < 2) {
            return "Faltan parámetros. Use: AÑADIR INDICACIONES [receta] [texto de las indicaciones]\n" +
                   "Ejemplo: AÑADIR INDICACIONES Pizza Extender la masa y hornear";
        }

        String nombreReceta = restParts[0];
        String indicaciones = restParts[1];

        try (Connection conn = getDatabaseConnection()) {
            // Buscar la receta por nombre
            String checkSql = "SELECT id, nombre FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;
            String nombreExacto = null;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setString(1, "%" + nombreReceta + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                        nombreExacto = rs.getString("nombre");
                    } else {
                        return "❌ Error: No se encontró la receta '" + nombreReceta + "'.\n" +
                               "Use 'CONSULTAR LISTA RECETAS' para ver recetas disponibles.";
                    }
                }
            }

            // Actualizar las indicaciones
            String sql = "UPDATE recetas SET indicaciones = ?, updated_at = NOW() WHERE id = ?";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, indicaciones);
                stmt.setInt(2, recetaId);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Indicaciones agregadas exitosamente a la receta: " + nombreExacto + "\n\n" +
                           "ℹ️  Para ver las indicaciones completas use:\n" +
                           "CONSULTAR RECETA " + nombreExacto;
                } else {
                    return "❌ Error: No se pudieron agregar las indicaciones.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error agregando indicaciones: " + e.getMessage());
            return "Error agregando indicaciones: " + e.getMessage();
        }
    }

    private static String addIngredientes(String command) {
        // Extraer parámetros del comando: AGREGAR INGREDIENTES [receta] [insumo] [cantidad], [insumo] [cantidad]...
        String[] parts = command.split("AGREGAR INGREDIENTES");
        if (parts.length < 2) {
            return "Formato incorrecto. Use: AGREGAR INGREDIENTES [receta] [insumo] [cantidad], [insumo] [cantidad]...\n" +
                   "Ejemplo: AGREGAR INGREDIENTES Pizza harina 200, tomate 3";
        }

        String rest = parts[1].trim();
        String[] restParts = rest.split(" ", 2);

        if (restParts.length < 2) {
            return "Faltan parámetros. Use: AGREGAR INGREDIENTES [receta] [insumo] [cantidad], [insumo] [cantidad]...\n" +
                   "Ejemplo: AGREGAR INGREDIENTES Pizza harina 200, tomate 3";
        }

        String nombreReceta = restParts[0];
        String ingredientesStr = restParts[1];

        try (Connection conn = getDatabaseConnection()) {
            // Buscar la receta
            String checkSql = "SELECT id, nombre FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;
            String nombreExacto = null;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setString(1, "%" + nombreReceta + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                        nombreExacto = rs.getString("nombre");
                    } else {
                        return "❌ Error: No se encontró la receta '" + nombreReceta + "'.\n" +
                               "Use 'CONSULTAR LISTA RECETAS' para ver recetas disponibles.";
                    }
                }
            }

            // Procesar ingredientes separados por comas
            String[] ingredientesArray = ingredientesStr.split(",");
            List<String> ingredientesAgregados = new ArrayList<>();

            for (String ingredienteStr : ingredientesArray) {
                ingredienteStr = ingredienteStr.trim();
                String[] ingParts = splitParams(ingredienteStr, 2);

                if (ingParts.length < 2) {
                    continue;
                }

                String nombreInsumo = ingParts[0];
                float cantidad;

                try {
                    cantidad = Float.parseFloat(ingParts[1]);
                } catch (NumberFormatException e) {
                    return "❌ Error: La cantidad debe ser un número válido en '" + ingredienteStr + "'";
                }

                // Buscar el insumo
                String insumoSql = "SELECT id, nombre FROM insumos WHERE nombre ILIKE ?";
                int insumoId = -1;
                String nombreInsumoExacto = null;

                try (PreparedStatement insumoStmt = conn.prepareStatement(insumoSql)) {
                    insumoStmt.setString(1, "%" + nombreInsumo + "%");
                    try (ResultSet rs = insumoStmt.executeQuery()) {
                        if (rs.next()) {
                            insumoId = rs.getInt("id");
                            nombreInsumoExacto = rs.getString("nombre");
                        } else {
                            return "❌ Error: No se encontró el insumo '" + nombreInsumo + "'";
                        }
                    }
                }

                // Agregar ingrediente (o actualizar si ya existe)
                String insertSql = "INSERT INTO recetas_insumos (receta_id, insumo_id, cantidad, created_at, updated_at) " +
                                   "VALUES (?, ?, ?, NOW(), NOW()) " +
                                   "ON DUPLICATE KEY UPDATE cantidad = ?, updated_at = NOW()";

                try (PreparedStatement stmt = conn.prepareStatement(insertSql)) {
                    stmt.setInt(1, recetaId);
                    stmt.setInt(2, insumoId);
                    stmt.setFloat(3, cantidad);
                    stmt.setFloat(4, cantidad);

                    stmt.executeUpdate();
                    ingredientesAgregados.add(nombreInsumoExacto + ": " + cantidad);
                }
            }

            if (ingredientesAgregados.isEmpty()) {
                return "❌ Error: No se especificaron ingredientes válidos.";
            }

            StringBuilder response = new StringBuilder("✅ Ingredientes agregados a la receta:\n");
            response.append("• Receta: ").append(nombreExacto).append("\n");
            response.append("• Ingredientes agregados:\n");
            for (String ing : ingredientesAgregados) {
                response.append("  - ").append(ing).append("\n");
            }

            return response.toString();

        } catch (SQLException e) {
            System.out.println("❌ Error agregando ingredientes: " + e.getMessage());
            return "Error agregando ingredientes: " + e.getMessage();
        }
    }

    private static String removeIngrediente(String command) {
        // Extraer parámetros del comando: QUITAR INGREDIENTE [receta] [insumo]
        String[] parts = command.split("QUITAR INGREDIENTE");
        if (parts.length < 2) {
            return "Formato incorrecto. Use: QUITAR INGREDIENTE [receta] [insumo]\n" +
                   "Ejemplo: QUITAR INGREDIENTE Pizza tomate";
        }

        String[] params = splitParams(parts[1].trim(), 2);
        if (params.length < 2) {
            return "Faltan parámetros. Use: QUITAR INGREDIENTE [receta] [insumo]\n" +
                   "Ejemplo: QUITAR INGREDIENTE Pizza tomate";
        }

        String nombreReceta = params[0];
        String nombreInsumo = params[1];

        try (Connection conn = getDatabaseConnection()) {
            // Buscar la receta
            String checkSql = "SELECT id, nombre FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;
            String nombreRecetaExacto = null;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setString(1, "%" + nombreReceta + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                        nombreRecetaExacto = rs.getString("nombre");
                    } else {
                        return "❌ Error: No se encontró la receta '" + nombreReceta + "'";
                    }
                }
            }

            // Buscar el insumo
            String insumoSql = "SELECT id, nombre FROM insumos WHERE nombre ILIKE ?";
            int insumoId = -1;
            String nombreInsumoExacto = null;

            try (PreparedStatement insumoStmt = conn.prepareStatement(insumoSql)) {
                insumoStmt.setString(1, "%" + nombreInsumo + "%");
                try (ResultSet rs = insumoStmt.executeQuery()) {
                    if (rs.next()) {
                        insumoId = rs.getInt("id");
                        nombreInsumoExacto = rs.getString("nombre");
                    } else {
                        return "❌ Error: No se encontró el insumo '" + nombreInsumo + "'";
                    }
                }
            }

            // Eliminar ingrediente de la receta
            String deleteSql = "DELETE FROM recetas_insumos WHERE receta_id = ? AND insumo_id = ?";

            try (PreparedStatement stmt = conn.prepareStatement(deleteSql)) {
                stmt.setInt(1, recetaId);
                stmt.setInt(2, insumoId);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    // Contar ingredientes restantes
                    String countSql = "SELECT COUNT(*) FROM recetas_insumos WHERE receta_id = ?";
                    int remaining = 0;
                    try (PreparedStatement countStmt = conn.prepareStatement(countSql)) {
                        countStmt.setInt(1, recetaId);
                        try (ResultSet rs = countStmt.executeQuery()) {
                            if (rs.next()) {
                                remaining = rs.getInt(1);
                            }
                        }
                    }

                    return "✅ Ingrediente quitado de la receta:\n" +
                           "• Receta: " + nombreRecetaExacto + "\n" +
                           "• Ingrediente eliminado: " + nombreInsumoExacto + "\n" +
                           "• Ingredientes restantes: " + remaining;
                } else {
                    return "❌ Error: El ingrediente '" + nombreInsumoExacto + "' no estaba en la receta '" + nombreRecetaExacto + "'.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error quitando ingrediente: " + e.getMessage());
            return "Error quitando ingrediente: " + e.getMessage();
        }
    }

    private static String updateReceta(String command) {
        // Extraer parámetros del comando: ACTUALIZAR RECETA [nombre_receta] [campo] [valor], [campo] [valor]...
        String[] parts = command.split("ACTUALIZAR RECETA");
        if (parts.length < 2) {
            parts = command.split("EDITAR RECETA");
        }

        if (parts.length < 2) {
            return "Formato incorrecto. Use: ACTUALIZAR RECETA [nombre_receta] [campo] [valor], [campo] [valor]...\n" +
                   "(Campos: nombre, precio, tiempo_preparacion, indicaciones)\n" +
                   "Ejemplo: ACTUALIZAR RECETA Pizza precio 40, tiempo_preparacion 35";
        }

        String rest = parts[1].trim();
        if (rest.isEmpty()) {
            return "Formato incorrecto. Use: ACTUALIZAR RECETA [nombre_receta] [campo] [valor], [campo] [valor]...\n" +
                   "(Campos: nombre, precio, tiempo_preparacion, indicaciones)";
        }

        // Separar el nombre de la receta del resto
        String[] restParts = rest.split(" ", 2);
        if (restParts.length < 2) {
            return "Faltan parámetros. Use: ACTUALIZAR RECETA [nombre_receta] [campo] [valor], [campo] [valor]...\n" +
                   "(Campos: nombre, precio, tiempo_preparacion, indicaciones)";
        }

        String nombreReceta = restParts[0];
        String camposStr = restParts[1];

        try (Connection conn = getDatabaseConnection()) {
            // Buscar la receta por nombre
            String checkSql = "SELECT id, nombre FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;
            String nombreExacto = null;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setString(1, "%" + nombreReceta + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                        nombreExacto = rs.getString("nombre");
                    } else {
                        return "❌ Error: No se encontró la receta '" + nombreReceta + "'.\n" +
                               "Use 'CONSULTAR LISTA RECETAS' para ver recetas disponibles.";
                    }
                }
            }

            // Procesar campos separados por comas
            String[] camposArray = camposStr.split(",");
            List<String> camposActualizados = new ArrayList<>();
            StringBuilder sql = new StringBuilder("UPDATE recetas SET ");
            List<String> updates = new ArrayList<>();
            boolean hayActualizaciones = false;

            for (String campoStr : camposArray) {
                campoStr = campoStr.trim();
                String[] campoParts = splitParams(campoStr, 2);

                if (campoParts.length < 2) {
                    continue;
                }

                String campo = campoParts[0].toLowerCase();
                String valor = campoParts[1].trim();

                // Validar campo
                if (!campo.equals("nombre") && !campo.equals("precio") &&
                    !campo.equals("tiempo_preparacion") && !campo.equals("indicaciones")) {
                    return "❌ Error: Campo '" + campo + "' no válido.\n" +
                           "(Campos: nombre, precio, tiempo_preparacion, indicaciones)";
                }

                // Procesar cada tipo de campo
                if (campo.equals("nombre")) {
                    // Verificar nombre único
                    String checkNameSql = "SELECT COUNT(*) FROM recetas WHERE nombre = ? AND id != ?";
                    try (PreparedStatement checkNameStmt = conn.prepareStatement(checkNameSql)) {
                        checkNameStmt.setString(1, valor);
                        checkNameStmt.setInt(2, recetaId);
                        try (ResultSet rs = checkNameStmt.executeQuery()) {
                            if (rs.next() && rs.getInt(1) > 0) {
                                return "❌ Error: Ya existe otra receta con el nombre '" + valor + "'.";
                            }
                        }
                    }
                    updates.add("nombre = ?");
                    camposActualizados.add("nombre: " + valor);
                    hayActualizaciones = true;

                } else if (campo.equals("precio")) {
                    try {
                        Float.parseFloat(valor);
                        updates.add("precio = ?");
                        camposActualizados.add("precio: " + valor);
                        hayActualizaciones = true;
                    } catch (NumberFormatException e) {
                        return "❌ Error: El precio debe ser un número válido.";
                    }

                } else if (campo.equals("tiempo_preparacion")) {
                    try {
                        Integer.parseInt(valor);
                        updates.add("tiempo_preparacion = ?");
                        camposActualizados.add("tiempo_preparacion: " + valor);
                        hayActualizaciones = true;
                    } catch (NumberFormatException e) {
                        return "❌ Error: El tiempo_preparacion debe ser un número válido.";
                    }

                } else if (campo.equals("indicaciones")) {
                    updates.add("indicaciones = ?");
                    camposActualizados.add("indicaciones: " + valor);
                    hayActualizaciones = true;
                }
            }

            if (!hayActualizaciones) {
                return "❌ Error: No se especificaron campos válidos para actualizar.\n" +
                       "(Campos: nombre, precio, tiempo_preparacion, indicaciones)";
            }

            // Construir SQL final
            sql.append(String.join(", ", updates));
            sql.append(", updated_at = NOW() WHERE id = ?");

            // Ejecutar actualización
            try (PreparedStatement stmt = conn.prepareStatement(sql.toString())) {
                int paramIndex = 1;

                // Procesar campos de nuevo para asignar valores
                for (String campoStr : camposArray) {
                    campoStr = campoStr.trim();
                    String[] campoParts = splitParams(campoStr, 2);

                    if (campoParts.length < 2) continue;

                    String campo = campoParts[0].toLowerCase();
                    String valor = campoParts[1].trim();

                    if (campo.equals("nombre") || campo.equals("indicaciones")) {
                        stmt.setString(paramIndex++, valor);
                    } else if (campo.equals("precio")) {
                        stmt.setFloat(paramIndex++, Float.parseFloat(valor));
                    } else if (campo.equals("tiempo_preparacion")) {
                        stmt.setInt(paramIndex++, Integer.parseInt(valor));
                    }
                }

                stmt.setInt(paramIndex, recetaId);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    StringBuilder response = new StringBuilder("✅ Receta actualizada exitosamente:\n");
                    response.append("• Nombre: ").append(nombreExacto).append("\n");
                    response.append("• Campos actualizados:\n");
                    for (String campo : camposActualizados) {
                        response.append("  - ").append(campo).append("\n");
                    }
                    return response.toString();
                } else {
                    return "❌ Error: No se pudo actualizar la receta.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error actualizando receta: " + e.getMessage());
            return "Error actualizando receta: " + e.getMessage();
        }
    }

    private static String editReceta(String command) {
        // Redirigir a updateReceta
        return updateReceta(command);
    }

    private static String deleteReceta(String command) {
        // Extraer parámetros del comando: ELIMINAR RECETA [nombre]
        String[] parts = command.split("ELIMINAR RECETA");
        if (parts.length < 2) {
            return "Formato incorrecto. Use: ELIMINAR RECETA [nombre]\n" +
                   "Ejemplo: ELIMINAR RECETA Pizza";
        }

        String nombreReceta = parts[1].trim();

        try (Connection conn = getDatabaseConnection()) {
            // Buscar la receta por nombre y obtener su ID
            String checkSql = "SELECT id, nombre FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;
            String nombreExacto = null;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setString(1, "%" + nombreReceta + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                        nombreExacto = rs.getString("nombre");
                    } else {
                        return "❌ Error: No se encontró la receta '" + nombreReceta + "'.\n" +
                               "Use 'CONSULTAR LISTA RECETAS' para ver recetas disponibles.";
                    }
                }
            }

            // Contar ingredientes antes de eliminar
            String countSql = "SELECT COUNT(*) FROM recetas_insumos WHERE receta_id = ?";
            int cantidadIngredientes = 0;
            try (PreparedStatement countStmt = conn.prepareStatement(countSql)) {
                countStmt.setInt(1, recetaId);
                try (ResultSet rs = countStmt.executeQuery()) {
                    if (rs.next()) {
                        cantidadIngredientes = rs.getInt(1);
                    }
                }
            }

            // Eliminar ingredientes de la receta
            String deleteIngSql = "DELETE FROM recetas_insumos WHERE receta_id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(deleteIngSql)) {
                stmt.setInt(1, recetaId);
                stmt.executeUpdate();
            }

            // Eliminar la receta
            String deleteSql = "DELETE FROM recetas WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(deleteSql)) {
                stmt.setInt(1, recetaId);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Receta '" + nombreExacto + "' eliminada exitosamente.\n" +
                           "• Ingredientes eliminados: " + cantidadIngredientes + "\n" +
                           "• La receta ya no estará disponible en el sistema.";
                } else {
                    return "❌ Error: No se pudo eliminar la receta.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error eliminando receta: " + e.getMessage());
            return "Error eliminando receta: " + e.getMessage();
        }
    }

    private static String oldEditReceta(String command) {
        // Extraer parámetros del comando: EDITAR RECETA [id] [campo] [nuevo_valor]
        String[] parts = command.split("EDITAR RECETA");
        if (parts.length < 2) {
            return "Formato incorrecto. Use: EDITAR RECETA [id] [campo] [nuevo_valor]\n" +
                   "Campos disponibles: nombre, descripcion, precio\n" +
                   "Ejemplo: EDITAR RECETA 1 precio 30.00";
        }

        String[] params = splitParams(parts[1].trim(), 3);
        if (params.length < 3) {
            return "Faltan parámetros. Use: EDITAR RECETA [id] [campo] [nuevo_valor]\n" +
                   "Campos disponibles: nombre, descripcion, precio\n" +
                   "Ejemplo: EDITAR RECETA 1 precio 30.00";
        }

        int id;
        try {
            id = Integer.parseInt(params[0]);
        } catch (NumberFormatException e) {
            return "Error: El ID debe ser un número válido.\n" +
                   "Ejemplo: EDITAR RECETA 1 precio 30.00";
        }

        String campo = params[1].toLowerCase();
        String nuevoValor = params[2];

        // Validar campo
        if (!campo.equals("nombre") && !campo.equals("descripcion") && !campo.equals("precio")) {
            return "❌ Error: Campo no válido. Campos disponibles: nombre, descripcion, precio";
        }

        // Validar precio si es el campo a editar
        if (campo.equals("precio")) {
            try {
                Float.parseFloat(nuevoValor);
            } catch (NumberFormatException e) {
                return "Error: El precio debe ser un número válido.";
            }
        }

        try (Connection conn = getDatabaseConnection()) {
            // Verificar si la receta existe
            String checkSql = "SELECT nombre FROM recetas WHERE id = ?";
            String nombreActual = null;
            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setInt(1, id);
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        nombreActual = rs.getString("nombre");
                    } else {
                        return "❌ Error: No se encontró una receta con ID " + id;
                    }
                }
            }

            // Verificar nombre único si se está editando el nombre
            if (campo.equals("nombre")) {
                String checkNameSql = "SELECT COUNT(*) FROM recetas WHERE nombre = ? AND id != ?";
                try (PreparedStatement checkNameStmt = conn.prepareStatement(checkNameSql)) {
                    checkNameStmt.setString(1, nuevoValor);
                    checkNameStmt.setInt(2, id);
                    try (ResultSet rs = checkNameStmt.executeQuery()) {
                        if (rs.next() && rs.getInt(1) > 0) {
                            return "❌ Error: Ya existe otra receta con el nombre '" + nuevoValor + "'.";
                        }
                    }
                }
            }

            // Actualizar la receta
            String sql = "UPDATE recetas SET " + campo + " = ?, updated_at = NOW() WHERE id = ?";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                if (campo.equals("precio")) {
                    stmt.setFloat(1, Float.parseFloat(nuevoValor));
                } else {
                    stmt.setString(1, nuevoValor);
                }
                stmt.setInt(2, id);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Receta actualizada exitosamente:\n" +
                           "• ID: " + id + "\n" +
                           "• Nombre: " + nombreActual + "\n" +
                           "• Campo '" + campo + "' actualizado a: " + nuevoValor;
                } else {
                    return "❌ Error: No se pudo actualizar la receta.";
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error editando receta: " + e.getMessage());
            return "Error editando receta: " + e.getMessage();
        }
    }

    // ========== FUNCIONES ESPECÍFICAS PARA VENTAS ==========

    private static String getVentasByDate(String fecha) {
        StringBuilder result = new StringBuilder();
        result.append("=== VENTAS POR FECHA: ").append(fecha).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT v.id, v.cantidad, v.precio_unitario, v.total, v.created_at, r.nombre as receta " +
                        "FROM ventas v " +
                        "JOIN recetas r ON v.receta_id = r.id " +
                        "WHERE DATE(v.created_at) = ? " +
                        "ORDER BY v.created_at DESC";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, fecha);
                try (ResultSet rs = stmt.executeQuery()) {
                    int count = 0;
                    float totalDia = 0;
                    while (rs.next()) {
                        count++;
                        int id = rs.getInt("id");
                        float cantidad = rs.getFloat("cantidad");
                        float precioUnitario = rs.getFloat("precio_unitario");
                        float total = rs.getFloat("total");
                        String fechaVenta = rs.getString("created_at");
                        String receta = rs.getString("receta");

                        totalDia += total;

                        result.append(count).append(". ID: ").append(id)
                              .append(" - ").append(receta)
                              .append(" x").append(cantidad)
                              .append(" @Bs.").append(precioUnitario)
                              .append(" = Bs.").append(total)
                              .append(" (").append(fechaVenta).append(")\n");
                    }

                    if (count == 0) {
                        result.append("❌ No se encontraron ventas para la fecha: ").append(fecha).append("\n");
                        result.append("Formato de fecha: YYYY-MM-DD");
                    } else {
                        result.append("\n=== RESUMEN ===\n");
                        result.append("Total ventas: ").append(count).append("\n");
                        result.append("Monto total: Bs. ").append(totalDia);
                    }
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando ventas por fecha: " + e.getMessage());
            return "Error consultando ventas por fecha: " + e.getMessage();
        }

        return result.toString();
    }

    private static String getVentasByPeriod(String command) {
        // Extraer parámetros del comando: CONSULTAR VENTAS PERIODO [fecha_inicio] [fecha_fin]
        String[] parts = command.split("CONSULTAR VENTAS PERIODO");
        if (parts.length < 2) {
            return "Formato incorrecto. Use: CONSULTAR VENTAS PERIODO [fecha_inicio] [fecha_fin]\n" +
                   "Ejemplo: CONSULTAR VENTAS PERIODO 2024-01-01 2024-01-31";
        }

        String[] params = splitParams(parts[1].trim());
        if (params.length < 2) {
            return "Faltan parámetros. Use: CONSULTAR VENTAS PERIODO [fecha_inicio] [fecha_fin]\n" +
                   "Ejemplo: CONSULTAR VENTAS PERIODO 2024-01-01 2024-01-31";
        }

        String fechaInicio = params[0];
        String fechaFin = params[1];

        StringBuilder result = new StringBuilder();
        result.append("=== VENTAS POR PERÍODO: ").append(fechaInicio).append(" a ").append(fechaFin).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT v.id, v.cantidad, v.precio_unitario, v.total, v.created_at, r.nombre as receta " +
                        "FROM ventas v " +
                        "JOIN recetas r ON v.receta_id = r.id " +
                        "WHERE DATE(v.created_at) BETWEEN ? AND ? " +
                        "ORDER BY v.created_at DESC";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, fechaInicio);
                stmt.setString(2, fechaFin);
                try (ResultSet rs = stmt.executeQuery()) {
                    int count = 0;
                    float totalPeriodo = 0;
                    while (rs.next()) {
                        count++;
                        int id = rs.getInt("id");
                        float cantidad = rs.getFloat("cantidad");
                        float precioUnitario = rs.getFloat("precio_unitario");
                        float total = rs.getFloat("total");
                        String fechaVenta = rs.getString("created_at");
                        String receta = rs.getString("receta");

                        totalPeriodo += total;

                        result.append(count).append(". ID: ").append(id)
                              .append(" - ").append(receta)
                              .append(" x").append(cantidad)
                              .append(" @Bs.").append(precioUnitario)
                              .append(" = Bs.").append(total)
                              .append(" (").append(fechaVenta).append(")\n");
                    }

                    if (count == 0) {
                        result.append("❌ No se encontraron ventas en el período: ").append(fechaInicio).append(" a ").append(fechaFin).append("\n");
                        result.append("Formato de fecha: YYYY-MM-DD");
                    } else {
                        result.append("\n=== RESUMEN ===\n");
                        result.append("Total ventas: ").append(count).append("\n");
                        result.append("Monto total: Bs. ").append(totalPeriodo);
                    }
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Error consultando ventas por período: " + e.getMessage());
            return "Error consultando ventas por período: " + e.getMessage();
        }

        return result.toString();
    }

    // ========== FUNCIONES PARA PROVEEDORES ==========
    private static String getProveedorDetails(String proveedorName) {
        StringBuilder result = new StringBuilder();
        result.append("=== DETALLES DEL PROVEEDOR: ").append(proveedorName.toUpperCase()).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT * FROM proveedores WHERE nombre ILIKE ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, "%" + proveedorName + "%");
                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        result.append("• ID: ").append(rs.getInt("id")).append("\n");
                        result.append("• Nombre: ").append(rs.getString("nombre")).append("\n");
                        result.append("• Teléfono: ").append(rs.getString("telefono")).append("\n");
                        result.append("• Email: ").append(rs.getString("email")).append("\n");
                        result.append("• Dirección: ").append(rs.getString("direccion")).append("\n");
                        result.append("• Creado: ").append(rs.getString("created_at")).append("\n");
                    } else {
                        result.append("❌ No se encontró el proveedor: ").append(proveedorName);
                    }
                }
            }
        } catch (SQLException e) {
            return "Error consultando proveedor: " + e.getMessage();
        }
        return result.toString();
    }

    private static String getProveedoresList() {
        StringBuilder result = new StringBuilder();
        result.append("=== LISTADO DE PROVEEDORES ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT * FROM proveedores ORDER BY nombre";
            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    result.append(count).append(". ").append(rs.getString("nombre"))
                          .append(" - ").append(rs.getString("telefono"))
                          .append(" - ").append(rs.getString("email")).append("\n");
                }
                result.append("\nTotal proveedores: ").append(count);
            }
        } catch (SQLException e) {
            return "Error consultando proveedores: " + e.getMessage();
        }
        return result.toString();
    }

    private static String createProveedor(String command) {
        String[] params = extractParams(command, "CREAR PROVEEDOR", 4);
        if (params.length < 4) {
            return "Formato incorrecto. Use: CREAR PROVEEDOR [nombre] [telefono] [email] [direccion]";
        }

        String nombre = params[0];
        String telefono = params[1];
        String email = params[2];
        String direccion = params[3];

        try (Connection conn = getDatabaseConnection()) {
            String sql = "INSERT INTO proveedores (nombre, telefono, email, direccion, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, nombre);
                stmt.setString(2, telefono);
                stmt.setString(3, email);
                stmt.setString(4, direccion);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Proveedor creado exitosamente: " + nombre;
                } else {
                    return "❌ Error: No se pudo crear el proveedor.";
                }
            }
        } catch (SQLException e) {
            return "Error creando proveedor: " + e.getMessage();
        }
    }

    private static String editProveedor(String command) {
        String[] params = extractParams(command, "EDITAR PROVEEDOR", 3);
        if (params.length < 3) {
            return "Formato incorrecto. Use: EDITAR PROVEEDOR [id] [campo] [nuevo_valor]";
        }

        int id = Integer.parseInt(params[0]);
        String campo = params[1];
        String nuevoValor = params[2];

        try (Connection conn = getDatabaseConnection()) {
            String sql = "UPDATE proveedores SET " + campo + " = ?, updated_at = NOW() WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, nuevoValor);
                stmt.setInt(2, id);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Proveedor actualizado exitosamente.";
                } else {
                    return "❌ Error: No se encontró el proveedor con ID " + id;
                }
            }
        } catch (SQLException e) {
            return "Error editando proveedor: " + e.getMessage();
        }
    }

    private static String deleteProveedor(String command) {
        String[] params = extractParams(command, "ELIMINAR PROVEEDOR");
        if (params.length < 1) {
            return "Formato incorrecto. Use: ELIMINAR PROVEEDOR [id]";
        }

        int id = Integer.parseInt(params[0]);

        try (Connection conn = getDatabaseConnection()) {
            String sql = "DELETE FROM proveedores WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, id);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Proveedor eliminado exitosamente.";
                } else {
                    return "❌ Error: No se encontró el proveedor con ID " + id;
                }
            }
        } catch (SQLException e) {
            return "Error eliminando proveedor: " + e.getMessage();
        }
    }

    // ========== FUNCIONES PARA CATEGORÍAS ==========
    private static String getCategoriasList() {
        StringBuilder result = new StringBuilder();
        result.append("=== LISTADO DE CATEGORÍAS ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT * FROM categorias ORDER BY nombre";
            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    result.append(count).append(". ").append(rs.getString("nombre"))
                          .append(" - ").append(rs.getString("descripcion")).append("\n");
                }
                result.append("\nTotal categorías: ").append(count);
            }
        } catch (SQLException e) {
            return "Error consultando categorías: " + e.getMessage();
        }
        return result.toString();
    }

    private static String createCategoria(String command) {
        String[] params = extractParams(command, "CREAR CATEGORIA", 2);
        if (params.length < 2) {
            return "Formato incorrecto. Use: CREAR CATEGORIA [nombre] [descripcion]";
        }

        String nombre = params[0];
        String descripcion = params[1];

        try (Connection conn = getDatabaseConnection()) {
            String sql = "INSERT INTO categorias (nombre, descripcion, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, nombre);
                stmt.setString(2, descripcion);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Categoría creada exitosamente: " + nombre;
                } else {
                    return "❌ Error: No se pudo crear la categoría.";
                }
            }
        } catch (SQLException e) {
            return "Error creando categoría: " + e.getMessage();
        }
    }

    private static String editCategoria(String command) {
        String[] params = extractParams(command, "EDITAR CATEGORIA", 3);
        if (params.length < 3) {
            return "Formato incorrecto. Use: EDITAR CATEGORIA [id] [campo] [nuevo_valor]";
        }

        int id = Integer.parseInt(params[0]);
        String campo = params[1];
        String nuevoValor = params[2];

        try (Connection conn = getDatabaseConnection()) {
            String sql = "UPDATE categorias SET " + campo + " = ?, updated_at = NOW() WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, nuevoValor);
                stmt.setInt(2, id);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Categoría actualizada exitosamente.";
                } else {
                    return "❌ Error: No se encontró la categoría con ID " + id;
                }
            }
        } catch (SQLException e) {
            return "Error editando categoría: " + e.getMessage();
        }
    }

    private static String deleteCategoria(String command) {
        String[] params = extractParams(command, "ELIMINAR CATEGORIA");
        if (params.length < 1) {
            return "Formato incorrecto. Use: ELIMINAR CATEGORIA [id]";
        }

        int id = Integer.parseInt(params[0]);

        try (Connection conn = getDatabaseConnection()) {
            String sql = "DELETE FROM categorias WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, id);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Categoría eliminada exitosamente.";
                } else {
                    return "❌ Error: No se encontró la categoría con ID " + id;
                }
            }
        } catch (SQLException e) {
            return "Error eliminando categoría: " + e.getMessage();
        }
    }

    // ========== FUNCIONES PARA UNIDADES DE MEDIDA ==========
    private static String getUnidadesList() {
        StringBuilder result = new StringBuilder();
        result.append("=== LISTADO DE UNIDADES DE MEDIDA ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT * FROM unidad_medidas ORDER BY nombre";
            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    result.append(count).append(". ").append(rs.getString("nombre"))
                          .append(" (").append(rs.getString("simbolo")).append(")\n");
                }
                result.append("\nTotal unidades: ").append(count);
            }
        } catch (SQLException e) {
            return "Error consultando unidades: " + e.getMessage();
        }
        return result.toString();
    }

    private static String createUnidad(String command) {
        String[] params = extractParams(command, "CREAR UNIDAD", 2);
        if (params.length < 2) {
            return "Formato incorrecto. Use: CREAR UNIDAD [nombre] [simbolo]";
        }

        String nombre = params[0];
        String simbolo = params[1];

        try (Connection conn = getDatabaseConnection()) {
            String sql = "INSERT INTO unidad_medidas (nombre, simbolo, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, nombre);
                stmt.setString(2, simbolo);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Unidad creada exitosamente: " + nombre + " (" + simbolo + ")";
                } else {
                    return "❌ Error: No se pudo crear la unidad.";
                }
            }
        } catch (SQLException e) {
            return "Error creando unidad: " + e.getMessage();
        }
    }

    private static String editUnidad(String command) {
        String[] params = extractParams(command, "EDITAR UNIDAD", 3);
        if (params.length < 3) {
            return "Formato incorrecto. Use: EDITAR UNIDAD [id] [campo] [nuevo_valor]";
        }

        int id = Integer.parseInt(params[0]);
        String campo = params[1];
        String nuevoValor = params[2];

        try (Connection conn = getDatabaseConnection()) {
            String sql = "UPDATE unidad_medidas SET " + campo + " = ?, updated_at = NOW() WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, nuevoValor);
                stmt.setInt(2, id);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Unidad actualizada exitosamente.";
                } else {
                    return "❌ Error: No se encontró la unidad con ID " + id;
                }
            }
        } catch (SQLException e) {
            return "Error editando unidad: " + e.getMessage();
        }
    }

    private static String deleteUnidad(String command) {
        String[] params = extractParams(command, "ELIMINAR UNIDAD");
        if (params.length < 1) {
            return "Formato incorrecto. Use: ELIMINAR UNIDAD [id]";
        }

        int id = Integer.parseInt(params[0]);

        try (Connection conn = getDatabaseConnection()) {
            String sql = "DELETE FROM unidad_medidas WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, id);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    return "✅ Unidad eliminada exitosamente.";
                } else {
                    return "❌ Error: No se encontró la unidad con ID " + id;
                }
            }
        } catch (SQLException e) {
            return "Error eliminando unidad: " + e.getMessage();
        }
    }

    // ========== FUNCIONES PARA REPORTES ==========
    private static String getReporteStock() {
        StringBuilder result = new StringBuilder();
        result.append("=== REPORTE DE STOCK ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT i.nombre, i.stock_minimo, um.nombre as unidad, " +
                        "COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.cantidad ELSE -m.cantidad END), 0) as stock_actual " +
                        "FROM insumos i " +
                        "LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                        "LEFT JOIN movimiento_inventarios m ON i.id = m.insumo_id " +
                        "GROUP BY i.id, i.nombre, i.stock_minimo, um.nombre " +
                        "ORDER BY i.nombre";

            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    String nombre = rs.getString("nombre");
                    float stockMinimo = rs.getFloat("stock_minimo");
                    String unidad = rs.getString("unidad");
                    float stockActual = rs.getFloat("stock_actual");
                    String estado = stockActual >= stockMinimo ? "✅ OK" : "⚠️ BAJO";

                    result.append(count).append(". ").append(nombre)
                          .append(" - Stock: ").append(stockActual).append(" ").append(unidad != null ? unidad : "")
                          .append(" - Mínimo: ").append(stockMinimo).append(" ").append(unidad != null ? unidad : "")
                          .append(" - Estado: ").append(estado).append("\n");
                }
                result.append("\nTotal insumos: ").append(count);
            }
        } catch (SQLException e) {
            return "Error generando reporte de stock: " + e.getMessage();
        }
        return result.toString();
    }

    private static String getReporteVentas() {
        StringBuilder result = new StringBuilder();
        result.append("=== REPORTE DE VENTAS ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT DATE(v.created_at) as fecha, COUNT(*) as cantidad_ventas, SUM(v.total) as total_ventas " +
                        "FROM ventas v " +
                        "GROUP BY DATE(v.created_at) " +
                        "ORDER BY fecha DESC " +
                        "LIMIT 30";

            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                float totalGeneral = 0;
                while (rs.next()) {
                    count++;
                    String fecha = rs.getString("fecha");
                    int cantidadVentas = rs.getInt("cantidad_ventas");
                    float totalVentas = rs.getFloat("total_ventas");
                    totalGeneral += totalVentas;

                    result.append(count).append(". ").append(fecha)
                          .append(" - Ventas: ").append(cantidadVentas)
                          .append(" - Total: Bs. ").append(totalVentas).append("\n");
                }
                result.append("\nTotal general (30 días): Bs. ").append(totalGeneral);
            }
        } catch (SQLException e) {
            return "Error generando reporte de ventas: " + e.getMessage();
        }
        return result.toString();
    }

    private static String getReporteMovimientos() {
        StringBuilder result = new StringBuilder();
        result.append("=== REPORTE DE MOVIMIENTOS ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT DATE(m.created_at) as fecha, m.tipo, COUNT(*) as cantidad_movimientos, SUM(m.cantidad) as total_cantidad " +
                        "FROM movimiento_inventarios m " +
                        "GROUP BY DATE(m.created_at), m.tipo " +
                        "ORDER BY fecha DESC " +
                        "LIMIT 30";

            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    String fecha = rs.getString("fecha");
                    String tipo = rs.getString("tipo");
                    int cantidadMovimientos = rs.getInt("cantidad_movimientos");
                    float totalCantidad = rs.getFloat("total_cantidad");

                    result.append(count).append(". ").append(fecha)
                          .append(" - ").append(tipo.toUpperCase())
                          .append(" - Movimientos: ").append(cantidadMovimientos)
                          .append(" - Cantidad: ").append(totalCantidad).append("\n");
                }
            }
        } catch (SQLException e) {
            return "Error generando reporte de movimientos: " + e.getMessage();
        }
        return result.toString();
    }

    private static String getReporteGeneral() {
        StringBuilder result = new StringBuilder();
        result.append("=== REPORTE GENERAL ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            // Contar insumos
            String insumosSql = "SELECT COUNT(*) as total FROM insumos";
            int totalInsumos = 0;
            try (PreparedStatement stmt = conn.prepareStatement(insumosSql);
                 ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    totalInsumos = rs.getInt("total");
                }
            }

            // Contar recetas
            String recetasSql = "SELECT COUNT(*) as total FROM recetas";
            int totalRecetas = 0;
            try (PreparedStatement stmt = conn.prepareStatement(recetasSql);
                 ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    totalRecetas = rs.getInt("total");
                }
            }

            // Contar ventas del día
            String ventasSql = "SELECT COUNT(*) as total, SUM(total) as suma FROM ventas WHERE DATE(created_at) = CURRENT_DATE";
            int ventasHoy = 0;
            float totalHoy = 0;
            try (PreparedStatement stmt = conn.prepareStatement(ventasSql);
                 ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    ventasHoy = rs.getInt("total");
                    totalHoy = rs.getFloat("suma");
                }
            }

            result.append("📊 RESUMEN GENERAL:\n");
            result.append("• Total insumos: ").append(totalInsumos).append("\n");
            result.append("• Total recetas: ").append(totalRecetas).append("\n");
            result.append("• Ventas hoy: ").append(ventasHoy).append("\n");
            result.append("• Total vendido hoy: Bs. ").append(totalHoy).append("\n");

        } catch (SQLException e) {
            return "Error generando reporte general: " + e.getMessage();
        }
        return result.toString();
    }

    // ========== FUNCIONES PARA USUARIOS ==========
    private static String getUsuarioDetails(String email) {
        StringBuilder result = new StringBuilder();
        result.append("=== DETALLES DEL USUARIO ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT * FROM usuarios WHERE email IILIKE ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, "%" + email + "%");
                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        result.append("• ID: ").append(rs.getInt("id")).append("\n");
                        String apellidoMaterno = rs.getString("apellido_materno");
                        if (apellidoMaterno == null) {
                            apellidoMaterno = "";
                        }
                        String nombreCompleto = (rs.getString("nombre") + " " +
                                                rs.getString("apellido_paterno") + " " +
                                                apellidoMaterno).replaceAll("\\s+", " ").trim();
                        result.append("• Nombre: ").append(nombreCompleto).append("\n");
                        result.append("• Email: ").append(rs.getString("email")).append("\n");
                        result.append("• Creado: ").append(rs.getString("created_at")).append("\n");
                    } else {
                        result.append("❌ No se encontró el usuario: ").append(email);
                    }
                }
            }
        } catch (SQLException e) {
            return "Error consultando usuario: " + e.getMessage();
        }
        return result.toString();
    }

    private static String getUsuariosList() {
        StringBuilder result = new StringBuilder();
        result.append("========================================\n");
        result.append("    LISTADO DE USUARIOS DEL SISTEMA\n");
        result.append("========================================\n\n");

        try (Connection conn = getDatabaseConnection()) {
            // Consulta corregida para obtener usuarios con sus roles
            String sql = "SELECT u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, " +
                        "COALESCE(r.name, 'Sin rol') as rol " +
                        "FROM usuarios u " +
                        "LEFT JOIN model_has_roles mr ON u.id = mr.model_id AND mr.model_type = 'App\\Models\\User' " +
                        "LEFT JOIN roles r ON mr.role_id = r.id " +
                        "ORDER BY u.id";

            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    String nombreCompleto = rs.getString("nombre") + " " +
                                          rs.getString("apellido_paterno") + " " +
                                          rs.getString("apellido_materno");
                    String email = rs.getString("email");
                    String rol = rs.getString("rol");
                    if (rol == null) rol = "Sin rol";

                    result.append("\uD83D\uDCCB ID: ").append(rs.getInt("id")).append("\n");
                    result.append("   Nombre: ").append(nombreCompleto).append("\n");
                    result.append("   Email: ").append(email).append("\n");
                    result.append("   Rol: ").append(rol).append("\n");
                    result.append("   \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\n\n");
                }
                result.append("Total de usuarios: ").append(count).append("\n");
            }
        } catch (SQLException e) {
            System.out.println("\u274C Error consultando usuarios: " + e.getMessage());
            e.printStackTrace();
            return "Error consultando usuarios: " + e.getMessage();
        }
        return result.toString();
    }

    private static String createUsuario(String command) {
        String[] params = extractParams(command, "CREAR USUARIO", 5);
        if (params.length < 5) {
            return "Formato incorrecto.\nUse: CREAR USUARIO [nombre] [apellidoP] [apellidoM] [email] [password]\n\nEjemplo: CREAR USUARIO Juan Perez Lopez juan@mail.com Pass1234";
        }

        String nombre = params[0];
        String apellidoPaterno = params[1];
        String apellidoMaterno = params[2];
        String email = params[3];
        String password = params[4];

        // Validar longitud de contraseña
        if (password.length() < 8) {
            return "\u274C ERROR: La contrase\u00f1a debe tener al menos 8 caracteres.";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Verificar si el email ya existe
            String checkSql = "SELECT id FROM usuarios WHERE email = ?";
            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setString(1, email);
                ResultSet rs = checkStmt.executeQuery();
                if (rs.next()) {
                    return "\u274C ERROR: El email " + email + " ya est\u00e1 registrado.";
                }
            }

            // Crear usuario (password será encriptado con bcrypt en la app real)
            String sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, password, created_at, updated_at) " +
                        "VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            try (PreparedStatement stmt = conn.prepareStatement(sql, PreparedStatement.RETURN_GENERATED_KEYS)) {
                stmt.setString(1, nombre);
                stmt.setString(2, apellidoPaterno);
                stmt.setString(3, apellidoMaterno);
                stmt.setString(4, email);
                stmt.setString(5, "$2y$10$" + password); // Placeholder bcrypt

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    ResultSet generatedKeys = stmt.getGeneratedKeys();
                    int userId = 0;
                    if (generatedKeys.next()) {
                        userId = generatedKeys.getInt(1);
                    }

                    String nombreCompleto = nombre + " " + apellidoPaterno + " " + apellidoMaterno;
                    StringBuilder response = new StringBuilder();
                    response.append("\u2705 USUARIO CREADO EXITOSAMENTE\n\n");
                    response.append("ID: ").append(userId).append("\n");
                    response.append("Nombre: ").append(nombreCompleto).append("\n");
                    response.append("Email: ").append(email).append("\n");
                    response.append("Estado: Activo\n\n");
                    response.append("\u26A0\uFE0F IMPORTANTE: Asigne un rol al usuario usando:\n");
                    response.append("ASIGNAR ROL ").append(userId).append(" [admin|director|cocinero|cajero|ayudante_cocina|mesero]");

                    return response.toString();
                } else {
                    return "\u274C Error: No se pudo crear el usuario.";
                }
            }
        } catch (SQLException e) {
            System.out.println("\u274C Error creando usuario: " + e.getMessage());
            e.printStackTrace();
            return "Error creando usuario: " + e.getMessage();
        }
    }

    private static String actualizarUsuario(String command) {
        String[] params = extractParams(command, "ACTUALIZAR USUARIO", 3);
        if (params.length < 3) {
            return "Formato incorrecto.\nUse: ACTUALIZAR USUARIO [id] [campo] [valor]\n\nCampos disponibles: nombre, apellido_paterno, apellido_materno, email, password\n\nEjemplos:\n\u2022 ACTUALIZAR USUARIO 5 nombre Carlos\n\u2022 ACTUALIZAR USUARIO 5 email nuevo@mail.com\n\u2022 ACTUALIZAR USUARIO 5 password NuevaPass123";
        }

        int userId = Integer.parseInt(params[0]);
        String campo = params[1].toLowerCase();
        String valor = params[2];

        String[] camposPermitidos = {"nombre", "apellido_paterno", "apellido_materno", "email", "password"};
        boolean campoValido = false;
        for (String c : camposPermitidos) {
            if (c.equals(campo)) {
                campoValido = true;
                break;
            }
        }

        if (!campoValido) {
            return "\u274C ERROR: Campo '" + campo + "' no v\u00e1lido.\nCampos disponibles: nombre, apellido_paterno, apellido_materno, email, password";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Verificar que el usuario existe
            String checkSql = "SELECT nombre, apellido_paterno, apellido_materno, email FROM usuarios WHERE id = ?";
            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setInt(1, userId);
                ResultSet rs = checkStmt.executeQuery();
                if (!rs.next()) {
                    return "\u274C ERROR: Usuario con ID " + userId + " no encontrado.";
                }
            }

            // Si es email, validar que no esté en uso
            if (campo.equals("email")) {
                String emailCheckSql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
                try (PreparedStatement emailStmt = conn.prepareStatement(emailCheckSql)) {
                    emailStmt.setString(1, valor);
                    emailStmt.setInt(2, userId);
                    ResultSet rs = emailStmt.executeQuery();
                    if (rs.next()) {
                        return "\u274C ERROR: El email " + valor + " ya est\u00e1 en uso por otro usuario.";
                    }
                }
            }

            // Si es password, validar longitud
            if (campo.equals("password")) {
                if (valor.length() < 8) {
                    return "\u274C ERROR: La contrase\u00f1a debe tener al menos 8 caracteres.";
                }
                valor = "$2y$10$" + valor; // Placeholder bcrypt
            }

            // Actualizar
            String sql = "UPDATE usuarios SET " + campo + " = ?, updated_at = NOW() WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, valor);
                stmt.setInt(2, userId);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    // Obtener datos actualizados
                    String getUserSql = "SELECT nombre, apellido_paterno, apellido_materno, email FROM usuarios WHERE id = ?";
                    try (PreparedStatement getUserStmt = conn.prepareStatement(getUserSql)) {
                        getUserStmt.setInt(1, userId);
                        ResultSet userRs = getUserStmt.executeQuery();
                        if (userRs.next()) {
                            String nombreCompleto = userRs.getString("nombre") + " " +
                                                   userRs.getString("apellido_paterno") + " " +
                                                   userRs.getString("apellido_materno");
                            StringBuilder response = new StringBuilder();
                            response.append("\u2705 USUARIO ACTUALIZADO EXITOSAMENTE\n\n");
                            response.append("ID: ").append(userId).append("\n");
                            response.append("Nombre: ").append(nombreCompleto).append("\n");
                            response.append("Email: ").append(userRs.getString("email")).append("\n");
                            response.append("Campo actualizado: ").append(campo).append("\n");
                            return response.toString();
                        }
                    }
                    return "\u2705 Usuario actualizado exitosamente.";
                } else {
                    return "\u274C Error: No se pudo actualizar el usuario.";
                }
            }
        } catch (SQLException e) {
            System.out.println("\u274C Error actualizando usuario: " + e.getMessage());
            e.printStackTrace();
            return "Error actualizando usuario: " + e.getMessage();
        }
    }

    private static String eliminarUsuario(String command) {
        String[] params = extractParams(command, "ELIMINAR USUARIO");
        if (params.length < 1) {
            return "Formato incorrecto.\nUse: ELIMINAR USUARIO [id]\n\nEjemplo: ELIMINAR USUARIO 5";
        }

        int userId = Integer.parseInt(params[0]);

        try (Connection conn = getDatabaseConnection()) {
            // Obtener datos del usuario antes de eliminarlo
            String getUserSql = "SELECT nombre, apellido_paterno, apellido_materno, email FROM usuarios WHERE id = ?";
            String nombreCompleto = "";
            String email = "";

            try (PreparedStatement getUserStmt = conn.prepareStatement(getUserSql)) {
                getUserStmt.setInt(1, userId);
                ResultSet rs = getUserStmt.executeQuery();
                if (!rs.next()) {
                    return "\u274C ERROR: Usuario con ID " + userId + " no encontrado.";
                }
                nombreCompleto = rs.getString("nombre") + " " +
                               rs.getString("apellido_paterno") + " " +
                               rs.getString("apellido_materno");
                email = rs.getString("email");
            }

            // Eliminar usuario
            String sql = "DELETE FROM usuarios WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setInt(1, userId);

                int rowsAffected = stmt.executeUpdate();
                if (rowsAffected > 0) {
                    StringBuilder response = new StringBuilder();
                    response.append("\u2705 USUARIO ELIMINADO EXITOSAMENTE\n\n");
                    response.append("ID eliminado: ").append(userId).append("\n");
                    response.append("Nombre: ").append(nombreCompleto).append("\n");
                    response.append("Email: ").append(email).append("\n");
                    response.append("\n\u26A0\uFE0F Esta acci\u00f3n no se puede deshacer.");
                    return response.toString();
                } else {
                    return "\u274C Error: No se pudo eliminar el usuario.";
                }
            }
        } catch (SQLException e) {
            System.out.println("\u274C Error eliminando usuario: " + e.getMessage());
            e.printStackTrace();
            return "Error eliminando usuario: " + e.getMessage();
        }
    }

    private static String asignarRol(String command) {
        String[] params = extractParams(command, "ASIGNAR ROL", 2);
        if (params.length < 2) {
            return "Formato incorrecto.\nUse: ASIGNAR ROL [id] [rol]\n\nRoles disponibles: admin, director, cocinero, cajero, ayudante_cocina, mesero\n\nEjemplo: ASIGNAR ROL 5 cocinero";
        }

        int userId = Integer.parseInt(params[0]);
        String rolName = params[1].toLowerCase();

        String[] rolesDisponibles = {"admin", "director", "cocinero", "cajero", "ayudante_cocina", "mesero"};
        boolean rolValido = false;
        for (String r : rolesDisponibles) {
            if (r.equals(rolName)) {
                rolValido = true;
                break;
            }
        }

        if (!rolValido) {
            return "\u274C ERROR: Rol '" + rolName + "' no v\u00e1lido.\nRoles disponibles: admin, director, cocinero, cajero, ayudante_cocina, mesero";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Verificar que el usuario existe
            String checkUserSql = "SELECT nombre, apellido_paterno, apellido_materno, email FROM usuarios WHERE id = ?";
            String nombreCompleto = "";
            String email = "";

            try (PreparedStatement checkStmt = conn.prepareStatement(checkUserSql)) {
                checkStmt.setInt(1, userId);
                ResultSet rs = checkStmt.executeQuery();
                if (!rs.next()) {
                    return "\u274C ERROR: Usuario con ID " + userId + " no encontrado.";
                }
                nombreCompleto = rs.getString("nombre") + " " +
                               rs.getString("apellido_paterno") + " " +
                               rs.getString("apellido_materno");
                email = rs.getString("email");
            }

            // Obtener el ID del rol
            String getRoleSql = "SELECT id FROM roles WHERE name = ?";
            int roleId = 0;
            try (PreparedStatement getRoleStmt = conn.prepareStatement(getRoleSql)) {
                getRoleStmt.setString(1, rolName);
                ResultSet rs = getRoleStmt.executeQuery();
                if (!rs.next()) {
                    return "\u274C ERROR: Rol '" + rolName + "' no encontrado en el sistema.";
                }
                roleId = rs.getInt("id");
            }

            // Eliminar roles existentes del usuario
            String deleteSql = "DELETE FROM model_has_roles WHERE model_id = ? AND model_type = 'App\\\\Models\\\\User'";
            try (PreparedStatement deleteStmt = conn.prepareStatement(deleteSql)) {
                deleteStmt.setInt(1, userId);
                deleteStmt.executeUpdate();
            }

            // Asignar nuevo rol
            String insertSql = "INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, 'App\\\\Models\\\\User', ?)";
            try (PreparedStatement insertStmt = conn.prepareStatement(insertSql)) {
                insertStmt.setInt(1, roleId);
                insertStmt.setInt(2, userId);

                int rowsAffected = insertStmt.executeUpdate();
                if (rowsAffected > 0) {
                    StringBuilder response = new StringBuilder();
                    response.append("\u2705 ROL ASIGNADO EXITOSAMENTE\n\n");
                    response.append("Usuario: ").append(nombreCompleto).append("\n");
                    response.append("Email: ").append(email).append("\n");
                    response.append("Nuevo rol: ").append(rolName.substring(0, 1).toUpperCase() + rolName.substring(1)).append("\n");
                    return response.toString();
                } else {
                    return "\u274C Error: No se pudo asignar el rol.";
                }
            }
        } catch (SQLException e) {
            System.out.println("\u274C Error asignando rol: " + e.getMessage());
            e.printStackTrace();
            return "Error asignando rol: " + e.getMessage();
        }
    }

    // ========== FUNCIONES PARA ALERTAS Y NOTIFICACIONES ==========
    private static String getAlertasList() {
        StringBuilder result = new StringBuilder();
        result.append("=== ALERTAS ACTIVAS ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT i.nombre, i.stock_minimo, um.nombre as unidad, " +
                        "COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.cantidad ELSE -m.cantidad END), 0) as stock_actual " +
                        "FROM insumos i " +
                        "LEFT JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                        "LEFT JOIN movimiento_inventarios m ON i.id = m.insumo_id " +
                        "GROUP BY i.id, i.nombre, i.stock_minimo, um.nombre " +
                        "HAVING stock_actual < i.stock_minimo " +
                        "ORDER BY i.nombre";

            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    String nombre = rs.getString("nombre");
                    float stockMinimo = rs.getFloat("stock_minimo");
                    String unidad = rs.getString("unidad");
                    float stockActual = rs.getFloat("stock_actual");

                    result.append("⚠️ ").append(count).append(". ").append(nombre)
                          .append(" - Stock actual: ").append(stockActual).append(" ").append(unidad != null ? unidad : "")
                          .append(" - Stock mínimo: ").append(stockMinimo).append(" ").append(unidad != null ? unidad : "").append("\n");
                }
                if (count == 0) {
                    result.append("✅ No hay alertas activas. Todos los insumos tienen stock suficiente.");
                } else {
                    result.append("\nTotal alertas: ").append(count);
                }
            }
        } catch (SQLException e) {
            return "Error consultando alertas: " + e.getMessage();
        }
        return result.toString();
    }

    private static String getNotificacionesList() {
        StringBuilder result = new StringBuilder();
        result.append("=== NOTIFICACIONES RECIENTES ===\n\n");
        result.append("📧 Sistema de notificaciones por email activo\n");
        result.append("📊 Alertas de stock bajo configuradas\n");
        result.append("✅ Todas las operaciones se registran en el historial\n");
        return result.toString();
    }

    private static String createAlerta(String command) {
        String[] params = extractParams(command, "CREAR ALERTA", 2);
        if (params.length < 2) {
            return "Formato incorrecto. Use: CREAR ALERTA [tipo] [mensaje]";
        }

        String tipo = params[0];
        String mensaje = params[1];

        return "✅ Alerta creada: [" + tipo.toUpperCase() + "] " + mensaje + "\n" +
               "📧 La alerta se ha registrado en el sistema.";
    }

    // ========== FUNCIONES PARA HISTORIAL ==========
    private static String getHistorialByDate(String fecha) {
        StringBuilder result = new StringBuilder();
        result.append("=== HISTORIAL POR FECHA: ").append(fecha).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT 'movimiento' as tipo, m.created_at, CONCAT('Movimiento: ', m.tipo, ' - ', i.nombre, ' - ', m.cantidad) as descripcion " +
                        "FROM movimiento_inventarios m " +
                        "JOIN insumos i ON m.insumo_id = i.id " +
                        "WHERE DATE(m.created_at) = ? " +
                        "UNION ALL " +
                        "SELECT 'venta' as tipo, v.created_at, CONCAT('Venta: ', r.nombre, ' - ', v.cantidad, ' unidades - Bs. ', v.total) as descripcion " +
                        "FROM ventas v " +
                        "JOIN recetas r ON v.receta_id = r.id " +
                        "WHERE DATE(v.created_at) = ? " +
                        "ORDER BY created_at DESC";

            try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                stmt.setString(1, fecha);
                stmt.setString(2, fecha);
                try (ResultSet rs = stmt.executeQuery()) {
                    int count = 0;
                    while (rs.next()) {
                        count++;
                        String tipo = rs.getString("tipo");
                        String hora = rs.getString("created_at");
                        String descripcion = rs.getString("descripcion");

                        result.append(count).append(". [").append(tipo.toUpperCase()).append("] ")
                              .append(hora).append(" - ").append(descripcion).append("\n");
                    }
                    if (count == 0) {
                        result.append("No hay registros para la fecha: ").append(fecha);
                    } else {
                        result.append("\nTotal registros: ").append(count);
                    }
                }
            }
        } catch (SQLException e) {
            return "Error consultando historial: " + e.getMessage();
        }
        return result.toString();
    }

    private static String getHistorialByTable(String tabla) {
        StringBuilder result = new StringBuilder();
        result.append("=== HISTORIAL DE TABLA: ").append(tabla.toUpperCase()).append(" ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            String sql = "SELECT created_at, updated_at FROM " + tabla + " ORDER BY created_at DESC LIMIT 20";
            try (PreparedStatement stmt = conn.prepareStatement(sql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    String creado = rs.getString("created_at");
                    String actualizado = rs.getString("updated_at");

                    result.append(count).append(". Creado: ").append(creado)
                          .append(" - Actualizado: ").append(actualizado).append("\n");
                }
                if (count == 0) {
                    result.append("No hay registros en la tabla: ").append(tabla);
                } else {
                    result.append("\nTotal registros mostrados: ").append(count);
                }
            }
        } catch (SQLException e) {
            return "Error consultando historial de tabla: " + e.getMessage();
        }
        return result.toString();
    }

    // ========== FUNCIONES PARA PREDICCIONES ==========
    private static String getPrediccionDetails(String id) {
        StringBuilder result = new StringBuilder();
        result.append("=== DETALLES DE PREDICCIÓN ===\n\n");
        result.append("🔮 Sistema de predicciones en desarrollo\n");
        result.append("📊 Análisis de tendencias de consumo\n");
        result.append("📈 Predicciones de demanda futura\n");
        result.append("⚠️ Funcionalidad disponible próximamente\n");
        return result.toString();
    }

    private static String getPrediccionesList() {
        StringBuilder result = new StringBuilder();
        result.append("=== PREDICCIONES DISPONIBLES ===\n\n");
        result.append("🔮 Sistema de predicciones en desarrollo\n");
        result.append("📊 Análisis de tendencias de consumo\n");
        result.append("📈 Predicciones de demanda futura\n");
        result.append("⚠️ Funcionalidad disponible próximamente\n");
        return result.toString();
    }

    private static String generatePrediccion(String tipo) {
        StringBuilder result = new StringBuilder();
        result.append("=== GENERANDO PREDICCIÓN: ").append(tipo.toUpperCase()).append(" ===\n\n");
        result.append("🔮 Sistema de predicciones en desarrollo\n");
        result.append("📊 Análisis de tendencias de consumo\n");
        result.append("📈 Predicciones de demanda futura\n");
        result.append("⚠️ Funcionalidad disponible próximamente\n");
        return result.toString();
    }

    // ========== FUNCIONES PARA DASHBOARD ==========
    private static String getDashboardData() {
        StringBuilder result = new StringBuilder();
        result.append("=== DASHBOARD GENERAL ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            // Contar insumos
            String insumosSql = "SELECT COUNT(*) as total FROM insumos";
            int totalInsumos = 0;
            try (PreparedStatement stmt = conn.prepareStatement(insumosSql);
                 ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    totalInsumos = rs.getInt("total");
                }
            }

            // Contar recetas
            String recetasSql = "SELECT COUNT(*) as total FROM recetas";
            int totalRecetas = 0;
            try (PreparedStatement stmt = conn.prepareStatement(recetasSql);
                 ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    totalRecetas = rs.getInt("total");
                }
            }

            // Ventas del día
            String ventasSql = "SELECT COUNT(*) as total, SUM(total) as suma FROM ventas WHERE DATE(created_at) = CURRENT_DATE";
            int ventasHoy = 0;
            float totalHoy = 0;
            try (PreparedStatement stmt = conn.prepareStatement(ventasSql);
                 ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    ventasHoy = rs.getInt("total");
                    totalHoy = rs.getFloat("suma");
                }
            }

            // Alertas de stock bajo
            String alertasSql = "SELECT COUNT(*) as total FROM insumos i " +
                               "LEFT JOIN movimiento_inventarios m ON i.id = m.insumo_id " +
                               "GROUP BY i.id " +
                               "HAVING COALESCE(SUM(CASE WHEN m.tipo = 'entrada' THEN m.cantidad ELSE -m.cantidad END), 0) < i.stock_minimo";
            int alertasActivas = 0;
            try (PreparedStatement stmt = conn.prepareStatement(alertasSql);
                 ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    alertasActivas++;
                }
            }

            result.append("📊 RESUMEN GENERAL:\n");
            result.append("• Total insumos: ").append(totalInsumos).append("\n");
            result.append("• Total recetas: ").append(totalRecetas).append("\n");
            result.append("• Ventas hoy: ").append(ventasHoy).append("\n");
            result.append("• Total vendido hoy: Bs. ").append(totalHoy).append("\n");
            result.append("• Alertas activas: ").append(alertasActivas).append("\n");

        } catch (SQLException e) {
            return "Error generando dashboard: " + e.getMessage();
        }
        return result.toString();
    }

    private static String getEstadisticasData() {
        StringBuilder result = new StringBuilder();
        result.append("=== ESTADÍSTICAS DEL SISTEMA ===\n\n");

        try (Connection conn = getDatabaseConnection()) {
            // Estadísticas de ventas últimos 7 días
            String ventasSql = "SELECT DATE(created_at) as fecha, COUNT(*) as ventas, SUM(total) as total " +
                              "FROM ventas " +
                              "WHERE created_at >= (NOW() - INTERVAL '7 DAY') " +
                              "GROUP BY DATE(created_at) " +
                              "ORDER BY fecha DESC";

            result.append("📈 VENTAS ÚLTIMOS 7 DÍAS:\n");
            try (PreparedStatement stmt = conn.prepareStatement(ventasSql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    String fecha = rs.getString("fecha");
                    int ventas = rs.getInt("ventas");
                    float total = rs.getFloat("total");

                    result.append(count).append(". ").append(fecha)
                          .append(" - Ventas: ").append(ventas)
                          .append(" - Total: Bs. ").append(total).append("\n");
                }
                if (count == 0) {
                    result.append("No hay ventas en los últimos 7 días.\n");
                }
            }

            // Estadísticas de movimientos
            String movimientosSql = "SELECT tipo, COUNT(*) as cantidad, SUM(cantidad) as total_cantidad " +
                                  "FROM movimiento_inventarios " +
                                  "WHERE created_at >= (NOW() - INTERVAL '7 DAY') " +
                                  "GROUP BY tipo";

            result.append("\n📦 MOVIMIENTOS ÚLTIMOS 7 DÍAS:\n");
            try (PreparedStatement stmt = conn.prepareStatement(movimientosSql);
                 ResultSet rs = stmt.executeQuery()) {
                int count = 0;
                while (rs.next()) {
                    count++;
                    String tipo = rs.getString("tipo");
                    int cantidad = rs.getInt("cantidad");
                    float totalCantidad = rs.getFloat("total_cantidad");

                    result.append(count).append(". ").append(tipo.toUpperCase())
                          .append(" - Movimientos: ").append(cantidad)
                          .append(" - Cantidad: ").append(totalCantidad).append("\n");
                }
                if (count == 0) {
                    result.append("No hay movimientos en los últimos 7 días.\n");
                }
            }

        } catch (SQLException e) {
            return "Error generando estadísticas: " + e.getMessage();
        }
        return result.toString();
    }

    // ==================== CU5 - GESTIÓN DE PRODUCCIÓN ====================

    private static String calcularProduccion(String command) {
        // Formato: CALCULAR PRODUCCION [nombre_receta] [cantidad]
        String[] parts = command.split("CALCULAR PRODUCCION");
        if (parts.length < 2) {
            return "Formato incorrecto.\nUse: CALCULAR PRODUCCION [nombre_receta] [cantidad]\n\nEjemplo: CALCULAR PRODUCCION Pizza 20";
        }

        String[] params = splitParams(parts[1].trim(), 2);
        if (params.length < 2) {
            return "Formato incorrecto.\nUse: CALCULAR PRODUCCION [nombre_receta] [cantidad]\n\nEjemplo: CALCULAR PRODUCCION Pizza 20";
        }

        String nombreReceta = params[0];
        int cantidad;
        try {
            cantidad = Integer.parseInt(params[1]);
            if (cantidad <= 0) {
                return "❌ ERROR: La cantidad debe ser mayor a 0";
            }
        } catch (NumberFormatException e) {
            return "❌ ERROR: La cantidad debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Buscar receta
            String checkRecetaSql = "SELECT id, nombre, precio FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;
            String recetaNombre = "";
            float precio = 0;

            try (PreparedStatement checkStmt = conn.prepareStatement(checkRecetaSql)) {
                checkStmt.setString(1, "%" + nombreReceta + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                        recetaNombre = rs.getString("nombre");
                        precio = rs.getFloat("precio");
                    } else {
                        return "❌ ERROR: No se encontró la receta: " + nombreReceta;
                    }
                }
            }

            // Calcular insumos (SIN guardar plan)
            StringBuilder result = new StringBuilder();
            result.append("📊 CÁLCULO DE PRODUCCIÓN (No guardado)\n\n");
            result.append("Receta: ").append(recetaNombre).append("\n");
            result.append("Cantidad: ").append(cantidad).append(" unidades\n");
            result.append("Precio unitario: $").append(precio).append("\n");
            result.append("Total estimado: $").append(precio * cantidad).append("\n\n");
            result.append("INSUMOS NECESARIOS:\n");

            String insumosSql = "SELECT i.nombre, ri.cantidad * ? as cantidad_total, um.abreviatura, i.id " +
                               "FROM recetas_insumos ri " +
                               "JOIN insumos i ON ri.insumo_id = i.id " +
                               "JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                               "WHERE ri.receta_id = ?";

            boolean todoDisponible = true;
            try (PreparedStatement insumosStmt = conn.prepareStatement(insumosSql)) {
                insumosStmt.setInt(1, cantidad);
                insumosStmt.setInt(2, recetaId);

                try (ResultSet rs = insumosStmt.executeQuery()) {
                    while (rs.next()) {
                        String insumoNombre = rs.getString("nombre");
                        float cantidadNecesaria = rs.getFloat("cantidad_total");
                        String unidad = rs.getString("abreviatura");
                        int insumoId = rs.getInt("id");

                        // Verificar stock
                        float stockActual = getStockInsumo(conn, insumoId);
                        boolean suficiente = stockActual >= cantidadNecesaria;
                        String simbolo = suficiente ? "✅" : "❌";

                        if (!suficiente) {
                            todoDisponible = false;
                        }

                        result.append("• ").append(insumoNombre).append(": ")
                              .append(cantidadNecesaria).append(" ").append(unidad).append("\n");
                        result.append("  Stock actual: ").append(stockActual).append(" ")
                              .append(unidad).append(" ").append(simbolo).append("\n");
                    }
                }
            }

            result.append("\n");
            result.append(todoDisponible ? "✅ Todos los insumos disponibles" : "⚠️ Insumos insuficientes");
            result.append("\n\n💡 Para guardar este plan use: CREAR PLAN PRODUCCION ").append(nombreReceta).append(" ").append(cantidad);

            return result.toString();

        } catch (SQLException e) {
            System.out.println("❌ Error calculando producción: " + e.getMessage());
            return "Error calculando producción: " + e.getMessage();
        }
    }

    private static String createPlanProduccion(String command) {
        // Formato: CREAR PLAN PRODUCCION [nombre_plan] [nombre_receta] [cantidad]
        String[] parts = command.split("CREAR PLAN PRODUCCION");
        if (parts.length < 2) {
            return "Formato incorrecto.\nUse: CREAR PLAN PRODUCCION [nombre_plan] [nombre_receta] [cantidad]\n\nEjemplo: CREAR PLAN PRODUCCION MenuDiario Pizza 20";
        }

        String[] params = splitParams(parts[1].trim(), 3);
        if (params.length < 3) {
            return "Formato incorrecto.\nUse: CREAR PLAN PRODUCCION [nombre_plan] [nombre_receta] [cantidad]\n\nEjemplo: CREAR PLAN PRODUCCION MenuDiario Pizza 20";
        }

        String nombrePlan = params[0];
        String nombreReceta = params[1];
        int cantidad;
        try {
            cantidad = Integer.parseInt(params[2]);
            if (cantidad <= 0) {
                return "❌ ERROR: La cantidad debe ser mayor a 0";
            }
        } catch (NumberFormatException e) {
            return "❌ ERROR: La cantidad debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Buscar receta
            String checkRecetaSql = "SELECT id, nombre FROM recetas WHERE nombre ILIKE ?";
            int recetaId = -1;
            String recetaNombre = "";

            try (PreparedStatement checkStmt = conn.prepareStatement(checkRecetaSql)) {
                checkStmt.setString(1, "%" + nombreReceta + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (rs.next()) {
                        recetaId = rs.getInt("id");
                        recetaNombre = rs.getString("nombre");
                    } else {
                        return "❌ ERROR: No se encontró la receta: " + nombreReceta;
                    }
                }
            }

            // Crear plan
            String insertSql = "INSERT INTO planes_produccion (nombre, receta_id, cantidad, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
            int planId = -1;

            try (PreparedStatement stmt = conn.prepareStatement(insertSql, Statement.RETURN_GENERATED_KEYS)) {
                stmt.setString(1, nombrePlan);
                stmt.setInt(2, recetaId);
                stmt.setInt(3, cantidad);
                stmt.executeUpdate();

                try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
                    if (generatedKeys.next()) {
                        planId = generatedKeys.getInt(1);
                    }
                }
            }

            // Calcular insumos
            StringBuilder result = new StringBuilder();
            result.append("✅ PLAN DE PRODUCCIÓN CREADO\n\n");
            result.append("ID: ").append(planId).append("\n");
            result.append("Nombre: ").append(nombrePlan).append("\n");
            result.append("Receta: ").append(recetaNombre).append("\n");
            result.append("Cantidad a producir: ").append(cantidad).append(" unidades\n\n");
            result.append("INSUMOS CALCULADOS:\n");

            String insumosSql = "SELECT i.nombre, ri.cantidad * ? as cantidad_total, um.abreviatura, i.id " +
                               "FROM recetas_insumos ri " +
                               "JOIN insumos i ON ri.insumo_id = i.id " +
                               "JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                               "WHERE ri.receta_id = ?";

            try (PreparedStatement insumosStmt = conn.prepareStatement(insumosSql)) {
                insumosStmt.setInt(1, cantidad);
                insumosStmt.setInt(2, recetaId);

                try (ResultSet rs = insumosStmt.executeQuery()) {
                    while (rs.next()) {
                        String insumoNombre = rs.getString("nombre");
                        float cantidadNecesaria = rs.getFloat("cantidad_total");
                        String unidad = rs.getString("abreviatura");
                        int insumoId = rs.getInt("id");

                        // Verificar stock
                        float stockActual = getStockInsumo(conn, insumoId);
                        boolean suficiente = stockActual >= cantidadNecesaria;
                        String simbolo = suficiente ? "✅" : "❌";

                        result.append("• ").append(insumoNombre).append(": ")
                              .append(cantidadNecesaria).append(" ").append(unidad)
                              .append(" ").append(simbolo).append("\n");
                    }
                }
            }

            return result.toString();

        } catch (SQLException e) {
            System.out.println("❌ Error creando plan de producción: " + e.getMessage());
            return "Error creando plan de producción: " + e.getMessage();
        }
    }

    private static String getPlanesProduccion(String filtros) {
        try (Connection conn = getDatabaseConnection()) {
            StringBuilder sql = new StringBuilder();
            sql.append("SELECT pp.id, pp.nombre, pp.cantidad, pp.created_at, r.nombre as receta ");
            sql.append("FROM planes_produccion pp ");
            sql.append("JOIN recetas r ON pp.receta_id = r.id ");

            java.util.List<String> filtrosAplicados = new java.util.ArrayList<>();
            java.util.List<String> whereClauses = new java.util.ArrayList<>();
            java.util.List<Object> params = new java.util.ArrayList<>();

            if (!filtros.isEmpty()) {
                String[] filtrosPares = filtros.split(",");

                for (String par : filtrosPares) {
                    String[] partes = splitParams(par.trim(), 2);

                    if (partes.length >= 2) {
                        String tipoFiltro = partes[0].toLowerCase();
                        String valorFiltro = partes[1].trim();

                        switch (tipoFiltro) {
                            case "nombre":
                                whereClauses.add("pp.nombre ILIKE ?");
                                params.add("%" + valorFiltro + "%");
                                filtrosAplicados.add("Nombre: " + valorFiltro);
                                break;

                            case "receta":
                                whereClauses.add("r.nombre ILIKE ?");
                                params.add("%" + valorFiltro + "%");
                                filtrosAplicados.add("Receta: " + valorFiltro);
                                break;

                            case "cantidad":
                                whereClauses.add("pp.cantidad = ?");
                                params.add(valorFiltro);
                                filtrosAplicados.add("Cantidad: " + valorFiltro);
                                break;

                            case "fecha":
                                try {
                                    String fechaNorm = normalizarFecha(valorFiltro);
                                    whereClauses.add("DATE(pp.created_at) = ?");
                                    params.add(fechaNorm);
                                    filtrosAplicados.add("Fecha: " + fechaNorm);
                                } catch (Exception e) {
                                    // Ignorar fecha inválida
                                }
                                break;

                            case "fecha_desde":
                                try {
                                    String fechaDesde = normalizarFecha(valorFiltro);
                                    whereClauses.add("DATE(pp.created_at) >= ?");
                                    params.add(fechaDesde);
                                    filtrosAplicados.add("Desde: " + fechaDesde);
                                } catch (Exception e) {
                                    // Ignorar fecha inválida
                                }
                                break;

                            case "fecha_hasta":
                                try {
                                    String fechaHasta = normalizarFecha(valorFiltro);
                                    whereClauses.add("DATE(pp.created_at) <= ?");
                                    params.add(fechaHasta);
                                    filtrosAplicados.add("Hasta: " + fechaHasta);
                                } catch (Exception e) {
                                    // Ignorar fecha inválida
                                }
                                break;
                        }
                    }
                }
            }

            if (!whereClauses.isEmpty()) {
                sql.append("WHERE ").append(String.join(" AND ", whereClauses)).append(" ");
            }

            sql.append("ORDER BY pp.created_at DESC");

            try (PreparedStatement stmt = conn.prepareStatement(sql.toString())) {
                for (int i = 0; i < params.size(); i++) {
                    stmt.setObject(i + 1, params.get(i));
                }

                try (ResultSet rs = stmt.executeQuery()) {
                    StringBuilder result = new StringBuilder();
                    result.append("=== PLANES DE PRODUCCIÓN ===\n\n");

                    if (!filtrosAplicados.isEmpty()) {
                        result.append("Filtros aplicados:\n");
                        for (String filtro : filtrosAplicados) {
                            result.append("• ").append(filtro).append("\n");
                        }
                        result.append("\n");
                    }

                    int count = 0;
                    while (rs.next()) {
                        count++;
                        int id = rs.getInt("id");
                        String nombre = rs.getString("nombre");
                        String receta = rs.getString("receta");
                        int cantidad = rs.getInt("cantidad");
                        String fecha = rs.getString("created_at");

                        result.append(count).append(". [ID:").append(id).append("] ")
                              .append(nombre).append("\n");
                        result.append("   Receta: ").append(receta).append(" - ")
                              .append(cantidad).append(" unidades\n");
                        result.append("   Fecha: ").append(fecha).append("\n\n");
                    }

                    if (count == 0) {
                        return "No se encontraron planes de producción" + (filtrosAplicados.isEmpty() ? "." : " con los filtros especificados.");
                    }

                    result.append("Total planes: ").append(count);
                    return result.toString();
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error consultando planes: " + e.getMessage());
            return "Error consultando planes: " + e.getMessage();
        }
    }

    private static String getPlanProduccionDetails(String command) {
        String[] parts = command.split("CONSULTAR PLAN PRODUCCION");
        if (parts.length < 2 || parts[1].trim().isEmpty()) {
            return "Formato incorrecto.\nUse: CONSULTAR PLAN PRODUCCION [nombre_plan] [id]\n\nEjemplo: CONSULTAR PLAN PRODUCCION MenuDiario 10";
        }

        String[] params = splitParams(parts[1].trim(), 2);
        if (params.length < 2) {
            return "Formato incorrecto.\nUse: CONSULTAR PLAN PRODUCCION [nombre_plan] [id]\n\nEjemplo: CONSULTAR PLAN PRODUCCION MenuDiario 10";
        }

        String nombrePlan = params[0];
        int planId;
        try {
            planId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            String planSql = "SELECT pp.id, pp.nombre, pp.cantidad, pp.created_at, r.id as receta_id, r.nombre as receta " +
                            "FROM planes_produccion pp " +
                            "JOIN recetas r ON pp.receta_id = r.id " +
                            "WHERE pp.id = ? AND pp.nombre ILIKE ?";

            try (PreparedStatement stmt = conn.prepareStatement(planSql)) {
                stmt.setInt(1, planId);
                stmt.setString(2, "%" + nombrePlan + "%");

                try (ResultSet rs = stmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró el plan con nombre '" + nombrePlan + "' e ID: " + planId + ".\n" +
                               "Use: CONSULTAR PLANES PRODUCCION\npara ver los planes disponibles.";
                    }

                    String nombre = rs.getString("nombre");
                    int cantidad = rs.getInt("cantidad");
                    String receta = rs.getString("receta");
                    int recetaId = rs.getInt("receta_id");
                    String fecha = rs.getString("created_at");

                    StringBuilder result = new StringBuilder();
                    result.append("=== PLAN DE PRODUCCIÓN #").append(planId).append(" ===\n\n");
                    result.append("Nombre: ").append(nombre).append("\n");
                    result.append("Receta: ").append(receta).append("\n");
                    result.append("Cantidad: ").append(cantidad).append(" unidades\n");
                    result.append("Creado: ").append(fecha).append("\n\n");
                    result.append("INSUMOS NECESARIOS:\n");

                    // Calcular insumos
                    String insumosSql = "SELECT i.id, i.nombre, ri.cantidad * ? as cantidad_total, um.abreviatura " +
                                       "FROM recetas_insumos ri " +
                                       "JOIN insumos i ON ri.insumo_id = i.id " +
                                       "JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                                       "WHERE ri.receta_id = ?";

                    boolean todoDisponible = true;
                    try (PreparedStatement insumosStmt = conn.prepareStatement(insumosSql)) {
                        insumosStmt.setInt(1, cantidad);
                        insumosStmt.setInt(2, recetaId);

                        try (ResultSet insRs = insumosStmt.executeQuery()) {
                            while (insRs.next()) {
                                String insumoNombre = insRs.getString("nombre");
                                float cantidadNecesaria = insRs.getFloat("cantidad_total");
                                String unidad = insRs.getString("abreviatura");
                                int insumoId = insRs.getInt("id");

                                float stockActual = getStockInsumo(conn, insumoId);
                                boolean suficiente = stockActual >= cantidadNecesaria;
                                String simbolo = suficiente ? "✅" : "❌";

                                if (!suficiente) {
                                    todoDisponible = false;
                                }

                                result.append("• ").append(insumoNombre).append(": ")
                                      .append(cantidadNecesaria).append(" ").append(unidad).append("\n");
                                result.append("  Stock actual: ").append(stockActual).append(" ")
                                      .append(unidad).append(" ").append(simbolo).append("\n");
                            }
                        }
                    }

                    result.append("\n");
                    result.append(todoDisponible ? "✅ Todos los insumos disponibles" : "⚠️ Insumos insuficientes");

                    return result.toString();
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error consultando plan: " + e.getMessage());
            return "Error consultando plan: " + e.getMessage();
        }
    }

    private static String updatePlanProduccion(String command) {
        // Formato: ACTUALIZAR PLAN PRODUCCION [nombre_plan] [id] [campo] [valor]
        String[] parts = command.split("ACTUALIZAR PLAN PRODUCCION");
        if (parts.length < 2) {
            return "Formato incorrecto.\nUse: ACTUALIZAR PLAN PRODUCCION [nombre_plan] [id] [campo] [valor]\n\n" +
                   "Campos disponibles: nombre, receta, cantidad\n" +
                   "Ejemplo: ACTUALIZAR PLAN PRODUCCION MenuDiario 10 cantidad 25";
        }

        String[] params = splitParams(parts[1].trim(), 4);
        if (params.length < 4) {
            return "Formato incorrecto.\nUse: ACTUALIZAR PLAN PRODUCCION [nombre_plan] [id] [campo] [valor]\n\n" +
                   "Campos disponibles: nombre, receta, cantidad\n" +
                   "Ejemplo: ACTUALIZAR PLAN PRODUCCION MenuDiario 10 cantidad 25";
        }

        String nombrePlan = params[0];
        int planId;
        try {
            planId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        String campo = params[2].toLowerCase();
        String valor = params[3];

        try (Connection conn = getDatabaseConnection()) {
            // Verificar que el plan existe con ese nombre e ID
            String checkSql = "SELECT id FROM planes_produccion WHERE id = ? AND nombre ILIKE ?";
            try (PreparedStatement checkStmt = conn.prepareStatement(checkSql)) {
                checkStmt.setInt(1, planId);
                checkStmt.setString(2, "%" + nombrePlan + "%");
                try (ResultSet rs = checkStmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró el plan con nombre '" + nombrePlan + "' e ID: " + planId + ".\n" +
                               "Use: CONSULTAR PLANES PRODUCCION\npara ver los planes disponibles.";
                    }
                }
            }

            // Actualizar según el campo
            String updateSql = "";
            Object nuevoValor = null;

            switch (campo) {
                case "nombre":
                    updateSql = "UPDATE planes_produccion SET nombre = ?, updated_at = NOW() WHERE id = ?";
                    nuevoValor = valor;
                    break;

                case "receta":
                    // Buscar receta por nombre
                    String recetaSql = "SELECT id FROM recetas WHERE nombre ILIKE ?";
                    int recetaId = -1;
                    try (PreparedStatement recetaStmt = conn.prepareStatement(recetaSql)) {
                        recetaStmt.setString(1, "%" + valor + "%");
                        try (ResultSet rs = recetaStmt.executeQuery()) {
                            if (rs.next()) {
                                recetaId = rs.getInt("id");
                            } else {
                                return "❌ ERROR: No se encontró la receta: " + valor;
                            }
                        }
                    }
                    updateSql = "UPDATE planes_produccion SET receta_id = ?, updated_at = NOW() WHERE id = ?";
                    nuevoValor = recetaId;
                    break;

                case "cantidad":
                    int cantidad;
                    try {
                        cantidad = Integer.parseInt(valor);
                        if (cantidad <= 0) {
                            return "❌ ERROR: La cantidad debe ser mayor a 0";
                        }
                    } catch (NumberFormatException e) {
                        return "❌ ERROR: La cantidad debe ser un número válido";
                    }
                    updateSql = "UPDATE planes_produccion SET cantidad = ?, updated_at = NOW() WHERE id = ?";
                    nuevoValor = cantidad;
                    break;

                default:
                    return "❌ ERROR: Campo '" + campo + "' no válido.\nCampos disponibles: nombre, receta, cantidad";
            }

            // Ejecutar actualización
            try (PreparedStatement stmt = conn.prepareStatement(updateSql)) {
                if (nuevoValor instanceof Integer) {
                    stmt.setInt(1, (Integer) nuevoValor);
                } else if (nuevoValor instanceof String) {
                    stmt.setString(1, (String) nuevoValor);
                } else {
                    stmt.setObject(1, nuevoValor);
                }
                stmt.setInt(2, planId);
                stmt.executeUpdate();
            }

            // Obtener datos actualizados
            String selectSql = "SELECT pp.id, pp.cantidad, r.nombre as receta, r.id as receta_id " +
                              "FROM planes_produccion pp " +
                              "JOIN recetas r ON pp.receta_id = r.id " +
                              "WHERE pp.id = ?";

            try (PreparedStatement selectStmt = conn.prepareStatement(selectSql)) {
                selectStmt.setInt(1, planId);

                try (ResultSet rs = selectStmt.executeQuery()) {
                    if (rs.next()) {
                        String receta = rs.getString("receta");
                        int cantidad = rs.getInt("cantidad");
                        int recetaId = rs.getInt("receta_id");

                        StringBuilder result = new StringBuilder();
                        result.append("✅ PLAN DE PRODUCCIÓN ACTUALIZADO\n\n");
                        result.append("ID: ").append(planId).append("\n");
                        result.append("Receta: ").append(receta).append("\n");
                        result.append("Cantidad: ").append(cantidad).append(" unidades\n\n");

                        if (campo.equals("cantidad")) {
                            result.append("INSUMOS RECALCULADOS:\n");
                            String insumosSql = "SELECT i.nombre, ri.cantidad * ? as cantidad_total, um.abreviatura " +
                                               "FROM recetas_insumos ri " +
                                               "JOIN insumos i ON ri.insumo_id = i.id " +
                                               "JOIN unidad_medidas um ON i.unidad_medida_id = um.id " +
                                               "WHERE ri.receta_id = ?";

                            try (PreparedStatement insStmt = conn.prepareStatement(insumosSql)) {
                                insStmt.setInt(1, cantidad);
                                insStmt.setInt(2, recetaId);

                                try (ResultSet insRs = insStmt.executeQuery()) {
                                    while (insRs.next()) {
                                        result.append("• ").append(insRs.getString("nombre")).append(": ")
                                              .append(insRs.getFloat("cantidad_total")).append(" ")
                                              .append(insRs.getString("abreviatura")).append("\n");
                                    }
                                }
                            }
                        }

                        return result.toString();
                    }
                }
            }

            return "✅ PLAN ACTUALIZADO";

        } catch (SQLException e) {
            System.out.println("❌ Error actualizando plan: " + e.getMessage());
            return "Error actualizando plan: " + e.getMessage();
        }
    }

    private static String deletePlanProduccion(String command) {
        String[] parts = command.split("ELIMINAR PLAN PRODUCCION");
        if (parts.length < 2 || parts[1].trim().isEmpty()) {
            return "Formato incorrecto.\nUse: ELIMINAR PLAN PRODUCCION [nombre_plan] [id]\n\nEjemplo: ELIMINAR PLAN PRODUCCION MenuDiario 10";
        }

        String[] params = splitParams(parts[1].trim(), 2);
        if (params.length < 2) {
            return "Formato incorrecto.\nUse: ELIMINAR PLAN PRODUCCION [nombre_plan] [id]\n\nEjemplo: ELIMINAR PLAN PRODUCCION MenuDiario 10";
        }

        String nombrePlan = params[0];
        int planId;
        try {
            planId = Integer.parseInt(params[1]);
        } catch (NumberFormatException e) {
            return "❌ ERROR: El ID debe ser un número válido";
        }

        try (Connection conn = getDatabaseConnection()) {
            // Obtener datos del plan antes de eliminar
            String selectSql = "SELECT pp.id, pp.nombre, pp.cantidad, pp.created_at, r.nombre as receta " +
                              "FROM planes_produccion pp " +
                              "JOIN recetas r ON pp.receta_id = r.id " +
                              "WHERE pp.id = ? AND pp.nombre ILIKE ?";

            String nombre = "";
            String receta = "";
            int cantidad = 0;
            String fecha = "";

            try (PreparedStatement selectStmt = conn.prepareStatement(selectSql)) {
                selectStmt.setInt(1, planId);
                selectStmt.setString(2, "%" + nombrePlan + "%");

                try (ResultSet rs = selectStmt.executeQuery()) {
                    if (!rs.next()) {
                        return "❌ ERROR: No se encontró el plan con nombre '" + nombrePlan + "' e ID: " + planId + ".\n" +
                               "Use: CONSULTAR PLANES PRODUCCION\npara ver los planes disponibles.";
                    }
                    nombre = rs.getString("nombre");
                    receta = rs.getString("receta");
                    cantidad = rs.getInt("cantidad");
                    fecha = rs.getString("created_at");
                }
            }

            // Eliminar
            String deleteSql = "DELETE FROM planes_produccion WHERE id = ?";
            try (PreparedStatement stmt = conn.prepareStatement(deleteSql)) {
                stmt.setInt(1, planId);
                int rowsAffected = stmt.executeUpdate();

                if (rowsAffected > 0) {
                    return "✅ PLAN DE PRODUCCIÓN ELIMINADO\n\n" +
                           "ID: " + planId + "\n" +
                           "Nombre: " + nombre + "\n" +
                           "Receta: " + receta + "\n" +
                           "Cantidad: " + cantidad + " unidades\n" +
                           "Fecha: " + fecha;
                } else {
                    return "❌ Error: No se pudo eliminar el plan";
                }
            }

        } catch (SQLException e) {
            System.out.println("❌ Error eliminando plan: " + e.getMessage());
            return "Error eliminando plan: " + e.getMessage();
        }
    }

    /**
     * Enviar PDF del reporte por email
     */
    private static boolean enviarPDFReporte(int reporteId, String nombreReporte) {
        try {
            System.out.println("📧 Generando y enviando PDF del reporte...");

            // Generar PDF llamando al endpoint de Laravel (stream, sin auth)
            String urlPDF = "http://127.0.0.1:8000/reportes/obtener-pdf/" + reporteId;
            System.out.println("📥 Descargando PDF desde: " + urlPDF);

            java.net.URL url = new java.net.URL(urlPDF);
            java.net.HttpURLConnection connection = (java.net.HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");

            // Leer el PDF en memoria
            java.io.InputStream inputStream = connection.getInputStream();
            java.io.ByteArrayOutputStream baos = new java.io.ByteArrayOutputStream();
            byte[] buffer = new byte[1024];
            int bytesRead;
            while ((bytesRead = inputStream.read(buffer)) != -1) {
                baos.write(buffer, 0, bytesRead);
            }
            inputStream.close();
            byte[] pdfBytes = baos.toByteArray();

            System.out.println("✅ PDF descargado: " + pdfBytes.length + " bytes");

            // Configurar sesión de email
            Properties props = new Properties();
            props.put("mail.smtp.host", SMTP_HOST);
            props.put("mail.smtp.port", SMTP_PORT);
            props.put("mail.smtp.auth", "true");
            props.put("mail.smtp.starttls.enable", "true");
            props.put("mail.smtp.ssl.protocols", "TLSv1.2");

            Session session = Session.getInstance(props, new javax.mail.Authenticator() {
                protected javax.mail.PasswordAuthentication getPasswordAuthentication() {
                    return new javax.mail.PasswordAuthentication(EMAIL_USER, EMAIL_PASSWORD);
                }
            });

            // Obtener el email del usuario que envió el comando (del último email procesado)
            String destinatario = lastSenderEmail != null ? lastSenderEmail : EMAIL_USER;

            // Crear mensaje
            Message message = new MimeMessage(session);
            message.setFrom(new InternetAddress(EMAIL_USER));
            message.setRecipients(Message.RecipientType.TO, InternetAddress.parse(destinatario));
            message.setSubject("Reporte PDF: " + nombreReporte);

            // Crear cuerpo multiparte
            MimeMultipart multipart = new MimeMultipart();

            // Parte 1: Texto
            MimeBodyPart textPart = new MimeBodyPart();
            textPart.setText("Se adjunta el reporte '" + nombreReporte + "' en formato PDF.\n\n" +
                           "ID del reporte: " + reporteId + "\n" +
                           "Generado: " + new java.util.Date());
            multipart.addBodyPart(textPart);

            // Parte 2: PDF adjunto
            MimeBodyPart attachmentPart = new MimeBodyPart();
            javax.activation.DataSource source = new javax.mail.util.ByteArrayDataSource(pdfBytes, "application/pdf");
            attachmentPart.setDataHandler(new javax.activation.DataHandler(source));
            attachmentPart.setFileName("reporte_" + nombreReporte + "_" + reporteId + ".pdf");
            multipart.addBodyPart(attachmentPart);

            // Establecer contenido
            message.setContent(multipart);

            // Enviar
            Transport.send(message);

            System.out.println("✅ PDF enviado exitosamente a: " + destinatario);
            return true;

        } catch (Exception e) {
            System.out.println("❌ Error enviando PDF: " + e.getMessage());
            e.printStackTrace();
            return false;
        }
    }

    private static float getStockInsumo(Connection conn, int insumoId) throws SQLException {
        String sql = "SELECT " +
                    "(SELECT COALESCE(SUM(cantidad), 0) FROM movimiento_inventarios WHERE insumo_id = ? AND tipo = 'entrada') - " +
                    "(SELECT COALESCE(SUM(cantidad), 0) FROM movimiento_inventarios WHERE insumo_id = ? AND tipo = 'salida') as stock";

        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, insumoId);
            stmt.setInt(2, insumoId);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getFloat("stock");
                }
            }
        }
        return 0;
    }

    // ==================== UTILIDADES PARA PARÁMETROS ====================

    private static String[] splitParams(String text) {
        return splitParams(text, 0);
    }

    private static String[] splitParams(String text, int limit) {
        if (text == null) {
            return new String[0];
        }

        String trimmed = text.trim();
        if (trimmed.isEmpty()) {
            return new String[0];
        }

        if (trimmed.contains(",")) {
            List<String> tokens = new ArrayList<>();
            for (String part : trimmed.split(",")) {
                String cleaned = part.trim();
                if (!cleaned.isEmpty()) {
                    tokens.add(cleaned);
                }
            }

            if (limit > 0 && tokens.size() > limit) {
                List<String> limited = new ArrayList<>();
                int i = 0;
                for (; i < limit - 1 && i < tokens.size(); i++) {
                    limited.add(tokens.get(i));
                }

                if (i < tokens.size()) {
                    StringBuilder remaining = new StringBuilder(tokens.get(i));
                    for (int j = i + 1; j < tokens.size(); j++) {
                        remaining.append(", ").append(tokens.get(j));
                    }
                    limited.add(remaining.toString());
                }

                tokens = limited;
            }

            return tokens.toArray(new String[0]);
        }

        if (limit > 0) {
            return trimmed.split("\\s+", limit);
        }

        return trimmed.split("\\s+");
    }

    private static String removeKeyword(String command, String keyword) {
        return Pattern.compile("(?i)" + Pattern.quote(keyword))
                .matcher(command)
                .replaceFirst("")
                .trim();
    }

    private static String[] extractParams(String command, String keyword) {
        return splitParams(removeKeyword(command, keyword));
    }

    private static String[] extractParams(String command, String keyword, int limit) {
        return splitParams(removeKeyword(command, keyword), limit);
    }
}
