<?php
/**
 * File Block Implementation
 *
 * Gutenberg file block markup generator.
 *
 * @package MaxPertici\GutenbergMarkup\Blocks
 */

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\AnchorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Concerns\Block\BlockStyleTrait;

/**
 * File Gutenberg Block implementation.
 *
 * Generates the markup for a downloadable file block, including the
 * styled file link and an optional download button.
 *
 * Requires a WordPress environment (uses wp_get_attachment_url(),
 * get_the_title(), and wp_generate_uuid4()).
 *
 * @since 1.0.0
 */
class FileBlock extends BlockMarkup {

	use AnchorTrait;
	use CustomClassTrait;
	use BlockStyleTrait;

	/**
	 * Block attribute: id.
	 *
	 * The WordPress attachment ID of the file.
	 *
	 * @since 1.0.0
	 * @var int|null
	 */
	protected ?int $id = null;

	/**
	 * Block attribute: href.
	 *
	 * The direct URL to the file. Computed automatically from the
	 * attachment ID when not set explicitly.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected ?string $href = null;

	/**
	 * Block attribute: showDownloadButton.
	 *
	 * When false the download button is omitted and
	 * `{"showDownloadButton":false}` is added to the block attributes.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $showDownloadButton = true;

	/**
	 * Block attribute: downloadButtonText.
	 *
	 * Label text shown on the download button.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $downloadButtonText = 'Download';

	/**
	 * Block attribute: openInNewTab.
	 *
	 * When true the file link opens in a new browser tab.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $openInNewTab = false;

	/**
	 * Optional custom display name for the file link.
	 *
	 * When null the attachment title is used.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected ?string $fileName = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int         $attachmentId       The WordPress attachment ID.
	 * @param bool        $showDownloadButton Optional. Show download button. Default true.
	 * @param bool        $openInNewTab       Optional. Open file link in a new tab. Default false.
	 * @param string      $downloadButtonText Optional. Download button label. Default 'Download'.
	 * @param string|null $fileName           Optional. Override the displayed file name. Default null.
	 */
	public function __construct(
		int $attachmentId,
		bool $showDownloadButton = true,
		bool $openInNewTab = false,
		string $downloadButtonText = 'Download',
		?string $fileName = null
	) {
		$this->id                 = $attachmentId;
		$this->showDownloadButton = $showDownloadButton;
		$this->openInNewTab       = $openInNewTab;
		$this->downloadButtonText = $downloadButtonText;
		$this->fileName           = $fileName;

		parent::__construct(
			blockName: 'core/file',
			blockAttributes: array(),
			wrapper: '%children%',
			children: array()
		);
	}

	/**
	 * Gets or sets the attachment ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $id Optional. Attachment ID to set. Pass null to get.
	 * @return int|self|null
	 */
	public function id( ?int $id = null ) {
		if ( null === $id ) {
			return $this->id;
		}

		$this->id = $id;
		return $this;
	}

	/**
	 * Gets or sets the file URL (block attribute `href`).
	 *
	 * When set, overrides the URL computed from the attachment ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $href Optional. File URL to set. Pass null to get.
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
	 * Gets or sets whether the download button is shown.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|null $show Optional. True to show, false to hide. Pass null to get.
	 * @return bool|self
	 */
	public function showDownloadButton( ?bool $show = null ) {
		if ( null === $show ) {
			return $this->showDownloadButton;
		}

		$this->showDownloadButton = $show;
		return $this;
	}

	/**
	 * Gets or sets the download button label text.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $text Optional. Label text. Pass null to get.
	 * @return string|self
	 */
	public function downloadButtonText( ?string $text = null ) {
		if ( null === $text ) {
			return $this->downloadButtonText;
		}

		$this->downloadButtonText = $text;
		return $this;
	}

