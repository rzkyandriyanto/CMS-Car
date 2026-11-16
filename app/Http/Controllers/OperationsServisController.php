<?php
namespace App\Http\Controllers;
use App\Http\Requests\{FinalizeServisRequest,StartServisRequest,StoreDetailServisRequest,StoreServisSparepartRequest,UpdateInspectionRequest}; use App\Models\{BookingServis,Servis,DetailServis,ServisSparepart}; use App\Services\{ServicePersonnelAvailabilityService,ServisService,ServisSparepartService}; use Illuminate\Http\Request; use Throwable;
class OperationsServisController extends Controller {
 public function index(Request $request){
  $isMechanic = $request->user()?->roleKey() === 'mechanic';
  $mechanicId = $request->user()?->petugas?->id_petugas;
  $baseQuery = Servis::query()->when($isMechanic, fn($query) => $query->whereHas('assignments', fn($assigned) => $assigned->where('id_petugas', $mechanicId)->whereIn('assignment_role', ['primary','assistant'])->where('assigned_at', '<=', now())->whereNull('unassigned_at')));

  $stats = [
    'total_wo' => (clone $baseQuery)->count(),
    'pemeriksaan' => (clone $baseQuery)->where('status', 'pemeriksaan')->count(),
    'menunggu_konfirmasi' => (clone $baseQuery)->where('status', 'dikerjakan')->whereHas('approvalRequests', fn($ap) => $ap->where('status', 'pending'))->count(),
    'dikerjakan' => (clone $baseQuery)->where('status', 'dikerjakan')->count(),
    'selesai' => (clone $baseQuery)->where('status', 'selesai')->count(),
    'dibatalkan' => (clone $baseQuery)->where('status', 'dibatalkan')->count(),
    'booking_antri' => BookingServis::whereIn('status', ['menunggu', 'diterima'])->count(),
  ];

  $q = (clone $baseQuery)->with(['pelanggan','kendaraan','bookingServis.jenisServis','sparepartItems.sparepart','petugas','activeAssistants','approvalRequests','qualityControl','details'])
    ->when(request('q'), function($query, $v) {
      $term = '%'.$v.'%';
      $query->where(function($sub) use ($term) {
        $sub->where('kode_servis', 'like', $term)
          ->orWhereHas('pelanggan', fn($p) => $p->where('nama_lengkap', 'like', $term))
          ->orWhereHas('kendaraan', fn($k) => $k->where('nomor_polisi', 'like', $term)->orWhere('model', 'like', $term)->orWhere('merk', 'like', $term));
      });
    })
    ->when(request('status'), function($query, $v) {
      if ($v === 'menunggu_konfirmasi') {
        $query->where('status', 'dikerjakan')->whereHas('approvalRequests', fn($ap) => $ap->where('status', 'pending'));
      } else {
        $query->where('status', $v);
      }
    })
    ->latest('id_servis');

  return view('operations.servis.index', [
    'rows' => $q->paginate(20)->withQueryString(),
    'stats' => $stats,
  ]);
 }

