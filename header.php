<?php
/**
 * Шапка сайта.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#sdon-content"><?php esc_html_e( 'Перейти к содержимому', 'sd-on-theme' ); ?></a>

<?php if ( sdon_is( 'preloader' ) ) : ?>
	<div class="sdon-preloader" data-sdon-preloader aria-hidden="true"><span></span></div>
<?php endif; ?>

<?php if ( 'none' !== sdon_opt( 'bg_animation' ) ) : ?>
	<canvas class="sdon-bg-canvas" data-sdon-bg-canvas aria-hidden="true"></canvas>
<?php endif; ?>

<div class="sdon-site">
	<header
		id="sdon-masthead"
		class="sdon-header sdon-header--<?php echo esc_attr( sdon_opt( 'header_layout' ) ); ?><?php echo sdon_is( 'menu_sticky' ) ? ' sdon-header--sticky' : ''; ?>"
	>
		<div class="sdon-container sdon-header__inner">
			<div class="sdon-branding">
				<?php if ( sdon_is( 'header_show_logo' ) && has_custom_logo() ) : ?>
					<div class="sdon-branding__logo"><?php the_custom_logo(); ?></div>
				<?php endif; ?>

				<?php if ( sdon_is( 'header_show_title' ) ) : ?>
					<div class="sdon-branding__text">
						<?php if ( is_front_page() && ! is_paged() ) : ?>
							<h1 class="sdon-branding__title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( sdon_site_title() ); ?></a></h1>
						<?php else : ?>
							<p class="sdon-branding__title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( sdon_site_title() ); ?></a></p>
						<?php endif; ?>

						<?php $sdon_tagline = sdon_site_tagline(); ?>
						<?php if ( '' !== $sdon_tagline ) : ?>
							<p class="sdon-branding__tagline"><?php echo esc_html( $sdon_tagline ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<nav
				id="sdon-primary-navigation"
				class="sdon-nav"
				aria-label="<?php esc_attr_e( 'Главное меню', 'sd-on-theme' ); ?>"
			>
				<?php sdon_nav_menu( 'primary' ); ?>

				<?php if ( sdon_is( 'header_show_search' ) ) : ?>
					<div class="sdon-nav__search"><?php get_search_form(); ?></div>
				<?php endif; ?>
			</nav>

			<div class="sdon-header__actions">
				<?php sdon_dark_mode_toggle(); ?>
				<?php sdon_menu_toggle_button(); ?>
			</div>
		</div>
	</header>

	<?php sdon_render_slider(); ?>
