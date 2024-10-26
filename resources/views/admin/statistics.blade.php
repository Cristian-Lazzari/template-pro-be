@extends('layouts.base')

@section('contents')
@vite(['resources/js/app.js'])

{{-- <script src="{{ mix('js/app.js') }}"></script> --}}

    <div class="container">
        <h2>Statistiche</h2>

        <h3>Prodotti più ordinati</h3>
        <div class="chart">
            <canvas id="topProductsChart"></canvas>
        </div>

        <h3>Ordinazioni nel tempo</h3>
        <div class="chart">
            <canvas id="ordersOverTimeChart"></canvas>
        </div>

        <h3>Ricavi nel tempo</h3>
        <div class="chart">
            <canvas id="revenueOverTimeChart"></canvas>
        </div>
    </div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        // Grafico a torta per i prodotti più ordinati
        const topProductsData = @json($topProducts);
        new Chart(document.getElementById('topProductsChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: Object.keys(topProductsData),
                datasets: [{
                    label: 'Quantità ordinata',
                    data: Object.values(topProductsData),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                }]
            },
            options: {
                responsive: true,
            }
        });

        // Grafico a colonne per le ordinazioni nel tempo
        const ordersOverTime = @json($ordersOverTime);
        const columnsLabels = [...new Set(ordersOverTime.map(item => item.month))];
        const products = [...new Set(ordersOverTime.map(item => item.name))];
        const productDatasets = products.map(product => {
            return {
                label: product,
                data: columnsLabels.map(month => {
                    const item = ordersOverTime.find(e => e.month === month && e.name === product);
                    return item ? item.quantity : 0;
                }),
                backgroundColor: '#' + Math.floor(Math.random()*16777215).toString(16),
            };
        });
        new Chart(document.getElementById('ordersOverTimeChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: columnsLabels,
                datasets: productDatasets,
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Grafico a linee per i ricavi nel tempo
        const revenueOverTime = @json($revenueOverTime);
        new Chart(document.getElementById('revenueOverTimeChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: Object.keys(revenueOverTime),
                datasets: [{
                    label: 'Ricavi totali',
                    data: Object.values(revenueOverTime),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
