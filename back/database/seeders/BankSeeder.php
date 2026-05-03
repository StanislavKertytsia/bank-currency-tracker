<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            [
                'name'        => 'ПриватБанк',
                'description' => 'Найбільший комерційний банк України',
                'logo_url'    => 'images/banks/privatbank.svg',
                'website'     => 'https://privatbank.ua',
                'phone'       => '3700',
                'email'       => 'pb@privatbank.ua',
                'address'     => 'м. Дніпро, вул. Набережна Перемоги, 50',
                'rating'      => 4.5,
                'slug'        => 'privatbank',
            ],
            [
                'name'        => 'Монобанк',
                'description' => 'Перший мобільний банк України',
                'logo_url'    => 'images/banks/monobank.svg',
                'website'     => 'https://monobank.ua',
                'phone'       => '0800503733',
                'email'       => 'support@monobank.ua',
                'address'     => 'м. Київ, вул. Антоновича, 45',
                'rating'      => 4.8,
                'slug'        => 'monobank',
            ],
            [
                'name'        => 'Ощадбанк',
                'description' => 'Державний ощадний банк України',
                'logo_url'    => 'images/banks/oschadbank.svg',
                'website'     => 'https://oschadbank.ua',
                'phone'       => '0800210800',
                'email'       => 'info@oschadbank.ua',
                'address'     => 'м. Київ, вул. Госпітальна, 12Г',
                'rating'      => 3.9,
                'slug'        => 'oschadbank',
            ],
            [
                'name'        => 'ПУМБ',
                'description' => 'Перший Український Міжнародний Банк',
                'logo_url'    => 'images/banks/pumb.svg',
                'website'     => 'https://pumb.ua',
                'phone'       => '0800500321',
                'email'       => 'info@pumb.ua',
                'address'     => 'м. Донецьк (переміщений до Києва), вул. Артема, 62',
                'rating'      => 4.1,
                'slug'        => 'pumb',
            ],
            [
                'name'        => 'Укрексімбанк',
                'description' => 'Державний експортно-імпортний банк України',
                'logo_url'    => 'images/banks/ukreximbank.svg',
                'website'     => 'https://eximb.com',
                'phone'       => '0800505849',
                'email'       => 'info@eximb.com',
                'address'     => 'м. Київ, вул. Антоновича, 127',
                'rating'      => 3.7,
                'slug'        => 'ukreximbank',
            ],
        ];

        foreach ($banks as $bank) {
            Bank::updateOrCreate(['slug' => $bank['slug']], $bank);
        }
    }
}
