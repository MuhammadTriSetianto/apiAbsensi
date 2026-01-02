<?php

namespace App\Http\Controllers\Service;

use App\Models\UserProyeks;

class CekUserProyek{

    public function cekUserProyek($id_pegawai)
    {
        $data= UserProyeks::where('id_pegawai', $id_pegawai)->first();
        return response()->json(
            [
                'status' => true,
                'data' => $data,
                'message' => 'Data ditemukan'
            ]
        );
    }
}
