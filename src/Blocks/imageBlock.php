<?php

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;
use MaxPertici\Markup\Markup;

class ImageBlock extends BlockMarkup {

    /**
     * Block attribute: id.
     *
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * Block attribute: sizeSlug.
     *
     * @var string
     */
    protected string $sizeSlug = 'full';

    /**
     * Block attribute: linkDestination (none, media, attachment, custom).
     *
     * @var string
     */
    protected string $linkDestination = 'none';

    /**
     * Block attribute: lightbox.
     * Example: [ 'enabled' => false ]
     *
     * @var array<string, bool>|null
     */
    protected ?array $lightbox = null;

    /**
     * Block attribute: aspectRatio.
     * Example: 4/3
     *
     * @var string|null
     */
    protected ?string $aspectRatio = null;

    /**
     * Block attribute: scale.
     * Example: cover
     *
     * @var string|null
     */
    protected ?string $scale = null;

    /**
     * Block attribute: width.
     * Example: 240px
     *
     * @var string|null
     */
    protected ?string $width = null;

    /**
     * Block attribute: height.
     * Example: 120px
     *
     * @var string|null
     */
    protected ?string $height = null;

    /**
     * Block attribute for custom link destination.
     *
     * @var string|null
     */
    protected ?string $href = null;

    /**
     * Optional custom alt text for the img markup.
     *
     * @var string|null
     */
    protected ?string $alt = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
     * @param int                    $imageId          The image ID from the media library.
     * @param string                 $imageSize        The image size (e.g., 'thumbnail', 'medium', 'large', 'full').
     * @param string                 $linkDestination  The link destination for the image.
     * @param array<string, bool>|null $lightbox      Optional. The lightbox block attribute.
     * @param string|null            $aspectRatio      Optional. Aspect ratio (e.g. 4/3).
     * @param string|null            $scale            Optional. Scale (e.g. cover).
     * @param string|null            $width            Optional. Width (e.g. 240px).
     * @param string|null            $height           Optional. Height (e.g. 120px).
     * @param string|null            $href             Optional. Custom link URL for custom destination.
     * @param string|null            $alt              Optional. Custom alt text.
	 */
    public function __construct(
        int $imageId,
        string $imageSize = 'full',
        string $linkDestination = 'none',
        ?array $lightbox = null,
        ?string $aspectRatio = null,
        ?string $scale = null,
        ?string $width = null,
        ?string $height = null,
        ?string $href = null,
        ?string $alt = null
    ) {
        $this->id              = $imageId;
        $this->sizeSlug        = $imageSize;
        $this->linkDestination = $linkDestination;
        $this->aspectRatio     = $aspectRatio;
        $this->scale           = $scale;
        $this->width           = $width;
        $this->height          = $height;
        $this->href            = $href;
        $this->alt             = $alt;

        if ( is_array( $lightbox ) ) {
            $this->lightbox = [
                'enabled' => (bool) ( $lightbox['enabled'] ?? true ),
            ];
        }

		parent::__construct(
			blockName: 'core/image',
			blockAttributes: array(),
            wrapper: '%children%',
			children: []
		);
	}

    /**
     * Gets or sets the image id (block attribute `id`).
     *
     * @param int|null $id
     * @return int|self
     */
    public function id( ?int $id = null ) {
        if ( null === $id ) {
            return $this->id;
        }

        $this->id = $id;
        return $this;
    }

    /**
     * Gets or sets the image size slug (block attribute `sizeSlug`).
     *
     * @param string|null $sizeSlug
     * @return string|self
     */
    public function sizeSlug( ?string $sizeSlug = null ) {
        if ( null === $sizeSlug ) {
            return $this->sizeSlug;
        }

        $this->sizeSlug = $sizeSlug;
        return $this;
    }

