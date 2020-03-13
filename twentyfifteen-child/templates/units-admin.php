<?php
/**
 * Template Name: Админка юнитов
 */

get_header();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<article <?php post_class(); ?>>
				

				<header class="entry-header">
					<h1 class="entry-title">Админка юнитов</h1>
				</header><!-- .entry-header -->

				<div class="entry-content">
					<?php get_template_part( 'parts/units-admin-content' ); ?>
				</div><!-- .entry-content -->

				

				

			</article>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php
get_footer();
