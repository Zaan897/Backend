<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;

use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Absensi::with('user')
            ->whereIn('presensi', [
                'Sakit',
                'Cuti',
                'Izin',
                'Tidak Hadir'
            ])
            ->orderBy('id', 'desc')
            ->get();

        $result = [];

        $no = 1;

        foreach($data as $item){

            $result[] = [
                'no' => $no++,
                'id' => $item->id,
                'tgl' => $item->tgl,
                'presensi' => $item->presensi,
                'keterangan_presensi' => $item->keterangan_presensi,
                'created_at' => $item->created_at,

                'user' => [
                    'id'   => optional($item->user)->id,
                    'name' => optional($item->user)->name
                ]
            ];
        }

        return response()->json([
            'data' => $result
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    //Datang
    public function store_datang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => "required|integer",
        ]);

        if($validator->fails()) {
            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }

        $result = User::with('lokasi')
            ->where('id', $request->id_user)
            ->first();

        $abs_dtng = optional($result->lokasi)->absen_datang;
        $abs_plng = optional($result->lokasi)->absen_pulang;

        $result = $result ? [
            'id' => $result->id,
            'name' => $result->name,
            'username' => $result->username,
            'jenis_kelamin' => $result->jenis_kelamin,
            'no_hp' => $result->no_hp,
            'level' => $result->level,
            'id_lokasi' => $result->id_lokasi,

            'lokasi' => [
                'nama_tempat' => optional($result->lokasi)->nama_tempat,
                'lat' => optional($result->lokasi)->lat,
                'lang' => optional($result->lokasi)->lang,
                'absen_datang' => $abs_dtng,
                'absen_pulang' => $abs_plng,
                'radius' => optional($result->lokasi)->radius,
            ]
        ] : null;

        $date = now('Asia/Kuala_Lumpur')->format('Y-m-d');
        $jam  = now('Asia/Kuala_Lumpur')->format('H:i:s');

        $keterangan_datang = 'Hadir tepat waktu';

        if ($abs_dtng && $jam) {

            // ubah ke timestamp biar lebih stabil
            $jamMasuk    = strtotime($abs_dtng);
            $jamSekarang = strtotime($jam);

            // selisih detik
            $totalDetik = $jamSekarang - $jamMasuk;

            if ($totalDetik > 0) {

                $jamTelat   = floor($totalDetik / 3600);
                $menitTelat = floor(($totalDetik % 3600) / 60);
                $detikTelat = $totalDetik % 60;

                $parts = [];

                if ($jamTelat > 0) $parts[] = $jamTelat . ' jam';
                if ($menitTelat > 0) $parts[] = $menitTelat . ' menit';
                if ($detikTelat > 0) $parts[] = $detikTelat . ' detik';

                $keterangan_datang = "Anda Terlambat " . implode(' ', $parts);
            }
        }

        
        $lpresense = Absensi::where('id_user', $request->id_user)
            ->whereDate('tgl', $date)
            ->first();

        if($lpresense) return response() ->json([
            "message" => "Anda Telah Melakukan Presensi Datang Hari Ini...",
        ], 404);

        $absensi = Absensi::create([
            'id_user' => $request->id_user,
            'tgl' => $date,
            'jam_datang' => $jam,
            'keterangan_datang' => $keterangan_datang,
            'jam_pulang' => "-",
            'keterangan_pulang' => "-",
            'presensi' => "Hadir",
            'keterangan_presensi' => "Reguler",

        ]);

        return response()->json([
            "message" => "success upload"
        ]);
    }

    //Pulang
    public function store_pulang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => "required|integer",
        ]);

        if($validator->fails()) {
            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }

        $result = User::with('lokasi')
            ->where('id', $request->id_user)
            ->first();

        $abs_dtng = optional($result->lokasi)->absen_datang;
        $abs_plng = optional($result->lokasi)->absen_pulang;

        $date = now('Asia/Kuala_Lumpur')->format('Y-m-d');
        $jam  = now('Asia/Kuala_Lumpur')->format('H:i:s');


       

        $keterangan_pulang = 'Pulang tepat waktu';


        $absensi = Absensi::where('id_user', $request->id_user)
            ->whereDate('tgl', $date)
            ->first();

        if(!$absensi) return response() ->json([
            "message" => "Anda Belum Melakukan Presensi Datang Hari Ini...",
        ], 404);

        
        if ($absensi->jam_pulang != "-" && $absensi->jam_pulang != "-") {
            return response()->json([
                "message" => "Anda sudah melakukan presensi pulang hari ini...",
            ], 406);
        }

         if ($abs_plng && $jam) {

            $jamkeluar    = strtotime($abs_plng);
            $jamSekarang = strtotime($jam);

            $totalDetik = $jamSekarang - $jamkeluar;

            if ($totalDetik < 0) {
                return response()->json([
                   "message" => "Belum Jam Pulang",
                ], 303);

            }

        }

        $absensi->update([
            'jam_pulang' => $jam,
            'keterangan_pulang' => $keterangan_pulang,
        ]);

        return response()->json([
            "message" => "success upload"
        ]);
    }

    /**
     * Display the specified resource.
     */

    public function kembali(User $user, $id){
   
        $result = User::with('lokasi')
            ->where('id', $id)
            ->first();

        $abs_dtng = optional($result->lokasi)->absen_datang;
        $abs_plng = optional($result->lokasi)->absen_pulang;

        return response()->json([
            "absen_datang" => $abs_dtng,
            "absen_pulang" => $abs_plng,
        ]);

   
    }

    public function show(Absensi $absensi, $id)
    {
        $today = Carbon::now('Asia/Kuala_Lumpur')->toDateString();

        // ambil data absensi berdasarkan id + tanggal hari ini
        $absensi = Absensi::where('id_user', $id)
            ->whereDate('tgl', $today)
            ->first();

        // jika tidak ditemukan
        if (!$absensi) {
            return response()->json([
                "message" => "Absensi not found",
            ], 404);
        }

        // response
        return response()->json([
            "data" => $absensi,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Absensi $absensi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, Absensi $absensi)
    // {
    //     //
    // }

    // //logout
    // public function logout(Request $request) {
    //     $id_user = Auth::id_user();
    //     $id_user->currentAccessToken()->delete();

    //     return response()->json([
    //         "message" => "success logout",
    //     ]);
    // } 

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $absensi = Absensi::find($id);

        if(!$absensi){
            return response()->json([
                "message" => "Presensi tidak ditemukan",
            ], 404);
        }

        $absensi->delete();

        return response()->json([
            "message" => "Berhasil menghapus presensi",
        ], 200);
    }

    public function store_penudatang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => "required|integer",
        ]);

        if($validator->fails()) {
            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }

        $result = User::with('lokasi')
            ->where('id', $request->id_user)
            ->first();

        $abs_dtng = optional($result->lokasi)->absen_datang;
        $abs_plng = optional($result->lokasi)->absen_pulang;

        $result = $result ? [
            'id' => $result->id,
            'name' => $result->name,
            'username' => $result->username,
            'jenis_kelamin' => $result->jenis_kelamin,
            'no_hp' => $result->no_hp,
            'level' => $result->level,
            'id_lokasi' => $result->id_lokasi,

            'lokasi' => [
                'nama_tempat' => optional($result->lokasi)->nama_tempat,
                'lat' => optional($result->lokasi)->lat,
                'lang' => optional($result->lokasi)->lang,
                'absen_datang' => $abs_dtng,
                'absen_pulang' => $abs_plng,
                'radius' => optional($result->lokasi)->radius,
            ]
        ] : null;

        $date = now('Asia/Kuala_Lumpur')->format('Y-m-d');
        $jam  = now('Asia/Kuala_Lumpur')->format('H:i:s');

        $keterangan_datang = 'Hadir tepat waktu';

        if ($abs_dtng && $jam) {

            // ubah ke timestamp biar lebih stabil
            $jamMasuk    = strtotime($abs_dtng);
            $jamSekarang = strtotime($jam);

            // selisih detik
            $totalDetik = $jamSekarang - $jamMasuk;

            if ($totalDetik > 0) {

                $jamTelat   = floor($totalDetik / 3600);
                $menitTelat = floor(($totalDetik % 3600) / 60);
                $detikTelat = $totalDetik % 60;

                $parts = [];

                if ($jamTelat > 0) $parts[] = $jamTelat . ' jam';
                if ($menitTelat > 0) $parts[] = $menitTelat . ' menit';
                if ($detikTelat > 0) $parts[] = $detikTelat . ' detik';

                $keterangan_datang = "Anda Terlambat " . implode(' ', $parts);
            }
        }

        
        $lpresense = Absensi::where('id_user', $request->id_user)
            ->whereDate('tgl', $date)
            ->first();

        if($lpresense) return response() ->json([
            "message" => "Anda Telah Melakukan Presensi Datang Hari Ini...",
        ], 404);

        $absensi = Absensi::create([
            'id_user' => $request->id_user,
            'tgl' => $date,
            'jam_datang' => $jam,
            'keterangan_datang' => $keterangan_datang,
            'jam_pulang' => "-",
            'keterangan_pulang' => "-",
            'presensi' => "Hadir",
            'keterangan_presensi' => "Penugasan",

        ]);

        return response()->json([
            "message" => "success upload"
        ]);
    }


    public function store_penupulang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => "required|integer",
        ]);

        if($validator->fails()) {
            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }

        $result = User::with('lokasi')
            ->where('id', $request->id_user)
            ->first();

        $abs_dtng = optional($result->lokasi)->absen_datang;
        $abs_plng = optional($result->lokasi)->absen_pulang;

        $date = now('Asia/Kuala_Lumpur')->format('Y-m-d');
        $jam  = now('Asia/Kuala_Lumpur')->format('H:i:s');


       

        $keterangan_pulang = 'Pulang tepat waktu';


        $absensi = Absensi::where('id_user', $request->id_user)
            ->whereDate('tgl', $date)
            ->first();

        if(!$absensi) return response() ->json([
            "message" => "Anda Belum Melakukan Presensi Datang Hari Ini...",
        ], 404);

        
        if ($absensi->jam_pulang != "-" && $absensi->jam_pulang != "-") {
            return response()->json([
                "message" => "Anda sudah melakukan presensi pulang hari ini...",
            ], 406);
        }

         if ($abs_plng && $jam) {

            $jamkeluar    = strtotime($abs_plng);
            $jamSekarang = strtotime($jam);

            $totalDetik = $jamSekarang - $jamkeluar;

            if ($totalDetik < 0) {
                return response()->json([
                   "message" => "Belum Jam Pulang",
                ], 303);

            }

        }

        $absensi->update([
            'jam_pulang' => $jam,
            'keterangan_pulang' => $keterangan_pulang,
            'keterangan_presensi' => "Penugasan",

        ]);

        return response()->json([
            "message" => "success upload"
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $data = Absensi::where('id_user', $user->id)
            ->orderBy('tgl', 'desc')
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function store_absence(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|integer',
            'presensi' => 'required|in:Sakit,Cuti,Izin,Tidak Hadir',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }

        $date = now('Asia/Jakarta')->format('Y-m-d');

        // cek presensi hari ini
        $cek = Absensi::where('id_user', $request->id_user)
            ->whereDate('tgl', $date)
            ->first();

        if ($cek) {
            return response()->json([
                "message" => "User sudah memiliki presensi hari ini"
            ], 409);
        }

        // simpan sakit / cuti
        Absensi::create([

            'id_user' => $request->id_user,

            'tgl' => $date,

            'jam_datang' => null,

            'keterangan_datang' => null,

            'jam_pulang' => null,

            'keterangan_pulang' => null,

            'presensi' => $request->presensi,

            'keterangan_presensi' => null,

        ]);

        return response()->json([
            "message" => "presensi " . $request->presensi . " berhasil ditambahkan"
        ]);
    }

    public function user_absence()
    {
        $data = User::where('level', '!=', 'Admin')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function laporan(Request $request)
    {
        $query = Absensi::join('users', 'users.id', '=', 'absensis.id_user')
            ->select(
                'absensis.id',
                'users.name as nama_user',
                'absensis.tgl',
                'absensis.presensi',
                'absensis.keterangan_presensi',
                'absensis.jam_datang',
                'absensis.jam_pulang',
                'absensis.keterangan_datang',
                'absensis.keterangan_pulang',
                'absensis.created_at'
            );

        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL
        |--------------------------------------------------------------------------
        */

        if($request->tanggal){

            $query->whereDate(
                'absensis.tgl',
                $request->tanggal
            );

        }

        $data = $query
            ->orderBy('absensis.tgl', 'desc')
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function laporanBulanan(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $users = User::where('level', '!=', 'Admin')->get();

        $data = [];

        foreach($users as $user){

            $hadir = Absensi::where('id_user', $user->id)
                ->whereMonth('tgl', $bulan)
                ->whereYear('tgl', $tahun)
                ->where('presensi', 'Hadir')
                ->count();

            $sakit = Absensi::where('id_user', $user->id)
                ->whereMonth('tgl', $bulan)
                ->whereYear('tgl', $tahun)
                ->where('presensi', 'Sakit')
                ->count();

            $cuti = Absensi::where('id_user', $user->id)
                ->whereMonth('tgl', $bulan)
                ->whereYear('tgl', $tahun)
                ->where('presensi', 'Cuti')
                ->count();

            $izin = Absensi::where('id_user', $user->id)
                ->whereMonth('tgl', $bulan)
                ->whereYear('tgl', $tahun)
                ->where('presensi', 'Izin')
                ->count();

            $tidak_hadir = Absensi::where('id_user', $user->id)
                ->whereMonth('tgl', $bulan)
                ->whereYear('tgl', $tahun)
                ->where('presensi', 'Tidak Hadir')
                ->count();

            $data[] = [
                'nama_user' => $user->name,
                'hadir' => $hadir,
                'sakit' => $sakit,
                'cuti' => $cuti,
                'izin' => $izin,
                'tidak_hadir' => $tidak_hadir,
            ];
        }

        return response()->json([
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|integer',
            'presensi' => 'required|in:Sakit,Cuti,Izin,Tidak Hadir',
        ]);

        if($validator->fails()){
            return response()->json([
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 422);
        }

        $absensi = Absensi::find($id);

        if(!$absensi){
            return response()->json([
                "message" => "Presensi tidak ditemukan"
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | CEK DUPLIKAT PRESENSI
        |--------------------------------------------------------------------------
        */

        $cek = Absensi::where('id_user', $request->id_user)
            ->whereDate('tgl', $absensi->tgl)
            ->where('id', '!=', $id)
            ->first();

        if($cek){
            return response()->json([
                "message" => "User sudah memiliki presensi di tanggal ini"
            ], 409);
        }

        $absensi->update([
            'id_user' => $request->id_user,
            'presensi' => $request->presensi,
        ]);

        return response()->json([
            "message" => "Berhasil update presensi"
        ], 200);
    }

    public function dashboard()
    {
        $today = now()->toDateString();

        $tidak_hadir = Absensi::whereDate('tgl', $today)
            ->where('presensi', 'Tidak Hadir')
            ->count();

        $sakit = Absensi::whereDate('tgl', $today)
            ->where('presensi', 'Sakit')
            ->count();

        $izin = Absensi::whereDate('tgl', $today)
            ->where('presensi', 'Izin')
            ->count();

        $cuti = Absensi::whereDate('tgl', $today)
            ->where('presensi', 'Cuti')
            ->count();

        $penugasan = Absensi::whereDate('tgl', $today)
            ->where('keterangan_presensi', 'Penugasan')
            ->count();

        $reguler = Absensi::whereDate('tgl', $today)
            ->where('keterangan_presensi', 'Reguler')
            ->count();

        $total_user = User::where('level', 'User')->count();

        return response()->json([

            'tidak_hadir' => $tidak_hadir,
            'sakit' => $sakit,
            'izin' => $izin,
            'cuti' => $cuti,
            'penugasan' => $penugasan,
            'reguler' => $reguler,
            'total_user' => $total_user
        ]);
    }

    public function presensiHariIni()
    {

        $today = now()->toDateString();

        $data = Absensi::with('user')
            ->where('tgl', $today)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json($data);

    }

    public function bar_grafik(){
        $tahun = date('Y');

    $data = Absensi::selectRaw("
            MONTH(tgl) as bulan,

            SUM(CASE WHEN presensi = 'Hadir' THEN 1 ELSE 0 END) as hadir,

            SUM(CASE WHEN presensi = 'Cuti' THEN 1 ELSE 0 END) as cuti,

            SUM(CASE WHEN presensi = 'Sakit' THEN 1 ELSE 0 END) as sakit,

            SUM(CASE WHEN presensi = 'Izin' THEN 1 ELSE 0 END) as izin,

            SUM(CASE WHEN presensi = 'Tidak Hadir' THEN 1 ELSE 0 END) as tidak_hadir
        ")
        ->whereYear('tgl', $tahun)
        ->groupBy(DB::raw('MONTH(tgl)'))
        ->orderBy(DB::raw('MONTH(tgl)'))
        ->get();

    $result = [];

    // Januari - Desember
    for ($i = 1; $i <= 12; $i++) {

        $bulanData = $data->firstWhere('bulan', $i);

        $result[] = [

            'bulan' => Carbon::create()
                ->month($i)
                ->translatedFormat('F'),

            'hadir' => $bulanData ? (int)$bulanData->hadir : 0,

            'cuti' => $bulanData ? (int)$bulanData->cuti : 0,

            'sakit' => $bulanData ? (int)$bulanData->sakit : 0,

            'izin' => $bulanData ? (int)$bulanData->izin : 0,

            'tidak_hadir' => $bulanData ? (int)$bulanData->tidak_hadir : 0
        ];
    }

    return response()->json([
        'success' => true,
        'tahun' => $tahun,
        'data' => $result
    ]);

    }

}
