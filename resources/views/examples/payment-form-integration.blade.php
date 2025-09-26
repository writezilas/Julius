@extends('layouts.master')

@section('title', 'Payment Form Integration Example')

@section('css')
    <!-- Payment Form CSS -->
    <link href="{{ asset('assets/css/payment-form.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Examples @endslot
    @slot('title') Payment Form Integration @endslot
@endcomponent

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Enhanced Payment Submit Form</h4>
                <p class="text-muted mb-0">This demonstrates how to integrate the new payment form component</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Integration Steps:</h5>
                        <ol class="list-group list-group-numbered">
                            <li class="list-group-item">Include the CSS file in your layout</li>
                            <li class="list-group-item">Include the JavaScript file before closing body tag</li>
                            <li class="list-group-item">Use the payment form component in your blade template</li>
                            <li class="list-group-item">Ensure proper data is passed to the component</li>
                        </ol>

                        <h5 class="mt-4">Required Variables:</h5>
                        <ul class="list-group">
                            <li class="list-group-item"><code>$user</code> - The seller user object</li>
                            <li class="list-group-item"><code>$share</code> - The share object being purchased</li>
                            <li class="list-group-item"><code>$businessProfile</code> - Seller's business profile</li>
                            <li class="list-group-item"><code>$totalShare</code> - Number of shares</li>
                            <li class="list-group-item"><code>$tradePrice</code> - Price per share</li>
                            <li class="list-group-item"><code>$pairedIds</code> - Array of paired share IDs</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Example Usage:</h5>
                        <pre><code class="language-php">{{-- Include CSS in head --}}
@section('css')
&lt;link href="{{ asset('assets/css/payment-form.css') }}" rel="stylesheet" /&gt;
@endsection

{{-- Include the component --}}
@include('components.payment-submit-form', [
    'user' => $seller,
    'share' => $userShare,
    'businessProfile' => $businessProfile,
    'totalShare' => $totalShares,
    'tradePrice' => $trade->price,
    'pairedIds' => $pairedShareIds
])

{{-- Include JavaScript before closing body --}}
@section('script')
&lt;script src="{{ asset('assets/js/payment-form.js') }}"&gt;&lt;/script&gt;
@endsection</code></pre>

                        <h5 class="mt-4">Features:</h5>
                        <ul class="list-group">
                            <li class="list-group-item">✅ Enhanced form validation</li>
                            <li class="list-group-item">✅ Audio notifications</li>
                            <li class="list-group-item">✅ Loading states</li>
                            <li class="list-group-item">✅ Copy to clipboard functionality</li>
                            <li class="list-group-item">✅ Responsive design</li>
                            <li class="list-group-item">✅ Error handling</li>
                            <li class="list-group-item">✅ Accessibility features</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#demoPaymentModal">
                        <i class="ri-secure-payment-line me-2"></i>Demo Payment Form
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Demo Payment Form --}}
@php
    // Create dummy data for demonstration
    $demoUser = (object) [
        'id' => 1,
        'name' => 'John Seller',
        'username' => 'johnseller'
    ];
    
    $demoShare = (object) [
        'id' => 1,
        'pairedWithThis' => collect([(object) ['user_share_id' => 123]])
    ];
    
    $demoBusinessProfile = (object) [
        'mpesa_name' => 'John Doe',
        'mpesa_no' => '254700123456'
    ];
    
    $demoTotalShare = 100;
    $demoTradePrice = 150;
    $demoPairedIds = [1, 2, 3];
@endphp

@include('components.payment-submit-form', [
    'user' => $demoUser,
    'share' => $demoShare,
    'businessProfile' => $demoBusinessProfile,
    'totalShare' => $demoTotalShare,
    'tradePrice' => $demoTradePrice,
    'pairedIds' => $demoPairedIds
])

@endsection

@section('script')
    <!-- Payment Form JavaScript -->
    <script src="{{ asset('assets/js/payment-form.js') }}"></script>
    
    <!-- Payment Form Testing Script -->
    <script src="{{ asset('assets/js/payment-form-test.js') }}"></script>
    
    <script>
        // Additional demo-specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Payment form demo page loaded');
            
            // Add manual test button functionality
            const demoButton = document.querySelector('[data-bs-target*="demoPaymentModal"]');
            if (demoButton) {
                console.log('Demo button found, ready for testing');
            }
        });
        
        // Function to run tests manually
        function runPaymentFormTests() {
            if (window.PaymentFormTester) {
                const tester = new window.PaymentFormTester();
                tester.runAllTests();
            } else {
                console.error('PaymentFormTester not loaded');
            }
        }
    </script>
@endsection
