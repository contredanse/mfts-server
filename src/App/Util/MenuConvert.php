<?php declare(strict_types=1);

namespace App\Util;

use Cocur\Slugify\Slugify;
use Soluble\MediaTools\Video\Filter\Hqdn3dVideoFilter;
use Soluble\MediaTools\Video\Filter\NlmeansVideoFilter;
use Soluble\MediaTools\Video\Filter\VideoFilterChain;
use Soluble\MediaTools\Video\Filter\YadifVideoFilter;
use Soluble\MediaTools\Video\VideoAnalyzer;
use Soluble\MediaTools\Video\VideoAnalyzerInterface;
use Soluble\MediaTools\Video\VideoConverter;
use Soluble\MediaTools\Video\VideoConverterInterface;
use Soluble\MediaTools\Video\VideoConvertParams;
use Soluble\MediaTools\Video\VideoInfoReader;
use Soluble\MediaTools\Video\VideoInfoReaderInterface;
use Soluble\MediaTools\Video\VideoThumbGenerator;
use Soluble\MediaTools\Video\VideoThumbGeneratorInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MenuConvert
{

    /**
     * @var string[]
     */
    protected $uniqueVideos;

    /**
     * @var string[]
     */
    protected $uniqueAudios;

    /**
     * @var string[]
     */
    protected $audioTracksToConvert;

    /**
     * @var string[]
     */
    protected $zoomVideos;

    protected $assets_path;

    protected $output_path;

    protected $ffmpeg;
    protected $ffprobe;
    protected $ffbinaries_path;

    /**
     * @var VideoInfoReader
     */
    protected $infoReader;


    /**
     * @var VideoThumbGenerator
     */
    protected $thumbGenerator;

    /**
     * @var VideoConverter
     */
    protected $converter;

    /**
     * @var VideoAnalyzer
     */
    protected $analyzer;


    function __construct(
        string $assets_path,
        string $output_path,
        VideoInfoReaderInterface $videoProbe,
        VideoThumbGeneratorInterface $videoThumb,
        VideoConverterInterface $videoTranscode,
        VideoAnalyzerInterface $videoAnalyzer,
        string $ffbinaries_path = ''
    ) {
        $this->infoReader = $videoProbe;
        $this->thumbGenerator = $videoThumb;
        $this->converter = $videoTranscode;
        $this->analyzer = $videoAnalyzer;

        $this->audioTracksToConvert  = [];

        if (!is_dir($assets_path)) {
            throw new \Exception("Assets path $assets_path does not exists");
        }

        if (!is_dir($output_path)) {
            throw new \Exception("Output path $output_path does not exists");
        }


        $this->ffbinaries_path = $ffbinaries_path;

        $bin_path = $this->ffbinaries_path;

        $this->ffmpeg = $bin_path . 'ffmpeg';
        $this->ffprobe = $bin_path . 'ffprobe';

        $this->assets_path = $assets_path;
        $this->output_path = $output_path;
        $this->uniqueVideos  = [];
        $this->uniqueAudios  = [];
        $this->zoomVideos  = [];
    }


    function convert(string $inputFile)
    {

        $pages = $this->parsePages($inputFile);

        $menu = $this->parseMenu($inputFile);

        $videos = $this->parseVideos($inputFile);
        $audios = $this->parseAudios($inputFile);
        $data = [
            'menu' => $menu,
            'pages' => $pages,
            'videos' => $videos,
            'audios' => $audios,
        ];

        if (true) {
            $frontEndDataPath = '/web/www/mfts/src/data/json';
            file_put_contents($frontEndDataPath . '/data-menu.json', json_encode($menu, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
            file_put_contents($frontEndDataPath . '/data-pages.json', json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
            file_put_contents($frontEndDataPath . '/data-videos.json', json_encode($videos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
        }

        file_put_contents('data/data-menu.json', json_encode($menu, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
        file_put_contents('data/data-pages.json', json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
        file_put_contents('data/data-videos.json', json_encode($videos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
        //file_put_contents('data/data-audios.json', json_encode($audios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
        file_put_contents('data/data-all.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }


    function parseMenu(string $xmlFile)
    {
        $xml = simplexml_load_string(file_get_contents($xmlFile));

        $slugify = new Slugify();


        $xmlPages = $xml->xpath('///spirals//page');

        foreach ($xmlPages as $xmlPage) {
            $pageId = $this->generatePageId($xmlPage);
            // ancestors
            $firstParent = $xmlPage->xpath('../../@title_en');
            $firstParentFR = $xmlPage->xpath('../../@title_fr');
            $secondParent = $xmlPage->xpath('../@title_en');
            $secondParentFR = $xmlPage->xpath('../@title_fr');

            if (count($firstParent) === 0) {
                $firstLevel = $slugify->slugify((string) $secondParent[0]['title_en']);
                $secondLevel = '<null>';
                if (!isset($firstLevels[$firstLevel]['label'])) {
                    $firstLevels[$firstLevel]['type'] = 'section';
                    $firstLevels[$firstLevel]['id'] = $firstLevel;
                    /*
                    $firstLevels[$firstLevel]['label'] = [
                        'en' => (string) $secondParent[0]['title_en'],
                        'fr' => (string) $secondParentFR[0]['title_fr'],
                    ];
                    */
                    $firstLevels[$firstLevel]['title_en'] = (string) $secondParent[0]['title_en'];
                    $firstLevels[$firstLevel]['title_fr'] = (string) $secondParentFR[0]['title_fr'];
                }

                $firstLevels[$firstLevel]['content'][] = [
                    'type' => 'page',
                    'page_id' => $pageId,
                    /*
                    'label'=>  [
                        'en' => (string) $xmlPage['title_en'],
                        'fr' => (string) $xmlPage['title_fr'],
                    ]*/
                    'title_en' => ucfirst(strtolower((string) $xmlPage['title_en'])),
                    'title_fr' => ucfirst(strtolower((string) $xmlPage['title_fr'])),


                ];
            } elseif (count($secondParent) !== 0) {
                $firstLevel = $slugify->slugify((string) $firstParent[0]['title_en']);
                $secondLevel = $slugify->slugify((string) (string) $secondParent[0]['title_en']);
                if (!isset($firstLevels[$firstLevel]['content'][$secondLevel]['label'])) {
                    $firstLevels[$firstLevel]['content'][$secondLevel]['type'] = 'section';
                    $firstLevels[$firstLevel]['content'][$secondLevel]['id'] = $firstLevel . '.' . $secondLevel;
                    /*
                    $firstLevels[$firstLevel]['content'][$secondLevel]['label'] = [
                        'en' => (string) $secondParent[0]['title_en'],
                        'fr' => (string) $secondParentFR[0]['title_fr'],
                    ];*/
                    $firstLevels[$firstLevel]['content'][$secondLevel]['title_en'] = ucfirst(strtolower((string) $secondParent[0]['title_en']));
                    $firstLevels[$firstLevel]['content'][$secondLevel]['title_fr'] = ucfirst(strtolower((string) $secondParentFR[0]['title_fr']));
                }

                $firstLevels[$firstLevel]['content'][$secondLevel]['content'][] = [
                   'type' => 'page',
                   'page_id' => $pageId,
                    /*
                   'label'=>  [
                        'en' => (string) $xmlPage['title_en'],
                        'fr' => (string) $xmlPage['title_fr'],
                   ]*/
                    'title_en' => ucfirst(strtolower((string) $xmlPage['title_en'])),
                    'title_fr' => ucfirst(strtolower((string) $xmlPage['title_fr'])),
                ];
            } else {
                //var_dump($slugify->slugify((string) $firstParent[0]['title_en']));
                //var_dump($pageId);
                //die('second parent is null');
            }
        }


        $menu = [];
        $i = 0;
        foreach ($firstLevels as $elem) {
            $j = 0;
            $menu[$i]['type'] = 'section';
            $menu[$i]['id'] = $elem['id'];
            //$menu[$i]['label'] = $elem['label'];
            $menu[$i]['title_en'] = ucfirst(strtolower($elem['title_en']));
            $menu[$i]['title_fr'] = ucfirst(strtolower($elem['title_fr']));
            //$menu[$i]['level'] = 1;
            //$menu[$i]['pages'] = $elem['pages'];
            foreach ($elem['content'] as $key => $submenu) {
                $menu[$i]['content'][$j] = $submenu;
                //$menu[$i]['menu'][$j]['level'] = 2;
                $j++;
            }
            $i++;
        }

        return $menu;
    }



    function generatePageId(\SimpleXMLElement $page): string
    {

        $cleanTitle = str_replace('-- ', '', (string) $page['title_en']);

        $slugify = new Slugify();
        $parentSection = $page->xpath('../../@title_en');
        $parentSubSection = $page->xpath("../@title_en")[0];
        //echo $title['en'];
        //echo json_encode($parentSubSection, JSON_PRETTY_PRINT);
        //die();


        $subParentId = (string) $parentSubSection;
        //echo json_encode($parentSubSection, JSON_PRETTY_PRINT);
        //die();
        $breadcrumb = [
            $slugify->slugify($subParentId),
            $slugify->slugify($cleanTitle),
        ];

        if (count($parentSection) > 0) {
            $parentNode = $parentSection[0];
            $parentTitle = (string) $parentNode['title_en'];
            array_unshift($breadcrumb, $slugify->slugify($parentTitle));
        }


        $pageId = implode('.', $breadcrumb);
        return $pageId;
    }

    function generatePageTitle(\SimpleXMLElement $page, string $lang, string $delimiter = ' '): string
    {

        $langAttr = ($lang === 'en') ? 'title_en': 'title_fr';

        $parentSection = $page->xpath("../../@$langAttr");
        $parentSubSection = $page->xpath("../@$langAttr")[0];

        $subParentId = (string) $parentSubSection;
        //echo json_encode($parentSubSection, JSON_PRETTY_PRINT);
        //die();
        $breadcrumb = [
            $subParentId,
        ];

        if (count($parentSection) > 0) {
            $parentNode = $parentSection[0];
            $parentTitle = (string) $parentNode[$langAttr];
            array_unshift($breadcrumb, $parentTitle);
        }


        $title = implode($delimiter, $breadcrumb);
        return $title;
    }


    function parseVideos(string $xmlFile): array
    {
         $this->uniqueVideos = [];
         $this->zoomVideos = [];
         $this->parsePages($xmlFile);

        foreach ($this->zoomVideos as $zoomIdx => $video) {
            if (!array_key_exists($zoomIdx, $this->uniqueVideos)) {
                $this->uniqueVideos[$zoomIdx] = $video;
            }
        }
         $uniqueVideos = $this->uniqueVideos;
        foreach ($uniqueVideos as $idx => $video) {
            //var_dump($uniqueVideos[$idx]);

            unset($uniqueVideos[$idx]['legacy_tracks']);
            unset($uniqueVideos[$idx]['legacy_src']);
            //var_dump($uniqueVideos[$idx]);
            //die();
        }

         $this->uniqueVideos = [];
         $this->zoomVideos = [];
         return array_values($uniqueVideos);
    }

    function parseAudios(string $xmlFile): array
    {
        $this->uniqueAudios = [];
        $this->parsePages($xmlFile);
        $uniqueAudios = $this->uniqueAudios;
        $this->uniqueAudios = [];
        return array_values($uniqueAudios);
    }


    function parsePages(string $xmlFile)
    {
        $this->audioTracksToConvert = [];

        $xml = simplexml_load_string(file_get_contents($xmlFile));

        $pages = [];

        $xmlPages = $xml->xpath('//section/page');

        foreach ($xmlPages as $orderIdx => $page) {
            $title = [
                'en' => (string) $page['title_en'],
                'fr' => (string) $page['title_fr'],
            ];

            $mediaCount = count($page->xpath('media'));
            $videoCount = count($page->xpath('media[@tope="video"]'));
            $swfVideoCount = count($page->xpath('media[@tope="swfvideo"]'));
            $subsCount = count($page->xpath('media[@tope="subs"]'));
            $audioCount = count($page->xpath('media[@tope="audio"]'));

            $pageId = $this->generatePageId($page);


            //var_dump($pageId);

            $layout = 'undefined';

            if ($mediaCount === 2 && $videoCount === 1 && $subsCount === 1) {
                $layout = 'single-video';
                $video = $page->xpath('media[@tope="video"]')[0];
                $subs = $page->xpath('media[@tope="subs"]')[0];

                $videoTag = $this->parseVideoTag(
                    $video,
                    false,
                    false,
                    $this->makeSubsTrack(
                        (string) $subs['source_en'],
                        (string) $subs['source_fr']
                    )
                );

                $medias = [
                    'videos' => [ $videoTag ],
                ];
            } elseif ($mediaCount === 1 && $videoCount === 1) {
                $video = $page->xpath('media[@tope="video"]')[0];

                $videoTag = $this->parseVideoTag($video, false, false);

                if (isset($videoTag['i18n']) && $videoTag['i18n']===true) {
                    $layout = 'single-i18n-video';
                    unset($videoTag['i18n']);
                    $medias = [
                        'videos' => [$videoTag]
                    ];
                } else {
                    $layout = 'single-video';
                    $medias = [
                        'videos' => [$videoTag]
                    ];
                }
            } elseif ($mediaCount === 2 && $videoCount === 1 && $audioCount === 1) {
                $video = $page->xpath('media[@tope="video"]')[0];
                $audio = $page->xpath('media[@tope="audio"]')[0];
                $videoTag = $this->parseVideoTag($video, true, true);

                $audioTag = $this->makeAudioSourceTag(
                    (string) $audio['source_en'],
                    (string) $audio['source_fr']
                );

                if ($audioTag['i18n'] === true) {
                    $layout = 'single-video-audio_i18n';
                    unset($audioTag['i18n']);
                    $medias = [
                        'videos' => [ $videoTag ],
                        'audio' => [
                             [
                                'src' => [
                                    //'langs' => true,
                                    'en' => $audioTag['en']['src'],
                                    'fr' => $audioTag['fr']['src'],
                                ]
                             ]
                        ]
                    ];
                } else {
                    $layout = 'single-video-audio';
                    unset($audioTag['i18n']);
                    $medias = [
                        'videos' => [ $videoTag ],
                        'audio' => $audioTag
                    ];
                }
            } elseif ($mediaCount === 1 && $swfVideoCount === 1) {
                $layout = 'single-video';
                $video = $page->xpath('media[@tope="swfvideo"]')[0];
                $videoTag = $this->parseVideoTag($video, false, false);
                $medias = [
                    'videos' => [ $videoTag ],
                ];
            } elseif ($mediaCount === 5 && $videoCount === 3 && $audioCount=== 1 && $subsCount === 1) {
                $videos = $page->xpath('media[@tope="video"]');
                $audio = $page->xpath('media[@tope="audio"]')[0];
                $subs = $page->xpath('media[@tope="subs"]')[0];

                $vids = [];
                foreach ($videos as $video) {
                    $videoTag = $this->parseVideoTag($video, true, true);
                    $vids[] = $videoTag;
                }

                $audioTag = $this->makeAudioSourceTag(
                    (string) $audio['source_en'],
                    (string) $audio['source_fr']
                );


                if ($audioTag['i18n'] === true) {
                    $layout = 'three-videos-audio_i18n-subs';
                    //unset($audioTag['i18n']);
                    $medias = [
                        'videos' => $vids,
                        'audio' => [
                            //'i18n' => true,
                            'src' => $audioTag,
                            'tracks' => [
                                $this->makeAudioSourceTracks('en', (string) $subs['source_en'], (string) $audio['source_en']),
                                $this->makeAudioSourceTracks('fr', (string) $subs['source_fr'], (string) $audio['source_en']),
                            ]
                        ],
                    ];
                } else {
                    unset($audioTag['i18n']);
                    $layout = 'three-videos-audio-subs';
                    $medias = [
                        'videos' => $vids,
                        'audio' => array_merge(
                            $audioTag,
                            ['tracks' => [
                                $this->makeAudioSourceTracks('en', (string) $subs['source_en'], (string) $audio['source_en']),
                                $this->makeAudioSourceTracks('fr', (string) $subs['source_fr'], (string) $audio['source_en']),

                            ]]
                        )
                    ];
                }
            } elseif ($mediaCount === 2 && $videoCount === 2) {
                $layout = 'two-videos-only';
                $videos = $page->xpath('media[@tope="video"]');

                $vids = [];
                foreach ($videos as $video) {
                    $videoTag = $this->parseVideoTag($video, true, false);
                    $vids[] = $videoTag;
                }
                $medias = [
                    'videos' => $vids,
                ];
            } elseif ($mediaCount === 4 && $videoCount === 2 && $subsCount === 1 && $audioCount === 1) {
                $videos = $page->xpath('media[@tope="video"]');
                $audio = $page->xpath('media[@tope="audio"]')[0];
                $subs = $page->xpath('media[@tope="subs"]')[0];

                $vids = [];
                foreach ($videos as $video) {
                    $videoTag = $this->parseVideoTag($video, true, true);
                    $vids[] = $videoTag;
                }
                $audioTag = $this->makeAudioSourceTag(
                    (string) $audio['source_en'],
                    (string) $audio['source_fr']
                );


                if ($audioTag['i18n'] === true) {
                    throw new Exception("This case is unsupported, need refactor audio parsing");
                    /*
                    $layout = 'two-videos-audio_i18n-subs';
                    unset($audioTag['i18n']);
                    $medias = [
                        'videos' => $vids,
                        'audio' =>
                            [
                                'i18n' => true,
                                'versions' => $audioTag,
                                'tracks' => [
                                    'en' => (string) $subs['source_en'],
                                    'fr' => (string) $subs['source_fr'],
                                ]
                            ]
                    ];
                    */
                } else {
                    $layout = 'two-videos-audio-subs';
                    unset($audioTag['i18n']);
                    $medias = [
                        'videos' => $vids,
                        'audio' => array_merge($audioTag, [
                            'tracks' => [
                                $this->makeAudioSourceTracks('en', (string) $subs['source_en'], (string) $audio['source_en']),
                                $this->makeAudioSourceTracks('fr', (string) $subs['source_fr'], (string) $audio['source_en']),


                            ]
                        ])
                    ];
                }
            } elseif ($mediaCount === 3 && $videoCount === 3) {
                $layout = 'three-videos-only';
                $videos = $page->xpath('media[@tope="video"]');

                $vids = [];
                foreach ($videos as $video) {
                    $videoTag = $this->parseVideoTag($video, true, false);
                    $vids[] = $videoTag;
                }
                $medias = [
                    'videos' => $vids,
                ];
            } else {
                die("error mediaCount:$mediaCount videoCount:$videoCount, subsCount:$subsCount, audioCount:$audioCount title:" . json_encode($title));
            }

            if (count($pages) > 0 && array_key_exists($pageId, $pages)) {
                throw new \Exception("Duplicate page '$pageId' found");
            }

            $main_cover = null;
            $firstVideoId = $medias['videos'][0]['lang_video_id']['en'];
            if ($firstVideoId === null) {
                print_r($medias['videos'][0]);

                throw new \Exception("Cannot find first video cover for '$pageId'");
            }
            $main_cover = $firstVideoId .'.jpg';

            $pages[$pageId] = [
                'page_id' => $pageId,
                'title' => [
                    'en' => ucfirst(trim($title['en'])),
                    'fr' => ucfirst(trim($title['fr'])),
                ],

                'sort_idx' => ($orderIdx * 100) + 100,
                'cover' => $main_cover,
                'keywords' => [
                   'en' => $this->parseKeywords($this->generatePageTitle($page, 'en') . ' ' . $title['en'], 'en'),
                   'fr' => $this->parseKeywords($this->generatePageTitle($page, 'fr') . ' ' . $title['fr'], 'fr'),
                ],
                //'mediaCount' => $mediaCount,
                //'videoCount' => $videoCount,
                //'subsCount' => $subsCount,

                'content' => array_merge([
                    'layout' => $layout
                    ], $medias)
            ];
        }

        return array_values($pages);
    }

    protected function parseKeywords(string $title, string $lang): array
    {

        $elements = explode(' ', strtolower($title));
        switch ($lang) {
            case 'fr':
                $keywords = array_filter($elements, function ($element) {
                    $elem = trim($element);
                    return preg_match("/[a-z\']/i", $elem)
                          && strlen($elem) > 2
                          && !in_array($elem, ['le', 'la', 'et', 'les', 'un', 'une', 'sur', 'comme', '(extraits)', 'english', 'par', 'des', 'du', 'pour', 'aux']);
                });
                break;

            case 'en':
            default:
                $keywords = array_filter($elements, function ($element) {
                    $elem = trim($element);
                    return preg_match("/[a-z\']/i", $elem)
                        && strlen($elem) > 2
                        && !in_array($elem, ['the', 'for', 'a', 'and', 'of', 'by', '(extracts)', 'english']);
                });
        }
        return array_values(array_unique($keywords));
    }

    protected function makeSubsTrack(string $srcEn, string $srcFr): array
    {

        return [
            'en' => $srcEn,
            'fr' => $srcFr
        ];
    }

    /**
     * @param \SimpleXMLElement $videoElement
     * @return array|string
     */
    protected function parseVideoTag(\SimpleXMLElement $videoElement, bool $loop = false, bool $muted = false, $tracks = null)
    {

        $source_en = (string) $videoElement['source_en'];
        $source_fr = (string) $videoElement['source_fr'];
        $videoZoomCount = count($videoElement->xpath('page'));

        if ($source_en !== $source_fr) {
            if ($videoZoomCount > 0) {
                throw new \Exception('Unsupported video_detail for i18n');
            }

            if ($tracks !== null) {
                throw new \Exception('Tracks must be null');
            }

            $tag = [
                'lang_video_id' => [
                    'en' => $this->makeVideoSourceTag($source_en)['video_id'],
                    'fr' => $this->makeVideoSourceTag($source_fr)['video_id']
                ],
            ];
            if ($loop) {
                $tag['loop']=true;
            }
            if ($muted) {
                $tag['muted']=true;
            }
            return $tag;
        } else {
            $made = $this->makeVideoSourceTag($source_en, $loop = false, $muted = false, $tracks);

            $tag = [
                'lang_video_id' => [
                    'en' => $made['video_id']
                ],

            ];
            if ($loop) {
                $tag['loop']=true;
            }
            if ($muted) {
                $tag['muted']=true;
            }

            if (isset($made['tracks'])) {
                $tag['tracks'] = $made['tracks'];
            }

            if ($videoZoomCount > 0) {
                $zoomPage = $videoElement->xpath('page');
                $videoSrc = (string) $zoomPage[0]->media['source_en'];

                if ($videoSrc != (string) $zoomPage[0]->media['source_fr']) {
                    throw new \Exception('Unsupported localized video_detail');
                }


                if ($source_en === $videoSrc) {
                    //die('cool');
                    $tag['zoomable'] = true;
                } else {
                    /*
                    echo PHP_EOL;
                    var_dump($videoSrc);
                    var_dump($source_en);
                    echo PHP_EOL;
                    */

                    $tag['video_detail'] = [
                        'lang_video_id' => ['en' => $this->makeZoomVideoSourceTag($videoSrc, false, false, null)['video_id']],
                        'muted' => true,
                        'desc' => [
                            'en' => (string)$zoomPage[0]['title_en'],
                            'fr' => (string)$zoomPage[0]['title_fr'],
                        ],
                    ];
                }
            }

            return $tag;
        }
    }

    function makeAudioSourceTracks(string $lang, string $legacySrc, string $audioName): array
    {

        //$src = 'audio/' . basename($legacySrc, '.srt') . '.' . $lang . '.vtt';
        $src = basename($audioName, '.mp3') . '.' . $lang . '.vtt';
        $this->audioTracksToConvert[$legacySrc] = $src;
        return ['lang' => $lang, 'src' => $src];
    }

    function makeAudioSourceTag(string $srcEn, string $srcFr): array
    {
        $audioSrc = $srcEn;

        if (!array_key_exists($audioSrc, $this->uniqueAudios)) {
            $audioId = basename(basename($audioSrc, '.mp3'), '.ogv');

            $this->uniqueAudios[$audioSrc] = [
                'audio_id' => $audioId,
                'legacy_path' => $audioSrc,
            ];

            $ids = array_column(array_values($this->uniqueAudios), 'audio_id');
            if (count($ids) !== count(array_unique($ids))) {
                throw new \Exception("Audio id $audioId is not unique");
            }
        } else {
            $audioId = $this->uniqueAudios[$audioSrc]['audio_id'];
        }




        if ($srcEn === $srcFr) {
            // one track
            return [
                'src' => [
                    'en' => basename($srcEn, '.mp3') . '.mp3'
                ]
            ];
        } else {
            return [
                'src' => [
                    'en' => basename($srcEn, '.mp3') . '.mp3',
                    'fr' => basename($srcFr, '.mp3') . '.mp3'
                ]
            ];
        }
    }


    function makeZoomVideoSourceTag(string $videoSrc, bool $muted = false, bool $loop = false): array
    {
        if (!array_key_exists($videoSrc, $this->zoomVideos)) {
            $videoId = basename(basename($videoSrc, '.swf'), '.flv');

            $legacyVideoPath = $this->getOriginalVideoSourcePath($videoId);

//            $legacyVideoPath = $this->assets_path . '/' . $videoSrc;
            $meta = $this->ffprobe($legacyVideoPath);

            $thumbnails = $this->generateThumbnails($legacyVideoPath, $videoId, $meta);

            $this->zoomVideos[$videoSrc] = [
                'video_id' => $videoId,
                'sources' => [
                    [
                        'src' => $videoId . '.webm',
                        'priority' => 10,
                        //'type' => 'video/webm',
                        'codecs' => 'vp9',
                    ],
                    [
                        'src' => $videoId . '.mp4',
                        'priority' => 20,
                        //'type' => 'video/mp4',
                    ],
                ],
                'covers' => $thumbnails,
                'meta' => $meta,
                'legacy_src' => $videoSrc,
            ];
        } else {
            $videoId = $this->zoomVideos[$videoSrc]['video_id'];
        }
        $videoLink =  [
            'video_id' => $videoId,
            //'src' => $videoSrc
        ];

        if ($muted) {
            $videoLink['muted'] = true;
        }

        if ($loop) {
            $videoLink['loop'] = true;
        }


        if ($this->zoomVideos[$videoSrc]['meta']['audio_stream'] === false) {
            $videoLink['muted'] = true;
        }

        return $videoLink;
    }

    protected function getOriginalVideoSourcePath(string $videoId, string $ext = '.mov') : string
    {
        // ext mkv

        $mkvFile = $this->assets_path . '/latest_sources/' . $videoId . '.mkv';
        if (file_exists($mkvFile)) {
            return $mkvFile;
        }

        $file = $this->assets_path . '/latest_sources/' . $videoId . $ext;

        return $file;
    }

    function makeVideoSourceTag(string $videoSrc, bool $muted = false, bool $loop = false, array $tracks = null): array
    {
        if (!array_key_exists($videoSrc, $this->uniqueVideos)) {
            $videoId = basename(basename($videoSrc, '.swf'), '.flv');

            //$legacyVideoPath = $this->assets_path . '/' . $videoSrc;
            $legacyVideoPath = $this->getOriginalVideoSourcePath($videoId);
            $meta = $this->ffprobe($legacyVideoPath);
            $thumbnails = $this->generateThumbnails($legacyVideoPath, $videoId, $meta);

            $this->uniqueVideos[$videoSrc] = [
                'video_id' => $videoId,
                'sources' => [
                    [
                        'src' => $videoId . '.webm',
                        'priority' => 10,
                        //'type' => 'video/webm',
                        'codecs' => 'vp9',
                    ],
                    [
                        'src' => $videoId . '.mp4',
                        'priority' => 20,
                        //'type' => 'video/mp4',
                    ],
                ],
                'covers' => $thumbnails,
                'meta' => $meta,
                'legacy_src' => $videoSrc,
            ];

            if ($tracks !== null) {
                $new_tracks = [
                    [ 'lang' =>'en', 'src' => $videoId . '.en.vtt'],
                    [ 'lang' =>'fr', 'src' => $videoId . '.fr.vtt']
                ];

                /*
                'tracks' => [
                    $this->makeAudioSourceTracks('en', (string) $subs['source_en'], (string) $audio['source_en']),
                    $this->makeAudioSourceTracks('fr', (string) $subs['source_fr'], (string) $audio['source_en']),
                ]
                */

                $this->uniqueVideos[$videoSrc]['tracks'] = $new_tracks;
                $this->uniqueVideos[$videoSrc]['legacy_tracks'] = $tracks;
            }

            $ids = array_column(array_values($this->uniqueVideos), 'video_id');
            if (count($ids) !== count(array_unique($ids))) {
                throw new \Exception("Video id $videoId is not unique");
            }
        } else {
            $videoId = $this->uniqueVideos[$videoSrc]['video_id'];
        }
        $videoLink =  [
                'video_id' => $videoId,
                //'src' => $videoSrc
        ];

        if ($muted) {
            $videoLink['muted'] = true;
        }

        if ($loop) {
            $videoLink['loop'] = true;
        }


        if (preg_match('/swf$/', $videoSrc)) {
            //$videoLink['animations'] = true;
        }

        if ($this->uniqueVideos[$videoSrc]['meta']['audio_stream'] === false) {
            $videoLink['muted'] = true;
        }

        return $videoLink;
    }

    protected function generateThumbnails(string $videoFile, string $videoId, array $meta): array
    {
        $thumbnails =  [
            $videoId . '.jpg'
        ];
        return $thumbnails;
    }

    protected function ffprobe(string $videoFile, bool $includeMediaInfo = false): array
    {
        $mediaInfo = $this->infoReader->getInfo($videoFile);

        ['width' => $width, 'height' => $height] = $mediaInfo->getDimensions();
        $meta = [
            'duration' => round($mediaInfo->getDuration(), 1),
            'size' => "${width}x${height}",
        ];
        if ($mediaInfo->getAudioStreamInfo() === null) {
            $meta['no_audio'] = true;
        }

        if ($includeMediaInfo) {
            $meta['mediaInfo'] = $mediaInfo;
        }

        return $meta;
    }



    public function makeCovers(string $inputFile, int $number = 5, ?OutputInterface $consoleOutput = null): array
    {

        $videos = $this->parseVideos($inputFile);

        $result = [];
        $assetsPath = $this->assets_path;
        $outputPath = $this->output_path . '/videos/covers';

        if (!is_dir($outputPath) && mkdir($outputPath, 0644, true) && !is_dir($outputPath)) {
            throw new \Exception('Cannot create output path for covers: "%s"', $outputPath);
        }

        if (!file_exists($this->ffmpeg)) {
            throw new \Exception("Cannot find ffmpeg binary: {$this->ffmpeg}");
        }

        foreach ($videos as $video) {
            $videoId = $video['video_id'];
            $videoFile = $this->getOriginalVideoSourcePath($videoId);
            if (!is_file($videoFile)) {
                throw new \Exception("Cannot find video file $videoFile");
            }

            //$videoMeta = $this->ffprobe($videoFile, true);
            $duration = $video['meta']['duration'];

            $videoFilters = $this->thumbGenerator->getDeintFilter($videoFile, new NlmeansVideoFilter());

            if ($duration > 0) {
                for ($i=0; $i < $number; $i++) {
                    $suffix = ($i === 0) ? '' : '-0' . $i;
                    $output = (($i === 0) ? dirname($outputPath) : $outputPath) . "/$videoId$suffix.jpg";
                    if ($consoleOutput !== null) {
                        $consoleOutput->write('<fg=green>*</>');
                    }
                    $time =  ceil($duration / $number * $i);
                    $this->thumbGenerator->makeThumbnails($videoFile, $output, $time, $videoFilters);
                }
            } else {
                $result['failed'][] = $videoFile;
            }
        }

        return $result;
    }

    public function convertAudioSubs(string $inputFile, OutputInterface $consoleOutput): array
    {
        $this->parsePages($inputFile);
    }

    public function convertSubs(string $inputFile, OutputInterface $consoleOutput): array
    {
        // Step 1 : Converting video subs

        $videos = $this->parseVideos($inputFile);

        $result = [];
        $assetsPath = $this->assets_path;
        $outputPath = $this->output_path . '/videos';

        if (!is_dir($outputPath)) {
            throw new \Exception("You must create $outputPath");
        }

        foreach ($videos as $video) {
            $videoId = $video['video_id'];
            //$videoFile = $assetsPath . '/assets/subs/' . $videoId . '.srt';

            /*
            if (!is_file($videoFile)) {
                throw new \Exception("Cannot find file $videoFile");
            }*/

            var_dump($video);

            $legacy_tracks = $video['legacy_tracks'];
            $tracks = $video['tracks'];
            if (count($legacy_tracks) > 0) {
                $conversions = [
                    $legacy_tracks['en'] => $tracks['en'],
                    $legacy_tracks['fr'] => $tracks['fr']
                ];

                foreach ($conversions as $oldFile => $newFile) {
                    $inputFile = $this->assets_path . '/' . $oldFile;
                    if (!file_exists($inputFile)) {
                        throw new \Exception("Cannot find videoTrack $inputFile");
                    }

                    $cmd = sprintf(
                        '%s -y -i %s %s',
                        $this->ffmpeg,
                        $inputFile,
                        $this->output_path . '/videos/' . $newFile
                    );

                    $consoleOutput->write('<fg=green>*</>');
                    exec($cmd, $output, $ret);
                    if ($ret !== 0) {
                        throw new \Exception("Cannot convert tracks  for $videoId ($cmd)");
                    }
                }
            } else {
                $result['skipped'][] = $videoId;
            }
        }

        // Step 2 : Converting audio subs

        foreach ($this->audioTracksToConvert as $oldFile => $newFile) {
            $inputFile = $this->assets_path . '/assets/' . str_replace('srt/', 'subs/', $oldFile);

            if (!file_exists($inputFile)) {
                throw new \Exception("Cannot locate audioTrack $inputFile");
            }

            $cmd = sprintf(
                '%s -y -i %s %s',
                $this->ffmpeg,
                $inputFile,
                $this->output_path . '/' . $newFile
            );
            $consoleOutput->write('<fg=green>*</>');
            exec($cmd, $output, $ret);
            if ($ret !== 0) {
                throw new \Exception("Cannot convert audio tracks for $videoId ($cmd)");
            }
        }

        return $result;
    }

    public function convertVideos(string $inputFile, $formats = ['mp4', 'webm'], $overwrite = false, OutputInterface $consoleOutput): array
    {
        /*
        <video>
          <source src="path/to/video.webm" type="video/webm; codecs=vp9,vorbis">
          <source src="path/to/video.mp4" type="video/mp4">
        </video>
        */
        $videos = $this->parseVideos($inputFile);

        $result = [];
        $assetsPath = $this->assets_path;
        $outputPath = $this->output_path . '/videos';
        if (!is_dir($outputPath)) {
            throw new \Exception("You must create $outputPath");
        }

        foreach ($videos as $video) {
            $videoId = $video['video_id'];
            $videoFile = $this->getOriginalVideoSourcePath($videoId);
            if (!is_file($videoFile)) {
                throw new \Exception("Cannot find file $videoFile");
            }

            $duration = $video['meta']['duration'];
            if ($duration > 0) {
                $outputBase = "$outputPath/$videoId";

                //$denoiseFilter = new NlmeansVideoFilter(); // TOO SLOW, OK FOR THUMBNAIL THOUGH
                $denoiseFilter = new Hqdn3dVideoFilter();
                $videoFilters = $this->converter->getDeintFilter($videoFile, $denoiseFilter);
                //$videoFilters = $this->videoTranscode->getDeintFilter($videoFile);


                foreach ($formats as $format) {
                    switch ($format) {
                        case 'mp4':
                            $outputFile = "$outputBase.mp4";
                            if (file_exists($outputFile)) {
                                $consoleOutput->write('<fg=cyan>*</>');
                                break;
                            }
                            $params = (new VideoConvertParams())
                                        ->withVideoCodec('h264')
                                        ->withAudioCodec('aac')
                                        ->withAudioBitrate('128k')
                                        ->withPreset('medium')
                                        // TODO !!! USE VIDEOPROBE TO BE SURE OF PIX_FMT
                                        ->withPixFmt('yuv420p')
                                        ->withStreamable(true)
                                        ->withCrf(24)
                                        ->withThreads(10)
                                        ->withOutputFormat('mp4');

                            $this->converter->transcode($videoFile, $outputFile, $params, $videoFilters);

                            break;

                        case 'webm':
                            $outputFile = "$outputBase.webm";

                            if (file_exists($outputFile)) {
                                $consoleOutput->write('<fg=cyan>*</>');
                                break;
                            }


                            // two passes version
                            $width = $video['meta']['width'];

                            if ($width <  700) {
                                $targetBitRate = "400k";
                                $minBitRate = "300k";
                                $maxBitRate = "600k";
                                $quality = 'good';
                                $crf = 33;
                            } elseif ($width < 1025) {
                                $targetBitRate = "600k";
                                $minBitRate = '350k';
                                $maxBitRate = '750k';
                                $quality = 'good';
                                $crf = 33;
                            } elseif ($width < 1281) {
                                $targetBitRate = "1800k";
                                $minBitRate = "900k";
                                $maxBitRate = "2610k";
                                $quality = 'good';
                                $crf = 31;
                            } else {
                                throw new \Exception("Cannot determine bitrate for $videoFile");
                            }

                            $params = (new VideoConvertParams())
                                ->withVideoCodec('libvpx-vp9')
                                ->withVideoBitrate($targetBitRate)
                                ->withVideoBitrate('750k')
                                ->withQuality($quality)
                                ->withCrf(33)
                                ->withThreads(12)
                                //->withCrf($crf)
                                //->withVideoMinBitrate($minBitRate)
                                //->withVideoMaxBitrate($maxBitRate)
                                ->withAudioCodec('libopus')
                                ->withAudioBitrate('128k')
                                /**
                                 * It is recommended to allow up to 240 frames of video between keyframes (8 seconds for 30fps content).
                                 * Keyframes are video frames which are self-sufficient; they don't rely upon any other frames to render
                                 * but they tend to be larger than other frame types.
                                 * For web and mobile playback, generous spacing between keyframes allows the encoder to choose the best
                                 * placement of keyframes to maximize quality.
                                 */
                                ->withKeyframeSpacing(240)
                                // Most of the current VP9 decoders use tile-based, multi-threaded decoding.
                                // In order for the decoders to take advantage of multiple cores,
                                // the encoder must set tile-columns and frame-parallel.
                                ->withTileColumns(2)
                                ->withFrameParallel(1)
                                ->withSpeed(0)
                                //->withPixFmt('yuv420p') // TODO !!! USE VIDEOPROBE TO BE SURE OF PIX_FMT
                                ->withOutputFormat('webm');

                            $interlaceGuess = $this->analyzer->detectInterlacement($videoFile, 1000);

                            if ($interlaceGuess->getBestGuess(0.4)) {
                                $params = $params->withVideoFilter(
                                    new VideoFilterChain([
                                        new YadifVideoFilter(

                                        ),
                                        new Hqdn3dVideoFilter()
                                    ])
                                );
                            }

                            $this->converter->convert($videoFile, $outputFile, $params);


                            /*
                            $threads = '7';

                            $cmd =
                                implode(' && ', [
                                  sprintf(
                                      '%s -i %s -b:v %s %s %s  -threads %s -quality %s -crf %s -c:v libvpx-vp9 %s -pass 1 -speed 4 -f webm -y %s',
                                      $this->ffmpeg,
                                      $videoFile,
                                      $targetBitRate,
                                      $minBitRate !== '' ? "-minrate $minBitRate" : '',
                                      $maxBitRate !== '' ? "-maxrate $maxBitRate" : '',
                                      $threads,
                                      $quality,
                                      $crf,
                                      ($video['meta']['audio_stream']) ? '-c:a libopus' : '',
                                      $tmpFile
                                  ),
                                  sprintf(
                                      '%s -i %s -b:v %s %s %s  -threads %s -quality %s -crf %s -c:v libvpx-vp9 %s -pass 2 -speed 4 -f webm -y %s',
                                      $this->ffmpeg,
                                      $videoFile,
                                      $targetBitRate,
                                      $minBitRate !== '' ? "-minrate $minBitRate" : '',
                                      $maxBitRate !== '' ? "-maxrate $maxBitRate" : '',
                                      $threads,
                                      $quality,
                                      $crf,
                                      ($video['meta']['audio_stream']) ? '-c:a libopus' : '',
                                      $tmpFile
                                  )
                                  ]);
                            */

                            break;
                        default:
                            throw new \Exception("Format $format not supported");
                    }

                    if ($overwrite || !file_exists($outputFile)) {
                            echo PHP_EOL;
                            echo $cmd;
                            echo PHP_EOL;
                            echo PHP_EOL;

                            $consoleOutput->write('<fg=green>*</>');
                            exec($cmd, $output, $ret);
                        if ($ret !== 0) {
                            throw new \Exception("Cannot convert video to $format for $videoFile ($cmd)");
                        }
                        if ($overwrite === true && file_exists($outputFile)) {
                            unlink($outputFile);
                        }
                        rename($tmpFile, $outputFile);
                    } else {
                        $consoleOutput->write('<fg=yellow>*</>');
                        $result['skipped'] = $videoFile;
                    }
                }
            } else {
                $result['failed'][] = $videoFile;
            }
        }

        return $result;
    }
}
