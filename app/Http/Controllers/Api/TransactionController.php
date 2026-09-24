<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function liveTicker()
    {
        $user = Auth::user();
        
        $query = DB::table('transactions')
                    ->leftJoin('regions', 'transactions.region_id', '=', 'regions.id')
                    ->select('transactions.*', 'regions.lat', 'regions.lng')
                    ->orderBy('transactions.created_at', 'desc');

        if ($user && $user->role === 'gubernur') {
            $query->where('transactions.region_id', $user->region_id);
        }

        $latest = $query->take(10)->get();
        return response()->json($latest);
    }

    public function reportsMetrics()
    {
        $user = Auth::user();
        $query = DB::table('transactions');
        
        if ($user && $user->role === 'gubernur') {
            $query->where('region_id', $user->region_id);
        }

        $totalTransactions = (clone $query)->count();
        $totalIn = (clone $query)->where('type', 'IN')->sum('amount');
        $totalOut = (clone $query)->where('type', 'OUT')->sum('amount');

        return response()->json([
            'total_doc' => $totalTransactions,
            'total_in' => $totalIn,
            'total_out' => $totalOut
        ]);
    }

    public function simulate()
    {
        $categories = ['Pendapatan Pajak', 'Pendapatan Non-Pajak', 'Belanja Pusat', 'TKDD', 'Dana Desa'];
        $cat = $categories[array_rand($categories)];
        $isIncome = in_array($cat, ['Pendapatan Pajak', 'Pendapatan Non-Pajak']);
        $amount = rand(5000000000, 500000000000); 
        
        $isAnomaly = false;
        if (!$isIncome) {
            $isAnomaly = \App\Http\Controllers\Sentinel::detectAnomaly($cat, $amount);
        }
        $status = $isAnomaly ? 'Anomaly' : 'Success';
        $regionId = in_array($cat, ['TKDD', 'Dana Desa']) ? rand(1, 38) : null;

        DB::table('transactions')->insert([
            'transaction_number' => 'TRX-' . strtoupper(uniqid()),
            'category' => $cat,
            'description' => 'Transaksi Live: ' . $cat,
            'amount' => $amount,
            'type' => $isIncome ? 'IN' : 'OUT',
            'status' => $status,
            'region_id' => $regionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json(['status' => 'ok', 'amount' => $amount, 'is_anomaly' => $isAnomaly, 'region_id' => $regionId]);
    }

    public function storeExternal(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric',
            'type' => 'required|in:IN,OUT',
            'region_id' => 'nullable|exists:regions,id',
            'description' => 'nullable|string'
        ]);

        $isIncome = $request->type === 'IN';
        $isAnomaly = false;
        
        if (!$isIncome) {
            $isAnomaly = \App\Http\Controllers\Sentinel::detectAnomaly($request->category, $request->amount);
        }
        
        $status = $isAnomaly ? 'Anomaly' : 'Success';

        DB::table('transactions')->insert([
            'transaction_number' => 'EXT-' . strtoupper(uniqid()),
            'category' => $request->category,
            'description' => $request->description ?? 'Integrasi API External',
            'amount' => $request->amount,
            'type' => $request->type,
            'status' => $status,
            'region_id' => $request->region_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil dicatat SIKNAS',
            'status' => $status,
            'is_anomaly' => $isAnomaly
        ], 201);
    }
}
