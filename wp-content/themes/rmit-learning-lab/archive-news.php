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
            </ul>
        </nav>
        <a id="main-content"></a>

        <h1 class="margin-top-zero"><?php post_type_archive_title(); ?></h1>

        <?php if (have_posts()) : ?>
            <ul class="list-link-expanded">
                <?php while (have_posts()) : the_post(); ?>
                    <li class="result-item">
                        <a href="<?php the_permalink(); ?>"><h2 class="text"><?php the_title(); ?></h2></a>
                        <p class="small"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></p>
                        <p><?php echo esc_html(get_the_excerpt()); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else : ?>
            <p>There are no announcements yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer();
