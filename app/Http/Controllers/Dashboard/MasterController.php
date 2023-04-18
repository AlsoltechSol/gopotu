<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

use App\Model\Brand;
use App\Model\Category;
use App\Model\Color;
use App\Model\ProductAttribute;
use App\Model\ProductAttributeVariant;
use App\Model\AppBanner;
use App\Model\Product;
use App\Model\ProductImage;
use App\Model\ProductMaster;
use App\Model\Scheme;

class MasterController extends Controller
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

    public function index($type, $value = "none")
    {
        $data['activemenu'] = [
            'main' => 'master',
            'sub' => $type,
        ];

        switch ($type) {
            case 'brand':
                $permission = "view_brand";
                $view = "brand";
                break;

            case 'product':
                $schemes = Scheme::all();
                $permission = "view_product_master";
                $view = "products.index";
                break;

            case 'category':
                $schemes = Scheme::all();
                $data['level'] = (object)[
                    'type' => 'level1',
                    'name' => 'Level One',
                ];

                $permission = "view_category";
                $view = "category";
                break;

            case 'level2-category':
                $schemes = Scheme::all();
                $data['activemenu']['sub'] = 'category';
                $data['parent_category'] = Category::findorfail($value);
                $data['level'] = (object)[
                    'type' => 'level2',
                    'name' => 'Level Two',
                ];

                $permission = "view_category";
                $view = "category";
                break;

                // case 'level3-category':
                //     $data['activemenu']['sub'] = 'category';
                //     $data['parent_category'] = Category::findorfail($value);
                //     $data['level'] = (object)[
                //         'type' => 'level3',
                //         'name' => 'Level Three',
                //     ];

                //     $permission = "view_category";
                //     $view = "category";
                //     break;

            case 'color':
                $permission = "view_color";
                $view = "color";
                break;

            case 'attribute':
                $permission = "view_attribute";
                $view = "attribute";
                break;

            case 'attribute-variant':
                $data['activemenu']['sub'] = 'attribute';
                $permission = "view_attribute_variant";
                $view = "attributevariant";

                $data['attribute'] = ProductAttribute::findorfail($value);
                break;

            case 'top-appbanner':
                $data['activemenu']['sub'] = 'top-appbanner';
                $permission = "view_app_banner";
                $view = "appbanner";
                $data['position'] = "top";
                break;

            case 'middle-appbanner':
                $data['activemenu']['sub'] = 'middle-appbanner';
                $permission = "view_app_banner";
                $view = "appbanner";
                $data['position'] = "middle";
                break;

            case 'footer-appbanner':
                $data['activemenu']['sub'] = 'footer-appbanner';
                $permission = "view_app_banner";
                $view = "appbanner";
                $data['position'] = "footer";
                break;

            default:
                abort(404);
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(401);
        }

        return view('dashboard.master.' . $view, $data, compact('schemes'));
    }

    public function add($type)
    {
        $data['activemenu'] = [
            'main' => 'master',
            'sub' => $type,
        ];

        switch ($type) {
            case 'product-mart':
            case 'product-restaurant':
                $data['activemenu']['sub'] = 'product';
                $permission = "add_product_master";
                $view = "products.submit";

                switch ($type) {
                    case 'product-mart':
                        $t = 'mart';
                        break;

                    case 'product-restaurant':
                        $t = 'restaurant';
                        break;
                }

                $data['categories'] = Category::with('sub_categories')->where('type', $t)->where('parent_id', NULL)->orderBy('name', 'ASC')->get();
                $data['brands'] = Brand::orderBy('name', 'ASC')->get();
                $data['type'] = $t;
                $schemes = Scheme::all();

                break;

            default:
                abort(404);
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(401);
        }

        return view('dashboard.master.' . $view, $data, compact('schemes'));
    }

    public function edit($type, $id = "none")
    {
        $data['activemenu'] = [
            'main' => 'master',
            'sub' => $type,
        ];

        switch ($type) {
            case 'product':
                $permission = "edit_product_master";
                $view = "products.submit";

                $data['product'] = ProductMaster::findorfail($id);
                $data['type'] = $data['product']->type;

                $data['categories'] = Category::where('type', $data['type'])->with('sub_categories')->where('parent_id', NULL)->orderBy('name', 'ASC')->get();
                $data['brands'] = Brand::orderBy('name', 'ASC')->get();

                break;

            default:
                abort(404);
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(401);
        }

        return view('dashboard.master.' . $view, $data);
    }

    public function submit(Request $post)
    {
        switch ($post->operation) {
            case 'brand-new':
                $rules = [
                    'name' => 'required',
                    'icon' => 'nullable|mimes:jpeg,jpg,png,gif',
                ];

                $permission = 'add_brand';
                break;

            case 'brand-edit':
                $rules = [
                    'id' => 'required|exists:brands',
                    'name' => 'required',
                    'icon' => 'nullable|mimes:jpeg,jpg,png,gif',
                ];

                $permission = 'add_brand';
                break;

            case 'brand-changestatus':
                $rules = [
                    'id' => 'required|exists:brands',
                ];

                $permission = 'edit_brand';
                break;

            case 'brand-delete':
                $rules = [
                    'id' => 'required|exists:brands',
                ];

                $permission = 'delete_brand';
                break;

            case 'category-new':
                $rules = [
                    'parent_id' => 'nullable|exists:categories,id',
                    'name' => 'required',
                    'type' => 'required|in:mart,restaurant,service',
                    'icon' => 'required|mimes:jpeg,jpg,png,gif',
                ];

                $permission = 'add_category';
                break;

            case 'category-edit':
                $rules = [
                    'id' => 'required|exists:categories',
                    'parent_id' => 'nullable|exists:categories,id',
                    'name' => 'required',
                    'type' => 'required|in:mart,restaurant,service',
                    'icon' => 'nullable|mimes:jpeg,jpg,png,gif',
                ];

                $permission = 'add_category';
                break;

            case 'category-changestatus':
            case 'category-changefeatured':
                $rules = [
                    'id' => 'required|exists:categories',
                ];

                $permission = 'edit_category';
                break;

            case 'category-delete':
                $rules = [
                    'id' => 'required|exists:categories',
                ];

                $permission = 'delete_category';
                break;

            case 'product-new':
                if ($post->brand_id && !is_numeric($post->brand_id)) {
                    $brand = Brand::where('name', $post->brand_id)->first();
                    if (!$brand) {
                        $brand = Brand::create(['name' => $post->brand_id]);
                    }

                    $post['brand_id'] = $brand->id;
                }

                $rules = [
                    'type' => 'required|in:mart,restaurant',
                    'name' => 'required',
                    'category_id' => 'required|exists:categories,id',
                    'brand_id' => 'required|exists:brands,id',
                    'description' => 'required',
                    'product_image' => 'required|mimes:jpeg,jpg,png,gif',
                    'tax_rate' => 'nullable|numeric|min:0|max:100',
                ];

                $permission = 'add_product_master';
                break;

            case 'product-edit':
                if ($post->brand_id && !is_numeric($post->brand_id)) {
                    $brand = Brand::where('name', $post->brand_id)->first();
                    if (!$brand) {
                        $brand = Brand::create(['name' => $post->brand_id]);
                    }

                    $post['brand_id'] = $brand->id;
                }

                $rules = [
                    'type' => 'required|in:mart,restaurant',
                    'id' => 'required|exists:product_masters,id',
                    'name' => 'required',
                    'category_id' => 'required|exists:categories,id',
                    'brand_id' => 'required|exists:brands,id',
                    'description' => 'required',
                    'product_image' => 'nullable|mimes:jpeg,jpg,png,gif',
                    'tax_rate' => 'nullable|numeric|min:0|max:100',
                ];

                $permission = 'edit_product_master';
                break;

            case 'product-changestatus':
                $rules = [
                    'id' => 'required|exists:product_masters',
                ];

                $permission = 'edit_product_master';
                break;

            case 'product-delete':
                $rules = [
                    'id' => 'required|exists:product_masters',
                ];

                $permission = 'delete_product_master';
                break;

            case 'product-image-upload':
                $rules = [
                    'id' => 'required|exists:product_masters',
                    'file' => 'required|mimes:jpeg,jpg,png,gif',
                ];

                $permission = 'edit_product_master';
                break;

            case 'product-image-delete':
                $rules = [
                    'id' => 'required|exists:product_images,id',
                ];

                $permission = 'edit_product_master';
                break;

            case 'color-new':
                $rules = [
                    'name' => 'required',
                    'code' => 'required|unique:colors,code',
                ];

                $permission = 'add_color';
                break;

            case 'color-edit':
                $rules = [
                    'id' => 'required|exists:colors',
                    'name' => 'required',
                    'code' => 'required|unique:colors,code,' . $post->id,
                ];

                $permission = 'add_color';
                break;

            case 'color-changestatus':
                $rules = [
                    'id' => 'required|exists:colors',
                ];

                $permission = 'edit_color';
                break;

            case 'color-delete':
                $rules = [
                    'id' => 'required|exists:colors',
                ];

                $permission = 'delete_color';
                break;

            case 'attribute-new':
                $rules = [
                    'name' => 'required|alpha',
                    'slug' => 'required|unique:product_attributes,slug',
                ];

                $permission = 'add_attribute';
                break;

            case 'attribute-edit':
                $rules = [
                    'id' => 'required|exists:product_attributes',
                    'name' => 'required|alpha',
                    'slug' => 'required|unique:product_attributes,slug,' . $post->id,
                ];

                $permission = 'add_attribute';
                break;

            case 'attribute-changestatus':
                $rules = [
                    'id' => 'required|exists:attributes',
                ];

                $permission = 'edit_attribute';
                break;

            case 'attribute-delete':
                $rules = [
                    'id' => 'required|exists:attributes',
                ];

                $permission = 'delete_attribute';
                break;

            case 'attribute-variant-new':
                $rules = [
                    'attribute_id' => 'required|exists:product_attributes,id',
                    'name' => 'required|unique:product_attribute_variants',
                ];

                $permission = 'add_attribute_variant';
                break;

            case 'attribute-variant-edit':
                $rules = [
                    'id' => 'required|exists:product_attribute_variants',
                    'attribute_id' => 'required|exists:product_attributes,id',
                    'name' => 'required|unique:product_attribute_variants,name,' . $post->id,
                ];

                $permission = 'edit_attribute_variant';
                break;

            case 'attribute-variant-changestatus':
                $rules = [
                    'id' => 'required|exists:product_attribute_variants',
                ];

                $permission = 'edit_attribute_variant';
                break;

            case 'attribute-variant-delete':
                $rules = [
                    'id' => 'required|exists:product_attribute_variants',
                ];

                $permission = 'delete_attribute_variant';
                break;

            case 'appbanner-new':
                $rules = [
                    'position' => 'required|in:top,middle,footer',
                    'file' => 'required|mimes:jpeg,jpg,png,gif',
                    'type' => 'nullable|in:mart,restaurant,service|required_if:position,==,top',
                ];

                $permission = 'add_app_banner';

                switch ($post->position) {
                    case 'top':
                        $post['image_width'] = 950;
                        $post['image_height'] = 450;
                        break;

                    case 'middle':
                    case 'footer':
                        $post['image_width'] = 1300;
                        $post['image_height'] = 350;
                        break;
                }

                break;

            case 'appbanner-delete':
                $permission = "delete_app_banner";

                $rules = [
                    'id' => 'required|exists:app_banners,id',
                ];
                break;

            default:
                return response()->json(['status' => 'Invalid Request'], 400);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => 'Permission not allowed.'], 400);
        }

        if (isset($rules)) {
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => $value[0]], 400);
                }
            }
        }

        switch ($post->operation) {
            case 'brand-edit':
                $brand = Brand::findorfail($post->id);

                if ($brand->icon != null) {
                    $deletefile = 'uploads/brand/' . $brand->icon;
                }

            case 'brand-new':
                $document = array();
                $document['name'] = $post->name;

                if ($post->file('icon')) {
                    $file = $post->file('icon');
                    $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                    if (\Image::make($file->getRealPath())->save('uploads/brand/' . $filename)) {
                        $document['icon'] = $filename;

                        if (isset($deletefile)) {
                            \File::delete($deletefile);
                        }
                    } else {
                        return response()->json(['status' => 'File cannot be saved to server.'], 400);
                    }
                }

                $action = Brand::updateorcreate(['id' => $post->id], $document);
                break;

            case 'brand-changestatus':
                $brand = Brand::findorfail($post->id);
                if ($brand->status == '1') {
                    $brand->status = '0';
                } else {
                    $brand->status = '1';
                }

                $action = $brand->save();
                break;

            case 'brand-delete':
                $brand = Brand::findorfail($post->id);
                $action = $brand->delete();
                break;

            case 'category-edit':
                $category = Category::findorfail($post->id);

                if ($category->icon != null) {
                    $deletefile = 'uploads/category/' . $category->icon;
                }
            case 'category-new':
                $document = array();
                $document['parent_id'] = $post->parent_id;
                $document['name'] = $post->name;
                $document['type'] = $post->type;
                $document['scheme_id'] = $post->scheme_id;

                if ($post->file('icon')) {
                    $file = $post->file('icon');
                    $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                    if (\Image::make($file->getRealPath())->resize(250, 150)->save('uploads/category/' . $filename)) {
                        $document['icon'] = $filename;

                        if (isset($deletefile)) {
                            \File::delete($deletefile);
                        }
                    } else {
                        return response()->json(['status' => 'File cannot be saved to server.'], 400);
                    }
                }

                $action = Category::updateorcreate(['id' => $post->id], $document);
                break;

            case 'category-changestatus':
                $category = Category::findorfail($post->id);
                if ($category->status == '1') {
                    $category->status = '0';
                } else {
                    $category->status = '1';
                }

                $action = $category->save();
                break;

            case 'category-changefeatured':
                $category = Category::findorfail($post->id);
                if ($category->is_featured == '1') {
                    $category->is_featured = '0';
                } else {
                    $category->is_featured = '1';
                }

                $action = $category->save();
                break;

            case 'category-delete':
                $category = Category::findorfail($post->id);
                $action = $category->delete();
                break;

            case 'product-edit':
                $product = ProductMaster::findorfail($post->id);

                if ($product->image != null) {
                    $deletefile = 'uploads/product/' . $product->image;
                }
            case 'product-new':
                $document = array();
                $document['type'] = $post->type;
                $document['name'] = $post->name;
                $document['category_id'] = $post->category_id;
                $document['brand_id'] = $post->brand_id;
                $document['description'] = $post->description;
                $document['tax_rate'] = $post->tax_rate ?? 0;
                $document['scheme_id'] = $post->scheme_id;

                if ($post->file('product_image')) {
                    $file = $post->file('product_image');
                    $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                    if (\Image::make($file->getRealPath())->resize(500, 500)->save('uploads/product/' . $filename)) {
                        $document['image'] = $filename;

                        if (isset($deletefile)) {
                            \File::delete($deletefile);
                        }
                    } else {
                        return response()->json(['status' => 'File cannot be saved to server.'], 400);
                    }
                }

                $action = ProductMaster::updateorcreate(['id' => $post->id], $document);
                break;

            case 'product-changestatus':
                $product = ProductMaster::findorfail($post->id);
                if ($product->status == '1') {
                    $product->status = '0';
                } else {
                    $product->status = '1';
                }

                $action = $product->save();
                break;

            case 'product-delete':
                $product = ProductMaster::findorfail($post->id);
                $action = $product->delete();
                break;

            case 'product-image-upload':
                if ($post->file('file')) {
                    // $file = $post->file('file');
                    // $filename = $file->getClientOriginalName();

                    // if (\Image::make($file->getRealPath())->save('uploads/imgcompressor/' . $filename, 60)) {
                    //     $action = true;
                    // } else {
                    //     return response()->json(['status' => 'File cannot be saved to server.'], 400);
                    // }


                    $file = $post->file('file');
                    $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                    if (\Image::make($file->getRealPath())->resize(500, 500)->save('uploads/product/gallery/' . $filename)) {
                        $document = array();
                        $document['master_id'] = $post->id;
                        $document['image'] = $filename;

                        $action = ProductImage::create($document);
                    } else {
                        return response()->json(['status' => 'File cannot be saved to server.'], 400);
                    }
                } else {
                    $action = false;
                }
                break;

            case 'product-image-delete':
                $productimage = ProductImage::findorfail($post->id);
                if ($productimage->image) {
                    $deletefile = 'uploads/product/gallery/' . $productimage->image;

                    if (isset($deletefile)) {
                        \File::delete($deletefile);
                    }
                }

                $action = $productimage->delete();
                break;

            case 'color-edit':
            case 'color-new':
                $document = array();
                $document['code'] = $post->code;
                $document['name'] = $post->name;

                $action = Color::updateorcreate(['id' => $post->id], $document);
                break;

            case 'color-changestatus':
                $color = Color::findorfail($post->id);
                if ($color->status == '1') {
                    $color->status = '0';
                } else {
                    $color->status = '1';
                }

                $action = $color->save();
                break;

            case 'color-delete':
                $color = Color::findorfail($post->id);
                $action = $color->delete();
                break;

            case 'attribute-edit':
            case 'attribute-new':
                $document = array();
                $document['slug'] = $post->slug;
                $document['name'] = $post->name;

                $action = ProductAttribute::updateorcreate(['id' => $post->id], $document);
                break;

            case 'attribute-changestatus':
                $attribute = ProductAttribute::findorfail($post->id);
                if ($attribute->status == '1') {
                    $attribute->status = '0';
                } else {
                    $attribute->status = '1';
                }

                $action = $attribute->save();
                break;

            case 'attribute-delete':
                $attribute = ProductAttribute::findorfail($post->id);
                $action = $attribute->delete();
                break;

            case 'attribute-variant-edit':
            case 'attribute-variant-new':
                $document = array();
                $document['attribute_id'] = $post->attribute_id;
                $document['name'] = $post->name;

                $action = ProductAttributeVariant::updateorcreate(['id' => $post->id], $document);
                break;

            case 'attribute-variant-changestatus':
                $attribute_variant = ProductAttributeVariant::findorfail($post->id);
                if ($attribute_variant->status == '1') {
                    $attribute_variant->status = '0';
                } else {
                    $attribute_variant->status = '1';
                }

                $action = $attribute_variant->save();
                break;

            case 'attribute-variant-delete':
                $attribute_variant = ProductAttributeVariant::findorfail($post->id);
                $action = $attribute_variant->delete();
                break;

            case 'appbanner-new':
                if ($post->file('file')) {
                    $file = $post->file('file');
                    $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                    if (\Image::make($file->getRealPath())->resize($post->image_width, $post->image_height)->save('uploads/appbanner/' . $filename)) {
                        $document = array();
                        $document['position'] = $post->position;
                        $document['type'] = $post->type;
                        $document['image'] = $filename;

                        $action = AppBanner::create($document);
                    } else {
                        return response()->json(['status' => 'File cannot be saved to server.'], 400);
                    }
                } else {
                    $action = false;
                }
                break;

            case 'appbanner-delete':
                $bannerimage = AppBanner::findorfail($post->id);
                if ($bannerimage->image) {
                    $deletefile = 'uploads/product/gallery/' . $bannerimage->image;

                    if (isset($deletefile)) {
                        \File::delete($deletefile);
                    }
                }

                $action = $bannerimage->delete();
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task successfully completed.'], 200);
        } else {
            return response()->json(['status' => 'Task cannot be completed.'], 400);
        }
    }

    public function ajaxMethods(Request $post)
    {
        switch ($post->type) {
            case 'fetch-appbanners':
                $images = AppBanner::where('position', $post->position)->get();
                return response()->json(['images' => $images], 200);
                break;

            case 'fetch-gallery':
                $images = ProductImage::where('master_id', $post->product_id)->get();
                return response()->json(['images' => $images], 200);
                break;

            default:
                return response()->json(['status' => 'Unsupported Request.'], 400);
                break;
        }
    }
}
