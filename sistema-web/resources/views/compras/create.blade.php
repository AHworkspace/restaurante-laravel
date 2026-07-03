@extends('layouts.app')

@section('content')
<div class="title-wrapper pt-30"><div class="title mb-30"><h2>Nueva compra</h2></div></div>
<div class="card-style-3 mb-30"><div class="card-content">
    <form method="POST" action="{{ route('compras.store') }}">
        @csrf
        <div class="row g-3 mb-4">
            <div class="col-md-4"><label>Proveedor</label><select class="form-select" name="proveedor_id" required><option value="">Seleccionar</option>@foreach($proveedores as $p)<option value="{{ $p->id }}" @selected(old('proveedor_id')==$p->id)>{{ $p->nombre }}</option>@endforeach</select></div>
            <div class="col-md-3"><label>Fecha</label><input type="date" class="form-control" name="fecha_compra" value="{{ old('fecha_compra',now()->toDateString()) }}" required></div>
            <div class="col-md-3"><label>Número de documento</label><input class="form-control" name="numero_documento" value="{{ old('numero_documento') }}"></div>
            <div class="col-12"><label>Descripción</label><textarea class="form-control" name="descripcion">{{ old('descripcion') }}</textarea></div>
        </div>

        <section class="border-top pt-3">
            <div class="d-flex justify-content-between align-items-center mb-3"><h4>Líneas de compra</h4><button type="button" class="btn btn-sm btn-secondary" id="agregar-linea"><i class="lni lni-plus"></i> Agregar línea</button></div>
            <div id="lineas"></div>
            <div class="text-end mb-4"><strong>Total de la compra: Bs <span id="total-compra">0.00</span></strong></div>
        </section>

        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <button class="btn btn-primary">Guardar compra</button>
    </form>
</div></div>

<template id="linea-template">
    <div class="border-bottom pb-3 mb-4 linea">
        <div class="row g-2">
            <input type="hidden" class="insumo"><div class="col-lg-3 col-md-6"><label>Insumo y presentación</label><select class="form-select presentacion" required><option value="">Seleccionar</option>@foreach($insumos as $i)<optgroup label="{{ $i->nombre }}">@foreach($i->presentaciones as $p)<option value="{{ $p->id }}" data-insumo="{{ $i->id }}">{{ $i->nombre }} · {{ $p->nombre }}</option>@endforeach</optgroup>@endforeach</select><small class="base-info text-muted"></small></div>
            <div class="col-lg-3 col-md-6"><label>Formato exterior</label><select class="form-select formato-compra"><option value="">Sin formato específico</option>@foreach($formatos as $formato)<option value="{{ $formato->id }}" data-unidad="{{ $formato->unidad_medida_id }}" data-granel="{{ $formato->es_granel?1:0 }}">{{ $formato->nombre }}</option>@endforeach</select><small class="formato-info text-muted">Indica cómo viene esta compra.</small><button type="button" class="btn btn-sm btn-outline-secondary mt-2 agregar-nivel d-none"><i class="lni lni-plus"></i> Añadir contenido interno</button></div>
            <div class="col-lg-2 col-md-6"><label>Marca/empresa</label><select class="form-select marca"><option value="">Sin especificar</option>@foreach($marcas as $m)<option value="{{ $m->id }}">{{ $m->nombre }}</option>@endforeach</select></div>
            <div class="col-lg-2 col-md-4"><label>Cantidad comprada</label><input type="number" step="0.0001" min="0.0001" class="form-control cantidad" required></div>
            <div class="col-lg-2 col-md-4"><label>Unidad comprada</label><select class="form-select unidad-compra" required><option value="">Seleccionar</option>@foreach($unidades as $u)<option value="{{ $u->id }}">{{ $u->nombre }} ({{ $u->abreviatura }})</option>@endforeach</select></div>
            <div class="col-lg-6 col-md-8 equiv-compra-group d-none"><div class="row g-2"><div class="col-4"><label>Equivalencia de la medida</label><input type="number" step="0.0001" min="0.0001" class="form-control cantidad-contenido" placeholder="Cantidad equivalente"></div><div class="col-4"><label>Unidad equivalente</label><select class="form-select unidad-contenido"><option value="">Seleccionar</option>@foreach($unidades as $u)<option value="{{ $u->id }}">{{ $u->nombre }} ({{ $u->abreviatura }})</option>@endforeach</select></div><div class="col-4"><label>Cantidad suelta adicional</label><input type="number" step="0.0001" min="0" value="0" class="form-control cantidad-suelta"></div><small class="factor-compra-info text-muted">Solo se usa para una medida que no tenga conversión automática.</small><input type="hidden" class="factor-compra"></div></div>
            <div class="col-12 niveles-empaque"></div>
            <div class="col-lg-1 d-flex align-items-end"><button type="button" class="btn btn-danger quitar" title="Quitar"><i class="lni lni-trash-can"></i></button></div>
        </div>
        <div class="row g-2 mt-1">
            <div class="col-lg-4 col-md-6"><label>Precio de cada unidad comprada (Bs)</label><input type="number" step="0.0001" min="0" class="form-control precio" required><input type="hidden" class="unidad-precio"><input type="hidden" class="factor-precio"></div>
            <div class="col-lg-3"><label>Total de esta línea</label><input class="form-control total-linea" value="0.00" readonly><small class="calculo-info text-muted"></small></div>
        </div>
    </div>