 public function qcHistory(Request $request)
 {
  $base = Servis::query()->whereHas('qualityControl');
  $stats = [
    'total' => (clone $base)->count(),
    'in_progress' => (clone $base)->whereHas('qualityControl', fn($q) => $q->where('status', 'in_progress'))->count(),
    'passed' => (clone $base)->whereHas('qualityControl', fn($q) => $q->where('status', 'passed'))->count(),
    'failed' => (clone $base)->whereHas('qualityControl', fn($q) => $q->where('status', 'failed'))->count(),
  ];

  $qcStatus = (string) $request->query('qc_status', '');
  $search = trim((string) $request->query('q', ''));

  $rows = (clone $base)
    ->with(['pelanggan', 'kendaraan', 'petugas', 'bookingServis.jenisServis', 'qualityControl.items'])
    ->when($qcStatus !== '', fn($q) => $q->whereHas('qualityControl', fn($qc) => $qc->where('status', $qcStatus)))
    ->when($search !== '', function ($q) use ($search): void {
      $term = '%'.$search.'%';
      $q->where(function ($sub) use ($term): void {
        $sub->where('kode_servis', 'like', $term)
          ->orWhereHas('pelanggan', fn($p) => $p->where('nama_lengkap', 'like', $term))
          ->orWhereHas('kendaraan', fn($k) => $k->where('nomor_polisi', 'like', $term)->orWhere('merk', 'like', $term)->orWhere('model', 'like', $term));
      });
    })
    ->latest('id_servis')
    ->paginate(15)
    ->withQueryString();

  return view('operations.servis.qc-history', compact('rows', 'stats', 'qcStatus', 'search'));
 }
 public function show(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){
  if ($request->user()?->roleKey() === 'service_advisor') {
    $servis->load(['pelanggan','kendaraan.penjualan.mobil','petugas','serviceAdvisor','bookingServis.jenisServis','bookingServis.preferredPetugas','details.jenisServis','sparepartItems.sparepart','customSparepartRequests','findings','approvalRequests.items','activeAssistants']);
    $mobil=$servis->kendaraan?->penjualan?->mobil;
    $galleryData=$mobil?app(\App\Services\VehicleGalleryService::class)->for($mobil):[];
    $selected=collect($galleryData)->first();
    $vehicleImage=$selected['images'][0]['src']??asset('assets/images/hero/yza-hero-reference-v3.png');
    $technicians = \App\Models\Petugas::query()
      ->where('status', 'aktif')
      ->where(function ($query): void {
        $query->whereHas('serviceStaffProfile', fn ($profile) => $profile->where('can_be_technician', true))
          ->orWhereHas('user.role', fn ($role) => $role->where('nama_role', 'mechanic'));
      })
      ->orderBy('nama_lengkap')
      ->get(['id_petugas', 'nama_lengkap', 'jabatan']);
    return view('operations.servis.advisor-detail', compact('servis', 'vehicleImage', 'technicians'));
  }
  $prefix=request()->is('admin/*')?'admin':'petugas';return redirect()->route($stages->routeName($prefix,$stages->currentStage($servis)),$servis);
 }
 public function pemeriksaan(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){return $this->stagePage($request,$servis,'pemeriksaan',$stages);}
 public function persetujuan(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){return $this->stagePage($request,$servis,'persetujuan',$stages);}
 public function pengerjaan(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){return $this->stagePage($request,$servis,'pengerjaan',$stages);}
 public function qualityControl(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){return $this->stagePage($request,$servis,'quality-control',$stages);}
 public function finalisasi(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){return $this->stagePage($request,$servis,'finalisasi',$stages);}
 private function stagePage(Request $request, Servis $servis, string $stage, \App\Services\WorkOrderStageService $stages){$prefix=$request->is('admin/*')?'admin':'petugas';if(!$stages->canAccess($servis,$stage)){return redirect()->route($stages->routeName($prefix,$stages->currentStage($servis)),$servis)->with('warning',$stages->redirectReason($stage));}$servis->load(['pelanggan','kendaraan.penjualan.mobil','petugas.serviceStaffProfile.specializations','serviceAdvisor.serviceStaffProfile.specializations','bookingServis.jenisServis','bookingServis.preferredPetugas','details.jenisServis','sparepartItems.sparepart','transaksi.pembayarans','findings','executionEvents','approvalRequests.finding','approvalRequests.items.sparepart','qualityControl.items']);if($servis->details->isEmpty() && $servis->bookingServis?->jenisServis && !$servis->id_transaksi){$initDetail=new DetailServis();$initDetail->forceFill(['id_servis'=>$servis->id_servis,'id_jenis_servis'=>$servis->bookingServis->id_jenis_servis,'deskripsi_pekerjaan'=>$servis->bookingServis->jenisServis->nama_servis,'biaya'=>(float)($servis->bookingServis->jenisServis->estimasi_harga??0),'catatan'=>'Layanan awal sesuai reservasi booking'])->save();$servis->load('details.jenisServis');}$promos=\App\Models\PromoMember::visible()->whereIn('berlaku_untuk',['semua','servis'])->get();$serviceTypes=\App\Models\JenisServis::where('status','aktif')->orderBy('nama_servis')->get();$spareparts=\App\Models\Sparepart::where('status','aktif')->where('stok','>',0)->orderBy('nama_sparepart')->get();$vehicle=$servis->kendaraan;$verifiedFitmentCount=$vehicle?\App\Models\SparepartFitment::where('status_verifikasi','verified_fit')->where(fn($q)=>$q->whereNull('merk_kendaraan')->orWhere('merk_kendaraan',$vehicle->merk))->where(fn($q)=>$q->whereNull('model_kendaraan')->orWhere('model_kendaraan','like','%'.$vehicle->model.'%'))->count():0;$workSources=app(\App\Services\WorkOrderSourceService::class);$mobil=$servis->kendaraan?->penjualan?->mobil;$galleryData=$mobil?app(\App\Services\VehicleGalleryService::class)->for($mobil):[];$selected=collect($galleryData)->first();$vehicleImage=$selected['images'][0]['src']??asset('assets/images/hero/yza-hero-reference-v3.png');return view('operations.servis.wizard',compact('servis','stage','stages','promos','serviceTypes','spareparts','verifiedFitmentCount','vehicleImage','prefix','workSources'));}
 public function fromBooking(StartServisRequest $r,BookingServis $booking_servis,ServisService $svc){try{$d=$r->validated();$canAssign=in_array($r->user()->roleKey(),['super_admin','admin','service_advisor'],true);$hasExplicitTechnician=$canAssign && array_key_exists('id_petugas',$d) && $d['id_petugas']!==null;$assigned=(int)($hasExplicitTechnician?$d['id_petugas']:($booking_servis->preferred_id_petugas ?: $r->user()->petugas?->id_petugas));$advisor=$canAssign&&isset($d['id_service_advisor'])?(int)$d['id_service_advisor']:($r->user()->roleKey()==='service_advisor'?$r->user()->petugas?->id_petugas:null);$servis=$svc->createFromBooking($booking_servis->id_booking_servis,$assigned,$d['kilometer']??null,$d['catatan']??null,$advisor,$hasExplicitTechnician);$route=$r->is('admin/*')?'admin.servis.show':'petugas.servis.show';return redirect()->route($route,$servis)->with('success','Work Order siap diproses.');}catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function inspection(UpdateInspectionRequest $r,Servis $servis,ServisService $svc,\App\Services\WorkOrderStageService $stages){try{$d=$r->validated();$updated=$svc->updateInspection($servis->id_servis,$d['hasil_pemeriksaan'],$d['catatan']??null);$prefix=$r->is('admin/*')?'admin':'petugas';return redirect()->route($stages->routeName($prefix,$stages->currentStage($updated)),$updated)->with('success','Hasil pemeriksaan tersimpan.');}catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e){return back()->withErrors($e->getMessage());}}
 public function detail(StoreDetailServisRequest $r,Servis $servis,ServisService $svc){try{$d=$r->validated();$svc->addDetail($servis->id_servis,$d['id_jenis_servis']??null,$d['deskripsi_pekerjaan'],$d['biaya'],$d['catatan']??null);return back()->with('success','Jasa berhasil ditambahkan.')->with('execution_form','jasa');}catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function removeDetail(Servis $servis,DetailServis $detail_servis,ServisService $svc){try{$svc->removeDetail($servis->id_servis,$detail_servis->id_detail_servis);return back()->with('success','Detail pekerjaan dihapus.');}catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e){return back()->withErrors($e->getMessage());}}
 public function sparepart(StoreServisSparepartRequest $r,Servis $servis,ServisSparepartService $svc){try{$d=$r->validated();$svc->add($servis->id_servis,(int)$d['id_sparepart'],(int)$d['jumlah']);return back()->with('success','Suku cadang berhasil dicatat.')->with('execution_form','sparepart');}catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function removeSparepart(Servis $servis,ServisSparepart $servis_sparepart,ServisSparepartService $svc){try{$svc->remove($servis->id_servis,$servis_sparepart->id_servis_sparepart);return back()->with('success','Draft spare part dihapus.');}catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e){return back()->withErrors($e->getMessage());}}
 public function finalize(FinalizeServisRequest $r,Servis $servis,ServisService $svc){try{$actor=$r->user()->petugas?->id_petugas ?? app(\App\Services\InventoryService::class)->actorPetugasId($r->user()->id_user);$tx=$svc->finalizeWithPromo($servis->id_servis,(int)$actor,$r->integer('promo_id')?:null);return back()->with('success','Servis difinalisasi dan transaksi '.$tx->kode_transaksi.' dibuat dengan status belum_bayar.');}catch (\Illuminate\Auth\Access\AuthorizationException $e) { throw $e; } catch (Throwable $e){return back()->withErrors($e->getMessage());}}

 public function updateIntake(Request $request, Servis $servis)
 {
  $data = $request->validate([
   'kilometer' => ['required', 'integer', 'min:0', 'max:9999999'],
   'keluhan' => ['nullable', 'string', 'max:1000'],
  ]);

  $km = (int) $data['kilometer'];
  $keluhan = filled($data['keluhan'] ?? null) ? trim((string) $data['keluhan']) : null;

  $servis->forceFill([
   'kilometer' => $km,
   'keluhan' => $keluhan,
  ])->save();

  if ($servis->kendaraan && ($servis->kendaraan->kilometer === null || $km > (int) $servis->kendaraan->kilometer)) {
   $servis->kendaraan->forceFill(['kilometer' => $km])->save();
  }

  return back()->with('success', 'Data odometer dan keluhan Work Order '.$servis->kode_servis.' berhasil diperbarui.');
 }
}
