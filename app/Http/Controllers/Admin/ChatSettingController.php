<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            // Only allow admin users (role_id != 2)
            if (auth()->user()->role_id === 2) {
                return redirect()->route('user.dashboard');
            }
            return $next($request);
        });
    }

    /**
     * Display chat settings page
     */
    public function index()
    {
        $pageTitle = 'Chat System Settings';
        $settings = ChatSetting::getSettingsForAdmin();
        
        return view('admin-panel.chat-settings.index', compact('pageTitle', 'settings'));
    }

    /**
     * Update chat settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_enabled' => 'nullable|boolean',
            'chat_character_limit' => 'required|integer|min:10|max:2000',
            'chat_file_upload_enabled' => 'nullable|boolean',
            'chat_max_file_size' => 'required|integer|min:100|max:10240' // 100KB to 10MB
        ], [
            'chat_character_limit.min' => 'Character limit must be at least 10 characters.',
            'chat_character_limit.max' => 'Character limit cannot exceed 2000 characters.',
            'chat_max_file_size.min' => 'File size limit must be at least 100KB.',
            'chat_max_file_size.max' => 'File size limit cannot exceed 10MB.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Update each setting - handle checkbox values properly
            ChatSetting::set(
                'chat_enabled', 
                $request->input('chat_enabled', 0) == '1' ? '1' : '0', 
                'boolean', 
                'Enable or disable the chat system'
            );

            ChatSetting::set(
                'chat_character_limit', 
                $request->chat_character_limit, 
                'integer', 
                'Maximum character limit for chat messages'
            );

            ChatSetting::set(
                'chat_file_upload_enabled', 
                $request->input('chat_file_upload_enabled', 0) == '1' ? '1' : '0', 
                'boolean', 
                'Allow file uploads in chat'
            );

            ChatSetting::set(
                'chat_max_file_size', 
                $request->chat_max_file_size, 
                'integer', 
                'Maximum file size in KB for chat uploads'
            );

            toastr()->success('Chat settings updated successfully!');
            
        } catch (\Exception $e) {
            toastr()->error('Failed to update chat settings. Please try again.');
            \Log::error('Error updating chat settings: ' . $e->getMessage());
        }

        return redirect()->route('admin.chat-settings.index');
    }

    /**
     * Get chat settings as JSON for API
     */
    public function getSettings()
    {
        try {
            $settings = [
                'chat_enabled' => ChatSetting::isChatEnabled(),
                'character_limit' => ChatSetting::getCharacterLimit(),
                'file_upload_enabled' => ChatSetting::isFileUploadEnabled(),
                'max_file_size' => ChatSetting::getMaxFileSize()
            ];

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve chat settings'
            ], 500);
        }
    }

    /**
     * Toggle chat system on/off quickly
     */
    public function toggleChat(Request $request)
    {
        try {
            $currentStatus = ChatSetting::isChatEnabled();
            $newStatus = !$currentStatus;
            
            ChatSetting::set(
                'chat_enabled', 
                $newStatus ? '1' : '0', 
                'boolean', 
                'Enable or disable the chat system'
            );

            $message = $newStatus ? 'Chat system enabled successfully!' : 'Chat system disabled successfully!';
            toastr()->success($message);

            return response()->json([
                'success' => true,
                'enabled' => $newStatus,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            toastr()->error('Failed to toggle chat system.');
            return response()->json([
                'success' => false,
                'error' => 'Failed to toggle chat system'
            ], 500);
        }
    }

    /**
     * Reset settings to default
     */
    public function resetToDefault()
    {
        try {
            ChatSetting::set('chat_enabled', '1', 'boolean', 'Enable or disable the chat system');
            ChatSetting::set('chat_character_limit', '100', 'integer', 'Maximum character limit for chat messages');
            ChatSetting::set('chat_file_upload_enabled', '1', 'boolean', 'Allow file uploads in chat');
            ChatSetting::set('chat_max_file_size', '5120', 'integer', 'Maximum file size in KB for chat uploads');

            toastr()->success('Chat settings reset to default values!');
            
        } catch (\Exception $e) {
            toastr()->error('Failed to reset chat settings.');
            \Log::error('Error resetting chat settings: ' . $e->getMessage());
        }

        return redirect()->route('admin.chat-settings.index');
    }
}
