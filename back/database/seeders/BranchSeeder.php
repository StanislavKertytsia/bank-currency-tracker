<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            'privatbank' => [
                ['branch_name' => 'ПриватБанк — Хрещатик', 'address' => 'вул. Хрещатик, 1, Київ', 'phone' => '3700', 'latitude' => 50.4501, 'longitude' => 30.5234],
                ['branch_name' => 'ПриватБанк — Поділ', 'address' => 'вул. Сагайдачного, 25, Київ', 'phone' => '3700', 'latitude' => 50.4640, 'longitude' => 30.5150],
                ['branch_name' => 'ПриватБанк — Оболонь', 'address' => 'просп. Оболонський, 18, Київ', 'phone' => '3700', 'latitude' => 50.5100, 'longitude' => 30.4980],
                ['branch_name' => 'ПриватБанк — Лівобережна', 'address' => 'просп. Броварський, 5, Київ', 'phone' => '3700', 'latitude' => 50.4450, 'longitude' => 30.6130],
            ],
            'monobank' => [
                ['branch_name' => 'Монобанк — Центр', 'address' => 'вул. Велика Васильківська, 100, Київ', 'phone' => '0800503733', 'latitude' => 50.4250, 'longitude' => 30.5200],
                ['branch_name' => 'Монобанк — Поштова площа', 'address' => 'вул. Спаська, 2, Київ', 'phone' => '0800503733', 'latitude' => 50.4610, 'longitude' => 30.5220],
                ['branch_name' => 'Монобанк — Лук\'янівська', 'address' => 'вул. Дегтярівська, 13, Київ', 'phone' => '0800503733', 'latitude' => 50.4580, 'longitude' => 30.4880],
            ],
            'oschadbank' => [
                ['branch_name' => 'Ощадбанк — Центральне відділення', 'address' => 'вул. Госпітальна, 12Г, Київ', 'phone' => '0800210800', 'latitude' => 50.4400, 'longitude' => 30.5120],
                ['branch_name' => 'Ощадбанк — Деміївська', 'address' => 'просп. Голосіївський, 15, Київ', 'phone' => '0800210800', 'latitude' => 50.4050, 'longitude' => 30.5180],
                ['branch_name' => 'Ощадбанк — Дарниця', 'address' => 'вул. Харківське шосе, 2, Київ', 'phone' => '0800210800', 'latitude' => 50.4290, 'longitude' => 30.6420],
                ['branch_name' => 'Ощадбанк — Троєщина', 'address' => 'вул. Сім\'ї Сосніних, 5, Київ', 'phone' => '0800210800', 'latitude' => 50.5270, 'longitude' => 30.6110],
            ],
            'pumb' => [
                ['branch_name' => 'ПУМБ — Київ Центр', 'address' => 'вул. Пушкінська, 42/4, Київ', 'phone' => '0800500321', 'latitude' => 50.4450, 'longitude' => 30.5110],
                ['branch_name' => 'ПУМБ — Нивки', 'address' => 'просп. Перемоги, 67, Київ', 'phone' => '0800500321', 'latitude' => 50.4680, 'longitude' => 30.4380],
                ['branch_name' => 'ПУМБ — Позняки', 'address' => 'вул. Ревуцького, 40, Київ', 'phone' => '0800500321', 'latitude' => 50.3960, 'longitude' => 30.6290],
            ],
            'ukreximbank' => [
                ['branch_name' => 'Укрексімбанк — Головне відділення', 'address' => 'вул. Антоновича, 127, Київ', 'phone' => '0800505849', 'latitude' => 50.4170, 'longitude' => 30.5060],
                ['branch_name' => 'Укрексімбанк — Печерськ', 'address' => 'вул. Інститутська, 9, Київ', 'phone' => '0800505849', 'latitude' => 50.4480, 'longitude' => 30.5270],
                ['branch_name' => 'Укрексімбанк — Шулявська', 'address' => 'просп. Перемоги, 26, Київ', 'phone' => '0800505849', 'latitude' => 50.4560, 'longitude' => 30.4720],
            ],
        ];

        foreach ($branches as $slug => $items) {
            $bank = Bank::where('slug', $slug)->first();

            if (! $bank) {
                continue;
            }

            foreach ($items as $item) {
                Branch::updateOrCreate(
                    ['bank_id' => $bank->id, 'branch_name' => $item['branch_name']],
                    array_merge($item, ['bank_id' => $bank->id])
                );
            }
        }
    }
}
