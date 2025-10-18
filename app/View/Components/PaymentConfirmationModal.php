<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PaymentConfirmationModal extends Component
{
    public $modalId;
    public $shares;
    public $pricePerShare;
    public $totalAmount;
    public $currency;
    public $recipientName;
    public $recipientUsername;
    public $recipientAvatar;
    public $mpesaNumber;
    public $submitUrl;
    public $csrfToken;

    /**
     * Create a new component instance.
     *
     * @param string $modalId
     * @param string|int $shares
     * @param string|float $pricePerShare
     * @param string|float $totalAmount
     * @param string $currency
     * @param string $recipientName
     * @param string $recipientUsername
     * @param string|null $recipientAvatar
     * @param string $mpesaNumber
     * @param string $submitUrl
     */
    public function __construct(
        $modalId = 'paymentConfirmationModal',
        $shares = '110,000',
        $pricePerShare = 'Ksh 1',
        $totalAmount = 'Ksh 110000',
        $currency = 'Ksh',
        $recipientName = 'Johanna',
        $recipientUsername = 'johana33',
        $recipientAvatar = null,
        $mpesaNumber = '7272737',
        $submitUrl = '/api/payment/submit'
    ) {
        $this->modalId = $modalId;
        $this->shares = $shares;
        $this->pricePerShare = $pricePerShare;
        $this->totalAmount = $totalAmount;
        $this->currency = $currency;
        $this->recipientName = $recipientName;
        $this->recipientUsername = $recipientUsername;
        $this->recipientAvatar = $recipientAvatar ?: $this->generateAvatarUrl($recipientName);
        $this->mpesaNumber = $mpesaNumber;
        $this->submitUrl = $submitUrl;
        $this->csrfToken = csrf_token();
    }

    /**
     * Generate avatar URL if not provided
     *
     * @param string $name
     * @return string
     */
    private function generateAvatarUrl($name)
    {
        $firstLetter = strtoupper(substr($name, 0, 1));
        return "https://ui-avatars.com/api/?name={$firstLetter}&background=28a745&color=ffffff&size=64&bold=true";
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.payment-confirmation-modal');
    }

    /**
     * Format number with commas
     *
     * @param string|int $number
     * @return string
     */
    public function formatNumber($number)
    {
        return number_format((int) str_replace(',', '', $number));
    }

    /**
     * Get the modal configuration as JSON for JavaScript
     *
     * @return string
     */
    public function getModalConfig()
    {
        return json_encode([
            'modalId' => $this->modalId,
            'submitUrl' => $this->submitUrl,
            'csrfToken' => $this->csrfToken,
            'validation' => [
                'transactionIdMinLength' => 8,
                'transactionIdMaxLength' => 15,
                'transactionIdPattern' => '^[A-Z0-9]{8,15}$'
            ],
            'mobile' => [
                'preventZoom' => true,
                'optimizeKeyboard' => true
            ]
        ]);
    }
}