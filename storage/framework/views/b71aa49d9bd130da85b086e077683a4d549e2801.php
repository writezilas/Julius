<?php $__env->startSection('title'); ?> <?php echo app('translator')->get('translation.dashboard'); ?> <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Home <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Dashboard <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row project-wrapper">
        <div class="col-xxl-8">
            <div class="row">
                <div class="col-xl-4">
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
                                        Earnings</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="825">0</span></h4>
                                        <span class="badge badge-soft-danger fs-12"><i
                                                class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>0%</span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

                <div class="col-xl-4">
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
                                    <p class="text-uppercase fw-medium text-muted mb-3">Expenses</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="7522">0</span></h4>
                                        <span class="badge badge-soft-success fs-12"><i
                                                class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>0%</span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

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
                                        Net</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="825">0</span></h4>
                                        <span class="badge badge-soft-danger fs-12"><i
                                                class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>0%</span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

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
                                        Referrals</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="825">0</span></h4>
                                        <span class="badge badge-soft-danger fs-12"><i
                                                class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>0</span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

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
                                        Available M-Pesa Shares</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="825">0</span></h4>
                                        <span class="badge badge-soft-danger fs-12"><i
                                                class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>0</span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

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
                                        Available Equity Shares</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="825">0</span></h4>
                                        <span class="badge badge-soft-danger fs-12"><i
                                                class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>0</span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

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
                                            Available KCB Shares</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="825">0</span></h4>
                                        <span class="badge badge-soft-danger fs-12"><i
                                                class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>0</span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

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
                                           Available Other Banks Shares (Kenya Only) </p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="825">0</span></h4>
                                        <span class="badge badge-soft-danger fs-12"><i
                                                class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>0</span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

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
                                            Available Airtel Shares</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="825">0</span></h4>
                                        <span class="badge badge-soft-danger fs-12"><i
                                                class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>0</span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

            </div><!-- end row -->
        </div><!-- end col -->

        
    </div><!-- end row -->

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header align-items-center border-0 d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Equity Bank Shares</h4>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content p-0">
                        <div class="">
                            <div class="p-3">
                                <div>
                                    <div class="input-group mb-3">
                                        <label class="input-group-text">Amount</label>
                                        <input type="number" class="form-control" placeholder="0000">
                                    </div>
                                    <div class="input-group mb-0">
                                        <label class="input-group-text">Period</label>
                                        <select class="form-select">
                                            <option value="1">4 Days</option>
                                            <option value="2">6 Days</option>
                                            <option value="3">8 Days</option>
                                            <option value="4">10 Days</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mt-3 pt-2">
                                    <button type="button" class="btn btn-primary w-100">Bid</button>
                                </div>
                            </div>
                        </div><!-- end tabpane -->
                    </div><!-- end tab pane -->
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header align-items-center border-0 d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">KCB Bank Shares</h4>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content p-0">
                        <div class="">
                            <div class="p-3">
                                <div>
                                    <div class="input-group mb-3">
                                        <label class="input-group-text">Amount</label>
                                        <input type="number" class="form-control" placeholder="0000">
                                    </div>
                                    <div class="input-group mb-0">
                                        <label class="input-group-text">Period</label>
                                        <select class="form-select">
                                            <option value="1">4 Days</option>
                                            <option value="2">6 Days</option>
                                            <option value="3">8 Days</option>
                                            <option value="4">10 Days</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mt-3 pt-2">
                                    <button type="button" class="btn btn-primary w-100">Bid</button>
                                </div>
                            </div>
                        </div><!-- end tabpane -->
                    </div><!-- end tab pane -->
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header align-items-center border-0 d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Other Banks Auction (Kenya Only)</h4>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content p-0">
                        <div class="">
                            <div class="p-3">
                                <div>
                                    <div class="input-group mb-3">
                                        <label class="input-group-text">Amount</label>
                                        <input type="number" class="form-control" placeholder="0000">
                                    </div>
                                    <div class="input-group mb-0">
                                        <label class="input-group-text">Period</label>
                                        <select class="form-select">
                                            <option value="1">4 Days</option>
                                            <option value="2">6 Days</option>
                                            <option value="3">8 Days</option>
                                            <option value="4">10 Days</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mt-3 pt-2">
                                    <button type="button" class="btn btn-primary w-100">Bid</button>
                                </div>
                            </div>
                        </div><!-- end tabpane -->
                    </div><!-- end tab pane -->
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header align-items-center border-0 d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">M-pesa Auction</h4>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content p-0">
                        <div class="">
                            <div class="p-3">
                                <div>
                                    <div class="input-group mb-3">
                                        <label class="input-group-text">Amount</label>
                                        <input type="number" class="form-control" placeholder="0000">
                                    </div>
                                    <div class="input-group mb-0">
                                        <label class="input-group-text">Period</label>
                                        <select class="form-select">
                                            <option value="1">4 Days</option>
                                            <option value="2">6 Days</option>
                                            <option value="3">8 Days</option>
                                            <option value="4">10 Days</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mt-3 pt-2">
                                    <button type="button" class="btn btn-primary w-100">Bid</button>
                                </div>
                            </div>
                        </div><!-- end tabpane -->
                    </div><!-- end tab pane -->
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header align-items-center border-0 d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Airtel Shares</h4>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content p-0">
                        <div class="">
                            <div class="p-3">
                                <div>
                                    <div class="input-group mb-3">
                                        <label class="input-group-text">Amount</label>
                                        <input type="number" class="form-control" placeholder="0000">
                                    </div>
                                    <div class="input-group mb-0">
                                        <label class="input-group-text">Period</label>
                                        <select class="form-select">
                                            <option value="1">4 Days</option>
                                            <option value="2">6 Days</option>
                                            <option value="3">8 Days</option>
                                            <option value="4">10 Days</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mt-3 pt-2">
                                    <button type="button" class="btn btn-primary w-100">Bid</button>
                                </div>
                            </div>
                        </div><!-- end tabpane -->
                    </div><!-- end tab pane -->
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row -->
    <div class="row">
        <div class="col-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Activities</h4>
                    <div class="flex-shrink-0">
                        <div class="dropdown card-header-dropdown">
                            <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="fw-semibold text-uppercase fs-12">Sort by: </span><span
                                    class="text-muted">Current Week<i
                                        class="mdi mdi-chevron-down ms-1"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Today</a>
                                <a class="dropdown-item" href="#">Last Week</a>
                                <a class="dropdown-item" href="#">Last Month</a>
                                <a class="dropdown-item" href="#">Current Year</a>
                            </div>
                        </div>
                    </div>
                </div><!-- end card header -->
                <div class="card-body p-0">
                    <div data-simplebar style="height: 390px;">
                        <div class="p-3">
                            <h6 class="text-muted text-uppercase mb-3 fs-11">25 Dec 2021</h6>
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title bg-light rounded-circle">
                                        <i data-feather="arrow-down-circle"
                                            class="icon-dual-success icon-sm"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-14 mb-1">Bought Bitcoin</h6>
                                    <p class="text-muted fs-12 mb-0">
                                        <i
                                            class="mdi mdi-circle-medium text-success fs-15 align-middle"></i>
                                        Visa Debit Card ***6
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-end">
                                    <h6 class="mb-1 text-success">+0.04025745<span
                                            class="text-uppercase ms-1">Btc</span></h6>
                                    <p class="text-muted fs-13 mb-0">+878.52 USD</p>
                                </div>
                            </div><!-- end -->
                            <div class="d-flex align-items-center mt-3">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title bg-light rounded-circle">
                                        <i data-feather="send" class="icon-dual-warning icon-sm"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-14 mb-1">Sent Eathereum</h6>
                                    <p class="text-muted fs-12 mb-0">
                                        <i
                                            class="mdi mdi-circle-medium text-warning fs-15 align-middle"></i>
                                        Sofia Cunha
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-end">
                                    <h6 class="mb-1 text-muted">-0.09025182<span
                                            class="text-uppercase ms-1">Eth</span></h6>
                                    <p class="text-muted fs-13 mb-0">-659.35 USD</p>
                                </div>
                            </div><!-- end -->

                            <h6 class="text-muted text-uppercase mb-3 mt-4 fs-11">24 Dec 2021</h6>
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title bg-light rounded-circle">
                                        <i data-feather="arrow-up-circle"
                                            class="icon-dual-danger icon-sm"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-14 mb-1">Sell Dash</h6>
                                    <p class="text-muted fs-12 mb-0">
                                        <i
                                            class="mdi mdi-circle-medium text-danger fs-15 align-middle"></i>
                                        www.cryptomarket.com
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-end">
                                    <h6 class="mb-1 text-danger">-98.6025422<span
                                            class="text-uppercase ms-1">Dash</span></h6>
                                    <p class="text-muted fs-13 mb-0">-1508.98 USD</p>
                                </div>
                            </div><!-- end -->
                            <div class="d-flex align-items-center mt-3">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title bg-light rounded-circle">
                                        <i data-feather="arrow-up-circle"
                                            class="icon-dual-danger icon-sm"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-14 mb-1">Sell Dogecoin</h6>
                                    <p class="text-muted fs-12 mb-0">
                                        <i
                                            class="mdi mdi-circle-medium text-success fs-15 align-middle"></i>
                                        www.coinmarket.com
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-end">
                                    <h6 class="mb-1 text-danger">-1058.08025142<span
                                            class="text-uppercase ms-1">Doge</span></h6>
                                    <p class="text-muted fs-13 mb-0">-89.36 USD</p>
                                </div>
                            </div><!-- end -->
                            <div class="d-flex align-items-center mt-3">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title bg-light rounded-circle">
                                        <i data-feather="arrow-up-circle"
                                            class="icon-dual-success icon-sm"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-14 mb-1">Bought Litecoin</h6>
                                    <p class="text-muted fs-12 mb-0">
                                        <i
                                            class="mdi mdi-circle-medium text-warning fs-15 align-middle"></i>
                                        Payment via Wallet
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-end">
                                    <h6 class="mb-1 text-success">+0.07225912<span
                                            class="text-uppercase ms-1">Ltc</span></h6>
                                    <p class="text-muted fs-13 mb-0">+759.45 USD</p>
                                </div>
                            </div><!-- end -->

                            <h6 class="text-muted text-uppercase mb-3 mt-4 fs-11">20 Dec 2021</h6>
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title bg-light rounded-circle">
                                        <i data-feather="send" class="icon-dual-warning icon-sm"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-14 mb-1">Sent Eathereum</h6>
                                    <p class="text-muted fs-12 mb-0">
                                        <i
                                            class="mdi mdi-circle-medium text-warning fs-15 align-middle"></i>
                                        Sofia Cunha
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-end">
                                    <h6 class="mb-1 text-muted">-0.09025182<span
                                            class="text-uppercase ms-1">Eth</span></h6>
                                    <p class="text-muted fs-13 mb-0">-659.35 USD</p>
                                </div>
                            </div><!-- end -->

                            <div class="d-flex align-items-center mt-3">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title bg-light rounded-circle">
                                        <i data-feather="arrow-down-circle"
                                            class="icon-dual-success icon-sm"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fs-14 mb-1">Bought Bitcoin</h6>
                                    <p class="text-muted fs-12 mb-0">
                                        <i
                                            class="mdi mdi-circle-medium text-success fs-15 align-middle"></i>
                                        Visa Debit Card ***6
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-end">
                                    <h6 class="mb-1 text-success">+0.04025745<span
                                            class="text-uppercase ms-1">Btc</span></h6>
                                    <p class="text-muted fs-13 mb-0">+878.52 USD</p>
                                </div>
                            </div><!-- end -->

                            <div class="mt-3 text-center">
                                <a href="javascript:void(0);"
                                    class="text-muted text-decoration-underline">Load More</a>
                            </div>

                        </div>

                    </div>
                </div><!-- end cardbody -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

    <script src="<?php echo e(URL::asset('/assets/js/app.min.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u128770841/domains/autobider.live/resources/views/user-panel/dashboard.blade.php ENDPATH**/ ?>