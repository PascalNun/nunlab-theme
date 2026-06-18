<?php
/**
 * Private WordPress admin view for local server-log analytics.
 *
 * The public site does not load analytics JavaScript. This screen only reads
 * the aggregated JSON generated on the VPS from nginx access logs.
 *
 * @package nunlab-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return candidate locations for the generated analytics summary.
 *
 * @return string[]
 */
function nunlab_analytics_summary_paths() {
	$paths = array();

	if ( defined( 'NUNLAB_ANALYTICS_SUMMARY_PATH' ) ) {
		$paths[] = (string) NUNLAB_ANALYTICS_SUMMARY_PATH;
	}

	$paths[] = '/var/lib/nunlab-analytics/summary.json';
	$paths[] = WP_CONTENT_DIR . '/nunlab-analytics/summary.json';

	return array_values( array_unique( $paths ) );
}

/**
 * Read the generated analytics summary.
 *
 * @return array{data: array|null, path: string, error: string}
 */
function nunlab_analytics_read_summary() {
	foreach ( nunlab_analytics_summary_paths() as $path ) {
		if ( ! is_readable( $path ) ) {
			continue;
		}

		$contents = file_get_contents( $path );

		if ( false === $contents ) {
			return array(
				'data'  => null,
				'path'  => $path,
				'error' => __( 'The analytics summary exists, but WordPress could not read it.', 'nunlab-theme' ),
			);
		}

		$data = json_decode( $contents, true );

		if ( ! is_array( $data ) ) {
			return array(
				'data'  => null,
				'path'  => $path,
				'error' => __( 'The analytics summary is not valid JSON.', 'nunlab-theme' ),
			);
		}

		return array(
			'data'  => $data,
			'path'  => $path,
			'error' => '',
		);
	}

	return array(
		'data'  => null,
		'path'  => implode( ', ', nunlab_analytics_summary_paths() ),
		'error' => __( 'No analytics summary has been generated yet.', 'nunlab-theme' ),
	);
}

/**
 * Register the private admin page.
 */
function nunlab_register_analytics_admin_page() {
	add_dashboard_page(
		__( 'N:UN Analytics', 'nunlab-theme' ),
		__( 'N:UN Analytics', 'nunlab-theme' ),
		'manage_options',
		'nunlab-analytics',
		'nunlab_render_analytics_admin_page'
	);
}
add_action( 'admin_menu', 'nunlab_register_analytics_admin_page' );

/**
 * Add a compact dashboard widget on the main WordPress dashboard.
 */
function nunlab_register_analytics_dashboard_widget() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'nunlab_analytics_overview',
		__( 'N:UN Analytics', 'nunlab-theme' ),
		'nunlab_render_analytics_dashboard_widget'
	);
}
add_action( 'wp_dashboard_setup', 'nunlab_register_analytics_dashboard_widget' );

/**
 * Enqueue the analytics admin stylesheet only where it is used.
 *
 * @param string $hook_suffix Current admin screen hook.
 */
function nunlab_enqueue_analytics_admin_assets( $hook_suffix ) {
	if ( 'index.php' !== $hook_suffix && false === strpos( $hook_suffix, 'nunlab-analytics' ) ) {
		return;
	}

	$style_path    = NUNLAB_THEME_DIR . '/assets/css/admin-analytics.css';
	$style_version = file_exists( $style_path ) ? (string) filemtime( $style_path ) : NUNLAB_THEME_VERSION;

	wp_enqueue_style(
		'nunlab-admin-analytics',
		NUNLAB_THEME_URI . '/assets/css/admin-analytics.css',
		array(),
		$style_version
	);
}
add_action( 'admin_enqueue_scripts', 'nunlab_enqueue_analytics_admin_assets' );

/**
 * Read a nested analytics value.
 *
 * @param array  $data    Analytics summary.
 * @param string $section Section key.
 * @param string $key     Value key.
 * @return int
 */
