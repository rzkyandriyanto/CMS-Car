<?php

namespace Tests\Feature;

use App\Models\{Membership, PembayaranGateway, Pelanggan, User};
use App\Services\{IndonesiaRegionService, MidtransClient};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationStep3PaymentButtonTest extends TestCase
{
    use DatabaseTransactions;

    private function validRegionPayload(): array
    {
        $regions = app(IndonesiaRegionService::class);
        $province = $regions->children('provinces', null)->first();
        $city = $regions->children('cities', $province['code'])->first();
        $district = $regions->children('districts', $city['code'])->first();
        $village = $regions->children('villages', $district['code'])->first();

        return [
            'province_code' => $province['code'],
            'province_name' => $province['name'],
            'city_code' => $city['code'],
            'city_name' => $city['name'],
            'district_code' => $district['code'],
            'district_name' => $district['name'],
            'village_code' => $village['code'],
            'village_name' => $village['name'],
            'address_detail' => 'Jl. Sudirman No. 10',
        ];
    }

    public function test_step_3_renders_submit_button_and_safe_step_validation(): void
    {
        $response = $this->get('/register?step=3');

        $response->assertOk()
            ->assertSee('id="register-submit-btn"', false)
            ->assertSee('type="submit"', false)
            ->assertSee('id="membership_confirmed"', false)
            ->assertSee('id="register-client-error"', false)
            ->assertSee('Memproses ke Pembayaran...', false)
            ->assertDontSee('!form.reportValidity()', false);
    }

    public function test_register_requires_membership_confirmation_checkbox(): void
    {
        $payload = array_merge([
            'nama_lengkap' => 'Test Member',
            'no_hp' => '081234567801',
            'email' => 'unconfirmed.member@yza.local',
            'username' => 'unconfirmed_member_01',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1995-05-10',
            'current_step' => '3',
        ], $this->validRegionPayload());

        $response = $this->from('/register?step=3')->post('/register', $payload);

        $response->assertRedirect('/register?from=login&step=3')
            ->assertSessionHasErrors('membership_confirmed');
        $this->assertNull(User::where('username', 'unconfirmed_member_01')->first());
    }

    public function test_register_step_3_submits_creates_pending_membership_and_initiates_midtrans_sandbox(): void
    {
        config([
            'midtrans.server_key' => 'SB-Mid-server-test-key',
            'midtrans.client_key' => 'SB-Mid-client-test-key',
            'midtrans.is_production' => false,
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-test-001',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-test-001',
            ], 201),
        ]);

        $payload = array_merge([
            'nama_lengkap' => 'Registrasi Midtrans',
            'no_hp' => '081234567802',
            'email' => 'reg.midtrans@yza.local',
            'username' => 'reg_midtrans_01',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1996-06-15',
            'membership_confirmed' => '1',
            'current_step' => '3',
        ], $this->validRegionPayload());

        $registerResponse = $this->post('/register', $payload);
        $registerResponse->assertRedirect(route('pelanggan.membership.activation'));

        $user = User::where('username', 'reg_midtrans_01')->firstOrFail();
        $customer = Pelanggan::where('id_user', $user->id_user)->firstOrFail();
        $membership = Membership::where('id_pelanggan', $customer->id_pelanggan)->firstOrFail();

        $this->assertSame('pending_payment', $membership->status);
        $this->assertSame('249000.00', number_format((float) $membership->activation_fee, 2, '.', ''));

        // First click creates Midtrans Sandbox payment attempt
        $payResponse = $this->actingAs($user)->post(route('pelanggan.membership.pay'));
        $payResponse->assertRedirect('https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-test-001');

        $gateway = PembayaranGateway::where('id_membership', $membership->id_membership)->firstOrFail();
        $this->assertSame('pending', $gateway->status_provider);
        $this->assertSame('249000.00', number_format((float) $gateway->gross_amount, 2, '.', ''));

        // Double click / repeat request reuses active attempt without creating duplicate payment
        $secondPayResponse = $this->actingAs($user)->post(route('pelanggan.membership.pay'));
        $secondPayResponse->assertRedirect('https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-test-001');
        $this->assertSame(1, PembayaranGateway::where('id_membership', $membership->id_membership)->count());

        // Membership remains pending_payment until verified settlement notification
        $this->assertSame('pending_payment', $membership->fresh()->status);

        $signature = hash('sha512', $gateway->order_id . '200' . '249000.00' . 'SB-Mid-server-test-key');
        Http::fake([
            "https://api.sandbox.midtrans.com/v2/{$gateway->order_id}/status" => Http::response([
                'order_id' => $gateway->order_id,
                'transaction_id' => 'MT-MBR-SETTLE-001',
                'transaction_status' => 'settlement',
                'status_code' => '200',
                'gross_amount' => '249000.00',
                'payment_type' => 'qris',
                'fraud_status' => 'accept',
                'signature_key' => $signature,
            ], 200),
        ]);

        $this->postJson('/payment/midtrans/notification', [
            'order_id' => $gateway->order_id,
            'transaction_id' => 'MT-MBR-SETTLE-001',
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => '249000.00',
            'payment_type' => 'qris',
            'fraud_status' => 'accept',
            'signature_key' => $signature,
        ])->assertOk();

        $membership->refresh();
        $this->assertSame('active', $membership->status);
        $this->assertSame($gateway->id_pembayaran_gateway, (int) $membership->gateway_payment_id);

        // Dashboard Member opens cleanly without HTTP 500
        $this->actingAs($user->fresh())->get(route('pelanggan.dashboard'))->assertOk();
    }
}
