<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $users = User::query()
            ->role('admin')
            ->select(['id', 'first_name', 'last_name', 'email'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->latest()->paginate(5)
            ->withQueryString();

        return view('dashboard.users.index', compact('users', 'search'));

    } // end of index

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.users.create');
    } // end of create

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
            'permissions' => 'nullable|array',
        ]);

        $request_data = collect($validated)
            ->except(['permissions'])
            ->put('password', bcrypt($validated['password']))
            ->all();

        $user = User::create($request_data);
        $user->syncPermissions($validated['permissions'] ?? []);

        session()->flash('success', __('site.added_successfully'));

        return redirect()->to(
            LaravelLocalization::getLocalizedURL(app()->getLocale(), route('dashboard.users.index'))
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
} // end of controller
