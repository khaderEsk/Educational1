<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Ads;
use App\Models\Comment;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    use GeneralTrait;

    public function commentsAds($id)
    {
        try {
            $ads=Ads::find($id);
            if(!$ads)
                return $this->returnError("404", 'Ads not found');
            $data=$ads->comments()->where('parent_id',null)->get();
            $data->loadMissing('user:id,name','childrenRecursive');

            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            return $this->returnError("500",$ex->getMessage());
        }
    }



    public function store(CommentRequest $request)
    {
        try {
            DB::beginTransaction();
            $user=auth()->user();

            $ads=Ads::find($request->ads_id);

            if(!$ads)
                return $this->returnError("404", 'Ads not found');
            if(isset($request->parent_id))
            {
                $parent=Comment::find($request->parent_id);
                if(!$parent)
                    return $this->returnError("404", 'Parent not found');
            }

            $comment=$user->comments()->create([
                'ads_id'=>$request->ads_id,
                'comment'=>$request->comment,
                'parent_id'=>isset($request->parent_id)? $request->parent_id :null,
            ]);

            DB::commit();
            return $this->returnData($comment, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }




    public function show(Comment $comment)
    {
        //
    }




    public function update(UpdateCommentRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $user=auth()->user();
            $data=$user->comments()->find($id);
            if (!$data) {
                return $this->returnError("404",'Not found');
            }

            $data->update([
                'comment'  => $request->comment,
            ]);
            DB::commit();
            return $this->returnData($data, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError("500",'Please try again later');

        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $user=auth()->user();

            $comment = $user->comments()->find($id);
            if (!$comment)
                return $this->returnError("404", 'not found');
            $comment->delete();

            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }
}
