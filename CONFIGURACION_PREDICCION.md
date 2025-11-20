# Configuración del Sistema de Predicción

## Problemas Identificados y Soluciones

### 1. **Problema: Endpoint Incorrecto**
**Problema:** El código estaba llamando a `/predict` que no existe.
**Solución:** Cambiado a `/predict/month_insumo` que es el endpoint correcto.

### 2. **Problema: Variable de Entorno Faltante**
**Problema:** No estaba configurada `HOST_MODELO_PREDICCION`.
**Solución:** Agregar al archivo `.env`:
```
HOST_MODELO_PREDICCION=http://127.0.0.1:5000
```

### 3. **Problema: Interfaz Confusa**
**Problema:** El texto no explicaba claramente qué hace la predicción.
**Solución:** Cambiado a: "Predecir la cantidad de insumo que se necesitará en un mes específico basado en el historial de consumo"

### 4. **Problema: Método Faltante**
**Problema:** El botón "RE-ENTRENAR" llamaba a un método que no existía.
**Solución:** Implementado el método `reentrenar()` completo.

## Configuración Requerida

### 1. **Archivo .env**
Crear o modificar el archivo `.env` en `sistema-web/` con:
```env
# Configuración del servicio de IA
HOST_MODELO_PREDICCION=http://127.0.0.1:5000
```

### 2. **Servicio de IA**
Asegurarse de que el servicio de IA esté ejecutándose:
```bash
cd servicio-ia
python flask_service.py
```

### 3. **Directorio de Exportación**
El directorio `sistema-web/public/exports/` debe existir para los archivos CSV.

## Cómo Debería Funcionar la Predicción

### **Predicción por Insumo (Implementada)**
- **Propósito:** Predecir cuánto de un insumo específico se necesitará en un mes basado en el historial de consumo.
- **Entrada:** Insumo + Mes
- **Salida:** Cantidad estimada en la unidad de medida correspondiente

### **Predicción por Receta (Alternativa)**
- **Propósito:** Predecir cuánto de un insumo se necesita para producir una receta específica en un mes.
- **Entrada:** Insumo + Receta + Mes
- **Salida:** Cantidad estimada para esa receta

### **Predicción de Desperdicio (Ya Implementada)**
- **Propósito:** Predecir cuánto desperdicio se generará de un insumo en una receta.
- **Entrada:** Insumo + Receta + Mes + Cantidad
- **Salida:** Cantidad de desperdicio estimada

## Mejoras Implementadas

1. **Validación de Campos:** Verifica que se seleccionen insumo y mes antes de hacer la predicción.
2. **Manejo de Errores:** Captura y muestra errores específicos del servicio de IA.
3. **Registro en Historial:** Todas las predicciones y errores se registran en el historial.
4. **Mensajes Claros:** Los resultados muestran información detallada y clara.
5. **Re-entrenamiento:** Permite actualizar el modelo con nuevos datos.

## Endpoints del Servicio de IA

- `/predict/month_insumo` - Predicción por insumo y mes
- `/predict/month_plato` - Predicción por insumo, receta y mes
- `/predict/desperdicio` - Predicción de desperdicio
- `/retrain` - Re-entrenamiento del modelo

## Pruebas Recomendadas

1. **Verificar Conexión:** Asegurarse de que el servicio de IA esté ejecutándose
2. **Probar Predicción:** Seleccionar un insumo y mes, hacer predicción
3. **Verificar Historial:** Revisar que las acciones se registren en el historial
4. **Probar Re-entrenamiento:** Ejecutar el re-entrenamiento con datos nuevos 