    /**
     * Alias for sizeSlug().
     *
     * @param string|null $imageSize
     * @return string|self
     */
    public function imageSize( ?string $imageSize = null ) {
        if ( null === $imageSize ) {
            return $this->sizeSlug;
        }

        $this->sizeSlug = $imageSize;
        return $this;
    }

    /**
     * Gets or sets link destination (`none`, `media`, `attachment`, `custom`).
     *
     * @param string|null $destination
     * @return string|self
     */
    public function linkDestination( ?string $destination = null ) {
        if ( null === $destination ) {
            return $this->linkDestination;
        }

        $allowed = [ 'none', 'media', 'attachment', 'custom' ];
        if ( ! in_array( $destination, $allowed, true ) ) {
            $destination = 'none';
        }

        $this->linkDestination = $destination;
        return $this;
    }

    /**
     * Gets or sets lightbox attribute.
     *
     * @param array<string, bool>|null $lightbox
     * @return array<string, bool>|self|null
     */
    public function lightbox( ?array $lightbox = null ) {
        if ( null === $lightbox ) {
            return $this->lightbox;
        }

        $this->lightbox = [
            'enabled' => (bool) ( $lightbox['enabled'] ?? true ),
        ];

        return $this;
    }

    /**
     * Shortcut to enable/disable lightbox.
     *
     * @param bool $enabled
     * @return self
     */
    public function lightboxEnabled( bool $enabled = true ): self {
        $this->lightbox = [ 'enabled' => $enabled ];
        return $this;
    }

    /**
     * Remove lightbox attribute from block attributes.
     *
     * @return self
     */
    public function withoutLightbox(): self {
        $this->lightbox = null;
        return $this;
    }

    /**
     * Gets or sets aspect ratio (block attribute `aspectRatio`).
     *
     * @param string|null $ratio
     * @return string|self|null
     */
    public function aspectRatio( ?string $ratio = null ) {
        if ( null === $ratio ) {
            return $this->aspectRatio;
        }

        $this->aspectRatio = $ratio;
        return $this;
    }

    /**
     * Gets or sets scale (block attribute `scale`).
     *
     * @param string|null $scale
     * @return string|self|null
     */
    public function scale( ?string $scale = null ) {
        if ( null === $scale ) {
            return $this->scale;
        }

        $this->scale = $scale;
        return $this;
    }

    /**
     * Gets or sets width (block attribute `width`).
     *
     * @param string|null $width
     * @return string|self|null
     */
    public function width( ?string $width = null ) {
        if ( null === $width ) {
            return $this->width;
        }

        $this->width = $width;
        return $this;
    }

    /**
     * Gets or sets height (block attribute `height`).
     *
     * @param string|null $height
     * @return string|self|null
     */
    public function height( ?string $height = null ) {
        if ( null === $height ) {
            return $this->height;
        }

        $this->height = $height;
        return $this;
    }

    /**
     * Gets or sets custom href (block attribute `href` when destination is `custom`).
     *
     * @param string|null $href
     * @return string|self|null
     */
    public function href( ?string $href = null ) {
        if ( null === $href ) {
            return $this->href;
        }

        $this->href = $href;
        return $this;
    }

    /**
     * Gets or sets image alt override.
     *
     * @param string|null $alt
     * @return string|self|null
     */
    public function alt( ?string $alt = null ) {
        if ( null === $alt ) {
            return $this->alt;
        }

        $this->alt = $alt;
        return $this;
    }

    /**
     * Helper to set custom link quickly.
     *
     * @param string $href
     * @return self
     */
    public function customLink( string $href ): self {
        $this->href = $href;
        $this->linkDestination = 'custom';
        return $this;
    }

