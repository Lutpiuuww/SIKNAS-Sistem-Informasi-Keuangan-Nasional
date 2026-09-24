<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiGatewayController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin') {
            abort(403, 'Hanya Super Admin yang dapat mengakses Vault API Keys.');
        }

        $tokens = $user->tokens;
        return view('api-gateway', compact('tokens'));
    }

    public function generate(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = $user->createToken($request->name);

        return back()->with('new_token', $token->plainTextToken)
                     ->with('success', 'API Key berhasil dibuat. Salin key ini sekarang karena tidak akan ditampilkan lagi.');
    }
}
