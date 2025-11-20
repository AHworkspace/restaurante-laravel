<div class="alert alert-info">Reporte Semanal: Aquí puedes mostrar el gráfico y los datos de la semana seleccionada.</div>
<div>
    <canvas id="graficoSemanal" width="400" height="200"></canvas>
    <script>
        // Ejemplo de gráfico simple para el parcial semanal
        setTimeout(function() {
            if (window.Chart) {
                new Chart(document.getElementById('graficoSemanal').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                        datasets: [{
                            label: 'Ventas',
                            data: [12, 19, 3, 5, 2, 3, 7],
                            backgroundColor: 'rgba(90, 40, 40, 0.5)'
                        }]
                    }
                });
            }
        }, 500);
    </script>
</div> 