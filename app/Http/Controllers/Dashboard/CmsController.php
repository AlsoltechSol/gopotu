<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\FaqContent;
use App\Model\CmsContent;
use App\Model\OrderCancellationReason;
use App\Model\SociallinkMaster;
use App\Model\TestimonialContent;
use App\Model\VideoContent;
use Carbon\Carbon;

class CmsController extends Controller
{
    public function index($type)
    {
        $data['activemenu']['main'] = 'cms';

        switch ($type) {
            case 'content':
                $view = 'content';
                $data['activemenu']['sub'] = 'content';
                $permission = 'view_cms';
                break;

            case 'testimonial':
                $view = 'testimonial';
                $data['activemenu']['sub'] = 'testimonial';
                $permission = 'view_testimonial';
                break;

            case 'sociallink':
                $view = 'sociallink';
                $data['activemenu']['sub'] = 'sociallink';
                $permission = 'view_sociallink';
                break;

            case 'cancellationreason-user':
            case 'cancellationreason-branch':
            case 'cancellationreason-deliveryboy':
                $view = 'cancellationreason';
                $data['activemenu']['sub'] = $type;
                $permission = 'view_cancellation_reason';

                if ($type == "cancellationreason-user") {
                    $data['usertype'] = "user";
                } else if ($type == "cancellationreason-branch") {
                    $data['usertype'] = "branch";
                } else if ($type == "cancellationreason-deliveryboy") {
                    $data['usertype'] = "deliveryboy";
                } else {
                    abort(404);
                }
                break;

            default:
                abort(404);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(401);
        }

        return view('dashboard.cms.' . $view, $data);
    }

    public function edit($type, $id)
    {
        $data['activemenu']['main'] = 'cms';

        switch ($type) {
            case 'content':
                $view = 'contentedit';
                $data['activemenu']['sub'] = 'content';
                $permission = 'update_cms';

                $data['content'] = CmsContent::findorfail($id);
                break;

            default:
                abort(404);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(401);
        }

        return view('dashboard.cms.' . $view, $data);
    }

