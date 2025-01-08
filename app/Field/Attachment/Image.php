<?php

namespace App\GenericLibrary\AirTable\Field\Attachment;

use App\GenericLibrary\AirTable\Field\URL;

readonly class Image
{
    public private(set)  URL $defaultImage;
    public private(set)  URL $thumbnailSmall;
    public private(set)  URL $thumbnailLarge;
    public private(set)  URL $thumbnailFull;

    /**
     * @param array{
     *     url: string,
     *     thumbnails: array{
     *         small: array{
     *             url: string
     *         },
     *         large: array{
     *             url: string
     *         },
     *         full: array{
     *             url: string
     *         }
     *     }
     * } $value
     */
    public function __construct(array $value)
    {
        $this->defaultImage = new URL($value['url']);

        $thumbnail            = $value['thumbnails'];
        $this->thumbnailFull  = new URL($thumbnail['full']['url']);
        $this->thumbnailLarge = new URL($thumbnail['large']['url']);
        $this->thumbnailSmall = new URL($thumbnail['small']['url']);
    }
}
