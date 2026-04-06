<?php

namespace App\Http\Controllers\Dashboard\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $orders = Order::query()
            ->select('orders.*')
            ->selectSub(
                DB::table('order_items')
                    ->selectRaw('COALESCE(SUM(quantity * unit_price), 0)')
                    ->whereColumn('order_id', 'orders.id'),
                'total_amount'
            )
            ->with('customer')
            ->when($search !== '', function ($query) use ($search) {
                $query->search($search);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('Dashboard.orders.index', compact('orders', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Dashboard.orders.create', $this->orderFormViewData(new Order()));
    }

    public function createForCustomer(Customer $customer)
    {
        return view('Dashboard.orders.create', $this->orderFormViewData(new Order(), $customer));
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->orderRules(), [], $this->validationAttributes());
        $products = $this->validatedProducts($validated['items']);

        $this->ensureStockAvailability($validated['items'], $products);

        DB::transaction(function () use ($validated, $products): void {
            $order = Order::create([
                'customer_id' => $validated['customer_id'],
            ]);

            $this->syncOrderItems($order, $validated['items'], $products);
        });

        session()->flash('success', __('site.added_successfully'));

        return redirect()->route('dashboard.orders.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load([
            'customer',
            'items' => function ($query) {
                $query->with([
                    'product' => function ($productQuery) {
                        $productQuery->withLocaleTranslations();
                    },
                ])->orderBy('id');
            },
        ]);

        return view('Dashboard.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $order->load('items');

        return view('Dashboard.orders.create', $this->orderFormViewData($order, null, true));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate($this->orderRules(), [], $this->validationAttributes());
        $products = $this->validatedProducts($validated['items']);

        $this->ensureStockAvailability($validated['items'], $products);

        DB::transaction(function () use ($order, $validated, $products): void {
            $order->update([
                'customer_id' => $validated['customer_id'],
            ]);

            $this->syncOrderItems($order, $validated['items'], $products);
        });

        session()->flash('success', __('site.updated_successfully'));

        return redirect()->route('dashboard.orders.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        session()->flash('success', __('site.deleted_successfully'));

        return redirect()->route('dashboard.orders.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function orderFormViewData(Order $order, ?Customer $customer = null, bool $isEditing = false): array
    {
        $categories = Category::query()
            ->withLocaleTranslations()
            ->withCount('products')
            ->has('products')
            ->get()
            ->sortBy('name')
            ->values();

        $products = Product::query()
            ->withLocaleTranslations()
            ->get()
            ->sortBy('name')
            ->values();

        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($order->exists) {
            $order->loadMissing('items');
        } else {
            $order->setRelation('items', collect());
        }

        return compact('categories', 'products', 'customers', 'customer', 'order', 'isEditing') + [
            'initialItems' => $this->initialItems($order),
            'customerOrderHistory' => $this->customerOrderHistory($customer),
            'customerOrderStats' => $this->customerOrderStats($customer),
        ];
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
    private function orderRules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    private function validatedProducts(array $items)
    {
        $productIds = collect($items)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'selling_price', 'stock'])
            ->keyBy('id');
    }

    private function ensureStockAvailability(array $items, $products): void
    {
        $stockErrors = [];

        foreach ($items as $index => $item) {
            $product = $products->get((int) $item['product_id']);

            if ($product !== null && (int) $item['quantity'] > (int) $product->stock) {
                $stockErrors["items.{$index}.quantity"] = __('site.insufficient_stock');
            }
        }

        if ($stockErrors !== []) {
            throw ValidationException::withMessages($stockErrors);
        }
    }

    private function syncOrderItems(Order $order, array $items, $products): void
    {
        $order->items()->delete();

        $order->items()->createMany(
            collect($items)
                ->map(function (array $item) use ($products): array {
                    $product = $products->get((int) $item['product_id']);

                    return [
                        'product_id' => (int) $item['product_id'],
                        'quantity' => (int) $item['quantity'],
                        'unit_price' => $product?->selling_price ?? 0,
                    ];
                })
                ->values()
                ->all()
        );
    }

    /**
     * @return array<int, array<string, int>>
     */
    private function initialItems(Order $order): array
    {
        if (! $order->relationLoaded('items')) {
            return [];
        }

        return $order->items
            ->map(function ($item): array {
                return [
                    'product_id' => (int) $item->product_id,
                    'quantity' => (int) $item->quantity,
                ];
            })
            ->values()
            ->all();
    }

    private function customerOrderHistory(?Customer $customer)
    {
        if (! $customer) {
            return collect();
        }

        return Order::query()
            ->select('orders.*')
            ->selectSub(
                DB::table('order_items')
                    ->selectRaw('COALESCE(SUM(quantity * unit_price), 0)')
                    ->whereColumn('order_id', 'orders.id'),
                'total_amount'
            )
            ->where('customer_id', $customer->id)
            ->withCount('items')
            ->latest('id')
            ->limit(6)
            ->get();
    }

    /**
     * @return array{orders_count:int,total_spent:float,last_order_at:?string}
     */
    private function customerOrderStats(?Customer $customer): array
    {
        if (! $customer) {
            return [
                'orders_count' => 0,
                'total_spent' => 0.0,
                'last_order_at' => null,
            ];
        }

        $ordersCount = (int) $customer->orders()->count();
        $totalSpent = (float) DB::table('orders')
            ->leftJoin('order_items', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customer->id)
            ->selectRaw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as total_spent')
            ->value('total_spent');
        $lastOrderAt = $customer->orders()->latest('id')->value('created_at');

        return [
            'orders_count' => $ordersCount,
            'total_spent' => round($totalSpent, 2),
            'last_order_at' => $lastOrderAt,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'customer_id' => __('site.customer'),
            'items' => __('site.selected_products'),
            'items.*.product_id' => __('site.products'),
            'items.*.quantity' => __('site.quantity'),
        ];
    }
}
