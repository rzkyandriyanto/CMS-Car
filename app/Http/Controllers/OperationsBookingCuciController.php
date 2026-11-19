<?php
namespace App\Http\Controllers;
use App\Models\{BookingCuci,PaketCuci,Transaksi};use App\Services\BookingCuciService;use Illuminate\Http\Request;use Illuminate\Support\Carbon;use Illuminate\View\View;use Throwable;
class OperationsBookingCuciController extends Controller {
 public function index(Request $r):View
 {
  $search=trim((string)$r->input('q',''));$status=(string)$r->input('status','');$date=(string)$r->input('date','');
  $perPage=in_array((int)$r->input('perPage',10),[10,20,50],true)?(int)$r->input('perPage',10):10;
  $base=BookingCuci::query();
  $rows=(clone $base)->with(['pelanggan','kendaraan.penjualan.mobil','kendaraan.pesanan','paket','petugas','transaksi','promo'])->when($status!=='',fn($q)=>$q->where('status',$status))->when($date!=='',fn($q)=>$q->whereDate('tanggal',$date))->when($search!=='',function($q)use($search){$term="%{$search}%";$q->where(function($w)use($term){$w->where('kode_booking','like',$term)->orWhereHas('pelanggan',fn($p)=>$p->where('nama_lengkap','like',$term))->orWhereHas('kendaraan',fn($v)=>$v->where('nomor_polisi','like',$term)->orWhere('model','like',$term));});})->latest('tanggal')->latest('jam')->paginate($perPage)->withQueryString();
  $today=now(config('app.timezone'))->toDateString();
  $eligible=fn($q)=>$q->whereNotIn('status',['dibatalkan']);
  $total=(clone $base)->count();$waiting=(clone $base)->where('status','menunggu')->count();$processing=(clone $base)->where('status','proses')->count();$completed=(clone $base)->where('status','selesai')->count();$completedToday=(clone $base)->where('status','selesai')->whereDate('tanggal',$today)->count();
  $revenue=(float)Transaksi::where('jenis_transaksi','cuci_mobil')->where('status','lunas')->whereDate('tanggal',$today)->sum('total');
  $favoritePackages=PaketCuci::query()->withCount(['bookingCuci'=>fn($q)=>$eligible($q)])->orderByDesc('booking_cuci_count')->orderBy('nama_paket')->get()->filter(fn($package)=>(int)$package->booking_cuci_count>0)->values();
  $favoriteTotal=max(1,(int)$favoritePackages->sum('booking_cuci_count'));
  $todayBookings=(clone $base)->whereDate('tanggal',$today)->whereNotIn('status',['dibatalkan']);
  $queueToday=(clone $todayBookings)->whereIn('status',['menunggu','diterima','proses'])->count();$assignedToday=(clone $todayBookings)->whereNotNull('id_petugas')->distinct('id_petugas')->count('id_petugas');
  $allPackages=PaketCuci::query()->withCount(['bookingCuci'=>fn($q)=>$eligible($q)])->orderBy('harga')->get();
  $promos=\App\Models\PromoMember::visible()->whereIn('berlaku_untuk',['semua','cuci_mobil'])->get();
  return view('operations.booking-cuci.index',['rows'=>$rows,'role'=>$r->user()->roleName(),'search'=>$search,'status'=>$status,'date'=>$date,'perPage'=>$perPage,'statuses'=>['menunggu','diterima','proses','selesai','dibatalkan'],'bookingStats'=>['total'=>$total,'waiting'=>$waiting,'processing'=>$processing,'completed'=>$completed,'completedToday'=>$completedToday,'revenue'=>$revenue],'favoritePackages'=>$favoritePackages,'allPackages'=>$allPackages,'promos'=>$promos,'favoriteTotal'=>$favoriteTotal,'operational'=>['queue'=>$queueToday,'completed'=>$completedToday,'averageDuration'=>null,'assigned'=>$assignedToday]]);
 }
 public function show(Request $r,BookingCuci $booking_cuci){$booking_cuci->load(['pelanggan','kendaraan','paket','petugas','transaksi']);$promos=\App\Models\PromoMember::visible()->whereIn('berlaku_untuk',['semua','cuci_mobil'])->get();return view('operations.booking-cuci.show',['booking'=>$booking_cuci,'role'=>$r->user()->roleName(),'promos'=>$promos]);}
 public function action(Request $r,BookingCuci $booking_cuci,string $action,BookingCuciService $svc){try{$petugas=$svc->actorPetugasId($r->user()->id_user);if($action==='confirm')$svc->confirm($booking_cuci->id_booking_cuci,(int)$petugas);elseif($action==='start'){$r->validate(['foto_sebelum_cuci'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],'foto_sebelum_cuci_camera'=>['nullable','string']],['foto_sebelum_cuci.uploaded'=>'Ukuran file foto terlalu besar untuk diunggah. Silakan gunakan tombol Buka Kamera atau pilih foto dengan ukuran lebih kecil.','foto_sebelum_cuci.image'=>'File harus berupa gambar (JPG, PNG, atau WEBP).','foto_sebelum_cuci.max'=>'Ukuran foto maksimal 5 MB.']);$savedPath=$this->storeWashPhoto($r,$booking_cuci,'foto_sebelum_cuci','before');if(!$savedPath){return back()->withErrors('Foto kondisi mobil sebelum dicuci wajib diunggah atau diambil melalui kamera.');}$svc->start($booking_cuci->id_booking_cuci,(int)$petugas);$booking_cuci->forceFill(['foto_sebelum_cuci'=>$savedPath])->save();}elseif($action==='finalize'){$r->validate(['foto_sesudah_cuci'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],'foto_sesudah_cuci_camera'=>['nullable','string']],['foto_sesudah_cuci.uploaded'=>'Ukuran file foto terlalu besar untuk diunggah. Silakan gunakan tombol Buka Kamera atau pilih foto dengan ukuran lebih kecil.','foto_sesudah_cuci.image'=>'File harus berupa gambar (JPG, PNG, atau WEBP).','foto_sesudah_cuci.max'=>'Ukuran foto maksimal 5 MB.']);$savedPath=$this->storeWashPhoto($r,$booking_cuci,'foto_sesudah_cuci','after');if(!$savedPath){return back()->withErrors('Foto hasil cucian mobil wajib diunggah atau diambil melalui kamera sebelum finalisasi.');}$tx=$svc->finalizeWithPromo($booking_cuci->id_booking_cuci,(int)$petugas,null);$booking_cuci->forceFill(['foto_sesudah_cuci'=>$savedPath])->save();return back()->with('success','Cuci mobil difinalisasi. Tagihan '.$tx->kode_transaksi.' telah diteruskan ke Finance untuk pembayaran.');}else abort(403);return back()->with('success','Status car wash diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 private function storeWashPhoto(Request $r, BookingCuci $booking, string $field, string $prefix): ?string
 {
  $dirRel = 'uploads/carwash';
  $dirAbs = public_path($dirRel);
  if (!is_dir($dirAbs)) {
   @mkdir($dirAbs, 0755, true);
  }
  if ($r->hasFile($field) && $r->file($field)->isValid()) {
   $file = $r->file($field);
   $ext = strtolower($file->extension() ?: 'jpg');
   $name = $prefix.'-'.$booking->id_booking_cuci.'-'.time().'.'.$ext;
   $file->move($dirAbs, $name);
   return $dirRel.'/'.$name;
  }
  $dataUrl = trim((string) $r->input($field.'_camera', ''));
  if ($dataUrl !== '' && preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/', $dataUrl, $m)) {
   $bin = base64_decode($m[2], true);
   if ($bin !== false && strlen($bin) <= 5 * 1024 * 1024) {
    $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
    $name = $prefix.'-'.$booking->id_booking_cuci.'-'.time().'.'.$ext;
    file_put_contents($dirAbs.DIRECTORY_SEPARATOR.$name, $bin);
    return $dirRel.'/'.$name;
   }
  }
  return null;
 }
}
