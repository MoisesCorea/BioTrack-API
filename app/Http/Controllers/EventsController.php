<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Events;
use Illuminate\Support\Facades\Validator; 

class EventsController extends Controller
{
    public function index()
    {
        $events = Events::all();
        return $this->successResponse($events);
    }

    public function show($id)
    {
        $event = Events::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        return $this->successResponse($event);
    }

        public function store(\App\Http\Requests\StoreEventRequest $request)
        {
            $event = Events::create([
                'name' => $request->name,
                'change_attendance'=> $request->change_attendance,
                'description'  => $request->description,
            ]);

            return $this->successResponse($event, 'Event created successfully', 201);
        }

        public function update(\App\Http\Requests\UpdateEventRequest $request, $id)
    {
        $event = Events::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        $event->name = $request->name;
        $event->change_attendance  = $request->change_attendance;
        $event->description  = $request->description;
        $event->save();

        return $this->successResponse($event, 'Event updated successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $event = Events::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation errors', 422, $validator->errors()->all());
        }

        $event->status = $request->get('status');

        if ($event->status && Events::where('status', true)->exists()) {
            $activeEvent = Events::where('status', true)->first();
            if ($activeEvent) {
                $activeEvent->status = false;
                $activeEvent->save();
            }
        }

        $event->save();

        return $this->successResponse($event, 'Status updated');
    }
    

    public function updateDailyAttendance(Request $request, $id)
    {
        $event = Events::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'daily_attendance' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation errors', 422, $validator->errors()->all());
        }

        $event->daily_attendance = $request->get('daily_attendance');
        $event->save();

        return $this->successResponse($event, 'Daily attendance updated');
    }

    
    public function destroy($id)
    {
        $event = Events::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found', 404);
        }

        $rowsAffected = Events::destroy($id);

        return $this->successResponse(['affected' => $rowsAffected], 'Event deleted successfully');
    }




}
