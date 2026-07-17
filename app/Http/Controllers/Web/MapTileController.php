<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MapTileController extends Controller
{
    public function __invoke(string $server, int $zoom, int $x, int $y): BinaryFileResponse|Response
    {
        abort_unless(in_array($server, ['a', 'b', 'c'], true), 404);
        abort_unless($zoom >= 0 && $zoom <= 19, 404);

        $maxTileIndex = (2 ** $zoom) - 1;
        abort_unless($x >= 0 && $x <= $maxTileIndex && $y >= 0 && $y <= $maxTileIndex, 404);

        $path = storage_path("app/map-tiles/{$server}/{$zoom}/{$x}/{$y}.png");

        if (! File::exists($path)) {
            File::ensureDirectoryExists(dirname($path));

            try {
                $response = Http::timeout(8)
                    ->withHeaders([
                        'User-Agent' => config('app.name', 'MYSIGNAL').'/1.0 ('.config('app.url').')',
                    ])
                    ->get("https://{$server}.tile.openstreetmap.org/{$zoom}/{$x}/{$y}.png")
                    ->throw();
            } catch (RequestException) {
                abort(502, 'Impossible de charger la tuile de carte.');
            }

            File::put($path, $response->body());
        }

        return response()
            ->file($path, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=604800',
            ]);
    }
}
