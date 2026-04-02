<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Company::query()->whereNull('complaints_public_token')->each(function (Company $company) {
            $company->update(['complaints_public_token' => (string) Str::uuid()]);
        });
    }

    public function down(): void
    {
        //
    }
};
