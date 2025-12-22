<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * StaffManagementController untuk mengelola staff/admin accounts
 * dimana owner dapat create, update, dan manage staff lainnya
 * dengan proper authorization dan audit logging
 */
class StaffManagementController extends Controller
{
    /**
     * Menampilkan daftar semua users (staff dan customers)
     * dengan filter dan pagination untuk management yang mudah
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->select(['id', 'name', 'email', 'role', 'phone', 'email_verified_at', 'created_at'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Staff/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->search,
                'role' => $request->role,
            ],
            'stats' => [
                'total_admins' => User::where('role', 'admin')->count(),
                'total_customers' => User::where('role', 'customer')->count(),
            ],
        ]);
    }

    /**
     * Menampilkan form untuk create staff baru
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Staff/Create');
    }

    /**
     * Menyimpan staff baru dengan role assignment yang secure
     * dimana role di-set secara eksplisit oleh admin, bukan dari request
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:customer,admin'],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
        ]);

        // Create user (tanpa role di $fillable, aman dari mass assignment)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'email_verified_at' => now(), // Auto-verified untuk staff
        ]);

        // ✅ SECURE: Set role secara eksplisit setelah create
        $user->role = $validated['role'];
        $user->save();

        // Audit log untuk staff creation
        AuditLog::log(
            action: 'staff.create',
            modelType: User::class,
            modelId: $user->id,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        );

        return redirect()
            ->route('admin.staff.index')
            ->with('success', "Staff {$user->name} berhasil ditambahkan dengan role {$user->role}.");
    }

    /**
     * Menampilkan form edit staff
     */
    public function edit(User $staff): Response
    {
        // Prevent editing self through this interface
        if ($staff->id === auth()->id()) {
            return redirect()
                ->route('admin.staff.index')
                ->with('error', 'Gunakan menu Profile untuk mengedit data Anda sendiri.');
        }

        return Inertia::render('Admin/Staff/Edit', [
            'staff' => $staff->only(['id', 'name', 'email', 'role', 'phone', 'email_verified_at']),
        ]);
    }

    /**
     * Update staff information dengan proper validation
     */
    public function update(Request $request, User $staff): RedirectResponse
    {
        // Prevent editing self
        if ($staff->id === auth()->id()) {
            return redirect()
                ->route('admin.staff.index')
                ->with('error', 'Gunakan menu Profile untuk mengedit data Anda sendiri.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($staff->id)],
            'role' => ['required', 'in:customer,admin'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Store old values for audit
        $oldValues = $staff->only(['name', 'email', 'role', 'phone']);

        // Update basic info (safe dari mass assignment karena explicit)
        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        $staff->phone = $validated['phone'] ?? null;

        // ✅ SECURE: Update role secara eksplisit
        $staff->role = $validated['role'];

        $staff->save();

        // Audit log
        AuditLog::log(
            action: 'staff.update',
            modelType: User::class,
            modelId: $staff->id,
            oldValues: $oldValues,
            newValues: $staff->only(['name', 'email', 'role', 'phone'])
        );

        return redirect()
            ->route('admin.staff.index')
            ->with('success', "Data staff {$staff->name} berhasil diperbarui.");
    }

    /**
     * Update password staff
     */
    public function updatePassword(Request $request, User $staff): RedirectResponse
    {
        // Prevent changing self password through this interface
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'Gunakan menu Profile untuk mengubah password Anda sendiri.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $staff->password = Hash::make($validated['password']);
        $staff->save();

        // Audit log (tidak simpan password untuk security)
        AuditLog::log(
            action: 'staff.password_reset',
            modelType: User::class,
            modelId: $staff->id,
            newValues: ['password_changed' => true]
        );

        return back()->with('success', "Password {$staff->name} berhasil diubah.");
    }

    /**
     * Delete staff account dengan protection
     */
    public function destroy(User $staff): RedirectResponse
    {
        // Prevent self-deletion
        if ($staff->id === auth()->id()) {
            return redirect()
                ->route('admin.staff.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Prevent deleting admin if this is the last admin
        if ($staff->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()
                    ->route('admin.staff.index')
                    ->with('error', 'Tidak dapat menghapus admin terakhir. Harus ada minimal 1 admin.');
            }
        }

        // Check if staff has orders (optional: prevent deletion if has orders)
        if ($staff->orders()->exists()) {
            return redirect()
                ->route('admin.staff.index')
                ->with('error', "Staff {$staff->name} memiliki riwayat order dan tidak dapat dihapus.");
        }

        $staffName = $staff->name;
        $staffData = $staff->toArray();

        $staff->delete();

        // Audit log
        AuditLog::log(
            action: 'staff.delete',
            modelType: User::class,
            modelId: $staffData['id'],
            oldValues: $staffData
        );

        return redirect()
            ->route('admin.staff.index')
            ->with('success', "Staff {$staffName} berhasil dihapus.");
    }
}
