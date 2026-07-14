@extends('layouts.app')
@php
use App\Models\ConfiguracionSistema as C;
$colores=[
'color_primario'=>['Principal','#7A5C58'],'color_secundario'=>['Secundario','#CDBEAC'],
'color_fondo'=>['Fondo general','#F5F1EC'],'color_sidebar'=>['Barra lateral','#3F3B3A'],
'color_superficie'=>['Tarjetas y paneles','#FFFFFF'],'color_encabezado'=>['Encabezado','#3F3B3A'],
'color_texto'=>['Texto principal','#2F2B27'],'color_texto_secundario'=>['Texto secundario','#8A8078'],
'color_borde'=>['Bordes','#D8D0C8'],'color_tabla_cabecera'=>['Cabecera de tablas','#E7DED3'],
'color_entrada'=>['Campos de formulario','#FFFFFF'],'color_exito'=>['Estado exitoso','#4F9D7A'],
'color_peligro'=>['Estado de peligro','#D26A6A']];
$fuentes=['Arial','Verdana','Tahoma','Georgia','Trebuchet MS'];
@endphp
@section('content')
<div class="title-wrapper pt-30"><div class="title mb-30"><h2>Configuraciones - Diseño</h2></div></div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<form method="POST" action="{{ route('configuraciones.diseno.update') }}" enctype="multipart/form-data" id="form-diseno">@csrf @method('PUT')
<div class="card-style-3 mb-30"><div class="card-content">
<ul class="nav nav-tabs mb-4" role="tablist">
<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#identidad" type="button">Identidad</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#colores" type="button">Colores</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#componentes" type="button">Componentes</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#acceso" type="button">Acceso</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#vista-previa" type="button">Vista previa</button></li>
</ul>

<div class="tab-content">
<div class="tab-pane fade show active" id="identidad"><h4 class="mb-3">Identidad e información</h4><div class="row">
<div class="col-md-6 mb-3"><label>Nombre del restaurante</label><input class="form-control" name="nombre_restaurante" value="{{ old('nombre_restaurante',C::valor('nombre_restaurante','Las Brazas')) }}" required></div>
<div class="col-md-3 mb-3"><label>Teléfono</label><input class="form-control" name="telefono_restaurante" value="{{ old('telefono_restaurante',C::valor('telefono_restaurante')) }}"></div>
<div class="col-md-3 mb-3"><label>Dirección</label><input class="form-control" name="direccion_restaurante" value="{{ old('direccion_restaurante',C::valor('direccion_restaurante')) }}"></div>
<div class="col-12 mb-3"><label>Descripción pública</label><textarea class="form-control" name="descripcion_restaurante" rows="3">{{ old('descripcion_restaurante',C::valor('descripcion_restaurante')) }}</textarea></div>
@foreach(['logo'=>'Logotipo','favicon'=>'Favicon','imagen_portada'=>'Imagen de portada','imagen_portal_cliente'=>'Fondo del portal del cliente'] as $campo=>$texto)
<div class="col-md-3 mb-3"><label>{{ $texto }}</label>@if(C::valor($campo))<img src="{{ Storage::url(C::valor($campo)) }}" alt="{{ $texto }}" class="d-block mb-2" style="width:100%;height:90px;object-fit:contain;border:1px solid #ddd">@endif<input type="file" class="form-control" name="{{ $campo }}" accept="image/*"></div>
@endforeach
</div></div>

<div class="tab-pane fade" id="colores"><div class="row"><div class="col-md-4 mb-3"><label>Tema</label><select class="form-select preview-control" name="tema"><option value="claro" @selected(C::valor('tema','personalizado')==='claro')>Claro</option><option value="oscuro" @selected(C::valor('tema')==='oscuro')>Oscuro</option><option value="personalizado" @selected(C::valor('tema','personalizado')==='personalizado')>Personalizado</option></select></div><div class="col-md-4 mb-3"><label>Contraste</label><select class="form-select preview-control" name="contraste"><option value="normal" @selected(C::valor('contraste','normal')==='normal')>Normal</option><option value="alto" @selected(C::valor('contraste')==='alto')>Alto</option></select></div></div><div class="row">@foreach($colores as $campo=>$config)<div class="col-lg-3 col-md-4 col-6 mb-3"><label>{{ $config[0] }}</label><input type="color" class="form-control form-control-color w-100 preview-color" name="{{ $campo }}" value="{{ old($campo,C::valor($campo,$config[1])) }}" data-variable="--preview-{{ Str::after($campo,'color_') }}"></div>@endforeach</div></div>

