<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Pembayaran, PembayaranGateway, Servis, Transaksi};
use App\Services\{InventoryService, MidtransPaymentService, PaymentService, ServisService};
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PaymentManagementController extends Controller
{
    public function index(Request $request): View
    {
        $carTab = $request->input('car_tab', 'offline');
        $serviceTab = $request->input('service_tab', 'offline');
        $status = trim((string) $request->input('status'));
        $q = trim((string) $request->input('q'));
        $from = $request->input('from');
        $to = $request->input('to');
        $perPage = (int) $request->input('per_page', 8);
        if (!in_array($perPage, [8, 10, 25, 50], true)) {
            $perPage = 8;
        }

        // --- BOX 1: PEMBAYARAN MOBIL ---
        $carOffline = Pembayaran::with(['transaksi.pelanggan', 'transaksi.pesananMobilMember.mobil', 'transaksi.penjualanMobil.mobil'])
            ->where('arah', 'masuk')
            ->whereIn('metode', ['tunai', 'transfer'])
            ->whereHas('transaksi', fn ($trx) => $trx->where('jenis_transaksi', 'penjualan_mobil'))
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('kode_pembayaran', 'like', "%{$q}%")
                    ->orWhereHas('transaksi.pelanggan', fn ($c) => $c->where('nama_lengkap', 'like', "%{$q}%")->orWhere('kode_pelanggan', 'like', "%{$q}%"))
                    ->orWhereHas('transaksi.pesananMobilMember', fn ($o) => $o->where('nomor_pesanan', 'like', "%{$q}%"))
                    ->orWhereHas('transaksi.penjualanMobil', fn ($s) => $s->where('kode_penjualan', 'like', "%{$q}%"))
                    ->orWhereHas('transaksi', fn ($t) => $t->where('kode_transaksi', 'like', "%{$q}%"));
            }))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($from, fn ($query) => $query->whereDate('tanggal', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('tanggal', '<=', $to))
            ->latest('tanggal')
            ->paginate($perPage, ['*'], 'car_offline_page')
            ->withQueryString();

        $carOnline = PembayaranGateway::with(['transaksi.pelanggan', 'transaksi.pesananMobilMember.mobil', 'transaksi.penjualanMobil.mobil'])
            ->whereHas('transaksi', fn ($trx) => $trx->where('jenis_transaksi', 'penjualan_mobil'))
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('order_id', 'like', "%{$q}%")
                    ->orWhereHas('transaksi.pelanggan', fn ($c) => $c->where('nama_lengkap', 'like', "%{$q}%")->orWhere('kode_pelanggan', 'like', "%{$q}%"))
                    ->orWhereHas('transaksi', fn ($t) => $t->where('kode_transaksi', 'like', "%{$q}%"));
            }))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest('created_at')
            ->paginate($perPage, ['*'], 'car_online_page')
            ->withQueryString();

        $carUnpaid = Transaksi::with(['pelanggan', 'pesananMobilMember', 'penjualanMobil.mobil'])
            ->where('jenis_transaksi', 'penjualan_mobil')
            ->where('status', 'belum_bayar')
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('kode_transaksi', 'like', "%{$q}%")
                    ->orWhereHas('pelanggan', fn ($c) => $c->where('nama_lengkap', 'like', "%{$q}%"))
                    ->orWhereHas('pesananMobilMember', fn ($o) => $o->where('nomor_pesanan', 'like', "%{$q}%"));
            }))
            ->latest('tanggal')
            ->paginate($perPage, ['*'], 'car_unpaid_page')
            ->withQueryString();

        $carStats = [
            'verified_total' => (float) Pembayaran::where('arah', 'masuk')->where('status', 'terverifikasi')
                ->whereHas('transaksi', fn ($t) => $t->where('jenis_transaksi', 'penjualan_mobil'))->sum('jumlah')
                + (float) PembayaranGateway::whereIn('status_provider', ['settlement', 'capture'])
                ->whereHas('transaksi', fn ($t) => $t->where('jenis_transaksi', 'penjualan_mobil'))->sum('gross_amount'),
            'waiting_confirmation' => Pembayaran::where('arah', 'masuk')->where('status', 'menunggu')
                ->whereHas('transaksi', fn ($t) => $t->where('jenis_transaksi', 'penjualan_mobil'))->count(),
            'unpaid_count' => Transaksi::where('jenis_transaksi', 'penjualan_mobil')->where('status', 'belum_bayar')->count(),
        ];

        // --- BOX 2: PEMBAYARAN SERVIS ---
        $serviceOffline = Pembayaran::with(['transaksi.pelanggan', 'transaksi.servis.kendaraan', 'transaksi.servis.details', 'transaksi.servis.sparepartItems.sparepart'])
            ->where('arah', 'masuk')
            ->whereIn('metode', ['tunai', 'transfer'])
            ->whereHas('transaksi', fn ($trx) => $trx->where('jenis_transaksi', 'servis'))
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('kode_pembayaran', 'like', "%{$q}%")
                    ->orWhereHas('transaksi.pelanggan', fn ($c) => $c->where('nama_lengkap', 'like', "%{$q}%")->orWhere('kode_pelanggan', 'like', "%{$q}%"))
                    ->orWhereHas('transaksi.servis', fn ($s) => $s->where('kode_servis', 'like', "%{$q}%"))
                    ->orWhereHas('transaksi', fn ($t) => $t->where('kode_transaksi', 'like', "%{$q}%"));
            }))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($from, fn ($query) => $query->whereDate('tanggal', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('tanggal', '<=', $to))
            ->latest('tanggal')
            ->paginate($perPage, ['*'], 'service_offline_page')
            ->withQueryString();

        $serviceOnline = PembayaranGateway::with(['transaksi.pelanggan', 'transaksi.servis.kendaraan', 'transaksi.servis.details', 'transaksi.servis.sparepartItems.sparepart'])
            ->whereHas('transaksi', fn ($trx) => $trx->where('jenis_transaksi', 'servis'))
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('order_id', 'like', "%{$q}%")
                    ->orWhereHas('transaksi.pelanggan', fn ($c) => $c->where('nama_lengkap', 'like', "%{$q}%")->orWhere('kode_pelanggan', 'like', "%{$q}%"))
                    ->orWhereHas('transaksi.servis', fn ($s) => $s->where('kode_servis', 'like', "%{$q}%"))
                    ->orWhereHas('transaksi', fn ($t) => $t->where('kode_transaksi', 'like', "%{$q}%"));
            }))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest('created_at')
            ->paginate($perPage, ['*'], 'service_online_page')
            ->withQueryString();

        $serviceUnpaid = Transaksi::with(['pelanggan', 'servis.kendaraan', 'servis.details', 'servis.sparepartItems.sparepart'])
            ->where('jenis_transaksi', 'servis')
            ->where('status', 'belum_bayar')
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('kode_transaksi', 'like', "%{$q}%")
                    ->orWhereHas('pelanggan', fn ($c) => $c->where('nama_lengkap', 'like', "%{$q}%"))
                    ->orWhereHas('servis', fn ($s) => $s->where('kode_servis', 'like', "%{$q}%"));
            }))
            ->latest('tanggal')
            ->paginate($perPage, ['*'], 'service_unpaid_page')
            ->withQueryString();

        $serviceReadyFromQc = Servis::with(['pelanggan', 'kendaraan', 'details', 'sparepartItems.sparepart', 'qualityControl'])
            ->where('status', 'dikerjakan')
            ->whereNull('id_transaksi')
            ->whereHas('qualityControl', fn ($qc) => $qc->where('status', 'passed'))
            ->when($q !== '', fn ($query) => $query->where(function ($search) use ($q): void {
                $search->where('kode_servis', 'like', "%{$q}%")
                    ->orWhereHas('pelanggan', fn ($c) => $c->where('nama_lengkap', 'like', "%{$q}%"));
            }))
            ->latest('id_servis')
            ->get();

        $serviceStats = [
            'verified_total' => (float) Pembayaran::where('arah', 'masuk')->where('status', 'terverifikasi')
                ->whereHas('transaksi', fn ($t) => $t->where('jenis_transaksi', 'servis'))->sum('jumlah')
                + (float) PembayaranGateway::whereIn('status_provider', ['settlement', 'capture'])
                ->whereHas('transaksi', fn ($t) => $t->where('jenis_transaksi', 'servis'))->sum('gross_amount'),
            'waiting_confirmation' => Pembayaran::where('arah', 'masuk')->where('status', 'menunggu')
                ->whereHas('transaksi', fn ($t) => $t->where('jenis_transaksi', 'servis'))->count(),
            'unpaid_count' => Transaksi::where('jenis_transaksi', 'servis')->where('status', 'belum_bayar')->count() + $serviceReadyFromQc->count(),
        ];

        if (!$request->has('service_tab') && $serviceOffline->total() === 0 && $serviceStats['unpaid_count'] > 0) {
            $serviceTab = 'unpaid';
        }

        return view('admin.pembayaran.index', compact(
            'carTab', 'serviceTab', 'status', 'q', 'from', 'to', 'perPage',
            'carOffline', 'carOnline', 'carUnpaid', 'carStats',
            'serviceOffline', 'serviceOnline', 'serviceUnpaid', 'serviceReadyFromQc', 'serviceStats'
        ));
    }

    public function checkoutServis(Request $request, Servis $servis, ServisService $servisService, InventoryService $inventory)
    {
        try {
            $tx = $servisService->finalizeFromQc($servis->id_servis, (int) $inventory->actorPetugasId($request->user()->id_user));
            return redirect()->route('admin.payment.create', $tx)->with('status', 'Tagihan ' . $tx->kode_transaksi . ' siap dibayar.');
        } catch (Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function show(Pembayaran $pembayaran): View
    {
        abort_unless($pembayaran->arah === 'masuk', 404);
        $pembayaran->load(['transaksi.pelanggan', 'transaksi.pesananMobilMember', 'transaksi.penjualanMobil.mobil', 'transaksi.pembayarans']);
        return view('admin.pembayaran.show', compact('pembayaran'));
    }

    public function verify(Request $request, Pembayaran $pembayaran, PaymentService $service, InventoryService $inventory)
    {
        try {
            $request->validate(['receipt_reference' => ['nullable', 'string', 'max:100']]);
            $payment = $service->verify($pembayaran->id_pembayaran, (int) $inventory->actorPetugasId($request->user()->id_user));
            if ($request->filled('receipt_reference')) $payment->forceFill(['receipt_reference' => $request->string('receipt_reference')->toString()])->save();
            return back()->with('status', 'Dana diterima dan pembayaran berhasil diverifikasi.');
        } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function reject(Request $request, Pembayaran $pembayaran, PaymentService $service, InventoryService $inventory)
    {
        try {
            $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);
            $service->reject($pembayaran->id_pembayaran, (int) $inventory->actorPetugasId($request->user()->id_user));
            $pembayaran->forceFill(['rejection_reason' => $data['rejection_reason'], 'verified_by' => (int) $inventory->actorPetugasId($request->user()->id_user), 'verified_at' => now()])->save();
            return back()->with('status', 'Pembayaran ditolak dan riwayatnya tetap tersimpan.');
        } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function sync(Request $request, PembayaranGateway $gateway, MidtransPaymentService $service)
    {
        try { $service->syncForAdmin($gateway); return back()->with('status', 'Status resmi Midtrans diperbarui.'); }
        catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function report(Request $request)
    {
        $from = (string) $request->input('from', '');
        $to = (string) $request->input('to', '');
        $jenis = (string) $request->input('jenis', 'semua');
        if (!in_array($jenis, ['semua', 'penjualan_mobil', 'servis'], true)) {
            $jenis = 'semua';
        }

        $buildInflows = function (string $txType) use ($from, $to) {
            $offlineRows = Pembayaran::with([
                'transaksi.pelanggan',
                'transaksi.pesananMobilMember.mobil',
                'transaksi.penjualanMobil.mobil',
                'transaksi.servis.kendaraan',
            ])
                ->where('arah', 'masuk')
                ->where('status', 'terverifikasi')
                ->whereHas('transaksi', fn ($t) => $t->where('jenis_transaksi', $txType))
                ->when($from !== '', fn ($q) => $q->whereDate('tanggal', '>=', $from))
                ->when($to !== '', fn ($q) => $q->whereDate('tanggal', '<=', $to))
                ->get()
                ->map(function (Pembayaran $p) use ($txType): array {
                    $trx = $p->transaksi;
                    if ($txType === 'penjualan_mobil') {
                        $car = $trx?->pesananMobilMember?->mobil ?? $trx?->penjualanMobil?->mobil;
                        $unit = $car ? trim($car->merk . ' ' . $car->model) : ($trx?->pesananMobilMember ? trim(($trx->pesananMobilMember->snapshot_merk ?? '') . ' ' . ($trx->pesananMobilMember->snapshot_model ?? '')) : 'Unit Mobil');
                        $ref = $trx?->pesananMobilMember?->nomor_pesanan ?? ($trx?->penjualanMobil?->kode_penjualan ?? ($trx?->kode_transaksi ?? '—'));
                    } else {
                        $srv = $trx?->servis;
                        $unit = $srv?->kendaraan ? trim($srv->kendaraan->merk . ' ' . $srv->kendaraan->model) : 'Kendaraan Servis';
                        $ref = $srv?->kode_servis ?? ($trx?->kode_transaksi ?? '—');
                    }
                    return [
                        'id' => $p->kode_pembayaran,
                        'trx_code' => $trx?->kode_transaksi ?? '—',
                        'ref_code' => $ref,
                        'date' => $p->tanggal,
                        'customer' => $trx?->pelanggan?->nama_lengkap ?? 'Member',
                        'unit' => $unit,
                        'channel' => strtoupper($p->metode),
                        'source' => 'offline',
                        'discount' => (float) ($trx?->diskon ?? 0),
                        'amount' => (float) $p->jumlah,
                        'category' => $txType === 'penjualan_mobil' ? 'Pembelian Mobil' : 'Servis Bengkel',
                    ];
                });

            $onlineRows = PembayaranGateway::with([
                'transaksi.pelanggan',
                'transaksi.pesananMobilMember.mobil',
                'transaksi.penjualanMobil.mobil',
                'transaksi.servis.kendaraan',
            ])
                ->whereIn('status_provider', ['settlement', 'capture'])
                ->whereHas('transaksi', fn ($t) => $t->where('jenis_transaksi', $txType))
                ->when($from !== '', fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to !== '', fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->get()
                ->map(function (PembayaranGateway $g) use ($txType): array {
                    $trx = $g->transaksi;
                    if ($txType === 'penjualan_mobil') {
                        $car = $trx?->pesananMobilMember?->mobil ?? $trx?->penjualanMobil?->mobil;
                        $unit = $car ? trim($car->merk . ' ' . $car->model) : ($trx?->pesananMobilMember ? trim(($trx->pesananMobilMember->snapshot_merk ?? '') . ' ' . ($trx->pesananMobilMember->snapshot_model ?? '')) : 'Unit Mobil');
                        $ref = $trx?->pesananMobilMember?->nomor_pesanan ?? ($trx?->penjualanMobil?->kode_penjualan ?? ($trx?->kode_transaksi ?? '—'));
                    } else {
                        $srv = $trx?->servis;
                        $unit = $srv?->kendaraan ? trim($srv->kendaraan->merk . ' ' . $srv->kendaraan->model) : 'Kendaraan Servis';
                        $ref = $srv?->kode_servis ?? ($trx?->kode_transaksi ?? '—');
                    }
                    return [
                        'id' => $g->order_id,
                        'trx_code' => $trx?->kode_transaksi ?? '—',
                        'ref_code' => $ref,
                        'date' => $g->paid_at ?? $g->created_at,
                        'customer' => $trx?->pelanggan?->nama_lengkap ?? 'Member',
                        'unit' => $unit,
                        'channel' => 'MIDTRANS',
                        'source' => 'online',
                        'discount' => (float) ($trx?->diskon ?? 0),
                        'amount' => (float) $g->gross_amount,
                        'category' => $txType === 'penjualan_mobil' ? 'Pembelian Mobil' : 'Servis Bengkel',
                    ];
                });

            return $offlineRows->concat($onlineRows)
                ->sortByDesc(fn (array $row) => $row['date']?->timestamp ?? 0)
                ->values();
        };

        $carInflows = $buildInflows('penjualan_mobil');
        $serviceInflows = $buildInflows('servis');

        $carTotal = (float) $carInflows->sum('amount');
        $carOfflineTotal = (float) $carInflows->where('source', 'offline')->sum('amount');
        $carOnlineTotal = (float) $carInflows->where('source', 'online')->sum('amount');

        $serviceTotal = (float) $serviceInflows->sum('amount');
        $serviceOfflineTotal = (float) $serviceInflows->where('source', 'offline')->sum('amount');
        $serviceOnlineTotal = (float) $serviceInflows->where('source', 'online')->sum('amount');
        $serviceDiscountTotal = (float) Transaksi::where('jenis_transaksi', 'servis')
            ->where('status', 'lunas')
            ->when($from !== '', fn ($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('tanggal', '<=', $to))
            ->sum('diskon');

        $refundTotal = (float) Pembayaran::where('arah', 'keluar')
            ->where('status', 'terverifikasi')
            ->when($from !== '', fn ($q) => $q->whereDate('tanggal', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('tanggal', '<=', $to))
            ->sum('jumlah');

        $summary = [
            'total_inflow' => $carTotal + $serviceTotal,
            'car_total' => $carTotal,
            'car_offline_total' => $carOfflineTotal,
            'car_online_total' => $carOnlineTotal,
            'car_count' => $carInflows->count(),
            'service_total' => $serviceTotal,
            'service_offline_total' => $serviceOfflineTotal,
            'service_online_total' => $serviceOnlineTotal,
            'service_discount_total' => $serviceDiscountTotal,
            'service_count' => $serviceInflows->count(),
            'refund_total' => $refundTotal,
            'net_inflow' => max(0, ($carTotal + $serviceTotal) - $refundTotal),
        ];

        if ($request->input('export') === 'csv') {
            $exportRows = match ($jenis) {
                'penjualan_mobil' => $carInflows,
                'servis' => $serviceInflows,
                default => $carInflows->concat($serviceInflows)->sortByDesc(fn (array $r) => $r['date']?->timestamp ?? 0)->values(),
            };
            $filename = 'laporan-keuangan-' . now()->format('Y-m-d') . '.csv';
            return response()->streamDownload(function () use ($exportRows): void {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Kategori', 'Kode Bayar', 'Kode Transaksi', 'Referensi', 'Tanggal', 'Pelanggan', 'Unit / Kendaraan', 'Metode', 'Nominal']);
                foreach ($exportRows as $row) {
                    fputcsv($out, [
                        $row['category'],
                        $row['id'],
                        $row['trx_code'],
                        $row['ref_code'],
                        $row['date']?->format('Y-m-d H:i') ?? '',
                        $row['customer'],
                        $row['unit'],
                        $row['channel'],
                        number_format($row['amount'], 2, '.', ''),
                    ]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $allDates = $carInflows->concat($serviceInflows)
            ->map(fn (array $row) => $row['date']?->format('Y-m-d'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($allDates->count() === 1) {
            $single = \Carbon\Carbon::parse($allDates->first());
            $allDates = collect([
                $single->copy()->subDay()->format('Y-m-d'),
                $single->format('Y-m-d'),
            ]);
        }

        $carByDate = $carInflows->groupBy(fn (array $r) => $r['date']?->format('Y-m-d') ?? '')->map->sum('amount');
        $serviceByDate = $serviceInflows->groupBy(fn (array $r) => $r['date']?->format('Y-m-d') ?? '')->map->sum('amount');

        $trendSeries = [
            'labels' => $allDates->map(fn (string $d) => \Carbon\Carbon::parse($d)->format('d M Y'))->values()->all(),
            'car' => $allDates->map(fn (string $d) => (float) ($carByDate[$d] ?? 0))->values()->all(),
            'service' => $allDates->map(fn (string $d) => (float) ($serviceByDate[$d] ?? 0))->values()->all(),
            'total' => $allDates->map(fn (string $d) => (float) ($carByDate[$d] ?? 0) + (float) ($serviceByDate[$d] ?? 0))->values()->all(),
        ];

        return view('admin.pembayaran.report', compact(
            'from', 'to', 'jenis', 'summary', 'carInflows', 'serviceInflows', 'trendSeries'
        ));
    }
}
