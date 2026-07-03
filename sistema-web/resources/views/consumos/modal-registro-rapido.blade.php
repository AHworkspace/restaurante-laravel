{{-- Modal para registro rápido de consumo desde el dashboard --}}
<div class="modal fade" id="modalRegistrarConsumo" tabindex="-1" aria-labelledby="modalRegistrarConsumoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content consumo-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title consumo-modal-title" id="modalRegistrarConsumoLabel">Registrar Consumo</h5>
                    <small class="text-muted">Agrega platos al pedido y guarda todo junto.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formRegistrarConsumo" action="{{ route('consumos.store') }}" method="POST">
                @csrf
                <input type="hidden" name="receta_id" id="modal_receta_id">
                <input type="hidden" name="cantidad" id="modal_cantidad" value="1">
                <input type="hidden" name="items" id="pedido_items_json">
                <input type="hidden" name="tipo_comida_id" id="modal_tipo_comida_id">
                <input type="hidden" name="fecha_consumo" value="{{ now()->toDateString() }}">

                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="consumo-panel h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Pedido actual</h6>
                                    <span class="badge rounded-pill text-bg-light" id="pedido-count-badge">0 items</span>
                                </div>

                                <div id="mensaje-inicial" class="consumo-empty-state">
                                    <div class="fw-semibold mb-1">Selecciona un plato del dashboard</div>
                                    <small class="text-muted">Se agregará automáticamente al pedido para editar cantidades.</small>
                                </div>

                                <div id="pedido-items-container" style="display:none;">
                                    <div id="items-pedido-tbody" class="pedido-items-list"></div>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label fw-semibold">Agregar otro plato</label>
                                    <select class="form-select" id="select-agregar-plato">
                                        <option value="">Seleccionar plato...</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="consumo-panel h-100">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Cliente (opcional)</label>
                                    <small class="text-muted d-block mb-2">Si no seleccionas cliente, se registra como venta pública.</small>
                                    <input type="text"
                                           class="form-control mb-2"
                                           id="buscar_consumidor"
                                           placeholder="Buscar por CI o nombre..."
                                           autocomplete="off">
                                    <input type="hidden" name="consumidor_id" id="consumidor_id_selected">

                                    <div id="lista_consumidores" class="consumidores-list">
                                        @if(isset($consumidores) && $consumidores->count() > 0)
                                            @foreach($consumidores as $consumidor)
                                                <div class="consumidor-item"
                                                     data-id="{{ $consumidor->id }}"
                                                     data-nombre="{{ $consumidor->nombre_completo }}"
                                                     data-ci="{{ $consumidor->ci }}"
                                                     data-grado="{{ $consumidor->grado->nombre ?? 'N/A' }}"
                                                     data-fuerza="{{ $consumidor->fuerza->nombre ?? 'N/A' }}"
                                                     data-pendiente="{{ number_format($consumidor->saldoPendiente(), 2) }}"
                                                     onclick="seleccionarConsumidorDesdeLista({{ $consumidor->id }}, '{{ $consumidor->nombre_completo }}', '{{ $consumidor->ci }}', '{{ $consumidor->grado->nombre ?? 'N/A' }}', '{{ $consumidor->fuerza->nombre ?? 'N/A' }}', '{{ number_format($consumidor->saldoPendiente(), 2) }}')">
                                                    <div class="consumidor-name">{{ $consumidor->nombre_completo }}</div>
                                                    <div class="consumidor-meta">CI: {{ $consumidor->ci }} | {{ $consumidor->grado->nombre ?? 'N/A' }} | {{ $consumidor->fuerza->nombre ?? 'N/A' }}</div>
                                                    <div class="consumidor-pendiente">Pendiente: Bs. {{ number_format($consumidor->saldoPendiente(), 2) }}</div>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted text-center py-2 mb-0">No hay clientes disponibles</p>
                                        @endif
                                    </div>
                                    <div id="resultados_consumidores" class="list-group mt-2" style="display:none; max-height: 220px; overflow-y: auto;"></div>
                                </div>

                                <div id="info_consumidor" class="consumidor-selected" style="display:none;">
                                    <div class="fw-semibold" id="consumidor_nombre"></div>
                                    <small id="consumidor_detalles" class="d-block text-muted mb-1"></small>
                                    <small class="text-danger">Pendiente: Bs. <span id="consumidor_pendiente">0.00</span></small>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label fw-semibold">Tipo de comida</label>
                                    <select class="form-select" id="modal_tipo_comida_select">
                                        <option value="">Seleccionar...</option>
                                        @foreach(\App\Models\TipoComida::where('activo', true)->get() as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label fw-semibold">Observaciones</label>
                                    <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas adicionales del pedido..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer consumo-footer">
                    <div class="consumo-total-box">
                        <small class="text-muted">Total del pedido</small>
                        <div id="total-pedido" class="consumo-total-amount">Bs. 0.00</div>
                    </div>
                    <button type="button" class="btn btn-outline-danger" id="btn-limpiar-pedido">Limpiar</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4">Guardar Pedido</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .consumo-modal {
        border: none;
        border-radius: 14px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }

    .consumo-modal-title {
        font-weight: 700;
        color: #4a1c1c;
    }

    .consumo-panel {
        border: 1px solid #ececec;
        border-radius: 12px;
        padding: 14px;
        background: #fff;
    }

    .consumo-empty-state {
        border: 1px dashed #d8d8d8;
        border-radius: 10px;
        background: #fafafa;
        padding: 18px;
        text-align: center;
    }

    .pedido-items-list {
        max-height: 320px;
        overflow-y: auto;
        padding-right: 2px;
    }

    .pedido-item-card {
        border: 1px solid #efefef;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #fff;
    }

    .pedido-item-title {
        font-weight: 600;
        color: #2f2f2f;
    }

    .pedido-item-subtitle {
        font-size: 12px;
        color: #8a8a8a;
    }

    .pedido-item-controls {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pedido-item-controls .input-cantidad {
        width: 62px;
        text-align: center;
        padding-left: 4px;
        padding-right: 4px;
    }

    .pedido-item-subtotal {
        font-weight: 700;
        color: #8B4513;
    }

    .consumidores-list {
        max-height: 220px;
        overflow-y: auto;
        border: 1px solid #ececec;
        border-radius: 10px;
        background: #fff;
        padding: 6px;
    }

    .consumidor-item {
        border-radius: 8px;
        padding: 8px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        margin-bottom: 4px;
    }

    .consumidor-item:last-child {
        margin-bottom: 0;
    }

    .consumidor-item:hover {
        background: #f7f7f7;
    }

    .consumidor-item.selected {
        background: #e7f1ff;
        border: 1px solid #cfe2ff;
    }

    .consumidor-name {
        font-weight: 600;
        font-size: 13px;
    }

    .consumidor-meta {
        font-size: 11px;
        color: #777;
    }

    .consumidor-pendiente {
        font-size: 11px;
        color: #c53939;
        margin-top: 2px;
    }

    .consumidor-selected {
        border: 1px solid #d1e7dd;
        background: #f4fbf7;
        border-radius: 10px;
        padding: 10px;
    }

    .consumo-footer {
        border-top: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .consumo-total-box {
        margin-right: auto;
        background: #f8f9fa;
        border: 1px solid #ececec;
        border-radius: 10px;
        padding: 8px 12px;
        min-width: 160px;
    }

    .consumo-total-amount {
        font-weight: 700;
        color: #4a1c1c;
        font-size: 18px;
        line-height: 1.1;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var pedidoItems = [];
    var pedidoMenuDiaId = null;

    function normalizarNumero(valor) {
        var n = parseFloat(valor);
        return Number.isFinite(n) ? n : 0;
    }

    function calcularTotalPedido() {
        return pedidoItems.reduce(function(total, item) {
            return total + (item.cantidad * item.precio);
        }, 0);
    }

    function renderPedido() {
        var tbody = document.getElementById('items-pedido-tbody');
        var totalPedido = document.getElementById('total-pedido');
        var contenedor = document.getElementById('pedido-items-container');
        var mensajeInicial = document.getElementById('mensaje-inicial');
        var recetaIdHidden = document.getElementById('modal_receta_id');
        var cantidadHidden = document.getElementById('modal_cantidad');
        var itemsJson = document.getElementById('pedido_items_json');
        var pedidoCountBadge = document.getElementById('pedido-count-badge');

        tbody.innerHTML = '';

        if (pedidoItems.length === 0) {
            contenedor.style.display = 'none';
            mensajeInicial.style.display = 'block';
            totalPedido.textContent = 'Bs. 0.00';
            pedidoCountBadge.textContent = '0 items';
            recetaIdHidden.value = '';
            cantidadHidden.value = '1';
            itemsJson.value = '';
            return;
        }

        contenedor.style.display = 'block';
        mensajeInicial.style.display = 'none';

        pedidoItems.forEach(function(item, index) {
            var subtotal = item.cantidad * item.precio;
            var div = document.createElement('div');
            div.className = 'pedido-item-card';
            div.innerHTML =
                '<div class="d-flex justify-content-between align-items-start gap-2 mb-2">' +
                    '<div>' +
                        '<div class="pedido-item-title">' + item.nombre + '</div>' +
                        '<div class="pedido-item-subtitle">Precio unitario: Bs. ' + item.precio.toFixed(2) + ' | Disponibles: ' + item.disponible + '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-item" data-index="' + index + '" title="Quitar">x</button>' +
                '</div>' +
                '<div class="d-flex justify-content-between align-items-center">' +
                    '<div class="pedido-item-controls">' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary btn-restar" data-index="' + index + '">-</button>' +
                        '<input type="number" min="1" max="' + item.disponible + '" class="form-control form-control-sm input-cantidad" data-index="' + index + '" value="' + item.cantidad + '">' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary btn-sumar" data-index="' + index + '" ' + (item.cantidad >= item.disponible ? 'disabled' : '') + '>+</button>' +
                    '</div>' +
                    '<div class="pedido-item-subtotal">Bs. ' + subtotal.toFixed(2) + '</div>' +
                '</div>';
            tbody.appendChild(div);
        });

        var total = calcularTotalPedido();
        totalPedido.textContent = 'Bs. ' + total.toFixed(2);
        pedidoCountBadge.textContent = pedidoItems.length + (pedidoItems.length === 1 ? ' item' : ' items');

        // Compatibilidad con flujo legado del controlador (primer item).
        recetaIdHidden.value = String(pedidoItems[0].receta_id);
        cantidadHidden.value = String(pedidoItems[0].cantidad);
        itemsJson.value = JSON.stringify(pedidoItems.map(function(item) {
            return {
                receta_id: item.receta_id,
                cantidad: item.cantidad,
                menu_dia_id: item.menu_dia_id
            };
        }));
    }

    function agregarItemAlPedido(recetaId, recetaNombre, recetaPrecio, menuDiaId, disponible) {
        recetaId = String(recetaId).startsWith('presentacion:') ? String(recetaId) : parseInt(recetaId, 10);
        var precio = normalizarNumero(recetaPrecio);
        disponible = parseInt(disponible || '0', 10);
        if (!recetaId || precio <= 0 || disponible < 1) return;

        var existente = pedidoItems.find(function(item) { return item.receta_id === recetaId; });
        if (existente) {
            existente.cantidad = Math.min(existente.disponible, existente.cantidad + 1);
        } else {
            pedidoItems.push({
                receta_id: recetaId,
                nombre: recetaNombre,
                precio: precio,
                cantidad: 1,
                menu_dia_id: menuDiaId ? parseInt(menuDiaId, 10) : null,
                disponible: disponible
            });
        }
        renderPedido();
    }

    // Cuando se abre el modal, cargar datos del plato
    var modalRegistrarConsumo = document.getElementById('modalRegistrarConsumo');
    if (modalRegistrarConsumo) {
        modalRegistrarConsumo.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var recetaId = button.getAttribute('data-receta-id');
            var recetaNombre = button.getAttribute('data-receta-nombre');
            var recetaPrecio = button.getAttribute('data-receta-precio');
            var tipoComidaId = button.getAttribute('data-tipo-comida-id');
            var menuDiaId = button.getAttribute('data-menu-dia-id');
            var disponibleSeleccionado = button.getAttribute('data-disponible');
            var dashboard = button.closest('.menu-dashboard');
            var selector = document.getElementById('select-agregar-plato');

            if (pedidoMenuDiaId !== String(menuDiaId || '')) {
                pedidoItems = [];
                pedidoMenuDiaId = String(menuDiaId || '');
            }

            selector.innerHTML = '<option value="">Seleccionar plato...</option>';
            if (dashboard) {
                dashboard.querySelectorAll('.plato-card').forEach(function(card) {
                    var id = card.querySelector('.plato-data-receta-id')?.value;
                    var nombre = card.querySelector('.plato-data-receta-nombre')?.value;
                    var precio = card.querySelector('.plato-data-receta-precio')?.value;
                    var disponible = card.querySelector('.plato-data-disponible')?.value;
                    if (!id) return;
                    var option = document.createElement('option');
                    option.value = id;
                    option.textContent = nombre + ' - Bs. ' + Number(precio).toFixed(2) + ' - Quedan ' + disponible;
                    option.dataset.nombre = nombre;
                    option.dataset.precio = precio;
                    option.dataset.menuDiaId = menuDiaId || '';
                    option.dataset.disponible = disponible || '0';
                    selector.appendChild(option);
                });
            }

            // Cada click en "Agregar" incorpora el plato al pedido actual.
            agregarItemAlPedido(recetaId, recetaNombre, recetaPrecio, menuDiaId, disponibleSeleccionado);
            renderPedido();

            // Pre-llenar tipo de comida automáticamente
            if (tipoComidaId) {
                document.getElementById('modal_tipo_comida_id').value = tipoComidaId;
                document.getElementById('modal_tipo_comida_select').value = tipoComidaId;
            } else {
                document.getElementById('modal_tipo_comida_id').value = '';
                document.getElementById('modal_tipo_comida_select').value = '';
            }

            // Limpiar formulario
            document.getElementById('buscar_consumidor').value = '';
            document.getElementById('consumidor_id_selected').value = '';
            document.getElementById('info_consumidor').style.display = 'none';
            document.getElementById('resultados_consumidores').style.display = 'none';
            document.getElementById('lista_consumidores').style.display = 'block';

            // Limpiar selección visual
            document.querySelectorAll('.consumidor-item').forEach(item => {
                item.classList.remove('selected');
                item.style.backgroundColor = 'transparent';
                item.style.display = 'block';
            });
        });
    }

    // Eventos del pedido (delegación)
    document.getElementById('items-pedido-tbody').addEventListener('click', function(e) {
        var target = e.target.closest('button');
        if (!target) return;
        var index = parseInt(target.getAttribute('data-index') || '-1', 10);
        if (index < 0 || !pedidoItems[index]) return;

        if (target.classList.contains('btn-sumar')) {
            pedidoItems[index].cantidad = Math.min(pedidoItems[index].disponible, pedidoItems[index].cantidad + 1);
            renderPedido();
        }
        if (target.classList.contains('btn-restar')) {
            pedidoItems[index].cantidad = Math.max(1, pedidoItems[index].cantidad - 1);
            renderPedido();
        }
        if (target.classList.contains('btn-eliminar-item')) {
            pedidoItems.splice(index, 1);
            renderPedido();
        }
    });

    document.getElementById('items-pedido-tbody').addEventListener('change', function(e) {
        if (!e.target.classList.contains('input-cantidad')) return;
        var index = parseInt(e.target.getAttribute('data-index') || '-1', 10);
        if (index < 0 || !pedidoItems[index]) return;
        var nuevaCantidad = parseInt(e.target.value || '1', 10);
        pedidoItems[index].cantidad = Number.isFinite(nuevaCantidad) && nuevaCantidad > 0
            ? Math.min(nuevaCantidad, pedidoItems[index].disponible)
            : 1;
        renderPedido();
    });

    // Agregar otro plato desde el selector
    var selectAgregarPlato = document.getElementById('select-agregar-plato');
    if (selectAgregarPlato) {
        selectAgregarPlato.addEventListener('change', function() {
            var recetaId = this.value;
            if (!recetaId) return;
            var opt = this.options[this.selectedIndex];
            agregarItemAlPedido(recetaId, opt.getAttribute('data-nombre'), opt.getAttribute('data-precio'), opt.getAttribute('data-menu-dia-id'), opt.getAttribute('data-disponible'));
            this.value = '';
        });
    }

    // Limpiar pedido completo
    var btnLimpiar = document.getElementById('btn-limpiar-pedido');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            pedidoItems = [];
            renderPedido();
        });
    }

    // Autocomplete de consumidores y filtrado de lista
    var buscarConsumidor = document.getElementById('buscar_consumidor');
    var resultadosConsumidores = document.getElementById('resultados_consumidores');
    var listaConsumidores = document.getElementById('lista_consumidores');
    var consumidorIdSelected = document.getElementById('consumidor_id_selected');
    var infoConsumidor = document.getElementById('info_consumidor');

    if (buscarConsumidor) {
        buscarConsumidor.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();

            if (query.length >= 2) {
                // Mostrar resultados de búsqueda AJAX y ocultar lista normal
                listaConsumidores.style.display = 'none';
                resultadosConsumidores.style.display = 'block';

                fetch('{{ route("consumidores.buscar") }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        resultadosConsumidores.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(function(consumidor) {
                                var item = document.createElement('a');
                                item.href = '#';
                                item.className = 'list-group-item list-group-item-action';
                                item.innerHTML = '<strong>' + consumidor.nombre_completo + '</strong><br>' +
                                                '<small>CI: ' + consumidor.ci + ' | ' + consumidor.grado + ' | ' + consumidor.fuerza + '</small><br>' +
                                                '<small class="text-danger">Pendiente: Bs. ' + consumidor.pendiente + '</small>';
                                item.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    seleccionarConsumidor(consumidor);
                                });
                                resultadosConsumidores.appendChild(item);
                            });
                        } else {
                            resultadosConsumidores.innerHTML = '<div class="list-group-item">No se encontraron resultados</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            } else {
                // Mostrar lista normal y ocultar resultados de búsqueda
                listaConsumidores.style.display = 'block';
                resultadosConsumidores.style.display = 'none';

                // Filtrar lista visible si hay texto
                if (query.length > 0) {
                    var items = listaConsumidores.querySelectorAll('.consumidor-item');
                    items.forEach(function(item) {
                        var nombre = item.getAttribute('data-nombre').toLowerCase();
                        var ci = item.getAttribute('data-ci').toLowerCase();
                        if (nombre.includes(query) || ci.includes(query)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                } else {
                    // Mostrar todos los items
                    var items = listaConsumidores.querySelectorAll('.consumidor-item');
                    items.forEach(function(item) {
                        item.style.display = 'block';
                    });
                }
            }
        });
    }

    function seleccionarConsumidor(consumidor) {
        seleccionarConsumidorDesdeLista(
            consumidor.id,
            consumidor.nombre_completo,
            consumidor.ci,
            consumidor.grado,
            consumidor.fuerza,
            consumidor.pendiente
        );
    }

    function seleccionarConsumidorDesdeLista(id, nombre, ci, grado, fuerza, pendiente) {
        consumidorIdSelected.value = id;
        buscarConsumidor.value = nombre;
        document.getElementById('consumidor_nombre').textContent = nombre;
        document.getElementById('consumidor_detalles').textContent = 'CI: ' + ci + ' | ' + grado + ' | ' + fuerza;
        document.getElementById('consumidor_pendiente').textContent = pendiente;

        // Ocultar resultados de búsqueda
        resultadosConsumidores.style.display = 'none';

        // Mostrar info del consumidor seleccionado
        infoConsumidor.style.display = 'block';

        // Marcar visualmente el item seleccionado en la lista
        document.querySelectorAll('.consumidor-item').forEach(item => {
            item.classList.remove('selected');
            item.style.backgroundColor = 'transparent';
            if (item.getAttribute('data-id') == id) {
                item.classList.add('selected');
                item.style.backgroundColor = '#e3f2fd';
            }
        });
    }

    // Disponible para onclick inline de la lista renderizada por Blade.
    window.seleccionarConsumidorDesdeLista = seleccionarConsumidorDesdeLista;

    // Sincronizar el campo oculto tipo_comida_id cuando cambie el select
    var tipoComidaSelect = document.getElementById('modal_tipo_comida_select');
    var tipoComidaHidden = document.getElementById('modal_tipo_comida_id');
    if (tipoComidaSelect && tipoComidaHidden) {
        tipoComidaSelect.addEventListener('change', function() {
            tipoComidaHidden.value = this.value;
        });
    }

    // Validación mínima antes de enviar.
    var formRegistrarConsumo = document.getElementById('formRegistrarConsumo');
    if (formRegistrarConsumo) {
        formRegistrarConsumo.addEventListener('submit', function(e) {
            if (pedidoItems.length === 0) {
                e.preventDefault();
                alert('Debes agregar al menos un plato al pedido.');
                return;
            }
            var excedido = pedidoItems.find(function(item) { return item.cantidad > item.disponible; });
            if (excedido) {
                e.preventDefault();
                alert('Solo quedan ' + excedido.disponible + ' porciones de ' + excedido.nombre + '.');
                return;
            }
            renderPedido();
        });
    }
});
</script>
