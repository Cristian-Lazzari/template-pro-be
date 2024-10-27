
@extends('layouts.base')

@section('contents')
@vite(['resources/js/app.js'])

    <div class="stat">
        <h1>Statistiche</h1>

        <h3>Prodotti più ordinati</h3>
        <div class="chart">
            <canvas id="topProductsChart"></canvas>
            <div class="list bg-light">
                <table class="mytable table table-striped "> 
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Prodotto</th>
                            <th scope="col">Quantità</th>
                        </tr>
                    </thead>
                    @php $index = 1 @endphp
                    @foreach ($topProducts as $key => $value)
                    <tr>
                        <td>{{$index}}</td>
                        <td>{{$key}}:</td>
                        <td><strong>{{$value}}</strong></td>
                    </tr>
                    @php $index ++ @endphp
                    @endforeach
                </table>
            </div>
        </div>

        <h3>Ordinazioni nel tempo</h3>
        <div class="chart">
            <canvas id="ordersOverTimeChart"></canvas>
        </div>

        <h3>Ricavi nel tempo</h3>
        <div class="chart">
            <canvas id="revenueOverTimeChart"></canvas>
            <div class="list bg-light">
                <table class="mytable table table-striped "> 
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Data</th>
                            <th scope="col">Fatturato dal sito con ordini</th>
                        </tr>
                    </thead>
                    @php $index = 1 @endphp
                    @foreach ($revenueOverTime as $key => $value)
                    <tr>
                        <td>{{$index}}</td>
                        <td>{{$key}}</td>
                        <td><strong>€{{$value / 100}}</strong></td>
                    </tr>
                    @php $index ++ @endphp
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#66FF66', '#FF6633', '#0099FF', '#CC33FF',
            '#FF6699', '#66CCFF', '#CCFF33', '#FF9933', '#CC66CC',
            '#FFCC66', '#3366FF', '#33CC99', '#FF6666', '#66FFCC'
        ];

        // Grafico a torta per i prodotti più ordinati
        const topProductsData = @json($topProducts);
        new Chart(document.getElementById('topProductsChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: Object.keys(topProductsData),
                datasets: [{
                    data: Object.values(topProductsData),
                    backgroundColor: colors,
                }]
            },
            options: {
                responsive: true,
            }
        });

        // Grafico a colonne per le ordinazioni nel tempo
        const ordersOverTime = @json($ordersOverTime);
        const columnsLabels = [...new Set(ordersOverTime.map(item => item.day))]; // Usa il giorno come label
        const products = [...new Set(ordersOverTime.map(item => item.name))];

        let i = -1;
        const productDatasets = products.map(product => {
            i++;
            return {
                label: product,
                data: columnsLabels.map(day => {
                    const item = ordersOverTime.find(e => e.day === day && e.name === product);
                    return item ? item.quantity : 0;
                }),
                backgroundColor: colors[i],
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
        const revenueInEuros = Object.values(revenueOverTime).map(value => value / 100);
        new Chart(document.getElementById('revenueOverTimeChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: Object.keys(revenueOverTime),
                datasets: [{
                    label: 'Ricavi totali',
                    data: revenueInEuros, // Usa i valori convertiti in euro
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
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
