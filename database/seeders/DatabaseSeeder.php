<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Organization;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Models\WifiPortal;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superadmin = User::query()->firstOrCreate(
            ['email' => 'superadmin@medsoft.local'],
            [
                'name' => 'Platform Superadmin',
                'role' => User::ROLE_SUPERADMIN,
                'password' => Hash::make('ChangeMe123!'),
                'is_active' => true,
            ]
        );

        $organization = Organization::query()->firstOrCreate(
            ['slug' => 'demo-cafe'],
            [
                'name' => 'Demo Cafe',
                'timezone' => 'Asia/Baku',
                'default_language' => 'az',
                'contact_email' => 'hello@democafe.local',
                'primary_color' => '#0F766E',
                'accent_color' => '#0284C7',
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'admin@democafe.local'],
            [
                'organization_id' => $organization->id,
                'name' => 'Demo Cafe Admin',
                'role' => User::ROLE_ORG_ADMIN,
                'password' => Hash::make('ChangeMe123!'),
                'is_active' => true,
            ]
        );

        $template = SurveyTemplate::query()->firstOrCreate(
            ['organization_id' => $organization->id, 'name' => 'Demo Experience Survey'],
            [
                'description' => 'Default survey for captive portal login.',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        if (! $template->questions()->exists()) {
            $template->questions()->createMany([
                [
                    'question_key' => 'q_rating',
                    'label' => 'How was your visit today?',
                    'type' => 'rating',
                    'is_required' => true,
                    'order_index' => 1,
                ],
                [
                    'question_key' => 'q_wifi',
                    'label' => 'How stable was our WiFi?',
                    'type' => 'single_choice',
                    'is_required' => true,
                    'order_index' => 2,
                    'options' => ['Excellent', 'Good', 'Average', 'Poor'],
                ],
                [
                    'question_key' => 'q_nps',
                    'label' => 'How likely are you to recommend us?',
                    'type' => 'nps',
                    'is_required' => true,
                    'order_index' => 3,
                ],
                [
                    'question_key' => 'q_comment',
                    'label' => 'What should we improve?',
                    'type' => 'long_text',
                    'is_required' => false,
                    'order_index' => 4,
                ],
            ]);
        }

        WifiPortal::query()->firstOrCreate(
            ['slug' => 'demo-cafe-guest'],
            [
                'organization_id' => $organization->id,
                'survey_template_id' => $template->id,
                'name' => 'Demo Cafe Guest WiFi',
                'portal_token' => Str::random(48),
                'welcome_title' => 'Welcome to Demo Cafe Guest WiFi',
                'welcome_text' => 'Complete this short form to access internet.',
                'terms_text' => 'By using this service you agree to acceptable use terms.',
                'network_vendor' => 'custom',
                'integration_key' => 'pk_demo_'.Str::random(12),
                'integration_secret' => Str::random(48),
            ]
        );

        Campaign::query()->firstOrCreate(
            ['organization_id' => $organization->id, 'name' => 'Welcome Offer'],
            [
                'title' => 'Get 10% Off Your Next Drink',
                'body' => 'Show this screen at checkout to redeem your loyalty discount.',
                'cta_text' => 'View Menu',
                'cta_url' => 'https://example.com/menu',
                'display_rule' => 'all',
                'is_active' => true,
            ]
        );
    }
}
