<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penugasan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PenugasanController extends Controller
{
    public function index()
    {
        $data = Penugasan::with('user')
            ->orderBy('tanggal','desc')
            ->get();

        foreach($data as $i => $row){

            $row->no = $i + 1;

        }

        return response()->json([
            'status'=>true,
            'data'=>$data
        ]);
    }

    public function user()
    {
        $data = User::where('level','!=','Admin')
                ->orderBy('name')
                ->get();

        return response()->json([
            'status'=>true,
            'data'=>$data
        ]);
    }

    public function show($id)
    {
        $data = Penugasan::with('user')->find($id);

        if(!$data){

            return response()->json([
                'status'=>false,
                'message'=>'Data tidak ditemukan'
            ],404);

        }

        return response()->json([
            'status'=>true,
            'data'=>$data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([

            'id_user' => 'required',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            // 'status_penugasan' => 'required',
            'keterangan' => 'nullable'

        ]);

        Penugasan::create([

            'id_user'=>$request->id_user,

            'tanggal'=>$request->tanggal,

            'jam_mulai'=>$request->jam_mulai,

            'jam_selesai'=>$request->jam_selesai,

            'status_penugasan'=>'Aktif',

            'keterangan'=>$request->keterangan,

            'created_by'=>Auth::id()

        ]);

        return response()->json([

            'status'=>true,

            'message'=>'Penugasan berhasil ditambahkan'

        ]);
    }

    public function update(Request $request,$id)
    {
        $data = Penugasan::find($id);

        if(!$data){

            return response()->json([
                'status'=>false,
                'message'=>'Data tidak ditemukan'
            ],404);

        }

        $request->validate([
            'id_user' => 'required',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'status_penugasan' => 'required',
            'keterangan' => 'nullable'
        ]);

        $data->update([

            'id_user'=>$request->id_user,

            'tanggal'=>$request->tanggal,

            'jam_mulai'=>$request->jam_mulai,

            'jam_selesai'=>$request->jam_selesai,

            'status_penugasan'=>$request->status_penugasan,

            'keterangan'=>$request->keterangan

        ]);

        return response()->json([

            'status'=>true,

            'message'=>'Data berhasil diupdate'

        ]);
    }

    public function destroy($id)
    {
        $data = Penugasan::find($id);

        if(!$data){

            return response()->json([
                'status'=>false,
                'message'=>'Data tidak ditemukan'
            ],404);

        }

        $data->delete();

        return response()->json([

            'status'=>true,

            'message'=>'Data berhasil dihapus'

        ]);
    }

    public function status()
    {
        $user = Auth::user();

        $tanggal = Carbon::today()->toDateString();

        $penugasan = Penugasan::where('id_user', $user->id)
            ->where('tanggal', $tanggal)
            ->where('status_penugasan', 'Aktif')
            ->first();

        if (!$penugasan) {

            return response()->json([
                'status' => false,
                'message' => 'Tidak ada penugasan aktif'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $penugasan->id,
                'tanggal' => $penugasan->tanggal,
                'jam_mulai' => $penugasan->jam_mulai,
                'jam_selesai' => $penugasan->jam_selesai,
                'keterangan' => $penugasan->keterangan,
                'status_penugasan' => $penugasan->status_penugasan
            ]
        ]);
    }
}