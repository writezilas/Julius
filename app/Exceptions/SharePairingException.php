<?php

namespace App\Exceptions;

use Exception;

class SharePairingException extends Exception
{
    protected $code = 422;
    
    public function __construct($message = "Share pairing failed", $code = 422, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        \Log::error('Share Pairing Error: ' . $this->getMessage(), [
            'exception' => $this,
            'trace' => $this->getTraceAsString()
        ]);
        
        return false;
    }
    
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Share Pairing Failed',
                'message' => $this->getMessage(),
            ], $this->code);
        }
        
        toastr()->error($this->getMessage());
        return redirect()->back()->withInput();
    }
}
