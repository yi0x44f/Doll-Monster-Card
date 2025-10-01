<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LotteryController extends Controller
{
    public function getVideo()
    {
        $path = storage_path('app/private/doll-animation.mp4');

        if (!file_exists($path)) {
            abort(404);
        }

        $stream = function () use ($path) {
            $stream = fopen($path, 'r');
            fpassthru($stream);
            fclose($stream);
        };

        return response()->stream($stream, 200, [
            'Content-Type' => 'video/mp4',
            'Content-Length' => filesize($path),
            'Accept-Ranges' => 'bytes',
        ]);
    }

    public function getAudio()
    {
        $path = storage_path('app/private/duel-bgm.mp3');

        if (!file_exists($path)) {
            abort(404);
        }

        $stream = function () use ($path) {
            $stream = fopen($path, 'r');
            fpassthru($stream);
            fclose($stream);
        };

        return response()->stream($stream, 200, [
            'Content-Type' => 'audio/mpeg',
            'Content-Length' => filesize($path),
            'Accept-Ranges' => 'bytes',
        ]);
    }
}
