<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Market extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the configured application timezone
     */
    public static function getAppTimezone()
    {
        return get_gs_value('app_timezone') ?? config('app.timezone', 'UTC');
    }

    /**
     * Get open time in the application timezone
     */
    public function getOpenTimeInAppTimezone()
    {
        $appTimezone = self::getAppTimezone();
        $today = Carbon::today($appTimezone);
        
        return Carbon::createFromFormat('H:i', $this->open_time, $appTimezone)
            ->setDateFrom($today);
    }

    /**
     * Get close time in the application timezone
     */
    public function getCloseTimeInAppTimezone()
    {
        $appTimezone = self::getAppTimezone();
        $today = Carbon::today($appTimezone);
        
        return Carbon::createFromFormat('H:i', $this->close_time, $appTimezone)
            ->setDateFrom($today);
    }

    /**
     * Check if the market is currently open
     */
    public function isOpen()
    {
        $now = Carbon::now(self::getAppTimezone());
        $open = $this->getOpenTimeInAppTimezone();
        $close = $this->getCloseTimeInAppTimezone();

        return $now->between($open, $close);
    }

    /**
     * Get the next opening time for this market
     */
    public function getNextOpenTime()
    {
        $appTimezone = self::getAppTimezone();
        $now = Carbon::now($appTimezone);
        $open = $this->getOpenTimeInAppTimezone();
        
        // If current time is before today's opening time, return today's opening time
        if ($now->lt($open)) {
            return $open;
        }
        
        // Otherwise, return tomorrow's opening time
        return $open->addDay();
    }

    /**
     * Format time for display in the admin interface
     */
    public function getFormattedOpenTime()
    {
        return $this->getOpenTimeInAppTimezone()->format('H:i');
    }

    /**
     * Format time for display in the admin interface
     */
    public function getFormattedCloseTime()
    {
        return $this->getCloseTimeInAppTimezone()->format('H:i');
    }
}
