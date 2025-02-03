@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('content')

    <div class="row">

        {{-- {{ auth()->user()->userPermission->role->role_name === 'Agent' }} --}}
        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
            <div class="small-box bg-green whitecolor mt-3">
                <div class="inner">
                    <h4><span class="count-number">{{ $total_customer }}</span></h4>
                    <p>{{ __('home.home')[0] }}</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
            <div class="small-box bg-pase whitecolor mt-3">
                <div class="inner">
                    <h4><span class="count-number">{{ $total_product }}</span></h4>
                    <p>{{ __('home.home')[1] }}</p>
                </div>
                <div class="icon">
                    <i class="fa fa-shopping-bag"></i>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
            <div class="small-box bg-bringal whitecolor mt-3">
                <div class="inner">
                    <h4><span class="count-number">{{ $total_supplier }}</span></h4>
                    <p>{{ __('home.home')[2] }}</p>
                </div>
                <div class="icon">
                    <i class="fa fa-user"></i>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
            <div class="small-box bg-darkgreen whitecolor mt-3">
                <div class="inner">
                    <h4><span class="count-number">{{ $today_sale }}</span> </h4>
                    <p>{{ __('home.home')[3] }}</p>
                </div>
                <div class="icon">
                    <i class="fa fa-money"></i>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-md-6 col-lg-6">
            <div class=" card-box table-responsive" style="border-top: 3px solid #157c34;">
                <h4 class="m-t-0 header-title mb-3"><b>{{ __('home.home')[4] }}</b></h4>
                <div id="chairt-container"></div>
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class=" card-box table-responsive" style="border-top: 3px solid #157c34;">
                <h4 class="m-t-0 header-title mb-3"><b>{{ __('home.home')[5] }}</b></h4>
                <canvas id="barChart" class="mt-3 mb-5"></canvas>
            </div>
        </div>
    </div>


@endsection


@section('script')

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>

    <script>
        $(function() {
            let datas = <?php echo json_encode($best_sale_product); ?>;
            let products = <?php echo json_encode($best_sale_product_name); ?>;
            let barCanvas = $("#barChart");
            let barChart = new Chart(barCanvas, {
                type: 'bar',
                data: {
                    // labels: ['product1','product2','product3','product4','product5','p6','p7','p8','p9','p10'],
                    labels: products,
                    datasets: [{
                        label: "Top 10 Selling Product This Month",
                        data: datas,
                        backgroundColor: ['read', 'orange', 'yellow', 'green', 'blue', 'indigo',
                            'violet', 'purple', 'pink', 'gray'
                        ]
                    }],
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    },
                }
            })
        })
    </script>

    <script>
        let datas = <?php echo json_encode($datas); ?>
        // let datas = "{{ json_encode($datas) }}"
        // console.log(datas)
        Highcharts.chart('chairt-container', {
            title: {
                text: ""
            },
            subtitle: {
                text: ""
            },
            xAxis: {
                categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "July", "Aug", "Sep", "Oct", "Nov", "Dec"]
            },
            yAxis: {
                title: {
                    text: ''
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: "middle"
            },
            plotOptions: {
                series: {
                    allowPointSelect: true
                }
            },
            series: [{
                name: "okk",
                data: datas
            }],
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            layout: "horizontal",
                            align: 'center',
                            verticalAlign: 'bottom'
                        }
                    }
                }]
            }

        })
    </script>

@endsection
