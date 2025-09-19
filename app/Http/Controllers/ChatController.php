<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use App\Models\UserShare;
use App\Models\ChatSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            // Check if chat is enabled
            if (!ChatSetting::isChatEnabled()) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Chat system is currently disabled'], 403);
                }
                abort(403, 'Chat system is currently disabled');
            }
            
            // Only allow users with role_id = 2 (traders)
            if (Auth::user()->role_id !== 2) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    /**
     * Get all conversations for the authenticated user
     */
    public function getConversations()
    {
        $user = Auth::user();
        
        $conversations = Conversation::forUser($user->id)
            ->with([
                'buyerShare.user:id,name,username,avatar',
                'sellerShare.user:id,name,username,avatar',
                'messages' => function ($query) {
                    $query->latest()->limit(1)->with('sender:id,name');
                },
                'userSharePair.trade:id,name'
            ])
            ->get();

        // Transform data for frontend
        $formattedConversations = $conversations->map(function ($conversation) use ($user) {
            $otherParticipant = $conversation->getOtherParticipant($user->id);
            $lastMessage = $conversation->messages->first();
            
            // Count unread messages
            $unreadCount = $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->whereDoesntHave('messageReads', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->count();

            return [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'trade_name' => $conversation->userSharePair->trade->name ?? 'Unknown Trade',
                'other_participant' => [
                    'id' => $otherParticipant->id,
                    'name' => $otherParticipant->name,
                    'username' => $otherParticipant->username,
                    'avatar' => $otherParticipant->avatar_url
                ],
                'last_message' => $lastMessage ? [
                    'message' => $lastMessage->message,
                    'created_at' => $lastMessage->created_at->diffForHumans(),
                    'sender_name' => $lastMessage->sender->name
                ] : null,
                'unread_count' => $unreadCount,
                'updated_at' => $conversation->updated_at
            ];
        });

        return response()->json([
            'success' => true,
            'conversations' => $formattedConversations
        ]);
    }

    /**
     * Get messages for a specific conversation
     */
    public function getMessages($conversationId)
    {
        $user = Auth::user();
        
        $conversation = Conversation::findOrFail($conversationId);
        
        // Check if user can access this conversation
        if (!$conversation->canUserAccess($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with(['sender:id,name,avatar', 'messageReads'])
            ->paginate(50);

        // Mark messages as read by current user
        $unreadMessages = $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereDoesntHave('messageReads', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();

        foreach ($unreadMessages as $message) {
            $message->markAsReadBy($user->id);
        }

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'other_participant' => $conversation->getOtherParticipant($user->id)
            ],
            'messages' => $messages
        ]);
    }

    /**
     * Send a message in a conversation
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $user = Auth::user();
        
        // Get chat settings for character limit
        $chatSettings = ChatSetting::getSettings();
        $characterLimit = $chatSettings['message_character_limit'];
        
        $validator = Validator::make($request->all(), [
            'message' => 'required_without:file|string|max:' . $characterLimit,
            'file' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $conversation = Conversation::findOrFail($conversationId);
        
        // Check if user can access this conversation
        if (!$conversation->canUserAccess($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if conversation is still active
        if (!$conversation->isActive()) {
            return response()->json(['error' => 'This conversation has ended'], 403);
        }

        $messageData = [
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->message ?: '',
            'type' => 'text'
        ];

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('chat_files', $filename, 'public');
            
            $messageData['file_path'] = $path;
            $messageData['file_name'] = $file->getClientOriginalName();
            $messageData['type'] = in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']) ? 'image' : 'file';
            
            if (empty($messageData['message'])) {
                $messageData['message'] = 'Sent a ' . $messageData['type'];
            }
        }

        $message = $conversation->messages()->create($messageData);
        
        // Update conversation updated_at
        $conversation->touch();

        // Load relationships for response
        $message->load('sender:id,name,avatar');

        // Here you would broadcast the message for real-time updates
        // broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($conversationId)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);
        
        if (!$conversation->canUserAccess($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $unreadMessages = $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereDoesntHave('messageReads', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();

        foreach ($unreadMessages as $message) {
            $message->markAsReadBy($user->id);
        }

        return response()->json([
            'success' => true,
            'marked_count' => $unreadMessages->count()
        ]);
    }

    /**
     * Get chat interface view
     */
    public function index()
    {
        $user = Auth::user();
        
        // Only allow traders (role_id = 2)
        if ($user->role_id !== 2) {
            abort(403, 'Unauthorized');
        }

        $pageTitle = 'Chat';
        return view('user-panel.chat', compact('pageTitle'));
    }

    /**
     * Auto-create conversations when shares are paired
     * This should be called from the pairing system
     */
    public static function createConversationForPair(UserSharePair $sharePair)
    {
        // Check if conversation already exists
        $existingConversation = Conversation::where('user_share_pair_id', $sharePair->id)->first();
        
        if (!$existingConversation) {
            // Create new conversation
            $conversation = Conversation::createForPairedShares($sharePair);
            
            // Here you could broadcast an event to notify users
            // broadcast(new ConversationStarted($conversation));
            
            return $conversation;
        }
        
        return $existingConversation;
    }

    /**
     * End conversations when both shares are completed
     * This should be called from the completion system
     */
    public static function endConversationsForCompletedShares($shareId)
    {
        // Find conversations where this share is involved
        $conversations = Conversation::where('buyer_share_id', $shareId)
            ->orWhere('seller_share_id', $shareId)
            ->where('status', 'active')
            ->get();

        foreach ($conversations as $conversation) {
            if ($conversation->checkAndEndIfCompleted()) {
                // Here you could broadcast an event to notify users
                // broadcast(new ConversationEnded($conversation));
            }
        }
    }

    /**
     * Get unread messages count
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        $count = $user->getUnreadMessagesCount();
        
        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Get chat settings for frontend
     */
    public function getChatSettings()
    {
        $settings = ChatSetting::getSettings();
        
        return response()->json([
            'success' => true,
            'settings' => [
                'is_enabled' => $settings['is_enabled'],
                'message_character_limit' => $settings['message_character_limit'],
                'allow_file_upload' => $settings['allow_file_upload']
            ]
        ]);
    }
}
