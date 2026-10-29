<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;
use Validator;

class LokasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lokasi = Lokasi::orderBy("nama_tempat")->get()->map(function($nmlokasi,$index){
            return [
                'no' => $index +1,
                'id' => $nmlokasi->id,
                'nama_tempat' => $nmlokasi->nama_tempat,
                'lat' => $nmlokasi->lat,
                'lang' => $nmlokasi->lang,
                
                'absen_datang' => $nmlokasi->absen_datang,
                'absen_pulang' => $nmlokasi->absen_pulang,
                'radius' => $nmlokasi->radius,
            ];
        });

        return response()->json([
            "data" => $lokasi,
        ]);  
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_tempat' => "required",
            'lat' => "required",
            'lang' => "required",
            'absen_datang' => "required",
            'absen_pulang' => "required",
            'radius' => "required",
        ]);

        if($validator->fails()) {
            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }
        
        $lokasi = Lokasi::create([
            'nama_tempat' => $request->nama_tempat,
            'lat' => $request->lat,
            'lang' => $request->lang,
            'absen_datang' => $request->absen_datang,
            'absen_pulang' => $request->absen_pulang,
            'radius' => $request->radius,
        ]);

        return response()->json([
            "message" => "success upload"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lokasi $lokasi,$id)
    {
        $lokasi = Lokasi::where("id", $id)->first();
        if(!$lokasi) return response() ->json([
            "message" => "Lokasi not found",
        ], 404);

        return response()->json([
            "data" => $lokasi,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lokasi $lokasi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lokasi $lokasi, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_tempat' => "required",
            'lat' => "required",
            'lang' => "required",
            'absen_datang' => "required",
            'absen_pulang' => "required",
            'radius' => "required",
        ]);

        if($validator->fails()) {
            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }

        $lokasi = Lokasi::where("id", $id)->first();
        if(!$lokasi) return response() ->json([
            "message" => "Lokasi not found",
        ], 404);

        $lokasi->update([
            'nama_tempat' => $request->nama_tempat,
            'lat' => $request->lat,
            'lang' => $request->lang,
            'absen_datang' => $request->absen_datang,
            'absen_pulang' => $request->absen_pulang,
            'radius' => $request->radius,
        ]);

        return response()->json([
            "message" => "success update"
        ]);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lokasi $lokasi, $id)
    {
        $lokasi = Lokasi::find($id);
        if(!$lokasi) return response()->json([
            "message" => "lokasi not found",
        ], 404);

        $lokasi->delete();

        return response()->json([
            "message" => "success delete",
        ]);
    }
}
