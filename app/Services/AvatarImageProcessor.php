<?php

namespace App\Services;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Interfaces\EncodedImageInterface;

final class AvatarImageProcessor
{
    private const AVATAR_MAX = 400;

    public function __construct(
        private readonly FilesystemFactory $filesystems,
        private readonly ConfigRepository $config,
    ) {}

    /**
     * Resize the upload, encode as JPEG, store on the public disk, return stored path.
     * If GD and Imagick are both unavailable, stores the original bytes without resizing (no crash).
     */
    public function storeUploadedAvatar(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        if ($path === false) {
            throw new \InvalidArgumentException('Uploaded file is not available on disk.');
        }

        if (! $this->hasImageDriver()) {
            return $this->storeRawAvatar($file);
        }

        $manager = new ImageManager($this->driverClass());

        $image = $manager->read($path);
        $image->scaleDown(self::AVATAR_MAX, self::AVATAR_MAX);

        $encoded = $image->toJpeg(
            quality: (int) $this->config->get('image.avatar_jpeg_quality', 85),
        );

        $relativePath = 'avatars/'.Str::uuid()->toString().'.jpg';
        $this->writeToPublicDisk($relativePath, $encoded);

        return $relativePath;
    }

    /**
     * Remove a file from the public disk (path as stored in users.avatar, e.g. avatars/uuid.jpg).
     */
    public function deletePublicAvatar(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $this->filesystems->disk('public')->delete($relativePath);
    }

    private function writeToPublicDisk(string $relativePath, EncodedImageInterface $encoded): void
    {
        $this->filesystems->disk('public')->put($relativePath, $encoded->toString());
    }

    private function hasImageDriver(): bool
    {
        return extension_loaded('gd') || extension_loaded('imagick');
    }

    /**
     * Last resort: no GD/Imagick — persist upload without Intervention (enable extension=gd in php.ini for resize).
     */
    private function storeRawAvatar(UploadedFile $file): string
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $ext = 'jpg';
        }

        $relativePath = 'avatars/'.Str::uuid()->toString().'.'.$ext;
        $this->filesystems->disk('public')->put($relativePath, $file->getContent());

        return $relativePath;
    }

    /**
     * Default: GD. Use Imagick only when image.driver=imagick and ext-imagick is loaded; otherwise GD if available.
     *
     * @return class-string<DriverInterface>
     */
    private function driverClass(): string
    {
        $preferred = (string) $this->config->get('image.driver', 'gd');

        if ($preferred === 'imagick' && extension_loaded('imagick')) {
            return ImagickDriver::class;
        }

        if (extension_loaded('gd')) {
            return GdDriver::class;
        }

        return ImagickDriver::class;
    }
}
