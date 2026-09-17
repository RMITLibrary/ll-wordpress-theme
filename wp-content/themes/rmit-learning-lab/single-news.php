<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

get_header();
?>

<div class="container" id="page-content">
    <div class="col-xl-8">
        <nav aria-label="breadcrumbs">
            <ul class="breadcrumbs">
                <li><a href="/">Home</a></li>
                <li><a href="<?php echo esc_url(get_post_type_archive_link('news')); ?>">What's new</a></li>
            </ul>
        </nav>
        <a id="main-content"></a>

        <?php while (have_posts()) : the_post(); ?>
            <h1 class="margin-top-zero"><?php the_title(); ?></h1>
            <p class="small"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></p>
            <?php the_content(); ?>
        <?php endwhile; ?>

        <p><a href="<?php echo esc_url(get_post_type_archive_link('news')); ?>">&larr; All announcements</a></p>
    </div>
</div>

<?php get_footer();