</template>

@php
    $insumosData = $insumos->mapWithKeys(fn($i) => [$i->id => ['base_id'=>$i->unidad_medida_id,'base'=>$i->unidad_medida?->nombre,'abrev'=>$i->unidad_medida?->abreviatura]]);
    $conversionesData = \App\Models\ConversionesUnidades::all()->map(fn($c) => ['origen'=>(int)$c->unidad_origen_id,'destino'=>(int)$c->unidad_destino_id,'factor'=>(float)$c->factor_conversion])->values();
    $presentacionesData = $presentaciones->mapWithKeys(fn($p)=>[$p->id=>['stock'=>$p->unidad_stock_id,'stock_nombre'=>$p->unidadStock()?->nombre,'stock_abrev'=>$p->unidadStock()?->abreviatura]]);
    $formatosData = $formatos->map(fn($f)=>['id'=>$f->id,'nombre'=>$f->nombre,'unidad_id'=>$f->unidad_medida_id,'granel'=>$f->es_granel])->values();
    $unidadesData = $unidades->map(fn($u)=>['id'=>$u->id,'nombre'=>$u->nombre,'abreviatura'=>$u->abreviatura])->values();
@endphp
<script>
document.addEventListener('DOMContentLoaded', () => {
    const insumos = @json($insumosData);
    const conversiones = @json($conversionesData);
    const presentaciones = @json($presentacionesData);
    const formatos = @json($formatosData);
    const unidadesMedida = @json($unidadesData);
    const contenedor = document.getElementById('lineas');
    let indice = 0;

    function factorAutomatico(origen, destino) {
        origen = Number(origen); destino = Number(destino);
        if (origen === destino) return 1;
        const cola = [[origen, 1]], visitadas = new Set([origen]);
        while (cola.length) {
            const [actual, acumulado] = cola.shift();
            for (const c of conversiones) {
                let siguiente = null, paso = null;
                if (c.origen === actual) { siguiente = c.destino; paso = c.factor; }
                else if (c.destino === actual) { siguiente = c.origen; paso = 1 / c.factor; }
                if (siguiente === null || visitadas.has(siguiente)) continue;
                if (siguiente === destino) return acumulado * paso;
                visitadas.add(siguiente); cola.push([siguiente, acumulado * paso]);
            }
        }
        return null;
    }

    function configurarFactor(input, info, unidadId, baseId, baseAbrev, factorCompartido = null, tipo = 'compra') {
        const grupo = input.closest(tipo === 'compra' ? '.equiv-compra-group' : '.equiv-precio-group');
        const unidadTexto = input.closest('.linea').querySelector(tipo === 'compra' ? '.unidad-compra' : '.unidad-precio').selectedOptions[0]?.text.split(' (')[0] || 'empaque';
        const automatico = factorAutomatico(unidadId, baseId);
        const factor = automatico ?? factorCompartido;
        if (factor !== null && factor !== undefined) {
            input.value = Number(factor).toFixed(6);
            grupo.classList.add('d-none');
        } else {
            grupo.classList.add('d-none');
            input.value = '';
        }
    }

    function recalcular(fila) {
        const insumo = insumos[fila.querySelector('.insumo').value];
        if (!insumo) return;
        const configuracion = presentaciones[fila.querySelector('.presentacion').value];
        const baseId = configuracion?.stock || insumo.base_id;
        const baseAbrev = configuracion?.stock_abrev || insumo.abrev;
        const unidadCompra = fila.querySelector('.unidad-compra').value;
        const unidadPrecio = unidadCompra;
        fila.querySelector('.unidad-precio').value = unidadCompra;
        const factorCompraInput = fila.querySelector('.factor-compra');
        const factorPrecioInput = fila.querySelector('.factor-precio');
        const formatoSeleccionado=fila.querySelector('.formato-compra');
        const usaEmpaque=formatoSeleccionado.value&&formatoSeleccionado.selectedOptions[0]?.dataset.granel!=='1';
        const cantidadesInternas=[...fila.querySelectorAll('.nivel-cantidad')].map(input=>Number(input.value)||0);
        const tieneNiveles=cantidadesInternas.length>0&&cantidadesInternas.every(cantidad=>cantidad>0);
        if(tieneNiveles){const factor=cantidadesInternas.reduce((total,cantidad)=>total*cantidad,1);factorCompraInput.value=factor.toFixed(6);fila.querySelector('.cantidad-contenido').value=factor;fila.querySelector('.unidad-contenido').value=baseId;fila.querySelector('.equiv-compra-group').classList.add('d-none');}
        else if(usaEmpaque){factorCompraInput.value='1.000000';fila.querySelector('.cantidad-contenido').value='';fila.querySelector('.unidad-contenido').value='';fila.querySelector('.equiv-compra-group').classList.add('d-none');}
        else configurarFactor(factorCompraInput, fila.querySelector('.factor-compra-info'), unidadCompra, baseId, baseAbrev);
        const compartir = Number(unidadPrecio) === Number(unidadCompra) ? Number(factorCompraInput.value) || null : null;
        factorPrecioInput.value = factorCompraInput.value;

        const cantidadContenido = Number(fila.querySelector('.cantidad-contenido').value) || 0;
        const cantidadSuelta = Number(fila.querySelector('.cantidad-suelta').value) || 0;
        const unidadContenido = fila.querySelector('.unidad-contenido').value;
        if (!factorCompraInput.value && cantidadContenido > 0 && unidadContenido) {
            const conversionContenido = factorAutomatico(unidadContenido, baseId);
            factorCompraInput.value = (cantidadContenido * (conversionContenido ?? 1)).toFixed(6);
            factorPrecioInput.value = factorCompraInput.value;
        }

        const cantidad = Number(fila.querySelector('.cantidad').value) || 0;
        const precio = Number(fila.querySelector('.precio').value) || 0;
        const factorCompra = Number(factorCompraInput.value) || 1;
        const base = (cantidad * factorCompra) + cantidadSuelta;
        const total = cantidad * precio;
        fila.querySelector('.total-linea').value = total.toFixed(2);
        fila.querySelector('.calculo-info').textContent = base > 0 ? base.toFixed(4) + ' ' + baseAbrev + ' entrarán al inventario.' : '';
        actualizarTotal();
    }

    function actualizarTotal() {
        const total = [...document.querySelectorAll('.total-linea')].reduce((s, input) => s + (Number(input.value) || 0), 0);
        document.getElementById('total-compra').textContent = total.toFixed(2);
    }

    function renumerarNiveles(fila,numeroLinea){[...fila.querySelectorAll('.nivel-empaque')].forEach((bloque,nivel)=>{bloque.querySelector('.nivel-cantidad').name=`lineas[${numeroLinea}][estructura_empaque][${nivel}][cantidad]`;bloque.querySelector('.nivel-formato').name=`lineas[${numeroLinea}][estructura_empaque][${nivel}][formato_empaque_id]`;bloque.querySelector('.nivel-contenido').name=`lineas[${numeroLinea}][estructura_empaque][${nivel}][contenido]`;bloque.querySelector('.nivel-unidad').name=`lineas[${numeroLinea}][estructura_empaque][${nivel}][unidad_medida_id]`;});}

    function agregar() {
        const fragmento = document.getElementById('linea-template').content.cloneNode(true);
        const fila = fragmento.querySelector('.linea');
        const numeroLinea=indice;
        const campos = {'insumo':'insumo_id','presentacion':'presentacion_id','formato-compra':'formato_empaque_id','marca':'marca_id','cantidad':'cantidad_pedida','unidad-compra':'unidad_medida_id','factor-compra':'factor_compra_base','cantidad-contenido':'cantidad_contenido','cantidad-suelta':'cantidad_suelta','unidad-contenido':'unidad_contenido_id','precio':'precio_unitario','unidad-precio':'unidad_precio_id','factor-precio':'factor_precio_base'};
        Object.entries(campos).forEach(([clase,nombre]) => fila.querySelector('.'+clase).name = `lineas[${indice}][${nombre}]`);
        fila.querySelector('.insumo').addEventListener('change', () => {
            const dato = insumos[fila.querySelector('.insumo').value];
            if (dato) {
                fila.querySelector('.base-info').textContent = 'Inventario en ' + dato.base + ' (' + dato.abrev + ')';
                fila.querySelector('.unidad-compra').value = dato.base_id;
                fila.querySelector('.unidad-precio').value = dato.base_id;
            }
            recalcular(fila);
        });
        fila.querySelector('.presentacion').addEventListener('change', () => {
            const opcion=fila.querySelector('.presentacion').selectedOptions[0];
            fila.querySelector('.insumo').value=opcion?.dataset.insumo||'';
            fila.querySelector('.insumo').dispatchEvent(new Event('change'));
            const configuracion=presentaciones[fila.querySelector('.presentacion').value];
            if(configuracion?.stock){fila.querySelector('.unidad-compra').value=configuracion.stock;fila.querySelector('.unidad-contenido').value=configuracion.stock;}
            recalcular(fila);
        });
        fila.querySelector('.formato-compra').addEventListener('change',()=>{
            const opcion=fila.querySelector('.formato-compra').selectedOptions[0];const configuracion=presentaciones[fila.querySelector('.presentacion').value];
            const esGranel=opcion?.dataset.granel==='1';const permiteContenido=Boolean(opcion?.value)&&!esGranel;fila.querySelector('.agregar-nivel').classList.toggle('d-none',!permiteContenido);if(!permiteContenido)fila.querySelector('.niveles-empaque').innerHTML='';
            fila.querySelector('.formato-info').textContent=esGranel?'Producto comprado sin empaque fijo.':(permiteContenido?'Puedes añadir lo que contiene este formato solamente para esta compra.':'Indica cómo viene esta compra.');
            recalcular(fila);
        });
        fila.querySelector('.agregar-nivel').addEventListener('click',()=>{
            const contenedorNiveles=fila.querySelector('.niveles-empaque');const nivel=contenedorNiveles.children.length;if(nivel>=3)return;
            const opciones=formatos.filter(f=>!f.granel).map(f=>`<option value="${f.id}">${f.nombre}</option>`).join('');
            const opcionesUnidades=unidadesMedida.map(u=>`<option value="${u.id}">${u.nombre} (${u.abreviatura})</option>`).join('');
            const bloque=document.createElement('div');bloque.className='row g-2 mt-2 align-items-end nivel-empaque';bloque.innerHTML=`<div class="col-md-2"><label class="form-label">Cantidad interior</label><input type="number" min="0.0001" step="0.0001" class="form-control nivel-cantidad" name="lineas[${numeroLinea}][estructura_empaque][${nivel}][cantidad]" required></div><div class="col-md-3"><label class="form-label">Formato interior</label><select class="form-select nivel-formato" name="lineas[${numeroLinea}][estructura_empaque][${nivel}][formato_empaque_id]" required><option value="">Seleccionar</option>${opciones}</select></div><div class="col-md-2"><label class="form-label">Contenido de cada uno</label><input type="number" min="0.0001" step="0.0001" class="form-control nivel-contenido" name="lineas[${numeroLinea}][estructura_empaque][${nivel}][contenido]" placeholder="Opcional"></div><div class="col-md-3"><label class="form-label">Unidad de medida interna</label><select class="form-select nivel-unidad" name="lineas[${numeroLinea}][estructura_empaque][${nivel}][unidad_medida_id]"><option value="">Seleccionar</option>${opcionesUnidades}</select></div><div class="col-md-1"><button type="button" class="btn btn-danger btn-sm quitar-nivel"><i class="lni lni-trash-can"></i></button></div>`;
            bloque.querySelector('.quitar-nivel').addEventListener('click',()=>{bloque.remove();renumerarNiveles(fila,numeroLinea);recalcular(fila)});bloque.querySelectorAll('input,select').forEach(el=>el.addEventListener('input',()=>recalcular(fila)));contenedorNiveles.appendChild(bloque);recalcular(fila);
        });
        fila.querySelector('.unidad-compra').addEventListener('change', () => {
            fila.querySelector('.unidad-precio').value = fila.querySelector('.unidad-compra').value;
            recalcular(fila);
        });
        fila.querySelectorAll('input,select').forEach(el => el.addEventListener('input', () => recalcular(fila)));
        fila.querySelector('.quitar').addEventListener('click', () => { fila.remove(); actualizarTotal(); });
        contenedor.appendChild(fragmento); indice++;
    }

    document.getElementById('agregar-linea').addEventListener('click', agregar);
    agregar();
});
</script>
@endsection
