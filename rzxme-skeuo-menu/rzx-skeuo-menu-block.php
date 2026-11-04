<?php
/**
 * RZX 拟物菜单 (rzxme/skeuo-menu) — 动态区块
 *
 * 从 old.rzx.me 首页经典拟物化菜单复刻。
 * 设计原则：
 *  1. 全部类名使用 rzx-skeuo 命名空间，与 WP 全局样式/脚本零交集；
 *  2. 样式与脚本随区块渲染内联输出，仅存在于使用该区块的页面；
 *  3. 不依赖 jQuery，动效均为原生 CSS 过渡/关键帧 + 少量原生 JS。
 *
 * @package rzxme-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 生成组件所需的图片 URL。
 * 优先走媒体库（附件 ID），失败时回退到固定上传路径。
 */
function rzxme_skeuo_menu_image( $key ) {
	static $cache = array();

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$attachment_ids = array(
		'animation' => 11843, // ray-home-menu-anim-alpha-1.png（透明框架）
		'latest'    => 11844, // ray-home-menu-latest.png
		'comic'     => 11845, // ray-home-menu-comic.png
		'blog'      => 11846, // ray-home-menu-wordpress-logo.png
		'sketch'    => 11849, // ray-home-menu-sketchbook-01.png（闭合封面）
		'sketch2'   => 11870, // ray-home-menu-sketchbook-02.png（翻开/hover 态）
		'works'     => 11848, // ray-home-menu-pictures.png
		'about'     => 11847, // ray-home-menu-avatar300.png
	);

	$fallbacks = array(
		'animation' => '/wp-content/uploads/2026/10/ray-home-menu-anim-alpha-1.png',
		'latest'    => '/wp-content/uploads/2026/10/ray-home-menu-latest.png',
		'comic'     => '/wp-content/uploads/2026/10/ray-home-menu-comic.png',
		'blog'      => '/wp-content/uploads/2026/10/ray-home-menu-wordpress-logo.png',
		'sketch'    => '/wp-content/uploads/2026/10/ray-home-menu-sketchbook-01.png',
		'sketch2'   => '/wp-content/uploads/2026/10/ray-home-menu-sketchbook-02.png',
		'works'     => '/wp-content/uploads/2026/10/ray-home-menu-pictures.png',
		'about'     => '/wp-content/uploads/2026/10/ray-home-menu-avatar300.png',
	);

	$url = '';
	if ( ! empty( $attachment_ids[ $key ] ) ) {
		$url = wp_get_attachment_image_url( (int) $attachment_ids[ $key ], 'full' );
	}
	if ( ! $url && ! empty( $fallbacks[ $key ] ) ) {
		$url = home_url( $fallbacks[ $key ] );
	}

	$cache[ $key ] = $url;
	return $url;
}

/**
 * 内联输出组件 CSS + JS（同一页面只输出一次）。
 */
function rzxme_skeuo_menu_inline_assets() {
	static $printed = false;

	if ( $printed ) {
		return '';
	}
	$printed = true;

	$theme_dir = get_stylesheet_directory();
	$css       = file_get_contents( $theme_dir . '/assets/skeuo-menu/rzx-skeuo-menu.css' );
	$js        = file_get_contents( $theme_dir . '/assets/skeuo-menu/rzx-skeuo-menu.js' );

	$css = str_replace(
		array( '__RZX_ANIM_ALPHA__', '__RZX_SKETCH__', '__RZX_SKETCH_02__' ),
		array(
			esc_url( rzxme_skeuo_menu_image( 'animation' ) ),
			esc_url( rzxme_skeuo_menu_image( 'sketch' ) ),
			esc_url( rzxme_skeuo_menu_image( 'sketch2' ) ),
		),
		$css
	);

	$out  = "\n<style id=\"rzx-skeuo-style\">\n" . $css . "\n</style>\n";
	$out .= "\n<script id=\"rzx-skeuo-script\">\n" . $js . "\n</script>\n";
	return $out;
}

/**
 * 区块渲染回调。
 */
function rzxme_render_skeuo_menu( $attributes = array(), $content = '', $block = null ) {
	$labels = array(
		'animation' => 'Animation',
		'latest'    => 'Latest',
		'comic'     => 'Comic',
		'blog'      => 'Blog',
		'sketch'    => 'SketchBooks',
		'works'     => 'Works',
		'about'     => 'About Me',
	);
	$order  = array( 'animation', 'latest', 'comic', 'blog', 'sketch', 'works', 'about' );

	$items = '';
	foreach ( $order as $key ) {
		$label = $labels[ $key ];

		if ( 'animation' === $key ) {
			// 波浪 + 顶部透明框架（::after 渲染框架，无 img）
			$icon = '<span class="rzx-skeuo__wave rzx-skeuo__wave--front"></span>';
		} elseif ( 'sketch' === $key ) {
			// 3D 翻书：封面/内页均由 ::before / ::after 背景图渲染
			$icon = '';
		} else {
			$url = rzxme_skeuo_menu_image( $key );
			$icon = '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $label ) . '" width="100" height="100" loading="lazy">';
		}

		$items .= sprintf(
			'<a class="rzx-skeuo__item rzx-skeuo__item--%1$s" href="#" aria-label="%2$s"><span class="rzx-skeuo__icon">%3$s</span><span class="rzx-skeuo__label">%2$s</span></a>',
			esc_attr( $key ),
			esc_html( $label ),
			$icon
		);
	}

	$out  = rzxme_skeuo_menu_inline_assets();
	$out .= '<div class="rzx-skeuo" role="navigation" aria-label="经典拟物菜单">';
	$out .= '<div class="rzx-skeuo__menu">' . $items . '</div>';
	$out .= '</div>';

	return $out;
}

/**
 * 注册区块。
 */
function rzxme_register_skeuo_menu_block() {
	register_block_type_from_metadata(
		__DIR__ . '/block.json',
		array(
			'render_callback' => 'rzxme_render_skeuo_menu',
		)
	);
}
add_action( 'init', 'rzxme_register_skeuo_menu_block' );
