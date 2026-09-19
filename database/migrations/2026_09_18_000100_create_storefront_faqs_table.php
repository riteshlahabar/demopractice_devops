<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Questions shown on the website FAQ page, managed from
 * Storefront -> FAQs. Seeded with real questions so the page never ships the
 * template's placeholder text; admin can edit or replace all of them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_faqs', function (Blueprint $table): void {
            $table->id();
            $table->string('question', 500);
            $table->text('answer');
            $table->string('category', 60)->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['getting-started', 'How do I create an account on Bawaskar Farmer Store?', "Click Sign Up in the top menu, fill in your name, mobile number, state, district, taluka, city or village and pincode, and set a password.\n\nYou can also sign in with your mobile number and a one-time password (OTP)."],
            ['getting-started', 'Do I need an account to place an order?', 'Yes. An account lets us confirm your delivery address, show your order history and issue a proper invoice for every purchase.'],
            ['getting-started', 'How do I register as a dealer?', "Register in the Bawaskar Dealer app with your mobile number, firm name and GST number.\n\nOur team verifies the details and activates your account, after which dealer prices and credit limits become visible."],
            ['orders-delivery', 'How long does delivery take?', 'Delivery time depends on your district and the products ordered. The expected date is shown at checkout and in the order tracking page once your order is dispatched.'],
            ['orders-delivery', 'How can I track my order?', 'Open Order Tracking from your dashboard, or use the Track option in the mobile app. You can see every stage from packing to dispatch, out for delivery and delivered.'],
            ['orders-delivery', 'Which areas do you deliver to?', 'Select your district in the location box at the top of the page to see whether we currently deliver there and the minimum order value, if any.'],
            ['pricing-payment', 'What payment methods can I use?', 'You can pay online while placing the order, or choose cash on delivery where it is available. Dealers can also buy on their approved credit limit.'],
            ['pricing-payment', 'Will I get a GST invoice?', 'Yes. A GST invoice is generated for every order and can be downloaded as a PDF from the Invoices page on the website or in the app.'],
            ['support', 'How do I return or cancel an order?', "An order can be cancelled from the order page until it is dispatched.\n\nFor a return, raise a request within 7 days of delivery from the Returns section and our team will confirm the pickup."],
            ['support', 'How do I contact support?', 'Use the Contact Us page or the Help & Support section in the app. You can also call or email us using the details shown in the website footer.'],
        ];

        $insert = [];
        foreach ($rows as $index => [$category, $question, $answer]) {
            $insert[] = [
                'question' => $question,
                'answer' => $answer,
                'category' => $category,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('storefront_faqs')->insert($insert);
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_faqs');
    }
};
