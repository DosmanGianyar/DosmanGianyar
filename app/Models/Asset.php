<?php

namespace App\Models;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Asset extends Model
{
    protected $fillable = [
        'qr_code', 'name', 'category', 'brand_id', 'room_id',
        'condition', 'photo', 'description', 'quantity', 'purchase_year',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Asset $asset) {
            $asset->qr_code ??= Str::uuid()->toString();
        });

        static::created(function (Asset $asset) {
            $asset->generateQrImage();
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(AssetLoan::class);
    }

    public function issuances(): HasMany
    {
        return $this->hasMany(AssetIssuance::class);
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function generateQrImage(): void
    {
        $url     = url("/siswa/sarpras/asset/{$this->qr_code}");
        $options = new QROptions(['outputType' => 'svg', 'svgAddXmlHeader' => false]);
        $svg     = (new QRCode($options))->render($url);

        Storage::disk('public')->put("qrcodes/{$this->qr_code}.svg", $svg);
    }

    public function qrImageUrl(): ?string
    {
        if (Storage::disk('public')->exists("qrcodes/{$this->qr_code}.svg")) {
            return Storage::url("qrcodes/{$this->qr_code}.svg");
        }
        return null;
    }

    public function conditionLabel(): string
    {
        return match ($this->condition) {
            'baik'         => 'Baik',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat'  => 'Rusak Berat',
            default        => $this->condition,
        };
    }

    public function conditionColor(): string
    {
        return match ($this->condition) {
            'baik'         => 'green',
            'rusak_ringan' => 'yellow',
            'rusak_berat'  => 'red',
            default        => 'gray',
        };
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'perpus'    => 'Perpustakaan',
            'sarana'    => 'Sarana',
            'prasarana' => 'Prasarana',
            default     => $this->category,
        };
    }
}
