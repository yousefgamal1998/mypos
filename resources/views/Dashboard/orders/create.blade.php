@extends('layouts.app')

@php
    $isEditing = $isEditing ?? false;
    $pageTitle = $isEditing ? __('site.edit_order') : __('site.add_order');
    $formAction = $isEditing ? route('dashboard.orders.update', $order->id) : route('dashboard.orders.store');
    $submitLabel = $isEditing ? __('site.edit_order') : __('site.add_order');
@endphp

@section('page-title', $pageTitle)

@section('breadcrumb')
    @if ($isEditing)
        <li class="breadcrumb-item"><a href="{{ route('dashboard.orders.index') }}">@lang('site.orders')</a></li>
        <li class="breadcrumb-item active">@lang('site.edit')</li>
    @elseif (isset($customer) && $customer)
        <li class="breadcrumb-item"><a href="{{ route('dashboard.customers.index') }}">@lang('site.customers')</a></li>
        <li class="breadcrumb-item active">@lang('site.add_order')</li>
    @else
        <li class="breadcrumb-item"><a href="{{ route('dashboard.orders.index') }}">@lang('site.orders')</a></li>
        <li class="breadcrumb-item active">@lang('site.add_order')</li>
    @endif
@endsection

@push('styles')
    <style>
        .order-workspace-card{border:1px solid #dce6f2;border-radius:1rem;box-shadow:0 20px 45px rgba(15,23,42,.08);overflow:hidden}
        .order-workspace-card .card-header{background:linear-gradient(135deg,#fff 0%,#f5faff 100%);border-bottom:1px solid #e4edf7;padding:1.1rem 1.35rem}
        .order-workspace-card .card-body{padding:1.35rem}
        .order-workspace-title{font-size:1.1rem;font-weight:700;margin-bottom:0;color:#12324a}
        .order-workspace-badge{display:inline-flex;align-items:center;justify-content:center;min-width:2.4rem;padding:.35rem .75rem;border-radius:999px;background:#e8f1ff;color:#1d6fd8;font-weight:700;font-size:.9rem}
        .order-overview-alert{border-radius:.9rem;border:1px solid #b7ddff;background:linear-gradient(135deg,#eff8ff 0%,#f8fbff 100%);color:#0f4b75;padding:.95rem 1rem}
        .order-overview-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}
        .order-overview-stat{border:1px solid #dce6f2;border-radius:.9rem;background:#f8fbff;padding:.95rem 1rem}
        .order-overview-stat-label{display:block;color:#5a6b7f;font-size:.88rem;margin-bottom:.35rem}
        .order-overview-stat-value{color:#17324d;font-size:1.45rem;font-weight:700;line-height:1}
        .order-summary-table th,.order-summary-table td{vertical-align:middle}.order-summary-table thead th{text-align:center}.order-summary-table thead th:first-child{text-align:start}.order-summary-product{font-weight:700;color:#17324d}.order-summary-qty{width:84px;text-align:center}.order-summary-empty-cell{padding:1.6rem .75rem}.order-summary-remove{width:2.65rem;height:2.65rem;border:0;border-radius:.45rem;background:linear-gradient(135deg,#e24b39 0%,#f0654a 100%);color:#fff;display:inline-flex;align-items:center;justify-content:center;padding:0;font-size:1rem;line-height:1;box-shadow:0 12px 22px rgba(226,75,57,.26);flex-shrink:0;transition:transform .18s ease,box-shadow .18s ease,opacity .18s ease}.order-summary-remove:hover,.order-summary-remove:focus{color:#fff;transform:translateY(-1px);box-shadow:0 16px 28px rgba(226,75,57,.34);outline:none}.order-summary-footer{border-top:1px solid #e4edf7;margin-top:1rem;padding-top:1rem}.order-summary-total{font-size:1.2rem;font-weight:700;color:#17324d}.order-summary-submit{padding:.8rem 1rem;font-weight:700}
        .order-category-list{display:flex;flex-direction:column;gap:.85rem;max-height:34rem;overflow-y:auto;padding-right:.25rem}
        .order-category-button{width:100%;border:1px solid #d7e3f1;border-radius:1rem;background:#fff;color:#17324d;display:flex;align-items:center;justify-content:space-between;text-align:start;padding:1rem 1.1rem;transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease,background .18s ease;box-shadow:0 12px 24px rgba(15,23,42,.05)}
        .order-category-button:hover,.order-category-button:focus{border-color:#75aef6;box-shadow:0 16px 28px rgba(29,111,216,.14);transform:translateY(-2px);outline:none}
        .order-category-button.active{background:linear-gradient(135deg,#1d6fd8 0%,#4f9bff 100%);border-color:#1d6fd8;color:#fff;box-shadow:0 18px 34px rgba(29,111,216,.26)}
        .order-category-name{display:block;font-weight:700;font-size:1rem;margin-bottom:.15rem}.order-category-caption{display:block;font-size:.82rem;opacity:.74}
        .order-category-count{display:inline-flex;align-items:center;justify-content:center;min-width:2.6rem;height:2.2rem;border-radius:999px;background:rgba(29,111,216,.1);color:inherit;font-weight:700;font-size:.92rem;margin-left:.9rem}
        .order-category-button.active .order-category-count{background:rgba(255,255,255,.18)}
        .order-products-grid{max-height:34rem;overflow-y:auto;padding-right:.25rem}
        .order-product-card{height:100%;border:1px solid #d9e3ee;border-radius:1rem;overflow:hidden;background:linear-gradient(180deg,#fff 0%,#f8fbff 100%);box-shadow:0 14px 28px rgba(15,23,42,.06);transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
        .order-product-card:hover{transform:translateY(-3px);border-color:#86b6f4;box-shadow:0 18px 30px rgba(29,111,216,.16)}
        .order-product-image-wrap{background:linear-gradient(135deg,#edf5ff 0%,#f8fbff 100%);padding:.9rem}.order-product-image{width:100%;height:150px;object-fit:cover;border-radius:.85rem;border:1px solid #d9e3ee;background:#fff;display:block}
        .order-product-body{padding:1rem}.order-product-title{font-size:1rem;font-weight:700;color:#17324d;margin-bottom:.55rem}.order-product-description{color:#5f7084;font-size:.88rem;line-height:1.55;min-height:2.7rem;margin-bottom:.85rem}
        .order-product-meta{display:flex;flex-wrap:wrap;gap:.5rem}.order-product-meta-item{display:inline-flex;align-items:center;padding:.4rem .7rem;border-radius:999px;background:#eef5ff;color:#1d4b77;font-size:.83rem;font-weight:600}
        .order-product-actions{display:flex;align-items:center;justify-content:space-between;margin-top:1rem}.order-product-selected-count{display:inline-flex;align-items:center;justify-content:center;min-width:2rem;height:2rem;border-radius:999px;background:#e6f4ea;color:#188148;font-weight:700;font-size:.88rem}
        .order-product-add-button{width:2.75rem;height:2.75rem;border:0;border-radius:.75rem;background:linear-gradient(135deg,#17a84b 0%,#2ecc71 100%);color:#fff;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 14px 24px rgba(35,153,86,.26);transition:transform .18s ease,box-shadow .18s ease,opacity .18s ease}
        .order-product-add-button:hover,.order-product-add-button:focus{transform:translateY(-1px);box-shadow:0 18px 28px rgba(35,153,86,.34);outline:none}.order-product-add-button:disabled{opacity:.45;box-shadow:none;cursor:not-allowed}
        .order-product-out-of-stock{color:#c05621;font-size:.82rem;font-weight:700}.order-empty-state{border:1px dashed #cdd8e5;border-radius:1rem;background:#f8fbff;color:#607286;padding:1rem;text-align:center}.order-submit-button:disabled{opacity:.65;cursor:not-allowed}
        .customer-history-layout{display:grid;grid-template-columns:minmax(0,.95fr) minmax(0,1.35fr);gap:1rem}
        .customer-history-panel{height:100%;border:1px solid #dce6f2;border-radius:1rem;background:linear-gradient(180deg,#fff 0%,#f8fbff 100%);padding:1.1rem}
        .customer-history-identity{display:flex;align-items:center;gap:.95rem;margin-bottom:1rem}
        .customer-history-avatar{width:3.5rem;height:3.5rem;border-radius:1rem;background:linear-gradient(135deg,#1d6fd8 0%,#4f9bff 100%);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;box-shadow:0 16px 28px rgba(29,111,216,.22)}
        .customer-history-eyebrow{display:inline-flex;align-items:center;padding:.35rem .7rem;border-radius:999px;background:#eef5ff;color:#1d4b77;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.45rem}
        .customer-history-name{font-size:1.25rem;font-weight:700;color:#17324d;line-height:1.2;margin:0}
        .customer-history-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem}
        .customer-history-detail{border:1px solid #e1eaf5;border-radius:.9rem;background:#fff;padding:.9rem .95rem}
        .customer-history-detail-full{grid-column:1 / -1}
        .customer-history-detail-label{display:block;color:#607286;font-size:.82rem;margin-bottom:.3rem}
        .customer-history-detail-value{color:#17324d;font-weight:700;word-break:break-word}
        .customer-history-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.8rem;margin-bottom:1rem}
        .customer-history-stat{border:1px solid #dde7f3;border-radius:.95rem;background:#fff;padding:.95rem 1rem}
        .customer-history-stat-label{display:block;color:#607286;font-size:.82rem;margin-bottom:.35rem}
        .customer-history-stat-value{display:block;color:#17324d;font-size:1.18rem;font-weight:700;line-height:1.3}
        .customer-history-subtitle{font-size:1rem;font-weight:700;color:#17324d;margin-bottom:0}
        .customer-history-table th,.customer-history-table td{vertical-align:middle}
        .customer-history-table thead th{border-top:0;color:#607286;font-size:.82rem;font-weight:700}
        .customer-history-table tbody td{color:#17324d}
        .customer-history-empty{border:1px dashed #d4deea;border-radius:1rem;background:#fbfdff;color:#607286;padding:1rem;text-align:center}
        @media (max-width:991.98px){.order-overview-stats{margin-top:1rem}.order-category-list,.order-products-grid{max-height:none}}
        @media (max-width:991.98px){.customer-history-layout{grid-template-columns:1fr}.customer-history-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media (max-width:575.98px){.order-overview-stats{grid-template-columns:1fr}.customer-history-details,.customer-history-stats{grid-template-columns:1fr}}
    </style>
@endpush

@section('content')
    <form action="{{ $formAction }}" method="POST" novalidate>
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card card-primary order-workspace-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h3 class="order-workspace-title mb-2 mb-md-0">{{ $pageTitle }}</h3>
                            <span class="order-workspace-badge">{{ $products->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('partials._errors')
                        @if (! $isEditing && isset($customer) && $customer)
                            <div class="order-overview-alert mb-4">{{ __('site.creating_order_for', ['name' => $customer->name]) }}</div>
                        @endif
                        <div class="row align-items-end">
                            <div class="col-lg-7 mb-3 mb-lg-0">
                                @include('Dashboard.orders._form')
                            </div>
                            <div class="col-lg-5">
                                <div class="order-overview-stats">
                                    <div class="order-overview-stat"><span class="order-overview-stat-label">@lang('site.categories')</span><strong class="order-overview-stat-value">{{ $categories->count() }}</strong></div>
                                    <div class="order-overview-stat"><span class="order-overview-stat-label">@lang('site.products')</span><strong class="order-overview-stat-value" data-visible-products-count>{{ $products->count() }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if (! $isEditing && isset($customer) && $customer)
                <div class="col-12 mb-4">
                    <div class="card card-primary order-workspace-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <h3 class="order-workspace-title mb-2 mb-md-0">@lang('site.customer_history')</h3>
                                <span class="order-workspace-badge">{{ $customerOrderStats['orders_count'] }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="customer-history-layout">
                                <div class="customer-history-panel">
                                    <div class="customer-history-identity">
                                        <div class="customer-history-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($customer->name, 0, 1)) }}</div>
                                        <div>
                                            <span class="customer-history-eyebrow">@lang('site.customer')</span>
                                            <h4 class="customer-history-name">{{ $customer->name }}</h4>
                                        </div>
                                    </div>
                                    <div class="customer-history-details">
                                        <div class="customer-history-detail">
                                            <span class="customer-history-detail-label">@lang('site.phone')</span>
                                            <div class="customer-history-detail-value">{{ $customer->phone ?: __('site.no_data_found') }}</div>
                                        </div>
                                        <div class="customer-history-detail">
                                            <span class="customer-history-detail-label">@lang('site.last_order')</span>
                                            <div class="customer-history-detail-value">
                                                {{ $customerOrderStats['last_order_at'] ? \Illuminate\Support\Carbon::parse($customerOrderStats['last_order_at'])->translatedFormat('j M, Y') : __('site.no_data_found') }}
                                            </div>
                                        </div>
                                        <div class="customer-history-detail customer-history-detail-full">
                                            <span class="customer-history-detail-label">@lang('site.address')</span>
                                            <div class="customer-history-detail-value">{{ $customer->address ?: __('site.no_data_found') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="customer-history-panel">
                                    <div class="customer-history-stats">
                                        <div class="customer-history-stat">
                                            <span class="customer-history-stat-label">@lang('site.orders')</span>
                                            <strong class="customer-history-stat-value">{{ $customerOrderStats['orders_count'] }}</strong>
                                        </div>
                                        <div class="customer-history-stat">
                                            <span class="customer-history-stat-label">@lang('site.total_spent')</span>
                                            <strong class="customer-history-stat-value" data-number-display>{{ $customerOrderStats['total_spent'] }}</strong>
                                        </div>
                                        <div class="customer-history-stat">
                                            <span class="customer-history-stat-label">@lang('site.items_count')</span>
                                            <strong class="customer-history-stat-value">{{ $customerOrderHistory->sum('items_count') }}</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                        <h4 class="customer-history-subtitle mb-2 mb-md-0">@lang('site.recent_orders')</h4>
                                        <span class="customer-history-eyebrow mb-0">@lang('site.order_details')</span>
                                    </div>
                                    @if ($customerOrderHistory->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-hover customer-history-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>@lang('site.added_at')</th>
                                                        <th>@lang('site.items_count')</th>
                                                        <th>@lang('site.total')</th>
                                                        <th>@lang('site.action')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($customerOrderHistory as $historyOrder)
                                                        <tr>
                                                            <td>#{{ $historyOrder->id }}</td>
                                                            <td>{{ optional($historyOrder->created_at)->translatedFormat('j F, Y') }}</td>
                                                            <td>{{ $historyOrder->items_count }}</td>
                                                            <td><span data-number-display>{{ (float) $historyOrder->total_amount }}</span></td>
                                                            <td>
                                                                <a href="{{ route('dashboard.orders.show', $historyOrder->id) }}" class="btn btn-outline-primary btn-sm">
                                                                    @lang('site.show')
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="customer-history-empty">@lang('site.no_order_history')</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-12 mb-4">
                <div class="card card-primary order-workspace-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h3 class="order-workspace-title mb-2 mb-md-0">@lang('site.orders')</h3>
                            <span class="order-workspace-badge" data-selected-count>0</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover order-summary-table mb-0">
                                <thead>
                                    <tr>
                                        <th>@lang('site.products')</th>
                                        <th class="text-center">@lang('site.quantity')</th>
                                        <th class="text-center">@lang('site.unit_price')</th>
                                    </tr>
                                </thead>
                                <tbody data-order-items-body>
                                    <tr data-order-items-empty>
                                        <td colspan="3" class="text-center text-muted order-summary-empty-cell">@lang('site.empty_order_selection')</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="order-summary-footer">
                            <div class="d-flex justify-content-end mb-3">
                                <div class="order-summary-total">@lang('site.total'): <span data-order-total data-number-display>0</span></div>
                            </div>
                            <div data-order-hidden-inputs></div>
                            <button type="submit" class="btn btn-info btn-block order-summary-submit order-submit-button" data-order-submit disabled>
                                <i class="fa {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-1"></i> {{ $submitLabel }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card card-primary order-workspace-card h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h3 class="order-workspace-title mb-2 mb-md-0">@lang('site.categories')</h3>
                            <span class="order-workspace-badge">{{ $categories->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($categories->isNotEmpty())
                            <div class="order-category-list">
                                <button type="button" class="order-category-button active" data-category-filter="all">
                                    <span>
                                        <span class="order-category-name">@lang('site.all_categories')</span>
                                        <span class="order-category-caption">@lang('site.categories')</span>
                                    </span>
                                    <span class="order-category-count">{{ $products->count() }}</span>
                                </button>
                                @foreach ($categories as $category)
                                    <button type="button" class="order-category-button" data-category-filter="{{ $category->id }}">
                                        <span>
                                            <span class="order-category-name">{{ $category->name }}</span>
                                            <span class="order-category-caption">@lang('site.products')</span>
                                        </span>
                                        <span class="order-category-count">{{ $category->products_count }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="order-empty-state">@lang('site.no_data_found')</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card card-primary order-workspace-card h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h3 class="order-workspace-title mb-2 mb-md-0">@lang('site.products')</h3>
                            <span class="order-workspace-badge" data-products-badge>{{ $products->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($products->isNotEmpty())
                            <div class="row order-products-grid">
                                @foreach ($products as $product)
                                    <div class="col-md-6 mb-3" data-product-card data-category-id="{{ $product->category_id }}">
                                        <article class="order-product-card">
                                            <div class="order-product-image-wrap">
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="order-product-image">
                                            </div>
                                            <div class="order-product-body">
                                                <h4 class="order-product-title">{{ $product->name }}</h4>
                                                <p class="order-product-description">{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 90) ?: __('site.no_data_found') }}</p>
                                                <div class="order-product-meta">
                                                    <span class="order-product-meta-item">@lang('site.selling_price'): <span data-number-display>{{ (float) $product->selling_price }}</span></span>
                                                    <span class="order-product-meta-item">@lang('site.warehouse'): {{ $product->stock }}</span>
                                                </div>
                                                <div class="order-product-actions">
                                                    <div>
                                                        <span class="order-product-selected-count d-none" data-product-selected-count="{{ $product->id }}">0</span>
                                                        @if ((int) $product->stock < 1)
                                                            <div class="order-product-out-of-stock mt-2">@lang('site.out_of_stock')</div>
                                                        @endif
                                                    </div>
                                                    <button type="button" class="order-product-add-button" data-add-product-button data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-product-price="{{ $product->selling_price }}" data-product-stock="{{ $product->stock }}" title="@lang('site.add_to_order')" @disabled((int) $product->stock < 1)>
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                @endforeach
                            </div>
                            <div class="order-empty-state d-none" data-empty-products>@lang('site.no_data_found')</div>
                        @else
                            <div class="order-empty-state">@lang('site.no_data_found')</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('adminlte/plugins/jquery-number/jquery.number.js') }}"></script>
    <script>
        (function () {
            var categoryButtons = Array.prototype.slice.call(document.querySelectorAll('[data-category-filter]'));
            var productCards = Array.prototype.slice.call(document.querySelectorAll('[data-product-card]'));
            var addButtons = Array.prototype.slice.call(document.querySelectorAll('[data-add-product-button]'));
            var emptyProductsState = document.querySelector('[data-empty-products]');
            var productsBadge = document.querySelector('[data-products-badge]');
            var productsCount = document.querySelector('[data-visible-products-count]');
            var itemsBody = document.querySelector('[data-order-items-body]');
            var hiddenInputs = document.querySelector('[data-order-hidden-inputs]');
            var totalNode = document.querySelector('[data-order-total]');
            var selectedCountNode = document.querySelector('[data-selected-count]');
            var submitButton = document.querySelector('[data-order-submit]');
            var initialItems = @json(old('items', $initialItems));
            var selectedItems = {};
            var productRegistry = {};

            function escapeHtml(value) {
                return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function applyNumberFormatting(scope) {
                if (!window.jQuery || typeof window.jQuery.fn.number !== 'function') {
                    return;
                }

                window.jQuery(scope || document).find('[data-number-display]').number(true, 2, '.', ',');
            }

            function updateProductButtons() {
                addButtons.forEach(function (button) {
                    var productId = button.getAttribute('data-product-id');
                    var product = productRegistry[productId];
                    var quantity = selectedItems[productId] ? selectedItems[productId].quantity : 0;
                    var selectedBadge = document.querySelector('[data-product-selected-count="' + productId + '"]');

                    if (selectedBadge) {
                        if (quantity > 0) {
                            selectedBadge.textContent = quantity;
                            selectedBadge.classList.remove('d-none');
                        } else {
                            selectedBadge.textContent = '0';
                            selectedBadge.classList.add('d-none');
                        }
                    }

                    button.disabled = !product || product.stock < 1 || quantity > 0;
                });
            }

            function renderSelectedItems() {
                var productIds = Object.keys(selectedItems);
                var grandTotal = 0;

                if (!itemsBody) {
                    return;
                }

                if (productIds.length === 0) {
                    itemsBody.innerHTML = '<tr data-order-items-empty><td colspan="3" class="text-center text-muted order-summary-empty-cell">' + @json(__('site.empty_order_selection')) + '</td></tr>';
                } else {
                    itemsBody.innerHTML = productIds.map(function (productId) {
                        var item = selectedItems[productId];
                        grandTotal += item.quantity * item.price;

                        return '' +
                            '<tr>' +
                                '<td><div class="d-flex justify-content-between align-items-center"><span class="order-summary-product">' + escapeHtml(item.name) + '</span><button type="button" class="order-summary-remove" data-item-action="remove" data-product-id="' + item.id + '" title=' + @json(__('site.delete')) + '><i class="fa fa-trash"></i></button></div></td>' +
                                '<td><div class="input-group input-group-sm justify-content-center"><div class="input-group-prepend"><button type="button" class="btn btn-outline-secondary" data-item-action="decrease" data-product-id="' + item.id + '">-</button></div><input type="number" min="1" max="' + item.stock + '" value="' + item.quantity + '" class="form-control order-summary-qty" data-quantity-input data-product-id="' + item.id + '"><div class="input-group-append"><button type="button" class="btn btn-outline-secondary" data-item-action="increase" data-product-id="' + item.id + '">+</button></div></div></td>' +
                                '<td class="text-center"><span data-number-display>' + (item.quantity * item.price) + '</span></td>' +
                            '</tr>';
                    }).join('');
                }

                if (hiddenInputs) {
                    hiddenInputs.innerHTML = productIds.map(function (productId, index) {
                        var item = selectedItems[productId];
                        return '<input type="hidden" name="items[' + index + '][product_id]" value="' + item.id + '"><input type="hidden" name="items[' + index + '][quantity]" value="' + item.quantity + '">';
                    }).join('');
                }

                applyNumberFormatting(itemsBody);

                if (totalNode) {
                    totalNode.textContent = grandTotal;
                    if (window.jQuery && typeof window.jQuery.fn.number === 'function') {
                        window.jQuery(totalNode).number(true, 2, '.', ',');
                    }
                }

                if (selectedCountNode) {
                    selectedCountNode.textContent = productIds.length;
                }

                if (submitButton) {
                    submitButton.disabled = productIds.length === 0;
                }

                updateProductButtons();
            }

            function setItemQuantity(productId, nextQuantity) {
                var product = productRegistry[productId];
                if (!product || product.stock < 1) {
                    return;
                }

                if (nextQuantity <= 0) {
                    delete selectedItems[productId];
                    renderSelectedItems();
                    return;
                }

                selectedItems[productId] = {id: product.id, name: product.name, price: product.price, stock: product.stock, quantity: Math.min(nextQuantity, product.stock)};
                renderSelectedItems();
            }

            function addProduct(productId) {
                var currentQuantity = selectedItems[productId] ? selectedItems[productId].quantity : 0;
                setItemQuantity(productId, currentQuantity + 1);
            }

            function applyCategoryFilter(categoryId) {
                var visibleCount = 0;
                categoryButtons.forEach(function (button) {
                    button.classList.toggle('active', button.getAttribute('data-category-filter') === categoryId);
                });
                productCards.forEach(function (card) {
                    var matches = categoryId === 'all' || card.getAttribute('data-category-id') === categoryId;
                    card.classList.toggle('d-none', !matches);
                    if (matches) {
                        visibleCount += 1;
                    }
                });
                if (emptyProductsState) {
                    emptyProductsState.classList.toggle('d-none', visibleCount !== 0);
                }
                if (productsBadge) {
                    productsBadge.textContent = visibleCount;
                }
                if (productsCount) {
                    productsCount.textContent = visibleCount;
                }
            }

            addButtons.forEach(function (button) {
                var productId = button.getAttribute('data-product-id');
                productRegistry[productId] = {
                    id: parseInt(productId, 10),
                    name: button.getAttribute('data-product-name') || '',
                    price: parseFloat(button.getAttribute('data-product-price') || '0'),
                    stock: parseInt(button.getAttribute('data-product-stock') || '0', 10),
                };
                button.addEventListener('click', function () {
                    addProduct(productId);
                });
            });

            if (itemsBody) {
                itemsBody.addEventListener('click', function (event) {
                    var actionButton = event.target.closest('[data-item-action]');
                    if (!actionButton) {
                        return;
                    }
                    var productId = actionButton.getAttribute('data-product-id');
                    var action = actionButton.getAttribute('data-item-action');
                    var currentQuantity = selectedItems[productId] ? selectedItems[productId].quantity : 0;

                    if (action === 'increase') {
                        setItemQuantity(productId, currentQuantity + 1);
                    }
                    if (action === 'decrease') {
                        setItemQuantity(productId, currentQuantity - 1);
                    }
                    if (action === 'remove') {
                        delete selectedItems[productId];
                        renderSelectedItems();
                    }
                });

                itemsBody.addEventListener('input', function (event) {
                    var quantityInput = event.target.closest('[data-quantity-input]');
                    if (!quantityInput) {
                        return;
                    }
                    var productId = quantityInput.getAttribute('data-product-id');
                    var nextQuantity = parseInt(quantityInput.value || '0', 10);
                    if (!Number.isNaN(nextQuantity)) {
                        setItemQuantity(productId, nextQuantity);
                    }
                });
            }

            categoryButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    applyCategoryFilter(button.getAttribute('data-category-filter'));
                });
            });

            if (Array.isArray(initialItems)) {
                initialItems.forEach(function (item) {
                    var productId = String(item.product_id || '');
                    var quantity = parseInt(item.quantity || '0', 10);
                    if (productRegistry[productId] && !Number.isNaN(quantity) && quantity > 0) {
                        productRegistry[productId].stock = Math.max(productRegistry[productId].stock, quantity);
                        setItemQuantity(productId, quantity);
                    }
                });
            }

            applyCategoryFilter('all');
            applyNumberFormatting(document);
            renderSelectedItems();
        })();
    </script>
@endpush
