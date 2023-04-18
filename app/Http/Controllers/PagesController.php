<?php

namespace App\Http\Controllers;

use App\Model\Order;
use Illuminate\Http\Request;

use App\Model\CmsContent;
use App\Model\TestimonialContent;

class PagesController extends Controller
{
    public function index()
    {
        return redirect('dashboard/login');

        $data['testimonials'] = TestimonialContent::where('status', 1)->get();
        return view('frontend.index', $data);
    }

    public function cmsPages($slug)
    {
        $cmscontent = CmsContent::where('slug', $slug)->first();
        if (!$cmscontent) {
            abort(404);
        }

        $data['cmscontent'] = $cmscontent;

        // dd($cmscontent->toArray());
        return view('frontend.cmspage', $data);
    }

    public function appShare(Request $request)
    {
        header("Location: intent://register#Intent;scheme=gopotuuser;package=com.syscogen.gopotuuser;end");
        die;



        if($request->has('referrer')){
            header("Location: intent://register?referrer=$request->referrer#Intent;scheme=gopotuuser;package=com.syscogen.gopotuuser;end");
            die;
        } else{
            header("Location: intent://register#Intent;scheme=gopotuuser;package=com.syscogen.gopotuuser;end");
            die;
        }

        return redirect()->route('index');
    }
}
