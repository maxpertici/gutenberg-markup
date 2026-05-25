<?php

declare(strict_types=1);

namespace MaxPertici\GutenbergMarkup\Tests\Unit;

use MaxPertici\GutenbergMarkup\Blocks\ButtonBlock;
use MaxPertici\GutenbergMarkup\Blocks\FileBlock;
use PHPUnit\Framework\TestCase;

final class BlockSecurityEscapingTest extends TestCase {

	public function testButtonEscapesTextAndSanitizesDangerousUrl(): void {
		$block = new ButtonBlock( '<script>alert("x")</script>', 'javascript:alert(1)' );

		$markup = $block->render();

		$this->assertStringContainsString( 'href=""', $markup );
		$this->assertStringContainsString( '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', $markup );
	}

	public function testFileBlockEscapesTextAndSanitizesDangerousHref(): void {
		$block = new FileBlock(
			attachmentId: 123,
			showDownloadButton: true,
			openInNewTab: false,
			downloadButtonText: 'Download "Now"',
			fileName: 'File <unsafe>'
		);
		$block->href( 'javascript:alert(1)' );

		$markup = $block->render();

		$this->assertStringContainsString( 'href=""', $markup );
		$this->assertStringContainsString( 'File &lt;unsafe&gt;', $markup );
		$this->assertStringContainsString( 'Download &quot;Now&quot;', $markup );
	}
}
