<?php
/**
 * Adds Digital_Newspaper_Posts_Grid_Widget widget.
 * 
 * @package Digital Newspaper
 * @since 1.0.0
 */
class Digital_Newspaper_Posts_Grid_Widget extends WP_Widget {
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'digital_newspaper_posts_grid_widget',
            esc_html__( 'Digital Newspaper : Posts Grid', 'fairway-finders' ),
            array( 'description' => __( 'A collection of posts from specific category displayed in grid layout.', 'fairway-finders' ) )
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract( $args );
        $widget_title = isset( $instance['widget_title'] ) ? $instance['widget_title'] : '';
        $posts_category = isset( $instance['posts_category'] ) ? $instance['posts_category'] : '';
        $posts_excerpt = isset( $instance['posts_excerpt'] ) ? $instance['posts_excerpt'] : false;
        echo wp_kses_post($before_widget);
            if ( ! empty( $widget_title ) ) {
                echo $before_title . esc_html( $widget_title ) . $after_title;
            }
    ?>
            <div class="posts-wrap posts-grid-wrap feature-post-block layout-two">
                <?php
                    $post = new WP_Query( 
                        array( 
                            'category_name'    => esc_html( $posts_category ),
                            'posts_per_page' => 4,
                            'ignore_sticky_posts'    => true
                        )
                    );
                    if( $post->have_posts() ) :
                        while( $post->have_posts() ) : $post->the_post();
                            $thumbnail_url = get_the_post_thumbnail_url();
                            $categories = get_the_category();
                    ?>
                            <div class="post-item format-standard digital-newspaper-category-no-bk">
                                <div class="post_thumb_image post-thumb <?php if( !$thumbnail_url ) echo esc_attr('no-feat-img'); ?>">
                                    <figure class="post-thumb">
                                        <?php if( $thumbnail_url ) : ?>
                                            <a href="<?php the_permalink(); ?>">
                                                <img src="<?php echo esc_url( $thumbnail_url ); ?>" loading="<?php digital_newspaper_lazy_load_value(); ?>">
                                            </a>
                                        <?php endif; ?>
                                    </figure>
                                </div>
                                <div class="post-content-wrap card__content">
                                    <?php
                                        echo '<div class="bmm-post-cats-wrap bmm-post-meta-item post-categories">';
                                            $count = 0;
                                            foreach( $categories as $cat ) {
                                                echo '<h5 class="card__content-category cat-item cat-' .esc_attr( $cat->cat_ID ). '"><a href="' .esc_url(get_term_link( $cat->cat_ID )). '">' .esc_html( $cat->name ). '</a></h5>';
                                                if( $count > 0 ) break;
                                                $count++;
                                            }
                                        echo '</div>';
                                    ?>
                                    <div class="digital-newspaper-post-title card__content-title post-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </div>
                                    <?php
                                        if( $posts_excerpt ) {
                                            ?>
                                            <div class="post-content card__content-info">
                                                <?php the_excerpt(); ?>
                                            </div>
                                        <?php
                                        }
                                    ?>
                                </div>
                            </div>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                ?>
            </div>
    <?php
        echo wp_kses_post($after_widget);
    }

    /**
     * Widgets fields
     * 
     */
    function widget_fields() {
        $categories = get_categories();
        $categories_options[''] = esc_html__( 'Select category', 'fairway-finders' );
        foreach( $categories as $category ) :
            $categories_options[$category->slug] = $category->name. ' (' .$category->count. ') ';
        endforeach;
        return array(
                array(
                    'name'      => 'widget_title',
                    'type'      => 'text',
                    'title'     => esc_html__( 'Widget Title', 'fairway-finders' ),
                    'description'=> esc_html__( 'Add the widget title here', 'fairway-finders' ),
                    'default'   => esc_html__( 'Trending News', 'fairway-finders' )
                ),
                array(
                    'name'      => 'posts_category',
                    'type'      => 'select',
                    'title'     => esc_html__( 'Categories', 'fairway-finders' ),
                    'description'=> esc_html__( 'Choose the category to display list of posts', 'fairway-finders' ),
                    'options'   => $categories_options
                ),
                array(
                    'name'      => 'posts_excerpt',
                    'type'      => 'checkbox',
                    'title'     => esc_html__( 'Show post excerpt content', 'fairway-finders' ),
                    'default'   => false
                )
            );
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $widget_fields = $this->widget_fields();
        foreach( $widget_fields as $widget_field ) :
            if ( isset( $instance[ $widget_field['name'] ] ) ) {
                $field_value = $instance[ $widget_field['name'] ];
            } else if( isset( $widget_field['default'] ) ) {
                $field_value = $widget_field['default'];
            } else {
                $field_value = '';
            }
            digital_newspaper_widget_fields( $this, $widget_field, $field_value );
        endforeach;
    }
 
    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $widget_fields = $this->widget_fields();
        if( ! is_array( $widget_fields ) ) {
            return $instance;
        }
        foreach( $widget_fields as $widget_field ) :
            $instance[$widget_field['name']] = digital_newspaper_sanitize_widget_fields( $widget_field, $new_instance );
        endforeach;

        return $instance;
    }
 
} // class Digital_Newspaper_Posts_Grid_Widget