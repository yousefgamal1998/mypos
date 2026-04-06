<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const PROTECTED_ROLE_NAMES = ['super_admin'];

    private const PROTECTED_USER_EMAILS = ['admin@admin.com', 'super_admin@app.com'];

    public function index(Request $request): View
    {
        $selectedRange = $this->normalizeAnalyticsRange(trim((string) $request->query('range')));
        $summaryCounts = $this->summaryCounts();
        $orderAnalytics = $this->buildOrderAnalytics($selectedRange);

        return view('Dashboard.index', compact('summaryCounts', 'orderAnalytics'));
    }

    private function summaryCounts(): array
    {
        return [
            'users' => User::query()
                ->whereNotIn('email', self::PROTECTED_USER_EMAILS)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', self::PROTECTED_ROLE_NAMES);
                })
                ->count(),
            'customers' => Customer::query()->count(),
            'products' => Product::query()->count(),
            'categories' => Category::query()->count(),
        ];
    }

    private function buildOrderAnalytics(string $selectedRange): array
    {
        $rangeOptions = $this->analyticsRangeOptions();
        $config = $rangeOptions[$selectedRange];
        $reference = now();

        $periodStart = $config['unit'] === 'day'
            ? $reference->copy()->subDays($config['points'] - 1)->startOfDay()
            : $reference->copy()->startOfMonth()->subMonths($config['points'] - 1)->startOfDay();
        $periodEnd = $reference->copy()->endOfDay();

        $buckets = $this->analyticsBuckets($config['unit'], $periodStart, $periodEnd);
        $orders = $this->ordersWithin($periodStart, $periodEnd);

        foreach ($orders as $order) {
            $createdAt = $order->created_at instanceof Carbon
                ? $order->created_at->copy()
                : Carbon::parse($order->created_at);

            $bucketKey = $this->analyticsBucketKey($createdAt, $config['unit']);

            if (! array_key_exists($bucketKey, $buckets)) {
                continue;
            }

            $buckets[$bucketKey]['sales'] += (float) $order->total_amount;
            $buckets[$bucketKey]['orders']++;
            $buckets[$bucketKey]['items'] += (int) $order->items_count;
        }

        $bucketCollection = collect(array_values($buckets))->map(function (array $bucket): array {
            $bucket['sales'] = round((float) $bucket['sales'], 2);

            return $bucket;
        })->values();

        $totalSales = round((float) $bucketCollection->sum('sales'), 2);
        $totalOrders = (int) $bucketCollection->sum('orders');
        $totalItems = (int) $bucketCollection->sum('items');

        return [
            'selectedRange' => $selectedRange,
            'selectedRangeLabel' => $config['label'],
            'rangeOptions' => collect($rangeOptions)
                ->map(fn (array $option, string $key) => [
                    'key' => $key,
                    'label' => $option['label'],
                ])
                ->values()
                ->all(),
            'periodLabel' => __('site.analytics_period', [
                'from' => $this->formatAnalyticsDate($periodStart),
                'to' => $this->formatAnalyticsDate($periodEnd),
            ]),
            'hasData' => $totalSales > 0 || $totalOrders > 0,
            'stats' => [
                'sales' => $totalSales,
                'orders' => $totalOrders,
                'average' => $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0.0,
                'items' => $totalItems,
            ],
            'chart' => [
                'labels' => $bucketCollection->pluck('label')->values()->all(),
                'ticks' => $this->analyticsTicks($bucketCollection, $config['unit']),
                'sales' => $bucketCollection
                    ->values()
                    ->map(fn (array $bucket, int $index) => [$index, $bucket['sales']])
                    ->all(),
                'orders' => $bucketCollection
                    ->values()
                    ->map(fn (array $bucket, int $index) => [$index, $bucket['orders']])
                    ->all(),
            ],
        ];
    }

    private function analyticsBuckets(string $unit, Carbon $periodStart, Carbon $periodEnd): array
    {
        $period = $unit === 'day'
            ? CarbonPeriod::create($periodStart->copy(), '1 day', $periodEnd->copy()->startOfDay())
            : CarbonPeriod::create($periodStart->copy()->startOfMonth(), '1 month', $periodEnd->copy()->startOfMonth());

        $buckets = [];

        foreach ($period as $date) {
            $localizedDate = $date->copy()->locale(app()->getLocale());
            $key = $this->analyticsBucketKey($localizedDate, $unit);

            $buckets[$key] = [
                'key' => $key,
                'label' => $unit === 'day'
                    ? $localizedDate->translatedFormat('d M')
                    : $localizedDate->translatedFormat('M Y'),
                'sales' => 0.0,
                'orders' => 0,
                'items' => 0,
            ];
        }

        return $buckets;
    }

    private function ordersWithin(Carbon $periodStart, Carbon $periodEnd): Collection
    {
        return Order::query()
            ->select('orders.id', 'orders.created_at')
            ->selectSub(
                DB::table('order_items')
                    ->selectRaw('COALESCE(SUM(quantity * unit_price), 0)')
                    ->whereColumn('order_id', 'orders.id'),
                'total_amount'
            )
            ->selectSub(
                DB::table('order_items')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('order_id', 'orders.id'),
                'items_count'
            )
            ->whereBetween('orders.created_at', [$periodStart, $periodEnd])
            ->orderBy('orders.created_at')
            ->get();
    }

    private function analyticsTicks(Collection $bucketCollection, string $unit): array
    {
        $lastIndex = max($bucketCollection->count() - 1, 0);

        return $bucketCollection
            ->values()
            ->map(function (array $bucket, int $index) use ($unit, $lastIndex): array {
                $tickLabel = $bucket['label'];

                if ($unit === 'day' && $index !== $lastIndex && $index % 4 !== 0) {
                    $tickLabel = '';
                }

                return [$index, $tickLabel];
            })
            ->all();
    }

    private function analyticsBucketKey(Carbon $date, string $unit): string
    {
        return $unit === 'day'
            ? $date->format('Y-m-d')
            : $date->format('Y-m');
    }

    private function formatAnalyticsDate(Carbon $date): string
    {
        return $date->copy()
            ->locale(app()->getLocale())
            ->translatedFormat('d M Y');
    }

    private function normalizeAnalyticsRange(?string $range): string
    {
        $rangeOptions = $this->analyticsRangeOptions();

        return is_string($range) && array_key_exists($range, $rangeOptions)
            ? $range
            : '6_months';
    }

    private function analyticsRangeOptions(): array
    {
        return [
            '30_days' => [
                'label' => __('site.last_30_days'),
                'unit' => 'day',
                'points' => 30,
            ],
            '6_months' => [
                'label' => __('site.last_6_months'),
                'unit' => 'month',
                'points' => 6,
            ],
            '12_months' => [
                'label' => __('site.last_12_months'),
                'unit' => 'month',
                'points' => 12,
            ],
        ];
    }
}
