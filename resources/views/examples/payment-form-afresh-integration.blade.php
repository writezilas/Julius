@extends('layouts.master')

@section('title', 'Afresh Payment Form - Modern Integration')

@section('css')
    <!-- Afresh Payment Form CSS -->
    <link href="{{ asset('assets/css/payment-form-afresh.css') }}?v={{ time() }}" rel="stylesheet" type="text/css" />
    
    <!-- Demo Page Styling -->
    <style>
        .demo-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            margin: -24px -24px 40px -24px;
            position: relative;
            overflow: hidden;
        }
        
        .demo-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)" /><circle cx="80" cy="30" r="1.5" fill="rgba(255,255,255,0.08)" /><circle cx="40" cy="70" r="1" fill="rgba(255,255,255,0.06)" /><circle cx="90" cy="80" r="1.2" fill="rgba(255,255,255,0.09)" /></svg>') repeat;
            animation: heroParticles 20s linear infinite;
        }
        
        @keyframes heroParticles {
            0% { transform: translateY(0); }
            100% { transform: translateY(-100px); }
        }
        
        .demo-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
            letter-spacing: -2px;
        }
        
        .demo-hero p {
            font-size: 1.3rem;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 45px rgba(31, 38, 135, 0.4);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(67, 233, 123, 0.3);
        }
        
        .feature-icon i {
            font-size: 40px;
            color: white;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .code-demo {
            background: #1a1d29;
            color: #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            margin: 20px 0;
            border: 1px solid #2d3748;
            font-family: 'SF Mono', 'Monaco', 'Cascadia Code', 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            overflow-x: auto;
        }
        
        .code-demo .keyword {
            color: #f093fb;
        }
        
        .code-demo .string {
            color: #4facfe;
        }
        
        .code-demo .comment {
            color: #68d391;
            font-style: italic;
        }
        
        .demo-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            padding: 16px 32px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .demo-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
        }
        
        .comparison-table {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }
        
        .comparison-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            padding: 20px;
            border: none;
        }
        
        .comparison-table td {
            padding: 16px 20px;
            border-color: rgba(102, 126, 234, 0.1);
            vertical-align: middle;
        }
        
        .check-icon {
            color: #10b981;
            font-size: 20px;
        }
        
        .new-badge {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
@endsection

@section('content')
<!-- Demo Hero Section -->
<div class="demo-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1>Afresh Payment Gateway</h1>
                <p>Experience the next generation of payment forms with glassmorphism design, particle effects, and enhanced user interactions.</p>
                <button type="button" class="demo-button" data-bs-toggle="modal" data-bs-target="#demoAfreshPaymentModal">
                    <i class="ri-secure-payment-fill"></i>
                    <span>Launch Demo</span>
                </button>
            </div>
        </div>
    </div>
</div>

@component('components.breadcrumb')
    @slot('li_1') Examples @endslot
    @slot('title') Afresh Payment Integration @endslot
@endcomponent

<div class="row">
    <!-- Features Overview -->
    <div class="col-12 mb-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ri-magic-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Glassmorphism Design</h5>
                    <p class="text-muted mb-0">Modern glass-effect UI with backdrop blur, gradient overlays, and smooth animations for a premium user experience.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ri-sound-module-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Audio & Haptic Feedback</h5>
                    <p class="text-muted mb-0">Enhanced user interactions with custom audio notifications and haptic feedback for mobile devices.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ri-sparkling-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Particle Effects</h5>
                    <p class="text-muted mb-0">Interactive particle systems for celebrations, visual feedback, and engaging micro-animations.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Integration Guide -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-gradient-primary text-white">
                <h4 class="card-title text-white mb-0">
                    <i class="ri-code-box-fill me-2"></i>Integration Guide
                </h4>
            </div>
            <div class="card-body">
                <h5 class="mb-3">Quick Setup Steps:</h5>
                
                <div class="alert alert-info border-0 mb-4">
                    <div class="d-flex align-items-start">
                        <i class="ri-information-line fs-4 me-3 mt-1"></i>
                        <div>
                            <h6 class="fw-bold mb-2">New vs Original</h6>
                            <p class="mb-0">The Afresh version retains all original functionality and logic while providing a completely redesigned modern interface.</p>
                        </div>
                    </div>
                </div>
                
                <h6 class="fw-bold">1. Include CSS in your layout:</h6>
                <div class="code-demo">
<span class="comment">{{-- Include in head section --}}</span>
<span class="keyword">@section</span>(<span class="string">'css'</span>)
    <span class="keyword">&lt;link</span> <span class="keyword">href=</span><span class="string">"{{ asset('assets/css/payment-form-afresh.css') }}"</span> <span class="keyword">rel=</span><span class="string">"stylesheet"</span> <span class="keyword">/&gt;</span>
<span class="keyword">@endsection</span>
                </div>
                
                <h6 class="fw-bold mt-4">2. Include the component:</h6>
                <div class="code-demo">
<span class="comment">{{-- Include in your blade template --}}</span>
<span class="keyword">@include</span>(<span class="string">'components.payment-submit-form-afresh'</span>, [
    <span class="string">'user'</span> => <span class="keyword">$seller</span>,
    <span class="string">'share'</span> => <span class="keyword">$userShare</span>,
    <span class="string">'businessProfile'</span> => <span class="keyword">$businessProfile</span>,
    <span class="string">'totalShare'</span> => <span class="keyword">$totalShares</span>,
    <span class="string">'tradePrice'</span> => <span class="keyword">$trade</span>->price,
    <span class="string">'pairedIds'</span> => <span class="keyword">$pairedShareIds</span>
])
                </div>
                
                <h6 class="fw-bold mt-4">3. Include JavaScript before closing body:</h6>
                <div class="code-demo">
<span class="comment">{{-- Include before closing body tag --}}</span>
<span class="keyword">@section</span>(<span class="string">'script'</span>)
    <span class="keyword">&lt;script</span> <span class="keyword">src=</span><span class="string">"{{ asset('assets/js/payment-form-afresh.js') }}"</span><span class="keyword">&gt;&lt;/script&gt;</span>
<span class="keyword">@endsection</span>
                </div>
                
                <h6 class="fw-bold mt-4">4. Trigger the modal:</h6>
                <div class="code-demo">
<span class="comment">&lt;!-- Use this button format to open the modal --&gt;</span>
<span class="keyword">&lt;button</span> <span class="keyword">data-bs-toggle=</span><span class="string">"modal"</span> 
        <span class="keyword">data-bs-target=</span><span class="string">"#afreshPaymentModal{{ $seller->id }}-{{ $share->id }}"</span><span class="keyword">&gt;</span>
    Pay Now
<span class="keyword">&lt;/button&gt;</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Features Comparison -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-gradient-success text-white">
                <h5 class="card-title text-white mb-0">
                    <i class="ri-star-fill me-2"></i>New Features
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="comparison-table">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th class="text-center">Afresh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Glassmorphism Design</td>
                                <td class="text-center">
                                    <i class="ri-check-fill check-icon"></i>
                                    <span class="new-badge ms-2">New</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Particle Effects</td>
                                <td class="text-center">
                                    <i class="ri-check-fill check-icon"></i>
                                    <span class="new-badge ms-2">New</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Haptic Feedback</td>
                                <td class="text-center">
                                    <i class="ri-check-fill check-icon"></i>
                                    <span class="new-badge ms-2">New</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Enhanced Audio System</td>
                                <td class="text-center">
                                    <i class="ri-check-fill check-icon"></i>
                                    <span class="new-badge ms-2">New</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Real-time Notifications</td>
                                <td class="text-center">
                                    <i class="ri-check-fill check-icon"></i>
                                    <span class="new-badge ms-2">New</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Morphing Animations</td>
                                <td class="text-center">
                                    <i class="ri-check-fill check-icon"></i>
                                    <span class="new-badge ms-2">New</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Form Validation</td>
                                <td class="text-center"><i class="ri-check-fill check-icon"></i></td>
                            </tr>
                            <tr>
                                <td>Copy to Clipboard</td>
                                <td class="text-center"><i class="ri-check-fill check-icon"></i></td>
                            </tr>
                            <tr>
                                <td>Responsive Design</td>
                                <td class="text-center"><i class="ri-check-fill check-icon"></i></td>
                            </tr>
                            <tr>
                                <td>Accessibility Features</td>
                                <td class="text-center"><i class="ri-check-fill check-icon"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Technical Details -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-gradient-info text-white">
                <h4 class="card-title text-white mb-0">
                    <i class="ri-settings-3-fill me-2"></i>Technical Implementation
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Required Variables:</h5>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item border-0 px-0">
                                <code class="text-primary">$user</code> - The seller user object with id, name, username
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <code class="text-primary">$share</code> - Share object with pairedWithThis relationship
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <code class="text-primary">$businessProfile</code> - Seller's M-Pesa details (mpesa_name, mpesa_no)
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <code class="text-primary">$totalShare</code> - Number of shares being purchased
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <code class="text-primary">$tradePrice</code> - Price per share for calculation
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <code class="text-primary">$pairedIds</code> - Array of paired share IDs (optional)
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Form Fields Generated:</h5>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item border-0 px-0">
                                <strong>Transaction ID:</strong> <code>txs_id</code> (required)
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <strong>Amount:</strong> <code>amount</code> (auto-calculated)
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <strong>Receiver:</strong> <code>receiver_id</code> (hidden)
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <strong>Sender:</strong> <code>sender_id</code> (hidden)
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <strong>Notes:</strong> <code>note_by_sender</code> (optional)
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <strong>Share Pairs:</strong> <code>user_share_pair_ids[]</code> (hidden)
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-success border-0 mt-4">
                    <div class="d-flex align-items-center">
                        <i class="ri-check-double-line fs-4 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Backward Compatible</h6>
                            <p class="mb-0">The Afresh component uses the same backend route and form structure, ensuring seamless integration with existing systems.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Demo Actions -->
<div class="row mt-4">
    <div class="col-12 text-center">
        <div class="btn-group" role="group">
            <button type="button" class="demo-button" data-bs-toggle="modal" data-bs-target="#demoAfreshPaymentModal">
                <i class="ri-play-circle-fill"></i>
                <span>Try Demo</span>
            </button>
            <button type="button" class="demo-button" onclick="testAfreshAudio()">
                <i class="ri-volume-up-fill"></i>
                <span>Test Audio</span>
            </button>
            <button type="button" class="demo-button" onclick="testAfreshNotifications()">
                <i class="ri-notification-fill"></i>
                <span>Test Notifications</span>
            </button>
        </div>
    </div>
</div>

{{-- Demo Payment Form with Sample Data --}}
@php
    // Create realistic demo data
    $demoUser = (object) [
        'id' => 999,
        'name' => 'Sarah Kamau',
        'username' => 'sarahkamau'
    ];
    
    $demoShare = (object) [
        'id' => 888,
        'pairedWithThis' => collect([(object) ['user_share_id' => 777]])
    ];
    
    $demoBusinessProfile = (object) [
        'mpesa_name' => 'Sarah W Kamau',
        'mpesa_no' => '254712345678'
    ];
    
    $demoTotalShare = 250;
    $demoTradePrice = 180;
    $demoPairedIds = [101, 102, 103];
@endphp

@include('components.payment-submit-form-afresh', [
    'user' => $demoUser,
    'share' => $demoShare,
    'businessProfile' => $demoBusinessProfile,
    'totalShare' => $demoTotalShare,
    'tradePrice' => $demoTradePrice,
    'pairedIds' => $demoPairedIds
])

@endsection

@section('script')
    <!-- Afresh Payment Form JavaScript -->
    <script src="{{ asset('assets/js/payment-form-afresh.js') }}?v={{ time() }}"></script>
    
    <script>
        // Demo-specific functions
        function testAfreshAudio() {
            if (window.afreshAudio) {
                window.afreshNotifications.show('Testing audio system...', 'info', 2000);
                
                setTimeout(() => window.afreshAudio.playClick(), 500);
                setTimeout(() => window.afreshAudio.playSuccess(), 1000);
                setTimeout(() => window.afreshAudio.playNotification(), 1500);
                
                setTimeout(() => {
                    window.afreshNotifications.show('Audio test completed!', 'success');
                }, 2000);
            } else {
                alert('Audio system not loaded');
            }
        }
        
        function testAfreshNotifications() {
            if (window.afreshNotifications) {
                window.afreshNotifications.show('This is an info notification', 'info');
                
                setTimeout(() => {
                    window.afreshNotifications.show('This is a success notification', 'success');
                }, 1000);
                
                setTimeout(() => {
                    window.afreshNotifications.show('This is a warning notification', 'warning');
                }, 2000);
                
                setTimeout(() => {
                    window.afreshNotifications.show('This is an error notification', 'error');
                }, 3000);
            } else {
                alert('Notification system not loaded');
            }
        }
        
        // Enhanced demo functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎨 Afresh Payment Form Demo Page Loaded');
            
            // Add demo-specific enhancements
            const demoButtons = document.querySelectorAll('.demo-button');
            demoButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (window.afreshHaptics) {
                        window.afreshHaptics.light();
                    }
                    if (window.afreshAudio) {
                        window.afreshAudio.playClick();
                    }
                });
            });
            
            // Add feature card interactions
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    if (window.afreshAudio) {
                        window.afreshAudio.playClick();
                    }
                });
            });
            
            // Welcome message
            setTimeout(() => {
                if (window.afreshNotifications) {
                    window.afreshNotifications.show('Welcome to Afresh Payment Form Demo!', 'info', 5000);
                }
            }, 1000);
        });
        
        // Override form submission for demo (prevent actual submission)
        function submitAfreshPaymentFormDemo(formId, buttonElement) {
            console.log('🎭 Demo Mode: Preventing actual form submission');
            
            // Run all validation as normal
            const form = document.getElementById(formId);
            if (!form) {
                window.afreshNotifications.show('Form not found!', 'error');
                return false;
            }
            
            // Show loading animation
            window.afreshLoading.show(form, buttonElement);
            
            // Simulate processing time
            setTimeout(() => {
                window.afreshLoading.hide(form, buttonElement);
                window.afreshNotifications.show('Demo submission completed successfully!', 'success');
                window.afreshAudio.playSuccess();
                window.afreshHaptics.success();
                
                // Show success animation
                const userId = buttonElement.id.match(/afreshSubmitBtn(\\d+)/)?.[1];
                if (userId) {
                    window.afreshAnimations.showFloatingSuccess(userId);
                }
                
                // Close modal after success
                setTimeout(() => {
                    const modal = buttonElement.closest('.modal');
                    if (modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }
                }, 2000);
            }, 3000);
            
            return false; // Prevent actual form submission in demo
        }
        
        // Override the global function for demo
        window.submitAfreshPaymentForm = submitAfreshPaymentFormDemo;
    </script>
@endsection
