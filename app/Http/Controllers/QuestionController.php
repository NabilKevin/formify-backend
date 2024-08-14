<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function store(Request $request, $slug)
    {
        $form = Form::firstWhere('slug', $slug);
        if(!$form) {
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }
        if($form->creator_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden access'
            ], 403);
        }
        $rule = [
            'name' => 'required',
            'choice_type' => 'required|in:short answer,paragraph,date,multiple choice,dropdown,checkboxes'
        ];

        $only = ['multiple choice', 'dropdown', 'checkboxes'];

        if(in_array($request->choice_type, $only)) {
            $rule['choices'] = 'required';
        }

        $validate = Validator::make($request->all(), $rule);

        if($validate->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = $request->all();

        $data['form_id'] = $form->id;

        $data['choices'] = implode(',', $data['choices']);

        $question = Question::create($data);

        return response()->json([
            'message' => 'Add question success',
            'question' => $question
        ], 200);
    }

    public function destroy(Request $request, $slug, $id)
    {
        $form = Form::firstWhere('slug', $slug);
        if(!$form) {
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }
        if($form->creator_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden access'
            ], 403);
        }
        $question = Question::find($id);
        if(!$question) {
            return response()->json([
                'message' => 'Question not found'
            ], 404);
        }
        $question->delete();
        return response()->json([
            'message' => 'Remove question success'
        ], 200);
    }
}
