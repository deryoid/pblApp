<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Normalisasi
        if ($request->has('nama_user')) {
            $request->merge(['nama_user' => preg_replace('/\s+/', ' ', trim($request->nama_user))]);
        }
        if ($request->has('email')) {
            $request->merge(['email' => strtolower(trim($request->email))]);
        }
        if ($request->has('username')) {
            $request->merge(['username' => strtolower(trim($request->username))]);
        }
        if ($request->has('no_hp')) {
            $request->merge(['no_hp' => preg_replace('/\s+/', '', trim($request->no_hp))]);
        }

        $validated = $request->validate([
            'nama_user' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => 'required|string|max:30',
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'remove_photo' => 'sometimes|boolean',
        ]);

        $user->nama_user = $validated['nama_user'];
        $user->email = $validated['email'];
        $user->no_hp = $validated['no_hp'];
        $user->username = $validated['username'];

        // Handle photo blob
        if ($request->boolean('remove_photo')) {
            $user->profile_photo = null;
            $user->profile_photo_mime = null;
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $compressedImage = $this->compressImage($file);
            $user->profile_photo = $compressedImage['data'];
            $user->profile_photo_mime = $compressedImage['mime'];
        }

        $user->save();

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    /**
     * Compress image to reduce file size while maintaining quality
     * Profile photos will be compressed to max 200KB
     */
    private function compressImage($file): array
    {
        $imageData = file_get_contents($file->getRealPath());
        $mimeType = $file->getMimeType();
        $maxSize = 200 * 1024; // 200KB

        // If already under 200KB, return as-is
        if (strlen($imageData) <= $maxSize) {
            return [
                'data' => $imageData,
                'mime' => $mimeType,
            ];
        }

        // Create image resource based on mime type
        $image = null;
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $image = imagecreatefrompng($file->getRealPath());
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file->getRealPath());
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($file->getRealPath());
                break;
        }

        if (! $image) {
            // Fallback: return original image if compression fails
            return [
                'data' => $imageData,
                'mime' => $mimeType,
            ];
        }

        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // Calculate new dimensions if needed (max 800px width/height for profile photos)
        $maxDimension = 800;
        if ($width > $maxDimension || $height > $maxDimension) {
            $ratio = min($maxDimension / $width, $maxDimension / $height);
            $newWidth = intval($width * $ratio);
            $newHeight = intval($height * $ratio);

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency for PNG
            if ($mimeType == 'image/png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $newImage;
        }

        // Start with high quality and reduce if still too large
        $quality = 85;
        $compressedData = null;

        while ($quality >= 30) { // Don't go below 30% quality
            ob_start();

            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($image, null, $quality);
                    break;
                case 'image/png':
                    // PNG quality is 0-9 (0 = no compression, 9 = max compression)
                    $pngQuality = 9 - (($quality / 100) * 9);
                    imagepng($image, null, intval($pngQuality));
                    break;
                case 'image/gif':
                    imagegif($image);
                    break;
                case 'image/webp':
                    imagewebp($image, null, $quality);
                    break;
            }

            $compressedData = ob_get_contents();
            ob_end_clean();

            if (strlen($compressedData) <= $maxSize) {
                break;
            }

            $quality -= 10; // Reduce quality by 10% and try again
        }

        imagedestroy($image);

        // If compression still fails to reduce size, use original
        if (! $compressedData || strlen($compressedData) > strlen($imageData)) {
            return [
                'data' => $imageData,
                'mime' => $mimeType,
            ];
        }

        return [
            'data' => $compressedData,
            'mime' => $mimeType,
        ];
    }
}
