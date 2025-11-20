<div class="alert alert-info">Reporte Anual: Aquí puedes mostrar el gráfico y los datos del año seleccionado.</div>
<div>
    <canvas id="graficoAnual" width="400" height="200"></canvas>
    <script>
        setTimeout(function() {
            if (window.Chart) {
                new Chart(document.getElementById('graficoAnual').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['2019', '2020', '2021', '2022', '2023', '2024'],
                        datasets: [{
                            label: 'Ventas',
                            data: [1200, 1500, 1800, 2000, 1700, 2100],
                            backgroundColor: 'rgba(90, 40, 40, 0.5)'
                        }]
                    }
                });
            }
        }, 500);
    </script>
</div> 