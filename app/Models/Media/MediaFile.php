<?php

namespace App\Models\Media;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaFile
{
    const DISK = 'media';
    const THUMBS_DISK = 'thumbs';

    const THUMBS_SIZES = [
        'small' => [
            'width' => 150,
            'height' => 93,
        ],

        'medium' => [
            'width' => 300,
            'height' => 185,
        ],

        'large' => [
            'width' => 550,
            'height' => 340,
        ],
    ];

    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public static function getFiles()
    {
        //$user = auth()->user();

        $files = Storage::disk(self::DISK)->allFiles('.');

        $fs = [];
        foreach ($files as $file) {
            $f = self::getFileByPath($file);
            //list($user_id, $time, $seq) = explode('_', $f['name']);
            //if($user_id == $user->id) {
                array_push($fs, $f);
            //}
        }

        return $fs;
    }

    public static function getFileByName($filename)
    {
        try {
            list($fn, $ext) = explode('.', $filename);
            list($user_id, $time, $seq) = explode('_', $fn);
            $dir = sprintf('%4d/%02d/%02d', date('Y', $time), date('m', $time), date('d', $time));
            $path = sprintf('%s/%s', $dir, $filename);
        } catch (\Exception $ex) {

        }

        return self::getFileByPath($path);
    }

    public static function getFileByPath($file)
    {
        try {
            $mime = self::mimeType($file);
            list($type, $subtype) = explode('/', $mime);
            if ($type === 'image') {
                $thumbs = [];
                foreach (self::THUMBS_SIZES as $size => $params) {
                    $fParts = explode('/', $file);
                    $tFilename = array_pop($fParts);
                    $tPath = implode('/', array_merge($fParts, [$size, $tFilename]));
                    $thumbs[$size] = [
                        'rel_url' => $tPath,
                        'abs_url' => Storage::disk(self::THUMBS_DISK)->url($tPath),
                    ];
                }
            }

            $fileMeta = [
                'name' => basename($file),
                'rel_url' => $file,
                'abs_url' => self::url($file),
                'size' => self::size($file),
                'mime' => $mime,
                'last_modified' => self::lastModified($file),
                'thumbs' => $type !== 'image' ? null : $thumbs,
            ];

        } catch (\Exception $ex) {
            abort(404);
        }

        return $fileMeta;
    }

    public static function putFiles($files)
    {
        $paths = [];
        $time = time();
        $dir = sprintf('%4d/%02d/%02d', date('Y', $time), date('m', $time), date('d', $time));
        $user = auth()->user();

        try {
            $seq = 0;
            foreach ($files as $file) {
                $ext = $file->getClientOriginalExtension();
                list($type, $subtype) = explode('/', $file->getMimeType());

                if (!$ext) {
                    $ext = $subtype;
                }

                $filename = sprintf('%d_%s_%03d.%s', $user->id, $time, $seq, $ext);

                $path = Storage::disk(self::DISK)->putFileAs($dir, $file, $filename);
                array_push($paths, $path);

                if ($type === 'image') {
                    self::generateThumbnail($dir, $filename, $path);
                }

                $seq++;

            }
        } catch (\Exception $ex) {

        }

        return $paths;
    }

    public static function delete($filename)
    {
        try {
            $file = self::getFileByName($filename);

            if ($file['thumbs']) {
                # delete thumbnails (if any)
                foreach ($file['thumbs'] as $size => $urls) {
                    $fileToDelete = $urls['rel_url'];
                    Storage::disk(self::THUMBS_DISK)->delete($fileToDelete);
                }
            }

            # delete the file itself
            $fileToDelete = $file['rel_url'];
            Storage::disk(self::DISK)->delete($fileToDelete);

        } catch (\Exception $ex) {
            abort(404);
        }
    }

    private static function generateThumbnail($dir, $filename, $path)
    {
        try {
            foreach (self::THUMBS_SIZES as $size => $params) {
                $tnDir = sprintf('%s/%s', $dir, $size);
                $tnPath = sprintf('%s/%s', $tnDir, $filename);

                $img = Image::make(Storage::disk(self::DISK)->get($path))->resize($params['width'], $params['height'], function ($constraint) {
                    $constraint->aspectRatio();
                });

                $sTnPath = Storage::disk(self::THUMBS_DISK)->path($tnDir);
                if (!File::isDirectory($sTnPath)) {
                    File::makeDirectory($sTnPath, 0755, true, true);
                }

                $img->save(Storage::disk(self::THUMBS_DISK)->path($tnPath));
            }
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public static function download($file, string|null $name = null, array $headers = [])
    {
        try {
            return Storage::disk(self::DISK)->download($file, $name, $headers);
        } catch (\Exception $ex) {

        }
    }

    public static function url($file)
    {
        try {
            return Storage::disk(self::DISK)->url($file);
        } catch (\Exception $ex) {

        }
    }

    public static function size($file)
    {
        try {
            return Storage::disk(self::DISK)->size($file);
        } catch (\Exception $ex) {

        }
    }

    public static function lastModified($file)
    {
        try {
            return Storage::disk(self::DISK)->lastModified($file);
        } catch (\Exception $ex) {

        }
    }

    public static function mimeType($file)
    {
        try {
            return Storage::disk(self::DISK)->mimeType($file);
        } catch (\Exception $ex) {

        }
    }

    public static function path($file)
    {
        try {
            return Storage::disk(self::DISK)->path($file);
        } catch (\Exception $ex) {

        }
    }
}