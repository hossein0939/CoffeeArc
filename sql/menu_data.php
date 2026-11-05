<?php

/**
 * Default demo menu shipped with the public installer.
 * Replace this file (or use menu_data.local.php) for a real cafe menu.
 */
return [
    [
        'name' => 'نوشیدنی گرم',
        'name_en' => 'Hot Drinks',
        'image' => 'categories/espresso.jpg',
        'items' => [
            ['اسپرسو', 'Espresso', 'شات دوبل اسپرسو', 'Double espresso shot', 90000],
            ['آمریکانو', 'Americano', 'اسپرسو با آب داغ', 'Espresso with hot water', 110000],
            ['لاته', 'Latte', 'اسپرسو و شیر بخار داده‌شده', 'Espresso with steamed milk', 145000],
            ['کاپوچینو', 'Cappuccino', 'اسپرسو، شیر و فوم', 'Espresso, milk and foam', 140000],
        ],
    ],
    [
        'name' => 'نوشیدنی سرد',
        'name_en' => 'Cold Drinks',
        'image' => 'categories/iced.jpg',
        'items' => [
            ['آیس لاته', 'Iced Latte', 'لاته روی یخ', 'Latte over ice', 155000],
            ['آیس آمریکانو', 'Iced Americano', 'آمریکانو سرد', 'Chilled americano', 120000],
            ['موهیتو', 'Mojito', 'نعنا، لیمو و سودا', 'Mint, lime and soda', 130000],
        ],
    ],
    [
        'name' => 'خوراکی',
        'name_en' => 'Food',
        'image' => 'categories/breakfast.jpg',
        'items' => [
            ['تست پنیر', 'Cheese Toast', 'نان تست با پنیر و سبزی', 'Toast with cheese and herbs', 180000],
            ['سالاد فصل', 'Garden Salad', 'سبزیجات تازه فصل', 'Fresh seasonal greens', 160000],
            ['کیک روز', 'Cake of the Day', 'برش کیک خانگی', 'Homemade cake slice', 150000],
        ],
    ],
];
