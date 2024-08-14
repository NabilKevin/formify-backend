<?php

namespace App\Http\Controllers;

use App\Models\AllowedDomain;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:forms,slug|regex:/^[A-Za-z-. ]+$/u',
            'allowed_domains' => 'array'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = $request->all();
        $data['creator_id'] = $request->user()->id;

        $form = Form::create($data);

        foreach($data['allowed_domains'] as $domain){
            if($domain !== '') {
                AllowedDomain::create([
                    'domain' => $domain,
                    'form_id' => $form->id
                ]);
            }
        }

        return response()->json([
            'message' => 'Create form success',
            'form' => $form->only(['name', 'slug', 'description', 'limit_one_response', 'creator_id', 'id'])
        ],  200);
    }
    public function index()
    {
        $forms = Form::where('creator_id', Auth::id())->get();

        return response()->json([
            'forms' => $forms
        ], 200);
    }
    public function show(Request $request, $slug)
    {
        $form = Form::with(['allowed_domains', 'questions'])->firstWhere('slug', $slug);
        if(!$form) {
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }
        $domain = explode('@', Auth::user()->email)[1];
        if(!$form->allowed_domains->where('domain', $domain)) {
            return response()->json([
                'message' => 'Forbidden access'
            ], 403);
        }

        return response()->json([
            'message' => 'Get form success',
            'form' => [
                'id' => $form->id,
                'name' => $form->name,
                'slug' => $form->slug,
                'description' => $form->description,
                'limit_one_response' => $form->limit_one_response,
                'creator_id' => $form->creator_id,
                'allowed_domains' => $form->allowed_domains->map(function($f) {
                    return $f->domain;
                }),
                'questions' => $form->questions,
            ]
        ], 200);
    }

}
