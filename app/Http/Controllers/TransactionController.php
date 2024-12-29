<?php

namespace App\Http\Controllers;

use App\Models\Saldo;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function indexByNim(Request $request, $nim): JsonResponse
    {
        $transactions = Transaction::where('nim', $nim)->get();

        if ($transactions->isEmpty()) {
            return response()->json(['message' => 'Tidak ada transaksi dengan NIM ini'], 404);
        }

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        Log::info('Data dari Flutter:', ['request' => $request->all()]); // Benar!
        Log::info('Data nim dari request:', ['nim' => $request->nim]); // Benar!
        Log::info('Tipe data nim:', ['tipe' => gettype($request->nim)]); // Benar!

        $validator = Validator::make($request->all(), [
            'nim' => 'required|exists:saldos,nim',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Mengembalikan error validasi
        }

        DB::beginTransaction(); // Memulai transaksi database

        try {
            $transaction = Transaction::create($request->all());

            $saldo = Saldo::firstOrCreate(['nim' => $request->nim]);
            if ($request->type === 'income') {
                $saldo->saldo += $request->amount;
            } else {
                $saldo->saldo -= $request->amount;
            }
            $saldo->save();

            DB::commit(); // Commit transaksi jika semuanya berhasil

            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            DB::rollback(); // Rollback transaksi jika terjadi error
            return response()->json(['message' => 'Terjadi kesalahan saat memproses transaksi.', 'error' => $e->getMessage()], 500); // Mengembalikan pesan error dan detail error
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nim' => 'required|exists:saldos,nim',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
            }

            $originalAmount = $transaction->amount;
            $originalType = $transaction->type;
            $originalNim = $transaction->nim;

            $transaction->update($request->all());

            // Koreksi saldo berdasarkan perubahan transaksi
            $saldo = Saldo::find($originalNim);
            if ($originalType === 'income') {
                $saldo->saldo -= $originalAmount;
            } else {
                $saldo->saldo += $originalAmount;
            }

            if ($request->type === 'income') {
                $saldo->saldo += $request->amount;
            } else {
                $saldo->saldo -= $request->amount;
            }
            $saldo->save();


            DB::commit();

            return response()->json($transaction, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Terjadi kesalahan saat memperbarui transaksi.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getSaldoByNim(Request $request, $nim)
    {
        $saldo = Saldo::find($nim);

        if ($saldo) {
            return response()->json([
                'saldo' => (double) $saldo->saldo // Casting ke double
            ], 200);
        } else {
            return response()->json(['message' => 'Saldo tidak ditemukan untuk NIM ini.'], 404);
        }
    }

    private function updateSaldoByNim($nim, $saldo)
    {
        DB::table('saldos')->updateOrInsert(
            ['nim' => $nim],
            ['saldo' => $saldo]
        );
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
            }

            $saldo = Saldo::find($transaction->nim);
            if ($transaction->type === 'income') {
                $saldo->saldo -= $transaction->amount;
            } else {
                $saldo->saldo += $transaction->amount;
            }
            $saldo->save();

            $transaction->delete();

            DB::commit();

            return response()->json(['message' => 'Transaksi berhasil dihapus.'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus transaksi.', 'error' => $e->getMessage()], 500);
        }
    }
}