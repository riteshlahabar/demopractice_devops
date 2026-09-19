<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Content of the website About Us page: the intro block, the feature bullets
 * and "What We Do" figures (both kept in one table, told apart by `block`),
 * and the team members. Seeded with real text so the page never ships the
 * template's placeholder copy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_about_page', function (Blueprint $table): void {
            $table->id();
            $table->string('intro_label')->nullable();
            $table->string('intro_heading')->nullable();
            $table->text('intro_text')->nullable();
            $table->string('image_one_path', 2048)->nullable();
            $table->string('image_two_path', 2048)->nullable();
            $table->string('stats_label')->nullable();
            $table->string('stats_heading')->nullable();
            $table->string('team_label')->nullable();
            $table->string('team_heading')->nullable();
            $table->timestamps();
        });

        Schema::create('storefront_about_items', function (Blueprint $table): void {
            $table->id();
            $table->string('block', 20)->index();          // highlight | stat
            $table->string('icon_path', 2048)->nullable();
            $table->string('value', 50)->nullable();        // the big number on a stat
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('storefront_team_members', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('bio', 500)->nullable();
            $table->string('photo_path', 2048)->nullable();
            $table->string('facebook_url', 2048)->nullable();
            $table->string('instagram_url', 2048)->nullable();
            $table->string('twitter_url', 2048)->nullable();
            $table->string('linkedin_url', 2048)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();

        DB::table('storefront_about_page')->insert([
            'intro_label' => 'About Us',
            'intro_heading' => 'Farm inputs you can trust, delivered to your district',
            'intro_text' => "Bawaskar Farmer Store supplies medicines, fertilizers, seeds, veterinary products and equipment to farmers and dealers across Maharashtra.\n\nEvery product we list is sourced from verified manufacturers, priced the same for everyone, and billed with a proper GST invoice. Our field team and dealer network keep the supply close to the farm so you spend less time waiting for stock.",
            'image_one_path' => 'fastkart-store/images/inner-page/about-us/1.jpg',
            'image_two_path' => 'fastkart-store/images/inner-page/about-us/2.jpg',
            'stats_label' => 'What We Do',
            'stats_heading' => 'Trusted by farmers and dealers',
            'team_label' => 'Our Team',
            'team_heading' => 'The people behind the store',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $items = [
            ['highlight', 'fastkart-store/svg/3/delivery.svg', null, 'Delivery across our service districts', null],
            ['highlight', 'fastkart-store/svg/3/leaf.svg', null, 'Genuine products from verified manufacturers', null],
            ['highlight', 'fastkart-store/svg/3/delivery.svg', null, 'GST invoice with every order', null],
            ['highlight', 'fastkart-store/svg/3/leaf.svg', null, 'Dealer prices and credit for registered firms', null],
            ['stat', 'fastkart-store/svg/3/work.svg', '10+', 'Years in Business', 'A decade of supplying agricultural inputs to farmers, dealers and institutional buyers.'],
            ['stat', 'fastkart-store/svg/3/buy.svg', '500+', 'Products', 'Medicines, fertilizers, seeds, veterinary products and equipment in one catalogue.'],
            ['stat', 'fastkart-store/svg/3/user.svg', '1000+', 'Farmers Served', 'Orders placed from the website and from our customer, dealer and salesman apps.'],
        ];

        $rows = [];
        foreach ($items as $index => [$block, $icon, $value, $title, $description]) {
            $rows[] = [
                'block' => $block,
                'icon_path' => $icon,
                'value' => $value,
                'title' => $title,
                'description' => $description,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('storefront_about_items')->insert($rows);

        $team = [
            ['Ramesh Bawaskar', 'Managing Director', 'Leads the company and its supplier relationships.', 'fastkart-store/images/inner-page/user/1.jpg'],
            ['Sunita Deshmukh', 'Operations Head', 'Looks after stock, dispatch and delivery across districts.', 'fastkart-store/images/inner-page/user/2.jpg'],
            ['Amit Jadhav', 'Sales Manager', 'Handles the dealer network and the field sales team.', 'fastkart-store/images/inner-page/user/3.jpg'],
        ];

        $members = [];
        foreach ($team as $index => [$name, $role, $bio, $photo]) {
            $members[] = [
                'name' => $name,
                'role' => $role,
                'bio' => $bio,
                'photo_path' => $photo,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('storefront_team_members')->insert($members);
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_team_members');
        Schema::dropIfExists('storefront_about_items');
        Schema::dropIfExists('storefront_about_page');
    }
};
