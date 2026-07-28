<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');
        $tab = $request->query('tab') === 'regional' ? 'regional' : 'internal';
        $perPage = min(max($request->integer('per_page', 10), 10), 100);

        return Inertia::render('Admin/Users', [
            'users' => User::query()
                ->with('committee:id,name')
                ->withCount(['sportAssignments as active_assignments_count' => fn ($query) => $query->where('is_active', true)])
                ->when($search, fn ($query) => $query->where(fn ($query) => $query
                    ->whereLike('name', "%{$search}%", caseSensitive: false)
                    ->orWhereLike('email', "%{$search}%", caseSensitive: false)))
                ->when($tab === 'regional', fn ($query) => $query->where('role', 'pd_admin'), fn ($query) => $query->whereIn('role', ['admin_event', 'scorekeeper', 'sport_coordinator']))
                ->when($status === 'active', fn ($query) => $query->where('account_status', 'verified'))
                ->when($status === 'inactive', fn ($query) => $query->where('account_status', '!=', 'verified'))
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'stats' => [
                'total' => User::query()->whereIn('role', ['admin_event', 'scorekeeper', 'sport_coordinator'])->count(),
                'event_admins' => User::query()->where('role', 'admin_event')->count(),
                'staff' => User::query()->whereIn('role', ['scorekeeper', 'sport_coordinator'])->count(),
                'regional' => User::query()->where('role', 'pd_admin')->count(),
                'regional_active' => User::query()->where('role', 'pd_admin')->where('account_status', 'verified')->count(),
                'regional_inactive' => User::query()->where('role', 'pd_admin')->where('account_status', '!=', 'verified')->count(),
            ],
            'filters' => ['search' => $search, 'status' => $status, 'tab' => $tab, 'per_page' => $perPage],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat membuat pengguna.');
        User::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', Rule::in(['super_admin', 'admin_event', 'scorekeeper', 'sport_coordinator'])],
        ], $this->passwordMessages()) + ['account_status' => 'verified']);

        return back()->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isPdAdmin(), 404);
        $actor = $request->user();
        abort_unless($actor->isSuperAdmin() || ($actor->isAdminEvent() && $actor->is($user)), 403, 'Admin hanya dapat memperbarui akun sendiri.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', Rule::in(['super_admin', 'admin_event', 'scorekeeper', 'sport_coordinator'])],
            'account_status' => ['required', Rule::in(['verified', 'suspended'])],
        ], $this->passwordMessages());
        abort_if($actor->is($user) && ($data['role'] !== $user->role || $data['account_status'] !== 'verified'), 422, 'Akun sendiri tidak dapat dinonaktifkan atau diubah perannya.');
        if (empty($data['password'])) unset($data['password']);
        unset($data['password_confirmation']);
        $user->update($data);

        return back()->with('success', 'Akun pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat menghapus pengguna.');
        abort_if($user->isPdAdmin(), 404);
        abort_if($request->user()->is($user), 422, 'Akun sendiri tidak dapat dihapus.');

        try {
            $user->delete();
        } catch (QueryException) {
            return back()->with('error', 'Pengguna tidak dapat dihapus karena sudah memiliki histori aktivitas. Nonaktifkan akun sebagai gantinya.');
        }

        return back()->with('success', 'Akun pengguna berhasil dihapus.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengubah status pengguna.');
        abort_if($user->isPdAdmin(), 404);
        abort_if($request->user()->is($user), 422, 'Akun sendiri tidak dapat dinonaktifkan.');

        $active = $user->account_status !== 'verified';
        $user->update(['account_status' => $active ? 'verified' : 'suspended']);

        return back()->with('success', $active ? 'Akun pengguna berhasil diaktifkan.' : 'Akun pengguna berhasil dinonaktifkan.');
    }

    private function passwordMessages(): array
    {
        return [
            'password.confirmed' => 'Ulangi Kata Sandi tidak cocok.',
            'password.min' => 'Kata Sandi minimal 8 karakter.',
            'password.mixed' => 'Kata Sandi harus memuat huruf besar dan huruf kecil.',
            'password.numbers' => 'Kata Sandi harus memuat angka.',
            'password.symbols' => 'Kata Sandi harus memuat karakter khusus.',
        ];
    }
}
