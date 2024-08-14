<?php

namespace App\Http\Controllers;

use App\Models\AllowedDomain;
use App\Models\Answer;
use App\Models\Form;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ResponseController extends Controller
{
    public function store(Request $request, $slug)
    {
        $form = Form::with('questions')->firstWhere('slug', $slug);
        if(!$form) {
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }

        $questions = Question::where('form_id', $form->id)->get();

        $rule = [
            'answers' => 'array'
        ];


        foreach($questions as $i => $question) {
            if($question->is_required === 1) {
                $rule["answers.$i"] = 'required';
            }
        }

        $validate = Validator::make($request->all(), $rule);

        if($validate->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors()
            ], 422);
        }

        $allowedDomain = AllowedDomain::where('form_id', $form->id)->get();

        $domain = explode('@', $request->user()->email);

        if(!$allowedDomain->firstWhere('domain', $domain[1])) {
            return response()->json([
                'message' => 'Forbidden access'
            ], 403);
        }

        $response = Response::where('form_id', $form->id)->firstWhere('user_id', $request->user()->id);

        if($response) {
            return response()->json([
                'message' => 'You cannot submit form twice'
            ], 422);
        }

        $data = $request->all();

        $response = Response::create([
            'form_id' => $form->id,
            'user_id' => $request->user()->id,
            'date' => now()
        ]);

        foreach ($data['answers'] as $i => $answer) {
            Answer::create([
                'response_id' => $response->id,
                'question_id' => $answer['question_id'],
                'value' => $answer['value']
            ]);
        }

        return response()->json([
            'message' => 'Submit response success'
        ], 200);
    }

    public function index($slug)
    {
        $form = Form::firstWhere('slug', $slug);

        if(!$form) {
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }

        if($form->creator_id !== Auth::id()) {
            return response()->json([
                'message' => 'Forbidden access'
            ], 422);
        }

        $responses = Response::with(['user', 'answers.question'])->where('form_id', $form->id)->get();

        return response()->json([
            'message' => 'Get responses success',
            'responses' => $responses->map(function($r) {
                    return [
                        'date' => $r->date,
                        'user' => $r->user,
                        'answers' => array_merge(...$r->answers->map(function($a) {
                            return [
                                $a->question->name => $a->value
                            ];
                        })),
                    ];
            })
        ], 200);
    }
}
