<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\DataExport;
use App\Models\Post;

class PostController extends Controller
{
    public function index() {
    	$obj = new DataExport();

    	// записываем в БД новые записи
    	$obj->process();

    	// получаем все статьи за последние 5 дней
    	$elements = Post::where('date', '>=', $obj->getStopDate())->get();
    	return view('index', compact('elements'));
    }

    public function refreshData() {
    	Post::truncate();

    	$obj = new DataExport();
    	$obj->process();
    }
}
