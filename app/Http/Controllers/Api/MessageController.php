<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\ChatbotResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Get messages for current user (Guest view)
    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $request->query('type', 'support');

        // If admin, we use a different endpoint to get conversations
        if ($user->role === 'admin') {
            return $this->getAdminConversations($request);
        }

        // Guest: get conversation with Admin
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
             return response()->json([], 200);
        }

        $isChatbot = ($type === 'chatbot');

        // FIXED: Proper SQL grouping to avoid OR/AND precedence issues
        $messages = Message::where('is_chatbot', $isChatbot)
            ->where(function($q) use ($user, $admin) {
                $q->where(function($inner) use ($user, $admin) {
                    $inner->where('sender_id', $user->id)
                          ->where('receiver_id', $admin->id);
                })->orWhere(function($inner) use ($user, $admin) {
                    $inner->where('sender_id', $admin->id)
                          ->where('receiver_id', $user->id);
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    // Admin: Get list of recent conversations
    private function getAdminConversations(Request $request)
    {
        $adminId = Auth::id();
        $type = $request->query('type', 'support');

        if ($type === 'suspended') {
            $users = User::where('role', '!=', 'admin')
                ->where('is_suspended', true)
                ->get();
            $userIds = $users->pluck('id');
            $isChatbot = false; // Default for suspended view or handle both? Support is safer.
        } else {
            $isChatbot = ($type === 'chatbot');

            // Get IDs of users who have sent/received messages to/from admin (filtered by type)
            $userIds = Message::where('is_chatbot', $isChatbot)
                ->where(function($q) use ($adminId) {
                    $q->where('sender_id', $adminId)
                      ->orWhere('receiver_id', $adminId);
                })
                ->get()
                ->map(function($msg) use ($adminId) {
                    return $msg->sender_id === $adminId ? $msg->receiver_id : $msg->sender_id;
                })
                ->unique();
        }

        $users = User::whereIn('id', $userIds)->get()->map(function($user) use ($adminId, $isChatbot) {
             // FIXED: Proper grouping for lastMessage query
             $lastMessage = Message::where('is_chatbot', $isChatbot)
                ->where(function($q) use ($user, $adminId) {
                    $q->where(function($inner) use ($user, $adminId) {
                        $inner->where('sender_id', $user->id)->where('receiver_id', $adminId);
                    })->orWhere(function($inner) use ($user, $adminId) {
                        $inner->where('sender_id', $adminId)->where('receiver_id', $user->id);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->first();

             $unreadCount = Message::where('sender_id', $user->id)
                ->where('receiver_id', $adminId)
                ->where('is_read', false)
                ->where('is_chatbot', $isChatbot)
                ->count();

             $user->last_message = $lastMessage;
             $user->unread_count = $unreadCount;
             return $user;
        })->sortByDesc(function($user) {
            return $user->last_message ? $user->last_message->created_at : 0;
        })->values();

        return response()->json($users);
    }

    // Admin: Get messages with specific user
    public function show(Request $request, $userId)
    {
        $adminId = Auth::id();
        $type = $request->query('type', 'support');
        $isChatbot = ($type === 'chatbot');

        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // FIXED: Proper SQL grouping
        $messages = Message::where('is_chatbot', $isChatbot)
            ->where(function($q) use ($userId, $adminId) {
                $q->where(function($inner) use ($userId, $adminId) {
                    $inner->where('sender_id', $adminId)->where('receiver_id', $userId);
                })->orWhere(function($inner) use ($userId, $adminId) {
                    $inner->where('sender_id', $userId)->where('receiver_id', $adminId);
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $adminId)
            ->where('is_read', false)
            ->where('is_chatbot', $isChatbot)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|max:5120', // 5MB max
            'receiver_id' => 'nullable|exists:users,id',
            'is_chatbot' => 'nullable|boolean'
        ]);

        $sender = Auth::user();
        $receiverId = $request->receiver_id;

        if ($sender->role === 'guest' || $sender->role === 'client') {
             $admin = User::where('role', 'admin')->first();
             if (!$admin) return response()->json(['message' => 'No admin available'], 500);
             $receiverId = $admin->id;
        } else if ($sender->role === 'admin') {
             if (!$receiverId) return response()->json(['message' => 'Receiver required'], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = '/storage/' . $request->file('image')->store('chat', 'public');
        }

        // Explicit boolean conversion: FormData sends '1' or '0' as strings
        $isChatbot = filter_var($request->is_chatbot, FILTER_VALIDATE_BOOLEAN);

        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'message' => $request->message ?? '',
            'image' => $imagePath,
            'is_read' => false,
            'is_chatbot' => $isChatbot
        ]);

        // Chatbot logic: Only trigger if coming from chatbot interface
        if ($sender->role !== 'admin' && $request->message && $isChatbot) {
            $this->handleChatbot($sender->id, $request->message, $isChatbot);
        }

        // Notify receiver if it's a guest being messaged by admin
        if ($sender->role === 'admin') {
            $receiver = User::find($receiverId);
            if ($receiver && $receiver->role !== 'admin') {
                $snippet = $request->message ?? 'Sent an image attachment';
                if (strlen($snippet) > 100) $snippet = substr($snippet, 0, 97) . '...';
                $receiver->notify(new \App\Notifications\NewMessageReceived($sender->name, $snippet));
            }
        } 
        // Notify Admin if it's a Guest sending a Support message
        else if (!$isChatbot) {
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $snippet = $request->message ?? 'Sent an image attachment';
                 if (strlen($snippet) > 100) $snippet = substr($snippet, 0, 97) . '...';
                $admin->notify(new \App\Notifications\NewMessageReceived($sender->name, $snippet));
            }
        }

        return response()->json($message, 201);
    }

    private function handleChatbot($guestId, $incomingMessage, $isChatbotSource = false)
    {
        // Strictly only respond if the message came from the chatbot interface
        if (!$isChatbotSource) return;

        $admin = User::where('role', 'admin')->first();
        if (!$admin) return;

        $rules = ChatbotResponse::where('is_active', true)->get();

        foreach ($rules as $rule) {
            $matched = false;
            
            // Support comma-separated triggers: "Time, Check, Open" becomes ["Time", "Check", "Open"]
            $triggers = array_map('trim', explode(',', $rule->trigger));

            if ($rule->match_type === 'exact') {
                // Exact match: entire message must equal ANY trigger (case-insensitive)
                foreach ($triggers as $trigger) {
                    if (strcasecmp(trim($incomingMessage), $trigger) === 0) {
                        $matched = true;
                        break;
                    }
                }
            } else {
                // Contains match: ANY trigger must appear as a WHOLE WORD (case-insensitive)
                foreach ($triggers as $trigger) {
                    if (empty($trigger)) continue;
                    // Using word boundaries \b to ensure "Time" doesn't match "Sometimes"
                    $pattern = '/\b' . preg_quote($trigger, '/') . '\b/i';
                    if (preg_match($pattern, $incomingMessage)) {
                        $matched = true;
                        break;
                    }
                }
            }

            if ($matched) {
                $quickReplies = $rule->suggested_triggers 
                    ? array_filter(array_map('trim', explode(',', $rule->suggested_triggers))) 
                    : null;

                Message::create([
                    'sender_id' => $admin->id,
                    'receiver_id' => $guestId,
                    'message' => $rule->response,
                    'quick_replies' => $quickReplies,
                    'is_read' => false,
                    'is_chatbot' => true
                ]);
                
                return; // Stop after first match
            }
        }

        // Fallback: If no keywords matched
        $guest = User::find($guestId);
        $firstName = $guest ? explode(' ', $guest->name)[0] : 'there';

        Message::create([
            'sender_id' => $admin->id,
            'receiver_id' => $guestId,
            'message' => "Hi {$firstName}! I didn't quite catch that. Could you try asking about our **WiFi**, **Price**, or **Location**? You can also switch to **Live Chat** to speak with our staff.",
            'is_read' => false,
            'is_chatbot' => true
        ]);
    }

    public function markAsRead(Request $request, $senderId) {
        $userId = Auth::id();
        $type = $request->query('type', 'support');

        Message::where('sender_id', $senderId)
               ->where('receiver_id', $userId)
               ->where('is_chatbot', $type === 'chatbot')
               ->update(['is_read' => true]);
        
        return response()->json(['message' => 'Marked as read']);
    }

    public function getUnreadCount(Request $request)
    {
        $userId = Auth::id();
        $type = $request->query('type');

        if ($type) {
            $count = Message::where('receiver_id', $userId)
                            ->where('is_read', false)
                            ->where('is_chatbot', $type === 'chatbot')
                            ->count();
            return response()->json(['count' => $count]);
        }

        $supportCount = Message::where('receiver_id', $userId)
                        ->where('is_read', false)
                        ->where('is_chatbot', false)
                        ->count();

        $chatbotCount = Message::where('receiver_id', $userId)
                        ->where('is_read', false)
                        ->where('is_chatbot', true)
                        ->count();
        
        return response()->json([
            'support' => $supportCount,
            'chatbot' => $chatbotCount,
            'total' => $supportCount + $chatbotCount,
            'count' => $supportCount + $chatbotCount
        ]);
    }

    public function markAllFromAdminAsRead(Request $request) {
        $userId = Auth::id(); // Guest
        $type = $request->query('type', 'support');
        
        $admin = User::where('role', 'admin')->first();
        if (!$admin) return response()->json(['message' => 'Admin not found'], 404);

        Message::where('sender_id', $admin->id)
               ->where('receiver_id', $userId)
               ->where('is_chatbot', $type === 'chatbot')
               ->update(['is_read' => true]);
        
        return response()->json(['message' => 'Messages marked as read']);
    }

    public function destroy(Request $request, $userId)
    {
        $adminId = Auth::id();
        $type = $request->query('type', 'support');
        $isChatbot = ($type === 'chatbot');

        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete all messages between these two users for the given type
        Message::where('is_chatbot', $isChatbot)
            ->where(function($q) use ($userId, $adminId) {
                $q->where(function($inner) use ($userId, $adminId) {
                    $inner->where('sender_id', $adminId)->where('receiver_id', $userId);
                })->orWhere(function($inner) use ($userId, $adminId) {
                    $inner->where('sender_id', $userId)->where('receiver_id', $adminId);
                });
            })
            ->delete();

        return response()->json(['message' => 'Conversation deleted successfully']);
    }
}