    public function submitcms(Request $post)
    {
        switch ($post->operation) {
            case 'contentedit':
                $rules = array(
                    'id' => 'required',
                    'page_title' => 'required',
                );

                $permission = 'update_cms';
                break;

            case 'testimonialnew':
                $rules = array(
                    'name' => 'required',
                    'content' => 'required',
                    // 'designation' => 'required',
                    // 'avatar_image' => 'required|image',
                );

                $permission = 'add_testimonial';
                break;

            case 'testimonialedit':
                $rules = array(
                    'name' => 'required',
                    'content' => 'required',
                    // 'designation' => 'required',
                    'id' => 'required',
                );

                $permission = 'edit_testimonial';
                break;

            case 'testimonialchangeaction':
                $rules = array(
                    'id' => 'required',
                );

                $permission = 'edit_testimonial';
                break;

            case 'testimonialdelete':
                $rules = array(
                    'id' => 'required',
                );

                $permission = 'delete_testimonial';
                break;

            case 'sociallinknew':
                $rules = array(
                    'tittle' => 'required',
                    'link' => 'required',
                    'icon_image' => 'required|mimes:jpeg,jpg,png,gif,webp',
                );

                $permission = 'add_sociallink';
                break;

            case 'sociallinkedit':
                $rules = array(
                    'tittle' => 'required',
                    'link' => 'required',
                    'icon_image' => 'nullable|mimes:jpeg,jpg,png,gif,webp',
                    'id' => 'required',
                );

                $permission = 'edit_sociallink';
                break;

            case 'sociallinkchangeaction':
                $rules = array(
                    'id' => 'required',
                );

                $permission = 'edit_sociallink';
                break;

            case 'sociallinkdelete':
                $rules = array(
                    'id' => 'required',
                );

                $permission = 'delete_sociallink';
                break;

            case 'cancellationreasonnew':
                $rules = array(
                    'description' => 'required',
                );

                $permission = 'add_cancellation_reason';
                break;

            case 'cancellationreasonedit':
                $rules = array(
                    'description' => 'required',
                    'id' => 'required|exists:order_cancellation_reasons,id',
                );

                $permission = 'edit_cancellation_reason';
                break;

            case 'cancellationreasonchangeaction':
                $rules = array(
                    'id' => 'required|exists:order_cancellation_reasons,id',
                );

                $permission = 'edit_cancellation_reason';
                break;

            case 'cancellationreasondelete':
                $rules = array(
                    'id' => 'required|exists:order_cancellation_reasons,id',
                );

                $permission = 'delete_cancellation_reason';
                break;

            default:
                return response()->json(['status' => 'Invalid Request'], 400);
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

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => 'Permission not Allowed'], 401);
        }

        switch ($post->operation) {
            case 'contentedit':
                $action = CmsContent::updateorcreate(['id' => $post->id], $post->all());
                break;

            case 'testimonialchangeaction':
                $content = TestimonialContent::findorfail($post->id);
                if ($content->status) {
                    $post['status'] = '0';
                } else {
                    $post['status'] = '1';
                }
            case 'testimonialedit':
                $content = TestimonialContent::findorfail($post->id);
                if ($content->image != NULL) {
                    $deletefile = 'uploads/testimonials/' . $content->image;
                }
            case 'testimonialnew':
                if ($post->file('avatar_image')) {
                    $file = $post->file('avatar_image');
                    $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                    //Resizing and compressing the image
                    if (\Image::make($file->getRealPath())->resize(100, 100)->save('uploads/testimonials/' . $filename, 60)) {
                        $post['image'] = $filename;

                        if (isset($deletefile)) {
                            \File::delete($deletefile);
                        }
                    } else {
                        return response()->json(['status' => 'File cannot be saved to server.'], 400);
                    }
                }

                $action = TestimonialContent::updateorcreate(['id' => $post->id], $post->all());
                break;

            case 'testimonialdelete':
                $content = TestimonialContent::findorfail($post->id);
                $action = $content->delete();
                break;

            case 'sociallinkchangeaction':
                $content = SociallinkMaster::findorfail($post->id);
                if ($content->status) {
                    $post['status'] = '0';
                } else {
                    $post['status'] = '1';
                }
            case 'sociallinkedit':
                $content = SociallinkMaster::findorfail($post->id);
                if ($content->icon != NULL) {
                    $deletefile = 'uploads/sociallinks/' . $content->icon;
                }
            case 'sociallinknew':
                if ($post->file('icon_image')) {
                    $file = $post->file('icon_image');
                    $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                    //Resizing and compressing the image
                    if (\Image::make($file->getRealPath())->resize(100, 100)->save('uploads/sociallinks/' . $filename, 60)) {
                        $post['icon'] = $filename;

                        if (isset($deletefile)) {
                            \File::delete($deletefile);
                        }
                    } else {
                        return response()->json(['status' => 'File cannot be saved to server.'], 400);
                    }
                }

                $action = SociallinkMaster::updateorcreate(['id' => $post->id], $post->all());
                break;

            case 'sociallinkdelete':
                $content = SociallinkMaster::findorfail($post->id);
                $action = $content->delete();
                break;

            case 'cancellationreasonchangeaction':
                $content = OrderCancellationReason::findorfail($post->id);
                if ($content->status) {
                    $post['status'] = '0';
                } else {
                    $post['status'] = '1';
                }
            case 'cancellationreasonedit':
            case 'cancellationreasonnew':
                $action = OrderCancellationReason::updateorcreate(['id' => $post->id], $post->all());
                break;

            case 'cancellationreasondelete':
                $content = OrderCancellationReason::findorfail($post->id);
                $action = $content->delete();
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task completed successfully'], 200);
        } else {
            return response()->json(['status' => 'Task failed. Please try again'], 400);
        }
    }
}
