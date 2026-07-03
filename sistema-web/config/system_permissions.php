<?php

$crud = fn (string $base, string $nombre) => [
    "{$base}.ver" => "Ver {$nombre}",
    "{$base}.crear" => "Crear {$nombre}",
    "{$base}.editar" => "Editar {$nombre}",
    "{$base}.eliminar" => "Eliminar {$nombre}",
];

return [
    'Dashboard' => [
        'Panel principal' => ['dashboard.ver' => 'Ver dashboard'],
    ],
    'Usuarios' => [
        'Personal' => $crud('usuarios', 'personal'),
        'Consumidores' => $crud('consumidores', 'consumidores'),
        'Clasificacion de clientes' => $crud('clasificacion_clientes', 'clasificaciones'),
        'Proveedores' => $crud('proveedores', 'proveedores'),
    ],
    'Productos' => [
        'Insumos' => $crud('insumos', 'insumos'),
        'Categorias de insumos' => $crud('categorias_insumos', 'categorias'),
        'Unidades de medida' => $crud('unidades', 'unidades'),
        'Formatos de empaque' => $crud('formatos_empaque', 'formatos de empaque'),
        'Marcas y empresas' => $crud('marcas', 'marcas y empresas'),
        'Alertas y notificaciones' => ['alertas.ver' => 'Ver alertas y notificaciones'],
    ],
    'Inventario' => [
        'Compras' => [
            'compras.ver' => 'Ver compras', 'compras.crear' => 'Registrar compras',
            'compras.abonar' => 'Registrar abonos a proveedores',
        ],
        'Movimientos' => $crud('movimientos', 'movimientos'),
    ],
    'Produccion' => [
        'Recetas' => $crud('recetas', 'recetas'),
        'Tipos de comida' => $crud('tipos_comida', 'tipos de comida'),
        'Menus del dia' => array_merge($crud('menus', 'menus'), ['menus.publicar' => 'Publicar u ocultar menus']),
        'Predicciones' => ['predicciones.ver' => 'Ver predicciones'],
    ],
    'Operaciones' => [
        'Ventas' => $crud('ventas', 'ventas'),
        'Consumos' => [
            'consumos.ver' => 'Ver consumos', 'consumos.crear' => 'Registrar consumos',
            'consumos.eliminar' => 'Eliminar consumos',
        ],
        'Pagos' => ['pagos.ver' => 'Ver pagos', 'pagos.crear' => 'Registrar pagos'],
    ],
    'Reportes y control' => [
        'Reportes' => [
            'reportes.ver' => 'Ver reportes', 'reportes.crear' => 'Crear reportes',
            'reportes.editar' => 'Editar reportes', 'reportes.eliminar' => 'Eliminar reportes',
            'reportes.exportar' => 'Generar y descargar reportes',
        ],
        'Historial' => ['historial.ver' => 'Ver historial'],
    ],
];
