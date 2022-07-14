<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\File;
use Storage;
use Illuminate\Support\Facades\Log;

class FilesController extends Controller
{
    public function upload_method(Request $request) {

        if( !$request->hasFile('selected_files') ) {
            Log::error('Error while upload file.', ['errors' => 'No file seleted']);
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 400);
        }

        if( !$request->user()->hasPermissionTo('create articles') ) {
            Log::error('User does not have permission to upload file.', ['user_id' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'user does not have permission to upload file'
            ], 403);
        }

        //dd($request->file('selected_files'));
        $selected_files = $request->file('selected_files');
        $uploaded_file_details = [];
        $dynamic_path = "public/uploads/" . date('Y') . "/" . date('m');
        if( !is_array($selected_files) ){
            $path = $selected_files->store($dynamic_path);
            array_push($uploaded_file_details, [
                'title' => $selected_files->getClientOriginalName(),
                'type' => $selected_files->getClientOriginalExtension(),
                'size' => $selected_files->getSize(),
                'path' => $path,
                'user_id' => $request->user()->id
            ]);
        }else{
            foreach ($selected_files as $file) {
                $path = $file->store($dynamic_path);
                array_push($uploaded_file_details, [
                    'title' => $file->getClientOriginalName(),
                    'type' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'path' => $path,
                    'user_id' => $request->user()->id
                ]);
            }
        }
        
        if( count($uploaded_file_details) > 0 ){
            foreach ($uploaded_file_details as $each_file) {
                File::create($each_file);
            }
            Log::info(count($uploaded_file_details) . " file(s) uploaded");
            return response()->json([
                'success' => true,
                'message' => count($uploaded_file_details) . " file(s) uploaded"
            ], 200);
        }else{
            Log::error('Error while upload file.', ['errors' => 'something went wrong while file uploaded.']);
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 400);
        }
    }

    public function delete_method(Request $request, $id) {
        
        $file = File::find($id); 

        if($file == null) {
            Log::error('Error while delete file.', ['errors' => 'Requested file is invalid for deletion.']);
            return response()->json([
                'success' => false,
                'message' => 'Invalid file requested'
            ], 400);
        }

        if($file->user_id === $request->user()->id && $request->user()->hasPermissionTo('delete articles') ) {
            if(Storage::exists($file->path)) // if file exists in storage path
                Storage::delete($file->path); // remove the file from storage path
            $file->delete(); // delete file from database
            Log::info("file deleted");
            return response()->json([
                'success' => true,
                'message' => "File deleted"
            ], 200);
        }
        Log::error('User does not have permission to delete file.', ['user_id' => $request->user()->id]);
        return response()->json([
            'success' => false,
            'message' => 'User is not eligible for delete file'
        ], 403);
    }
}
