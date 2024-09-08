<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileTeacherRequest;
use App\Jobs\AdminNotificationJob;
use App\Models\Domain;
use App\Models\ProfileTeacher;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProfileTeacherRequest;

class ProfileTeacherController extends Controller
{
    use GeneralTrait;

    private $uploadPath = "assets/images/profile_teachers";


    public function index(Request $request)
    {
        try {
            DB::beginTransaction();
            $user=auth()->user();

            $profile_teacher = ProfileTeacher::where('status',1)
                ->whereDoesntHave('user.block')
            ->orderByRaw("CASE WHEN (SELECT governorate FROM users WHERE users.id = profile_teachers.user_id) = '{$user->governorate}' THEN 0 ELSE 1 END")
                ->filter($request)
                ->get();
            if(count($profile_teacher)>0)
                $profile_teacher->loadMissing(['user','domains']);

            DB::commit();
            return $this->returnData($profile_teacher, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }


    public function store(ProfileTeacherRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            $certificate = null;
            if (isset($request->certificate)) {
                $certificate = $this->saveImage($request->certificate, $this->uploadPath);
            }
            $profile_teacher = $user->profile_teacher()->create([
                'certificate' => $certificate,
                'description' => $request->description,
                'jurisdiction' => $request->jurisdiction,
                'status' => 0,
                'assessing' => 0
            ]);
            $domains = $request->domains;
            $list_domains = [];
            foreach ($domains as $value) {
                $domain = [
                    'profile_teacher_id' => $profile_teacher->id,
                    'type' => $value,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                array_push($list_domains, $domain);
            }
            Domain::insert($list_domains);

            $profile_teacher->loadMissing('domains');

            AdminNotificationJob::dispatch( 'طلب انضمام', $user->name.' طلب انضمام جديد من قبل ')->delay(Carbon::now()->addSeconds(2));

            DB::commit();
            return $this->returnData($profile_teacher, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }


    public function show()
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $user->loadMissing(['profile_teacher.domains']);

            DB::commit();
            return $this->returnData($user, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", $ex->getMessage());
        }
    }

    public function getById($id)
    {
        try {
            DB::beginTransaction();

            $profile_teacher = ProfileTeacher::find($id);
            if (!$profile_teacher)
                return $this->returnError("404", 'Not found');
            $profile_teacher->loadMissing(['user','domains']);

            DB::commit();
            return $this->returnData($profile_teacher, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }




    public function update(UpdateProfileTeacherRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            $certificate = null;
            if (isset($request->certificate)) {
                $certificate = $this->saveImage($request->certificate, $this->uploadPath);
            }

            $profile_teacher = $user->profile_teacher()->first();
            $image=null;
            if(isset($request->image))
            {
                $image = $this->saveImage($request->image, $this->uploadPath);
                $this->deleteImage($user->image);
            }

            $profile_teacher->update([
                'certificate' => isset($request->certificate) ? $certificate : $profile_teacher->certificate,
                'description' => isset($request->description) ? $request->description : $profile_teacher->description,
                'jurisdiction'=> isset($request->jurisdiction) ? $request->jurisdiction : $profile_teacher->jurisdiction,
            ]);

            $user->update([
                'address'=>isset($request->address) ? $request->address : $user->address,
                'governorate'=>isset($request->governorate) ? $request->governorate : $user->governorate,
                'image'=>isset($request->image) ? $image: $user->image
            ]);


            $domains=isset($request->domains)?$request->domains:[];
            if(count($domains)>0) {
                foreach ($domains as $domain) {
                    $item = $profile_teacher->domains()->firstOrNew(['id' => $domain['id']]);
                    $item->type = $domain['type'];
                    $item->save();
                }
            }

            $profile_teacher->loadMissing(['domains','user']);
            DB::commit();
            return $this->returnData($profile_teacher, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }


    public function destroy()
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            $profile_teacher = $user->profile_teacher()->first();
            if (!$profile_teacher)
                return $this->returnError("404", 'not found');
            $profile_teacher->delete();
            DB::commit();
            return $this->returnSuccessMessage(__('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }


    public function getByIdForAdmin($id)
    {
        try {
            DB::beginTransaction();

            $profile_teacher = ProfileTeacher::find($id);

            if (!$profile_teacher)
                return $this->returnError("404", 'not found');

            $profile_teacher->loadMissing(['user.wallet'=> function ($query) {
                $query->withCount(['governor_charge','governor_recharge']);
            },'request_complete','teaching_methods','ads','day.hours','user.qualification_users']);
            $profile_teacher->loadCount([
                'report_as_reporter',
                'report_as_reported',
                'teaching_methods_free',
                'teaching_methods_paid'
            ]);


            DB::commit();
            return $this->returnData($profile_teacher, __('backend.operation completed successfully', [], app()->getLocale()));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError("500", 'Please try again later');
        }
    }

}
