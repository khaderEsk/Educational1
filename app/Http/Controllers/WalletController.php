<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use GeneralTrait;
    public function index()
    {
        $wallet = Wallet::with('user')->get();
        return $this->returnData($wallet, __('backend.operation completed successfully', [], app()->getLocale()));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        try {
            $user = Auth::user();
            $user->load('wallet');
            $responseData = [
                'name' => $user->name,
                'wallet' => $user->wallet
            ];
            return $this->returnData($responseData, 'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError("500", $ex->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
