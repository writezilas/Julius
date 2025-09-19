<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    /**
     * Attempt to authenticate a user with either email or username
     *
     * @param string $identifier The email or username
     * @param string $password The password
     * @param bool $remember Whether to remember the user
     * @return bool True if authentication successful
     */
    public function attemptLoginWithEmailOrUsername($identifier, $password, $remember = false)
    {
        // Determine if the identifier is an email or username
        $field = $this->isEmail($identifier) ? 'email' : 'username';
        
        // Try to authenticate with the determined field
        if (Auth::attempt([$field => $identifier, 'password' => $password], $remember)) {
            return true;
        }
        
        return false;
    }

    /**
     * Get user by email or username
     *
     * @param string $identifier The email or username
     * @return User|null
     */
    public function getUserByEmailOrUsername($identifier)
    {
        $field = $this->isEmail($identifier) ? 'email' : 'username';
        
        return User::where($field, $identifier)->first();
    }

    /**
     * Check if the given string is an email address
     *
     * @param string $identifier
     * @return bool
     */
    private function isEmail($identifier)
    {
        return filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate login credentials for either email or username
     *
     * @param array $credentials
     * @throws ValidationException
     */
    public function validateLoginCredentials($credentials)
    {
        $identifier = $credentials['login'] ?? '';
        $password = $credentials['password'] ?? '';

        // Basic validation
        if (empty($identifier)) {
            throw ValidationException::withMessages([
                'login' => ['Email or username is required.']
            ]);
        }

        if (empty($password)) {
            throw ValidationException::withMessages([
                'password' => ['Password is required.']
            ]);
        }

        // If it looks like an email, validate email format
        if ($this->isEmail($identifier)) {
            if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'login' => ['Please enter a valid email address.']
                ]);
            }
        } else {
            // Username validation (basic)
            if (strlen($identifier) < 3) {
                throw ValidationException::withMessages([
                    'login' => ['Username must be at least 3 characters long.']
                ]);
            }
        }

        // Password validation
        if (strlen($password) < 6) {
            throw ValidationException::withMessages([
                'password' => ['Password must be at least 6 characters long.']
            ]);
        }
    }
}
