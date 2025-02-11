<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventTheme;

class EventController extends Controller
{

    public function index(){
        $events = Event::orderBy('evt_datetime', 'asc')->get();

        $eventCollection = EventResource::collection($events);

        return response()->json([
            'events' => $eventCollection,
            'count' => $eventCollection->count(),
        ]);
    }

    public function presentFuturOnly(){
        $currentDate = now()->startOfDay(); // Récupère le début de la journée actuelle
        
        $events = Event::where('evt_datetime', '>=', $currentDate)
                        ->orderBy('evt_datetime', 'asc')
                        ->get();
    
        $eventCollection = EventResource::collection($events);
    
        return response()->json([
            'events' => $eventCollection,
            'count' => $eventCollection->count(),
        ]);
    }

    public function getById($id)
    {
        $succes = Event::findOrFail($id);
        return new EventResource($succes);
    }

    public function getTitleById($id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['error' => 'Événement non trouvé.'], 404);
        }
        return response()->json(['title' => $event->evt_name]);
    }

    public function store(Request $request)
    {
        try {


            $validatedData = $request->validate([
                'evt_name' => 'required|string',
                'evt_description' => 'required|string',
                'evt_datetime' => 'required|date',
                
                'evthm_id' => 'required_without:newthem.evthm_name|integer',
                'newthem.evthm_name' => 'required_without:evthm_id|string',
            ]);
    
            if (isset($validatedData['newthem'])) {          
                $newThem = new EventTheme();
                $newThem->evthm_name = $validatedData['newthem']['evthm_name'];
                $newThem->save();
            
                $themId = $newThem->evthm_id;
            } else {
                $themId = $validatedData['evthm_id'];
            }

            $event = new Event();
            $event->evt_name = $validatedData['evt_name'];
            $event->evt_description = $validatedData['evt_description'];
            $event->evt_datetime = $validatedData['evt_datetime'];
            $event->evthm_id = $themId;
            $event->save();

    
            return response()->json(['status' => 201, 'message' => 'Évènement ajouté avec succès', 'event' => $event]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de l\'évènement.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function deleteById($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Évènement non trouvé.'], 404);
        }

        $event->delete();

        return response()->json(['status' => 202, 'message' => 'Évènement supprimé avec succès.']);
    }

    public function put(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'evt_id' => 'required|integer',
                'evthm_id' => 'required|integer',
                'evt_name' => 'required|string',
                'evt_description' => 'required|string',
                'evt_datetime' => 'required|date',
            ]);
            $id = $validatedData['evt_id'];

            $event = Event::find($id);
            $event->evt_name = $validatedData['evt_name'];
            $event->evthm_id = $validatedData['evthm_id'];
            $event->evt_description = $validatedData['evt_description'];
            $event->evt_datetime = $validatedData['evt_datetime'];
            $event->save();
    
    
            return response()->json(['status' => 200, 'message' => 'Évènement modifié avec succès']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la modification de l\'évènement.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
