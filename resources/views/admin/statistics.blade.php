
@extends('layouts.base')

@section('contents')
@vite(['resources/js/app.js'])

    <div class="dash_page statistic_page">
        <h1>
            <i class="bi bi-bar-chart-line-fill" style="font-size: 22px"></i> Statistiche </h1>

        @if($order_count)
            <h2>{{ __('admin.Prodotti_pi_ordinati') }}</h2>
            <div class="chart">
                <canvas class="graph" id="topProductsChart"></canvas>
                <div class="list">
                    <table class=" table table mytable"> 
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">{{ __('admin.Prodotto') }}</th>
                                <th scope="col">{{ __('admin.Quantit') }}</th>
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

            <h2>{{ __('admin.Come_vengono_ordinati_i_prodotti_nel_tempo') }}</h2>
            <div class="chart">
                <canvas class="graph" id="ordersOverTimeChart"></canvas>
            </div>

            <h2>{{ __('admin.Ricavi_nel_tempo_da_ordini') }}</h2>
            <div class="chart">
                <canvas class="graph" id="revenueOverTimeChart"></canvas>
                <div class="int_stat">
                    <div class="line">
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Ordini') }}</span>
                            <span class="count">
                                {{$order_count}}
                            </span>
                        </div>
                        <div class="st ok">
                            <span class="label">
                                <i class="ok bi bi-check-circle-fill"></i>
                            </span>
                            <div class="donut-wrapper" style="--percent: {{$or['confirm'] / $order_count * 100}}">
                                <p>
                                    {{$or['confirm']}}
                                </p>
                            </div>
                        </div>
                        <div class="st st_null">
                            <span class="label">
                                <i class="null bi bi-x-circle-fill"></i>
                            </span>
                            <div class="donut-wrapper" style="--percent: {{$or['cancelled'] / $order_count * 100}}">
                                <p>
                                    {{$or['cancelled']}}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="int_stat media">
                    <div class="line">
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Mesi_di_attivit') }}</span>
                            <span class="count">
                                {{ $mesi_o }}
                            </span>
                        </div>
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Media_ordini') }}</span>
                            <span class="count">
                                {{ round(($order_count / $mesi_o), 1) }}
                            </span>
                        </div>
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Media_incassi') }}</span>
                            <span class="count">
                               € {{ round((($or_cash['confirmed'] + $or_cash['cancelled']) / $mesi_o), 2) }}
                            </span>
                        </div>
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Media_incassi_confermati') }}</span>
                            <span class="count">
                               € {{round(($or_cash['confirmed'] / $mesi_o), 2)}}
                            </span>
                        </div>
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Ordine_medio') }}</span>
                            <span class="count">
                               € {{ round((($or_cash['confirmed'] + $or_cash['cancelled']) / $order_count), 2) }}
                            </span>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endif
        @if($res_count)
            <h2>{{ __('admin.Prenotazioni_ai_tavoli_nel_tempo') }}</h2>
            <div class="chart">
                <canvas class="graph" id="reservationChart"></canvas>
                <div class="int_stat">
                    <div class="line">
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Prenotazioni') }}</span>
                            <span class="count">
                                {{$res_count}}
                            </span>
                        </div>
                        <div class="st ok">
                            <span class="label">
                                <i class="bi bi-check-circle-fill"></i>
                            </span>
                            <div class="donut-wrapper" style="--percent: {{$res['confirm'] / $res_count * 100}}">
                                <p>
                                    {{$res['confirm']}}
                                </p>
                            </div>
                        </div>
                        <div class="st st_null">
                            <span class="label">
                                <i class="null bi bi-x-circle-fill"></i>
                            </span>
                            <div class="donut-wrapper" style="--percent: {{$res['cancelled'] / $res_count * 100}}">
                                <p>
                                    {{$res['cancelled']}}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="int_stat media">
                    <div class="line">
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Mesi_di_attivit') }}</span>
                            <span class="count">
                                {{ $mesi_r }}
                            </span>
                        </div>
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Media_prenotazioni') }}</span>
                            <span class="count">
                                {{  round(($res_count / $mesi_r), 1) }}
                            </span>
                        </div>
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Media_persone') }}</span>
                            <span class="count">
                                {{  round((($res_people['adults_confirmed'] + $res_people['adults_cancelled'] + $res_people['children_confirmed'] + $res_people['children_cancelled']) / $mesi_r), 1) }}
                            </span>
                        </div>
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Media_persone_confermate') }}</span>
                            <span class="count">
                                {{  round((($res_people['adults_confirmed'] + $res_people['children_cancelled']) / $mesi_r), 1) }}
                            </span>
                        </div>
                        <div class="st top_s">
                            <span class="label">{{ __('admin.Prenotazione_media') }}</span>
                            <span class="count">
                                {{  round((($res_people['adults_confirmed'] + $res_people['adults_cancelled'] + $res_people['children_confirmed'] + $res_people['children_cancelled']) / $res_count), 1) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="int_stat">
                    <div class="line">
                        <div class="st top_s">
                            <span class="label">
                                <i class="bi bi-person-standing"></i>{{ __('admin.Adulti') }}</span>
                            <span class="count">
                                {{ $res_people['adults_confirmed'] + $res_people['adults_cancelled'] }}
                            </span>
                        </div>
                        <div class="st ok">
                            <span class="label">
                                <i class="bi bi-check-circle-fill"></i>
                            </span>
                            <div class="donut-wrapper" style="--percent: {{ ($res_people['adults_confirmed'] + $res_people['children_confirmed'] ) / $res_count * 100}}">
                                <p>
                                    {{ $res_people['adults_confirmed'] }}
                                </p>
                            </div>
                        </div>
                        <div class="st st_null">
                            <span class="label">
                                <i class="null bi bi-x-circle-fill"></i>
                            </span>
                            <div class="donut-wrapper" style="--percent: {{ $res_people['adults_cancelled'] / $res_count * 100}}">
                                <p>
                                    {{ $res_people['adults_cancelled'] }}
                                </p>
                            </div>
                        </div>
                    </div>                    
                    <div class="line">
                        <div class="st top_s">
                            <span class="label">
                                <i class="bi bi-person-arms-up"></i>{{ __('admin.Bambini') }}</span>
                            <span class="count">
                                {{ $res_people['children_confirmed'] + $res_people['children_cancelled'] }}
                            </span>
                        </div>
                        <div class="st ok">
                            <span class="label">
                                <i class="bi bi-check-circle-fill"></i>
                            </span>
                            <div class="donut-wrapper" style="--percent: {{ $res_people['children_confirmed'] / $res_count * 100}}">
                                <p>
                                    {{ $res_people['children_confirmed'] }}
                                </p>
                            </div>
                        </div>
                        <div class="st st_null">
                            <span class="label">
                                <i class="bi bi-x-circle-fill"></i>
                            </span>
                            <div class="donut-wrapper" style="--percent: {{ $res_people['children_cancelled'] / $res_count * 100}}">
                                <p>
                                    {{ $res_people['children_cancelled'] }}
                                </p>
                            </div>
                        </div>      
                    </div>       
                </div>
            </div>
        @endif
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

        if(@json($order_count)){
            // Grafico a torta per i prodotti più ordinati
            const topProductsData = @json($topProducts);
            new Chart(document.getElementById('topProductsChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(topProductsData),
                    datasets: [{
                        data: Object.values(topProductsData),
                        backgroundColor: colors,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,  // utile per adattarsi meglio al mobile
                }
            });
            // grafico colonne prodotti nel tempo
            const labels = @json($labels);  // Assicurati che anche $labels venga passato correttamente
            const datasets = @json($datasets);  // Dati del grafico

            // Funzione per generare un array di colori ripetuto se necessario
            const getColors = (numberOfColorsNeeded, colors) => {
                const repeatedColors = [];
                for (let i = 0; i < numberOfColorsNeeded; i++) {
                    repeatedColors.push(colors[i % colors.length]);  // Usa l'operatore modulo per ripetere i colori
                }
                return repeatedColors;
            };

            // Ottieni un array di colori sufficienti per tutti i dataset
            const colorsForDatasets = getColors(datasets.length, colors);

            // Crea un nuovo array di datasets con i colori associati
            const datasetsWithColors = datasets.map((dataset, index) => {
                return {
                    label: dataset.label,  // Etichetta del dataset
                    data: dataset.data,    // I dati numerici
                    backgroundColor: colorsForDatasets[index]  // Colore corrispondente dal nuovo array di colori
                };
            });

            // Ora, puoi usare datasetsWithColors nel grafico
            const ctx_o_t = document.getElementById('ordersOverTimeChart').getContext('2d');

            const chart_o_t = new Chart(ctx_o_t, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: datasetsWithColors  // Usa il nuovo array con i colori associati
                },
                options: {
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                filter: (legendItem, chartData) => {
                                    const totals = chartData.datasets.map(dataset => ({
                                        label: dataset.label,
                                        total: dataset.data.reduce((sum, value) => sum + value, 0),
                                    }));
                                    totals.sort((a, b) => b.total - a.total);
                                    const top10 = totals.slice(0, 20).map(item => item.label);  // Cambiato 10 a 20 per visualizzare i primi 20
                                    return top10.includes(legendItem.text);
                                }
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,  // utile per adattarsi meglio al mobile
                    scales: {
                        x: {
                            type: 'time', // Configura l'asse X come asse temporale
                            time: {
                                unit: 'day', // Mostra un intervallo basato sui giorni
                            },
                            stacked: true
                        },
                        y: {
                            stacked: true
                        }
                    }
                }
            });


            // Grafico a linee per i ricavi nel tempo
            const revenueOverTime = @json($revenueOverTime);
            const chart_order = document.getElementById('revenueOverTimeChart').getContext('2d');
            const chart_or = new Chart(chart_order, {
                type: 'line',
                data: {
                datasets: [
                    {
                    label: "{{ __('admin.Totale')}}",
                    data: revenueOverTime.tot, // Dati per ordini pagati
                    borderColor: 'rgba(145, 220, 224, 1)',
                    backgroundColor: 'rgba(145, 220, 224, .2)',
                    fill: true,
                    },
                    {
                    label: "{{ __('admin.Pagati')}}",
                    data: revenueOverTime.paid, // Dati per ordini pagati
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    },
                    {
                    label: "{{ __('admin.Pagati_alla_consegna')}} ",
                    data: revenueOverTime.cod, // Dati per ordini pagati alla consegna
                    borderColor: 'rgba(255, 206, 86, 1)',
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    fill: true,
                    },
                    {
                    label: "{{ __('admin.Annullati')}}",
                    data: revenueOverTime.canceled, // Dati per ordini annullati
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true,
                    },
                ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,  // utile per adattarsi meglio al mobile
                    scales: {
                        x: {
                            type: 'time', // Asse temporale
                            time: {
                                unit: 'day', // Mostra i dati per giorno
                            },
                        },
                        y: {
                        title: {
                            display: true,
                            text: 'Totale (€)',
                        },
                        },
                    },
                },
            });

        }
        //ultimo grafico
        if(@json($res_count))
        {
        
            const data = @json($reservations);

            // Estrai le date, adulti e bambini
            const label = data.map(item => item.date); // Array di date (formato ISO)
            const adults = data.map(item => item.adults);
            const children = data.map(item => item.children);

            // Configurazione del grafico
            const ctx = document.getElementById('reservationChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: label,
                    datasets: [
                        {
                            label: "{{ __('admin.Adulti')}}",
                            data: adults,
                            stack: 'stack',
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: "{{ __('admin.Bambini')}}",
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
                    maintainAspectRatio: false,  // utile per adattarsi meglio al mobile
                    scales: {
                        x: {
                            type: 'time', // Configura l'asse X come asse temporale
                            time: {
                                unit: 'day', // Mostra un intervallo basato sui giorni
                                //tooltipFormat: 'dd-MM-yyyy', // Formato per il tooltip
                                // displayFormats: {
                                //     day: 'MM-dd' // Formato per le etichette
                                // },
                            },
                        },
                        y: {
                            beginAtZero: true,
                            stacked: true // Abilita lo stacking
                        }
                    },
                }
            });
}
    });
</script>


