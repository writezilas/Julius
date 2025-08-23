<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPaymentFailure;
use App\Models\UserShare;
use App\Models\Trade;
use App\Services\PaymentFailureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspensionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $paymentFailureService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'status' => 'fine'
        ]);
        
        $this->paymentFailureService = new PaymentFailureService();
    }

    /** @test */
    public function test_user_gets_suspended_after_three_consecutive_failures()
    {
        // Create 3 consecutive payment failures
        for ($i = 1; $i <= 3; $i++) {
            $result = $this->paymentFailureService->handlePaymentFailure(
                $this->user->id, 
                "Test failure #{$i}"
            );
            
            if ($i < 3) {
                $this->assertFalse($result['suspended'], "User should not be suspended on failure #{$i}");
            } else {
                $this->assertTrue($result['suspended'], "User should be suspended on failure #{$i}");
                $this->assertNotNull($result['suspension_until']);
            }
        }

        // Verify user is suspended
        $this->user->refresh();
        $this->assertTrue($this->user->isSuspended());
        $this->assertEquals('suspend', $this->user->status);
        $this->assertNotNull($this->user->suspension_until);
    }

    /** @test */
    public function test_running_shares_are_paused_during_suspension()
    {
        // Create a running share for the user
        $trade = Trade::factory()->create();
        $share = UserShare::factory()->create([
            'user_id' => $this->user->id,
            'trade_id' => $trade->id,
            'status' => 'completed',
            'is_ready_to_sell' => 0,
            'start_date' => now()->subHour(),
            'timer_paused' => false,
        ]);

        // Trigger suspension
        for ($i = 1; $i <= 3; $i++) {
            $this->paymentFailureService->handlePaymentFailure($this->user->id, "Failure #{$i}");
        }

        // Check that the share timer is paused
        $share->refresh();
        $this->assertTrue($share->timer_paused);
        $this->assertNotNull($share->timer_paused_at);
    }

    /** @test */
    public function test_payment_failures_reset_on_successful_payment()
    {
        // Create 2 failures first
        for ($i = 1; $i <= 2; $i++) {
            $this->paymentFailureService->handlePaymentFailure($this->user->id, "Failure #{$i}");
        }

        $paymentFailure = $this->user->getCurrentPaymentFailure();
        $this->assertEquals(2, $paymentFailure->consecutive_failures);

        // Reset failures (simulate successful payment)
        $this->paymentFailureService->resetPaymentFailures($this->user->id);

        $paymentFailure->refresh();
        $this->assertEquals(0, $paymentFailure->consecutive_failures);
        $this->assertNull($paymentFailure->last_failure_at);
    }

    /** @test */
    public function test_suspension_expires_and_user_is_reactivated()
    {
        // Manually suspend user with past suspension time
        $this->user->update([
            'status' => 'suspend',
            'suspension_until' => now()->subMinute() // Expired 1 minute ago
        ]);

        // Check if suspension has expired
        $expired = $this->user->checkSuspensionExpiry();
        
        $this->assertTrue($expired);
        $this->user->refresh();
        $this->assertEquals('fine', $this->user->status);
        $this->assertNull($this->user->suspension_until);
    }

    /** @test */
    public function test_paused_shares_resume_after_suspension_lift()
    {
        // Create a share and pause it
        $trade = Trade::factory()->create();
        $share = UserShare::factory()->create([
            'user_id' => $this->user->id,
            'trade_id' => $trade->id,
            'status' => 'completed',
            'is_ready_to_sell' => 0,
            'timer_paused' => true,
            'timer_paused_at' => now()->subHour(),
            'paused_duration_seconds' => 0,
        ]);

        // Manually suspend then lift suspension
        $this->user->update(['status' => 'suspend']);
        $this->user->liftSuspension();

        // Check that share timer is resumed
        $share->refresh();
        $this->assertFalse($share->timer_paused);
        $this->assertNull($share->timer_paused_at);
        $this->assertGreaterThan(0, $share->paused_duration_seconds);
    }

    /** @test */
    public function test_suspension_middleware_redirects_suspended_users()
    {
        // Suspend the user
        $this->user->suspendForPaymentFailures();

        // Try to access a protected route
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertRedirect(route('suspended'));
    }

    /** @test */
    public function test_suspension_statistics_are_accurate()
    {
        // Create some test data
        $user2 = User::factory()->create(['status' => 'fine']);
        
        // User 1: 2 failures (approaching suspension)
        for ($i = 1; $i <= 2; $i++) {
            $this->paymentFailureService->handlePaymentFailure($this->user->id, "Failure #{$i}");
        }
        
        // User 2: Suspended
        for ($i = 1; $i <= 3; $i++) {
            $this->paymentFailureService->handlePaymentFailure($user2->id, "Failure #{$i}");
        }

        $stats = $this->paymentFailureService->getFailureStatistics();

        $this->assertEquals(1, $stats['users_approaching_suspension']);
        $this->assertEquals(1, $stats['currently_suspended']);
        $this->assertEquals(5, $stats['total_failures_today']); // 2 + 3 failures
    }

    /** @test */
    public function test_adjusted_share_timer_calculation()
    {
        $trade = Trade::factory()->create();
        $share = UserShare::factory()->create([
            'user_id' => $this->user->id,
            'trade_id' => $trade->id,
            'start_date' => now()->subDays(5),
            'period' => 7, // 7 days
            'paused_duration_seconds' => 3600, // 1 hour paused
            'timer_paused' => false,
        ]);

        $timerInfo = $this->paymentFailureService->getAdjustedShareTimer($share);

        $this->assertNotNull($timerInfo['original_end_time']);
        $this->assertNotNull($timerInfo['adjusted_end_time']);
        $this->assertEquals(3600, $timerInfo['paused_duration']);
        
        // Adjusted end time should be 1 hour later than original
        $this->assertEquals(
            3600,
            $timerInfo['adjusted_end_time']->diffInSeconds($timerInfo['original_end_time'])
        );
    }
}
