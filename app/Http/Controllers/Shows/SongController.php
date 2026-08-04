<?php

namespace App\Http\Controllers\Shows;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function index()
    {
        $songs = Song::all();
        return response()->json([
            'status' => 'success',
            'data' => $songs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'default_description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $song = Song::create([
            'title' => $request->input('title'),
            'default_description' => $request->input('default_description'),
            'notes' => $request->input('notes'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm bài hát vào kho thành công!',
            'data' => $song
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $song = Song::find($id);
        if (!$song) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy bài hát.'], 404);
        }

        $song->update([
            'title' => $request->input('title', $song->title),
            'default_description' => $request->input('default_description', $song->default_description),
            'notes' => $request->input('notes', $song->notes),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật bài hát thành công!',
            'data' => $song
        ]);
    }

    public function destroy($id)
    {
        $song = Song::find($id);
        if (!$song) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy bài hát.'], 404);
        }

        $song->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa bài hát khỏi kho.'
        ]);
    }
}
