@extends('layouts.app')

@section('content')
<div class="title-wrapper pt-30">
    <div class="title mb-30"><h2>Registrar consumo</h2></div>
</div>

<form method="POST" action="{{ route('consumos.store') }}" id="form-consumo">
    @csrf
    <input type="hidden" name="origen" value="consumos">
    <input type="hidden" name="items" id="items" value="{{ old('items') }}">

    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                @if($errors->any())
                    <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
                @endif

                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label for="consumidor_id">Cliente</label>
                        <select class="form-control" name="consumidor_id" id="consumidor_id" required>
                            <option value="">Seleccionar cliente</option>
                            @foreach($consumidores as $consumidor)
                                <option value="{{ $consumidor->id }}" @selected(old('consumidor_id', $consumidorSeleccionado) == $consumidor->id)>
                                    {{ $consumidor->nombre_completo }} - {{ $consumidor->ci }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label for="fecha_consumo">Fecha</label>
                        <input type="date" class="form-control" name="fecha_consumo" id="fecha_consumo" value="{{ old('fecha_consumo', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label for="hora_consumo">Hora</label>
                        <input type="time" class="form-control" name="hora_consumo" value="{{ old('hora_consumo', now()->format('H:i')) }}" required>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label for="menu_dia_id">Menú</label>
                        <select class="form-control" id="menu_dia_id">
                            <option value="">Sin menú - mostrar todos los platos</option>
                        </select>
                        <small class="text-muted" id="menu-ayuda">Puedes registrar el consumo con el catálogo completo.</small>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label for="tipo_comida_id">Tipo de comida</label>
                        <select class="form-control" name="tipo_comida_id" id="tipo_comida_id">
                            <option value="">Sin clasificar</option>
                            @foreach($tiposComida as $tipo)
                                <option value="{{ $tipo->id }}" @selected(old('tipo_comida_id') == $tipo->id)>{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-style-3 mb-30">
            <div class="card-content">
                <h4 class="mb-3">Platos del consumo</h4>
                <div class="row align-items-end">
                    <div class="col-lg-7 mb-3">
                        <label for="receta_selector">Plato o producto</label>
                        <select class="form-control" id="receta_selector"></select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-3">
                        <label for="cantidad_selector">Cantidad</label>
                        <input type="number" class="form-control" id="cantidad_selector" min="1" value="1">
                    </div>
                    <div class="col-lg-3 col-md-8 mb-3">
                        <button type="button" class="main-btn primary-btn btn-hover w-100" id="agregar-plato">
                            <i class="lni lni-plus"></i> Agregar plato
                        </button>
                    </div>
                </div>

                <div id="mensaje-platos" class="alert alert-light border">Todavía no agregaste platos.</div>
                <div class="table-responsive d-none" id="tabla-contenedor">
                    <table class="table">
                        <thead><tr><th>Plato</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th><th></th></tr></thead>
                        <tbody id="detalle-platos"></tbody>
                        <tfoot><tr><th colspan="3" class="text-end">Total</th><th id="total-pedido">Bs 0.00</th><th></th></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-style-3 mb-30">
            <div class="card-content">
                <label for="observaciones">Observaciones</label>
                <textarea class="form-control mb-3" id="observaciones" name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
                <button class="main-btn primary-btn btn-hover" type="submit">Registrar consumo</button>
                <a class="main-btn light-btn btn-hover" href="{{ route('consumos.index') }}">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const recetas = @json($recetasJson);
    const menus = @json($menusJson);
    const fecha = document.getElementById('fecha_consumo');
    const menuSelect = document.getElementById('menu_dia_id');
    const recetaSelect = document.getElementById('receta_selector');
    const cantidad = document.getElementById('cantidad_selector');
    const tipo = document.getElementById('tipo_comida_id');
    const itemsInput = document.getElementById('items');
    let items = [];

    try { items = JSON.parse(itemsInput.value || '[]'); } catch (error) { items = []; }
    items = items.map(item => {
        const menu = menus.find(actual => String(actual.id) === String(item.menu_dia_id || ''));
        const plato = menu?.platos.find(actual => String(actual.id) === String(item.receta_id))
            || recetas.find(actual => String(actual.id) === String(item.receta_id));
        return plato ? {...item, nombre: plato.nombre, precio: Number(plato.precio), cantidad: Number(item.cantidad)} : null;
    }).filter(Boolean);

    function menuActual() {
        return menus.find(menu => String(menu.id) === menuSelect.value) || null;
    }

    function cargarMenus() {
        const seleccionado = menuSelect.value || String(items[0]?.menu_dia_id || '');
        const disponibles = menus.filter(menu => menu.fecha === fecha.value);
        menuSelect.innerHTML = '<option value="">Sin menú - mostrar todos los platos</option>';
        disponibles.forEach(menu => {
            const horario = menu.hora_inicio || menu.hora_fin
                ? ` (${(menu.hora_inicio || '--:--').slice(0, 5)} - ${(menu.hora_fin || '--:--').slice(0, 5)})`
                : '';
            menuSelect.add(new Option(menu.titulo + horario, menu.id));
        });
        if (disponibles.some(menu => String(menu.id) === seleccionado)) menuSelect.value = seleccionado;
        if (!menuSelect.value && seleccionado) items = [];
        cargarPlatos();
    }

    function cargarPlatos() {
        const menu = menuActual();
        const lista = menu ? menu.platos.filter(plato => plato.disponible > 0) : recetas;
        recetaSelect.innerHTML = '<option value="">Seleccionar plato</option>';
        lista.forEach(plato => {
            const restante = menu ? ` - quedan ${plato.disponible}` : '';
            const option = new Option(`${plato.nombre} - Bs ${plato.precio.toFixed(2)}${restante}`, plato.id);
            option.dataset.precio = plato.precio;
            option.dataset.nombre = plato.nombre;
            option.dataset.disponible = menu ? plato.disponible : '';
            recetaSelect.add(option);
        });
        document.getElementById('menu-ayuda').textContent = menu
            ? 'Solo se muestran los platos disponibles de este menú.'
            : 'Puedes registrar el consumo con el catálogo completo.';
        if (menu && menu.tipo_comida_id) tipo.value = menu.tipo_comida_id;
        renderizar();
    }

    function renderizar() {
        const tbody = document.getElementById('detalle-platos');
        tbody.innerHTML = '';
        let total = 0;
        items.forEach((item, indice) => {
            total += item.precio * item.cantidad;
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${item.nombre}</td><td>${item.cantidad}</td><td>Bs ${item.precio.toFixed(2)}</td><td>Bs ${(item.precio * item.cantidad).toFixed(2)}</td><td><button type="button" class="btn btn-sm btn-outline-danger quitar" data-indice="${indice}" title="Quitar"><i class="lni lni-trash-can"></i></button></td>`;
            tbody.appendChild(tr);
        });
        itemsInput.value = items.length ? JSON.stringify(items.map(item => ({receta_id: item.receta_id, cantidad: item.cantidad, menu_dia_id: item.menu_dia_id}))) : '';
        document.getElementById('total-pedido').textContent = `Bs ${total.toFixed(2)}`;
        document.getElementById('tabla-contenedor').classList.toggle('d-none', !items.length);
        document.getElementById('mensaje-platos').classList.toggle('d-none', !!items.length);
    }

    document.getElementById('agregar-plato').addEventListener('click', function () {
        const option = recetaSelect.options[recetaSelect.selectedIndex];
        const menu = menuActual();
        const valor = Number(cantidad.value);
        if (!recetaSelect.value || !Number.isInteger(valor) || valor < 1) return;
        const existente = items.find(item => String(item.receta_id) === recetaSelect.value && String(item.menu_dia_id || '') === String(menu?.id || ''));
        const acumulado = valor + (existente ? existente.cantidad : 0);
        const disponible = Number(option.dataset.disponible || 0);
        if (menu && acumulado > disponible) {
            alert(`Solo quedan ${disponible} porciones de ${option.dataset.nombre}.`);
            return;
        }
        if (existente) existente.cantidad = acumulado;
        else items.push({receta_id: recetaSelect.value.startsWith('presentacion:') ? recetaSelect.value : Number(recetaSelect.value), menu_dia_id: menu?.id || null, nombre: option.dataset.nombre, precio: Number(option.dataset.precio), cantidad: valor});
        cantidad.value = 1;
        renderizar();
    });

    document.getElementById('detalle-platos').addEventListener('click', function (event) {
        const boton = event.target.closest('.quitar');
        if (!boton) return;
        items.splice(Number(boton.dataset.indice), 1);
        renderizar();
    });

    menuSelect.addEventListener('change', function () { items = []; cargarPlatos(); });
    fecha.addEventListener('change', function () { items = []; cargarMenus(); });
    document.getElementById('form-consumo').addEventListener('submit', function (event) {
        if (!items.length) {
            event.preventDefault();
            alert('Agrega al menos un plato al consumo.');
        }
    });

    cargarMenus();
    renderizar();
});
</script>
@endsection
