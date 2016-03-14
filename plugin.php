<?php
/**
Plugin Name: rest-api-modify-response
Plugin URI:  http://blog.jinyuntech.com
Description:  Modify wp rest api response
Author: ken
Version: 1.0
 */
 
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

/* 文章中加入缩略图 */
add_filter( 'rest_prepare_post', 'my_rest_prepare_post', 10, 3 );

function my_rest_prepare_post( $data, $post, $request ) {
    $_data = $data->data;
  
    if ( has_post_thumbnail() ) {
        $thumbnail_id = get_post_thumbnail_id( $_data['id'] );
        $thumbnail = wp_get_attachment_image_src( $thumbnail_id , 'thumbnail' );
        $featuredimg = wp_get_attachment_image_src( $thumbnail_id , 'full' );
        $thumbnailurl = $thumbnail[0];
        $featuredimgurl = $featuredimg[0];
        if( ! empty($thumbnailurl)){
            $_data['thumbnailurl'] = $thumbnailurl;
        }
        if( ! empty($featuredimgurl)){
            $_data['featuredimgurl'] = $featuredimgurl;
        }
    }else{
        $_data['thumbnailurl'] = null;
        $_data['featuredimgurl'] = null;
    }
    
    $pure_post = array();
    $pure_post['id'] = $_data['id'];
    $pure_post['title'] = $_data['title']['rendered'];
    $pure_post['date'] = $_data['date'];
    $pure_post['thumbnailurl'] = $_data['thumbnailurl'];
    
    $params = $request->get_params();
    if ( isset( $params['id'] ) ) {
        
        //增加分类字段
        $categories = my_rest_get_post_terms( $_data['id'], 'category' );
        if ( ! empty( $categories ) ) { 
            $_data['categories'] = $categories;
        } 
        //增加标签字段    
        $tags = my_rest_get_post_terms( $_data['id'], 'post_tag' );
        if ( ! empty( $tags ) ) {   
            $_data['tags'] = $tags;
        }
        
        $pure_post['categories'] = $_data['categories'];
        $pure_post['tags'] = $_data['tags'];
        $pure_post['featuredimgurl'] = $_data['featuredimgurl'];
        $pure_post['content'] = $_data['content']['rendered'];

    }

    return $pure_post;

}

/*获取分类和标签*/
function my_rest_get_post_terms( $id = false, $taxonomy = 'category' ) {
    if ( ! $id ) {        
        return FALSE; 
    }     
    $valid_tax = apply_filters( 'my_rest_valid_tax', array( 'category', 'post_tag' ) );
    $taxonomy = ( in_array( $taxonomy, $valid_tax ) ) ? $taxonomy : 'category';   
    $terms = wp_get_post_terms( absint( $id ), $taxonomy );    
    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
        foreach ( $terms as $term ) {
            $link = get_term_link( $term );
            $term->link = $link;     
        }   
    }   
    return $terms;
}

