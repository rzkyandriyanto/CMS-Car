<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\PromoMember;
use App\Services\{MemberIdentityService, PromoMemberService, YzaPointService};
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoMemberController extends Controller
{
    public function index(Request $request, MemberIdentityService $identity, PromoMemberService $promos): View
    {
        $customer = $request->user()->pelanggan; abort_unless($customer, 403);
        $hasVerifiedOwnership = app(\App\Services\VerifiedCarOwnershipService::class)->customerHasVerifiedPurchase((int) $customer->id_pelanggan);
        
        $claimedPromoCodes = \App\Models\RiwayatPoin::where('id_pelanggan', $customer->id_pelanggan)
            ->where('jenis', 'penukaran')
            ->pluck('keterangan')
            ->map(function ($ket) {
                if (preg_match('/\((YZA-[A-Z0-9]+)\)/i', $ket, $m)) return strtoupper($m[1]);
                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $claimedPromoIds = \App\Models\Notifikasi::where('id_user', $customer->id_user)
            ->where('referensi_tipe', 'promo')
            ->pluck('referensi_id')
            ->map(fn($id) => (int) $id)
            ->all();

        return view('pelanggan.promo.index', [
            'customer' => $customer,
            'items' => $promos->visible(),
            'hasVerifiedOwnership' => $hasVerifiedOwnership,
            'claimedPromoCodes' => $claimedPromoCodes,
            'claimedPromoIds' => $claimedPromoIds,
        ]);
    }
    public function show(Request $request, PromoMember $promoMember, MemberIdentityService $identity): View
    {
        $customer = $request->user()->pelanggan; abort_unless($customer, 403);
        abort_unless($promoMember->isVisibleAt(), 404);
        return view('pelanggan.promo.show', ['customer' => $customer, 'item' => $promoMember]);
    }
    public function claim(Request $request, PromoMember $promoMember, YzaPointService $points)
    {
        $customer = $request->user()->pelanggan;
        abort_unless($customer, 403);
        abort_unless($promoMember->isVisibleAt() && $promoMember->status === 'aktif', 404);

        try {
            $result = $points->redeemCoupon($customer, $promoMember);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($result);
            }
            return back()->with('success', $result['message']);
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors($e->getMessage());
        }
    }
}
