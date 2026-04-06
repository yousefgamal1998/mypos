@extends('layouts.app')

@section('page-title', __('site.dashboard'))

@php
    $summaryCards = collect([
        [
            'key' => 'users',
            'label' => __('site.users'),
            'count' => $summaryCounts['users'] ?? 0,
            'route' => route('dashboard.users.index'),
            'icon' => 'fas fa-users',
            'color' => 'bg-warning',
        ],
        [
            'key' => 'customers',
            'label' => __('site.customers'),
            'count' => $summaryCounts['customers'] ?? 0,
            'route' => route('dashboard.customers.index'),
            'icon' => 'fas fa-user',
            'color' => 'bg-danger',
        ],
        [
            'key' => 'products',
            'label' => __('site.products'),
            'count' => $summaryCounts['products'] ?? 0,
            'route' => route('dashboard.products.index'),
            'icon' => 'fas fa-chart-bar',
            'color' => 'bg-success',
        ],
        [
            'key' => 'categories',
            'label' => __('site.categories'),
            'count' => $summaryCounts['categories'] ?? 0,
            'route' => route('dashboard.categories.index'),
            'icon' => 'fas fa-shopping-bag',
            'color' => 'bg-info',
        ],
    ]);

    if (app()->isLocale('ar')) {
        $summaryCards = $summaryCards->reverse()->values();
    }

    $orderStats = [
        [
            'key' => 'sales',
            'label' => __('site.sales_total'),
            'value' => $orderAnalytics['stats']['sales'] ?? 0,
            'decimals' => 2,
            'icon' => 'fas fa-wallet',
            'accent' => 'primary',
        ],
        [
            'key' => 'orders',
            'label' => __('site.orders'),
            'value' => $orderAnalytics['stats']['orders'] ?? 0,
            'decimals' => 0,
            'icon' => 'fas fa-shopping-cart',
            'accent' => 'warning',
        ],
        [
            'key' => 'average',
            'label' => __('site.average_order'),
            'value' => $orderAnalytics['stats']['average'] ?? 0,
            'decimals' => 2,
            'icon' => 'fas fa-receipt',
            'accent' => 'success',
        ],
        [
            'key' => 'items',
            'label' => __('site.items_sold'),
            'value' => $orderAnalytics['stats']['items'] ?? 0,
            'decimals' => 0,
            'icon' => 'fas fa-box-open',
            'accent' => 'info',
        ],
    ];
@endphp

