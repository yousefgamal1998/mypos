<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

beforeEach(function () {
    $this->withoutMiddleware([
        LocaleSessionRedirect::class,
        LaravelLocalizationRedirectFilter::class,
        LaravelLocalizationViewPath::class,
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function createDashboardOrder(Customer $customer, Product $product, Carbon $createdAt, int $quantity, float $unitPrice): void
{
    $order = Order::create([
        'customer_id' => $customer->id,
    ]);

    $order->created_at = $createdAt;
    $order->updated_at = $createdAt;
    $order->save();

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
    ]);
}

it('shows summary counts on the dashboard homepage', function () {
    $user = User::create([
        'first_name' => 'Dashboard',
        'last_name' => 'Viewer',
        'email' => 'dashboard.viewer@example.com',
        'password' => 'password',
    ]);

    User::create([
        'first_name' => 'Protected',
        'last_name' => 'Admin',
        'email' => 'admin@admin.com',
        'password' => 'password',
    ]);

    Customer::create([
        'name' => 'Acme Customer',
        'phone' => '01000000000',
        'alternate_phone' => null,
        'address' => 'Cairo',
    ]);

    $category = new Category();
    $category->save();

    Product::create([
        'category_id' => $category->id,
        'purchase_price' => 10,
        'selling_price' => 15,
        'stock' => 8,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard.index'));

    $response->assertOk();
    $response->assertSee('data-summary-metric="users:1"', false);
    $response->assertSee('data-summary-metric="customers:1"', false);
    $response->assertSee('data-summary-metric="products:1"', false);
    $response->assertSee('data-summary-metric="categories:1"', false);
});

it('shows order analytics totals for the selected range', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-02 12:00:00'));

    $user = User::create([
        'first_name' => 'Analytics',
        'last_name' => 'Viewer',
        'email' => 'analytics.viewer@example.com',
        'password' => 'password',
    ]);

    $customer = Customer::create([
        'name' => 'Analytics Customer',
        'phone' => '01000000001',
        'alternate_phone' => null,
        'address' => 'Giza',
    ]);

    $category = new Category();
    $category->save();

    $product = Product::create([
        'category_id' => $category->id,
        'purchase_price' => 15,
        'selling_price' => 25,
        'stock' => 30,
    ]);

    createDashboardOrder($customer, $product, Carbon::now()->subDays(10), 2, 30);
    createDashboardOrder($customer, $product, Carbon::now()->subDays(2), 1, 40);
    createDashboardOrder($customer, $product, Carbon::now()->subDays(55), 5, 20);

    $response = $this->actingAs($user)->get(route('dashboard.index', ['range' => '30_days']));

    $response->assertOk();
    $response->assertSee('data-order-range="30_days"', false);
    $response->assertSee('data-order-chart-points="30"', false);
    $response->assertSee('data-order-stat="sales:100.00"', false);
    $response->assertSee('data-order-stat="orders:2"', false);
    $response->assertSee('data-order-stat="average:50.00"', false);
    $response->assertSee('data-order-stat="items:3"', false);
    $response->assertSee('id="orders-sales-chart"', false);
});
