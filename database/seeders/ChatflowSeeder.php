<?php

namespace Syofyanzuhad\FilamentChatflow\Database\Seeders;

use Illuminate\Database\Seeder;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowStep;

class ChatflowSeeder extends Seeder
{
    public function run(): void
    {
        // Create a sample customer support chatflow
        $chatflow = Chatflow::create([
            'name' => 'Customer Support Flow',
            'description' => 'A simple chatflow for customer support with common questions',
            'is_active' => true,
            'welcome_message' => [
                'en' => 'Hello! Welcome to our support chat. How can I help you today?',
                'id' => 'Halo! Selamat datang di chat dukungan kami. Ada yang bisa saya bantu?',
            ],
            'position' => 'bottom-right',
            'settings' => [
                'theme_color' => '#3b82f6',
                'sound_enabled' => true,
                'notification_sound' => 'notification.mp3',
                'message_sound' => 'message.mp3',
                'show_badge' => true,
                'auto_open' => false,
                'email_enabled' => true,
            ],
        ]);

        // Step 1: Welcome & Main Menu
        $step1 = ChatflowStep::create([
            'chatflow_id' => $chatflow->id,
            'type' => ChatflowStep::TYPE_QUESTION,
            'content' => [
                'en' => 'Please select one of the following options:',
                'id' => 'Silakan pilih salah satu opsi berikut:',
            ],
            'options' => [
                [
                    'value' => 'product_inquiry',
                    'label' => [
                        'en' => 'Product Inquiry',
                        'id' => 'Pertanyaan Produk',
                    ],
                ],
                [
                    'value' => 'technical_support',
                    'label' => [
                        'en' => 'Technical Support',
                        'id' => 'Dukungan Teknis',
                    ],
                ],
                [
                    'value' => 'billing',
                    'label' => [
                        'en' => 'Billing Question',
                        'id' => 'Pertanyaan Tagihan',
                    ],
                ],
                [
                    'value' => 'other',
                    'label' => [
                        'en' => 'Other',
                        'id' => 'Lainnya',
                    ],
                ],
            ],
            'position_x' => 100,
            'position_y' => 100,
            'order' => 1,
        ]);

        // Step 2a: Product Inquiry Response
        $step2a = ChatflowStep::create([
            'chatflow_id' => $chatflow->id,
            'parent_id' => $step1->id,
            'type' => ChatflowStep::TYPE_MESSAGE,
            'content' => [
                'en' => 'Great! I can help you with product information. You can browse our catalog at www.example.com/products or ask me specific questions.',
                'id' => 'Bagus! Saya dapat membantu Anda dengan informasi produk. Anda dapat melihat katalog kami di www.example.com/products atau tanyakan pertanyaan spesifik.',
            ],
            'position_x' => 300,
            'position_y' => 50,
            'order' => 2,
        ]);

        // Step 2b: Technical Support Response
        $step2b = ChatflowStep::create([
            'chatflow_id' => $chatflow->id,
            'parent_id' => $step1->id,
            'type' => ChatflowStep::TYPE_QUESTION,
            'content' => [
                'en' => 'What kind of technical issue are you experiencing?',
                'id' => 'Masalah teknis apa yang Anda alami?',
            ],
            'options' => [
                [
                    'value' => 'login_issue',
                    'label' => [
                        'en' => 'Login Issue',
                        'id' => 'Masalah Login',
                    ],
                ],
                [
                    'value' => 'performance',
                    'label' => [
                        'en' => 'Performance Issues',
                        'id' => 'Masalah Performa',
                    ],
                ],
                [
                    'value' => 'error_message',
                    'label' => [
                        'en' => 'Error Message',
                        'id' => 'Pesan Error',
                    ],
                ],
            ],
            'position_x' => 300,
            'position_y' => 150,
            'order' => 3,
        ]);

        // Step 3a: Login Issue Solution
        $step3a = ChatflowStep::create([
            'chatflow_id' => $chatflow->id,
            'parent_id' => $step2b->id,
            'type' => ChatflowStep::TYPE_MESSAGE,
            'content' => [
                'en' => 'For login issues, please try: 1) Clear your browser cache, 2) Reset your password using the "Forgot Password" link, 3) Try a different browser. If the issue persists, please contact support@example.com',
                'id' => 'Untuk masalah login, silakan coba: 1) Hapus cache browser Anda, 2) Reset password menggunakan link "Lupa Password", 3) Coba browser lain. Jika masalah berlanjut, hubungi support@example.com',
            ],
            'position_x' => 500,
            'position_y' => 50,
            'order' => 4,
        ]);

        // Step 3b: Performance Issue Solution
        $step3b = ChatflowStep::create([
            'chatflow_id' => $chatflow->id,
            'parent_id' => $step2b->id,
            'type' => ChatflowStep::TYPE_MESSAGE,
            'content' => [
                'en' => 'For performance issues, please check: 1) Your internet connection speed, 2) Close unnecessary browser tabs, 3) Update your browser to the latest version. Our technical team can also help at support@example.com',
                'id' => 'Untuk masalah performa, silakan periksa: 1) Kecepatan koneksi internet Anda, 2) Tutup tab browser yang tidak perlu, 3) Update browser ke versi terbaru. Tim teknis kami juga dapat membantu di support@example.com',
            ],
            'position_x' => 500,
            'position_y' => 150,
            'order' => 5,
        ]);

        // Step 2c: Billing Response
        $step2c = ChatflowStep::create([
            'chatflow_id' => $chatflow->id,
            'parent_id' => $step1->id,
            'type' => ChatflowStep::TYPE_MESSAGE,
            'content' => [
                'en' => 'For billing questions, please contact our billing department at billing@example.com or call +1-234-567-8900. They are available Monday-Friday, 9 AM - 5 PM.',
                'id' => 'Untuk pertanyaan tagihan, silakan hubungi departemen tagihan kami di billing@example.com atau telepon +1-234-567-8900. Mereka tersedia Senin-Jumat, 9 pagi - 5 sore.',
            ],
            'position_x' => 300,
            'position_y' => 250,
            'order' => 6,
        ]);

        // Step 2d: Other Response
        $step2d = ChatflowStep::create([
            'chatflow_id' => $chatflow->id,
            'parent_id' => $step1->id,
            'type' => ChatflowStep::TYPE_MESSAGE,
            'content' => [
                'en' => 'For other inquiries, please send us an email at info@example.com or fill out our contact form at www.example.com/contact. We will get back to you within 24 hours.',
                'id' => 'Untuk pertanyaan lainnya, silakan kirim email ke info@example.com atau isi formulir kontak kami di www.example.com/contact. Kami akan membalas dalam 24 jam.',
            ],
            'position_x' => 300,
            'position_y' => 350,
            'order' => 7,
        ]);

        // Final Step: Thank You & Ask for Feedback
        $stepFinal = ChatflowStep::create([
            'chatflow_id' => $chatflow->id,
            'type' => ChatflowStep::TYPE_QUESTION,
            'content' => [
                'en' => 'Was this helpful?',
                'id' => 'Apakah ini membantu?',
            ],
            'options' => [
                [
                    'value' => 'yes',
                    'label' => [
                        'en' => 'Yes, thank you!',
                        'id' => 'Ya, terima kasih!',
                    ],
                ],
                [
                    'value' => 'no',
                    'label' => [
                        'en' => 'No, I need more help',
                        'id' => 'Tidak, saya perlu bantuan lebih',
                    ],
                ],
            ],
            'position_x' => 700,
            'position_y' => 200,
            'order' => 8,
        ]);

        // End Step - Positive
        $stepEndPositive = ChatflowStep::create([
            'chatflow_id' => $chatflow->id,
            'parent_id' => $stepFinal->id,
            'type' => ChatflowStep::TYPE_END,
            'content' => [
                'en' => 'Great! Thank you for using our support chat. Have a wonderful day!',
                'id' => 'Bagus! Terima kasih telah menggunakan chat dukungan kami. Semoga hari Anda menyenangkan!',
            ],
            'position_x' => 900,
            'position_y' => 150,
            'order' => 9,
        ]);

        // End Step - Negative
        $stepEndNegative = ChatflowStep::create([
            'chatflow_id' => $chatflow->id,
            'parent_id' => $stepFinal->id,
            'type' => ChatflowStep::TYPE_END,
            'content' => [
                'en' => 'I apologize that I couldn\'t help more. A support agent will contact you shortly at your registered email address. Thank you for your patience.',
                'id' => 'Mohon maaf saya tidak dapat membantu lebih. Agen dukungan akan menghubungi Anda segera di alamat email terdaftar. Terima kasih atas kesabaran Anda.',
            ],
            'position_x' => 900,
            'position_y' => 250,
            'order' => 10,
        ]);

        // Update next_step_id relationships
        $step2a->update(['next_step_id' => $stepFinal->id]);
        $step3a->update(['next_step_id' => $stepFinal->id]);
        $step3b->update(['next_step_id' => $stepFinal->id]);
        $step2c->update(['next_step_id' => $stepFinal->id]);
        $step2d->update(['next_step_id' => $stepFinal->id]);

        // Update options with next_step_id
        $step1->update([
            'options' => [
                [
                    'value' => 'product_inquiry',
                    'label' => ['en' => 'Product Inquiry', 'id' => 'Pertanyaan Produk'],
                    'next_step_id' => $step2a->id,
                ],
                [
                    'value' => 'technical_support',
                    'label' => ['en' => 'Technical Support', 'id' => 'Dukungan Teknis'],
                    'next_step_id' => $step2b->id,
                ],
                [
                    'value' => 'billing',
                    'label' => ['en' => 'Billing Question', 'id' => 'Pertanyaan Tagihan'],
                    'next_step_id' => $step2c->id,
                ],
                [
                    'value' => 'other',
                    'label' => ['en' => 'Other', 'id' => 'Lainnya'],
                    'next_step_id' => $step2d->id,
                ],
            ],
        ]);

        $step2b->update([
            'options' => [
                [
                    'value' => 'login_issue',
                    'label' => ['en' => 'Login Issue', 'id' => 'Masalah Login'],
                    'next_step_id' => $step3a->id,
                ],
                [
                    'value' => 'performance',
                    'label' => ['en' => 'Performance Issues', 'id' => 'Masalah Performa'],
                    'next_step_id' => $step3b->id,
                ],
                [
                    'value' => 'error_message',
                    'label' => ['en' => 'Error Message', 'id' => 'Pesan Error'],
                    'next_step_id' => $stepFinal->id,
                ],
            ],
        ]);

        $stepFinal->update([
            'options' => [
                [
                    'value' => 'yes',
                    'label' => ['en' => 'Yes, thank you!', 'id' => 'Ya, terima kasih!'],
                    'next_step_id' => $stepEndPositive->id,
                ],
                [
                    'value' => 'no',
                    'label' => ['en' => 'No, I need more help', 'id' => 'Tidak, saya perlu bantuan lebih'],
                    'next_step_id' => $stepEndNegative->id,
                ],
            ],
        ]);

        $this->command->info('Sample chatflow created successfully!');
    }
}
