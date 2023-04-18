<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\Myhelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Commission;
use App\Model\Order;
use App\User;
use Carbon\Carbon;
use App\Model\OtpVerification;
use App\Model\Permission;
use App\Model\Product;
use App\Model\Provider;
use App\Model\Scheme;
use App\Model\UserBankDetail;
use App\Model\UserDocument;
use App\Model\UserPermission;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data['activemenu']['main'] = 'dashboard';

        if (Myhelper::hasRole(['superadmin', 'admin'])) {
            $data['weeksales'] = [];
            $current_week_day = Carbon::now()->startOfWeek();
            $last_week_day = Carbon::now()->startOfWeek()->subWeek();

            for ($i = 0; $i < 7; $i++) {
                $data['weeksales'][] = [
                    'label' => strtoupper($current_week_day->isoFormat('ddd')),
                    'currentweek' => Order::whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered'])->whereDate('created_at', $current_week_day)->sum('payable_amount'),
                    'lastweek' => Order::whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered'])->whereDate('created_at', $last_week_day)->sum('payable_amount'),
                ];

                $current_week_day->addDay();
                $last_week_day->addDay();
            }

            $data['monthsales'] = [];
            $current_month_day = Carbon::now()->startOfYear();
            $last_month_day = Carbon::now()->startOfYear()->subYear();

            for ($i = 0; $i < 12; $i++) {
                $data['monthsales'][] = [
                    'label' => strtoupper($current_month_day->isoFormat('MMM')),
                    'currentyear' => Order::whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered'])->whereMonth('created_at', $current_month_day)->whereYear('created_at', $current_month_day)->sum('payable_amount'),
                    'lastyear' => Order::whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered'])->whereMonth('created_at', $last_month_day)->whereYear('created_at', $last_month_day)->sum('payable_amount'),
                ];

                $current_month_day->addMonth();
                $last_month_day->addMonth();
            }

            $data['count'] = array(
                'neworders' => Order::whereIn('status', ['received'])->count(),
                'todaysales' => Order::whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered'])->whereDate('created_at', Carbon::now())->sum('payable_amount'),
                'monthsales' => Order::whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered'])->whereMonth('created_at', Carbon::now())->whereYear('created_at', Carbon::now())->sum('payable_amount'),
                'users' => User::whereHas('role', function ($q) {
                    $q->where('slug', 'user');
                })->count(),
            );

            $data['latestorders'] = Order::whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered', 'cancelled', 'returned'])->orderBy('created_at', 'DESC')->limit(8)->get();
            $data['latestproducts'] = Product::orderBy('created_at', 'DESC')->limit(5)->get();

            // dd($data['latestproducts']->toArray());

            return view('dashboard.dashboard.admin', $data);
        } elseif (Myhelper::hasRole(['deliveryboy'])) {
            $data['weeklydelivered'] = [];
            $current_week_day = Carbon::now()->startOfWeek();
            $last_week_day = Carbon::now()->startOfWeek()->subWeek();

            for ($i = 0; $i < 7; $i++) {
                $data['weeklydelivered'][] = [
                    'label' => strtoupper($current_week_day->isoFormat('ddd')),
                    'currentweek' => Order::where('deliveryboy_id', \Auth::id())->whereIn('status', ['delivered'])->whereDate('created_at', $current_week_day)->count(),
                    'lastweek' => Order::where('deliveryboy_id', \Auth::id())->whereIn('status', ['delivered'])->whereDate('created_at', $last_week_day)->count(),
                ];

                $current_week_day->addDay();
                $last_week_day->addDay();
            }

            $data['count'] = array(
                'neworders' => Order::where('deliveryboy_id', \Auth::id())->whereNotIn('status', ['paymentfailed', 'delivered', 'cancelled', 'returned'])->count(),
                'totaldelivered' => Order::where('deliveryboy_id', \Auth::id())->whereIn('status', ['delivered'])->count(),
            );

            $data['latestorders'] = Order::where('deliveryboy_id', \Auth::id())->orderBy('created_at', 'DESC')->limit(15)->get();

            return view('dashboard.dashboard.deliveryboy', $data);
        }

        return view('dashboard.home', $data);
    }

    public function profile($id = 'none')
    {
        if ($id == 'none') {
            $data['user'] = User::findorfail(\Auth::id());
            $data['activemenu']['main'] = 'profile';
        } else {
            $data['user'] = User::findorfail(base64_decode($id));
            $data['activemenu']['main'] = 'members';
            $data['activemenu']['sub'] = $data['user']->role->slug;


            if (!\Myhelper::can('edit_' . $data['user']->role->slug)) {
                abort(401);
            }
        }

        // dd($data['user']->documents->toArray());

        return view('dashboard.profile', $data);
    }

    public function updateProfile(Request $post)
    {
        if (!$post->has('id')) {
            $post['id'] = \Auth::id();
        }

        $userdata = User::with('documents')->findorfail($post->id);
        if ($userdata->id != \Auth::id()) {
            if (!\Myhelper::can('edit_' . $userdata->role->slug)) {
                return response()->json(['status' => 'Permission Denied'], 400);
            }
        }

        switch ($post->type) {
            case 'basicdetails':
                $rules = [
                    'name' => 'required',
                    'email' => 'required|unique:users,email,' . $post->id,
                    'mobile' => 'required|digits:10|unique:users,mobile,' . $post->id,
                ];

                if ($userdata->role->slug == 'branch') {
                    if (\Myhelper::hasrole(['superadmin', 'admin'])) {
                        $rules['scheme_id'] = 'required|exists:schemes,id';
                        $rules['business_category'] = 'required|in:mart,restaurant';
                    }
                } elseif ($userdata->role->slug == 'deliveryboy') {
                    if (\Myhelper::hasrole(['superadmin', 'admin'])) {
                        $rules['vaccination'] = 'nullable|in:partially,fully';
                    }
                }
                break;

            case 'profileimage':
                $rules = [
                    'profile_image' => 'required|mimes:jpeg,jpg,png,gif|max:1',
                ];
                break;

            case 'documentdetails':
                if (\Myhelper::hasrole(['superadmin', 'admin'])) {
                    if (in_array($userdata->role->slug, ['deliveryboy'])) {
                        $rules['drivinglicense_number'] = 'required';
                        $rules['drivinglicense_expiry'] = 'required|date|after:today';
                        $rules['drivinglicense_back_file'] = 'nullable|mimes:jpeg,jpg,png,gif';

                        if (@$userdata->documents->drivinglicense_front) {
                            $rules['drivinglicense_front_file'] = 'nullable|mimes:jpeg,jpg,png,gif';
                        } else {
                            $rules['drivinglicense_front_file'] = 'required|mimes:jpeg,jpg,png,gif';
                        }
                    }

                    if (in_array($userdata->role->slug, ['branch', 'deliveryboy'])) {
                        $rules['govtid_type'] = 'required|in:aadhaar,pancard';
                        $rules['govtid_number'] = 'required';
                        $rules['govtid_back_file'] = 'nullable|mimes:jpeg,jpg,png,gif';

                        if (@$userdata->documents->govtid_front) {
                            $rules['govtid_front_file'] = 'nullable|mimes:jpeg,jpg,png,gif';
                        } else {
                            $rules['govtid_front_file'] = 'required|mimes:jpeg,jpg,png,gif';
                        }
                    }

                    if (in_array($userdata->role->slug, ['branch'])) {
                        $rules['tradelicense_number'] = 'nullable';
                        $rules['tradelicense_doc'] = 'nullable|mimes:jpeg,jpg,png,gif';
                        $rules['fssaireg_number'] = 'nullable';
                        $rules['fssaireg_doc'] = 'nullable|mimes:jpeg,jpg,png,gif';
                        $rules['gstin_number'] = 'nullable|string|max:15|min:15|regex:^\d{2}[A-Z]{5}\d{4}[A-Z]{1}[A-Z\d]{1}[Z]{1}[A-Z\d]{1}^';
                        $rules['gstin_doc'] = 'nullable|mimes:jpeg,jpg,png,gif';
                    }
                } else {
                    return response()->json(['status' => 'Unsupported request'], 400);
                }
                break;

            case 'bankdetails':
                if (\Myhelper::hasrole(['superadmin', 'admin'])) {
                    if (in_array($userdata->role->slug, ['branch', 'deliveryboy'])) {
                        $rules = [
                            'accno' => 'required|numeric',
                            'ifsccode' => 'required',
                            'accholder' => 'required',
                            'bankname' => 'required',
                            'pancard_no' => 'required|regex:^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$^',
                        ];

                        if (@$userdata->bankdetails->pancard_file) {
                            $rules['pancard_file'] = 'nullable|mimes:jpeg,jpg,png,gif';
                        } else {
                            $rules['pancard_file'] = 'required|mimes:jpeg,jpg,png,gif';
                        }
                    }
                } else {
                    return response()->json(['status' => 'Unsupported request'], 400);
                }
                break;

            case 'changepassword':
                $rules = [
                    'new_password' => 'required|min:6|max:30|confirmed',
                ];

                if ($userdata->id == \Auth::id()) {
                    $rules['current_password'] = 'required';
                }
                break;

            case 'defaultpassword':
                $rules = [
                    'new_password' => 'required|min:6|max:30|confirmed',
                ];
                break;

            case 'verifymobile':
                if (!$post->has('otp') || !in_array($post->otp, ['send', 'resend'])) {
                    $rules = [
                        'otp' => 'required|digits:6',
                    ];
                }
                break;

            default:
                return response()->json(['status' => 'Unsupported request'], 400);
                break;
        }

        if (isset($rules)) {
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => $value[0]], 400);
                }
            }
        }

        switch ($post->type) {
            case 'basicdetails':
                $update['name'] = $post->name;
                $update['email'] = $post->email;
                $update['mobile'] = $post->mobile;

                if ($userdata->mobile != $post->mobile) {
                    $update['mobile_verified_at'] = NULL;
                }

                if ($userdata->role->slug == 'branch') {
                    if (\Myhelper::hasrole(['superadmin', 'admin'])) {
                        $update['scheme_id'] = $post->scheme_id;
                        $update['business_category'] = $post->business_category;

                        if ($update['business_category'] != $userdata->business_category) {
                            switch ($userdata->business_category) {
                                case 'mart':
                                case 'restaurant':
                                    $products = Product::where('type', $userdata->business_category)->where('shop_id', \Myhelper::getShop($userdata->id))->where('status', '1')->count();
                                    if ($products > 0) {
                                        return response()->json(['status' => 'The associated store with this merchant have products in ' . $userdata->business_category . ' category. Either suspennd or delete those to change category for this merchant'], 400);
                                    }
                                    break;
                            }

                            $permission_types = array(
                                'mart' => ['mart'],
                                'restaurant' => ['restaurant'],
                            );

                            foreach ($permission_types[$update['business_category']] as $key => $type) {
                                $permission_ids = Permission::where('type', $type)->pluck('id');
                                UserPermission::where('user_id', $userdata->id)->whereIn('permission_id', $permission_ids)->delete();
                            }
                        }
                    }
                } elseif ($userdata->role->slug == 'deliveryboy') {
                    if (\Myhelper::hasrole(['superadmin', 'admin'])) {
                        $update['vaccination'] = $post->vaccination;
                    }
                }

                $action = User::where('id', $post->id)->update($update);
                if ($action) {
                    return response()->json(['status' => 'Profile updated successfully'], 200);
                } else {
                    return response()->json(['status' => 'Task failed. Please try again later'], 400);
                }
                break;

            case 'documentdetails':
                if (in_array($userdata->role->slug, ['deliveryboy'])) {
                    $update['drivinglicense_number'] = $post->drivinglicense_number;
                    $update['drivinglicense_expiry'] = $post->drivinglicense_expiry;

                    if ($post->file('drivinglicense_front_file')) {
                        $file = $post->file('drivinglicense_front_file');
                        $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                        if (@$userdata->documents->drivinglicense_front) {
                            $dfront_deletefile = 'uploads/profile/documents/' . $userdata->documents->drivinglicense_front;
                        }

                        if (\Image::make($file->getRealPath())->save('uploads/profile/documents/' . $filename, 60)) {
                            $update['drivinglicense_front'] = $filename;

                            if (isset($dfront_deletefile)) {
                                \File::delete($dfront_deletefile);
                            }
                        } else {
                            return response()->json(['status' => 'File cannot be saved to server.'], 400);
                        }
                    }

                    if ($post->file('drivinglicense_back_file')) {
                        $file = $post->file('drivinglicense_back_file');
                        $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                        if (@$userdata->documents->drivinglicense_back) {
                            $dback_deletefile = 'uploads/profile/documents/' . $userdata->documents->drivinglicense_back;
                        }

                        if (\Image::make($file->getRealPath())->save('uploads/profile/documents/' . $filename, 60)) {
                            $update['drivinglicense_back'] = $filename;

                            if (isset($dback_deletefile)) {
                                \File::delete($dback_deletefile);
                            }
                        } else {
                            return response()->json(['status' => 'File cannot be saved to server.'], 400);
                        }
                    }
                }

                if (in_array($userdata->role->slug, ['branch', 'deliveryboy'])) {
                    $update['govtid_type'] = $post->govtid_type;
                    $update['govtid_number'] = $post->govtid_number;

                    if ($post->file('govtid_front_file')) {
                        $file = $post->file('govtid_front_file');
                        $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                        if (@$userdata->documents->govtid_front) {
                            $gfront_deletefile = 'uploads/profile/documents/' . $userdata->documents->govtid_front;
                        }

                        if (\Image::make($file->getRealPath())->save('uploads/profile/documents/' . $filename, 60)) {
                            $update['govtid_front'] = $filename;

                            if (isset($gfront_deletefile)) {
                                \File::delete($gfront_deletefile);
                            }
                        } else {
                            return response()->json(['status' => 'File cannot be saved to server.'], 400);
                        }
                    }

                    if ($post->file('govtid_back_file')) {
                        $file = $post->file('govtid_back_file');
                        $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                        if (@$userdata->documents->govtid_back) {
                            $gback_deletefile = 'uploads/profile/documents/' . $userdata->documents->govtid_back;
                        }

                        if (\Image::make($file->getRealPath())->save('uploads/profile/documents/' . $filename, 60)) {
                            $update['govtid_back'] = $filename;

                            if (isset($gback_deletefile)) {
                                \File::delete($gback_deletefile);
                            }
                        } else {
                            return response()->json(['status' => 'File cannot be saved to server.'], 400);
                        }
                    }
                }

                if (in_array($userdata->role->slug, ['branch'])) {
                    $update['tradelicense_number'] = $post->tradelicense_number;
                    $update['fssaireg_number'] = $post->fssaireg_number;
                    $update['gstin_number'] = strtoupper($post->gstin_number);

                    if ($post->file('tradelicense_doc')) {
                        $file = $post->file('tradelicense_doc');
                        $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                        if (@$userdata->documents->tradelicense_doc) {
                            $tl_deletefile = 'uploads/profile/documents/' . $userdata->documents->tradelicense_doc;
                        }

                        if (\Image::make($file->getRealPath())->save('uploads/profile/documents/' . $filename, 60)) {
                            $update['tradelicense_doc'] = $filename;

                            if (isset($tl_deletefile)) {
                                \File::delete($tl_deletefile);
                            }
                        } else {
                            return response()->json(['status' => 'File cannot be saved to server.'], 400);
                        }
                    }

                    if ($post->file('fssaireg_doc')) {
                        $file = $post->file('fssaireg_doc');
                        $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                        if (@$userdata->documents->fssaireg_doc) {
                            $fssai_deletefile = 'uploads/profile/documents/' . $userdata->documents->fssaireg_doc;
                        }

                        if (\Image::make($file->getRealPath())->save('uploads/profile/documents/' . $filename, 60)) {
                            $update['fssaireg_doc'] = $filename;

                            if (isset($fssai_deletefile)) {
                                \File::delete($fssai_deletefile);
                            }
                        } else {
                            return response()->json(['status' => 'File cannot be saved to server.'], 400);
                        }
                    }

                    if ($post->file('gstin_doc')) {
                        $file = $post->file('gstin_doc');
                        $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                        if (@$userdata->documents->gstin_doc) {
                            $gstin_deletefile = 'uploads/profile/documents/' . $userdata->documents->gstin_doc;
                        }

                        if (\Image::make($file->getRealPath())->save('uploads/profile/documents/' . $filename, 60)) {
                            $update['gstin_doc'] = $filename;

                            if (isset($gstin_deletefile)) {
                                \File::delete($gstin_deletefile);
                            }
                        } else {
                            return response()->json(['status' => 'File cannot be saved to server.'], 400);
                        }
                    }
                }

                if (!isset($update)) {
                    return response()->json(['status' => 'Nothing to update!! Please update a document for the operation'], 400);
                }

                $action = UserDocument::updateorcreate(['user_id' => $post->id], $update);
                if ($action) {
                    return response()->json(['status' => 'Documents updated successfully'], 200);
                } else {
                    return response()->json(['status' => 'Task failed. Please try again later'], 400);
                }
                break;

            case 'bankdetails':
                $update = array(
                    'accno' => $post->accno,
                    'ifsccode' => $post->ifsccode,
                    'accholder' => $post->accholder,
                    'bankname' => $post->bankname,
                    'pancard_no' => strtoupper($post->pancard_no),
                );

                if ($post->file('pancard_file')) {
                    $file = $post->file('pancard_file');
                    $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                    if (@$userdata->bankdetails->pancard_file) {
                        $pncrd_delfile = 'uploads/profile/bankdetails/' . $userdata->bankdetails->pancard_file;
                    }

                    if (\Image::make($file->getRealPath())->save('uploads/profile/bankdetails/' . $filename, 60)) {
                        $update['pancard_file'] = $filename;

                        if (isset($pncrd_delfile)) {
                            \File::delete($pncrd_delfile);
                        }
                    } else {
                        return response()->json(['status' => 'File cannot be saved to server.'], 400);
                    }
                }

                $action = UserBankDetail::updateorcreate(['user_id' => $post->id], $update);
                if ($action) {
                    return response()->json(['status' => 'Bank details updated successfully'], 200);
                } else {
                    return response()->json(['status' => 'Task failed. Please try again later'], 400);
                }
                break;

            case 'profileimage':
                $file = $post->file('profile_image');
                $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                if ($userdata->profile_image != NULL) {
                    $deletefile = 'uploads/profile/' . $userdata->profile_image;
                }

                //Resizing and compressing the image
                if (\Image::make($file->getRealPath())->resize(160, 160)->save('uploads/profile/' . $filename, 60)) {
                    $update['profile_image'] = $filename;

                    if (isset($deletefile)) {
                        \File::delete($deletefile);
                    }
                } else {
                    return response()->json(['status' => 'File cannot be saved to server.'], 400);
                }

                $action = User::where('id', $post->id)->update($update);

                if ($action) {
                    \Session::flash('success', 'Profile updated successfully.');
                    return response()->json(['status' => 'Profile updated successfully'], 200);
                } else {
                    return response()->json(['status' => 'Task failed. Please try again later'], 400);
                }
                break;

            case 'changepassword':
                if ($userdata->id == \Auth::id()) {
                    if (!\Hash::check($post->current_password, $userdata->password)) {
                        return response()->json(['status' => 'Current password didnnot matched'], 400);
                    }
                } else {
                    $update['resetpwd'] = 'default';

                    $smsflag = 1;
                    $content = 'Hey ' . $userdata->name . ', we have reset your GoPotu account password on your request. Use ' . $post->new_password . ' as your new password to sign in to your account. Thanks, Team GoPotu';
                }

                $update['password'] = bcrypt($post->new_password);

                $action = User::where('id', $post->id)->update($update);
                if ($action) {
                    if (isset($smsflag) && $smsflag == 1) {
                        \Myhelper::sms($userdata->mobile, $content);
                    }

                    return response()->json(['status' => 'Profile updated successfully'], 200);
                } else {
                    return response()->json(['status' => 'Task failed. Please try again later'], 400);
                }
                break;

            case 'defaultpassword':
                $update['password'] = bcrypt($post->new_password);
                $update['resetpwd'] = 'changed';

                $action = User::where('id', $post->id)->update($update);

                if ($action) {
                    return response()->json(['status' => 'Profile updated successfully'], 200);
                } else {
                    return response()->json(['status' => 'Task failed. Please try again later'], 400);
                }
                break;

            case 'verifymobile':
                if (in_array($post->otp, ['send', 'resend'])) {
                    $post['otp'] = rand(111111, 999999);

                    $body = "Dear $userdata->name, your verification code is $post->otp. Team GoPotu";
                    if (\Myhelper::sms($userdata->mobile, $body)) {
                        OtpVerification::where('mobile', $userdata->mobile)->where('email', $userdata->email)->delete(); #delete prev records

                        $action = OtpVerification::create([
                            'email' => $userdata->email,
                            'mobile' => $userdata->mobile,
                            'otp' => $post->otp,
                        ]);

                        if ($action) {
                            \Session::put('registerdata', $post->all());
                            return response()->json(['status' => 'An OTP has been successfully sent to your Mobile Number.'], 200);
                        } else {
                            return response()->json(['status' => 'Internal server error. Please try again later.'], 400);
                        }
                    } else {
                        return response()->json(['status' => 'OTP cannot be sent. Please try again later.'], 400);
                    }
                } else {
                    $verfication = OtpVerification::where('mobile', $userdata->mobile)->whereBetween('created_at', [Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->first(); #valiate only otp with mobile number
                    if ($verfication) {
                        if (!\Hash::check($post->otp, $verfication->otp)) {
                            return response()->json(['status' => "The otp you entered doesn't matched"], 400);
                        }

                        $update = array();
                        $update['mobile_verified_at'] = Carbon::now()->format('Y-m-d H:i:s');
                        $action = User::where('id', $post->id)->update($update);
                        if ($action) {
                            $verfication->delete();
                            \Session::flash('success', 'Mobile number verified successfully');
                            return response()->json(['status' => 'Mobile number verified successfully'], 200);
                        } else {
                            return response()->json(['status' => 'Task failed. Please try again later'], 400);
                        }
                    } else {
                        return response()->json(['status' => 'The otp you entered is invalid or may have been expired'], 400);
                    }
                }
                break;

            default:
                return response()->json(['status' => 'Unsupported request'], 400);
                break;
        }
    }

    public function mycommission(Request $post)
    {
        $scheme = Scheme::find(\Auth::user()->scheme_id);
        if (!$scheme) {
            return redirect('dashboard/home')->with('warning', "Your commission is not set yet. Please contact our support team to activate commission for your account");
        }

        $types = [
            'order_admincharges' => 'Admin Order Charges',
        ];

        $data['providers'] = [];
        foreach ($types as $key => $value) {
            $providers = Provider::where('type', $key);

            if ($key == 'order_admincharges') {
                $providers->where('slug', \Auth::user()->business_category);
            }

            $providers = $providers->get();

            foreach ($providers as $provider) {
                $comm = Commission::where('scheme_id', $scheme->id)->where('provider_id', $provider->id)->first();
                if ($comm) {
                    $provider->commission_type = $comm->type;
                    $provider->commission_value = $comm->value;
                } else {
                    $provider->commission_type = "N/A";
                    $provider->commission_value = "N/A";
                }
            }

            $data['providers'][$key] = $providers;
        }

        $data['activemenu']['main'] = 'mycommission';
        $data['scheme'] = $scheme;
        $data['types'] = $types;
        return view('dashboard.mycommission', $data);
    }
}
