<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Intervention Image driver
    |--------------------------------------------------------------------------
    |
    | "gd" — works on most XAMPP installs (enable extension=gd in php.ini).
    | "imagick" — requires PHP imagick extension + ImageMagick binaries.
    |
    | If imagick is chosen but not loaded, AvatarImageProcessor falls back to gd.
    |
    */
    'driver' => env('IMAGE_DRIVER', 'gd'),

    'avatar_jpeg_quality' => 85,

    /*
    | Path (relative to public/) for the image shown when users.avatar is empty.
    */
    'default_avatar' => env('DEFAULT_AVATAR', 'images/default-avatar.svg'),

];
