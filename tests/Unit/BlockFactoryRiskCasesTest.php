<?php

declare(strict_types=1);

namespace MaxPertici\GutenbergMarkup\Tests\Unit;

use MaxPertici\GutenbergMarkup\BlockFactory;
use MaxPertici\GutenbergMarkup\Blocks\GroupBlock;
use MaxPertici\GutenbergMarkup\PostContentBlock;
use PHPUnit\Framework\TestCase;

final class BlockFactoryRiskCasesTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		BlockFactory::clearBlockParsers();
		BlockFactory::clearNativeSupportedBlocks();
		BlockFactory::enableAutomaticClassResolution( false );
	}

	public function testUnsupportedBlockFallsBackToPostContentBlock(): void {
		$parsed = [
			'blockName' => 'core/unsupported',
			'attrs' => [ 'foo' => 'bar' ],
			'innerBlocks' => [],
			'innerHTML' => '<div>Unknown</div>',
			'innerContent' => [ '<div>Unknown</div>' ],
		];

		$blocks = BlockFactory::parseParsedBlocks( [ $parsed ] );

		$this->assertCount( 1, $blocks );
		$this->assertInstanceOf( PostContentBlock::class, $blocks[0] );
		$this->assertStringContainsString( '<!-- wp:unsupported {"foo":"bar"} -->', $blocks[0]->render() );
		$this->assertStringContainsString( '<div>Unknown</div>', $blocks[0]->render() );
	}

	public function testNestedBlocksAreParsedAsObjectsWhenEnabled(): void {
		BlockFactory::registerNativeSupportedBlocks( [ 'core/group', 'core/paragraph' ] );

		$parsed = [
			'blockName' => 'core/group',
			'attrs' => [],
			'innerBlocks' => [
				[
					'blockName' => 'core/paragraph',
					'attrs' => [],
					'innerBlocks' => [],
					'innerHTML' => '<p>Nested child</p>',
					'innerContent' => [ '<p>Nested child</p>' ],
				],
			],
			'innerHTML' => '<p>Nested child</p>',
			'innerContent' => [ null ],
		];

		$blocks = BlockFactory::parseParsedBlocks( [ $parsed ] );

		$this->assertCount( 1, $blocks );
		$this->assertInstanceOf( GroupBlock::class, $blocks[0] );
		$this->assertStringContainsString( 'Nested child', $blocks[0]->render() );
	}

	public function testMalformedParsedPayloadDoesNotCrashAndFallsBackSafely(): void {
		$parsed = [
			'blockName' => 'core/unsupported',
			'attrs' => 'invalid-shape',
			'innerBlocks' => 'invalid-shape',
			'innerHTML' => '',
			'innerContent' => 'invalid-shape',
		];

		$blocks = BlockFactory::parseParsedBlocks( [ $parsed ] );

		$this->assertCount( 1, $blocks );
		$this->assertInstanceOf( PostContentBlock::class, $blocks[0] );
		$this->assertSame( '<!-- wp:unsupported /-->', $blocks[0]->render() );
	}
}
