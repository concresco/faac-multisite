<?php
/*
Template name: Product Page
*/

use Timber\Timber;

$context = Timber::get_context();

get_header();

$pageFields = get_fields();

//echo '<h1>Test page</h1>';


//===========================================================
// Display info
//===========================================================
$context['header']['isHome'] = true; // to have white logo and nav menu



//$context['productInfo'] = json_decode(file_get_contents(get_template_directory_uri(). '/views/sections/productSection/index.json'), true);

$context['productInfo'] = array(
    'breadcrumbs' => array(
        0 => array(
            'text' => 'Product',
            'url' => '#'
        ),
        1 => array(
            'text' => 'Automatic doors',
            'url' => '#'
        ),
        2 => array(
            'text' => 'Revolving doors',
            'url' => '#'
        ),
    ),
    'title' => "RD3 prova test pagina",
    'subtitle' => "Climate-control with the attractive RD3 and RD4 revolving doors.",
    'content' => "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.<br>Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.<br>Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil.",
    'gallery' => array(
        0 => array(
            'img' => get_template_directory_uri() . "/assets/images/stock/AirslideFAAC.jpg",
            'caption' => "lorem ipsum dolor sit amet"
        ),
        1 => array(
            'img' => get_template_directory_uri() . "/assets/images/stock/FAAC-AirSlide-copertina.jpg",
            'caption' => "Dolor sit amet"
        )
    ),
    'btnShowMore' => "Show more",
    'productExtraBtn' => array(
        0 => array(
            'textBtn'=> "How to buy this product",
            'url' => "#"
        ),
        1 => array(
            'textBtn' => "Environmental Product Declaration",
            'url' => "#"
        ),
    ),
    'bimSection' => array(
        "title" => "",
        "bg_image" => get_template_directory_uri() . "/assets/images/stock/sliding-gate.jpg",
        "bg_color" => "bg-color-white",
        "content" => "<h3 classs=\"h2 color-blue\">BIM Object</h3><a href=\"#\" class=\"btn btn-black\">Download the file</a>"
    ),
    'videoGallery' => array(
        'sliderTitle' => "Product video",
        'videoSlider' => array(
            0 => array(
                "image"  => get_template_directory_uri() . "/assets/images/stock/building.jpg",
                "title" => "Video Title here",
                "description"  => "Description.. FAAC Automatic Doors BV supplies access solutions for retail and wholesale, catering, banks, ships, housing associations, hospitals and healthcare institutions, etc. Our clientele includes Schiphol Airport and various large retail chains",
                "videoUrl" => "https://www.youtube.com/embed/RPup22z8_rk"
            ),
            1 => array(
                "image"  => get_template_directory_uri() . "/assets/images/stock/speedway.jpg",
                "title" => "Second slide Title heAutomatic Doors BV supplies access solutions for retail and wholesale, catering, banks, ships, housing associations, hospitals and healthcare institutions, etc. Our clientele includes Schiphol Airport anre",
                "description"  => "Other Description.. FAAC d various large retail chains",
                "videoUrl" => "https://www.youtube.com/embed/RPup22z8_rk"
            ),
        ),
    ),
    'dataSection' => array(
        "ID" => "data",
        "accordion" => array(
            0 => array(
                "ID" => "tech_data_section",
                "accordionTitle" => " Technical description",
                "accordionBody" => "<h3>Lorem ipsum</h3><ul><li>Power supply: 230 V, 50 Hz,mains fuse max 10 A,100-120 V, 50/60 Hz,mains fuse max 16 A</li><li>Internal height non-standard (2100 - 2600 mm)</li><li>Power consumption: 200 W /30 W resting</li><li>Fascia height non-standard (200-1250 mm)</li><li>Powder-coated finish (RAL colours)</li><li>Anodizing, clear or bronze</li><li>Anodizing, clear or bronze</li></ul>"
            ),
            1 => array(
                "ID" => "green_section",
                "accordionTitle" => "Automatically green",
                "accordionBody" => "<h3>Lorem ipsum</h3><p>dolor sit omen</p>"
            ),
            2 => array(
                "ID" => "downloads_section",
                "accordionType" => "download",
                "accordionTitle" => "Download",
                "accordionList" => array(
                    0 => array(
                        "category_name" => "Product leaflet",
                        "file_list" => array(
                            0 => array(
                                "name_file" => "Product leaflet - RD3 and RD4 - EN",
                                "type_file" => "PDF",
                                "size_file" => "5MB",
                                "url_file" => "/media/pdf/Scheda-tecnica-portone-XY.pdf"
                            ),
                        ),
                    ),
                    1 => array(
                        "category_name" => "User manual",
                        "file_list" => array(
                            0 => array(
                                "name_file" => "User manual - RD3 and RD4 - EN",
                                "type_file" => "PDF",
                                "size_file" => "3MB",
                                "url_file" => "/media/pdf/Scheda-tecnica-portone-XY.pdf"
                            ),
                            1 => array(
                                "name_file" => "Manuale utente - RD3 and RD4 - IT",
                                "type_file" => "PDF",
                                "size_file" => "3MB",
                                "url_file" => "/media/pdf/Scheda-tecnica-portone-XY.pdf"
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'referenceSection' => array(
        "sliderTitle" => "References",
        "productsSlider" => array(
            0 => array(
                "image" => get_template_directory_uri() . "/assets/images/stock/building.jpg",
                "description" => "FAAC Automatic Doors BV supplies access solutions for retail and wholesale, catering, banks, ships, housing associations, hospitals and healthcare institutions, etc. Our clientele includes Schiphol Airport and various large retail chains",
                "url" => "#"
            ),
            1 => array(
                "image" => get_template_directory_uri() . "/assets/images/stock/speedway.jpg",
                "description" => "Our clientele includes Schiphol Airport and various large retail chains",
                "url" => "#"
            ),
        ),
    ),
    'relatedProductsSection' => array(
        'ID' => 'related',
        'accordion' => array(
            0 => array(
                'ID' => 'accessories',
                'accordionType' => 'list',
                'accordionTitle' => 'Accessories',
                "productsList" => array(
                    0 => array(
                        "title" => "Access controlled revolving doors",
                        "excerpt" => "Fully automated access controlled revolving doors. All glass revolving doors for a masterpiece entrance Fully automated access controlled revolving doors. All glass revolving doors for a masterpiece entrance.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/building.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    1 => array(
                        "title" => "All glass revolving doors",
                        "excerpt" => "All glass revolving doors for a masterpiece entrance.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/FAAC-AirSlide-copertina.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    2 => array(
                        "title" => "Compact revolving doors",
                        "excerpt" => "Compact revolving doors that fit your entrance needs.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/ambasciata.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    3 => array(
                        "title" => "High capacity revolving doors",
                        "excerpt" => "Reliable high-capacity revolving doors.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/automotive-faac.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                ),
                "btnLoadMore" => array(
                    "text" => "Load More",
                    "url"=> "#loadmore"
                ),
            ),
            1 => array(
                'ID' => 'similar',
                'accordionType' => 'list',
                'accordionTitle' => 'Similar',
                "productsList" => array(
                    0 => array(
                        "title" => "Access controlled revolving doors",
                        "excerpt" => "Fully automated access controlled revolving doors. All glass revolving doors for a masterpiece entrance Fully automated access controlled revolving doors. All glass revolving doors for a masterpiece entrance.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/ambasciata.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    1 => array(
                        "title" => "All glass revolving doors",
                        "excerpt" => "All glass revolving doors for a masterpiece entrance.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/automotive-faac.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    2 => array(
                        "title" => "Compact revolving doors",
                        "excerpt" => "Compact revolving doors that fit your entrance needs.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/FAAC-AirSlide-copertina.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    3 => array(
                        "title" => "High capacity revolving doors",
                        "excerpt" => "Reliable high-capacity revolving doors.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/building.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                ),
                "btnLoadMore" => array(
                    "text" => "Load More",
                    "url"=> "#loadmore"
                ),
            ),
        ),
    ),
);



//showDebugInfo($context['header']['mainMenu'], "top menu data");

Timber::render('templates/productPage/index.twig', $context);

//===========================================================
// Add more elements here
//===========================================================


get_footer();