	/**
	 * Gets or sets whether the file link opens in a new tab.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|null $open Optional. True to open in new tab. Pass null to get.
	 * @return bool|self
	 */
	public function openInNewTab( ?bool $open = null ) {
		if ( null === $open ) {
			return $this->openInNewTab;
		}

		$this->openInNewTab = $open;
		return $this;
	}

	/**
	 * Gets or sets the display file name.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $fileName Optional. Display name. Pass null to get.
	 * @return string|self|null
	 */
	public function fileName( ?string $fileName = null ) {
		if ( null === $fileName ) {
			return $this->fileName;
		}

		$this->fileName = $fileName;
		return $this;
	}

	/**
	 * Generate a unique media ID for the file link anchor.
	 *
	 * Uses wp_generate_uuid4() when available, otherwise falls back to
	 * a deterministic value derived from the attachment ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string A UUID-style string.
	 */
	protected function generateMediaId(): string {
		if ( \function_exists( 'wp_generate_uuid4' ) ) {
			return \wp_generate_uuid4();
		}

		// Deterministic fallback based on attachment ID.
		return sprintf(
			'%08x-%04x-%04x-%04x-%012x',
			$this->id,
			0x1d2e,
			0x429b,
			0xb655,
			$this->id
		);
	}

	/**
	 * Build the block markup and attributes before rendering.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function build(): void {
		if ( null === $this->id ) {
			$this->children        = array();
			$this->blockAttributes = array();
			return;
		}

		// Resolve file URL.
		$href = $this->href;
		if ( null === $href || '' === $href ) {
			$href = \function_exists( 'wp_get_attachment_url' )
				? (string) \wp_get_attachment_url( $this->id )
				: '';
		}

		// Resolve display name.
		$fileName = $this->fileName;
		if ( null === $fileName || '' === $fileName ) {
			$fileName = \function_exists( 'get_the_title' )
				? (string) \get_the_title( $this->id )
				: (string) $this->id;
		}

		$mediaId = 'wp-block-file--media-' . $this->generateMediaId();

		$safeHref             = \function_exists( 'esc_url' ) ? \esc_url( $href ) : htmlspecialchars( $href, ENT_QUOTES );
		$safeFileName         = \function_exists( 'esc_html' ) ? \esc_html( $fileName ) : htmlspecialchars( $fileName, ENT_QUOTES );
		$safeDownloadText     = \function_exists( 'esc_html' ) ? \esc_html( $this->downloadButtonText ) : htmlspecialchars( $this->downloadButtonText, ENT_QUOTES );

		// Build file link attributes.
		$linkAttrs = sprintf( 'id="%s" href="%s"', $mediaId, $safeHref );
		if ( $this->openInNewTab ) {
			$linkAttrs .= ' target="_blank" rel="noreferrer noopener"';
		}

		$html = '<div class="wp-block-file">';
		$html .= sprintf( '<a %s>%s</a>', $linkAttrs, $safeFileName );

		if ( $this->showDownloadButton ) {
			$html .= sprintf(
				'<a href="%s" class="wp-block-file__button wp-element-button" download aria-describedby="%s">%s</a>',
				$safeHref,
				$mediaId,
				$safeDownloadText
			);
		}

		$html .= '</div>';

		$this->children = array( $html );

		// Build block attributes.
		// Also keep the raw href in block attributes (Gutenberg expects the unescaped URL).
		$this->blockAttributes = array(
			'id'   => $this->id,
			'href' => $href,
		);

		if ( ! $this->showDownloadButton ) {
			$this->blockAttributes['showDownloadButton'] = false;
		}

		if ( $this->openInNewTab ) {
			$this->blockAttributes['openInNewTab'] = true;
		}

		if ( 'Download' !== $this->downloadButtonText && $this->showDownloadButton ) {
			$this->blockAttributes['downloadButtonText'] = $this->downloadButtonText;
		}
	}

	/**
	 * Gets the complete block markup with Gutenberg comments.
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
	 * Prints the complete block markup with Gutenberg comments.
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
