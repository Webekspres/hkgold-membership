<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqItemSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            [
                'question' => 'Apa itu program HK GOLD VIP?',
                'answer' => 'HK GOLD VIP adalah program loyalitas eksklusif untuk pelanggan HK GOLD. Setiap transaksi pembelian emas dan perhiasan akan menghasilkan poin yang dapat ditukar dengan berbagai reward menarik.',
            ],
            [
                'question' => 'Bagaimana cara mendapatkan poin?',
                'answer' => 'Poin diperoleh otomatis dari setiap transaksi di cabang HK GOLD maupun melalui kanal resmi lainnya. Jumlah poin bergantung pada nominal transaksi dan tier keanggotaan Anda saat itu.',
            ],
            [
                'question' => 'Bagaimana cara menukar poin?',
                'answer' => 'Buka menu Katalog Reward, pilih reward yang diinginkan, lalu tekan Redeem. Setelah berhasil, Anda dapat melihat detail penukaran di menu Riwayat Redeem pada halaman profil.',
            ],
            [
                'question' => 'Apa saja tier keanggotaan HK GOLD VIP?',
                'answer' => 'Terdapat beberapa tier keanggotaan seperti Silver, Gold, Platinum, dan Elite. Semakin tinggi tier, semakin besar benefit yang Anda dapatkan, termasuk multiplier poin dan akses reward eksklusif.',
            ],
            [
                'question' => 'Apakah poin memiliki masa berlaku?',
                'answer' => 'Ya, poin memiliki masa berlaku sesuai kebijakan program yang berlaku. Pastikan untuk menukarkan poin sebelum tanggal kedaluwarsa agar tidak hangus.',
            ],
            [
                'question' => 'Bisakah saya mentransfer poin ke member lain?',
                'answer' => 'Saat ini poin HK GOLD VIP bersifat personal dan tidak dapat dipindahkan ke akun member lain, kecuali jika ada kebijakan promosi khusus yang diumumkan resmi oleh HK GOLD.',
            ],
            [
                'question' => 'Bagaimana jika reward yang saya inginkan habis stok?',
                'answer' => 'Jika stok reward habis, tombol redeem akan dinonaktifkan sementara. Anda dapat memantau ketersediaan secara berkala atau memilih reward alternatif lain di katalog.',
            ],
            [
                'question' => 'Bagaimana cara menghubungi customer service?',
                'answer' => 'Anda dapat menghubungi customer service HK GOLD melalui cabang terdekat atau saluran resmi yang tercantum di aplikasi. Tim kami siap membantu pertanyaan seputar poin, redeem, dan keanggotaan.',
            ],
        ];

        foreach ($items as $sortOrder => $item) {
            FaqItem::query()->updateOrCreate(
                ['question' => $item['question']],
                [
                    'answer' => $item['answer'],
                    'sort_order' => $sortOrder,
                ],
            );
        }
    }
}
