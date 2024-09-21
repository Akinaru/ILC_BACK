<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;

class NotificationController extends Controller
{

    public function index()
    {
        $notifications = Notification::orderBy('not_date', 'desc')->get();
        $notificationCollection = NotificationResource::collection($notifications);
        $notificationCount = $notifications->count();
    
        return response()->json([
            'notifications' => $notificationCollection,
            'count' => $notificationCount,
        ]);
    }
    
    public function getByLogin($acc_id)
    {
        // Fetch the notifications for the given account ID, ordered by date descending
        $notifications = Notification::where('not_receveur', $acc_id)
                                    ->orderBy('not_date', 'desc')
                                    ->get();
    
        // Create a resource collection
        $notificationCollection = NotificationResource::collection($notifications);
    
        // Count the total number of notifications
        $notificationCount = $notifications->count();
    
        // Count the number of notifications with not_vue set to false
        $notificationCountVue = $notifications->where('not_vue', false)->count();
    
        // Return a JSON response with the notifications, count, and countvue
        return response()->json([
            'notifications' => $notificationCollection,
            'count' => $notificationCount,
            'countvue' => $notificationCountVue,
        ]);
    }

    public function watch(Request $request)
    {
        $validatedData = $request->validate([
            'acc_id' => 'required|string'
        ]);

        Notification::where('not_receveur', $validatedData['acc_id'])
                    ->update(['not_vue' => true]);

        return response()->json(['status' => 200, 'message' => 'Notifications updated successfully']);
    }
    
    
}