    protected function build(): void {
        if ( null === $this->id ) {
            $this->children = [];
            $this->blockAttributes = [];
            return;
        }

        $imageSrc = (string) \wp_get_attachment_image_url( $this->id, $this->sizeSlug );

        $imageAlt = (string) \get_post_meta( $this->id, '_wp_attachment_image_alt', true );
        $imageAlt = ( null !== $this->alt ) ? $this->alt : (string) $imageAlt;

        $imgStyle = [];

        if ( ! empty( $this->aspectRatio ) ) {
            $imgStyle[] = 'aspect-ratio:' . $this->aspectRatio;
        }

        if ( 'cover' === $this->scale ) {
            $imgStyle[] = 'object-fit:cover';
        }

        if ( ! empty( $this->width ) ) {
            $imgStyle[] = 'width:' . $this->width;
        }

        if ( ! empty( $this->height ) ) {
            $imgStyle[] = 'height:' . $this->height;
        }

        $imageAttributes = [
            'src'   => $imageSrc,
            'alt'   => $imageAlt,
            'class' => 'wp-image-' . $this->id,
        ];

        if ( ! empty( $imgStyle ) ) {
            $imageAttributes['style'] = implode( ';', $imgStyle );
        }

        $imageMarkup = new Markup(
            wrapper: '<img %attributes%/>',
            wrapperAttributes: $imageAttributes
        );

        $innerMarkup = $imageMarkup->render();

        if ( 'attachment' === $this->linkDestination || 'media' === $this->linkDestination || 'custom' === $this->linkDestination ) {
            $linkHref = '';

            if ( 'attachment' === $this->linkDestination ) {
                $linkHref = (string) \get_attachment_link( $this->id );
            } elseif ( 'media' === $this->linkDestination ) {
                $linkHref = (string) \wp_get_attachment_url( $this->id );
            } elseif ( 'custom' === $this->linkDestination ) {
                $linkHref = (string) $this->href;
            }

            if ( '' !== $linkHref ) {
                $linkMarkup = new Markup(
                    wrapper: '<a %attributes%>%children%</a>',
                    wrapperAttributes: [
                        'href' => $linkHref,
                    ],
                    children: [ $innerMarkup ]
                );

                $innerMarkup = $linkMarkup->render();
            }
        }

        $figureClasses = [ 'wp-block-image', 'size-' . $this->sizeSlug ];

        if ( ! empty( $this->width ) || ! empty( $this->height ) ) {
            $figureClasses[] = 'is-resized';
        }

        $figureMarkup = new Markup(
            wrapper: '<figure class="%classes%" %attributes%>%children%</figure>',
            wrapperClass: $figureClasses,
            children: [ $innerMarkup ]
        );

        $this->children = [ $figureMarkup->render() ];

        $this->blockAttributes = [
            'id'              => $this->id,
            'sizeSlug'        => $this->sizeSlug,
            'linkDestination' => $this->linkDestination,
        ];

        if ( null !== $this->lightbox ) {
            $this->blockAttributes['lightbox'] = $this->lightbox;
        }

        if ( ! empty( $this->aspectRatio ) ) {
            $this->blockAttributes['aspectRatio'] = $this->aspectRatio;
        }

        if ( ! empty( $this->scale ) ) {
            $this->blockAttributes['scale'] = $this->scale;
        }

        if ( ! empty( $this->width ) ) {
            $this->blockAttributes['width'] = $this->width;
        }

        if ( ! empty( $this->height ) ) {
            $this->blockAttributes['height'] = $this->height;
        }

        if ( 'custom' === $this->linkDestination && ! empty( $this->href ) ) {
            $this->blockAttributes['href'] = $this->href;
        }
    }

	/**
	 * Gets the complete block markup with Gutenberg comments.
	 *
	 * Builds the wrapper and attributes before rendering the block.
	 *
	 * @since 1.0.0
	 *
	 * @return string The complete block markup including Gutenberg comment syntax.
	 */
	public function render(): string {
		$this->build();
		return parent::render();
	}

	/**
	 * Prints the complete block markup with Gutenberg comments (echo mode).
	 *
	 * Builds the wrapper and attributes before printing the block.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function print(): void {
		$this->build();
		parent::print();
	}
}

