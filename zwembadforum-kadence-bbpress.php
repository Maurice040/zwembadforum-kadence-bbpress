<?php
/**
 * Plugin Name: Zwembadforum Kadence bbPress
 * Description: Modernere Kadence styling en lichte hygiene voor de bbPress frontend van Zwembadforum.
 * Version: 0.4.1
 * Author: Codex
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Text Domain: zwembadforum-kadence-bbpress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ZF_KADENCE_BBP_VERSION', '0.4.1' );
define( 'ZF_KADENCE_BBP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZF_KADENCE_BBP_URL', plugin_dir_url( __FILE__ ) );
define( 'ZF_KADENCE_BBP_OPTION', 'zf_kadence_bbp_settings' );
define( 'ZF_KADENCE_BBP_BASENAME', plugin_basename( __FILE__ ) );
define( 'ZF_KADENCE_BBP_UPDATE_TRANSIENT', 'zf_kadence_bbp_update_manifest' );

function zf_kadence_bbp_default_settings(): array {
	return array(
		'enable_forum_ui'         => 1,
		'compact_cards'           => 0,
		'style_affiliate_ads'     => 1,
		'add_ugc_nofollow'        => 1,
		'remove_guest_editor_js'  => 1,
		'accent_color'            => '#087f8c',
		'accent_dark_color'       => '#0b3f4d',
		'max_content_width'       => 1120,
		'update_manifest_url'     => '',
	);
}

function zf_kadence_bbp_get_settings(): array {
	$settings = get_option( ZF_KADENCE_BBP_OPTION, array() );

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	return array_merge( zf_kadence_bbp_default_settings(), $settings );
}

function zf_kadence_bbp_sanitize_settings( array $input ): array {
	$defaults = zf_kadence_bbp_default_settings();
	$output   = array();

	foreach ( array( 'enable_forum_ui', 'compact_cards', 'style_affiliate_ads', 'add_ugc_nofollow', 'remove_guest_editor_js' ) as $key ) {
		$output[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
	}

	$output['accent_color']      = sanitize_hex_color( $input['accent_color'] ?? $defaults['accent_color'] ) ?: $defaults['accent_color'];
	$output['accent_dark_color'] = sanitize_hex_color( $input['accent_dark_color'] ?? $defaults['accent_dark_color'] ) ?: $defaults['accent_dark_color'];
	$output['max_content_width'] = absint( $input['max_content_width'] ?? $defaults['max_content_width'] );
	$output['max_content_width'] = min( 1400, max( 860, $output['max_content_width'] ) );
	$output['update_manifest_url'] = esc_url_raw( $input['update_manifest_url'] ?? $defaults['update_manifest_url'] );

	return $output;
}

function zf_kadence_bbp_activate(): void {
	if ( false === get_option( ZF_KADENCE_BBP_OPTION, false ) ) {
		add_option( ZF_KADENCE_BBP_OPTION, zf_kadence_bbp_default_settings() );
	}
}
register_activation_hook( __FILE__, 'zf_kadence_bbp_activate' );

function zf_kadence_bbp_register_settings(): void {
	register_setting(
		'zf_kadence_bbp',
		ZF_KADENCE_BBP_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'zf_kadence_bbp_sanitize_settings',
			'default'           => zf_kadence_bbp_default_settings(),
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', 'zf_kadence_bbp_register_settings' );

function zf_kadence_bbp_register_rest_routes(): void {
	register_rest_route(
		'zf-kadence-bbpress/v1',
		'/settings',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => 'zf_kadence_bbp_can_manage_settings',
				'callback'            => 'zf_kadence_bbp_rest_get_settings',
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'permission_callback' => 'zf_kadence_bbp_can_manage_settings',
				'callback'            => 'zf_kadence_bbp_rest_update_settings',
			),
		)
	);
}
add_action( 'rest_api_init', 'zf_kadence_bbp_register_rest_routes' );

function zf_kadence_bbp_can_manage_settings(): bool {
	return current_user_can( 'manage_options' );
}

function zf_kadence_bbp_rest_get_settings(): WP_REST_Response {
	return rest_ensure_response( zf_kadence_bbp_get_settings() );
}

function zf_kadence_bbp_rest_update_settings( WP_REST_Request $request ): WP_REST_Response {
	$current = zf_kadence_bbp_get_settings();
	$input   = $request->get_json_params();

	if ( ! is_array( $input ) ) {
		$input = $request->get_params();
	}

	$settings = zf_kadence_bbp_sanitize_settings( array_merge( $current, $input ) );
	update_option( ZF_KADENCE_BBP_OPTION, $settings );
	zf_kadence_bbp_clear_update_cache();

	return rest_ensure_response( $settings );
}

function zf_kadence_bbp_add_settings_page(): void {
	add_options_page(
		'Zwembadforum bbPress',
		'Zwembadforum bbPress',
		'manage_options',
		'zf-kadence-bbpress',
		'zf_kadence_bbp_render_settings_page'
	);
}
add_action( 'admin_menu', 'zf_kadence_bbp_add_settings_page' );

function zf_kadence_bbp_render_checkbox( array $settings, string $key, string $label, string $description ): void {
	?>
	<label>
		<input type="checkbox" name="<?php echo esc_attr( ZF_KADENCE_BBP_OPTION . '[' . $key . ']' ); ?>" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?>>
		<?php echo esc_html( $label ); ?>
	</label>
	<p class="description"><?php echo esc_html( $description ); ?></p>
	<?php
}

function zf_kadence_bbp_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = zf_kadence_bbp_get_settings();
	?>
	<div class="wrap">
		<h1>Zwembadforum bbPress</h1>
		<p>Beheer de Kadence/bbPress frontendlaag zonder bbPress templates of forumdata aan te passen.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'zf_kadence_bbp' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Forum uiterlijk</th>
					<td>
						<?php zf_kadence_bbp_render_checkbox( $settings, 'enable_forum_ui', 'Kadence forumstyling inschakelen', 'Laadt de moderne forumlayout op bbPress pagina’s.' ); ?>
						<?php zf_kadence_bbp_render_checkbox( $settings, 'compact_cards', 'Compactere forumkaarten', 'Maakt lijsten iets dichter voor pagina’s met veel onderwerpen.' ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">Advertentieblok</th>
					<td>
						<?php zf_kadence_bbp_render_checkbox( $settings, 'style_affiliate_ads', 'Bouwzelfjezwembad bannerpositie stylen', 'Behoudt de bestaande advertentie onder de vraag en geeft desktop/mobiel banners nette spacing.' ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">Performance en spam</th>
					<td>
						<?php zf_kadence_bbp_render_checkbox( $settings, 'add_ugc_nofollow', 'UGC/nofollow op externe forumlinks', 'Voegt rel="ugc nofollow" toe aan links in topics en reacties.' ); ?>
						<?php zf_kadence_bbp_render_checkbox( $settings, 'remove_guest_editor_js', 'bbPress editor JS uitschakelen voor gasten', 'Vermindert frontend JavaScript voor uitgelogde bezoekers.' ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="zf-accent-color">Accentkleur</label></th>
					<td>
						<input id="zf-accent-color" type="text" class="regular-text" name="<?php echo esc_attr( ZF_KADENCE_BBP_OPTION . '[accent_color]' ); ?>" value="<?php echo esc_attr( $settings['accent_color'] ); ?>">
						<p class="description">Hex kleur, bijvoorbeeld #2b6cb0.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="zf-accent-dark-color">Donkere accentkleur</label></th>
					<td>
						<input id="zf-accent-dark-color" type="text" class="regular-text" name="<?php echo esc_attr( ZF_KADENCE_BBP_OPTION . '[accent_dark_color]' ); ?>" value="<?php echo esc_attr( $settings['accent_dark_color'] ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="zf-max-content-width">Maximale forum breedte</label></th>
					<td>
						<input id="zf-max-content-width" type="number" min="860" max="1400" step="10" name="<?php echo esc_attr( ZF_KADENCE_BBP_OPTION . '[max_content_width]' ); ?>" value="<?php echo esc_attr( (string) $settings['max_content_width'] ); ?>">
						<p class="description">Tussen 860 en 1400 pixels.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="zf-update-manifest-url">Update manifest URL</label></th>
					<td>
						<input id="zf-update-manifest-url" type="url" class="large-text code" name="<?php echo esc_attr( ZF_KADENCE_BBP_OPTION . '[update_manifest_url]' ); ?>" value="<?php echo esc_attr( $settings['update_manifest_url'] ); ?>" placeholder="https://raw.githubusercontent.com/.../update.json">
						<p class="description">Gebruik dit om toekomstige pluginupdates via WordPress/GitHub te installeren zonder losse zip-upload.</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function zf_kadence_bbp_is_forum_screen(): bool {
	if ( function_exists( 'is_bbpress' ) && is_bbpress() ) {
		return true;
	}

	if ( is_post_type_archive( array( 'forum', 'topic', 'reply' ) ) ) {
		return true;
	}

	if ( is_singular( array( 'forum', 'topic', 'reply' ) ) ) {
		return true;
	}

	return false;
}

function zf_kadence_bbp_enqueue_assets(): void {
	if ( ! zf_kadence_bbp_is_forum_screen() ) {
		return;
	}

	$settings = zf_kadence_bbp_get_settings();

	if ( empty( $settings['enable_forum_ui'] ) ) {
		return;
	}

	wp_enqueue_style(
		'zf-kadence-bbpress',
		ZF_KADENCE_BBP_URL . 'assets/forum.css',
		array(),
		ZF_KADENCE_BBP_VERSION
	);

	$inline_css = sprintf(
		':root{--zf-forum-accent:%1$s;--zf-forum-accent-dark:%2$s;--zf-forum-max-width:%3$dpx;}',
		esc_html( $settings['accent_color'] ),
		esc_html( $settings['accent_dark_color'] ),
		absint( $settings['max_content_width'] )
	);

	wp_add_inline_style( 'zf-kadence-bbpress', $inline_css );
}
add_action( 'wp_enqueue_scripts', 'zf_kadence_bbp_enqueue_assets', 50 );

function zf_kadence_bbp_body_classes( array $classes ): array {
	$settings = zf_kadence_bbp_get_settings();

	if ( zf_kadence_bbp_is_forum_screen() && ! empty( $settings['enable_forum_ui'] ) ) {
		$classes[] = 'zf-forum-ui';

		if ( function_exists( 'bbp_is_forum_archive' ) && bbp_is_forum_archive() ) {
			$classes[] = 'zf-forum-view-index';
		}

		if ( function_exists( 'bbp_is_single_forum' ) && bbp_is_single_forum() ) {
			$classes[] = 'zf-forum-view-list';
		}

		if ( function_exists( 'bbp_is_single_topic' ) && bbp_is_single_topic() ) {
			$classes[] = 'zf-forum-view-topic';
		}

		if ( ! empty( $settings['compact_cards'] ) ) {
			$classes[] = 'zf-forum-compact';
		}

		if ( ! empty( $settings['style_affiliate_ads'] ) ) {
			$classes[] = 'zf-forum-ad-styling';
		}
	}

	return $classes;
}
add_filter( 'body_class', 'zf_kadence_bbp_body_classes' );

function zf_kadence_bbp_add_ugc_to_external_links( string $content ): string {
	$settings = zf_kadence_bbp_get_settings();

	if ( empty( $settings['add_ugc_nofollow'] ) || ! zf_kadence_bbp_is_forum_screen() || false === stripos( $content, '<a ' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'/<a\s+([^>]*href=[\'"]https?:\/\/[^\'"]+[\'"][^>]*)>/i',
		static function ( array $matches ): string {
			$attrs = $matches[1];

			if ( preg_match( '/\srel=[\'"]([^\'"]*)[\'"]/i', $attrs, $rel_match ) ) {
				$rel_values = preg_split( '/\s+/', strtolower( $rel_match[1] ) );
				$rel_values = array_filter( array_unique( array_merge( $rel_values, array( 'ugc', 'nofollow' ) ) ) );
				$attrs = preg_replace( '/\srel=[\'"][^\'"]*[\'"]/i', ' rel="' . esc_attr( implode( ' ', $rel_values ) ) . '"', $attrs );
			} else {
				$attrs .= ' rel="ugc nofollow"';
			}

			return '<a ' . $attrs . '>';
		},
		$content
	);
}
add_filter( 'bbp_get_reply_content', 'zf_kadence_bbp_add_ugc_to_external_links', 20 );
add_filter( 'bbp_get_topic_content', 'zf_kadence_bbp_add_ugc_to_external_links', 20 );

function zf_kadence_bbp_remove_low_value_forum_assets(): void {
	if ( ! zf_kadence_bbp_is_forum_screen() ) {
		return;
	}

	$settings = zf_kadence_bbp_get_settings();

	if ( ! empty( $settings['remove_guest_editor_js'] ) && ! is_user_logged_in() ) {
		wp_dequeue_script( 'bbpress-editor' );
	}
}
add_action( 'wp_enqueue_scripts', 'zf_kadence_bbp_remove_low_value_forum_assets', 100 );

function zf_kadence_bbp_get_update_manifest( bool $force = false ): ?array {
	$settings     = zf_kadence_bbp_get_settings();
	$manifest_url = $settings['update_manifest_url'] ?? '';

	if ( empty( $manifest_url ) ) {
		return null;
	}

	if ( ! $force ) {
		$cached = get_site_transient( ZF_KADENCE_BBP_UPDATE_TRANSIENT );
		if ( is_array( $cached ) ) {
			return $cached;
		}
	}

	$response = wp_remote_get(
		$manifest_url,
		array(
			'timeout' => 8,
			'headers' => array(
				'Accept' => 'application/json',
			),
		)
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return null;
	}

	$manifest = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_array( $manifest ) || empty( $manifest['version'] ) || empty( $manifest['download_url'] ) ) {
		return null;
	}

	$manifest['version']      = sanitize_text_field( (string) $manifest['version'] );
	$manifest['download_url'] = esc_url_raw( (string) $manifest['download_url'] );
	$manifest['homepage']     = empty( $manifest['homepage'] ) ? 'https://zwembadforum.eu' : esc_url_raw( (string) $manifest['homepage'] );
	$manifest['tested']       = empty( $manifest['tested'] ) ? '' : sanitize_text_field( (string) $manifest['tested'] );
	$manifest['requires']     = empty( $manifest['requires'] ) ? '' : sanitize_text_field( (string) $manifest['requires'] );
	$manifest['requires_php'] = empty( $manifest['requires_php'] ) ? '' : sanitize_text_field( (string) $manifest['requires_php'] );
	$manifest['changelog']    = empty( $manifest['changelog'] ) ? '' : wp_kses_post( (string) $manifest['changelog'] );

	set_site_transient( ZF_KADENCE_BBP_UPDATE_TRANSIENT, $manifest, 6 * HOUR_IN_SECONDS );

	return $manifest;
}

function zf_kadence_bbp_check_for_update( $transient ) {
	if ( empty( $transient->checked ) || empty( $transient->checked[ ZF_KADENCE_BBP_BASENAME ] ) ) {
		return $transient;
	}

	$manifest = zf_kadence_bbp_get_update_manifest();

	if ( ! $manifest || ! version_compare( $manifest['version'], ZF_KADENCE_BBP_VERSION, '>' ) ) {
		return $transient;
	}

	$transient->response[ ZF_KADENCE_BBP_BASENAME ] = (object) array(
		'id'            => ZF_KADENCE_BBP_BASENAME,
		'slug'          => dirname( ZF_KADENCE_BBP_BASENAME ),
		'plugin'        => ZF_KADENCE_BBP_BASENAME,
		'new_version'   => $manifest['version'],
		'url'           => $manifest['homepage'],
		'package'       => $manifest['download_url'],
		'tested'        => $manifest['tested'],
		'requires'      => $manifest['requires'],
		'requires_php'  => $manifest['requires_php'],
	);

	return $transient;
}
add_filter( 'pre_set_site_transient_update_plugins', 'zf_kadence_bbp_check_for_update' );

function zf_kadence_bbp_plugin_info( $result, string $action, object $args ) {
	if ( 'plugin_information' !== $action || empty( $args->slug ) || dirname( ZF_KADENCE_BBP_BASENAME ) !== $args->slug ) {
		return $result;
	}

	$manifest = zf_kadence_bbp_get_update_manifest();

	if ( ! $manifest ) {
		return $result;
	}

	return (object) array(
		'name'          => 'Zwembadforum Kadence bbPress',
		'slug'          => dirname( ZF_KADENCE_BBP_BASENAME ),
		'version'       => $manifest['version'],
		'author'        => '<a href="https://zwembadforum.eu">Zwembadforum</a>',
		'homepage'      => $manifest['homepage'],
		'requires'      => $manifest['requires'],
		'tested'        => $manifest['tested'],
		'requires_php'  => $manifest['requires_php'],
		'download_link' => $manifest['download_url'],
		'sections'      => array(
			'description' => 'Modernere Kadence styling en lichte hygiene voor de bbPress frontend van Zwembadforum.',
			'changelog'   => $manifest['changelog'],
		),
	);
}
add_filter( 'plugins_api', 'zf_kadence_bbp_plugin_info', 20, 3 );

function zf_kadence_bbp_clear_update_cache(): void {
	delete_site_transient( ZF_KADENCE_BBP_UPDATE_TRANSIENT );
}
add_action( 'upgrader_process_complete', 'zf_kadence_bbp_clear_update_cache' );
