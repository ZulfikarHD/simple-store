<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * PasswordVerificationController untuk verifikasi password admin
 * sebelum melakukan aksi sensitif seperti update status, edit, atau pembatalan
 *
 * @author Zulfikar Hidayatullah
 */
class PasswordVerificationController extends Controller
{
    /**
     * Verifikasi password admin untuk aksi sensitif
     * dimana password akan dicocokkan dengan password user yang sedang login
     * dengan audit logging untuk tracking verification attempts
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ], [
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = $request->user();

        if (! Hash::check($validated['password'], $user->password)) {
            // Audit log untuk failed password verification
            AuditLog::log(
                action: 'password.verify.failed',
                newValues: ['result' => 'failed']
            );

            return response()->json([
                'success' => false,
                'message' => 'Password yang Anda masukkan salah.',
            ], 422);
        }

        // Audit log untuk successful password verification
        AuditLog::log(
            action: 'password.verify.success',
            newValues: ['result' => 'success']
        );

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diverifikasi.',
        ]);
    }
}
