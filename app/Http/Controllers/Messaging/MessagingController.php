<?php
namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagingController extends Controller
{
    public function __construct(private readonly MessagingService $messagingService) {}

    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isRh() && !$user->isCollaborateur()) {
            return response()->json([]);
        }

        return response()->json(
            $this->messagingService->getConversationsFor($user)
        );
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isRh()) {
            $request->validate(['collaborateur_id' => 'required|exists:users,id']);
            $rhId            = (int) $user->id;
            $collaborateurId = (int) $request->collaborateur_id;

        } elseif ($user->isCollaborateur()) {
            // Récupère le premier RH actif disponible
            $rh = User::whereHas('role', fn($q) => $q->where('name', 'rh'))
                ->where('active', true)
                ->first();

            if (!$rh) {
                return response()->json([
                    'message' => 'Aucun responsable RH disponible.',
                ], 422);
            }

            $rhId            = (int) $rh->id;
            $collaborateurId = (int) $user->id;

        } else {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $conversation = $this->messagingService->getOrCreateConversation($rhId, $collaborateurId);

        return response()->json(['conversation_id' => $conversation->id], 201);
    }

    public function messages(Conversation $conversation): JsonResponse
    {
        $this->authorize('access', $conversation);

        /** @var User $user */
        $user = Auth::user();

        $messages = $this->messagingService->getMessages($conversation, $user);

        return response()->json($messages);
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('access', $conversation);

        $request->validate(['body' => 'required|string|max:2000']);

        /** @var User $user */
        $user = Auth::user();

        $message = $this->messagingService->sendMessage(
            $conversation,
            $user,
            $request->body
        );

        return response()->json([
            'id'         => $message->id,
            'body'       => $message->body,
            'sender_id'  => $message->sender_id,
            'created_at' => $message->created_at,
            'read_at'    => $message->read_at,
        ], 201);
    }

    public function unreadCount(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isRh() && !$user->isCollaborateur()) {
            return response()->json(['unread' => 0]);
        }

        return response()->json([
            'unread' => $this->messagingService->totalUnreadFor($user),
        ]);
    }
    // MessagingController.php
public function searchCollaborateurs(Request $request): JsonResponse
{
    $request->validate(['q' => 'required|string|min:2|max:50']);

    /** @var User $user */
    $user = Auth::user();

    if (!$user->isRh()) {
        return response()->json(['message' => 'Accès refusé.'], 403);
    }

    return response()->json(
        $this->messagingService->searchCollaborateurs($request->query('q'))
    );
}
}