<?php

namespace App\Http\Controllers;

use App\Services\Media\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index($id, $conversion = '')
    {
        $media = Media::where('name', $id)->first();
        return $media->read($conversion);
    }
}
