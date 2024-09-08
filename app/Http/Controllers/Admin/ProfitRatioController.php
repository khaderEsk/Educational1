<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfitRatioRequest;
use App\Models\FinancialReport;
use App\Models\ProfitRatio;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitRatioController extends Controller
{

    use GeneralTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $ProfitRatios = ProfitRatio::get();
            return $this->returnData(200, $ProfitRatios);
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
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
    public function show(string $id)
    {
        //
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
    public function updateProfitRatioFile(ProfitRatioRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $ProfitRatio = ProfitRatio::find($id);
            if (!$ProfitRatio) {
                return $this->returnError(404, 'Profit Ratio not found');
            }
            if ($ProfitRatio->type != 'file') {
                return $this->returnError(500, 'Do not modify the profit percentage of the files');
            }
            $ProfitRatio->update([
                'value' => $request->value
            ]);
            DB::commit();
            return $this->returnData($ProfitRatio, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }

    public function updateProfitRatioVideoCall(ProfitRatioRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $ProfitRatio = ProfitRatio::find($id);
            if (!$ProfitRatio) {
                return $this->returnError(404, 'Profit Ratio not found');
            }
            if ($ProfitRatio->type != 'video call') {
                return $this->returnError(500, 'Do not modify the profit percentage of the files');
            }
            $ProfitRatio->update([
                'value' => $request->value
            ]);
            DB::commit();
            return $this->returnData($ProfitRatio, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }


    public function updateProfitRatioPrivate(ProfitRatioRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $ProfitRatio = ProfitRatio::find($id);
            if (!$ProfitRatio) {
                return $this->returnError(404, 'Profit Ratio not found');
            }
            if ($ProfitRatio->type != 'private lesson') {
                return $this->returnError(500, 'Do not modify the profit percentage of the files');
            }
            $ProfitRatio->update([
                'value' => $request->value
            ]);
            DB::commit();
            return $this->returnData($ProfitRatio, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }


    public function updateProfitRatioAds(ProfitRatioRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $ProfitRatio = ProfitRatio::find($id);
            if (!$ProfitRatio) {
                return $this->returnError(404, 'Profit Ratio not found');
            }
            if ($ProfitRatio->type != 'ads') {
                return $this->returnError(500, 'Do not modify the profit percentage of the files');
            }
            $ProfitRatio->update([
                'value' => $request->value
            ]);
            DB::commit();
            return $this->returnData($ProfitRatio, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function getFinancialReports()
    {
        DB::beginTransaction();
        try {

            $reports = FinancialReport::get();
            DB::commit();
            return $this->returnData($reports, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }
    }
}
