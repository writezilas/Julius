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
                    $investment = \App\Models\UserShare::where('status', 'completed')->where('user_id', auth()->user()->id)->sum('amount');
                    $profit = \App\Models\UserShare::where('status', 'completed')->where('user_id', auth()->user()->id)->sum('profit_share');
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
                                                data-target="0">0</span></h4>
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

    <!-- end row -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Activities</h4>
{{--                    <div class="flex-shrink-0">--}}
{{--                        <div class="dropdown card-header-dropdown">--}}
{{--                            <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"--}}
{{--                                aria-haspopup="true" aria-expanded="false">--}}
{{--                                <span class="fw-semibold text-uppercase fs-12">Sort by: </span><span--}}
{{--                                    class="text-muted">Current Week<i--}}
{{--                                        class="mdi mdi-chevron-down ms-1"></i></span>--}}
{{--                            </a>--}}
{{--                            <div class="dropdown-menu dropdown-menu-end">--}}
{{--                                <a class="dropdown-item" href="#">Today</a>--}}
{{--                                <a class="dropdown-item" href="#">Last Week</a>--}}
{{--                                <a class="dropdown-item" href="#">Last Month</a>--}}
{{--                                <a class="dropdown-item" href="#">Current Year</a>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </div><!-- end card header -->
                <div class="card-body p-0">
                    <div data-simplebar style="height: 390px;">
                        <div class="p-3">

                            @php
                                if(auth()->check()) {
                                    $logs = auth()->user()->logs->sortByDesc('id');
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
{{--                                        <p class="text-muted fs-13 mb-0">+878.52 USD</p>--}}
                                    </div>
                                </div>
                            @endforeach


{{--                            <div class="mt-3 text-center">--}}
{{--                                <a href="javascript:void(0);"--}}
{{--                                    class="text-muted text-decoration-underline">Load More</a>--}}
{{--                            </div>--}}

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

{{--    <script>--}}
{{--        var linechartcustomerColorsNew = getChartColorsArray("customer-daily-earning-chart");--}}

{{--        if (linechartcustomerColorsNew) {--}}
{{--            var options = {--}}
{{--                series: [{--}}
{{--                    name: "Bought",--}}
{{--                    type: "area",--}}
{{--                    data: [34, 65, 46, 68, 49, 61, 42]--}}
{{--                }, {--}}
{{--                    name: "Profit",--}}
{{--                    type: "bar",--}}
{{--                    data: [89.25, 98.58, 68.74, 108.87, 77.54, 84.03, 51.24]--}}
{{--                }],--}}
{{--                chart: {--}}
{{--                    height: 370,--}}
{{--                    type: "line",--}}
{{--                    toolbar: {--}}
{{--                        show: false--}}
{{--                    }--}}
{{--                },--}}
{{--                stroke: {--}}
{{--                    curve: "straight",--}}
{{--                    dashArray: [0, 0, 8],--}}
{{--                    width: [2, 0, 2.2]--}}
{{--                },--}}
{{--                fill: {--}}
{{--                    opacity: [0.1, 0.9, 1]--}}
{{--                },--}}
{{--                markers: {--}}
{{--                    size: [0, 0, 0],--}}
{{--                    strokeWidth: 2,--}}
{{--                    hover: {--}}
{{--                        size: 4--}}
{{--                    }--}}
{{--                },--}}
{{--                xaxis: {--}}
{{--                    categories: ["Sat", "Sun", "Mon", "Tue", "Wed", "Thu", "Fri"],--}}
{{--                    axisTicks: {--}}
{{--                        show: false--}}
{{--                    },--}}
{{--                    axisBorder: {--}}
{{--                        show: false--}}
{{--                    }--}}
{{--                },--}}
{{--                grid: {--}}
{{--                    show: true,--}}
{{--                    xaxis: {--}}
{{--                        lines: {--}}
{{--                            show: true--}}
{{--                        }--}}
{{--                    },--}}
{{--                    yaxis: {--}}
{{--                        lines: {--}}
{{--                            show: false--}}
{{--                        }--}}
{{--                    },--}}
{{--                    padding: {--}}
{{--                        top: 0,--}}
{{--                        right: -2,--}}
{{--                        bottom: 15,--}}
{{--                        left: 10--}}
{{--                    }--}}
{{--                },--}}
{{--                legend: {--}}
{{--                    show: true,--}}
{{--                    horizontalAlign: "center",--}}
{{--                    offsetX: 0,--}}
{{--                    offsetY: -5,--}}
{{--                    markers: {--}}
{{--                        width: 9,--}}
{{--                        height: 9,--}}
{{--                        radius: 6--}}
{{--                    },--}}
{{--                    itemMargin: {--}}
{{--                        horizontal: 10,--}}
{{--                        vertical: 0--}}
{{--                    }--}}
{{--                },--}}
{{--                plotOptions: {--}}
{{--                    bar: {--}}
{{--                        columnWidth: "30%",--}}
{{--                        barHeight: "70%"--}}
{{--                    }--}}
{{--                },--}}
{{--                colors: linechartcustomerColorsNew,--}}
{{--                tooltip: {--}}
{{--                    shared: true,--}}
{{--                    y: [{--}}
{{--                        formatter: function formatter(y) {--}}
{{--                            if (typeof y !== "undefined") {--}}
{{--                                return y.toFixed(0);--}}
{{--                            }--}}

{{--                            return y;--}}
{{--                        }--}}
{{--                    }, {--}}
{{--                        formatter: function formatter(y) {--}}
{{--                            if (typeof y !== "undefined") {--}}
{{--                                return "$" + y.toFixed(2) + "k";--}}
{{--                            }--}}

{{--                            return y;--}}
{{--                        }--}}
{{--                    }, {--}}
{{--                        formatter: function formatter(y) {--}}
{{--                            if (typeof y !== "undefined") {--}}
{{--                                return y.toFixed(0) + " Sales";--}}
{{--                            }--}}

{{--                            return y;--}}
{{--                        }--}}
{{--                    }]--}}
{{--                }--}}
{{--            };--}}
{{--            var chart = new ApexCharts(document.querySelector("#customer-daily-earning-chart"), options);--}}
{{--            chart.render();--}}
{{--        }--}}


{{--        function getChartColorsArray(chartId) {--}}
{{--            if (document.getElementById(chartId) !== null) {--}}
{{--                var colors = document.getElementById(chartId).getAttribute("data-colors");--}}

{{--                if (colors) {--}}
{{--                    colors = JSON.parse(colors);--}}
{{--                    return colors.map(function (value) {--}}
{{--                        var newValue = value.replace(" ", "");--}}

{{--                        if (newValue.indexOf(",") === -1) {--}}
{{--                            var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);--}}
{{--                            if (color) return color;else return newValue;--}}
{{--                        } else {--}}
{{--                            var val = value.split(",");--}}

{{--                            if (val.length == 2) {--}}
{{--                                var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);--}}
{{--                                rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";--}}
{{--                                return rgbaColor;--}}
{{--                            } else {--}}
{{--                                return newValue;--}}
{{--                            }--}}
{{--                        }--}}
{{--                    });--}}
{{--                } else {--}}
{{--                    console.warn('data-colors atributes not found on', chartId);--}}
{{--                }--}}
{{--            }--}}
{{--        }--}}
{{--    </script>--}}

@endsection