@push('styles')
    .dashboard-summary-row {
        row-gap: 1rem;
    }

    .dashboard-summary-card {
        margin-bottom: 0;
    }

    .dashboard-summary-card .inner {
        min-height: 138px;
    }

    .dashboard-summary-card .inner h3 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 0.4rem;
    }

    .dashboard-summary-card .inner p {
        font-size: 1.55rem;
        font-weight: 600;
        margin-bottom: 0;
    }

    .dashboard-summary-card .icon {
        opacity: 0.22;
    }

    .dashboard-summary-card .icon > i {
        font-size: 4.8rem;
        top: 18px;
    }

    .dashboard-summary-card .small-box-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        font-size: 1.2rem;
        font-weight: 600;
        padding: 0.45rem 0.75rem;
    }

    .dashboard-analytics-card {
        --analytics-primary: #1f6aa5;
        --analytics-primary-soft: rgba(31, 106, 165, 0.12);
        --analytics-warning: #f39c12;
        --analytics-warning-soft: rgba(243, 156, 18, 0.16);
        --analytics-success: #1f9d6a;
        --analytics-success-soft: rgba(31, 157, 106, 0.14);
        --analytics-info: #17a2b8;
        --analytics-info-soft: rgba(23, 162, 184, 0.14);
        --analytics-border: #e5edf5;
        --analytics-muted: #6c7b8a;
        margin-top: 1.5rem;
        border-radius: 18px;
        overflow: hidden;
    }

    .dashboard-analytics-card .card-header {
        background: #fff;
        padding-bottom: 0.75rem;
    }

    .dashboard-analytics-intro {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .dashboard-analytics-title {
        color: #17324d;
        font-size: 1.55rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .dashboard-analytics-subtitle {
        color: var(--analytics-muted);
        margin-bottom: 0;
    }

    .dashboard-analytics-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .dashboard-range-switch {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .dashboard-range-switch .btn {
        border-radius: 999px;
        border: 1px solid #d5e1ec;
        background: #fff;
        color: #4d6175;
        box-shadow: none;
        font-weight: 600;
        padding: 0.45rem 0.95rem;
    }

    .dashboard-range-switch .btn:hover,
    .dashboard-range-switch .btn:focus,
    .dashboard-range-switch .btn.active {
        background: var(--analytics-primary);
        border-color: var(--analytics-primary);
        color: #fff;
    }

    .dashboard-analytics-period {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.85rem;
        border: 1px solid var(--analytics-border);
        border-radius: 999px;
        background: #f7fbff;
        color: #567084;
        font-weight: 600;
    }

    .dashboard-analytics-stats {
        row-gap: 1rem;
        margin-bottom: 1.35rem;
    }

    .dashboard-analytics-stat {
        height: 100%;
        border-radius: 16px;
        border: 1px solid var(--analytics-border);
        background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
        padding: 1rem 1.1rem;
    }

    .dashboard-analytics-stat__inner {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
    }

    .dashboard-analytics-stat__icon {
        width: 3rem;
        height: 3rem;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .dashboard-analytics-stat--primary .dashboard-analytics-stat__icon {
        color: var(--analytics-primary);
        background: var(--analytics-primary-soft);
    }

    .dashboard-analytics-stat--warning .dashboard-analytics-stat__icon {
        color: var(--analytics-warning);
        background: var(--analytics-warning-soft);
    }

    .dashboard-analytics-stat--success .dashboard-analytics-stat__icon {
        color: var(--analytics-success);
        background: var(--analytics-success-soft);
    }

    .dashboard-analytics-stat--info .dashboard-analytics-stat__icon {
        color: var(--analytics-info);
        background: var(--analytics-info-soft);
    }

    .dashboard-analytics-stat__value {
        color: #17324d;
        font-size: 1.65rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 0.25rem;
    }

    .dashboard-analytics-stat__label {
        color: var(--analytics-muted);
        font-weight: 600;
        line-height: 1.35;
    }

    .dashboard-chart-shell {
        border: 1px solid var(--analytics-border);
        border-radius: 18px;
        background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
        padding: 1rem;
        min-height: 410px;
    }

    .dashboard-chart-legends {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 0.85rem;
    }

    .dashboard-chart-legend-group {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .dashboard-chart-legend {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #5a6b7c;
        font-weight: 600;
    }

    .dashboard-chart-legend-swatch {
        display: inline-block;
        width: 0.85rem;
        height: 0.85rem;
        flex-shrink: 0;
    }

    .dashboard-chart-legend-swatch--sales {
        background: var(--analytics-primary);
        border-radius: 999px;
    }

    .dashboard-chart-legend-swatch--orders {
        background: var(--analytics-warning);
        border-radius: 0.2rem;
    }

    .dashboard-chart-range-label {
        color: var(--analytics-muted);
        font-weight: 600;
    }

    .dashboard-chart-canvas {
        width: 100%;
        height: 320px;
    }

    .dashboard-chart-empty {
        min-height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0.75rem;
        color: var(--analytics-muted);
        text-align: center;
    }

    .dashboard-chart-empty i {
        font-size: 2.8rem;
        color: #b8c4d0;
    }

    .dashboard-chart-tooltip {
        position: absolute;
        z-index: 1080;
        pointer-events: none;
        opacity: 0;
        transform: translateY(8px);
        transition: opacity 0.15s ease, transform 0.15s ease;
        background: #17324d;
        color: #fff;
        border-radius: 12px;
        padding: 0.7rem 0.85rem;
        box-shadow: 0 18px 32px rgba(23, 50, 77, 0.24);
        font-size: 0.9rem;
        line-height: 1.4;
        white-space: nowrap;
    }

    .dashboard-chart-tooltip.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .dashboard-chart-tooltip__label {
        display: block;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }

    @media (max-width: 767.98px) {
        .dashboard-summary-card .inner {
            min-height: auto;
        }

        .dashboard-summary-card .inner h3 {
            font-size: 2.4rem;
        }

        .dashboard-summary-card .inner p {
            font-size: 1.25rem;
        }

        .dashboard-summary-card .icon > i {
            font-size: 4rem;
            top: 20px;
        }

        .dashboard-analytics-actions {
            justify-content: flex-start;
        }

        .dashboard-chart-legends {
            align-items: flex-start;
        }

        .dashboard-chart-range-label {
            width: 100%;
        }

        .dashboard-chart-shell {
            min-height: 360px;
            padding: 0.85rem;
        }

        .dashboard-chart-canvas {
            height: 280px;
        }
    }
@endpush

@section('content')
    <div class="row dashboard-summary-row">
        @foreach ($summaryCards as $card)
            <div class="col-lg-3 col-sm-6">
                <div
                    class="small-box {{ $card['color'] }} dashboard-summary-card"
                    data-summary-card="{{ $card['key'] }}"
                    data-summary-count="{{ $card['count'] }}"
                    data-summary-metric="{{ $card['key'] }}:{{ $card['count'] }}"
                >
                    <div class="inner">
                        <h3>{{ $card['count'] }}</h3>
                        <p>{{ $card['label'] }}</p>
                    </div>
                    <div class="icon">
                        <i class="{{ $card['icon'] }}" aria-hidden="true"></i>
                    </div>
                    <a href="{{ $card['route'] }}" class="small-box-footer">
                        <span>@lang('site.show')</span>
                        <i class="fas fa-arrow-circle-left" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div
        class="card card-outline card-primary dashboard-analytics-card"
        data-order-range="{{ $orderAnalytics['selectedRange'] }}"
        data-order-chart-points="{{ count($orderAnalytics['chart']['labels']) }}"
    >
        <div class="card-header border-0">
            <div class="dashboard-analytics-intro">
                <div>
                    <h3 class="card-title dashboard-analytics-title">@lang('site.sales_graph')</h3>
                    <p class="dashboard-analytics-subtitle">@lang('site.orders_analytics')</p>
                </div>

                <div class="dashboard-analytics-actions">
                    <div class="dashboard-range-switch" role="group" aria-label="@lang('site.sales_graph')">
                        @foreach ($orderAnalytics['rangeOptions'] as $option)
                            <a
                                href="{{ route('dashboard.index', ['range' => $option['key']]) }}"
                                class="btn btn-sm {{ $option['key'] === $orderAnalytics['selectedRange'] ? 'active' : '' }}"
                                @if ($option['key'] === $orderAnalytics['selectedRange']) aria-current="page" @endif
                            >
                                {{ $option['label'] }}
                            </a>
                        @endforeach
                    </div>

                    <div class="dashboard-analytics-period">
                        <i class="far fa-calendar-alt" aria-hidden="true"></i>
                        <span>{{ $orderAnalytics['periodLabel'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-2">
            <div class="row dashboard-analytics-stats">
                @foreach ($orderStats as $stat)
                    <div class="col-lg-3 col-sm-6">
                        <div
                            class="dashboard-analytics-stat dashboard-analytics-stat--{{ $stat['accent'] }}"
                            data-order-stat="{{ $stat['key'] }}:{{ number_format((float) $stat['value'], $stat['decimals'], '.', '') }}"
                        >
                            <div class="dashboard-analytics-stat__inner">
                                <span class="dashboard-analytics-stat__icon">
                                    <i class="{{ $stat['icon'] }}" aria-hidden="true"></i>
                                </span>

                                <div>
                                    <div class="dashboard-analytics-stat__value">
                                        {{ number_format((float) $stat['value'], $stat['decimals']) }}
                                    </div>
                                    <div class="dashboard-analytics-stat__label">{{ $stat['label'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="dashboard-chart-shell">
                @if ($orderAnalytics['hasData'])
                    <div class="dashboard-chart-legends">
                        <div class="dashboard-chart-legend-group">
                            <span class="dashboard-chart-legend">
                                <span class="dashboard-chart-legend-swatch dashboard-chart-legend-swatch--sales"></span>
                                <span>@lang('site.sales')</span>
                            </span>
                            <span class="dashboard-chart-legend">
                                <span class="dashboard-chart-legend-swatch dashboard-chart-legend-swatch--orders"></span>
                                <span>@lang('site.orders')</span>
                            </span>
                        </div>

                        <span class="dashboard-chart-range-label">{{ $orderAnalytics['selectedRangeLabel'] }}</span>
                    </div>

                    <div id="orders-sales-chart" class="dashboard-chart-canvas" aria-label="@lang('site.sales_graph')"></div>
                @else
                    <div class="dashboard-chart-empty">
                        <i class="far fa-chart-bar" aria-hidden="true"></i>
                        <p class="mb-0">@lang('site.no_orders_for_selected_period')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('adminlte/plugins/flot/jquery.flot.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/flot/plugins/jquery.flot.resize.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/flot/plugins/jquery.flot.hover.js') }}"></script>
    <script>
        (function () {
            const chartElement = document.getElementById('orders-sales-chart');

            if (!chartElement || typeof $.plot !== 'function') {
                return;
            }

            const chartPayload = @json($orderAnalytics['chart']);
            const labelFormatter = new Intl.NumberFormat(document.documentElement.lang || 'en');
            const salesFormatter = new Intl.NumberFormat(document.documentElement.lang || 'en', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
            const salesLabel = @json(__('site.sales'));
            const ordersLabel = @json(__('site.orders'));
            const isRtl = document.documentElement.dir === 'rtl';
            const tooltip = document.createElement('div');

            tooltip.className = 'dashboard-chart-tooltip';
            document.body.appendChild(tooltip);

            $.plot($(chartElement), [
                {
                    data: chartPayload.orders,
                    color: '#f39c12',
                    bars: {
                        show: true,
                        barWidth: 0.34,
                        align: 'center',
                        fill: 0.28,
                        lineWidth: 1,
                    },
                    yaxis: 2,
                },
                {
                    data: chartPayload.sales,
                    color: '#1f6aa5',
                    lines: {
                        show: true,
                        lineWidth: 3,
                        fill: 0.08,
                    },
                    points: {
                        show: true,
                        radius: 4,
                        lineWidth: 2,
                        fillColor: '#ffffff',
                    },
                }
            ], {
                series: {
                    shadowSize: 0,
                },
                grid: {
                    borderWidth: 1,
                    borderColor: '#e7edf5',
                    tickColor: '#eef3f8',
                    hoverable: true,
                    clickable: true,
                    labelMargin: 14,
                    backgroundColor: '#ffffff',
                },
                xaxis: {
                    ticks: chartPayload.ticks,
                    tickLength: 0,
                    color: '#d5e1ec',
                    min: -0.25,
                    max: Math.max(chartPayload.labels.length - 0.75, 0.25),
                    font: {
                        color: '#6c7b8a',
                        size: 12,
                    },
                },
                yaxes: [
                    {
                        position: isRtl ? 'right' : 'left',
                        min: 0,
                        color: '#e7edf5',
                        font: {
                            color: '#6c7b8a',
                            size: 12,
                        },
                        tickFormatter: function (value) {
                            return labelFormatter.format(Math.round(value));
                        },
                    },
                    {
                        position: isRtl ? 'left' : 'right',
                        min: 0,
                        color: '#e7edf5',
                        font: {
                            color: '#6c7b8a',
                            size: 12,
                        },
                        tickFormatter: function (value) {
                            return labelFormatter.format(Math.round(value));
                        },
                    }
                ],
                legend: {
                    show: false,
                }
            });

            $(chartElement).bind('plothover', function (event, position, item) {
                if (!item) {
                    tooltip.classList.remove('is-visible');
                    return;
                }

                const pointIndex = item.dataIndex;
                const label = chartPayload.labels[pointIndex];
                const salesValue = chartPayload.sales[pointIndex]?.[1] ?? 0;
                const ordersValue = chartPayload.orders[pointIndex]?.[1] ?? 0;

                tooltip.innerHTML =
                    '<span class="dashboard-chart-tooltip__label">' + label + '</span>' +
                    '<div>' + salesLabel + ': ' + salesFormatter.format(salesValue) + '</div>' +
                    '<div>' + ordersLabel + ': ' + labelFormatter.format(ordersValue) + '</div>';
                tooltip.style.left = (item.pageX + 16) + 'px';
                tooltip.style.top = (item.pageY - 20) + 'px';
                tooltip.classList.add('is-visible');
            });

            $(chartElement).on('mouseleave', function () {
                tooltip.classList.remove('is-visible');
            });
        })();
    </script>
@endpush
