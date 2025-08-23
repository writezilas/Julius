@extends('layouts.master')
@section('title') @lang('translation.dashboard') @endsection

@section('content')

    @component('components.breadcrumb')
        @slot('li_1') Home @endslot
        @slot('title') Dashboard @endslot
    @endcomponent

    <div class="row project-wrapper">
        <div class="col-xxl-8">
            <div class="row">
                @php
                    $investment = \App\Models\UserShare::where('status', 'completed')
                                    ->where('user_id', auth()->user()->id)->sum('amount');
                    $profit = \App\Models\UserShare::where('status', 'completed')
                            ->where('user_id', auth()->user()->id)->sum('profit_share');
                @endphp
                <div class="col-xl-3">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span
                                        class="avatar-title bg-soft-primary text-primary rounded-2 fs-2">
                                        <i data-feather="briefcase" class="text-primary"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden ms-3">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-3">
                                        Investment
                                    </p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0">
                                            <span class="counter-value" data-target="{{ $investment }}">
                                                0
                                            </span>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

                <div class="col-xl-3">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span
                                        class="avatar-title bg-soft-warning text-warning rounded-2 fs-2">
                                        <i data-feather="award" class="text-warning"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-3">Earning</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="{{ $profit }}">0</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

                <div class="col-xl-3">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-info text-info rounded-2 fs-2">
                                        <i data-feather="clock" class="text-info"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden ms-3">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-3">
                                        Expense
                                    </p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="0">0</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

                <div class="col-xl-3">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-info text-info rounded-2 fs-2">
                                        <i data-feather="clock" class="text-info"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden ms-3">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-3">
                                        Referrals</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="{{auth()->user()->referrals ? auth()->user()->referrals->sum('ref_amount') : 0}}">0</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

                @php
                    $trades = \App\Models\Trade::whereStatus('1')->get();
                @endphp
                @foreach($trades as $trade)
                    <div class="col-xl-4">

                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-info text-info rounded-2 fs-2">
                                        <i data-feather="clock" class="text-info"></i>
                                    </span>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden ms-3">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-3">
                                            Available {{ $trade->name }}
                                        </p>
                                        <div class="d-flex align-items-center mb-3">
                                            <h4 class="fs-4 flex-grow-1 mb-0">
                                                <span class="counter-value" data-target="{{ checkAvailableSharePerTrade($trade->id) }}">0</span>
                                            </h4>
                                        </div>
                                        {{-- <p class="text-muted text-truncate mb-0">Work this month</p> --}}
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div>
                    </div>
                @endforeach


            </div><!-- end row -->
        </div><!-- end col -->

        <div class="col-xxl-4">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Earning</h4>
                </div><!-- end card header -->

                <div class="card-body p-0 pb-2">
                    <div class="w-100">
                        <div id="customer-daily-earning-chart"
                             data-colors='["--vz-primary", "--vz-success", "--vz-danger"]'
                             class="apex-charts" dir="ltr"></div>
                    </div>
                </div><!-- end card body -->
            </div>
        </div>


    </div><!-- end row -->
    
    @include('components.trades')

    <!-- Live Statistics Section -->
    <div class="row live-stats-container">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Live Statistics</h4>
                    <small class="text-muted">Real-time platform data (updates every 30 seconds)</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Leaderboard Section -->
                        <div class="col-xl-4 col-lg-6 mb-3">
                            <div class="h-100">
                                <div class="card bg-primary text-white live-stats-card">
                                    <div class="card-body">
                                        <h5 class="card-title text-white mb-3">
                                            <i class="fas fa-trophy me-2"></i>Top Traders
                                        </h5>
                                        <div id="leaderboard-data">
                                            <div class="live-stats-loading">
                                                <div class="spinner-border text-light spinner-border-sm" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span class="ms-2">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Real-time Stats Section -->
                        <div class="col-xl-4 col-lg-6 mb-3">
                            <div class="h-100">
                                <div class="card bg-success text-white live-stats-card">
                                    <div class="card-body">
                                        <h5 class="card-title text-white mb-3">
                                            <i class="fas fa-chart-line me-2"></i>Recent Activity
                                        </h5>
                                        <div id="realtime-stats">
                                            <div class="live-stats-loading">
                                                <div class="spinner-border text-light spinner-border-sm" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span class="ms-2">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Referrers Section -->
                        <div class="col-xl-4 col-lg-12 mb-3">
                            <div class="h-100">
                                <div class="card bg-info text-white live-stats-card">
                                    <div class="card-body">
                                        <h5 class="card-title text-white mb-3">
                                            <i class="fas fa-users me-2"></i>Top Referrers
                                        </h5>
                                        <div id="referrers-data">
                                            <div class="live-stats-loading">
                                                <div class="spinner-border text-light spinner-border-sm" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span class="ms-2">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- end live statistics row -->

    <!-- end row -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Activities</h4>

                </div><!-- end card header -->
                <div class="card-body p-0">
                    <div data-simplebar style="height: 390px;">
                        <div class="p-3">

                            @php
                                if(auth()->check()) {
                                    $logs = auth()->user()->alllogs->sortByDesc('id');
                                }
                            @endphp

                            @foreach($logs as $log)
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fs-14 mb-1">{{ $log->remarks }}</h6>
                                        <p class="text-muted fs-12 mb-0">
                                            <i class="mdi mdi-clock text-success fs-15 align-middle"></i>
                                            {{ $log->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 text-end">
                                        <h6 class="mb-1 text-success">
                                            {{ $log->value }} {{ $log->type == 'share' ? 'share' : 'KSH' }}
                                        </h6>
                                    </div>
                                </div>
                            @endforeach




                        </div>

                    </div>
                </div><!-- end cardbody -->
            </div><!-- end card -->
        </div>
        <div class="col-xl-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Announcements</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div class="table-card">
                        <table class="table table-centered table-hover align-middle mb-0">
                            <tbody>
                            @php
                                $announcements = \App\Models\Announcement::where('status', '1')->orderBy('id', 'desc')->paginate(5);
                            @endphp

                            @foreach($announcements as $key => $announcement)
                                <tr>
                                    <td>
                                        <div>
                                            <h4 class="m-0 p-0">{{ $announcement->title }}</h4>
                                            <small class="my-3">
                                                {{ $announcement->created_at->diffForHumans() }}
                                            </small>
                                            <p>
                                                {{ $announcement->excerpt }}
                                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#announcementModal{{ $announcement->id }}">View details</a>
                                            </p>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="announcementModal{{ $announcement->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title" id="exampleModalLabel">
                                                    {{ $announcement->title }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                               <div>
                                                   {!! $announcement->description !!}
                                               </div>
                                              @if($announcement->image)
                                                <div class="announce-image mt-2">
                                                    <img src="{{ asset($announcement->image) }}" alt="{{ $announcement->name }}" width="100%">
                                                </div>
                                              @endif
                                            @if($announcement->video_url)
                                                <div class="announce-video mt-2">
                                                    <iframe width="100%" height="315"
                                                            src="{{ $announcement->video_url }}">
                                                    </iframe>
                                                </div>
                                            @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach


                            </tbody>
                        </table><!-- end table -->
                        <div class="float-end mt-2">
                            {{ $announcements->links() }}
                        </div>
                    </div>
                </div> <!-- .card-body-->
            </div> <!-- .card-->
        </div>
    </div><!-- end row -->
@endsection
@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/swiper/swiper.min.js')}}"></script>
    <!-- dashboard init -->
    <script src="{{ URL::asset('/assets/js/pages/dashboard-ecommerce.init.js') }}"></script>

    <script>
        var bought = {{json_encode($resultArray['bought'])}};
        var profit = {{json_encode($resultArray['earning'])}};
        var days = {!!json_encode($resultArray["days"], true)!!};
        
        var linechartcustomerColorsNew = getChartColorsArray("customer-daily-earning-chart");
        if (linechartcustomerColorsNew) {
            var options = {
                series: [{
                    name: "Bought",
                    type: "area",
                    data: bought
                }, {
                    name: "Profit",
                    type: "bar",
                    data: profit
                }],
                chart: {
                    height: 370,
                    type: "line",
                    toolbar: {
                        show: false
                    }
                },
                stroke: {
                    curve: "straight",
                    dashArray: [0, 0, 8],
                    width: [2, 0, 2.2]
                },
                fill: {
                    opacity: [0.1, 0.9, 1]
                },
                markers: {
                    size: [0, 0, 0],
                    strokeWidth: 2,
                    hover: {
                        size: 4
                    }
                },
                xaxis: {
                    categories: days,
                    axisTicks: {
                        show: false
                    },
                    axisBorder: {
                        show: false
                    }
                },
                grid: {
                    show: true,
                    xaxis: {
                        lines: {
                            show: true
                        }
                    },
                    yaxis: {
                        lines: {
                            show: false
                        }
                    },
                    padding: {
                        top: 0,
                        right: -2,
                        bottom: 15,
                        left: 10
                    }
                },
                legend: {
                    show: true,
                    horizontalAlign: "center",
                    offsetX: 0,
                    offsetY: -5,
                    markers: {
                        width: 9,
                        height: 9,
                        radius: 6
                    },
                    itemMargin: {
                        horizontal: 10,
                        vertical: 0
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: "30%",
                        barHeight: "70%"
                    }
                },
                colors: linechartcustomerColorsNew,
                tooltip: {
                    shared: true,
                    y: [{
                        formatter: function formatter(y) {
                            if (typeof y !== "undefined") {
                                return y.toFixed(0);
                            }
                            return y;
                        }
                    }, {
                        formatter: function formatter(y) {
                            if (typeof y !== "undefined") {
                                return  y.toFixed(2);
                            }
                            return y;
                        }
                    }, {
                        formatter: function formatter(y) {
                            if (typeof y !== "undefined") {
                                return y.toFixed(0) + " Sales";
                            }
                            return y;
                        }
                    }]
                }
            };
            var chart = new ApexCharts(document.querySelector("#customer-daily-earning-chart"), options);
            chart.render();
        }
        function getChartColorsArray(chartId) {
            if (document.getElementById(chartId) !== null) {
                var colors = document.getElementById(chartId).getAttribute("data-colors");
                if (colors) {
                    colors = JSON.parse(colors);
                    return colors.map(function (value) {
                        var newValue = value.replace(" ", "");
                        if (newValue.indexOf(",") === -1) {
                            var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                            if (color) return color;else return newValue;
                        } else {
                            var val = value.split(",");
                            if (val.length == 2) {
                      var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);                                rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                                return rgbaColor;
                            } else {
                                return newValue;
                            }
                        }
                    });
                } else {
                    console.warn('data-colors atributes not found on', chartId);
                }
            }
        }
        
        // Live Statistics Functions
        function loadLiveStatistics() {
            // Load Leaderboard
            fetch('/api/live-statistics?type=leaderboard')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.leaderboard && data.leaderboard.length > 0) {
                        data.leaderboard.forEach((trader, index) => {
                            const position = index + 1;
                            const badge = position <= 3 ? `<span class="badge bg-warning live-stats-position">#${position}</span>` : `<small class="text-light live-stats-position">#${position}</small>`;
                            html += `
                                <div class="live-stats-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            ${badge}
                                            <span class="ms-2 fw-medium">${trader.username}</span>
                                        </div>
                                        <div class="text-end">
                                            <div class="live-stats-value">KSH ${parseFloat(trader.total_investment || 0).toFixed(2)}</div>
                                            <small class="live-stats-subtitle">Investment</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="text-center text-light"><small>No traders found</small></div>';
                    }
                    document.getElementById('leaderboard-data').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading leaderboard:', error);
                    document.getElementById('leaderboard-data').innerHTML = '<div class="text-center text-light"><small>Error loading data</small></div>';
                });

            // Load Real-time Stats
            fetch('/api/live-statistics?type=realtime')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.activities && data.activities.length > 0) {
                        data.activities.forEach(activity => {
                            const actionText = activity.type === 'bought' ? 'Bought shares' : 'Sold shares';
                            html += `
                                <div class="live-stats-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-medium">${activity.username}</div>
                                            <small class="live-stats-subtitle">${actionText}</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="live-stats-value">KSH ${parseFloat(activity.amount).toFixed(2)}</div>
                                            <small class="live-stats-subtitle">${activity.time}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="text-center text-light"><small>No recent activity</small></div>';
                    }
                    document.getElementById('realtime-stats').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading real-time stats:', error);
                    document.getElementById('realtime-stats').innerHTML = '<div class="text-center text-light"><small>Error loading data</small></div>';
                });

            // Load Top Referrers
            fetch('/api/live-statistics?type=referrers')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.referrers && data.referrers.length > 0) {
                        data.referrers.forEach((referrer, index) => {
                            const position = index + 1;
                            const badge = position <= 3 ? `<span class="badge bg-warning live-stats-position">#${position}</span>` : `<small class="text-light live-stats-position">#${position}</small>`;
                            html += `
                                <div class="live-stats-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            ${badge}
                                            <span class="ms-2 fw-medium">${referrer.username}</span>
                                        </div>
                                        <div class="text-end">
                                            <div class="live-stats-value">${referrer.referral_count}</div>
                                            <small class="live-stats-subtitle">Referrals</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="text-center text-light"><small>No referrers found</small></div>';
                    }
                    document.getElementById('referrers-data').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading referrers:', error);
                    document.getElementById('referrers-data').innerHTML = '<div class="text-center text-light"><small>Error loading data</small></div>';
                });
        }

        // Load statistics on page load and refresh every 30 seconds
        document.addEventListener('DOMContentLoaded', function() {
            loadLiveStatistics();
            setInterval(loadLiveStatistics, 30000); // Refresh every 30 seconds
        });
    </script>

@endsection