function nunlab_analytics_int( $data, $section, $key ) {
	if ( ! isset( $data[ $section ] ) || ! is_array( $data[ $section ] ) ) {
		return 0;
	}

	return isset( $data[ $section ][ $key ] ) ? (int) $data[ $section ][ $key ] : 0;
}

/**
 * Format an ISO timestamp in the WordPress timezone.
 *
 * @param string $timestamp ISO timestamp.
 * @return string
 */
function nunlab_analytics_format_timestamp( $timestamp ) {
	if ( ! $timestamp ) {
		return __( 'Unknown', 'nunlab-theme' );
	}

	$time = strtotime( $timestamp );

	if ( false === $time ) {
		return esc_html( $timestamp );
	}

	return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $time );
}

/**
 * Render a single metric card.
 *
 * @param string $label Metric label.
 * @param int    $value Metric value.
 * @param string $note  Optional note.
 */
function nunlab_analytics_metric_card( $label, $value, $note = '' ) {
	?>
	<section class="nunlab-analytics-card">
		<p class="nunlab-analytics-card__label"><?php echo esc_html( $label ); ?></p>
		<p class="nunlab-analytics-card__value"><?php echo esc_html( number_format_i18n( (int) $value ) ); ?></p>
		<?php if ( $note ) : ?>
			<p class="nunlab-analytics-card__note"><?php echo esc_html( $note ); ?></p>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Return a list-shaped analytics section.
 *
 * @param array  $data Analytics summary.
 * @param string $key  Section key.
 * @return array
 */
function nunlab_analytics_rows( $data, $key ) {
	if ( ! isset( $data[ $key ] ) || ! is_array( $data[ $key ] ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$data[ $key ],
			static function ( $row ) {
				return is_array( $row );
			}
		)
	);
}

/**
 * Read an integer from a row.
 *
 * @param array  $row Row data.
 * @param string $key Value key.
 * @return int
 */
function nunlab_analytics_row_int( $row, $key ) {
	return isset( $row[ $key ] ) ? (int) $row[ $key ] : 0;
}

/**
 * Render a compact proportional list.
 *
 * @param string $title     Panel title.
 * @param array  $rows      Row data.
 * @param string $label_key Label key.
 * @param string $value_key Value key.
 * @param int    $limit     Maximum rows.
 */
function nunlab_analytics_render_bar_list( $title, $rows, $label_key, $value_key, $limit = 8 ) {
	$rows = array_slice( $rows, 0, $limit );
	$max  = 0;

	foreach ( $rows as $row ) {
		$max = max( $max, nunlab_analytics_row_int( $row, $value_key ) );
	}
	?>
	<section class="nunlab-analytics-panel">
		<h2><?php echo esc_html( $title ); ?></h2>
		<?php if ( empty( $rows ) || 0 === $max ) : ?>
			<p class="description"><?php esc_html_e( 'No data yet.', 'nunlab-theme' ); ?></p>
		<?php else : ?>
			<ol class="nunlab-analytics-bars">
				<?php foreach ( $rows as $row ) : ?>
					<?php
					$label   = isset( $row[ $label_key ] ) ? (string) $row[ $label_key ] : '';
					$value   = nunlab_analytics_row_int( $row, $value_key );
					$percent = $max > 0 ? max( 2, ( $value / $max ) * 100 ) : 0;
					?>
					<li class="nunlab-analytics-bars__row">
						<div class="nunlab-analytics-bars__meta">
							<span><?php echo esc_html( $label ); ?></span>
							<strong><?php echo esc_html( number_format_i18n( $value ) ); ?></strong>
						</div>
						<div class="nunlab-analytics-bars__track" aria-hidden="true">
							<span style="width: <?php echo esc_attr( number_format( $percent, 2, '.', '' ) ); ?>%;"></span>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Build SVG point data for the trend chart.
 *
 * @param array  $rows   Daily rows.
 * @param string $key    Value key.
 * @param int    $max    Maximum chart value.
 * @param int    $width  Plot width.
 * @param int    $height Plot height.
 * @return string
 */
function nunlab_analytics_chart_points( $rows, $key, $max, $width, $height ) {
	$count = count( $rows );

	if ( 0 === $count || 0 === $max ) {
		return '';
	}

	$points = array();

	foreach ( $rows as $index => $row ) {
		$x        = $count > 1 ? ( $index / ( $count - 1 ) ) * $width : 0;
		$value    = nunlab_analytics_row_int( $row, $key );
		$y        = $height - ( ( $value / $max ) * $height );
		$points[] = number_format( $x, 2, '.', '' ) . ',' . number_format( $y, 2, '.', '' );
	}

	return implode( ' ', $points );
}

/**
 * Render the main daily trend chart.
 *
 * @param array $rows Daily rows.
 */
function nunlab_analytics_render_trend_chart( $rows ) {
	$rows = array_slice( $rows, -30 );
	$max  = 0;

	foreach ( $rows as $row ) {
		$max = max(
			$max,
			nunlab_analytics_row_int( $row, 'views' ),
			nunlab_analytics_row_int( $row, 'likely_views' ),
			nunlab_analytics_row_int( $row, 'content_views' )
		);
	}

	$width          = 720;
	$height         = 210;
	$views_points   = nunlab_analytics_chart_points( $rows, 'views', $max, $width, $height );
	$likely_points  = nunlab_analytics_chart_points( $rows, 'likely_views', $max, $width, $height );
	$content_points = nunlab_analytics_chart_points( $rows, 'content_views', $max, $width, $height );
	$first_day      = isset( $rows[0]['date'] ) ? (string) $rows[0]['date'] : '';
	$last_day       = ! empty( $rows ) && isset( $rows[ count( $rows ) - 1 ]['date'] ) ? (string) $rows[ count( $rows ) - 1 ]['date'] : '';
	?>
	<section class="nunlab-analytics-panel nunlab-analytics-trend">
		<div class="nunlab-analytics-panel__header">
			<h2><?php esc_html_e( 'Daily Signal', 'nunlab-theme' ); ?></h2>
			<div class="nunlab-analytics-legend" aria-hidden="true">
				<span class="is-total"><?php esc_html_e( 'Page views', 'nunlab-theme' ); ?></span>
				<span class="is-likely"><?php esc_html_e( 'Likely visits', 'nunlab-theme' ); ?></span>
				<span class="is-content"><?php esc_html_e( 'Content views', 'nunlab-theme' ); ?></span>
			</div>
		</div>

		<?php if ( empty( $rows ) || 0 === $max ) : ?>
			<p class="description"><?php esc_html_e( 'No data yet.', 'nunlab-theme' ); ?></p>
		<?php else : ?>
			<svg class="nunlab-analytics-chart" viewBox="0 0 720 210" role="img" aria-labelledby="nunlab-analytics-chart-title">
				<title id="nunlab-analytics-chart-title"><?php esc_html_e( 'Daily page views, likely visits, and content views', 'nunlab-theme' ); ?></title>
				<line x1="0" y1="52.5" x2="720" y2="52.5" />
				<line x1="0" y1="105" x2="720" y2="105" />
				<line x1="0" y1="157.5" x2="720" y2="157.5" />
				<polyline class="is-total" points="<?php echo esc_attr( $views_points ); ?>" />
				<polyline class="is-likely" points="<?php echo esc_attr( $likely_points ); ?>" />
				<polyline class="is-content" points="<?php echo esc_attr( $content_points ); ?>" />
			</svg>
			<div class="nunlab-analytics-chart__axis">
				<span><?php echo esc_html( $first_day ); ?></span>
				<span><?php echo esc_html( $last_day ); ?></span>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Render a simple analytics table.
 *
 * @param string $title   Table title.
 * @param array  $rows    Row data.
 * @param array  $columns Column map.
 */
function nunlab_analytics_render_table( $title, $rows, $columns ) {
	?>
	<section class="nunlab-analytics-panel">
		<h2><?php echo esc_html( $title ); ?></h2>
		<?php if ( empty( $rows ) ) : ?>
			<p class="description"><?php esc_html_e( 'No data yet.', 'nunlab-theme' ); ?></p>
		<?php else : ?>
			<table class="widefat striped nunlab-analytics-table">
				<thead>
					<tr>
						<?php foreach ( $columns as $column ) : ?>
							<th scope="col"><?php echo esc_html( $column ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<?php foreach ( $columns as $key => $column ) : ?>
								<td>
									<?php
									$value = isset( $row[ $key ] ) ? $row[ $key ] : '';

									if ( is_numeric( $value ) ) {
										echo esc_html( number_format_i18n( (int) $value ) );
									} else {
										echo esc_html( (string) $value );
									}
									?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Render the full analytics admin page.
 */
function nunlab_render_analytics_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$summary = nunlab_analytics_read_summary();
	$data    = $summary['data'];
	?>
	<div class="wrap nunlab-analytics">
		<h1><?php esc_html_e( 'N:UN Analytics', 'nunlab-theme' ); ?></h1>

		<?php if ( ! is_array( $data ) ) : ?>
			<div class="notice notice-warning">
				<p><?php echo esc_html( $summary['error'] ); ?></p>
				<p>
					<?php
					printf(
						/* translators: %s: analytics summary paths */
						esc_html__( 'Expected path: %s', 'nunlab-theme' ),
						esc_html( $summary['path'] )
					);
					?>
				</p>
			</div>
		<?php else : ?>
			<?php
			$generated_at = isset( $data['generated_at'] ) ? (string) $data['generated_at'] : '';
			$period       = isset( $data['period'] ) && is_array( $data['period'] ) ? $data['period'] : array();
			$days         = isset( $period['days'] ) ? (int) $period['days'] : 0;
			?>
			<p class="description">
				<?php
				printf(
					/* translators: 1: days, 2: generated timestamp */
					esc_html__( 'Local server-log summary for the last %1$d days. Last updated: %2$s.', 'nunlab-theme' ),
					$days,
					esc_html( nunlab_analytics_format_timestamp( $generated_at ) )
				);
				?>
			</p>

			<div class="nunlab-analytics-notice">
				<?php esc_html_e( 'No visitor-side analytics script is loaded. This dashboard is built from aggregated nginx access logs and stores no visitor IP addresses in WordPress.', 'nunlab-theme' ); ?>
			</div>

			<?php
			$days_rows      = nunlab_analytics_rows( $data, 'days' );
			$page_rows      = nunlab_analytics_rows( $data, 'pages' );
			$country_rows   = nunlab_analytics_rows( $data, 'countries' );
			$referrer_rows  = nunlab_analytics_rows( $data, 'referrers' );
			$source_rows    = nunlab_analytics_rows( $data, 'sources' );
			$device_rows    = nunlab_analytics_rows( $data, 'devices' );
			$browser_rows   = nunlab_analytics_rows( $data, 'browsers' );
			$unique_ips     = nunlab_analytics_int( $data, 'totals', 'visitors' );
			$filtered_total = nunlab_analytics_int( $data, 'totals', 'filtered_requests' );
			$bot_total      = nunlab_analytics_int( $data, 'totals', 'bot_requests' );
			?>

			<div class="nunlab-analytics-grid">
				<?php
				nunlab_analytics_metric_card( __( 'Likely visits', 'nunlab-theme' ), nunlab_analytics_int( $data, 'totals', 'likely_visitors' ), __( 'Stricter unique-IP estimate', 'nunlab-theme' ) );
				nunlab_analytics_metric_card( __( 'Content views', 'nunlab-theme' ), nunlab_analytics_int( $data, 'totals', 'content_views' ), __( 'Non-homepage page views', 'nunlab-theme' ) );
				nunlab_analytics_metric_card( __( 'Unique IPs', 'nunlab-theme' ), $unique_ips, __( 'Broader human-looking traffic', 'nunlab-theme' ) );
				nunlab_analytics_metric_card( __( 'Filtered noise', 'nunlab-theme' ), $filtered_total, __( 'Assets, bots, scanners, and failed requests', 'nunlab-theme' ) );
				?>
			</div>

			<?php nunlab_analytics_render_trend_chart( $days_rows ); ?>

			<div class="nunlab-analytics-grid nunlab-analytics-grid--secondary">
				<?php
				nunlab_analytics_metric_card( __( 'Today likely', 'nunlab-theme' ), nunlab_analytics_int( $data, 'today', 'likely_visitors' ), __( 'Stricter visitors today', 'nunlab-theme' ) );
				nunlab_analytics_metric_card( __( 'Today content', 'nunlab-theme' ), nunlab_analytics_int( $data, 'today', 'content_views' ), __( 'Content page views today', 'nunlab-theme' ) );
				nunlab_analytics_metric_card( __( 'Bot UAs', 'nunlab-theme' ), $bot_total, __( 'Known crawler user agents', 'nunlab-theme' ) );
				nunlab_analytics_metric_card( __( '404s', 'nunlab-theme' ), nunlab_analytics_int( $data, 'totals', 'not_found' ), __( 'Useful for broken links and probes', 'nunlab-theme' ) );
				?>
			</div>

			<div class="nunlab-analytics-columns">
				<?php
				nunlab_analytics_render_bar_list(
					__( 'Top Pages', 'nunlab-theme' ),
					$page_rows,
					'path',
					'likely_views'
				);

				nunlab_analytics_render_bar_list(
					__( 'Traffic Sources', 'nunlab-theme' ),
					$source_rows,
					'label',
					'views'
				);

				nunlab_analytics_render_bar_list(
					__( 'Countries', 'nunlab-theme' ),
					$country_rows,
					'label',
					'likely_views'
				);

				nunlab_analytics_render_bar_list(
					__( 'Referrers', 'nunlab-theme' ),
					$referrer_rows,
					'label',
					'views'
				);

				nunlab_analytics_render_bar_list(
					__( 'Devices', 'nunlab-theme' ),
					$device_rows,
					'label',
					'views'
				);

				nunlab_analytics_render_bar_list(
					__( 'Browsers', 'nunlab-theme' ),
					$browser_rows,
					'label',
					'views'
				);

				nunlab_analytics_render_table(
					__( 'Recent Days', 'nunlab-theme' ),
					array_slice( array_reverse( $days_rows ), 0, 14 ),
					array(
						'date'            => __( 'Date', 'nunlab-theme' ),
						'likely_visitors' => __( 'Likely', 'nunlab-theme' ),
						'content_views'   => __( 'Content', 'nunlab-theme' ),
						'views'           => __( 'Views', 'nunlab-theme' ),
						'visitors'        => __( 'IPs', 'nunlab-theme' ),
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render the compact WordPress dashboard widget.
 */
function nunlab_render_analytics_dashboard_widget() {
	$summary = nunlab_analytics_read_summary();
	$data    = $summary['data'];

	if ( ! is_array( $data ) ) {
		echo '<p>' . esc_html( $summary['error'] ) . '</p>';
		return;
	}

	?>
	<div class="nunlab-analytics-widget">
		<div class="nunlab-analytics-widget__metrics">
			<?php
			nunlab_analytics_metric_card( __( 'Likely visits', 'nunlab-theme' ), nunlab_analytics_int( $data, 'totals', 'likely_visitors' ) );
			nunlab_analytics_metric_card( __( 'Content views', 'nunlab-theme' ), nunlab_analytics_int( $data, 'totals', 'content_views' ) );
			nunlab_analytics_metric_card( __( 'Today likely', 'nunlab-theme' ), nunlab_analytics_int( $data, 'today', 'likely_visitors' ) );
			?>
		</div>
		<p>
			<a href="<?php echo esc_url( admin_url( 'index.php?page=nunlab-analytics' ) ); ?>">
				<?php esc_html_e( 'Open full analytics', 'nunlab-theme' ); ?>
			</a>
		</p>
	</div>
	<?php
}
