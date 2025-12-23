<?php

namespace App\Http\Controllers\Api;

use App\Models\FotoAbsensi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
class FotoAbsensiController extends Controller
{
    public function index()
    {
        return FotoAbsensi::all();
    }

    public function show($id)
    {
     $getFoto = FotoAbsensi::findoffail($id);

     return response()->json([
        'data' => $getFoto,
        'message' => 'Success',
        'status' => 200
     ]);
    }

    public function destroy($id)
    {
        FotoAbsensi::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
