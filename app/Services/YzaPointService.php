<?php

namespace App\Services;

use App\Models\{Notifikasi, Pelanggan, PoinMember, RiwayatPoin, Transaksi};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class YzaPointService
{
    public const ELIGIBLE_TYPES = ['servis', 'cuci_mobil', 'penjualan_sparepart', 'penjualan_mobil', 'trade_in'];

    public function rate(): int { return (int) config('yza.points.rupiah_per_point', 10000); }

    public function carPurchasePoints(): int { return (int) config('yza.points.car_purchase_points', 500); }

    public function calculate(string|int $total, ?string $type = null): int
    {
        if ($type === 'penjualan_mobil') {
            return $this->carPurchasePoints();
        }

        $normalized = trim(str_replace([',', ' '], '', (string) $total));
        $whole = explode('.', $normalized, 2)[0] ?? '0';
        $whole = ltrim($whole, '+-');
        if ($whole === '' || !ctype_digit($whole)) return 0;
        return intdiv((int) $whole, $this->rate());
    }

    public function awardIfEligible(Transaksi $transaction): int
    {
        return DB::transaction(function () use ($transaction): int {
            $tx = Transaksi::lockForUpdate()->findOrFail($transaction->id_transaksi);
            if ($tx->status !== 'lunas' || !in_array($tx->jenis_transaksi, self::ELIGIBLE_TYPES, true)) return 0;
            $customer = Pelanggan::with('user.role')->find($tx->id_pelanggan);
            if (!$customer || !$this->isMember($customer)) return 0;
            if (!$this->domainCompleted($tx)) return 0;
            $points = $tx->jenis_transaksi === 'penjualan_mobil'
                ? $this->carPurchasePoints()
                : $this->calculate((string) $tx->total);
            if ($points <= 0) return 0;
            $key = 'earn:transaction:'.$tx->id_transaksi;
            if (RiwayatPoin::where('idempotency_key', $key)->lockForUpdate()->exists()) return 0;

            DB::table('poin_member')->insertOrIgnore([
                'id_pelanggan' => $customer->id_pelanggan,
                'saldo' => 0,
                'total_diperoleh' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $account = PoinMember::where('id_pelanggan', $customer->id_pelanggan)->lockForUpdate()->firstOrFail();
            $newBalance = (int) $account->saldo + $points;
            $ledger = new RiwayatPoin();
            $ledger->forceFill([
                'id_pelanggan' => $customer->id_pelanggan,
                'id_transaksi' => $tx->id_transaksi,
                'jenis' => 'perolehan',
                'perubahan_poin' => $points,
                'saldo_setelah' => $newBalance,
                'idempotency_key' => $key,
                'keterangan' => $tx->jenis_transaksi === 'penjualan_mobil'
                    ? 'Perolehan '.$points.' YZA Points dari Pembelian Mobil ('.$tx->kode_transaksi.')'
                    : 'Perolehan YZA Points dari '.$tx->kode_transaksi,
            ])->save();
            $account->forceFill([
                'saldo' => $newBalance,
                'total_diperoleh' => (int) $account->total_diperoleh + $points,
            ])->save();

            if ($tx->jenis_transaksi === 'penjualan_mobil') {
                $this->sendCarPurchasePointsNotification($customer, $points);
            }

            return $points;
        });
    }

    public function ensureAccount(Pelanggan $customer): ?PoinMember
    {
        if (!$this->isMember($customer)) return null;
        DB::table('poin_member')->insertOrIgnore([
            'id_pelanggan' => $customer->id_pelanggan,
            'saldo' => 0,
            'total_diperoleh' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return PoinMember::where('id_pelanggan', $customer->id_pelanggan)->first();
    }

    public function isMember(Pelanggan $customer): bool
    {
        return $customer->status === 'aktif'
            && $customer->user?->status === 'aktif'
            && $customer->user?->role?->nama_role === 'pelanggan'
            && (!$customer->membership || $customer->membership->isActive());
    }

    public function label(string $type): string
    {
        return [
            'servis' => 'Servis Kendaraan',
            'cuci_mobil' => 'Car Wash',
            'penjualan_sparepart' => 'Pembelian Spare Part',
            'penjualan_mobil' => 'Pembelian Mobil',
            'trade_in' => 'Trade-In',
        ][$type] ?? ucwords(str_replace('_', ' ', $type));
    }

    private function domainCompleted(Transaksi $tx): bool
    {
        return match ($tx->jenis_transaksi) {
            'servis' => DB::table('servis')->where('id_transaksi', $tx->id_transaksi)->where('status', 'selesai')->exists(),
            'cuci_mobil' => DB::table('booking_cuci')->where('id_transaksi', $tx->id_transaksi)->where('status', 'selesai')->exists(),
            'penjualan_sparepart' => DB::table('penjualan_sparepart')->where('id_transaksi', $tx->id_transaksi)->where('status', 'selesai')->exists(),
            'penjualan_mobil' => DB::table('penjualan_mobil')->where('id_transaksi', $tx->id_transaksi)->where('status', 'selesai')
                ->whereExists(fn($q) => $q->from('mobil')->whereColumn('mobil.id_mobil', 'penjualan_mobil.id_mobil')->where('mobil.status', 'terjual'))->exists(),
            'trade_in' => DB::table('trade_in')->where('id_transaksi', $tx->id_transaksi)->where('status', 'selesai')->exists(),
            default => false,
        };
    }

    public function sendCarPurchasePointsNotification(Pelanggan $customer, int $points): void
    {
        $userId = $customer->id_user ?? $customer->user?->id_user;
        if (!$userId) return;

        $title = 'Selamat! Anda Mendapatkan ' . $points . ' Poin';
        $message = 'Selamat atas pembelian mobil Anda! Anda mendapatkan ' . $points . ' poin loyalitas yang dapat digunakan untuk klaim promo servis dan carwash.';

        $exists = Notifikasi::query()
            ->where('id_user', $userId)
            ->where('referensi_tipe', 'promo')
            ->where('judul', $title)
            ->exists();

        if (!$exists) {
            Notifikasi::create([
                'id_user' => $userId,
                'judul' => $title,
                'pesan' => $message,
                'tipe' => 'sukses',
                'referensi_tipe' => 'promo',
                'referensi_id' => null,
                'is_read' => false,
            ]);
        }
    }

    public function redeemCoupon(Pelanggan $customer, \App\Models\PromoMember $promo): array
    {
        return DB::transaction(function () use ($customer, $promo): array {
            $cost = (int) ($promo->points_cost ?? 0);
            $account = PoinMember::where('id_pelanggan', $customer->id_pelanggan)->lockForUpdate()->first();
            $balance = (int) ($account?->saldo ?? 0);

            if ($cost > 0) {
                if ($balance < $cost) {
                    throw new RuntimeException("Poin tidak mencukupi. Dibutuhkan {$cost} poin, saldo Anda {$balance} poin.");
                }

                $newBalance = $balance - $cost;
                $key = 'redeem:promo:' . $promo->id_promo . ':cust:' . $customer->id_pelanggan . ':time:' . hrtime(true);

                $ledger = new RiwayatPoin();
                $ledger->forceFill([
                    'id_pelanggan' => $customer->id_pelanggan,
                    'id_transaksi' => null,
                    'jenis' => 'penukaran',
                    'perubahan_poin' => $cost,
                    'saldo_setelah' => $newBalance,
                    'idempotency_key' => $key,
                    'keterangan' => 'Penukaran ' . $cost . ' YZA Points untuk ' . $promo->nama . ' (' . $promo->kode_promo . ')',
                ])->save();

                $account->forceFill([
                    'saldo' => $newBalance,
                ])->save();
            } else {
                $newBalance = $balance;
            }

            if ($customer->id_user) {
                Notifikasi::create([
                    'id_user' => $customer->id_user,
                    'judul' => 'Kupon Berhasil Diklaim: ' . $promo->kode_promo,
                    'pesan' => 'Selamat! Anda berhasil menukarkan ' . ($cost > 0 ? $cost . ' poin' : 'benefit member') . ' untuk ' . $promo->nama . '. Gunakan kode promo ' . $promo->kode_promo . ' saat bertransaksi.',
                    'tipe' => 'sukses',
                    'referensi_tipe' => 'promo',
                    'referensi_id' => $promo->id_promo,
                    'is_read' => false,
                ]);
            }

            return [
                'success' => true,
                'promo_code' => $promo->kode_promo,
                'promo_name' => $promo->nama,
                'cost' => $cost,
                'balance' => $newBalance,
                'message' => 'Selamat! Kupon ' . $promo->nama . ' berhasil diklaim' . ($cost > 0 ? ' dengan ' . $cost . ' poin.' : '.') . ' Kode promo Anda: ' . $promo->kode_promo,
            ];
        });
    }
}
