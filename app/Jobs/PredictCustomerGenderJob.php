<?php
namespace App\Jobs;

use App\Enums\Gender;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PredictCustomerGenderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 10;

    public function __construct(
        public Customer $customer
    ) {}

    public function handle(): void
    {
        // Skip if gender is already set (not unknown)
        if ($this->customer->gender !== Gender::UNKOWN) {
            return;
        }

        // Get customer name 
        $name = $this->customer->user?->name;

        if (empty($name)) {
            Log::info("PredictCustomerGenderJob: No name found for customer #{$this->customer->id}");
            return;
        }

        try {
            $baseUrl = config('services.ai_gender.base_url');

            /** @var Response $response */
            $response = Http::timeout(10)
                ->post("{$baseUrl}/predict", [
                    'name' => $name,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    $predictedGender = $data['data']['gender'] ?? null;
                    $confidence      = $data['data']['confidence'] ?? 0;

                    // Only update if confidence is high enough (> 70%)
                    if ($predictedGender && $confidence >= 0.7) {
                        $genderEnum = match ($predictedGender) {
                            'male'   => Gender::MALE,
                            'female' => Gender::FEMALE,
                            default  => Gender::UNKOWN,
                        };

                        $this->customer->update(['gender' => $genderEnum]);

                        Log::info("PredictCustomerGenderJob: Updated customer #{$this->customer->id} gender to {$predictedGender} (confidence: {$confidence})");
                    }
                }
            } else {
                Log::warning("PredictCustomerGenderJob: API returned error for customer #{$this->customer->id}", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("PredictCustomerGenderJob: Failed for customer #{$this->customer->id}", [
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }
}
