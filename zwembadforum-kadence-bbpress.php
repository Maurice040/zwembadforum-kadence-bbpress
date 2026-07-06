<?php
/**
 * Plugin Name: Zwembadforum Kadence bbPress
 * Description: Modernere Kadence styling en lichte hygiene voor de bbPress frontend van Zwembadforum.
 * Version: 0.6.9
 * Author: Codex
 * Requires at least: 6.3
 * Requires PHP: 7.0
 * Text Domain: zwembadforum-kadence-bbpress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ZF_KADENCE_BBP_VERSION', '0.6.9' );
define( 'ZF_KADENCE_BBP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZF_KADENCE_BBP_URL', plugin_dir_url( __FILE__ ) );
define( 'ZF_KADENCE_BBP_OPTION', 'zf_kadence_bbp_settings' );
define( 'ZF_KADENCE_BBP_BASENAME', plugin_basename( __FILE__ ) );
define( 'ZF_KADENCE_BBP_UPDATE_TRANSIENT', 'zf_kadence_bbp_update_manifest' );
define( 'ZF_KADENCE_BBP_SEEMS_UTF8_TRACE_OPTION', 'zf_kadence_bbp_seems_utf8_trace' );
define( 'ZF_KADENCE_BBP_MU_COMPAT_FILE', WPMU_PLUGIN_DIR . '/zf-wp69-seems-utf8-compat.php' );

function zf_kadence_bbp_default_settings() {
	return array(
		'enable_forum_ui'         => 1,
		'style_site_header'       => 1,
		'style_front_page_widget' => 1,
		'compact_cards'           => 0,
		'style_affiliate_ads'     => 1,
		'managed_ads_enabled'     => 0,
		'managed_ads_safe_mode'   => 1,
		'managed_ads_label'       => 'Partner',
		'managed_ads_banners'     => '',
		'add_ugc_nofollow'        => 1,
		'remove_guest_editor_js'  => 1,
		'accent_color'            => '#087f8c',
		'accent_dark_color'       => '#0b3f4d',
		'max_content_width'       => 1120,
		'custom_css'              => '',
		'update_manifest_url'     => '',
	);
}

function zf_kadence_bbp_get_settings() {
	$settings = get_option( ZF_KADENCE_BBP_OPTION, array() );

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	return array_merge( zf_kadence_bbp_default_settings(), $settings );
}

function zf_kadence_bbp_sanitize_settings( $input ) {
	$defaults = zf_kadence_bbp_default_settings();
	$output   = array();

	foreach ( array( 'enable_forum_ui', 'style_site_header', 'style_front_page_widget', 'compact_cards', 'style_affiliate_ads', 'managed_ads_enabled', 'managed_ads_safe_mode', 'add_ugc_nofollow', 'remove_guest_editor_js' ) as $key ) {
		$output[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
	}

	$output['accent_color']      = sanitize_hex_color( isset( $input['accent_color'] ) ? $input['accent_color'] : $defaults['accent_color'] ) ?: $defaults['accent_color'];
	$output['accent_dark_color'] = sanitize_hex_color( isset( $input['accent_dark_color'] ) ? $input['accent_dark_color'] : $defaults['accent_dark_color'] ) ?: $defaults['accent_dark_color'];
	$output['max_content_width'] = absint( isset( $input['max_content_width'] ) ? $input['max_content_width'] : $defaults['max_content_width'] );
	$output['max_content_width'] = min( 1400, max( 860, $output['max_content_width'] ) );
	$output['managed_ads_label'] = sanitize_text_field( isset( $input['managed_ads_label'] ) ? $input['managed_ads_label'] : $defaults['managed_ads_label'] );
	$output['managed_ads_banners'] = zf_kadence_bbp_sanitize_ad_banners( isset( $input['managed_ads_banners'] ) ? $input['managed_ads_banners'] : $defaults['managed_ads_banners'] );
	$output['custom_css']        = zf_kadence_bbp_sanitize_custom_css( isset( $input['custom_css'] ) ? $input['custom_css'] : $defaults['custom_css'] );
	$output['update_manifest_url'] = esc_url_raw( isset( $input['update_manifest_url'] ) ? $input['update_manifest_url'] : $defaults['update_manifest_url'] );

	return $output;
}

function zf_kadence_bbp_sanitize_ad_banners( $banners ) {
	$lines = preg_split( '/\R/', (string) $banners );
	$clean = array();

	foreach ( $lines as $line ) {
		$line = trim( $line );

		if ( '' === $line || 0 === strpos( $line, '#' ) ) {
			$clean[] = $line;
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line ) );
		$desktop_image = esc_url_raw( isset( $parts[0] ) ? $parts[0] : '' );
		$mobile_image  = esc_url_raw( isset( $parts[1] ) ? $parts[1] : '' );
		$click_url     = esc_url_raw( isset( $parts[2] ) ? $parts[2] : '' );
		$alt_text      = sanitize_text_field( isset( $parts[3] ) ? $parts[3] : '' );
		$weight        = max( 1, absint( isset( $parts[4] ) ? $parts[4] : 1 ) );

		if ( empty( $desktop_image ) || empty( $click_url ) ) {
			continue;
		}

		$clean[] = implode( ' | ', array( $desktop_image, $mobile_image, $click_url, $alt_text, (string) $weight ) );
	}

	return trim( implode( "\n", $clean ) );
}

function zf_kadence_bbp_sanitize_custom_css( $css ) {
	$css = (string) $css;
	$css = str_replace( array( "\r\n", "\r" ), "\n", $css );
	$css = preg_replace( '#</?style[^>]*>#i', '', $css );
	$css = preg_replace( '/@import\b[^;]*;?/i', '', $css );
	$css = preg_replace( '/expression\s*\(/i', 'blocked-expression(', $css );
	$css = preg_replace( '/javascript\s*:/i', '', $css );

	return trim( $css );
}

function zf_kadence_bbp_activate() {
	if ( false === get_option( ZF_KADENCE_BBP_OPTION, false ) ) {
		add_option( ZF_KADENCE_BBP_OPTION, zf_kadence_bbp_default_settings() );
	}

	zf_kadence_bbp_install_mu_compat();
}
register_activation_hook( __FILE__, 'zf_kadence_bbp_activate' );

function zf_kadence_bbp_get_mu_compat_source() {
	return <<<'PHP'
<?php
/**
 * Plugin Name: Zwembadforum WP 6.9 seems_utf8 compat
 * Description: Vroege compatibiliteitslaag voor plugins die seems_utf8() nog aanroepen op WordPress 6.9.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ZF_KADENCE_BBP_SEEMS_UTF8_TRACE_OPTION' ) ) {
	define( 'ZF_KADENCE_BBP_SEEMS_UTF8_TRACE_OPTION', 'zf_kadence_bbp_seems_utf8_trace' );
}

function zf_mu_wp69_record_seems_utf8_trace( $function_name, $replacement, $version ) {
	if ( 'seems_utf8' !== $function_name ) {
		return;
	}

	$GLOBALS['zf_mu_wp69_suppress_seems_utf8_deprecation'] = true;

	$existing = get_option( ZF_KADENCE_BBP_SEEMS_UTF8_TRACE_OPTION, array() );
	if ( is_array( $existing ) && ! empty( $existing['frames'] ) ) {
		return;
	}

	$frames = array();
	foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 18 ) as $frame ) {
		$frames[] = array(
			'function' => isset( $frame['function'] ) ? (string) $frame['function'] : '',
			'class'    => isset( $frame['class'] ) ? (string) $frame['class'] : '',
			'file'     => isset( $frame['file'] ) ? wp_normalize_path( $frame['file'] ) : '',
			'line'     => isset( $frame['line'] ) ? absint( $frame['line'] ) : 0,
		);
	}

	update_option(
		ZF_KADENCE_BBP_SEEMS_UTF8_TRACE_OPTION,
		array(
			'captured_at'   => current_time( 'mysql' ),
			'function_name' => $function_name,
			'replacement'   => $replacement,
			'version'       => $version,
			'frames'        => $frames,
		),
		false
	);
}
add_action( 'deprecated_function_run', 'zf_mu_wp69_record_seems_utf8_trace', 1, 3 );

function zf_mu_wp69_suppress_seems_utf8_deprecation( $trigger ) {
	if ( empty( $GLOBALS['zf_mu_wp69_suppress_seems_utf8_deprecation'] ) ) {
		return $trigger;
	}

	$GLOBALS['zf_mu_wp69_suppress_seems_utf8_deprecation'] = false;

	return false;
}
add_filter( 'deprecated_function_trigger_error', 'zf_mu_wp69_suppress_seems_utf8_deprecation', 1 );

function zf_mu_wp69_should_suppress_error( $errno, $errstr ) {
	if ( ! in_array( $errno, array( E_USER_DEPRECATED, E_DEPRECATED ), true ) ) {
		return false;
	}

	return false !== strpos( wp_strip_all_tags( (string) $errstr ), 'seems_utf8' );
}

function zf_mu_wp69_error_handler( $errno, $errstr, $errfile = '', $errline = 0 ) {
	if ( zf_mu_wp69_should_suppress_error( $errno, $errstr ) ) {
		return true;
	}

	$previous = isset( $GLOBALS['zf_mu_wp69_previous_error_handler'] )
		? $GLOBALS['zf_mu_wp69_previous_error_handler']
		: null;

	if ( is_callable( $previous ) && 'zf_mu_wp69_error_handler' !== $previous ) {
		return (bool) call_user_func( $previous, $errno, $errstr, $errfile, $errline );
	}

	return false;
}

function zf_mu_wp69_register_error_handler() {
	$previous = set_error_handler( 'zf_mu_wp69_error_handler' );

	if ( is_callable( $previous ) && 'zf_mu_wp69_error_handler' !== $previous ) {
		$GLOBALS['zf_mu_wp69_previous_error_handler'] = $previous;
	}
}
zf_mu_wp69_register_error_handler();
add_action( 'plugins_loaded', 'zf_mu_wp69_register_error_handler', PHP_INT_MAX );
add_action( 'init', 'zf_mu_wp69_register_error_handler', PHP_INT_MAX );
PHP;
}

function zf_kadence_bbp_install_mu_compat() {
	if ( ! defined( 'WPMU_PLUGIN_DIR' ) || ! wp_is_writable( WP_CONTENT_DIR ) ) {
		return false;
	}

	if ( ! is_dir( WPMU_PLUGIN_DIR ) && ! wp_mkdir_p( WPMU_PLUGIN_DIR ) ) {
		return false;
	}

	if ( ! wp_is_writable( WPMU_PLUGIN_DIR ) ) {
		return false;
	}

	$source = zf_kadence_bbp_get_mu_compat_source();
	$current = file_exists( ZF_KADENCE_BBP_MU_COMPAT_FILE )
		? file_get_contents( ZF_KADENCE_BBP_MU_COMPAT_FILE )
		: '';

	if ( $current === $source ) {
		return true;
	}

	return false !== file_put_contents( ZF_KADENCE_BBP_MU_COMPAT_FILE, $source );
}
add_action( 'admin_init', 'zf_kadence_bbp_install_mu_compat' );

function zf_kadence_bbp_register_settings() {
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

function zf_kadence_bbp_register_rest_routes() {
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

	register_rest_route(
		'zf-kadence-bbpress/v1',
		'/diagnostics/seems-utf8',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => 'zf_kadence_bbp_can_manage_settings',
			'callback'            => 'zf_kadence_bbp_rest_get_seems_utf8_trace',
		)
	);

	register_rest_route(
		'zf-kadence-bbpress/v1',
		'/maintenance/install-mu-compat',
		array(
			'methods'             => WP_REST_Server::EDITABLE,
			'permission_callback' => 'zf_kadence_bbp_can_manage_settings',
			'callback'            => 'zf_kadence_bbp_rest_install_mu_compat',
		)
	);
}
add_action( 'rest_api_init', 'zf_kadence_bbp_register_rest_routes' );

function zf_kadence_bbp_can_manage_settings() {
	return current_user_can( 'manage_options' );
}

function zf_kadence_bbp_rest_get_settings() {
	return rest_ensure_response( zf_kadence_bbp_get_settings() );
}

function zf_kadence_bbp_rest_update_settings( $request ) {
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

function zf_kadence_bbp_rest_get_seems_utf8_trace() {
	$trace = get_option( ZF_KADENCE_BBP_SEEMS_UTF8_TRACE_OPTION, array() );

	if ( ! is_array( $trace ) ) {
		$trace = array();
	}

	$trace['mu_compat_installed'] = file_exists( ZF_KADENCE_BBP_MU_COMPAT_FILE );
	$trace['mu_compat_file']      = ZF_KADENCE_BBP_MU_COMPAT_FILE;

	return rest_ensure_response( $trace );
}

function zf_kadence_bbp_rest_install_mu_compat() {
	$installed = zf_kadence_bbp_install_mu_compat();

	return rest_ensure_response(
		array(
			'installed' => (bool) $installed,
			'file'      => ZF_KADENCE_BBP_MU_COMPAT_FILE,
		)
	);
}

function zf_kadence_bbp_add_settings_page() {
	add_options_page(
		'Zwembadforum bbPress',
		'Zwembadforum bbPress',
		'manage_options',
		'zf-kadence-bbpress',
		'zf_kadence_bbp_render_settings_page'
	);
}
add_action( 'admin_menu', 'zf_kadence_bbp_add_settings_page' );

function zf_kadence_bbp_render_checkbox( $settings, $key, $label, $description ) {
	?>
	<label>
		<input type="checkbox" name="<?php echo esc_attr( ZF_KADENCE_BBP_OPTION . '[' . $key . ']' ); ?>" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?>>
		<?php echo esc_html( $label ); ?>
	</label>
	<p class="description"><?php echo esc_html( $description ); ?></p>
	<?php
}

function zf_kadence_bbp_render_settings_page() {
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
						<?php zf_kadence_bbp_render_checkbox( $settings, 'style_site_header', 'Kadence header stylen', 'Geeft de siteheader weer de strakke Zwembadforum-balk en compacte navigatie.' ); ?>
						<?php zf_kadence_bbp_render_checkbox( $settings, 'style_front_page_widget', 'Voorpagina forumwidget stylen', 'Laadt dezelfde styling en het Forum CSS veld ook op de voorpagina, voor het forumoverzicht in de widget.' ); ?>
						<?php zf_kadence_bbp_render_checkbox( $settings, 'compact_cards', 'Compactere forumkaarten', 'Maakt lijsten iets dichter voor pagina’s met veel onderwerpen.' ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">Advertentieblok</th>
					<td>
						<?php zf_kadence_bbp_render_checkbox( $settings, 'style_affiliate_ads', 'Bouwzelfjezwembad bannerpositie stylen', 'Behoudt de bestaande advertentie onder de vraag en geeft desktop/mobiel banners nette spacing.' ); ?>
						<?php zf_kadence_bbp_render_checkbox( $settings, 'managed_ads_enabled', 'Advertenties beheren vanuit deze plugin', 'Toont zelf een banner onder de vraag, zodat de losse bbp affiliate ads plugin later uit kan.' ); ?>
						<?php zf_kadence_bbp_render_checkbox( $settings, 'managed_ads_safe_mode', 'Niet tonen zolang bbp affiliate ads actief is', 'Voorkomt dubbele advertenties tijdens de overstap. Zet pas uit als je bewust beide wilt testen.' ); ?>
						<p>
							<label for="zf-managed-ads-label"><strong>Advertentielabel</strong></label><br>
							<input id="zf-managed-ads-label" type="text" class="regular-text" name="<?php echo esc_attr( ZF_KADENCE_BBP_OPTION . '[managed_ads_label]' ); ?>" value="<?php echo esc_attr( $settings['managed_ads_label'] ); ?>">
						</p>
						<p>
							<label for="zf-managed-ads-banners"><strong>Banners</strong></label><br>
							<textarea id="zf-managed-ads-banners" class="large-text code" rows="8" name="<?php echo esc_attr( ZF_KADENCE_BBP_OPTION . '[managed_ads_banners]' ); ?>" spellcheck="false" placeholder="desktop-afbeelding | mobiel-afbeelding | klik-url | alt-tekst | gewicht"><?php echo esc_textarea( $settings['managed_ads_banners'] ); ?></textarea>
						</p>
						<p class="description">Een banner per regel: <code>desktop-afbeelding | mobiel-afbeelding | klik-url | alt-tekst | gewicht</code>. Laat mobiel-afbeelding leeg om dezelfde afbeelding te gebruiken. Regels met <code>#</code> kun je als notitie gebruiken.</p>
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
					<th scope="row"><label for="zf-custom-css">Forum CSS</label></th>
					<td>
						<textarea id="zf-custom-css" class="large-text code" rows="16" name="<?php echo esc_attr( ZF_KADENCE_BBP_OPTION . '[custom_css]' ); ?>" spellcheck="false"><?php echo esc_textarea( $settings['custom_css'] ); ?></textarea>
						<p class="description">Wordt geladen op bbPress/forum-schermen en, als die optie aanstaat, op de voorpagina. Gebruik bij voorkeur <code>.zf-forum-ui</code> als scope.</p>
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

function zf_kadence_bbp_is_forum_screen() {
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

function zf_kadence_bbp_is_front_page_widget_screen( $settings ) {
	return ! empty( $settings['style_front_page_widget'] ) && is_front_page();
}

function zf_kadence_bbp_should_load_assets( $settings ) {
	if ( empty( $settings['enable_forum_ui'] ) ) {
		return false;
	}

	return ! empty( $settings['style_site_header'] ) || zf_kadence_bbp_is_forum_screen() || zf_kadence_bbp_is_front_page_widget_screen( $settings );
}

function zf_kadence_bbp_enqueue_assets() {
	$settings = zf_kadence_bbp_get_settings();

	if ( ! zf_kadence_bbp_should_load_assets( $settings ) ) {
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

	$custom_css = trim( (string) ( isset( $settings['custom_css'] ) ? $settings['custom_css'] : '' ) );

	if ( '' !== $custom_css ) {
		wp_add_inline_style( 'zf-kadence-bbpress', "\n/* Zwembadforum custom forum CSS */\n" . $custom_css );
	}
}
add_action( 'wp_enqueue_scripts', 'zf_kadence_bbp_enqueue_assets', 50 );

