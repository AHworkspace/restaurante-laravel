@php
    $reporteSector = $sector ?? '';
    $reporteTitulo = $titulo ?? 'Guardar reporte filtrado';
    $reporteFiltros = collect(request()->except('page'))->filter(fn ($valor) => $valor !== null && $valor !== '' && $valor !== 'todos');
    $reporteId = 'guardar-reporte-' . $reporteSector;
@endphp

@if($reporteFiltros->isNotEmpty())
<div class="card mt-3" id="{{ $reporteId }}">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <strong>{{ $reporteTitulo }}</strong>
            <div class="text-muted small">
                Seleccionados: <span class="reporte-selected-count">0</span>
                <span class="ms-3">Total seleccionado: Bs. <span class="reporte-selected-total">0.00</span></span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <button type="button" class="btn btn-outline-primary reporte-select-all">Seleccionar todos</button>
            <input type="text" class="form-control reporte-nombre" style="min-width:260px" placeholder="Nombre del reporte (opcional)">
            <button type="button" class="btn btn-primary reporte-guardar-btn">
                <i class="lni lni-save"></i> Guardar reporte
            </button>
        </div>
        <div class="w-100">
            <small class="text-muted">Si seleccionas filas, se guarda solo esa selección. Si no seleccionas nada, se guarda todo lo que coincide con los filtros actuales.</small>
        </div>
        <small class="reporte-mensaje d-block w-100"></small>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById(@json($reporteId));
    if (!wrapper || wrapper.dataset.ready === '1') return;
    wrapper.dataset.ready = '1';

    const btn = wrapper.querySelector('.reporte-guardar-btn');
    const selectAllBtn = wrapper.querySelector('.reporte-select-all');
    const nameInput = wrapper.querySelector('.reporte-nombre');
    const message = wrapper.querySelector('.reporte-mensaje');
    const countEl = wrapper.querySelector('.reporte-selected-count');
    const totalEl = wrapper.querySelector('.reporte-selected-total');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const checkboxes = Array.from(document.querySelectorAll('.reporte-row-checkbox[data-sector="' + @json($reporteSector) + '"]'));

    function selectedRows() {
        return checkboxes.filter(cb => cb.checked);
    }

    function updateSelection() {
        const selected = selectedRows();
        const total = selected.reduce((acc, cb) => acc + (parseFloat(cb.dataset.total || 0) || 0), 0);
        countEl.textContent = selected.length;
        totalEl.textContent = total.toFixed(2);
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateSelection));

    selectAllBtn.addEventListener('click', function () {
        const mark = selectedRows().length !== checkboxes.length;
        checkboxes.forEach(cb => { cb.checked = mark; });
        updateSelection();
    });

    document.querySelectorAll('.reporte-select-all-top[data-target-sector="' + @json($reporteSector) + '"]').forEach(button => {
        button.addEventListener('click', function () {
            const mark = selectedRows().length !== checkboxes.length;
            checkboxes.forEach(cb => { cb.checked = mark; });
            updateSelection();
        });
    });

    btn.addEventListener('click', async function () {
        const selected = selectedRows();
        btn.disabled = true;
        message.className = 'reporte-mensaje d-block mt-2 text-muted';
        message.textContent = 'Guardando reporte...';

        try {
            const response = await fetch(@json(route('reportes.guardar-sector')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    sector: @json($reporteSector),
                    nombre: nameInput.value,
                    filtros: @json($reporteFiltros),
                    ids: selected.map(cb => parseInt(cb.value, 10)),
                }),
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'No se pudo guardar el reporte.');
            message.className = 'reporte-mensaje d-block mt-2 text-success';
            message.textContent = 'Reporte guardado correctamente. Puedes verlo en Reportes y estadísticas.';
            nameInput.value = '';
            checkboxes.forEach(cb => { cb.checked = false; });
            updateSelection();
        } catch (error) {
            message.className = 'reporte-mensaje d-block mt-2 text-danger';
            message.textContent = error.message;
        } finally {
            btn.disabled = false;
        }
    });
});
</script>
@endpush
@endif
