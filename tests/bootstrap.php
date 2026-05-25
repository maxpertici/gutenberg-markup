<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

if ( ! function_exists( 'parse_blocks' ) ) {
	/**
	 * Minimal test parser fallback for Gutenberg comments.
	 *
	 * @param string $content
	 * @return array<int, array<string, mixed>>
	 */
	function parse_blocks( string $content ): array {
		$results = [];

		$pairedPattern = '/<!--\s+wp:([a-z0-9\/-]+)(?:\s+(\{.*?\}))?\s+-->(.*?)<!--\s+\/wp:\1\s+-->/si';
		if ( preg_match_all( $pairedPattern, $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$attrs = [];
				if ( isset( $match[2] ) && '' !== $match[2] ) {
					$decoded = json_decode( $match[2], true );
					$attrs   = is_array( $decoded ) ? $decoded : [];
				}

				$innerHtml = (string) ( $match[3] ?? '' );
				$results[] = [
					'blockName' => (string) $match[1],
					'attrs' => $attrs,
					'innerBlocks' => [],
					'innerHTML' => $innerHtml,
					'innerContent' => [ $innerHtml ],
				];
			}
		}

		$selfClosingPattern = '/<!--\s+wp:([a-z0-9\/-]+)(?:\s+(\{.*?\}))?\s+\/-->/si';
		if ( preg_match_all( $selfClosingPattern, $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$attrs = [];
				if ( isset( $match[2] ) && '' !== $match[2] ) {
					$decoded = json_decode( $match[2], true );
					$attrs   = is_array( $decoded ) ? $decoded : [];
				}

				$results[] = [
					'blockName' => (string) $match[1],
					'attrs' => $attrs,
					'innerBlocks' => [],
					'innerHTML' => '',
					'innerContent' => [],
				];
			}
		}

		if ( [] !== $results ) {
			return $results;
		}

		return [
			[
				'blockName' => null,
				'attrs' => [],
				'innerBlocks' => [],
				'innerHTML' => $content,
				'innerContent' => [ $content ],
			],
		];
	}
}

if ( ! function_exists( 'do_blocks' ) ) {
	/**
	 * @param string $content
	 * @return string
	 */
	function do_blocks( string $content ): string {
		return $content;
	}
}
