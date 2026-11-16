<?php
namespace App\Http\Controllers;
use App\Models\BookingServis;use App\Services\{BookingServisService,ServicePersonnelAvailabilityService};use Illuminate\Http\Request;use Throwable;
class OperationsBookingServisController extends Controller {
 public function index(Request $r){
  $stats = [
    'total' => BookingServis::count(),
    'menunggu' => BookingServis::where('status', 'menunggu')->count(),
    'diterima' => BookingServis::where('status', 'diterima')->count(),
    'diproses' => BookingServis::where('status', 'diproses')->count(),
    'selesai' => BookingServis::where('status', 'selesai')->count(),
    'ditolak' => BookingServis::where('status', 'ditolak')->count(),
  ];

  $q=BookingServis::with(['pelanggan','kendaraan','jenisServis','preferredPetugas','servis.petugas'])->latest('tanggal')->latest('jam');
  if($r->filled('status'))$q->where('status',$r->string('status'));
  if($r->filled('q')){$term='%'.$r->string('q').'%';$q->where(function($w)use($term){$w->where('kode_booking','like',$term)->orWhereHas('pelanggan',fn($p)=>$p->where('nama_lengkap','like',$term))->orWhereHas('kendaraan',fn($v)=>$v->where('nomor_polisi','like',$term)->orWhere('model','like',$term));});}
  $technicians = \App\Models\Petugas::query()
    ->where('status', 'aktif')
    ->where(function ($query): void {
      $query->whereHas('serviceStaffProfile', fn ($profile) => $profile->where('can_be_technician', true))
        ->orWhereHas('user.role', fn ($role) => $role->where('nama_role', 'mechanic'));
    })
    ->orderBy('nama_lengkap')
    ->get(['id_petugas', 'nama_lengkap', 'jabatan']);
  return view('operations.booking-servis.index',['rows'=>$q->paginate(20)->withQueryString(),'role'=>$r->user()->roleName(),'stats'=>$stats,'technicians'=>$technicians]);
 }
 public function show(Request $r,BookingServis $booking_servis,ServicePersonnelAvailabilityService $personnel){$booking_servis->load(['pelanggan','kendaraan','jenisServis','preferredPetugas','servis.petugas','servis.serviceAdvisor']);$eligible=$booking_servis->jenisServis?->id_jenis_servis?$personnel->technicians($booking_servis->tanggal->toDateString(),substr((string)$booking_servis->jam,0,5),(int)$booking_servis->jenisServis->id_jenis_servis,$booking_servis->servis?->id_servis):[];return view('operations.booking-servis.show',['booking'=>$booking_servis,'role'=>$r->user()->roleName(),'eligibleTechnicians'=>$eligible,'advisors'=>$personnel->advisors()]);}
 public function action(Request $r,BookingServis $booking_servis,string $action,BookingServisService $svc){try{if($action==='confirm')$svc->confirm($booking_servis->id_booking_servis);elseif($action==='reject')$svc->reject($booking_servis->id_booking_servis);else abort(403);return back()->with('success','Status booking servis diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
