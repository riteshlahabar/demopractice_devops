<?php

namespace App\Models;

use App\Models\Auth\AdminRole;
use App\Models\Auth\ApiToken;
use App\Models\Communication\Notification;
use App\Models\Field\AttendanceLog;
use App\Models\Field\DealerVisit;
use App\Models\Field\Expense;
use App\Models\Field\LeaveApplication;
use App\Models\Field\SalarySlip;
use App\Models\Field\SalesmanAsset;
use App\Models\Field\SalesmanTarget;
use App\Models\Field\TourPlan;
use App\Models\Finance\DealerStatement;
use App\Models\Finance\Payment;
use App\Models\Sales\Order;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'email', 'mobile', 'password', 'role', 'admin_role_id', 'status', 'mobile_verified_at', 'last_login_at',
    'state_code', 'state_name', 'district_code', 'district_name', 'subdistrict_code', 'subdistrict_name', 'city_village', 'pincode',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SALESMAN = 'salesman';

    public const ROLE_DEALER = 'dealer';

    public const ROLE_CUSTOMER = 'customer';

    /** Domain of the placeholder emails given to accounts created by mobile OTP. */
    public const PLACEHOLDER_EMAIL_SUFFIX = '.bawaskar.local';

    /** @var list<string> */
    protected $appends = ['profile_photo_url'];

    /**
     * The email to show a person, or null for an OTP placeholder address.
     */
    public static function displayEmail(?string $email): ?string
    {
        $email = trim((string) $email);

        return $email === '' || str_ends_with(strtolower($email), self::PLACEHOLDER_EMAIL_SUFFIX) ? null : $email;
    }

    /**
     * Full URL of the uploaded profile photo, or null when there is none.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => filled($this->profile_photo) ? asset($this->profile_photo) : null);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_set_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function createApiToken(string $name = 'mobile-app', array $abilities = ['*']): string
    {
        $plainTextToken = Str::random(80);

        $this->apiTokens()->create([
            'name' => $name,
            'token_hash' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => now()->addDays((int) config('erp_auth.token.lifetime_days', 30)),
        ]);

        return $plainTextToken;
    }

    /**
     * Called when the password changes so a token stolen earlier stops working.
     */
    public function revokeApiTokens(): void
    {
        $this->apiTokens()->delete();
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    /**
     * Admin panel role (admin accounts only).
     */
    public function adminRole(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class);
    }

    public function salesmanProfile(): HasOne
    {
        return $this->hasOne(SalesmanProfile::class);
    }

    public function dealerProfile(): HasOne
    {
        return $this->hasOne(DealerProfile::class);
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function assignedDealers(): HasMany
    {
        return $this->hasMany(DealerProfile::class, 'salesman_id');
    }

    public function customerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function dealerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'dealer_id');
    }

    public function salesmanOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'salesman_id');
    }

    public function paymentsCollected(): HasMany
    {
        return $this->hasMany(Payment::class, 'collected_by');
    }

    public function dealerStatements(): HasMany
    {
        return $this->hasMany(DealerStatement::class, 'dealer_id');
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'salesman_id');
    }

    public function dealerVisits(): HasMany
    {
        return $this->hasMany(DealerVisit::class, 'salesman_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'salesman_id');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(LeaveApplication::class, 'salesman_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(SalesmanAsset::class, 'salesman_id');
    }

    public function tourPlans(): HasMany
    {
        return $this->hasMany(TourPlan::class, 'salesman_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(SalesmanTarget::class, 'salesman_id');
    }

    public function salarySlips(): HasMany
    {
        return $this->hasMany(SalarySlip::class, 'salesman_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
