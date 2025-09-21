<?php
// includes/Data/comic_data.php

function get_all_comics_data() {
    return [
        'wine' => [
            'title' => '又到一年醉酒时',
            'subtitle' => '生日扎堆,节日扎堆,庆祝扎堆',
            'lines' => "躁动的时节.... <br />疯狂终于找到了理由",
            'image' => '/assets/images/ray-comic-img-wine350px.jpg',
            'alt' => '又到一年醉酒时'
        ], 
        'gzjy' => [
            'title' => '经验,经验从何而来？',
            'subtitle' => '都要有经验的, 都要有经验的，初始经验从何而来?',
            'lines' => "这又不是魔兽世界，遍地的狗头人给你开始提升经验～～",
            'image' => '/assets/images/ray-comic-img-Ubuntu神奇.jpg',
            'alt' => '又到一年醉酒时'
        ]
        , 'wine' => [
            'title' => '又到一年醉酒时',
            'subtitle' => '生日扎堆,节日扎堆,庆祝扎堆',
            'lines' => "躁动的时节.... <br />疯狂终于找到了理由",
            'image' => '/assets/images/ray-comic-img-wine350px.jpg',
            'alt' => '又到一年醉酒时'
        ],
        'MagicUbuntu' => [
            'title' => 'MagicUbuntu',
            'subtitle' => '来自Buzz里的一段对话...',
            'lines' => "如有巧合纯属正常！",
            'image' => '/assets/images/ray-comic-img-Ubuntu神奇.jpg',
            'alt' => 'MagicUbuntu'
        ],
        'icefire' => [
            'title' => '冰与火',
            'subtitle' => '装逼的下场',
            'lines' => "如有巧合纯属正常！",
            'image' => '/assets/images/ray-comic-img-icefire.jpg',
            'alt' => '冰与火之歌'
        ],
        // ... 在这里添加更多漫画数据
    ];
}

function get_comic_by_id($id) {
    $comics = get_all_comics_data();
    return $comics[$id] ?? null;
}