<?php

namespace App\Http\Controllers;

use App\Concerns\HasDataTable;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use HasDataTable;

    protected array $searchable = ['name', 'email'];
    protected array $sortable = ['id', 'name', 'email', 'created_at'];

    public function index(Request $request): Response
    {
        $query = $this->applyDataTableFilters(
            User::query(),
            $request,
            $this->searchable,
            $this->sortable
        );

        $users = $query->paginate($request->input('per_page', 10));

        return Inertia::render('Users/Index', [
            'users' => $users->items(),
            ...$this->getDataTableResponse($users, $request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (filled($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back();
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        User::destroy($user->id);

        return back();
    }
}
