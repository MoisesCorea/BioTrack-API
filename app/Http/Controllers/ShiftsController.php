<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Shifts;
use Illuminate\Support\Facades\Validator; 


class ShiftsController extends Controller
{
    public function index()
    {
        $shifts = Shifts::all();
        return $this->successResponse($shifts);
    }

    public function show($id)
    {
        $shift = Shifts::find($id);

        if (!$shift) {
            return $this->errorResponse('Shift not found.', 404);
        }

        return $this->successResponse($shift);
    }

        public function store(\App\Http\Requests\StoreShiftRequest $request)
        {
            $shift = Shifts::create([
                'name' => $request->name,
                'entry_time'  => $request->entry_time,
                'finish_time'  => $request->finish_time,
                'shift_duration'  => $request->shift_duration,
                'monthly_late_allowance'  => $request->monthly_late_allowance,
                'days' => json_encode($request->days)
            ]);

            return $this->successResponse($shift, 'Shift created successfully', 201);
        }

        public function update(\App\Http\Requests\UpdateShiftRequest $request, $id)
    {
        $shift = Shifts::find($id);

        if (!$shift) {
            return $this->errorResponse('Shift not found.', 404);
        }

        $shift->name = $request->name;
        $shift->entry_time  = $request->entry_time;
        $shift->finish_time  = $request->finish_time;
        $shift->shift_duration  = $request->shift_duration;
        $shift->monthly_late_allowance  = $request->monthly_late_allowance;
        $shift->days = $request->days;
        $shift->save();

        return $this->successResponse($shift, 'Shift updated successfully');
    }


    public function destroy($id)
    {
        $shift = Shifts::find($id);

        if (!$shift) {
            return $this->errorResponse('Shift not found.', 404);
        }

        $rowsAffected = Shifts::destroy($id);

        return $this->successResponse(['affected' => $rowsAffected], 'Shift deleted successfully');
    }

}
