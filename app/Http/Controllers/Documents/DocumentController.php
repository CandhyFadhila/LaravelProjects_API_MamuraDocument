<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\CreateMultipleDocumentsRequest;
use App\Http\Requests\Documents\GetDocumentRequest;
use App\Http\Resources\Templates\WithDataResource;
use App\Http\Resources\Templates\WithoutDataResource;
use App\Models\Document;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function getFile(GetDocumentRequest $request)
    {
        $validation = $request->validated();

        try {
            $document = Document::where('id', $validation['file_id'])->first();
            if (!$document || !Storage::exists("public/{$document->path}")) {
                return response()->json(new WithoutDataResource(
                    Response::HTTP_NOT_FOUND,
                    'Dokumen Tidak Ditemukan',
                    'File dengan ID yang diberikan tidak tersedia.'
                ), Response::HTTP_NOT_FOUND);
            }

            return response()->file(storage_path("app/public/{$document->path}"));
        } catch (\Exception $e) {
            Log::error('Error fetching document: ' . $e->getMessage());
            return response()->json(new WithoutDataResource(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Server Error',
                'Terjadi kesalahan saat mengambil dokumen.'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadMultipleFiles(CreateMultipleDocumentsRequest $request)
    {
        $validation = $request->validated();

        try {
            $uploadedFiles = [];

            foreach ($request->file('files') as $file) {
                $filename = Str::random(25);
                $mimeType = $file->getClientMimeType();
                $size = $this->getFileSize($file);
                $fileId = (string) Str::uuid();

                $file->move('storage/file', $filename);
                // Storage::disk('public')->putFileAs('file', $file, $filename);

                $document = Document::create([
                    'id' => $fileId,
                    'user_id' => Auth::id(),
                    'filename' => $filename,
                    'path' => "file/{$filename}",
                    'mime_type' => $mimeType,
                    'size' => $size,
                ]);

                Log::info("Dokumen berhasil diunggah pada path: {$document->path}");

                // Simpan hasil upload ke array
                $uploadedFiles[] = [
                    'server_file_id' => $document->id,
                    'server_file_path' => $document->path,
                    'server_file_name' => $document->filename,
                    'server_file_url'       => url("storage/{$document->path}"),
                    'server_file_mime_type' => $document->mime_type,
                    'server_file_size' => $this->formatFileSize($document->size),
                ];
            }

            return response()->json(new WithDataResource(
                Response::HTTP_CREATED,
                'File berhasil diunggah.',
                'Semua file berhasil diunggah ke server.',
                $uploadedFiles
            ), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Error uploading multiple documents: ' . $e->getMessage());
            return response()->json(new WithoutDataResource(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Server Error',
                'Terjadi kesalahan saat mengunggah dokumen. ' . $e->getMessage()
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteFile(GetDocumentRequest $request)
    {
        $request->validated();

        try {
            $deleted = [];

            foreach ($request->file_id as $fileId) {
                $document = Document::find($fileId);

                $relativePath = $document->path;
                Log::info("Akan hapus dari disk 'public': {$relativePath}");

                // if ($document) {
                //     $filePath = public_path("storage/{$document->path}");
                //     Log::info("Dokumen yang akan dihapus terletak di: {$filePath}");

                //     if (file_exists($filePath)) {
                //         unlink($filePath);
                //         $document->delete();
                //         $deleted[] = $fileId;
                //         Log::success("Dokumen berhasil dihapus: {$fileId}");
                //     } else {
                //         Log::warning("Dokumen tidak ditemukan atau tidak ada di storage: {$fileId}");
                //     }
                // }

                if (Storage::disk('public')->exists($relativePath)) {
                    $ok = Storage::disk('public')->delete($relativePath);
                    if ($ok) {
                        $document->delete();
                        $deleted[] = $fileId;
                        Log::info("Dokumen berhasil dihapus: {$fileId}");
                    } else {
                        Log::warning("Gagal menghapus file di storage untuk ID: {$fileId}");
                    }
                } else {
                    Log::warning("File tidak ditemukan di storage untuk ID: {$fileId} (path: {$relativePath})");
                    $document->delete();
                }
            }

            return response()->json(new WithDataResource(
                Response::HTTP_OK,
                'Dokumen Berhasil Dihapus',
                'Dokumen yang dipilih berhasil dihapus.',
                $deleted
            ), Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Error deleting document: ' . $e->getMessage());
            return response()->json(new WithoutDataResource(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Server Error',
                'Terjadi kesalahan saat menghapus dokumen.'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getFileSize($file)
    {
        return $file->getSize(); // Mengembalikan ukuran dalam byte
    }

    private function formatFileSize($bytes)
    {
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor(log($bytes, 1024));

        return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $sizes[$factor];
    }
}
