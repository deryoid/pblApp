<?php

namespace App\Http\Controllers\Evaluator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EvaluatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('evaluator.index');
    }

}
