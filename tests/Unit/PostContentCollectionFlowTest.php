<?php

declare(strict_types=1);

namespace MaxPertici\GutenbergMarkup\Tests\Unit;

use MaxPertici\GutenbergMarkup\BlockFactory;
use MaxPertici\GutenbergMarkup\Blocks\ParagraphBlock;
use MaxPertici\GutenbergMarkup\PostContent;
use PHPUnit\Framework\TestCase;

final class PostContentCollectionFlowTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		BlockFactory::clearBlockParsers();
		BlockFactory::clearNativeSupportedBlocks();
		BlockFactory::enableAutomaticClassResolution( false );
		BlockFactory::registerNativeSupportedBlock( 'core/paragraph' );
	}

	public function testCollectionUpdateFlowWithWithBlocksRemainsStable(): void {
		$initial = ( new ParagraphBlock( 'Initial title' ) )->render();
		$postContent = new PostContent( $initial );

		$updatedBlocks = $postContent
			->toBlocksCollection()
			->map(
				static function ( mixed $block ) {
					if ( ! $block instanceof ParagraphBlock ) {
						return $block;
					}

					return new ParagraphBlock( 'Updated title' );
				}
			);

		$updatedPostContent = $postContent->withBlocks( $updatedBlocks );
		$updatedMarkup = $updatedPostContent->toMarkup();

		$this->assertStringContainsString( 'Updated title', $updatedMarkup );
		$this->assertStringContainsString( '<!-- wp:paragraph', $updatedMarkup );
	}
}
