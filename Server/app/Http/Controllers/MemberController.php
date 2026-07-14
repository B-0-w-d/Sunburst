<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController
{
    public function index(Request $request)
    {
        $filters = array_filter([
            'role' => $request->query('role'),
            'status' => $request->query('status'),
            'instrument' => $request->query('instrument'),
        ]);

        $sort = [
            'sortBy'    => $request->query('sortBy'),
            'sortOrder' => $request->query('sortOrder', 'asc'),
        ];

        $list = Member::withFilters($filters, $sort)->get();

        // Dynamically alphabetize internal instrument array values for the API response
        $list->transform(function ($member) {
            if (isset($member->instrument) && is_array($member->instrument)) {
                $instruments = $member->instrument;
                sort($instruments);
                $member->instrument = array_values($instruments);
            }
            return $member;
        });

        return response()->json([
            'status' => 'success',
            'count'  => $list->count(),
            'data'   => $list
        ], 200);
    }

    public function store(Request $request)
    {
        if (!$request->has(['name', 'email']) || empty($request->name) || empty($request->email)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Name and Email are strictly required.'
            ], 400);
        }

        $member = Member::create($request->all());

        if ($member) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Added successfully!'
            ], 201);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Database write failed.'
        ], 500);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        if (empty($data)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No data provided for update.'
            ], 400);
        }

        $member = Member::find($id);
        if (!$member) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Member not found.'
            ], 404);
        }

        if ($member->update($data)) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Member updated successfully.'
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to update member.'
        ], 500);
    }

    public function destroy($id)
    {
        $member = Member::find($id);
        if (!$member || !$member->delete()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Member not found or already deleted.'
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Member deleted successfully.'
        ]);
    }
}
