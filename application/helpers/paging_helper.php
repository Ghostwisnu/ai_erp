<?php defined('BASEPATH') or exit('No direct script access allowed');

function build_pagination($CI, $base_url, $total_rows, $per_page)
{
    $CI->load->library('pagination');
    $config = [
        'base_url'            => $base_url,
        'total_rows'          => $total_rows,
        'per_page'            => $per_page,
        'reuse_query_string'  => true,
        'page_query_string'   => true,
        'query_string_segment' => 'page',

        'full_tag_open'       => '<ul class="pagination">',
        'full_tag_close'      => '</ul>',
        'num_tag_open'        => '<li class="page-item"><span class="page-link">',
        'num_tag_close'       => '</span></li>',
        'cur_tag_open'        => '<li class="page-item active"><span class="page-link">',
        'cur_tag_close'       => '</span></li>',
        'prev_tag_open'       => '<li class="page-item"><span class="page-link">',
        'prev_tag_close'      => '</span></li>',
        'next_tag_open'       => '<li class="page-item"><span class="page-link">',
        'next_tag_close'      => '</span></li>',
        'first_tag_open'      => '<li class="page-item"><span class="page-link">',
        'first_tag_close'     => '</span></li>',
        'last_tag_open'       => '<li class="page-item"><span class="page-link">',
        'last_tag_close'      => '</span></li>',
    ];
    $CI->pagination->initialize($config);
    return $CI->pagination->create_links();
}
