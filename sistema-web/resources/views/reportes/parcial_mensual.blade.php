<div class="alert alert-info">Reporte Mensual: Aquí puedes mostrar el gráfico y los datos del mes seleccionado.</div>
<div>
    <canvas id="graficoMensual" width="400" height="200"></canvas>
    <script>
        setTimeout(function() {
            if (window.Chart) {
                new Chart(document.getElementById('graficoMensual').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
                        datasets: [{
                            label: 'Ventas',
                            data: [120, 190, 300, 500, 200, 300],
                            borderColor: 'rgba(90, 40, 40, 0.8)',
                            backgroundColor: 'rgba(90, 40, 40, 0.2)',
                            fill: true
                        }]
                    }
                });
            }
        }, 500);
    </script>
</div> 