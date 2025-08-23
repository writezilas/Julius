<?php

namespace App\Rules;

use App\Models\Market;
use Illuminate\Contracts\Validation\Rule;

class ValidateTimeRange implements Rule
{
    protected $marketId; // Store the ID of the current record

    public function __construct($marketId = null)
    {
        $this->marketId = $marketId;
    }

    public function passes($attribute, $value)
    {
        $openTime = request()->input('open_time');
        $closeTime = request()->input('close_time');

        // Check if the time range conflicts with other existing records
        $existingRecords = Market::where(function ($query) use ($openTime, $closeTime) {
            $query->whereBetween('open_time', [$openTime, $closeTime])
                ->orWhereBetween('close_time', [$openTime, $closeTime])
                ->orWhere(function ($query) use ($openTime, $closeTime) {
                    $query->where('open_time', '<', $openTime)
                        ->where('close_time', '>', $closeTime);
                });
        })->where('id', '<>', $this->marketId)->exists();

        return $existingRecords ? false : true;
    }

    public function message()
    {
        return 'The selected time range conflicts with existing records.';
    }
}
