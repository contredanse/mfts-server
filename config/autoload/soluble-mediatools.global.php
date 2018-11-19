<?php

return [
    'soluble-mediatools' => [
        // do not set any threads: 0 means all cores
        'ffmpeg.threads'   => getenv('FFMPEG_THREADS'),
        'ffmpeg.binary'  => getenv('FFMPEG_BIN'),
        'ffprobe.binary' => getenv('FFPROBE_BIN'),
    ],
    //'ffmpeg.logger' => $logger,
];