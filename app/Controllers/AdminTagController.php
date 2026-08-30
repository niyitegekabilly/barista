<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Tag;
use App\Services\TagService;

class AdminTagController extends Controller {

    public function index(Request $request): void {
        $tags = Tag::allWithCourseCount();
        $popular = Tag::getPopular(5);
        $unused = Tag::getUnused();

        $this->render('admin/categories/tags', [
            'pageTitle' => 'Course Tags & Taxonomy',
            'tags' => $tags,
            'popular' => $popular,
            'unused' => $unused
        ], 'dashboard');
    }

    public function store(Request $request): void {
        $name = $request->input('name');
        $desc = $request->input('description');

        $res = TagService::createTag($name, $desc);
        $this->flash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('admin/tags');
    }

    public function update(Request $request, int $id): void {
        $name = $request->input('name');
        $desc = $request->input('description');

        $res = TagService::updateTag($id, $name, $desc);
        $this->flash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('admin/tags');
    }

    public function delete(Request $request, int $id): void {
        $res = TagService::deleteTag($id);
        $this->flash('success', $res['message']);
        $this->redirect('admin/tags');
    }

    public function apiSearch(Request $request): void {
        $query = $request->input('q', '');
        $tags = TagService::searchTags($query);
        Response::json(['tags' => $tags]);
    }
}
