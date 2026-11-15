<?php

namespace App\Http\Controllers;

use App\Models\Bimbingan;
use App\Models\Dosen;
use App\Models\LaporanMagang;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\PengajuanTempat;
use App\Models\PenempatanMagang;
use App\Models\SuratMagang;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Arahkan ke dashboard sesuai role pengguna. */
    public function index(Request $request)
    {
        return match ($request->user()->role) {
            'Mahasiswa' => redirect()->route('mahasiswa.dashboard'),
            'Dosen'     => redirect()->route('dashboard.dosen'),
            default     => redirect()->route('dashboard.admin'),
        };
    }

    public function mahasiswa(Request $request)
    {
        $user = $request->user();
        $mhs  = $user->mahasiswa;          // bisa null jika profil belum dibuat
        $id   = $mhs?->id_mahasiswa;

        $pengajuan = $id ? PengajuanTempat::with('tempat')
            ->whereHas('pendaftaran', fn ($q) => $q->where('id_mahasiswa', $id))
            ->latest('tanggal_pengajuan')->first() : null;

        $penempatan = $id ? PenempatanMagang::with('tempat')
            ->where('id_mahasiswa', $id)->latest('created_at')->first() : null;

        $surat = $id ? SuratMagang::whereHas('pengajuan.pendaftaran',
            fn ($q) => $q->where('id_mahasiswa', $id))
            ->latest('tanggal_pengajuan')->first() : null;

        $jumlahBimbingan = $id ? Bimbingan::whereHas('penempatan',
            fn ($q) => $q->where('id_mahasiswa', $id))->count() : 0;

        $jumlahLaporan = $id ? LaporanMagang::whereHas('penempatan',
            fn ($q) => $q->where('id_mahasiswa', $id))->count() : 0;

        $notifikasi = Notifikasi::where('id_pengguna', $user->id_pengguna)
            ->latest('created_at')->take(5)->get();

        return view('dashboard.mahasiswa', compact(
            'user', 'mhs', 'pengajuan', 'penempatan', 'surat',
            'jumlahBimbingan', 'jumlahLaporan', 'notifikasi'
        ));
    }

    public function dosen(Request $request)
    {
        $user  = $request->user();
        $dosen = $user->dosen;
        $id    = $dosen?->id_dosen;

        $mahasiswaPa = $id ? Mahasiswa::where('id_dosen_pa', $id)->count() : 0;

        $pengajuanMenunggu = $id ? PengajuanTempat::where('status_pengajuan', 'Diajukan')
            ->whereHas('pendaftaran.mahasiswa', fn ($q) => $q->where('id_dosen_pa', $id))
            ->count() : 0;

        $mahasiswaBimbingan = $id ? PenempatanMagang::where('id_dosen_pembimbing', $id)
            ->where('status_magang', 'Berjalan')->count() : 0;

        $laporanMenunggu = $id ? LaporanMagang::where('status_laporan', 'Diunggah')
            ->whereHas('penempatan', fn ($q) => $q->where('id_dosen_pembimbing', $id))
            ->count() : 0;

        $pengajuanTerbaru = $id ? PengajuanTempat::with(['pendaftaran.mahasiswa.pengguna', 'tempat'])
            ->whereHas('pendaftaran.mahasiswa', fn ($q) => $q->where('id_dosen_pa', $id))
            ->latest('tanggal_pengajuan')->take(5)->get() : collect();

        return view('dashboard.dosen', compact(
            'user', 'dosen', 'mahasiswaPa', 'pengajuanMenunggu',
            'mahasiswaBimbingan', 'laporanMenunggu', 'pengajuanTerbaru'
        ));
    }

    public function admin(Request $request)
    {
        $user = $request->user();

        $totalDosen      = Dosen::count();
        $totalMahasiswa  = Mahasiswa::count();
        $pengajuanMasuk  = PengajuanTempat::where('status_pengajuan', 'Diajukan')->count();
        $suratDiproses   = SuratMagang::where('status_surat', 'Diproses')->count();

        $pengajuanTerbaru = PengajuanTempat::with(['pendaftaran.mahasiswa.pengguna', 'tempat'])
            ->latest('tanggal_pengajuan')->take(5)->get();

        return view('dashboard.admin', compact(
            'user', 'totalDosen', 'totalMahasiswa',
            'pengajuanMasuk', 'suratDiproses', 'pengajuanTerbaru'
        ));
    }
}
