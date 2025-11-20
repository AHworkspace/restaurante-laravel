# Diagrama de Secuencia - Caso de Uso Insumos

## Figura X: Diagrama de secuencia del Caso de Uso Insumos (creación de un insumo)

```
┌─────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────────┐    ┌─────────────┐
│ Gerente │    │ VistaInsumos.   │    │ VistaInsumos.   │    │InsumoController │    │ Categoria   │    │UnidadMedida │
│         │    │ index           │    │ create          │    │                 │    │             │    │             │
└────┬────┘    └────────┬────────┘    └────────┬────────┘    └────────┬────────┘    └──────┬──────┘    └──────┬──────┘
     │                   │                      │                      │                      │                   │
     │                   │                      │                       │                      │                   │
     │ 1. crearInsumo()  │                      │                       │                      │                   │
     │───────────────────>│                      │                       │                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │ 2. mostrarFormulario()│                      │                       │                      │                   │
     │                   │─────────────────────>│                      │                       │                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │ 3. create()           │                      │                   │
     │                   │                      │─────────────────────>│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │ 4. get()              │                      │                   │
     │                   │                      │─────────────────────>│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │                       │ 5. *return(categorias)│                   │
     │                   │                      │<──────────────────────│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │ 6. get()              │                      │                   │
     │                   │                      │─────────────────────>│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │                       │                      │ 7. *return(unidades)│
     │                   │                      │<──────────────────────│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │ 8. *return(vista)     │                      │                   │
     │                   │                      │<──────────────────────│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │ 9. *return(formulario)│                      │                       │                      │                   │
     │                   │<─────────────────────│                      │                       │                      │                   │
     │                   │                      │                       │                      │                   │
     │ 10. guardarInsumo()│                      │                       │                      │                   │
     │───────────────────>│                      │                       │                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │ 11. store(request)   │                      │                       │                      │                   │
     │                   │─────────────────────>│                      │                       │                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │ 12. validar(request)  │                      │                   │
     │                   │                      │─────────────────────>│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │ 13. *return(validado) │                      │                   │
     │                   │                      │<──────────────────────│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │ 14. create(datos)     │                      │                   │
     │                   │                      │─────────────────────>│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │ 15. *return(insumo)   │                      │                   │
     │                   │                      │<──────────────────────│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │ 16. registrarHistorial()│                      │                   │
     │                   │                      │─────────────────────>│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │                      │ 17. *return(confirmado)│                      │                   │
     │                   │                      │<──────────────────────│                      │                   │
     │                   │                      │                       │                      │                   │
     │                   │ 18. *return(success) │                      │                       │                      │                   │
     │                   │<─────────────────────│                      │                       │                      │                   │
     │                   │                      │                       │                      │                   │
     │ 19. *return(confirmado)│                      │                       │                      │                   │
     │<──────────────────│                      │                       │                      │                   │
     │                   │                      │                       │                      │                   │
```

## Descripción del Flujo:

### Participantes (Líneas de vida de izquierda a derecha):
1. **Gerente**: Representado por una figura de palo, es el usuario que inicia el proceso.
2. **VistaInsumos.index**: La vista principal de insumos que maneja la navegación.
3. **VistaInsumos.create**: La vista específica para el formulario de creación de insumos.
4. **InsumoController**: El controlador responsable de la lógica de negocio relacionada con insumos.
5. **Categoria**: Un componente o fuente de datos para información de categorías.
6. **UnidadMedida**: Un componente o fuente de datos para información de unidades de medida.

### Flujo de Interacciones:

1. **Iniciación**: El `Gerente` envía un mensaje `crearInsumo()` a `VistaInsumos.index`.
2. **Redirección de Solicitud**: `VistaInsumos.index` reenvía el mensaje `mostrarFormulario()` a `VistaInsumos.create`.
3. **Solicitud de Creación**: `VistaInsumos.create` envía un mensaje `create()` a `InsumoController`.
4. **Recuperación de Datos**:
   - `InsumoController` envía un mensaje `get()` a `Categoria` para recuperar datos de categorías.
   - `Categoria` responde devolviendo datos (indicado por una flecha punteada con asterisco `*`, sugiriendo una colección o múltiples elementos).
   - `InsumoController` luego envía otro mensaje `get()` a `UnidadMedida` para recuperar datos de unidades de medida.
   - `UnidadMedida` responde devolviendo datos (también indicado por una flecha punteada con asterisco `*`).
5. **Devolución de Vista**: `InsumoController` envía un mensaje `*return(vista)` de vuelta a `VistaInsumos.create`.
6. **Devolución de Formulario**: `VistaInsumos.create` envía un mensaje `*return(formulario)` a `VistaInsumos.index`.
7. **Guardado de Insumo**: El `Gerente` envía un mensaje `guardarInsumo()` a `VistaInsumos.index`.
8. **Procesamiento de Guardado**: `VistaInsumos.index` envía un mensaje `store(request)` a `InsumoController`.
9. **Validación**: `InsumoController` envía un mensaje `validar(request)` y recibe la respuesta.
10. **Creación**: `InsumoController` envía un mensaje `create(datos)` y recibe el insumo creado.
11. **Registro en Historial**: `InsumoController` envía un mensaje `registrarHistorial()` y recibe confirmación.
12. **Confirmación Final**: `InsumoController` envía un mensaje `*return(success)` a `VistaInsumos.index`.
13. **Respuesta al Usuario**: `VistaInsumos.index` envía un mensaje `*return(confirmado)` al `Gerente`.

El diagrama muestra la secuencia de operaciones desde la solicitud de un usuario para crear un insumo, a través de la recuperación de datos necesarios (categorías y unidades de medida), la validación, creación y registro en historial, hasta la confirmación final al usuario. 