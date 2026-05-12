<?php
namespace App\Http\Controllers\Notifications;

use App\Services\NotificationService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NotificationController extends BaseController
{
    use AuthorizesRequests;

    protected $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->getMesNotifications();
        return response()->json([
            'message'       => 'Notifications récupérées avec succès',
            'notifications' => $data['notifications'],
            'unread_count'  => $data['unread_count'],
        ], 200);
    }

    public function marquerLue(string $id)
    {
        $notification = $this->service->marquerCommeLue($id);
        return response()->json([
            'message'      => 'Notification marquée comme lue',
            'notification' => $notification,
        ], 200);
    }

    public function marquerToutesLues()
    {
        $this->service->marquerToutesCommeLues();
        return response()->json([
            'message' => 'Toutes les notifications ont été marquées comme lues',
        ], 200);
    }

    public function supprimer(string $id)
    {
        $this->service->supprimer($id);
        return response()->json([
            'message' => 'Notification supprimée avec succès',
        ], 200);
    }
}