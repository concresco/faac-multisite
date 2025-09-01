<?php
/*
Template name: Category Products Page
*/

use Timber\Timber;

$context = Timber::get_context();

get_header();

$pageFields = get_fields();



//===========================================================
// Display info
//===========================================================
$context['header']['isHome'] = true; // to have white logo and nav menu
//$context['archivePage'] = json_decode(file_get_contents(get_template_directory_uri() . '/views/sections/archiveSection/index.json'), true);

$context['archivePage'] = array(
    'hero' => array(
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
        'post' => array(
            'post_title' => 'Revolving doors',
            'post_subtitle' => 'Lorem subtitle content',
            'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
            'thumbnail' => array(
                'src' => 'https://faac.it/wp-content/uploads/2015/02/img-144.jpg'
            ),
        ),
    ),
    'archive' => array(
        'activeFilter' => array(
            'FilterLabel' => 'Filters',
            'filtersSelected' => array(
                0 => array(
                    'filter' => 'Retail',
                    'url' => '#'
                ),
                1 => array(
                    'filter' => 'Hospitality',
                    'url' => '#'
                ),
                2 => array(
                    'filter' => 'Public administration',
                    'url' => '#'
                ),
                3 => array(
                    'filter' => 'Solutions to aid social distancing',
                    'url' => '#'
                ),
                4 => array(
                    'filter' => 'Make Doors Contactless Quickly ',
                    'url' => '#'
                ),
            ),
        ),
        'archiveFilter' => array(
            0 => array(
                'type' => 'list',
                'archiveFilterElement' => 'Product range',
                'category' => array(
                    0 => array(

                        "mainTitle" => "Automatic door",
                        "url" => "/automatic-doors",
                        "subCategory" => array(
                            0 => array(
                                "mainTitle" => "Folding doors",
                                "url" => "/folding-doors"
                            ),
                            1 => array(
                                "mainTitle" => "ICU doors",
                                "url" => "/icu-doors"
                            ),
                            2 => array(
                                "mainTitle" => "Revolving doors",
                                "url" => "/revolving-doors",
                                "subCategory" => array(
                                    0 => array(
                                        "mainTitle" => "Access controlled revolving doors",
                                        "url" => "/Access-controlled-revolving-doors"
                                    ),
                                    1 => array(
                                        "mainTitle" => "All glass revolving doors",
                                        "url" => "/All-glass-revolving-doors"
                                    ),
                                    2 => array(
                                        "mainTitle" => "Compact revolving doors",
                                        "url" => "/Compact-revolving-doors"
                                    ),
                                    3 => array(
                                        "mainTitle" => "High capacity revolving doors",
                                        "url" => "/High-capacity-revolving-doors"
                                    ),
                                    4 => array(
                                        "mainTitle" => "Manual revolving doors",
                                        "url" => "/Manual-revolving-doors"
                                    ),
                                )
                            ),
                            3 => array(
                                "mainTitle" => "Sliding doors",
                                "url" =>"/Sliding-doors"
                            ),
                            4 => array(
                                "mainTitle" => "Swing doors",
                                "url" =>"/Swing-doors"
                            ),
                            5 => array(
                                "mainTitle" => "Accessories",
                                "url" =>"/automatic-doors"
                            ),
                        ),
                    ),
                    1 => array(
                        "mainTitle" => "Industrial door",
                        "url" => "/Industrial-door",
                        "subCategory" => array(
                            0 => array(
                                "mainTitle" => "Folding doors",
                                "url" => "/Folding-doors"
                            ),
                            1 => array(
                                "mainTitle" => "ICU doors",
                                "url" => "/ICU-doors"
                            ),
                            2 => array(
                                "mainTitle" => "Revolving doors",
                                "subCategory" =>array(
                                    0 => array(
                                        "mainTitle" => "Access controlled revolving doors",
                                        "url" => "/#"
                                    ),
                                    1 => array(
                                        "mainTitle" => "All glass revolving doors",
                                        "url" => "/#"
                                    ),
                                    2 => array(
                                        "mainTitle" => "Compact revolving doors",
                                        "url" => "/#"
                                    ),
                                    3 => array(
                                        "mainTitle" => "High capacity revolving doors",
                                        "url" => "/#"
                                    ),
                                    4 => array(
                                        "mainTitle" => "Manual revolving doors",
                                        "url" => "/#"
                                    ),
                                ),
                            ),
                            3 => array(
                                "mainTitle" => "Sliding doors",
                                "url" => "/#"
                            ),
                            4 => array(
                                "mainTitle" => "Swing doors",
                                "url" => "/#"
                            ),
                            5 => array(
                                "mainTitle" => "Accessories",
                                "url" => "/#"
                            ),
                        ),
                    ),
                    2 => array(
                        "mainTitle" => "Roller Shutters",
                        "url" => "/Roller-Shutters"
                    ),
                    3 => array(
                        "mainTitle" => "Loading Bay Equipment",
                        "url" => "/Loading-Bay-Equipment"
                    ),
                    4 => array(
                        "mainTitle" => "Megadoors",
                        "url" => "/Megadoors"
                    ),
                ),
            ),
            1 => array(
                "type" => "cecklist",
                "archiveFilterElement" => "Requirements",
                "category" => array(
                    0 => array(
                        "mainTitle" => "Solutions to aid social distancing"
                    ),
                    1 => array(
                        "mainTitle" => "Make Doors Contactless Quickly "
                    ),
                    2 => array(
                        "mainTitle" => "Hermetic Sealing"
                    ),
                    3 => array(
                        "mainTitle" => "Shop Fronts and All Glazing"
                    ),
                    4 => array(
                        "mainTitle" => "Radiation Protection"
                    ),
                    5 => array(
                        "mainTitle" => "Soundproofing"
                    ),
                    6 => array(
                        "mainTitle" => "Insulated Doors"
                    ),
                    7 => array(
                        "mainTitle" => "Security & Bomb Blast Doors"
                    ),
                    8 => array(
                        "mainTitle" => "Burglar Resistant Doors"
                    ),
                    9 => array(
                        "mainTitle" => "Emergency Opening"
                    ),
                    10 => array(
                        "mainTitle" => "Concealed Drives - Underfloor or In-Head"
                    ),
                    11 => array(
                        "mainTitle" => "Fire Proof Doors"
                    ),
                )
            ),
            2 => array(
                "type" => "cecklist",
                "archiveFilterElement" => "Industry",
                "category" => array(
                    0 => array(
                        "mainTitle" => "Retail"
                    ),
                    1 => array(
                        "mainTitle" => "Distribution & Logistics"
                    ),
                    3 => array(
                        "mainTitle" => "Manufacturing"
                    ),
                    4 => array(
                        "mainTitle" => "Healthcare"
                    ),
                    5 => array(
                        "mainTitle" => "Transportation"
                    ),
                    6 => array(
                        "mainTitle" => "Aviation"
                    ),
                    7 => array(
                        "mainTitle" => "Transport & Aviation"
                    ),
                    8 => array(
                        "mainTitle" => "Shipyards"
                    ),
                    9 => array(
                        "mainTitle" => "Mining industry"
                    ),
                    10 => array(
                        "mainTitle" => "Education"
                    ),
                    11 => array(
                        "mainTitle" => "Commercial & Offices"
                    ),
                    12 => array(
                        "mainTitle" => "Finance"
                    ),
                    13 => array(
                        "mainTitle" => "Public Buildings"
                    ),
                    14 => array(
                        "mainTitle" => "Hospitality"
                    ),
                    15 => array(
                        "mainTitle" => "Hotels & Restaurants"
                    )
                ),
            ),
        ),
        'archiveElement' => array(
            'list' => array(
                0 => array(
                    "title" => "Access controlled revolving doors",
                    "excerpt" => "Fully automated access controlled revolving doors. All glass revolving doors for a masterpiece entrance Fully automated access controlled revolving doors. All glass revolving doors for a masterpiece entrance.",
                    "thumbnail" => get_template_directory_uri() . "/assets/images/stock/AirslideFAAC.jpg",
                    "deepen" => array(
                        "text" => "Explore category",
                        "link" => "#"
                    ),
                ),
                1 => array(
                    "title" => "All glass revolving doors",
                    "excerpt" => "All glass revolving doors for a masterpiece entrance.",
                    "thumbnail" => get_template_directory_uri() . "/assets/images/stock/AirslideFAAC.jpg",
                    "deepen" => array(
                        "text" => "Explore category",
                        "link" => "#"
                    ),
                ),
                2 => array(
                    "title" => "Compact revolving doors",
                    "excerpt" => "Compact revolving doors that fit your entrance needs.",
                    "thumbnail" => get_template_directory_uri() . "/assets/images/stock/AirslideFAAC.jpg",
                    "deepen" => array(
                        "text" => "Explore category",
                        "link" => "#"
                    ),
                ),
                3 => array(
                    "title" => "High capacity revolving doors",
                    "excerpt" => "Reliable high-capacity revolving doors.",
                    "thumbnail" => get_template_directory_uri() . "/assets/images/stock/AirslideFAAC.jpg",
                    "deepen" => array(
                        "text" => "Explore category",
                        "link" => "#"
                    ),
                ),
                4 => array(
                    "title" => "Manual revolving doors",
                    "excerpt" => "Reliable high-capacity revolving doors.",
                    "thumbnail" => get_template_directory_uri() . "/assets/images/stock/AirslideFAAC.jpg",
                    "deepen" => array(
                        "text" => "Explore category",
                        "link" => "#"
                    ),
                ),
            ),
        ),
    ),
);


//showDebugInfo($context['header']['mainMenu'], "top menu data");

Timber::render('templates/archive/index.twig', $context);

//===========================================================
// Add more elements here
//===========================================================


get_footer();
