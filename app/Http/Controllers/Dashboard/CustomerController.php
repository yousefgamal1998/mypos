<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->search($search);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('Dashboard.customers.index', compact('customers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Dashboard.customers.create', [
            'customer' => new Customer(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), [], $this->validationAttributes());

        Customer::create($validated);

        session()->flash('success', __('site.added_successfully'));

        return redirect()->route('dashboard.customers.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $recentOrders = Order::query()
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
            ->limit(8)
            ->get();

        $customerStats = [
            'orders_count' => (int) $customer->orders()->count(),
            'total_spent' => round((float) DB::table('orders')
                ->leftJoin('order_items', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.customer_id', $customer->id)
                ->selectRaw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as total_spent')
                ->value('total_spent'), 2),
            'last_order_at' => $customer->orders()->latest('id')->value('created_at'),
        ];

        return view('Dashboard.customers.show', compact('customer', 'recentOrders', 'customerStats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('Dashboard.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate($this->rules(), [], $this->validationAttributes());

        $customer->update($validated);

        session()->flash('success', __('site.updated_successfully'));

        return redirect()->route('dashboard.customers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        session()->flash('success', __('site.deleted_successfully'));

        return redirect()->route('dashboard.customers.index');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'alternate_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'name' => __('site.name'),
            'phone' => __('site.phone'),
            'alternate_phone' => app()->isLocale('ar') ? __('site.phone') : __('site.alternate_phone'),
            'address' => __('site.address'),
        ];
    }
}

