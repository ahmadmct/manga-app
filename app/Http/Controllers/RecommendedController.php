<?php

namespace App\Http\Controllers;

use App\Services\MangaApiService;
use Illuminate\Http\Request;

class RecommendedController extends Controller
{
    public function __construct(protected MangaApiService $api) {}

    public function index(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 24);
        $data = $this->api->getRecommendedMangaPerPage($page, $perPage);
        $manga = $data['manga_list'] ?? [];
        $perPage = (int) ($data['per_page'] ?? $perPage);

        return view('recommended.index', compact('manga', 'page', 'perPage'));
    }
}
