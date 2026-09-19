<?php

namespace Database\Seeders;

use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\CustomerProfile;
use App\Models\DealerProfile;
use App\Models\Inventory\InventoryBatch;
use App\Models\Inventory\Warehouse;
use App\Models\SalesmanProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LanguageSeeder::class);
        $this->call(UnitSeeder::class);

        $admin = User::query()->updateOrCreate(['email' => 'admin@turnkeyinfotech.com'], [
            'name' => 'Bawaskar Administrator', 'mobile' => '9000000001', 'password' => Hash::make('123456'), 'role' => User::ROLE_ADMIN, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $salesman = User::query()->updateOrCreate(['email' => 'salesman@bawaskarerp.com'], [
            'name' => 'Demo Salesman', 'mobile' => '9000000002', 'password' => Hash::make('Salesman@123'), 'role' => User::ROLE_SALESMAN, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        SalesmanProfile::query()->updateOrCreate(['user_id' => $salesman->id], [
            'employee_code' => 'EMP0001', 'joining_date' => today(), 'basic_salary' => 25000, 'target_amount' => 500000, 'territory' => 'Pune',
        ]);
        $dealer = User::query()->updateOrCreate(['email' => 'dealer@bawaskarerp.com'], [
            'name' => 'Demo Dealer', 'mobile' => '9000000003', 'password' => Hash::make('Dealer@123'), 'role' => User::ROLE_DEALER, 'status' => 'active', 'mobile_verified_at' => now(),
        ]);
        DealerProfile::query()->updateOrCreate(['user_id' => $dealer->id], [
            'salesman_id' => $salesman->id, 'dealer_code' => 'DLR000001', 'firm_name' => 'Demo Agro Agency', 'gst_number' => '27AAAAA0000A1Z5', 'credit_limit' => 100000, 'outstanding_balance' => 0, 'approved_at' => now(), 'approved_by' => $admin->id,
        ]);
        $customer = User::query()->updateOrCreate(['email' => 'customer@bawaskarerp.com'], [
            'name' => 'Demo Customer', 'mobile' => '9000000004', 'password' => Hash::make('Customer@123'), 'role' => User::ROLE_CUSTOMER, 'status' => 'active', 'mobile_verified_at' => now(),
        ]);
        CustomerProfile::query()->updateOrCreate(['user_id' => $customer->id], ['preferred_language' => 'mr']);
        $category = Category::query()->updateOrCreate(['slug' => 'veterinary-medicine'], ['name' => 'Veterinary Medicine', 'is_active' => true, 'sort_order' => 1]);
        $brand = Brand::query()->updateOrCreate(['name' => 'Bawaskar Care'], ['is_active' => true]);
        $product = Product::query()->updateOrCreate(['sku' => 'BVC-001'], [
            'category_id' => $category->id, 'brand_id' => $brand->id, 'name' => 'Animal Health Tonic', 'product_type' => 'veterinary', 'hsn_code' => '3004', 'gst_percent' => 12, 'mrp' => 600, 'customer_price' => 550, 'dealer_price' => 480, 'description' => 'Demonstration product. Replace with the company catalogue.', 'is_visible_to_customers' => true, 'is_visible_to_dealers' => true, 'is_active' => true,
        ]);
        $warehouse = Warehouse::query()->updateOrCreate(['code' => 'WH-PUNE'], ['name' => 'Pune Main Warehouse', 'city' => 'Pune', 'is_active' => true]);
        InventoryBatch::query()->updateOrCreate(['warehouse_id' => $warehouse->id, 'product_id' => $product->id, 'batch_no' => 'DEMO-B001'], [
            'manufacturing_date' => today()->subMonth(), 'expiry_date' => today()->addYear(), 'purchase_price' => 350, 'quantity' => 500, 'reserved_quantity' => 0, 'low_stock_alert' => 50,
        ]);

        $this->call(StorefrontSeeder::class);
        $this->call(IndexFiveHomepageSeeder::class);
    }
}
