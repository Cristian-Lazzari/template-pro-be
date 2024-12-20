
@extends('layouts.base')

@section('contents')
@vite(['resources/js/app.js'])

    <div class="stat">
        <h1>Statistiche</h1>

        <h2>Prodotti più ordinati</h2>
        <div class="chart">
            <canvas class="graph" id="topProductsChart"></canvas>
            <div class="list">
                <table class=" table table mytable"> 
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
                        <td>{{$key}}</td>
                        <td><strong>{{$value}}</strong></td>
                    </tr>
                    @php $index ++ @endphp
                    @endforeach
                </table>
            </div>
        </div>

        <h2>Come vengono ordinati i prodotti nel tempo</h2>
        <div class="chart">
            <canvas class="graph" id="ordersOverTimeChart"></canvas>
            <div class="list">
                <table class="mytable table table-striped "> 
                    <thead>
                        <tr>
                            <th scope="col">Prodotto</th>
                            <th scope="col">Data</th>
                            <th scope="col">Quantità</th>
                        </tr>
                    </thead>
                    @foreach ($ordersOverTime as $key => $value)
                    <tr>
                        <td>{{$value->name}}</td>
                        <td>{{$value->day}}</td>
                        <td>{{$value->quantity}}</td>
                       
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <h2>Ricavi nel tempo</h2>
        <div class="chart">
            <canvas class="graph" id="revenueOverTimeChart"></canvas>
            <div class="list">
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
        <h2>Prenotazioni ai tavoli nel tempo</h2>
        <div class="chart">
            <canvas class="graph" id="reservationChart"></canvas>

            <div class="list">
                <table class="mytable table table-striped "> 
                    <thead>
                        <tr>
                            
                            <th scope="col">Data</th>
                            <th scope="col">Adulti</th>
                            <th scope="col">Bambini</th>
                        </tr>
                    </thead>
                    @foreach ($reservations as $key => $value)
                    <tr>
                        
                        <td>{{$value->date}}</td>
                        <td>{{$value->adults}}</td>
                        <td>{{$value->children}}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
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
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Ricavi in €'
                        },
                    },
                }
            }
        });
      
        const data = @json($reservations);

            // Estrai le date, adulti e bambini
            const labels = data.map(item => item.date); // Array di date (formato ISO)
            const adults = data.map(item => item.adults);
            const children = data.map(item => item.children);

            // Configurazione del grafico
            const ctx = document.getElementById('reservationChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Adulti',
                            data: adults,
                            stack: 'stack',
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Bambini',
                            data: children,
                            stack: 'stack',
                            backgroundColor: 'rgba(255, 99, 132, 0.6)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            type: 'time', // Configura l'asse X come asse temporale
                            time: {
                                unit: 'day', // Mostra un intervallo basato sui giorni
                                tooltipFormat: 'dd-MM-yyyy', // Formato per il tooltip
                                displayFormats: {
                                    day: 'MM-dd' // Formato per le etichette
                                },
                            },
                            title: {
                                display: true,
                                text: 'Data'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Numero di persone'
                            },
                            stacked: true // Abilita lo stacking
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Prenotazioni giornaliere'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                }
            });
    });
</script>