function zf_kadence_bbp_body_classes( $classes ) {
	$settings = zf_kadence_bbp_get_settings();

	if ( zf_kadence_bbp_should_load_assets( $settings ) ) {
		if ( ! empty( $settings['style_site_header'] ) ) {
			$classes[] = 'zf-kadence-header';
		}

		if ( zf_kadence_bbp_is_forum_screen() || zf_kadence_bbp_is_front_page_widget_screen( $settings ) ) {
			$classes[] = 'zf-forum-ui';
		}

		if ( zf_kadence_bbp_is_front_page_widget_screen( $settings ) ) {
			$classes[] = 'zf-forum-view-front-widget';
		}

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

function zf_kadence_bbp_add_ugc_to_external_links( $content ) {
	$settings = zf_kadence_bbp_get_settings();

	if ( empty( $settings['add_ugc_nofollow'] ) || ! zf_kadence_bbp_is_forum_screen() || false === stripos( $content, '<a ' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'/<a\s+([^>]*href=[\'"]https?:\/\/[^\'"]+[\'"][^>]*)>/i',
		static function ( $matches ) {
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

function zf_kadence_bbp_remove_low_value_forum_assets() {
	if ( ! zf_kadence_bbp_is_forum_screen() ) {
		return;
	}

	$settings = zf_kadence_bbp_get_settings();

	if ( ! empty( $settings['remove_guest_editor_js'] ) && ! is_user_logged_in() ) {
		wp_dequeue_script( 'bbpress-editor' );
	}
}
add_action( 'wp_enqueue_scripts', 'zf_kadence_bbp_remove_low_value_forum_assets', 100 );

function zf_kadence_bbp_format_user_display_name( $display_name ) {
	$display_name = (string) $display_name;

	if ( '' === $display_name ) {
		return $display_name;
	}

	if ( function_exists( 'mb_check_encoding' ) && function_exists( 'mb_convert_encoding' ) ) {
		if ( ! mb_check_encoding( $display_name, 'UTF-8' ) ) {
			$converted = mb_convert_encoding( $display_name, 'UTF-8', 'ISO-8859-1' );

			if ( is_string( $converted ) && '' !== $converted ) {
				return $converted;
			}
		}

		return $display_name;
	}

	if ( function_exists( 'wp_is_valid_utf8' ) && ! wp_is_valid_utf8( $display_name ) && function_exists( 'iconv' ) ) {
		$converted = iconv( 'ISO-8859-1', 'UTF-8//IGNORE', $display_name );

		if ( false !== $converted && '' !== $converted ) {
			return $converted;
		}
	}

	return $display_name;
}

function zf_kadence_bbp_patch_bbpress_display_name_filter() {
	if ( ! function_exists( 'remove_filter' ) ) {
		return;
	}

	remove_filter( 'bbp_get_topic_author_display_name', 'bbp_format_user_display_name' );
	remove_filter( 'bbp_get_reply_author_display_name', 'bbp_format_user_display_name' );

	add_filter( 'bbp_get_topic_author_display_name', 'zf_kadence_bbp_format_user_display_name', 5 );
	add_filter( 'bbp_get_reply_author_display_name', 'zf_kadence_bbp_format_user_display_name', 5 );
}
add_action( 'init', 'zf_kadence_bbp_patch_bbpress_display_name_filter', 20 );
add_action( 'plugins_loaded', 'zf_kadence_bbp_patch_bbpress_display_name_filter', PHP_INT_MAX );
add_action( 'bbp_init', 'zf_kadence_bbp_patch_bbpress_display_name_filter', PHP_INT_MAX );
add_action( 'wp', 'zf_kadence_bbp_patch_bbpress_display_name_filter', 1 );

function zf_kadence_bbp_record_seems_utf8_trace( $function_name, $replacement, $version ) {
	if ( 'seems_utf8' !== $function_name ) {
		return;
	}

	$GLOBALS['zf_kadence_bbp_suppress_seems_utf8_deprecation'] = true;

	$existing = get_option( ZF_KADENCE_BBP_SEEMS_UTF8_TRACE_OPTION, array() );
	if ( is_array( $existing ) && ! empty( $existing['frames'] ) ) {
		return;
	}

	$frames = array();
	foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 18 ) as $frame ) {
		$file = isset( $frame['file'] ) ? wp_normalize_path( $frame['file'] ) : '';

		$frames[] = array(
			'function' => isset( $frame['function'] ) ? (string) $frame['function'] : '',
			'class'    => isset( $frame['class'] ) ? (string) $frame['class'] : '',
			'file'     => $file,
			'line'     => isset( $frame['line'] ) ? absint( $frame['line'] ) : 0,
		);
	}

	update_option(
		ZF_KADENCE_BBP_SEEMS_UTF8_TRACE_OPTION,
		array(
			'captured_at'   => current_time( 'mysql' ),
			'function_name' => $function_name,
			'replacement'   => $replacement,
			'version'       => $version,
			'frames'        => $frames,
		),
		false
	);
}
add_action( 'deprecated_function_run', 'zf_kadence_bbp_record_seems_utf8_trace', 1, 3 );

function zf_kadence_bbp_suppress_seems_utf8_deprecation( $trigger ) {
	if ( empty( $GLOBALS['zf_kadence_bbp_suppress_seems_utf8_deprecation'] ) ) {
		return $trigger;
	}

	$GLOBALS['zf_kadence_bbp_suppress_seems_utf8_deprecation'] = false;

	return false;
}
add_filter( 'deprecated_function_trigger_error', 'zf_kadence_bbp_suppress_seems_utf8_deprecation', 1 );

function zf_kadence_bbp_is_legacy_ad_plugin_active() {
	$active_plugins = (array) get_option( 'active_plugins', array() );

	return in_array( 'bbp-affiliate-ads/bbp-affiliate-ads.php', $active_plugins, true )
		|| in_array( 'bbp-affiliate-ads/bbp-affiliate-ads', $active_plugins, true );
}

function zf_kadence_bbp_parse_managed_ads( $source ) {
	$ads = array();

	foreach ( preg_split( '/\R/', $source ) as $line ) {
		$line = trim( $line );

		if ( '' === $line || 0 === strpos( $line, '#' ) ) {
			continue;
		}

		$parts         = array_map( 'trim', explode( '|', $line ) );
		$desktop_image = esc_url_raw( isset( $parts[0] ) ? $parts[0] : '' );
		$mobile_image  = esc_url_raw( isset( $parts[1] ) ? $parts[1] : '' );
		$click_url     = esc_url_raw( isset( $parts[2] ) ? $parts[2] : '' );
		$alt_text      = sanitize_text_field( isset( $parts[3] ) ? $parts[3] : '' );
		$weight        = max( 1, absint( isset( $parts[4] ) ? $parts[4] : 1 ) );

		if ( empty( $desktop_image ) || empty( $click_url ) ) {
			continue;
		}

		$ads[] = array(
			'desktop_image' => $desktop_image,
			'mobile_image'  => $mobile_image ?: $desktop_image,
			'click_url'     => $click_url,
			'alt_text'      => $alt_text ?: 'Bouwzelfjezwembad',
			'weight'        => $weight,
		);
	}

	return $ads;
}

function zf_kadence_bbp_pick_managed_ad( $ads ) {
	if ( empty( $ads ) ) {
		return null;
	}

	$total_weight = array_sum( wp_list_pluck( $ads, 'weight' ) );
	$needle       = wp_rand( 1, max( 1, $total_weight ) );
	$current      = 0;

	foreach ( $ads as $ad ) {
		$current += (int) $ad['weight'];

		if ( $needle <= $current ) {
			return $ad;
		}
	}

	return $ads[0];
}

function zf_kadence_bbp_render_managed_topic_ad() {
	static $rendered = false;

	if ( $rendered || ! function_exists( 'bbp_is_single_topic' ) || ! bbp_is_single_topic() ) {
		return;
	}

	$settings = zf_kadence_bbp_get_settings();

	if ( empty( $settings['managed_ads_enabled'] ) ) {
		return;
	}

	if ( ! empty( $settings['managed_ads_safe_mode'] ) && zf_kadence_bbp_is_legacy_ad_plugin_active() ) {
		return;
	}

	$ad = zf_kadence_bbp_pick_managed_ad( zf_kadence_bbp_parse_managed_ads( (string) $settings['managed_ads_banners'] ) );

	if ( ! $ad ) {
		return;
	}

	$rendered = true;
	$label    = trim( (string) ( isset( $settings['managed_ads_label'] ) ? $settings['managed_ads_label'] : '' ) );
	?>
	<div class="zf-managed-topic-ad" aria-label="<?php echo esc_attr( $label ?: 'Advertentie' ); ?>">
		<?php if ( '' !== $label ) : ?>
			<span class="zf-managed-topic-ad__label"><?php echo esc_html( $label ); ?></span>
		<?php endif; ?>
		<a class="zf-managed-topic-ad__link" href="<?php echo esc_url( $ad['click_url'] ); ?>" target="_blank" rel="sponsored nofollow noopener">
			<picture>
				<source media="(max-width: 700px)" srcset="<?php echo esc_url( $ad['mobile_image'] ); ?>">
				<img src="<?php echo esc_url( $ad['desktop_image'] ); ?>" alt="<?php echo esc_attr( $ad['alt_text'] ); ?>" loading="lazy" decoding="async">
			</picture>
		</a>
	</div>
	<?php
}
add_action( 'bbp_theme_after_topic_content', 'zf_kadence_bbp_render_managed_topic_ad', 30 );
add_action( 'bbp_template_after_lead_topic', 'zf_kadence_bbp_render_managed_topic_ad', 5 );

function zf_kadence_bbp_get_update_manifest( $force = false ) {
	$settings     = zf_kadence_bbp_get_settings();
	$manifest_url = isset( $settings['update_manifest_url'] ) ? $settings['update_manifest_url'] : '';

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

function zf_kadence_bbp_plugin_info( $result, $action, $args ) {
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

function zf_kadence_bbp_clear_update_cache() {
	delete_site_transient( ZF_KADENCE_BBP_UPDATE_TRANSIENT );
}
add_action( 'upgrader_process_complete', 'zf_kadence_bbp_clear_update_cache' );
