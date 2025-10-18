@extends('layouts.master')
@php($pageTitle = 'How it works')
@section('title', $pageTitle)

@section('css')
<style>
/* How It Works Page Styles */
.how-it-works-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4rem 0 2rem;
    margin: -1.5rem -15px 2rem;
    border-radius: 0 0 30px 30px;
}

.how-it-works-hero h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.how-it-works-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 0;
}

.step-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    border: none;
}

.step-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.step-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 2rem;
}

.step-number {
    background: #667eea;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    position: absolute;
    top: -15px;
    right: -15px;
    font-size: 0.9rem;
}

.step-title {
    color: #333;
    font-weight: 600;
    font-size: 1.3rem;
    margin-bottom: 1rem;
}

.step-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 0;
}

.investment-periods {
    background: #f8f9ff;
    border-radius: 15px;
    padding: 2rem;
    margin: 2rem 0;
}

.period-item {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin: 1rem 0;
    border-left: 5px solid #667eea;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.period-item:hover {
    transform: translateX(5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.period-days {
    font-size: 1.5rem;
    font-weight: bold;
    color: #667eea;
    margin-bottom: 0.5rem;
}

.period-return {
    color: #28a745;
    font-weight: 600;
    font-size: 1.1rem;
}

.faq-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.faq-item {
    border-bottom: 1px solid #eee;
}

.faq-item:last-child {
    border-bottom: none;
}

.faq-question {
    background: none;
    border: none;
    width: 100%;
    padding: 1.5rem 2rem;
    text-align: left;
    font-weight: 600;
    font-size: 1.1rem;
    color: #333;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.faq-question:hover {
    background: #f8f9ff;
    color: #667eea;
}

.faq-question.active {
    background: #667eea;
    color: white;
}

.faq-icon {
    transition: transform 0.3s ease;
    font-size: 1.2rem;
}

.faq-question.active .faq-icon {
    transform: rotate(45deg);
}

.faq-answer {
    padding: 0 2rem 1.5rem;
    color: #666;
    line-height: 1.6;
    display: none;
}

.faq-answer.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.section-title {
    text-align: center;
    margin-bottom: 3rem;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.section-title h2 {
    font-size: 2.2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 1rem;
    text-align: center;
    width: 100%;
    display: block;
    order: 1;
}

.section-title p {
    color: #666;
    font-size: 1.1rem;
    text-align: center;
    width: 100%;
    display: block;
    margin-top: 0;
    order: 2;
}

.warning-box {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
    padding: 1.5rem;
    border-radius: 10px;
    margin: 2rem 0;
    text-align: center;
}

.warning-box i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
}

.process-flow {
    position: relative;
    margin: 3rem 0;
}

/* Horizontal line removed */

@media (max-width: 768px) {
    .how-it-works-hero {
        padding: 2rem 0 1rem;
        margin: -1rem -15px 1rem;
    }
    
    .how-it-works-hero h1 {
        font-size: 2rem;
    }
    
    .step-card {
        margin-bottom: 2rem;
    }
    
    .process-flow::before {
        display: none;
    }
    
    .section-title {
        text-align: center !important;
        width: 100% !important;
        margin-bottom: 2rem;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .section-title h2 {
        font-size: 1.8rem;
        text-align: center !important;
        width: 100% !important;
        margin-bottom: 0.8rem;
        display: block !important;
        order: 1 !important;
    }
    
    .section-title p {
        text-align: center !important;
        width: 100% !important;
        font-size: 1rem;
        display: block !important;
        margin-top: 0 !important;
        order: 2 !important;
    }
}
</style>
@endsection

@section('content')
	<!-- Hero Section -->
	<div class="how-it-works-hero">
		<div class="container-fluid">
			<div class="row justify-content-center text-center">
				<div class="col-lg-8">
					<h1>{{ $policy->heading_one }}</h1>
					<p>Learn how our peer-to-peer investment platform works and start earning today</p>
				</div>
			</div>
		</div>
	</div>

	<!-- How to Bid Section -->
	<div class="row justify-content-center">
		<div class="col-12 text-center">
			<div class="section-title">
				<h2>How Our Platform Works</h2>
				<p>Simple steps to start your investment journey</p>
			</div>
		</div>
	</div>

	<!-- Platform Overview -->
	<div class="row mb-5">
		<div class="col-lg-8 offset-lg-2">
			<div class="card step-card">
				<div class="card-body text-center">
					<div class="step-icon">
						<i class="ri-exchange-line"></i>
					</div>
					<h4 class="step-title">Peer-to-Peer Investment Platform</h4>
					<p class="step-description">
						Autobidder.live is a peer-to-peer investment platform that simulates the stock exchange market. 
						The system uses advanced algorithms & Logic to match buyers and sellers in the Market.
					</p>
					<div class="warning-box">
						<i class="ri-shield-check-line"></i>
						<strong>No money is held in the autobidder.live wallet. The money will be sent directly to you.</strong>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Investment Periods -->
	<div class="row mb-5">
		<div class="col-12">
			<div class="investment-periods">
				<h3 class="text-center mb-4">Investment Periods & Returns</h3>
				<div class="row">
					<div class="col-md-4">
						<div class="period-item">
							<div class="period-days">3 Days</div>
							<div class="period-return">Expected Return: 30%</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="period-item">
							<div class="period-days">6 Days</div>
							<div class="period-return">Expected Return: 60%</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="period-item">
							<div class="period-days">9 Days</div>
							<div class="period-return">Expected Return: 90%</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Process Steps -->
	<div class="process-flow">
		<div class="row">
			<div class="col-lg-3 col-md-6 mb-4">
				<div class="card step-card">
					<div class="step-number">1</div>
					<div class="card-body text-center">
						<div class="step-icon">
							<i class="ri-shopping-cart-line"></i>
						</div>
						<h5 class="step-title">Buy Shares</h5>
						<p class="step-description">Choose your investment period and purchase shares on the platform.</p>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 mb-4">
				<div class="card step-card">
					<div class="step-number">2</div>
					<div class="card-body text-center">
						<div class="step-icon">
							<i class="ri-user-heart-line"></i>
						</div>
						<h5 class="step-title">Get Matched</h5>
						<p class="step-description">Our system matches you with payees ready to sell their shares.</p>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 mb-4">
				<div class="card step-card">
					<div class="step-number">3</div>
					<div class="card-body text-center">
						<div class="step-icon">
							<i class="ri-money-dollar-circle-line"></i>
						</div>
						<h5 class="step-title">Make Payment</h5>
						<p class="step-description">Pay the payees within the required time through MPESA, Airtel Money, or Bank.</p>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-6 mb-4">
				<div class="card step-card">
					<div class="step-number">4</div>
					<div class="card-body text-center">
						<div class="step-icon">
							<i class="ri-trophy-line"></i>
						</div>
						<h5 class="step-title">Earn Returns</h5>
						<p class="step-description">Wait for your shares to mature and receive your investment plus earnings.</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Process Description -->
	<div class="row mb-5">
		<div class="col-lg-10 offset-lg-1">
			<div class="card step-card">
				<div class="card-body">
					<h4 class="mb-4">The Complete Process</h4>
					<p class="step-description">
						Once an investor successfully buys shares, they will be matched with payees who are ready to sell their shares. 
						The buyer should pay the payee(s) within the required time (failure to do so will result in automatic cancellation). 
						Once all payees have been paid, the buy transaction will be completed. The shares will move to the "Sold Shares Page" 
						where the timer starts counting down according to your chosen investment period.
					</p>
					<p class="step-description">
						Once the shares mature, you will be matched with payers who will pay back your investment plus earnings, and the process continues.
					</p>
					<div class="warning-box">
						<i class="ri-alert-line"></i>
						The payee should ensure they receive the money in MPESA, Airtel Money, or Bank before confirmation. 
						We will not be liable for any loss. Read our 
						<a href="https://www.autobidder.live/terms-and-conditions" class="text-white" style="text-decoration: underline;">Terms & Conditions</a>
					</div>
					<p class="step-description">
						If the payee is paired with payer(s) who fail to pay, the payee will be automatically matched with other payer(s) 
						once they become available in the market on a priority basis.
					</p>
				</div>
			</div>
		</div>
	</div>

	<!-- FAQ Section -->
	<div class="row">
		<div class="col-12">
			<div class="section-title">
				<h2>{{ $policy->heading_two }}</h2>
				<p>Get answers to common questions about our platform</p>
			</div>
		</div>
	</div>

	<div class="row mb-5">
		<div class="col-lg-10 offset-lg-1">
			<div class="faq-container">
				<div class="faq-item">
					<button class="faq-question" type="button">
						<span>Where does the profit & Referral come from?</span>
						<i class="ri-add-line faq-icon"></i>
					</button>
					<div class="faq-answer">
						<p>The system adopts a crowdfunding model, where investor resources are re-invested into the market.</p>
					</div>
				</div>
				
				<div class="faq-item">
					<button class="faq-question" type="button">
						<span>How do you benefit?</span>
						<i class="ri-add-line faq-icon"></i>
					</button>
					<div class="faq-answer">
						<p>We engage in trading activity like any other investor in the platform.</p>
					</div>
				</div>
				
				<div class="faq-item">
					<button class="faq-question" type="button">
						<span>What are the chances of losing your investment?</span>
						<i class="ri-add-line faq-icon"></i>
					</button>
					<div class="faq-answer">
						<p>There are minimal chances of losing your investment. We do not hold money in our system. Money is transacted directly between the buyer and seller. Any loss due to system failure/market crash will be evaluated and genuine cases will have their investment reimbursed.</p>
					</div>
				</div>
				
				<div class="faq-item">
					<button class="faq-question" type="button">
						<span>Are we required to pay taxes?</span>
						<i class="ri-add-line faq-icon"></i>
					</button>
					<div class="faq-answer">
						<p>Investors may be required to pay taxes in compliance with the laws & regulations of a jurisdiction.</p>
					</div>
				</div>
				
				<div class="faq-item">
					<button class="faq-question" type="button">
						<span>What commission do you charge per transaction?</span>
						<i class="ri-add-line faq-icon"></i>
					</button>
					<div class="faq-answer">
						<p>All transactions are free. We do not ask for money from investors either to trade or to signup. Report any suspicious activity by contacting us through our <a href="https://www.autobidder.live/support" target="_blank">contact form</a>.</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
	// FAQ Toggle Functionality
	document.addEventListener('DOMContentLoaded', function() {
		const faqQuestions = document.querySelectorAll('.faq-question');
		
		faqQuestions.forEach(question => {
			question.addEventListener('click', function() {
				const answer = this.nextElementSibling;
				const isActive = this.classList.contains('active');
				
				// Close all other FAQ items
				faqQuestions.forEach(q => {
					q.classList.remove('active');
					q.nextElementSibling.classList.remove('active');
				});
				
				// Toggle current item
				if (!isActive) {
					this.classList.add('active');
					answer.classList.add('active');
				}
			});
		});
	});
	</script>
@endsection