<div class="tab-pane fade" id="componentes"><div class="row">
<div class="col-md-4 mb-3"><label>Tipografía general</label><select class="form-select preview-control" name="tipografia">@foreach($fuentes as $fuente)<option @selected(C::valor('tipografia','Arial')===$fuente)>{{ $fuente }}</option>@endforeach</select></div>
<div class="col-md-4 mb-3"><label>Tipografía de títulos</label><select class="form-select preview-control" name="tipografia_titulos">@foreach($fuentes as $fuente)<option @selected(C::valor('tipografia_titulos','Arial')===$fuente)>{{ $fuente }}</option>@endforeach</select></div>
<div class="col-md-4 mb-3"><label>Tamaño de texto</label><input type="number" min="14" max="18" class="form-control preview-control" name="tamano_texto" value="{{ C::valor('tamano_texto',16) }}"></div>
<div class="col-md-3 mb-3"><label>Radio de bordes</label><input type="number" min="0" max="16" class="form-control preview-control" name="radio_bordes" value="{{ C::valor('radio_bordes',8) }}"></div>
<div class="col-md-3 mb-3"><label>Densidad</label><select class="form-select" name="densidad"><option value="compacta" @selected(C::valor('densidad')==='compacta')>Compacta</option><option value="normal" @selected(C::valor('densidad','normal')==='normal')>Normal</option><option value="comoda" @selected(C::valor('densidad')==='comoda')>Cómoda</option></select></div>
<div class="col-md-3 mb-3"><label>Sombras</label><select class="form-select preview-control" name="sombra"><option value="ninguna" @selected(C::valor('sombra')==='ninguna')>Ninguna</option><option value="suave" @selected(C::valor('sombra','suave')==='suave')>Suave</option><option value="media" @selected(C::valor('sombra')==='media')>Media</option></select></div>
<div class="col-md-3 mb-3"><label>Botones</label><select class="form-select preview-control" name="estilo_botones"><option value="solido" @selected(C::valor('estilo_botones','solido')==='solido')>Sólidos</option><option value="contorno" @selected(C::valor('estilo_botones')==='contorno')>Contorno</option></select></div>
</div></div>

<div class="tab-pane fade" id="acceso"><h4 class="mb-3">Inicio de sesión</h4><div class="row"><div class="col-md-4 mb-3"><label>Imagen de fondo</label>@if(C::valor('imagen_login'))<img src="{{ Storage::url(C::valor('imagen_login')) }}" alt="Fondo de acceso" class="d-block mb-2" style="width:100%;height:100px;object-fit:cover">@endif<input type="file" class="form-control" name="imagen_login" accept="image/*"></div><div class="col-md-4 mb-3"><label>Posición del formulario</label><select class="form-select" name="posicion_login"><option value="izquierda" @selected(C::valor('posicion_login')==='izquierda')>Izquierda</option><option value="centro" @selected(C::valor('posicion_login','centro')==='centro')>Centro</option><option value="derecha" @selected(C::valor('posicion_login')==='derecha')>Derecha</option></select></div><div class="col-md-4 mb-3"><label>Oscurecimiento del fondo (%)</label><input type="number" min="0" max="90" class="form-control" name="opacidad_login" value="{{ C::valor('opacidad_login',0) }}"></div></div></div>

<div class="tab-pane fade" id="vista-previa"><div id="preview-diseno" style="background:var(--preview-fondo);color:var(--preview-texto);font-family:Arial;padding:20px;border:1px solid var(--preview-borde);border-radius:8px"><div style="background:var(--preview-encabezado);color:white;padding:12px;border-radius:6px;margin-bottom:16px">Encabezado del sistema</div><h3 style="color:var(--preview-primario)">Título de ejemplo</h3><div style="background:var(--preview-superficie);border:1px solid var(--preview-borde);padding:16px;margin:12px 0"><p>Contenido de una tarjeta y texto secundario.</p><input class="form-control mb-3" value="Campo de formulario"><button type="button" class="btn" id="preview-boton">Acción principal</button></div><table class="table"><thead><tr><th>Producto</th><th>Estado</th></tr></thead><tbody><tr><td>Ejemplo</td><td><span class="badge bg-success">Activo</span></td></tr></tbody></table></div></div>
</div>
</div></div>
<button class="main-btn primary-btn btn-hover" type="submit">Guardar diseño</button>
</form>
<form method="POST" action="{{ route('configuraciones.diseno.reset') }}" class="d-inline" onsubmit="return confirm('¿Restablecer el diseño original?')">@csrf @method('DELETE')<button class="main-btn danger-btn btn-hover" type="submit">Restablecer diseño</button></form>

<script>
function actualizarVistaPrevia(){var p=document.getElementById('preview-diseno');document.querySelectorAll('.preview-color').forEach(function(i){p.style.setProperty(i.dataset.variable,i.value);});p.style.fontFamily=document.querySelector('[name="tipografia"]').value;p.style.fontSize=document.querySelector('[name="tamano_texto"]').value+'px';p.style.borderRadius=document.querySelector('[name="radio_bordes"]').value+'px';var b=document.getElementById('preview-boton'),c=document.querySelector('[name="color_primario"]').value;b.style.background=document.querySelector('[name="estilo_botones"]').value==='contorno'?'transparent':c;b.style.border='1px solid '+c;b.style.color=document.querySelector('[name="estilo_botones"]').value==='contorno'?c:'#fff';}
document.querySelectorAll('.preview-color,.preview-control').forEach(function(i){i.addEventListener('input',actualizarVistaPrevia);i.addEventListener('change',actualizarVistaPrevia);});actualizarVistaPrevia();
</script>
@endsection

