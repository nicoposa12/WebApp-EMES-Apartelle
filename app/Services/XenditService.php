<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.xendit.co';

    public function __construct()
    {
        $this->apiKey = env('XENDIT_SECRET_KEY');
    }

    /**
     * Create a Xendit Invoice using the REST API directly.
     * This avoids the SDK's ObjectSerializer bug ("Only variables should be passed by reference")
     * that occurs with newer PHP versions.
     */
    public function createInvoice($data)
    {
        try {
            $payload = [
                'external_id' => (string) $data['reservation_id'],
                'description' => 'Reservation: ' . $data['room_type'] . ' - Room #' . $data['room_number'],
                'amount' => (float) $data['total_amount'],
                'currency' => 'PHP',
                'success_redirect_url' => env('XENDIT_SUCCESS_URL', env('APP_URL') . '/booking/success'),
                'failure_redirect_url' => env('XENDIT_CANCEL_URL', env('APP_URL') . '/rooms'),
                'customer' => [
                    'given_names' => $data['customer_name'] ?? 'Guest',
                    'email' => $data['customer_email'] ?? '',
                    'mobile_number' => $data['customer_phone'] ?? '',
                ],
                // Only allow: Credit/Debit Card, E-Wallets, and QR Payments
                'payment_methods' => [
                    'CREDIT_CARD',   // Credit/Debit Card
                    'GCASH',         // E-Wallet: GCash
                    'PAYMAYA',       // E-Wallet: Maya (PayMaya)
                    'GRABPAY',       // E-Wallet: GrabPay
                    'SHOPEEPAY',     // E-Wallet: ShopeePay
                    'QRPH',          // QR Payments (QR Ph)
                ],
                'fees' => [],
                'items' => [
                    [
                        'name' => $data['room_type'] . ' - Room #' . $data['room_number'],
                        'quantity' => 1,
                        'price' => (float) $data['total_amount']
                    ]
                ]
            ];

            // Remove empty mobile_number to avoid Xendit validation errors
            if (empty($payload['customer']['mobile_number'])) {
                unset($payload['customer']['mobile_number']);
            }

            Log::info('Xendit Invoice Request:', ['payload' => $payload]);

            $response = Http::withBasicAuth($this->apiKey, '')
                ->timeout(30)
                ->post($this->baseUrl . '/v2/invoices', $payload);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Xendit Invoice Created Successfully', [
                    'invoice_id' => $result['id'] ?? null,
                    'invoice_url' => $result['invoice_url'] ?? null,
                ]);

                return [
                    'status' => 'success',
                    'invoice_url' => $result['invoice_url'],
                    'id' => $result['id']
                ];
            }

            Log::error('Xendit Invoice Creation Failed (HTTP ' . $response->status() . '): ' . $response->body());
            return [
                'status' => 'error',
                'message' => 'Xendit returned HTTP ' . $response->status() . ': ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Xendit Invoice Creation Exception: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
