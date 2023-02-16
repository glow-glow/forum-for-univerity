<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Session;

class FileController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of File.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($filterBy = $request->input('filter')) {
            if ($filterBy == 'all') {
                Session::put('File.filter', 'all');
            } elseif ($filterBy == 'my') {
                Session::put('File.filter', 'my');
            }
        }

        if (request('show_deleted') == 1) {
            $files = File::onlyTrashed()->get();
        } else {
            $files = File::all();
        }
        $user = Auth::getUser();
        $userFilesCount = File::where('created_by_id', $user->id)->count();

        return view('admin.files.index', compact('files', 'userFilesCount'));
    }

    /**
     * Show the form for creating new File.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roleId = Auth::getUser()->role_id;
        $userFilesCount = File::where('created_by_id', Auth::getUser()->id)->count();
        if ($roleId == 2 && $userFilesCount > 5) {
            return redirect('/admin/files');
        }

        $folders = Folder::get()->pluck('name', 'id')->prepend(trans('quickadmin.qa_please_select'), '');
        $created_bies = User::get()->pluck('name', 'id')->prepend(trans('quickadmin.qa_please_select'), '');

        return view('admin.files.create', compact('folders', 'created_bies', 'userFilesCount', 'roleId'));
    }

    /**
     * Store a newly created File in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request = $this->saveFiles($request);

        $fileIds = $request->input('filename_id');

        foreach ($fileIds as $fileId) {
            File::create([
                'id' => $fileId,
                'folder_id' => $request->input('folder_id'),
                'created_by_id' => Auth::getUser()->id

            ]);
        }

        foreach ($request->input('filename_id', []) as $id) {
            $model = config('laravel-medialibrary.media_model');
            $file = $model::find($id);
            $file->model_id = $file->id;
            $file->save();
        }
        return redirect()->route('admin.files.index');

    }


    /**
     * Show the form for editing File.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Update File in storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request = $this->saveFiles($request);
        $file = File::findOrFail($id);
        $file->update($request->all());


        $media = [];
        foreach ($request->input('filename_id', []) as $id) {
            $model = config('laravel-medialibrary.media_model');
            $file = $model::find($id);
            $file->model_id = $file->id;
            $file->save();
            $media[] = $file->toArray();
        }
        $file->updateMedia($media, 'filename');

        return redirect()->route('admin.files.index');
    }


    /**
     * Display File.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Remove File from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = File::findOrFail($id);
        $file->deletePreservingMedia();

        return redirect()->route('admin.files.index');
    }

    /**
     * Delete all selected File at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if ($request->input('ids')) {
            $entries = File::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->deletePreservingMedia();
            }
        }
    }


    /**
     * Restore File from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $file = File::onlyTrashed()->findOrFail($id);
        $file->restore();

        return redirect()->route('admin.files.index');
    }

    /**
     * Permanently delete File from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        $file = File::onlyTrashed()->findOrFail($id);
        $file->forceDelete();

        return redirect()->route('admin.files.index');
    }
}
