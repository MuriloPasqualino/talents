<?php

namespace App\Services\Commercial;

use App\Enums\CommercialProductPricingType;
use App\Models\CommercialProduct;
use Illuminate\Support\Collection;

class CommercialProductPricingService
{
    /**
     * @param  array<string, mixed>  $selection  enabled, modality?, salary_cents?
     * @return array{total_cents: int, detail: string}
     */
    public function calculateLine(CommercialProduct $product, int $employeeCount, array $selection): array
    {
        if (! ($selection['enabled'] ?? false)) {
            return ['total_cents' => 0, 'subtotal_cents' => 0, 'detail' => ''];
        }

        $config = $product->pricing_config ?? [];
        $employees = max(0, $employeeCount);

        $total = match ($product->pricing_type) {
            CommercialProductPricingType::Fixed => (int) ($config['amount_cents'] ?? 0),
            CommercialProductPricingType::PerEmployee => $employees > 0
                ? $employees * (int) ($config['cents_per_employee'] ?? 0)
                : 0,
            CommercialProductPricingType::TieredPerEmployee => $employees > 0
                ? $employees * $this->pickTier(
                    $employees,
                    [
                        (int) ($config['tier1_max'] ?? 5),
                        (int) ($config['tier2_max'] ?? 10),
                        (int) ($config['tier3_max'] ?? 20),
                    ],
                    [
                        (int) ($config['tier1_cents'] ?? 0),
                        (int) ($config['tier2_cents'] ?? 0),
                        (int) ($config['tier3_cents'] ?? 0),
                        (int) ($config['tier4_cents'] ?? 0),
                    ],
                )
                : 0,
            CommercialProductPricingType::FixedModality => $this->modalityCents($config, (string) ($selection['modality'] ?? '')),
            CommercialProductPricingType::SalaryTimesEmployees => $employees > 0
                ? $employees * max(0, (int) ($selection['salary_cents'] ?? 0))
                : 0,
            CommercialProductPricingType::ThresholdMultiplier => $this->thresholdTotal($employees, $config),
            CommercialProductPricingType::FlexibleRates => $this->flexibleRatesTotal($config, $selection)['total'],
            default => 0,
        };

        $flexible = $product->pricing_type === CommercialProductPricingType::FlexibleRates
            ? $this->flexibleRatesTotal($config, $selection)
            : null;

        return [
            'total_cents' => max(0, $total),
            'subtotal_cents' => $flexible['subtotal'] ?? max(0, $total),
            'detail' => $this->buildDetail($product, $employees, $selection, $total, $flexible),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $selections
     * @return array{total_cents: int, lines: array<int, array<string, mixed>>}
     */
    public function calculateMany(Collection $products, int $employeeCount, array $selections): array
    {
        $byId = $products->keyBy('id');
        $lines = [];
        $total = 0;

        foreach ($selections as $selection) {
            $productId = (int) ($selection['product_id'] ?? 0);
            /** @var CommercialProduct|null $product */
            $product = $byId->get($productId);
            if (! $product || ! $product->is_active) {
                continue;
            }

            $result = $this->calculateLine($product, $employeeCount, $selection);
            if (! $this->shouldIncludeLine($selection, $result)) {
                continue;
            }

            $lines[] = [
                'product_id' => $product->id,
                'key' => $product->slug,
                'label' => $product->name,
                'detail' => $result['detail'],
                'value_cents' => $result['total_cents'],
                'options' => $this->selectionOptions($selection, $result),
            ];
            $total += $result['total_cents'];
        }

        return ['total_cents' => $total, 'lines' => $lines];
    }

    /**
     * @param  array<string, mixed>  $selection
     * @param  array{total_cents: int, subtotal_cents: int, detail: string}  $result
     */
    private function shouldIncludeLine(array $selection, array $result): bool
    {
        if (! ($selection['enabled'] ?? false)) {
            return false;
        }

        if ($result['total_cents'] > 0) {
            return true;
        }

        return ($selection['adjustment'] ?? 'none') === 'bonus'
            && ($result['subtotal_cents'] ?? 0) > 0;
    }

    /**
     * @param  array<string, mixed>  $selection
     * @param  array{total_cents: int, subtotal_cents: int, detail: string}  $result
     * @return array<string, mixed>
     */
    private function selectionOptions(array $selection, array $result): array
    {
        return array_filter([
            'modality' => $selection['modality'] ?? null,
            'salary_cents' => isset($selection['salary_cents']) ? (int) $selection['salary_cents'] : null,
            'rate_mode' => $selection['rate_mode'] ?? null,
            'units' => isset($selection['units']) ? (float) $selection['units'] : null,
            'adjustment' => $selection['adjustment'] ?? null,
            'discount_percent' => isset($selection['discount_percent']) ? (float) $selection['discount_percent'] : null,
            'subtotal_cents' => ($result['subtotal_cents'] ?? 0) > 0 ? (int) $result['subtotal_cents'] : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $selection
     * @return array{subtotal: int, total: int}
     */
    private function flexibleRatesTotal(array $config, array $selection): array
    {
        $mode = (string) ($selection['rate_mode'] ?? '');
        $rate = $config['rates'][$mode] ?? null;

        if (! is_array($rate) || ! ($rate['enabled'] ?? false)) {
            return ['subtotal' => 0, 'total' => 0];
        }

        $units = max(0, (float) ($selection['units'] ?? 0));
        if ($units <= 0) {
            return ['subtotal' => 0, 'total' => 0];
        }

        $subtotal = (int) round($units * (int) ($rate['cents_per_unit'] ?? 0));
        $adjustment = (string) ($selection['adjustment'] ?? 'none');

        $total = match ($adjustment) {
            'bonus' => 0,
            'discount' => (int) round($subtotal * (1 - min(100, max(0, (float) ($selection['discount_percent'] ?? 0))) / 100)),
            default => $subtotal,
        };

        return ['subtotal' => $subtotal, 'total' => max(0, $total)];
    }

    public static function flexibleRateModeLabel(string $mode): string
    {
        return match ($mode) {
            'hour' => 'Por hora',
            'quantity' => 'Por quantidade',
            'unit' => 'Por unidade',
            default => $mode,
        };
    }

    public static function flexibleUnitsSuffix(string $mode): string
    {
        return match ($mode) {
            'hour' => 'h',
            'quantity' => 'un.',
            'unit' => 'un.',
            default => '',
        };
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function modalityCents(array $config, string $modality): int
    {
        if ($modality === '') {
            return 0;
        }

        foreach ($config['modalities'] ?? [] as $item) {
            if (($item['key'] ?? '') === $modality) {
                return (int) ($item['cents'] ?? 0);
            }
        }

        return 0;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function thresholdTotal(int $employees, array $config): int
    {
        $base = (int) ($config['base_cents'] ?? 0);
        $threshold = (int) ($config['threshold_employees'] ?? 30);
        $multiplier = max(1, (int) ($config['multiplier'] ?? 2));

        return $employees > $threshold ? $base * $multiplier : $base;
    }

    /**
     * @param  array<string, mixed>  $selection
     * @param  array{subtotal: int, total: int}|null  $flexible
     */
    private function buildDetail(
        CommercialProduct $product,
        int $employees,
        array $selection,
        int $totalCents,
        ?array $flexible = null,
    ): string {
        if ($product->pricing_type === CommercialProductPricingType::FlexibleRates) {
            return $this->buildFlexibleDetail($selection, $flexible ?? ['subtotal' => 0, 'total' => 0], $product->pricing_config ?? []);
        }

        if ($totalCents <= 0) {
            return '—';
        }

        return match ($product->pricing_type) {
            CommercialProductPricingType::Fixed => 'Valor fixo',
            CommercialProductPricingType::PerEmployee,
            CommercialProductPricingType::TieredPerEmployee => "{$employees} funcionários",
            CommercialProductPricingType::FixedModality => $this->modalityLabel($product->pricing_config ?? [], (string) ($selection['modality'] ?? '')),
            CommercialProductPricingType::SalaryTimesEmployees => sprintf(
                'Salário base R$ %s × %d funcionários',
                number_format(((int) ($selection['salary_cents'] ?? 0)) / 100, 2, ',', '.'),
                $employees,
            ),
            CommercialProductPricingType::ThresholdMultiplier => $employees > (int) ($product->pricing_config['threshold_employees'] ?? 30)
                ? 'Pacote ampliado'
                : 'Pacote padrão',
            default => '—',
        };
    }

    /**
     * @param  array<string, mixed>  $selection
     * @param  array{subtotal: int, total: int}  $flexible
     * @param  array<string, mixed>  $config
     */
    private function buildFlexibleDetail(array $selection, array $flexible, array $config): string
    {
        if ($flexible['subtotal'] <= 0) {
            return '—';
        }

        $mode = (string) ($selection['rate_mode'] ?? '');
        $units = (float) ($selection['units'] ?? 0);
        $centsPerUnit = (int) ($config['rates'][$mode]['cents_per_unit'] ?? 0);
        $suffix = self::flexibleUnitsSuffix($mode);

        $base = sprintf(
            '%s %s × R$ %s',
            rtrim(rtrim(number_format($units, 2, ',', '.'), '0'), ','),
            $suffix,
            number_format($centsPerUnit / 100, 2, ',', '.'),
        );

        $adjustment = (string) ($selection['adjustment'] ?? 'none');

        return match ($adjustment) {
            'bonus' => $base.' · Bonificação',
            'discount' => $base.sprintf(
                ' · Desconto %s%%',
                rtrim(rtrim(number_format((float) ($selection['discount_percent'] ?? 0), 2, ',', '.'), '0'), ','),
            ),
            default => $base,
        };
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function modalityLabel(array $config, string $modality): string
    {
        foreach ($config['modalities'] ?? [] as $item) {
            if (($item['key'] ?? '') === $modality) {
                return (string) ($item['label'] ?? $modality);
            }
        }

        return $modality !== '' ? $modality : '—';
    }

    /**
     * @param  array<int, int>  $maxes
     * @param  array<int, int>  $values
     */
    private function pickTier(int $employees, array $maxes, array $values): int
    {
        foreach ($maxes as $i => $max) {
            if ($employees <= $max) {
                return (int) $values[$i];
            }
        }

        return (int) $values[count($maxes)];
    }
}
