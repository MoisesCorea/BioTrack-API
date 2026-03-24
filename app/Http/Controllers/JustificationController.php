<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Justification;
use App\Models\Users;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class JustificationController extends Controller
{
    public function index()
    {
        $justifications = Justification::with('user')->get();
        return $this->successResponse($justifications);
    }

    public function store(\App\Http\Requests\StoreJustificationRequest $request)
    {
        $data = $request->only(['user_id', 'type', 'start_date', 'end_date', 'description']);

        if ($request->hasFile('evidence')) {
            $path = $request->file('evidence')->store('justifications/evidence', 'public');
            $data['evidence_path'] = $path;
        }

        $justification = Justification::create($data);

        return $this->successResponse($justification, 'Justification registered successfully', 201);
    }

    public function show($id)
    {
        $justification = Justification::with('user')->find($id);

        if (!$justification) {
            return $this->errorResponse('Justification not found', 404);
        }

        return $this->successResponse($justification);
    }

    public function updateStatus(\App\Http\Requests\UpdateJustificationStatusRequest $request, $id)
    {
        $justification = Justification::find($id);

        if (!$justification) {
            return $this->errorResponse('Justification not found', 404);
        }

        $justification->status = $request->status;
        $justification->save();

        return $this->successResponse($justification, 'Status updated successfully');
    }

    public function destroy($id)
    {
        $justification = Justification::find($id);

        if (!$justification) {
            return $this->errorResponse('Justification not found', 404);
        }

        if ($justification->evidence_path) {
            Storage::disk('public')->delete($justification->evidence_path);
        }

        $justification->delete();

        return $this->successResponse(null, 'Justification deleted successfully');
    }
}
