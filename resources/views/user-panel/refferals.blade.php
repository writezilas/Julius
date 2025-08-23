@extends('layouts.master')
@php($pageTitle = 'Referral Program')
@section('title', $pageTitle)
@section('content')
	@component('components.breadcrumb')
		@slot('li_1') Dashboard @endslot
		@slot('title')  {{$pageTitle}} @endslot
	@endcomponent

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Referrals</p>
                        </div>
                        <div class="flex-shrink-0">
                            <h5 class="text-success fs-14 mb-0">
                                <i class="ri-user-add-line fs-13 align-middle"></i> {{$totalReferrals}}
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{$totalReferrals}}</h4>
                            <span class="badge bg-success-subtle text-success mb-0"> 
                                <i class="ri-arrow-up-line align-middle"></i> All Time 
                            </span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                <i class="ri-team-line text-success"></i>
                            </span>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Earnings</p>
                        </div>
                        <div class="flex-shrink-0">
                            <h5 class="text-primary fs-14 mb-0">
                                <i class="ri-money-dollar-circle-line fs-13 align-middle"></i> Ksh {{number_format($totalEarnings, 2)}}
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">Ksh {{number_format($totalEarnings, 2)}}</h4>
                            <span class="badge bg-primary-subtle text-primary mb-0"> 
                                <i class="ri-coins-line align-middle"></i> Commission 
                            </span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                <i class="ri-currency-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Paid Referrals</p>
                        </div>
                        <div class="flex-shrink-0">
                            <h5 class="text-info fs-14 mb-0">
                                <i class="ri-check-double-line fs-13 align-middle"></i> {{$paidReferrals}}
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{$paidReferrals}}</h4>
                            <span class="badge bg-info-subtle text-info mb-0"> 
                                <i class="ri-check-line align-middle"></i> Completed 
                            </span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                <i class="ri-shield-check-line text-info"></i>
                            </span>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Recent (7 days)</p>
                        </div>
                        <div class="flex-shrink-0">
                            <h5 class="text-warning fs-14 mb-0">
                                <i class="ri-time-line fs-13 align-middle"></i> {{$recentReferrals}}
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{$recentReferrals}}</h4>
                            <span class="badge bg-warning-subtle text-warning mb-0"> 
                                <i class="ri-calendar-line align-middle"></i> This Week 
                            </span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                <i class="ri-calendar-check-line text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row -->

    <!-- Referral Link Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary bg-gradient">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="text-white">
                                <h4 class="fw-semibold fs-20 text-white mb-3">
                                    <i class="ri-share-line align-middle me-2"></i>Share Your Referral Link
                                </h4>
                                <p class="text-white-75 mb-3 fs-15">
                                    Earn commission by inviting friends to join our platform. Share your unique referral link below.
                                </p>
                                
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control bg-white" id="referralCode" 
                                           value="{{url('/register?refferal_code='.auth()->user()->username)}}" 
                                           readonly style="font-size: 14px;">
                                    <button class="btn btn-light" type="button" onclick="handleCopyLink()">
                                        <i class="ri-file-copy-line me-1"></i> Copy
                                    </button>
                                </div>
                                
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-light btn-sm" onclick="shareOnWhatsApp()">
                                        <i class="ri-whatsapp-line me-1"></i> WhatsApp
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm" onclick="shareOnTelegram()">
                                        <i class="ri-telegram-line me-1"></i> Telegram
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm" onclick="shareViaEmail()">
                                        <i class="ri-mail-line me-1"></i> Email
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm" onclick="generateQR()">
                                        <i class="ri-qr-code-line me-1"></i> QR Code
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center">
                            <div class="text-white">
                                <div class="display-6 mb-2">
                                    <i class="ri-gift-line text-white-75"></i>
                                </div>
                                <h6 class="text-white mb-0">Start Earning Today!</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referrals Table -->
    @if(count($refferals) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="ri-team-line align-middle me-2 text-primary"></i>Your Referrals
                            </h5>
                            <p class="text-muted mb-0">Track all your referred users and earnings</p>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="tooltip" 
                                        data-bs-placement="top" title="Export to Excel">
                                    <i class="ri-file-excel-line align-middle"></i>
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="tooltip" 
                                        data-bs-placement="top" title="Print">
                                    <i class="ri-printer-line align-middle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="referralsTable" class="table table-hover align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center">#</th>
                                    <th scope="col">
                                        <i class="ri-user-line align-middle me-1"></i>Username
                                    </th>
                                    <th scope="col">
                                        <i class="ri-calendar-line align-middle me-1"></i>Signup Date
                                    </th>
                                    <th scope="col">
                                        <i class="ri-money-dollar-circle-line align-middle me-1"></i>Commission
                                    </th>
                                    <th scope="col" class="text-center">
                                        <i class="ri-shield-check-line align-middle me-1"></i>Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($refferals as $ref)
                                <tr>
                                    <td class="text-center">
                                        <span class="fw-medium text-primary">{{$loop->iteration}}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-3">
                                                <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                    {{ strtoupper(substr($ref->username, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="fs-15 fw-semibold mb-0">{{ $ref->username }}</h6>
                                                <p class="text-muted mb-0 fs-13">{{ $ref->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-medium">{{ \Carbon\Carbon::parse($ref->created_at)->format('d M Y') }}</span>
                                            <p class="text-muted mb-0 fs-13">{{ \Carbon\Carbon::parse($ref->created_at)->diffForHumans() }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ri-coins-line text-warning me-2"></i>
                                            <span class="fw-semibold fs-15">Ksh {{ number_format($ref->ref_amount, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($ref->ref_amount > 0)
                                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                                <i class="ri-check-line align-middle me-1"></i>Paid
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                                <i class="ri-time-line align-middle me-1"></i>Pending
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if(count($refferals) == 0)
                    <div class="noresult text-center py-5">
                        <div class="mb-3">
                            <i class="ri-team-line display-4 text-muted"></i>
                        </div>
                        <h5 class="mt-2">No Referrals Yet!</h5>
                        <p class="text-muted mb-0">Start sharing your referral link to earn commissions from new users.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="ri-team-line display-4 text-muted"></i>
                    </div>
                    <h4 class="mb-3">No Referrals Yet!</h4>
                    <p class="text-muted mb-4 fs-15">You haven't referred anyone yet. Start sharing your referral link above to earn commissions from new users who sign up through your link.</p>
                    <button type="button" class="btn btn-primary" onclick="handleCopyLink()">
                        <i class="ri-file-copy-line align-middle me-1"></i> Copy Referral Link
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">Referral QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qrcode" class="mb-3"></div>
                    <p class="text-muted">Share this QR code for easy mobile scanning</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="downloadQR()">Download QR</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <!-- QR Code Generator -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <!-- DataTables for enhanced table -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable for referrals
            $('#referralsTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                language: {
                    search: "<i class='ri-search-line'></i>",
                    searchPlaceholder: "Search referrals...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ referrals",
                    infoEmpty: "Showing 0 to 0 of 0 referrals",
                    infoFiltered: "(filtered from _MAX_ total referrals)"
                },
                columnDefs: [
                    { orderable: false, targets: [0] }, // Disable sorting on # column
                    { searchable: false, targets: [0] } // Disable search on # column
                ],
                order: [[2, 'desc']] // Sort by signup date descending by default
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Enhanced copy link function with modern clipboard API
        async function handleCopyLink() {
            const referralCode = document.getElementById('referralCode').value;
            
            try {
                // Use modern clipboard API if available
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(referralCode);
                } else {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = referralCode;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-999999px';
                    textArea.style.top = '-999999px';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    document.execCommand('copy');
                    textArea.remove();
                }
                
                // Show success toast
                showToast('Success!', 'Referral link copied to clipboard', 'success');
                
            } catch (err) {
                console.error('Failed to copy text: ', err);
                showToast('Error!', 'Failed to copy referral link', 'error');
            }
        }

        // Share on WhatsApp
        function shareOnWhatsApp() {
            const referralCode = document.getElementById('referralCode').value;
            const message = `🎉 Join me on this amazing trading platform! Use my referral link to get started: ${referralCode}`;
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }

        // Share on Telegram
        function shareOnTelegram() {
            const referralCode = document.getElementById('referralCode').value;
            const message = `🚀 Join me on this incredible trading platform! Sign up using my referral link: ${referralCode}`;
            const telegramUrl = `https://t.me/share/url?url=${encodeURIComponent(referralCode)}&text=${encodeURIComponent(message)}`;
            window.open(telegramUrl, '_blank');
        }

        // Share via Email
        function shareViaEmail() {
            const referralCode = document.getElementById('referralCode').value;
            const subject = 'Join me on this amazing trading platform!';
            const body = `Hi there!\n\nI wanted to share this incredible trading platform with you. You can sign up using my referral link and we both benefit!\n\nReferral Link: ${referralCode}\n\nLooking forward to having you on board!\n\nBest regards`;
            const emailUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            window.location.href = emailUrl;
        }

        // Generate QR Code
        function generateQR() {
            const referralCode = document.getElementById('referralCode').value;
            const qrContainer = document.getElementById('qrcode');
            
            // Clear previous QR code
            qrContainer.innerHTML = '';
            
            // Generate new QR code
            QRCode.toCanvas(qrContainer, referralCode, {
                width: 256,
                height: 256,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            }, function (error) {
                if (error) {
                    console.error('QR Code generation failed:', error);
                    showToast('Error!', 'Failed to generate QR code', 'error');
                } else {
                    // Show modal
                    const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
                    qrModal.show();
                }
            });
        }

        // Download QR Code
        function downloadQR() {
            const canvas = document.querySelector('#qrcode canvas');
            if (canvas) {
                const link = document.createElement('a');
                link.download = 'referral-qr-code.png';
                link.href = canvas.toDataURL();
                link.click();
                showToast('Success!', 'QR code downloaded successfully', 'success');
            }
        }

        // Toast notification function
        function showToast(title, message, type = 'info') {
            // Create toast element
            const toastId = 'toast_' + Date.now();
            const iconClass = {
                'success': 'ri-check-line',
                'error': 'ri-close-line',
                'warning': 'ri-alert-line',
                'info': 'ri-information-line'
            }[type] || 'ri-information-line';
            
            const bgClass = {
                'success': 'bg-success',
                'error': 'bg-danger',
                'warning': 'bg-warning',
                'info': 'bg-primary'
            }[type] || 'bg-primary';

            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="${iconClass} me-2"></i>${title} ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            // Create toast container if it doesn't exist
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            // Add toast to container
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            // Show toast
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 3000
            });
            toast.show();
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function () {
                toastElement.remove();
            });
        }

        // Animate statistics cards on load
        function animateStats() {
            const cards = document.querySelectorAll('.card-animate');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 150);
            });
        }

        // Initialize animations when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(animateStats, 300);
        });

        // Export to Excel functionality
        function exportToExcel() {
            const table = document.getElementById('referralsTable');
            const wb = XLSX.utils.table_to_book(table, {sheet: 'Referrals'});
            XLSX.writeFile(wb, 'referrals_' + new Date().toISOString().slice(0,10) + '.xlsx');
        }

        // Print functionality
        function printTable() {
            const printContent = document.querySelector('.table-responsive').innerHTML;
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Referrals Report</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">');
            printWindow.document.write('<style>@media print { .btn { display: none; } }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<div class="container-fluid"><h2 class="mb-4">Referrals Report</h2>');
            printWindow.document.write(printContent);
            printWindow.document.write('</div></body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }

        // Add click events to export buttons
        document.addEventListener('DOMContentLoaded', function() {
            const excelBtn = document.querySelector('[title="Export to Excel"]');
            const printBtn = document.querySelector('[title="Print"]');
            
            if (excelBtn) excelBtn.addEventListener('click', exportToExcel);
            if (printBtn) printBtn.addEventListener('click', printTable);
        });
    </script>

    <!-- Add some custom CSS for better responsive design -->
    <style>
        @media (max-width: 768px) {
            .card-animate {
                margin-bottom: 1rem;
            }
            
            .input-group {
                flex-direction: column;
            }
            
            .input-group .form-control {
                border-radius: 0.375rem !important;
                margin-bottom: 0.5rem;
            }
            
            .input-group .btn {
                border-radius: 0.375rem !important;
            }
            
            .d-flex.flex-wrap.gap-2 {
                justify-content: center;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .avatar-xs {
                display: none;
            }
        }
        
        @media (max-width: 576px) {
            .col-xl-3.col-md-6 {
                margin-bottom: 1rem;
            }
            
            .display-6 {
                font-size: 2rem;
            }
            
            .fs-22 {
                font-size: 1.2rem !important;
            }
        }
        
        .card-animate:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        .badge {
            font-weight: 500;
        }
        
        .toast {
            min-width: 300px;
        }
        
        #qrcode canvas {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
@endsection

